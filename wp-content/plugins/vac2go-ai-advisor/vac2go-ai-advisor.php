<?php
/**
 * Plugin Name: Vac2Go AI Equipment Advisor
 * Description: Front-end AI equipment advisor for Vac2Go. Recommends a truck category from a plain-language job description and answers GapVax HV-57 spec questions, grounded in a fixed knowledge base with server-side guardrails, an output filter pipeline, full Q&A logging, and a human review/correction workflow.
 * Version: 2.1.3
 * Author: HighWater
 * License: GPL-2.0-or-later
 * Requires PHP: 8.1
 * Text Domain: vac2go-ai-advisor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VA_ADVISOR_VERSION', '2.1.3' );
define( 'VA_ADVISOR_FILE', __FILE__ );
define( 'VA_ADVISOR_DIR', plugin_dir_path( __FILE__ ) );
define( 'VA_ADVISOR_URL', plugin_dir_url( __FILE__ ) );
define( 'VA_ADVISOR_DB_VERSION', '2' );

// Anthropic models + API endpoint (per build spec; do not change models without sign-off).
define( 'VA_ADVISOR_MODEL', 'claude-fable-5-1' );
define( 'VA_ADVISOR_JUDGE_MODEL', 'claude-haiku-4-5-20251001' );
define( 'VA_ADVISOR_API_URL', 'https://api.anthropic.com/v1/messages' );
define( 'VA_ADVISOR_API_HOST', 'api.anthropic.com' );
define( 'VA_ADVISOR_API_TIMEOUT', 45 );

require_once VA_ADVISOR_DIR . 'includes/class-va-text.php';
require_once VA_ADVISOR_DIR . 'includes/class-va-db.php';
require_once VA_ADVISOR_DIR . 'includes/class-va-knowledge.php';
require_once VA_ADVISOR_DIR . 'includes/class-va-filter.php';
require_once VA_ADVISOR_DIR . 'includes/class-va-ratelimit.php';
require_once VA_ADVISOR_DIR . 'includes/class-va-rest.php';
require_once VA_ADVISOR_DIR . 'includes/class-va-stream.php';

if ( is_admin() ) {
	require_once VA_ADVISOR_DIR . 'admin/class-va-admin.php';
}

/**
 * The site's hw-block-slow-api mu-plugin hard-caps EVERY outbound HTTP request to a
 * 3s timeout at priority 99. Anthropic calls take 5-30s. Re-apply http_request_args
 * at a HIGHER priority (100), only for api.anthropic.com, so every other request on
 * the site keeps the 3s protection untouched.
 */
add_filter(
	'http_request_args',
	function ( $args, $url ) {
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( VA_ADVISOR_API_HOST === $host ) {
			$args['timeout'] = VA_ADVISOR_API_TIMEOUT;
		}
		return $args;
	},
	100,
	2
);

// Activation / deactivation.
register_activation_hook( __FILE__, array( 'VA_DB', 'activate' ) );
register_deactivation_hook( __FILE__, 'va_advisor_deactivate' );

/**
 * Nothing is scheduled by this plugin (the daily counters are date-keyed option rows
 * that expire on their own), so deactivation only clears a legacy cron hook that
 * earlier builds registered. Harmless when it was never scheduled.
 */
function va_advisor_deactivate() {
	wp_clear_scheduled_hook( 'va_advisor_daily_reset' );
}

// Ensure the table/options are migrated even when files are updated in place.
add_action(
	'plugins_loaded',
	function () {
		if ( (string) get_option( 'va_db_version' ) !== VA_ADVISOR_DB_VERSION ) {
			VA_DB::activate();
		}
	}
);

// Boot REST + front-end.
add_action( 'rest_api_init', array( 'VA_REST', 'register_routes' ) );
add_action( 'wp_footer', 'va_advisor_print_bootstrap' );

