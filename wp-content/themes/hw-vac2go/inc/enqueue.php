<?php
/**
 * Front-end asset enqueues.
 *
 * WHAT THIS FILE IS
 *   Loads CSS/JS for the front end: the Kadence parent stylesheet first, then
 *   THIS child's stylesheet (so it always cascades last), then a tiny deferred
 *   child script. Assets are cache-busted with filemtime() so editing a file
 *   invalidates the browser cache automatically — no manual version bump.
 *
 * WHEN YOU'D EDIT THIS FILE
 *   - Adding another CSS or JS file?  ->  add a wp_enqueue_style/script() call
 *     inside hw_starter_enqueue_assets(), reusing hw_starter_asset_version()
 *     for the version arg. Depend on 'hw-starter-child' to load after it.
 *   YOU PROBABLY DON'T need to change the two existing enqueues — just point
 *   assets/css/child.css and assets/js/child.js at your content.
 *
 * @package HW_Starter
 * @link    https://developer.wordpress.org/themes/basics/including-css-javascript/  Enqueuing assets.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return a filemtime-based version string for a theme-relative asset,
 * falling back to the theme version if the file is missing.
 *
 * WHY IT EXISTS: hardcoded ?ver= numbers go stale — devs forget to bump them
 * and clients see cached old CSS. Using the file's modified time means every
 * save busts the cache automatically. Reuse this for any asset you enqueue.
 *
 * @param string $relative_path Path relative to the child theme root (e.g. 'assets/css/child.css').
 * @return string Version string safe for enqueue.
 *
 * @example
 *   wp_enqueue_style( 'my-handle', $uri, array(), hw_starter_asset_version( 'assets/css/extra.css' ) );
 */
function hw_starter_asset_version( $relative_path ) {
	$absolute = get_theme_file_path( $relative_path );

	if ( is_readable( $absolute ) ) {
		$mtime = filemtime( $absolute );

		if ( false !== $mtime ) {
			return (string) $mtime;
		}
	}

	return HW_STARTER_VERSION;
}

/**
 * Enqueue parent and child styles/scripts.
 *
 * WHY IT EXISTS: a child theme is responsible for loading BOTH the parent's
 * stylesheet and its own, in the right order. Load order = cascade order, so
 * the child depends on the parent handle to guarantee it wins.
 * WHAT A DEV CHANGES HERE: add more wp_enqueue_* calls for new assets. To load
 * something only on certain pages, wrap the call in a conditional (e.g.
 * `if ( is_front_page() )`) to keep pages lean.
 *
 * @return void
 */
function hw_starter_enqueue_assets() {
	// 1. Parent (Kadence) stylesheet — lives in the PARENT (template) directory.
	$parent_handle = 'kadence-theme-css';
	wp_enqueue_style(
		$parent_handle,
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme( get_template() )->get( 'Version' )
	);

	// 2. Child stylesheet — depends on the parent so it always cascades after it.
	wp_enqueue_style(
		'hw-starter-child',
		get_stylesheet_directory_uri() . '/assets/css/child.css',
		array( $parent_handle ),
		hw_starter_asset_version( 'assets/css/child.css' )
	);

	// 3. Small, dependency-free child script — deferred, no jQuery.
	wp_enqueue_script(
		'hw-starter-child',
		get_stylesheet_directory_uri() . '/assets/js/child.js',
		array(),
		hw_starter_asset_version( 'assets/js/child.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'hw_starter_enqueue_assets' );
