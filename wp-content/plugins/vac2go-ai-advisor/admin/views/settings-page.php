<?php
/**
 * Settings admin view (Settings API).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Insufficient permissions.' );
}

$capture_mode = VA_Knowledge::get_capture_mode();
$modes        = array(
	'email_only'      => 'Name + email only',
	'phone_only'      => 'Name + phone only',
	'email_or_phone'  => 'Name + (email or phone)',
	'email_and_phone' => 'Name + email + phone',
);

$key_ok = defined( 'VA_ANTHROPIC_KEY' ) && '' !== trim( (string) VA_ANTHROPIC_KEY ) && 'REPLACE_ME' !== VA_ANTHROPIC_KEY;
?>
<div class="wrap va-advisor-wrap">
	<h1>Vac2Go Advisor Settings</h1>

	<div class="notice <?php echo $key_ok ? 'notice-success' : 'notice-warning'; ?> inline">
		<p>
			<strong>API key:</strong>
			<?php if ( $key_ok ) : ?>
				<code>VA_ANTHROPIC_KEY</code> is defined. Advisor is configured.
			<?php else : ?>
				<code>VA_ANTHROPIC_KEY</code> is missing or a placeholder in <code>wp-config.php</code>. The chat returns a graceful "unavailable" message until a real key is set.
			<?php endif; ?>
		</p>
	</div>
	<p><strong>Answer model:</strong> <code><?php echo esc_html( VA_ADVISOR_MODEL ); ?></code> &nbsp; <strong>Judge/classifier model:</strong> <code><?php echo esc_html( VA_ADVISOR_JUDGE_MODEL ); ?></code></p>

	<form method="post" action="options.php">
		<?php settings_fields( 'va_advisor_settings' ); ?>

		<h2>Kill switch</h2>
		<input type="hidden" name="va_enabled" value="0">
		<label>
			<input type="checkbox" name="va_enabled" value="1" <?php checked( 1, (int) get_option( 'va_enabled', 1 ) ); ?>>
			<strong>Advisor enabled.</strong> Unchecking this instantly disables the chat endpoint; the widget shows the contact link instead.
		</label>

		<h2>System prompt (knowledge base)</h2>
		<p class="description">The full knowledge base and guardrails sent to the model on every request. An internal integrity marker is appended automatically at runtime; you do not need to include it here.</p>
		<textarea name="va_system_prompt" rows="24" class="large-text code"><?php echo esc_textarea( get_option( 'va_system_prompt', VA_Knowledge::default_system_prompt() ) ); ?></textarea>

		<h2>Committal / pricing patterns</h2>
		<p class="description">One PCRE regex per line. Any model response matching a line is replaced with the safe fallback and flagged. Invalid regex lines are dropped on save.</p>
		<textarea name="va_banned_patterns" rows="12" class="large-text code"><?php echo esc_textarea( get_option( 'va_banned_patterns', VA_Filter::default_patterns_text() ) ); ?></textarea>

		<h2>Profanity / slur list</h2>
		<p class="description">One word per line, matched on word boundaries, case-insensitive.</p>
		<textarea name="va_profanity_list" rows="6" class="large-text code"><?php echo esc_textarea( get_option( 'va_profanity_list', VA_Filter::default_profanity_text() ) ); ?></textarea>

		<h2>Contact capture mode</h2>
		<select name="va_capture_mode">
			<?php foreach ( $modes as $val => $label ) : ?>
				<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $capture_mode, $val ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>

		<h2>Rate limits and spend</h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="va_rate_ip_minute">Per-IP burst / minute</label></th>
				<td><input name="va_rate_ip_minute" id="va_rate_ip_minute" type="number" min="0" value="<?php echo esc_attr( get_option( 'va_rate_ip_minute', 6 ) ); ?>" class="small-text"> <span class="description">0 = off. Transient counters can overshoot by a few under heavy concurrency (accepted).</span></td>
			</tr>
			<tr>
				<th scope="row"><label for="va_rate_ip_hourly">Per-IP requests / hour</label></th>
				<td><input name="va_rate_ip_hourly" id="va_rate_ip_hourly" type="number" min="0" value="<?php echo esc_attr( get_option( 'va_rate_ip_hourly', 30 ) ); ?>" class="small-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="va_rate_session_turns">Per-session turn cap</label></th>
				<td><input name="va_rate_session_turns" id="va_rate_session_turns" type="number" min="0" value="<?php echo esc_attr( get_option( 'va_rate_session_turns', 40 ) ); ?>" class="small-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="va_global_minute">Global requests / minute (circuit breaker)</label></th>
				<td><input name="va_global_minute" id="va_global_minute" type="number" min="0" value="<?php echo esc_attr( get_option( 'va_global_minute', 60 ) ); ?>" class="small-text"> <span class="description">Exact (atomic DB counter). Tripping enforces a 5-minute cooldown and emails the admin.</span></td>
			</tr>
			<tr>
				<th scope="row"><label for="va_daily_token_ceiling">Daily token ceiling (all token types)</label></th>
				<td><input name="va_daily_token_ceiling" id="va_daily_token_ceiling" type="number" min="0" value="<?php echo esc_attr( get_option( 'va_daily_token_ceiling', 2000000 ) ); ?>" class="regular-text"> <span class="description">80% emails the admin; 100% disables the chat until midnight. 0 = unlimited.</span></td>
			</tr>
			<tr>
				<th scope="row">Prices (USD per million tokens)</th>
				<td>
					Input <input name="va_price_in_per_m" type="number" step="0.01" min="0" value="<?php echo esc_attr( get_option( 'va_price_in_per_m', 3.0 ) ); ?>" class="small-text">
					Output <input name="va_price_out_per_m" type="number" step="0.01" min="0" value="<?php echo esc_attr( get_option( 'va_price_out_per_m', 15.0 ) ); ?>" class="small-text">
					Cache read <input name="va_price_cache_read_per_m" type="number" step="0.01" min="0" value="<?php echo esc_attr( get_option( 'va_price_cache_read_per_m', 0.30 ) ); ?>" class="small-text">
					<p class="description">Used for the estimated-spend figure on the Stats page. Cache creation is billed at 1.25x input.</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="va_admin_email">Alert email</label></th>
				<td><input name="va_admin_email" id="va_admin_email" type="email" value="<?php echo esc_attr( get_option( 'va_admin_email', get_option( 'admin_email' ) ) ); ?>" class="regular-text"> <span class="description">Breaker trips, canary hits, budget warnings, API auth/credit errors (max one email per hour per type).</span></td>
			</tr>
		</table>

		<?php submit_button( 'Save settings' ); ?>
	</form>
</div>
