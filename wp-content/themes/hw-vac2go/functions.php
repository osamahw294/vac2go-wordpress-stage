<?php
/**
 * HW Kadence Starter — functions.php  (the theme's entry point)
 *
 * WHAT THIS FILE IS
 *   The single file WordPress auto-loads for this theme. It does only three
 *   things: (1) block direct access, (2) define the version constant, and
 *   (3) load the include files in inc/ that hold the real logic.
 *
 * WHEN YOU'D EDIT THIS FILE
 *   Almost never — only when you ADD a new include file to inc/. In that case,
 *   add its path to the $hw_starter_includes list below (see the note there).
 *
 * YOU PROBABLY WANT TO CHANGE:
 *   - Adding a feature?  ->  create/edit a file in inc/ (NOT here), then list
 *                            it in $hw_starter_includes.
 *   - Bumping the theme version?  ->  HW_STARTER_VERSION below + CHANGELOG.md.
 *   DO NOT put feature/business logic directly in this file. Keeping it lean is
 *   the whole point (our old themes had a 600-line functions.php — never again).
 *
 * @package HW_Starter
 * @author  Highwater
 * @link    https://wearehighwater.com/
 * @license GPL-2.0-or-later
 */

// Bail if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme version. Bumped on every release (see CHANGELOG.md).
 * Used for asset cache-busting fallbacks and feature gating.
 */
if ( ! defined( 'HW_STARTER_VERSION' ) ) {
	define( 'HW_STARTER_VERSION', '1.0.0' );
}

/**
 * Explicit, readable include loader.
 *
 * We list every include by name (no blind glob) so load order is obvious
 * and a missing file fails loudly rather than silently changing behavior.
 * Order matters: setup and enqueue first, then feature registrations.
 *
 * TO ADD A NEW INCLUDE FILE:
 *   1. Create it in inc/ (copy the header from an existing one).
 *   2. Add its path to this array, in the position its load order needs.
 * That is the ONLY wiring step — do not require_once files anywhere else.
 */
$hw_starter_includes = array(
	'inc/setup.php',        // Theme supports, textdomain, editor styles, image sizes.
	'inc/enqueue.php',      // Parent + child stylesheet and script enqueues.
	'inc/helpers.php',      // Escaped, i18n-ready helper functions.
	'inc/block-styles.php', // Custom block styles + their CSS.
	'inc/patterns.php',     // Pattern category registration.
);

foreach ( $hw_starter_includes as $hw_starter_file ) {
	$hw_starter_path = get_theme_file_path( $hw_starter_file );

	if ( is_readable( $hw_starter_path ) ) {
		require_once $hw_starter_path;
	} else {
		// Fail loudly for admins in debug mode; never break the front end.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && is_admin() ) {
			trigger_error(
				sprintf(
					/* translators: %s: relative path of the missing include file. */
					esc_html__( 'HW Starter: required include file is missing: %s', 'hw-starter' ),
					esc_html( $hw_starter_file )
				),
				E_USER_WARNING
			);
		}
	}
}

unset( $hw_starter_includes, $hw_starter_file, $hw_starter_path );
