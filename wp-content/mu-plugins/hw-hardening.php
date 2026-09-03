<?php
/*
Plugin Name: HW Fleet Hardening (must-use)
Description: Agency baseline WordPress security hardening. Deployed as a must-use plugin so it cannot be deactivated or edited from wp-admin and survives a partial compromise. Targets the vectors this fleet has actually been hit by (uploaded shells, editor/file-edit backdoors, account/enumeration recon, XML-RPC).
Version: 1.0
Author: Highwater
*/
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* 1. Theme/plugin code editor — BREAK-GLASS (blocks the functions.php / plugin backdoor-injection
   vector: hw_add_user persistence, Code Snippets abuse). Disabled by default fleet-wide. A developer
   opens a temporary, AUTO-EXPIRING window on a single site by setting the option `hw_file_edit_until`
   to a future unix timestamp, e.g.:
       wp option update hw_file_edit_until $(date -d '+2 hours' +%s)
   The editor works while the window is open and auto-re-locks when it expires (fail-safe — no need to
   remember to turn it back off). Close early with:  wp option delete hw_file_edit_until
   Every editor save is logged to the PHP error log regardless of the window state. */
$hw_fe_until = (int) get_option( 'hw_file_edit_until', 0 );
if ( $hw_fe_until <= time() && ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}
add_action( 'wp_ajax_edit-theme-plugin-file', function () {
	$u   = wp_get_current_user();
	$file = isset( $_POST['file'] ) ? sanitize_text_field( wp_unslash( $_POST['file'] ) ) : '?';
	$tgt  = isset( $_POST['theme'] ) ? 'theme:' . sanitize_text_field( wp_unslash( $_POST['theme'] ) )
	      : ( isset( $_POST['plugin'] ) ? 'plugin:' . sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : '?' );
	error_log( sprintf( 'HW-FILE-EDIT: user=%s(#%d) ip=%s saved %s %s',
		$u->user_login, $u->ID, ( $_SERVER['REMOTE_ADDR'] ?? '?' ), $tgt, $file ) );
}, 1 );

/* 2. XML-RPC: Jetpack-aware. Jetpack requires XML-RPC for its WordPress.com link, so only
   hard-disable XML-RPC where Jetpack is NOT active. The pingback method (DDoS/SSRF amplification)
   is stripped everywhere, including on Jetpack sites. Fleet scan 2026-06-08: only deltapaper.com
   runs Jetpack; iwp-client sites (policecanineconsultants/oakeslandscapeservices/propadj/savekidscastle)
   should have that remote-mgmt plugin removed separately. */
add_filter( 'xmlrpc_enabled', function ( $enabled ) {
	return class_exists( 'Jetpack' ) ? $enabled : false;
} );
add_filter( 'xmlrpc_methods', function ( $methods ) {
	unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
	return class_exists( 'Jetpack' ) ? $methods : array();
} );
add_filter( 'wp_headers', function ( $headers ) { unset( $headers['X-Pingback'] ); return $headers; } );
add_filter( 'pings_open', '__return_false', 9999 );

/* 3. Block unauthenticated REST API user enumeration (username leak -> brute force). */
add_filter( 'rest_endpoints', function ( $endpoints ) {
	if ( ! is_user_logged_in() ) {
		foreach ( array( '/wp/v2/users', '/wp/v2/users/(?P<id>[\d]+)' ) as $route ) {
			if ( isset( $endpoints[ $route ] ) ) { unset( $endpoints[ $route ] ); }
		}
	}
	return $endpoints;
} );

/* 4. Block ?author=N enumeration for anonymous visitors. */
add_action( 'init', function () {
	if ( ! is_admin() && ! is_user_logged_in() && isset( $_GET['author'] ) && '' !== $_GET['author'] ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
} );

/* 5. Remove WP version fingerprinting. */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/* 6. Generic login error (no username/password distinction -> no user enumeration via login). */
add_filter( 'login_errors', function () { return 'Invalid login credentials.'; } );

/* 7. Baseline security response headers (HSTS intentionally left to the edge/Cloudflare). */
add_action( 'send_headers', function () {
	if ( headers_sent() ) { return; }
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'X-XSS-Protection: 1; mode=block' );
	// header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains' ); // enable once all-HTTPS is confirmed (or set at Cloudflare)
} );
