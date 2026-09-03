<?php
/**
 * Admin: menu, review queue (WP_List_Table), settings, stats, correction AJAX,
 * CSV export (formula-injection safe), per-session delete.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VA_Admin {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'admin_post_va_export_csv', array( __CLASS__, 'export_csv' ) );
		add_action( 'admin_post_va_delete_session', array( __CLASS__, 'delete_session' ) );
	}

	public static function menu() {
		add_menu_page(
			'Vac2Go Advisor',
			'Vac2Go Advisor',
			'manage_options',
			'va-advisor',
			array( __CLASS__, 'render_review_page' ),
			'dashicons-format-chat',
			58
		);

		add_submenu_page( 'va-advisor', 'Review Queue', 'Review Queue', 'manage_options', 'va-advisor', array( __CLASS__, 'render_review_page' ) );
		add_submenu_page( 'va-advisor', 'Advisor Stats', 'Stats', 'manage_options', 'va-advisor-stats', array( __CLASS__, 'render_stats_page' ) );
		add_submenu_page( 'va-advisor', 'Advisor Settings', 'Settings', 'manage_options', 'va-advisor-settings', array( __CLASS__, 'render_settings_page' ) );
	}

	public static function enqueue( $hook ) {
		if ( false === strpos( (string) $hook, 'va-advisor' ) ) {
			return;
		}
		wp_enqueue_style( 'va-advisor-admin', VA_ADVISOR_URL . 'assets/admin.css', array(), VA_ADVISOR_VERSION );
		wp_enqueue_script( 'va-advisor-admin', VA_ADVISOR_URL . 'assets/admin.js', array(), VA_ADVISOR_VERSION, true );
		wp_localize_script(
			'va-advisor-admin',
			'vaAdvisorAdmin',
			array(
				'restUrl' => esc_url_raw( rest_url( 'vac2go/v1' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	public static function render_review_page() {
		require VA_ADVISOR_DIR . 'admin/views/review-page.php';
	}

	public static function render_settings_page() {
		require VA_ADVISOR_DIR . 'admin/views/settings-page.php';
	}

	public static function render_stats_page() {
		require VA_ADVISOR_DIR . 'admin/views/stats-page.php';
	}

	public static function register_settings() {
		$string = function ( $cb ) {
			return array( 'type' => 'string', 'sanitize_callback' => $cb );
		};
		$int = array( 'type' => 'integer', 'sanitize_callback' => 'absint' );

		register_setting( 'va_advisor_settings', 'va_system_prompt', $string( array( __CLASS__, 'sanitize_multiline' ) ) );
		register_setting( 'va_advisor_settings', 'va_banned_patterns', $string( array( __CLASS__, 'sanitize_patterns' ) ) );
		register_setting( 'va_advisor_settings', 'va_profanity_list', $string( array( __CLASS__, 'sanitize_wordlist' ) ) );
		register_setting( 'va_advisor_settings', 'va_capture_mode', $string( array( __CLASS__, 'sanitize_capture_mode' ) ) );
		register_setting( 'va_advisor_settings', 'va_enabled', $int );
		register_setting( 'va_advisor_settings', 'va_rate_ip_minute', $int );
		register_setting( 'va_advisor_settings', 'va_rate_ip_hourly', $int );
		register_setting( 'va_advisor_settings', 'va_rate_session_turns', $int );
		register_setting( 'va_advisor_settings', 'va_global_minute', $int );
		register_setting( 'va_advisor_settings', 'va_global_daily', $int );
		register_setting( 'va_advisor_settings', 'va_streaming', $int );
		register_setting( 'va_advisor_settings', 'va_stream_pad', $int );
		register_setting( 'va_advisor_settings', 'va_daily_token_ceiling', $int );
		register_setting( 'va_advisor_settings', 'va_price_in_per_m', array( 'type' => 'number', 'sanitize_callback' => 'floatval' ) );
		register_setting( 'va_advisor_settings', 'va_price_out_per_m', array( 'type' => 'number', 'sanitize_callback' => 'floatval' ) );
		register_setting( 'va_advisor_settings', 'va_price_cache_read_per_m', array( 'type' => 'number', 'sanitize_callback' => 'floatval' ) );
		register_setting( 'va_advisor_settings', 'va_admin_email', $string( 'sanitize_email' ) );
	}

	public static function sanitize_multiline( $value ) {
		return trim( wp_kses_post( (string) $value ) );
	}

	public static function sanitize_wordlist( $value ) {
		$out = array();
		foreach ( preg_split( '/\r\n|\r|\n/', (string) $value ) as $line ) {
			$line = trim( sanitize_text_field( $line ) );
			if ( '' !== $line ) {
				$out[] = $line;
			}
		}
		return implode( "\n", $out );
	}

	public static function sanitize_patterns( $value ) {
		$out = array();
		foreach ( preg_split( '/\r\n|\r|\n/', (string) $value ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			if ( false !== @preg_match( $line, '' ) ) {
				$out[] = $line;
			} else {
				add_settings_error( 'va_banned_patterns', 'bad_regex', 'Dropped an invalid regex: ' . esc_html( $line ), 'warning' );
			}
		}
		return implode( "\n", $out );
	}

	public static function sanitize_capture_mode( $value ) {
		$valid = array( 'email_only', 'phone_only', 'email_or_phone', 'email_and_phone' );
		return in_array( $value, $valid, true ) ? $value : 'email_only';
	}

	/**
	 * CSV export. Cells starting with = + - @ TAB or CR are prefixed with a single
	 * quote so spreadsheet apps treat them as text, not formulas (CSV injection).
	 */
	public static function export_csv() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions.' );
		}
		check_admin_referer( 'va_export_csv' );

		global $wpdb;
		$table  = VA_DB::table();
		$filter = isset( $_GET['va_filter'] ) ? sanitize_key( $_GET['va_filter'] ) : 'all';
		$where  = self::filter_where( $filter );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( "SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT 5000", ARRAY_A );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=vac2go-advisor-log.csv' );

		$out  = fopen( 'php://output', 'w' );
		$cols = array( 'id', 'created_at', 'session_id', 'request_id', 'turn_index', 'question', 'answer', 'raw_model_answer', 'was_filtered', 'filter_stage', 'filter_reason', 'error_type', 'input_tokens', 'output_tokens', 'cache_creation_input_tokens', 'cache_read_input_tokens', 'contact_name', 'contact_email', 'contact_phone', 'marked_incorrect', 'correction_text', 'corrected_by', 'corrected_at' );
		fputcsv( $out, $cols );
		foreach ( (array) $rows as $r ) {
			$line = array();
			foreach ( $cols as $c ) {
				$line[] = self::csv_safe( isset( $r[ $c ] ) ? (string) $r[ $c ] : '' );
			}
			fputcsv( $out, $line );
		}
		fclose( $out );
		exit;
	}

	/**
	 * Neutralize spreadsheet formula injection.
	 */
	public static function csv_safe( $value ) {
		if ( '' === $value ) {
			return $value;
		}
		$first = substr( $value, 0, 1 );
		if ( in_array( $first, array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
			return "'" . $value;
		}
		return $value;
	}

	/**
	 * Hard-delete all rows for one session (data deletion requests).
	 */
	public static function delete_session() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions.' );
		}
		check_admin_referer( 'va_delete_session' );

		$sid = isset( $_GET['session_id'] ) ? sanitize_text_field( wp_unslash( $_GET['session_id'] ) ) : '';
		if ( '' !== $sid ) {
			$deleted = VA_DB::delete_session( $sid );
			wp_safe_redirect( add_query_arg( array( 'page' => 'va-advisor', 'va_deleted' => (int) $deleted ), admin_url( 'admin.php' ) ) );
			exit;
		}
		wp_safe_redirect( admin_url( 'admin.php?page=va-advisor' ) );
		exit;
	}

	public static function filter_where( $filter ) {
		switch ( $filter ) {
			case 'filtered':
				return 'WHERE was_filtered = 1';
			case 'incorrect':
				return 'WHERE marked_incorrect = 1';
			case 'contact':
				return "WHERE (contact_email <> '' AND contact_email IS NOT NULL) OR (contact_phone <> '' AND contact_phone IS NOT NULL)";
			case 'errors':
				return "WHERE error_type IS NOT NULL AND error_type <> ''";
			default:
				return '';
		}
	}
}

VA_Admin::init();

require_once VA_ADVISOR_DIR . 'admin/class-va-list-table.php';
