<?php
/**
 * Database layer: table creation (dbDelta) and CRUD helpers for the advisor log.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VA_DB {

	/**
	 * Fully-qualified log table name.
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'va_advisor_log';
	}

	/**
	 * Create/upgrade the log table. Safe to call repeatedly (dbDelta is idempotent).
	 */
	public static function activate() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table           = self::table();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			session_id VARCHAR(64) NOT NULL,
			request_id VARCHAR(64) NULL,
			turn_index INT UNSIGNED NOT NULL DEFAULT 0,
			question TEXT NOT NULL,
			answer TEXT NOT NULL,
			raw_model_answer TEXT NULL,
			was_filtered TINYINT(1) NOT NULL DEFAULT 0,
			filter_stage VARCHAR(32) NULL,
			filter_reason VARCHAR(255) NULL,
			error_type VARCHAR(32) NULL,
			input_tokens INT UNSIGNED NULL,
			output_tokens INT UNSIGNED NULL,
			cache_creation_input_tokens INT UNSIGNED NULL,
			cache_read_input_tokens INT UNSIGNED NULL,
			client_history_ignored TINYINT(1) NOT NULL DEFAULT 0,
			history_truncated TINYINT(1) NOT NULL DEFAULT 0,
			contact_name VARCHAR(190) NULL,
			contact_email VARCHAR(190) NULL,
			contact_phone VARCHAR(60) NULL,
			marked_incorrect TINYINT(1) NOT NULL DEFAULT 0,
			correction_text TEXT NULL,
			corrected_by BIGINT UNSIGNED NULL,
			corrected_at DATETIME NULL,
			ip_hash VARCHAR(64) NULL,
			user_agent VARCHAR(255) NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY session_id (session_id),
			KEY request_id (request_id),
			KEY marked_incorrect (marked_incorrect),
			KEY created_at (created_at)
		) {$charset_collate};";

		dbDelta( $sql );

		update_option( 'va_db_version', VA_ADVISOR_DB_VERSION );

		// Seed default options if absent.
		$defaults = array(
			'va_system_prompt'      => null, // set below (needs VA_Knowledge)
			'va_banned_patterns'    => null,
			'va_capture_mode'       => 'email_only',
			'va_capture_name'       => 1,
			'va_answer_length'      => 'short',
			'va_rate_ip_hourly'     => 30,
			'va_rate_ip_minute'     => 6,
			'va_rate_session_turns' => 40,
			'va_global_minute'      => 60,
			'va_global_daily'       => 5000,
			'va_streaming'          => 1,
			'va_stream_pad'         => 4096,
			'va_notify_leads'       => 1,
			'va_corrections_in_prompt' => 1,
			'va_daily_token_ceiling'=> 2000000,
			'va_price_in_per_m'     => 3.0,
			'va_price_out_per_m'    => 15.0,
			'va_price_cache_read_per_m' => 0.30,
			'va_enabled'            => 1,
			'va_admin_email'        => get_option( 'admin_email' ),
		);
		foreach ( $defaults as $key => $val ) {
			if ( null !== $val && false === get_option( $key, false ) ) {
				add_option( $key, $val );
			}
		}
		if ( false === get_option( 'va_system_prompt', false ) ) {
			add_option( 'va_system_prompt', VA_Knowledge::default_system_prompt() );
		}
		if ( false === get_option( 'va_banned_patterns', false ) ) {
			add_option( 'va_banned_patterns', VA_Filter::default_patterns_text() );
		}
		// One-time canary token for prompt-leak detection. Random, generated once.
		if ( false === get_option( 'va_canary', false ) ) {
			add_option( 'va_canary', 'VA-CANARY-' . strtoupper( wp_generate_password( 16, false, false ) ) );
		}
	}

	/**
	 * Insert one Q&A turn. Returns insert id or false.
	 */
	public static function insert_turn( array $data ) {
		global $wpdb;

		$defaults = array(
			'session_id'       => '',
			'request_id'       => null,
			'turn_index'       => 0,
			'question'         => '',
			'answer'           => '',
			'raw_model_answer' => null,
			'was_filtered'     => 0,
			'filter_stage'     => null,
			'filter_reason'    => null,
			'error_type'       => null,
			'input_tokens'     => null,
			'output_tokens'    => null,
			'cache_creation_input_tokens' => null,
			'cache_read_input_tokens'     => null,
			'client_history_ignored'      => 0,
			'history_truncated'           => 0,
			'ip_hash'          => null,
			'user_agent'       => null,
			'created_at'       => current_time( 'mysql' ),
		);
		$row = array_merge( $defaults, $data );

		// Carry forward any contact details already captured for this session, so a
		// turn logged AFTER the customer submitted contact still shows it.
		$contact = self::get_session_contact( $row['session_id'] );

		$ok = $wpdb->insert(
			self::table(),
			array(
				'session_id'       => $row['session_id'],
				'request_id'       => $row['request_id'],
				'turn_index'       => (int) $row['turn_index'],
				'question'         => $row['question'],
				'answer'           => $row['answer'],
				'raw_model_answer' => $row['raw_model_answer'],
				'was_filtered'     => (int) $row['was_filtered'],
				'filter_stage'     => $row['filter_stage'],
				'filter_reason'    => $row['filter_reason'],
				'error_type'       => $row['error_type'],
				'input_tokens'     => $row['input_tokens'],
				'output_tokens'    => $row['output_tokens'],
				'cache_creation_input_tokens' => $row['cache_creation_input_tokens'],
				'cache_read_input_tokens'     => $row['cache_read_input_tokens'],
				'client_history_ignored'      => (int) $row['client_history_ignored'],
				'history_truncated'           => (int) $row['history_truncated'],
				'contact_name'     => $contact['contact_name'],
				'contact_email'    => $contact['contact_email'],
				'contact_phone'    => $contact['contact_phone'],
				'ip_hash'          => $row['ip_hash'],
				'user_agent'       => $row['user_agent'],
				'created_at'       => $row['created_at'],
			)
		);

		return $ok ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Look up a recent identical request (idempotency). Returns the stored answer row
	 * or null. Window keeps replays from re-billing the model.
	 */
	public static function find_recent_request( $session_id, $request_id, $window_seconds = 30 ) {
		global $wpdb;
		if ( ! $request_id ) {
			return null;
		}
		$cutoff = gmdate( 'Y-m-d H:i:s', current_time( 'timestamp' ) - $window_seconds );
		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT id, answer, was_filtered FROM ' . self::table() .
				' WHERE session_id = %s AND request_id = %s AND created_at >= %s ORDER BY id DESC LIMIT 1',
				$session_id,
				$request_id,
				$cutoff
			),
			ARRAY_A
		);
		return $row ? $row : null;
	}

	/**
	 * Server-side conversation history for a session: the questions and the answers
	 * actually returned, ordered by turn. Excludes rows that were pure errors so the
	 * model does not learn from outage fallbacks.
	 */
	public static function get_history( $session_id, $max_turns = 10, $max_chars = 12000 ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT question, answer FROM ' . self::table() .
				' WHERE session_id = %s AND ( error_type IS NULL OR error_type = "" ) ORDER BY turn_index DESC LIMIT %d',
				$session_id,
				$max_turns
			),
			ARRAY_A
		);
		$rows = array_reverse( $rows ); // chronological

		$messages  = array();
		$truncated = false;
		$total     = 0;
		// Build newest-last; enforce char budget by dropping OLDEST first.
		$pairs = array();
		foreach ( $rows as $r ) {
			$pairs[] = $r;
		}
		// Walk from newest to oldest accumulating until budget hit.
		$kept = array();
		for ( $i = count( $pairs ) - 1; $i >= 0; $i-- ) {
			$len = mb_strlen( $pairs[ $i ]['question'] ) + mb_strlen( $pairs[ $i ]['answer'] );
			if ( $total + $len > $max_chars && count( $kept ) > 0 ) {
				$truncated = true;
				break;
			}
			$total += $len;
			array_unshift( $kept, $pairs[ $i ] );
		}
		if ( count( $kept ) < count( $pairs ) ) {
			$truncated = true;
		}

		foreach ( $kept as $r ) {
			$messages[] = array( 'role' => 'user', 'content' => $r['question'] );
			$messages[] = array( 'role' => 'assistant', 'content' => $r['answer'] );
		}

		return array( 'messages' => $messages, 'truncated' => $truncated );
	}

	/**
	 * Attach contact details to every row of a session.
	 */
	public static function update_contact( $session_id, $name, $email, $phone ) {
		global $wpdb;

		return $wpdb->update(
			self::table(),
			array(
				'contact_name'  => $name,
				'contact_email' => $email,
				'contact_phone' => $phone,
			),
			array( 'session_id' => $session_id ),
			array( '%s', '%s', '%s' ),
			array( '%s' )
		);
	}

	/**
	 * Latest known contact details for a session (any prior row that has them).
	 */
	public static function get_session_contact( $session_id ) {
		global $wpdb;

		$empty = array(
			'contact_name'  => null,
			'contact_email' => null,
			'contact_phone' => null,
		);

		if ( '' === (string) $session_id ) {
			return $empty;
		}

		$found = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT contact_name, contact_email, contact_phone FROM ' . self::table() .
				" WHERE session_id = %s AND (
					( contact_name IS NOT NULL AND contact_name <> '' ) OR
					( contact_email IS NOT NULL AND contact_email <> '' ) OR
					( contact_phone IS NOT NULL AND contact_phone <> '' )
				) ORDER BY id DESC LIMIT 1",
				$session_id
			),
			ARRAY_A
		);

		return $found ? $found : $empty;
	}

	/**
	 * Does this session have any logged rows at all?
	 */
	public static function session_exists( $session_id ) {
		global $wpdb;
		return (bool) $wpdb->get_var(
			$wpdb->prepare( 'SELECT 1 FROM ' . self::table() . ' WHERE session_id = %s LIMIT 1', $session_id )
		);
	}

	/**
	 * Highest turn_index used so far for a session.
	 */
	public static function next_turn_index( $session_id ) {
		global $wpdb;
		$max = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT MAX(turn_index) FROM ' . self::table() . ' WHERE session_id = %s',
				$session_id
			)
		);
		return null === $max ? 0 : ( (int) $max + 1 );
	}

	/**
	 * Save a human correction on a single row.
	 */
	public static function save_correction( $log_id, $correction_text, $user_id ) {
		global $wpdb;

		return $wpdb->update(
			self::table(),
			array(
				'marked_incorrect' => 1,
				'correction_text'  => $correction_text,
				'corrected_by'     => (int) $user_id,
				'corrected_at'     => current_time( 'mysql' ),
			),
			array( 'id' => (int) $log_id ),
			array( '%d', '%s', '%d', '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Corrections a human has written, newest first, for feeding back to the model.
	 *
	 * @return array<int,array{question:string,correction_text:string}>
	 */
	public static function get_corrections( $limit = 25 ) {
		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT question, correction_text FROM ' . self::table() .
				" WHERE marked_incorrect = 1 AND correction_text IS NOT NULL AND correction_text <> ''" .
				' ORDER BY corrected_at DESC, id DESC LIMIT %d',
				(int) $limit
			),
			ARRAY_A
		);
		return $rows ? $rows : array();
	}

	/**
	 * Hard-delete every row for a session (data deletion requests).
	 */
	public static function delete_session( $session_id ) {
		global $wpdb;
		return $wpdb->query(
			$wpdb->prepare( 'DELETE FROM ' . self::table() . ' WHERE session_id = %s', $session_id )
		);
	}

	/**
	 * Count rows created since local midnight.
	 */
	public static function count_today() {
		global $wpdb;
		$midnight = gmdate( 'Y-m-d 00:00:00', current_time( 'timestamp' ) );
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM ' . self::table() . ' WHERE created_at >= %s',
				$midnight
			)
		);
	}

	/**
	 * Aggregate token usage since local midnight.
	 */
	public static function tokens_today() {
		global $wpdb;
		$midnight = gmdate( 'Y-m-d 00:00:00', current_time( 'timestamp' ) );
		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT COALESCE(SUM(input_tokens),0) AS in_t,
				        COALESCE(SUM(output_tokens),0) AS out_t,
				        COALESCE(SUM(cache_creation_input_tokens),0) AS cc_t,
				        COALESCE(SUM(cache_read_input_tokens),0) AS cr_t
				 FROM ' . self::table() . ' WHERE created_at >= %s',
				$midnight
			),
			ARRAY_A
		);
		return array(
			'input'          => (int) $row['in_t'],
			'output'         => (int) $row['out_t'],
			'cache_creation' => (int) $row['cc_t'],
			'cache_read'     => (int) $row['cr_t'],
		);
	}

	/**
	 * Estimated spend today in USD, from the configurable per-million prices.
	 * Cache-creation tokens are billed at 1.25x input price (5m ephemeral).
	 */
	public static function estimated_spend_today() {
		$t        = self::tokens_today();
		$p_in     = (float) get_option( 'va_price_in_per_m', 3.0 );
		$p_out    = (float) get_option( 'va_price_out_per_m', 15.0 );
		$p_cread  = (float) get_option( 'va_price_cache_read_per_m', 0.30 );
		return ( $t['input'] * $p_in
			+ $t['cache_creation'] * $p_in * 1.25
			+ $t['cache_read'] * $p_cread
			+ $t['output'] * $p_out ) / 1000000;
	}
}
