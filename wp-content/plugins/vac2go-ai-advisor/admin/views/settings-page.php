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
	'email_only'      => 'Email',
	'phone_only'      => 'Phone',
	'email_or_phone'  => 'Email or phone',
	'email_and_phone' => 'Email and phone',
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

		<h2>Streaming</h2>
		<input type="hidden" name="va_streaming" value="0">
		<label>
			<input type="checkbox" name="va_streaming" value="1" <?php checked( 1, (int) get_option( 'va_streaming', 1 ) ); ?>>
			<strong>Stream replies token by token.</strong> The answer appears as it is written instead of arriving all at once.
		</label>
		<p class="description">
			Text is still held back and passed through the deterministic filter stages before any of it is shown, so nothing unfiltered can reach a customer mid-stream. Turn this off if the host buffers the response (the widget then falls back to the buffered endpoint with a typing indicator).
			<?php if ( ! function_exists( 'curl_init' ) ) : ?>
				<br><strong>cURL is not available on this server, so streaming cannot run regardless of this setting.</strong>
			<?php endif; ?>
		</p>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="va_stream_pad">Flush padding (bytes per message)</label></th>
				<td>
					<input name="va_stream_pad" id="va_stream_pad" type="number" min="0" max="65536" step="256" value="<?php echo esc_attr( get_option( 'va_stream_pad', 4096 ) ); ?>" class="small-text">
					<span class="description">
						Some hosts ignore <code>X-Accel-Buffering</code> and release the response only once their own buffer fills, which collapses streaming into one lump at the end. Padding each message past that threshold forces a real flush. <strong>4096</strong> is correct for this server (measured: it buffers at roughly 4KB). Raise it if streaming still arrives in bursts; set <strong>0</strong> on a host that streams correctly by itself, to save the bandwidth.
					</span>
				</td>
			</tr>
		</table>

		<h2>Answer length</h2>
		<?php $len = VA_Knowledge::get_answer_length(); ?>
		<select name="va_answer_length">
			<option value="short" <?php selected( $len, 'short' ); ?>>Short, 2 to 4 sentences (recommended)</option>
			<option value="medium" <?php selected( $len, 'medium' ); ?>>Medium, a short paragraph or two</option>
			<option value="long" <?php selected( $len, 'long' ); ?>>Long, a few paragraphs</option>
		</select>
		<p class="description">
			Shorter answers also arrive faster, since the customer is waiting on the words being written. This is applied on top of whatever the system prompt below says, so you do not need to edit the prompt to change it. Guardrails are unaffected: the caveat sentence, the "I don't know" rule and the refusal scripts always apply in full.
			Length comes from the rules above, not from the token cap. The cap (<strong><?php echo (int) VA_Knowledge::max_tokens(); ?></strong> tokens) is only a runaway guard, and has to be generous because the model's own reasoning is drawn from the same budget before it writes anything.
		</p>

		<h2>System prompt (knowledge base)</h2>
		<p class="description">The full knowledge base and guardrails sent to the model on every request. An internal integrity marker is appended automatically at runtime; you do not need to include it here.</p>
		<textarea name="va_system_prompt" rows="24" class="large-text code"><?php echo esc_textarea( get_option( 'va_system_prompt', VA_Knowledge::default_system_prompt() ) ); ?></textarea>

		<h2>Committal / pricing patterns</h2>
		<p class="description">One PCRE regex per line. Any model response matching a line is replaced with the safe fallback and flagged. Invalid regex lines are dropped on save.</p>
		<textarea name="va_banned_patterns" rows="12" class="large-text code"><?php echo esc_textarea( get_option( 'va_banned_patterns', VA_Filter::default_patterns_text() ) ); ?></textarea>

		<h2>Profanity / slur list</h2>
		<p class="description">One word per line, matched on word boundaries, case-insensitive.</p>
		<textarea name="va_profanity_list" rows="6" class="large-text code"><?php echo esc_textarea( get_option( 'va_profanity_list', VA_Filter::default_profanity_text() ) ); ?></textarea>

		<h2>Review feedback loop</h2>
		<input type="hidden" name="va_corrections_in_prompt" value="0">
		<label>
			<input type="checkbox" name="va_corrections_in_prompt" value="1" <?php checked( 1, (int) get_option( 'va_corrections_in_prompt', 1 ) ); ?>>
			<strong>Teach the advisor from corrections.</strong> Answers you mark incorrect in the Review Queue, together with what you wrote they should have said, are sent to the model as authoritative guidance.
		</label>
		<p class="description">
			The 25 most recent corrections are included, up to roughly 6,000 characters, newest first. They are sent as a separate block after the system prompt, so prompt caching is unaffected. Corrections can never override the guardrails, so one cannot be used to authorise a price or an availability commitment.
			<?php
			$correction_count = class_exists( 'VA_DB' ) ? count( VA_DB::get_corrections( 25 ) ) : 0;
			?>
			<br><strong><?php echo (int) $correction_count; ?></strong> correction<?php echo 1 === $correction_count ? '' : 's'; ?> currently in use.
		</p>

		<h2>Lead notifications</h2>
		<input type="hidden" name="va_notify_leads" value="0">
		<label>
			<input type="checkbox" name="va_notify_leads" value="1" <?php checked( 1, (int) get_option( 'va_notify_leads', 1 ) ); ?>>
			<strong>Email me when a visitor leaves their details.</strong> Sent to the alert address below, with the full conversation, so a rep can follow up without watching the queue.
		</label>

		<h2>Contact capture</h2>
		<p class="description">What the advisor asks for once a visitor shows real interest. It asks once per conversation and never blocks the chat.</p>
		<select name="va_capture_mode">
			<?php foreach ( $modes as $val => $label ) : ?>
				<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $capture_mode, $val ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<p>
			<input type="hidden" name="va_capture_name" value="0">
			<label>
				<input type="checkbox" name="va_capture_name" value="1" <?php checked( 1, (int) get_option( 'va_capture_name', 1 ) ); ?>>
				Also ask for a name
			</label>
		</p>

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
				<th scope="row"><label for="va_global_daily">Global requests / day (circuit breaker)</label></th>
				<td><input name="va_global_daily" id="va_global_daily" type="number" min="0" value="<?php echo esc_attr( get_option( 'va_global_daily', 5000 ) ); ?>" class="regular-text"> <span class="description">Counts every request, including prescreen-only ones that never reach the model and so never move the token counter. Tripping holds until UTC midnight. 0 = off.</span></td>
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