/**
 * Front-end bootstrap (S3 split-widget pattern).
 *
 * The site's hw-delay-js mu-plugins rewrite every normal script tag to
 * type="text/hwdelay" and only execute after the visitor's FIRST interaction, so an
 * enqueued widget script never renders the launcher on an untouched page. The
 * mu-plugin's exemption mechanism is a substring match on the tag's attributes
 * (it skips tags whose attributes contain 'hw-delay-loader'). We therefore print a
 * TINY inline bootstrap whose id carries that marker: it renders just the launcher
 * immediately, and loads the full chat bundle (widget.js + widget.css) only when the
 * visitor clicks. This keeps the heavy code off the critical path for visitors who
 * never open the chat, and the launcher is visible on a cold load with zero
 * interaction.
 *
 * NOTE: no nonce is printed here. Pages are cached for up to 7 days; a baked nonce
 * would go stale (WP nonces rotate in 12-24h). The widget fetches a fresh nonce from
 * GET /vac2go/v1/nonce (no-store) at boot.
 */
function va_advisor_print_bootstrap() {
	if ( is_admin() ) {
		return;
	}

	$cfg = array(
		'restUrl'     => esc_url_raw( rest_url( 'vac2go/v1' ) ),
		'streaming'   => VA_Stream::is_available(),
		'captureMode' => VA_Knowledge::get_capture_mode(),
		'contactUrl'  => 'https://vac2go.com/contact/',
		'cssUrl'      => VA_ADVISOR_URL . 'assets/widget.css?ver=' . VA_ADVISOR_VERSION,
		'jsUrl'       => VA_ADVISOR_URL . 'assets/widget.js?ver=' . VA_ADVISOR_VERSION,
	);
	?>
	<style id="va-boot-css">
	#va-boot-launcher{position:fixed;right:20px;bottom:20px;z-index:999999;display:inline-flex;align-items:center;gap:8px;padding:12px 18px;background:#e01f30;color:#fff;border:none;border-radius:4px;box-shadow:0 6px 20px rgba(0,0,0,.22);cursor:pointer;font-family:Poppins,"Open Sans",Helvetica,Arial,sans-serif;font-size:15px;font-weight:600}
	#va-boot-launcher:hover{background:#b8121f}
	#va-boot-launcher .va-boot-dot{width:9px;height:9px;background:#57e389;border-radius:50%}
	</style>
	<script id="hw-delay-loader-va-boot">
	/* Exempt from hw-delay-js via the 'hw-delay-loader' attribute marker (see mu-plugin). */
	(function () {
		if (document.getElementById('va-boot-launcher') || window.vaAdvisorBooted) { return; }
		window.vaAdvisor = <?php echo wp_json_encode( $cfg ); ?>;
		window.vaAdvisorBootTime = Date.now();
		var b = document.createElement('button');
		b.id = 'va-boot-launcher';
		b.type = 'button';
		b.setAttribute('aria-label', 'Open equipment advisor chat');
		b.setAttribute('aria-expanded', 'false');
		b.innerHTML = '<span class="va-boot-dot"></span>Ask the Equipment Advisor';
		b.addEventListener('click', function () {
			if (window.vaAdvisorBooted) { return; }
			b.disabled = true;
			window.vaAdvisorAutoOpen = true;
			var l = document.createElement('link');
			l.rel = 'stylesheet'; l.id = 'va-advisor-css'; l.href = window.vaAdvisor.cssUrl;
			document.head.appendChild(l);
			var s = document.createElement('script');
			s.src = window.vaAdvisor.jsUrl; s.async = true;
			document.body.appendChild(s);
		});
		function add() { if (document.body) { document.body.appendChild(b); } }
		if (document.body) { add(); } else { document.addEventListener('DOMContentLoaded', add); }
	})();
	</script>
	<?php
}

/**
 * Admin notice if the API key constant is missing.
 */
add_action(
	'admin_notices',
	function () {
		if ( ! defined( 'VA_ANTHROPIC_KEY' ) || '' === trim( (string) VA_ANTHROPIC_KEY ) || 'REPLACE_ME' === VA_ANTHROPIC_KEY ) {
			echo '<div class="notice notice-warning"><p><strong>Vac2Go Advisor:</strong> set <code>VA_ANTHROPIC_KEY</code> in <code>wp-config.php</code> to a real Anthropic API key to activate the chat. The advisor endpoint is currently returning a graceful "unavailable" response.</p></div>';
		}
	}
);
