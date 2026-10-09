<?php
/**
 * Rate limiting + circuit breaker + spend ceiling.
 *
 * Layers (each independently stops abuse):
 *  - per-IP per-minute burst and per-hour limits (transients; non-atomic, so a race
 *    can overshoot by a few requests per window; accepted, documented), escalating
 *    to a 15-minute lock for an IP that keeps sending while limited
 *  - global per-minute and per-day circuit breaker (single option row updated with an
 *    atomic UPDATE, exact)
 *  - daily spend ceiling in USD (from real per-turn usage in the log table)
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

	/** Requests an IP may send while already limited before the longer lock applies. */
	const STRIKES_BEFORE_LOCK = 5;

	/** How long the escalated lock lasts. */
	const LOCK_SECONDS = 900; // 15 minutes

	/**
	 * Per-IP checks: the escalated lock, then minute burst, then hourly. Increments on
	 * success. Returns 0 when allowed, otherwise the seconds until this IP may send
	 * again, so the visitor can be told how long to wait.
	 *
	 * Transient counters are not atomic; concurrent requests can overshoot a window by
	 * a few requests. Accepted for this layer; the global breaker is exact.
	 *
	 * Each counter stores its own reset time. A transient's expiry cannot be read back
	 * portably (with an object cache it is not in the options table at all), and
	 * without it there is no honest "try again in N minutes".
	 */
	public static function ip_wait( $ip_hash ) {
		$lock_until = (int) get_transient( 'va_rl_lock_' . $ip_hash );
		if ( $lock_until > time() ) {
			return $lock_until - time();
		}

		$windows = array(
			'va_rl_ipm_' => array( (int) get_option( 'va_rate_ip_minute', 6 ), MINUTE_IN_SECONDS ),
			'va_rl_iph_' => array( (int) get_option( 'va_rate_ip_hourly', 30 ), HOUR_IN_SECONDS ),
		);

		foreach ( $windows as $prefix => $w ) {
			list( $limit, $length ) = $w;
			if ( $limit <= 0 ) {
				continue;
			}
			$key     = $prefix . $ip_hash;
			$counter = self::read_counter( $key, $length );
			if ( $counter['n'] >= $limit ) {
				return self::strike( $ip_hash, max( 1, $counter['reset'] - time() ) );
			}
			$counter['n']++;
			set_transient( $key, $counter, max( 1, $counter['reset'] - time() ) );
		}

		return 0;
	}

	/**
	 * A window counter as array( n, reset ). Older builds stored a bare integer; treat
	 * those as a window that started now, which can only err toward a longer wait.
	 */
	private static function read_counter( $key, $length ) {
		$raw = get_transient( $key );
		if ( is_array( $raw ) && isset( $raw['n'], $raw['reset'] ) && (int) $raw['reset'] > time() ) {
			return array( 'n' => (int) $raw['n'], 'reset' => (int) $raw['reset'] );
		}
		return array( 'n' => is_numeric( $raw ) ? (int) $raw : 0, 'reset' => time() + $length );
	}

	/**
	 * Count a request sent while already limited. A person waits; a script keeps
	 * firing. After STRIKES_BEFORE_LOCK of those the IP is locked for LOCK_SECONDS,
	 * however short its original window was.
	 *
	 * @return int Seconds the caller must wait.
	 */
	private static function strike( $ip_hash, $wait ) {
		$key     = 'va_rl_strk_' . $ip_hash;
		$strikes = (int) get_transient( $key ) + 1;
		if ( $strikes >= self::STRIKES_BEFORE_LOCK ) {
			delete_transient( $key );
			set_transient( 'va_rl_lock_' . $ip_hash, time() + self::LOCK_SECONDS, self::LOCK_SECONDS );
			return self::LOCK_SECONDS;
		}
		set_transient( $key, $strikes, HOUR_IN_SECONDS );
		return $wait;
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
	 * Daily spend ceiling in US dollars, from real per-turn usage priced at the
	 * configured rates. Returns 'ok', 'warn' (>=80%) or 'over'; emails at 80% and 100%
	 * (once per hour per level), and 'over' makes the chat unavailable until midnight.
	 *
	 * Dollars rather than tokens: a token ceiling counts a cache read, which costs
	 * 0.025x an input token, the same as an output token, which costs 5x. With the
	 * Phase 2 knowledge base most tokens are cache reads, so a token ceiling would
	 * trip on a few dozen cheap conversations.
	 */
	public static function daily_budget_state() {
		$ceiling = (float) get_option( 'va_daily_spend_usd', 25 );
		$spend   = VA_DB::estimated_spend_today();
		$state   = self::budget_state( $spend, $ceiling );

		$line = 'Estimated spend today: $' . number_format( $spend, 2 ) . ' of a $' . number_format( $ceiling, 2 ) . ' daily ceiling.';
		if ( 'over' === $state ) {
			self::alert( 'budget100', 'Vac2Go Advisor: daily spend ceiling reached', $line . ' The chat is unavailable until midnight.' );
		} elseif ( 'warn' === $state ) {
			self::alert( 'budget80', 'Vac2Go Advisor: 80% of the daily spend ceiling used', $line );
		}
		return $state;
	}

	/**
	 * 'ok' | 'warn' (>=80%) | 'over' (>=100%) for a spend against a ceiling; a
	 * ceiling of 0 or less means unlimited.
	 */
	public static function budget_state( $spend, $ceiling ) {
		if ( $ceiling <= 0 ) {
			return 'ok';
		}
		if ( $spend >= $ceiling ) {
			return 'over';
		}
		if ( $spend >= 0.8 * $ceiling ) {
			return 'warn';
		}
		return 'ok';
	}

	/**
	 * Hourly spike alert. The daily ceiling only speaks up at 80%, which on a quiet
	 * day can be hours after something started burning tokens. This catches a sudden
	 * surge (a bot, a loop, a viral page) within the hour. Alert only; never blocks.
	 */
	public static function check_hourly_spike() {
		$threshold = (float) get_option( 'va_hourly_spend_alert_usd', 5 );
		$usage     = VA_DB::usage_last_hour();
		$spend     = VA_DB::spend_for( $usage );
		if ( self::spike_due( $spend, $threshold ) ) {
			$tokens = array_sum( $usage );
			self::alert( 'spike', 'Vac2Go Advisor: usage spike', 'Estimated spend in the last 60 minutes: $' . number_format( $spend, 2 ) . ' (alert at $' . number_format( $threshold, 2 ) . '; ' . number_format( $tokens ) . ' tokens). Check Stats and the Review Queue for unusual traffic.' );
		}
	}

	/**
	 * Whether an hour's spend calls for the spike alert. Dollars, like the daily
	 * ceiling: counted in tokens, the ~12,000 cached tokens every Phase 2 turn reads
	 * tripped a 400,000-token alert on about 30 ordinary questions. 0 = off.
	 */
	public static function spike_due( $spend, $threshold ) {
		return $threshold > 0 && $spend >= $threshold;
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
