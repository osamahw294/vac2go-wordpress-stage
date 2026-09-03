<?php
/**
 * Rate limiting + circuit breaker + spend ceiling.
 *
 * Layers (each independently stops abuse):
 *  - per-IP per-minute burst and per-hour limits (transients; non-atomic, so a race
 *    can overshoot by a few requests per window; accepted, documented)
 *  - global per-minute and per-day circuit breaker (single option row updated with an
 *    atomic UPDATE, exact)
 *  - daily token ceiling with USD estimate (from real per-turn usage in the log table)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VA_RateLimit {

	/**
	 * Keyed IP hash: HMAC-SHA256 with the site's AUTH salt. An unsalted SHA-256 of an
	 * IPv4 is reversible with a 4-billion-entry table; HMAC with a secret key is not.
	 */
	public static function ip_hash() {
		return hash_hmac( 'sha256', self::client_ip(), wp_salt( 'auth' ) );
	}

	/**
	 * Best-effort client IP.
	 */
	public static function client_ip() {
		$candidates = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
		foreach ( $candidates as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$val = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
				$val = trim( explode( ',', $val )[0] );
				if ( filter_var( $val, FILTER_VALIDATE_IP ) ) {
					return $val;
				}
			}
		}
		return '0.0.0.0';
	}

	/**
	 * Per-IP checks: minute burst then hourly. Increments on success. Returns true if
	 * allowed. Transient counters are not atomic; concurrent requests can overshoot a
	 * window by a few requests. Accepted for this layer; the global breaker is exact.
	 */
	public static function check_ip( $ip_hash ) {
		$minute_limit = (int) get_option( 'va_rate_ip_minute', 6 );
		$hour_limit   = (int) get_option( 'va_rate_ip_hourly', 30 );

		if ( $minute_limit > 0 ) {
			$mkey  = 'va_rl_ipm_' . $ip_hash;
			$count = (int) get_transient( $mkey );
			if ( $count >= $minute_limit ) {
				return false;
			}
			set_transient( $mkey, $count + 1, MINUTE_IN_SECONDS );
		}

		if ( $hour_limit > 0 ) {
			$hkey  = 'va_rl_iph_' . $ip_hash;
			$count = (int) get_transient( $hkey );
			if ( $count >= $hour_limit ) {
				return false;
			}
			set_transient( $hkey, $count + 1, HOUR_IN_SECONDS );
		}

		return true;
	}

	/**
	 * Per-session turn cap.
	 */
	public static function check_session( $session_id ) {
		$limit = (int) get_option( 'va_rate_session_turns', 40 );
		if ( $limit <= 0 ) {
			return true;
		}
		$key   = 'va_rl_sess_' . md5( $session_id );
		$count = (int) get_transient( $key );
		if ( $count >= $limit ) {
			return false;
		}
		set_transient( $key, $count + 1, DAY_IN_SECONDS );
		return true;
	}

	/**
	 * Global circuit breaker: site-wide per-minute counter kept in a single option row
	 * and incremented with an atomic UPDATE so it is exact under concurrency.
	 * Returns true if under threshold.
	 */
	public static function check_global() {
		global $wpdb;

		$limit = (int) get_option( 'va_global_minute', 60 );
		if ( $limit <= 0 ) {
			return true;
		}

		// A manual trip (or a previous breach) enforces a cooldown window.
		$tripped_until = (int) get_option( 'va_breaker_until', 0 );
		if ( $tripped_until > time() ) {
			return false;
		}

		$slot = 'va_gm_' . gmdate( 'YmdHi' ); // per-minute slot key
		// Ensure the row exists, then increment atomically.
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, '0', 'no')
				 ON DUPLICATE KEY UPDATE option_name = option_name",
				$slot
			)
		);
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->options} SET option_value = option_value + 1 WHERE option_name = %s",
				$slot
			)
		);
		$count = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $slot )
		);

		// Opportunistic cleanup of old slot rows (keep the table tidy).
		if ( 1 === wp_rand( 1, 20 ) ) {
			$wpdb->query(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE 'va\\_gm\\_%' AND option_name < 'va_gm_" . gmdate( 'YmdHi', time() - 600 ) . "'"
			);
		}

		if ( $count > $limit ) {
			self::trip_breaker( 'global_minute_exceeded (' . $count . '/' . $limit . ')', 5 * MINUTE_IN_SECONDS );
			return false;
		}

		return self::check_global_daily();
	}

	/**
	 * Site-wide per-DAY request threshold (S6.3). The token ceiling bounds spend, but
	 * a flood of cheap prescreen-only requests never reaches the model and so never
	 * moves the token counter; this layer stops that separately. Same atomic-option
	 * counter as the per-minute slot, keyed by UTC date.
	 */
	private static function check_global_daily() {
		global $wpdb;

		$limit = (int) get_option( 'va_global_daily', 5000 );
		if ( $limit <= 0 ) {
			return true;
		}

		$slot = 'va_gd_' . gmdate( 'Ymd' );
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, '0', 'no')
				 ON DUPLICATE KEY UPDATE option_name = option_name",
				$slot
			)
		);
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->options} SET option_value = option_value + 1 WHERE option_name = %s",
				$slot
			)
		);
		$count = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $slot )
		);

		// Drop day-slot rows older than a week.
		if ( 1 === wp_rand( 1, 50 ) ) {
			$wpdb->query(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE 'va\\_gd\\_%' AND option_name < 'va_gd_" . gmdate( 'Ymd', time() - WEEK_IN_SECONDS ) . "'"
			);
		}

		if ( $count > $limit ) {
			// Cool down until the next UTC midnight, so the day's flood stays stopped.
			$midnight = strtotime( 'tomorrow midnight UTC' );
			self::trip_breaker( 'global_daily_exceeded (' . $count . '/' . $limit . ')', max( 60, $midnight - time() ) );
			return false;
		}
		return true;
	}

	/**
	 * Trip the breaker for a cooldown window, log it, and alert the admin.
	 */
	public static function trip_breaker( $why, $cooldown_seconds ) {
		update_option( 'va_breaker_until', time() + $cooldown_seconds, false );
		update_option( 'va_breaker_last_reason', $why . ' at ' . current_time( 'mysql' ), false );
		error_log( '[vac2go-ai-advisor] circuit breaker tripped: ' . $why );
		self::alert( 'breaker', 'Vac2Go Advisor: circuit breaker tripped', 'Reason: ' . $why );
	}

	/**
	 * Daily token ceiling from real usage. Returns 'ok', 'warn' (>=80%), or 'over'.
	 * Sends the 80% email once per day, trips availability at 100%.
	 */
	public static function daily_budget_state() {
		$ceiling = (int) get_option( 'va_daily_token_ceiling', 2000000 );
		if ( $ceiling <= 0 ) {
			return 'ok';
		}
		$t     = VA_DB::tokens_today();
		$total = $t['input'] + $t['output'] + $t['cache_creation'] + $t['cache_read'];

		if ( $total >= $ceiling ) {
			self::alert( 'budget100', 'Vac2Go Advisor: daily token ceiling reached', 'Total tokens today: ' . $total . ' / ' . $ceiling . '. Estimated spend: $' . number_format( VA_DB::estimated_spend_today(), 2 ) );
			return 'over';
		}
		if ( $total >= 0.8 * $ceiling ) {
			self::alert( 'budget80', 'Vac2Go Advisor: 80% of daily token budget used', 'Total tokens today: ' . $total . ' / ' . $ceiling . '. Estimated spend: $' . number_format( VA_DB::estimated_spend_today(), 2 ) );
			return 'warn';
		}
		return 'ok';
	}

	/**
	 * Email the admin, at most once per hour per alert type.
	 */
	public static function alert( $type, $subject, $body ) {
		$key = 'va_alert_' . $type;
		if ( get_transient( $key ) ) {
			return;
		}
		set_transient( $key, 1, HOUR_IN_SECONDS );
		$to = get_option( 'va_admin_email', get_option( 'admin_email' ) );
		if ( $to ) {
			wp_mail( $to, $subject, $body . "\n\nSite: " . home_url() . "\nTime: " . current_time( 'mysql' ) );
		}
	}
}
