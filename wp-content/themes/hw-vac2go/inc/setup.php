<?php
/**
 * Theme setup.
 *
 * WHAT THIS FILE IS
 *   Registers the small setup extras that the Kadence parent does NOT already
 *   provide: the child text domain, the editor stylesheet link, and one custom
 *   image size. Kadence already handles title-tag, post-thumbnails, HTML5
 *   markup, responsive embeds, block styles, wide/full alignment, the editor
 *   color palette, etc. — do not duplicate those here.
 *
 * WHEN YOU'D EDIT THIS FILE
 *   - Need a new cropped image size for the client?  ->  add_image_size() in
 *     hw_starter_setup(), then expose it in hw_starter_custom_image_sizes().
 *   - Need a theme support Kadence lacks?  ->  add it inside hw_starter_setup().
 *   YOU PROBABLY WON'T need to touch the text domain / editor-style lines.
 *
 * @package HW_Starter
 * @link    https://www.kadencewp.com/help-center/  Kadence help center.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the child theme text domain and register light setup extras.
 *
 * WHY IT EXISTS: a child theme must load its OWN translations and tell the
 * editor which stylesheet to preview with; the parent does not do this for us.
 * WHAT A DEV CHANGES HERE: add_theme_support() calls Kadence lacks, and custom
 * image sizes. Change the 720x480 crop below to fit the client's imagery.
 *
 * @return void
 */
function hw_starter_setup() {
	// Translations for this child theme. Parent handles its own.
	load_child_theme_textdomain(
		'hw-starter',
		get_stylesheet_directory() . '/languages'
	);

	/*
	 * Make the block editor render content close to the front end by loading
	 * the child stylesheet into the editor. add_editor_style() resolves paths
	 * relative to the child theme root, so a relative path is correct here.
	 */
	add_editor_style( 'assets/css/child.css' );

	// A house image size for pattern/feature imagery (soft-cropped 3:2).
	add_image_size( 'hw-starter-feature', 720, 480, true );
}
add_action( 'after_setup_theme', 'hw_starter_setup' );

/**
 * Expose the custom image size in the editor's image size chooser.
 *
 * WHY IT EXISTS: add_image_size() alone crops the file but hides the size from
 * the block editor's "Image size" dropdown. This filter surfaces it so editors
 * can actually pick "HW Feature (3:2)".
 * WHAT A DEV CHANGES HERE: keep this in sync with the add_image_size() call
 * above — one entry per custom size, same slug.
 *
 * @param array $sizes Existing selectable image sizes.
 * @return array Filtered sizes.
 */
function hw_starter_custom_image_sizes( $sizes ) {
	$sizes['hw-starter-feature'] = esc_html__( 'HW Feature (3:2)', 'hw-starter' );

	return $sizes;
}
add_filter( 'image_size_names_choose', 'hw_starter_custom_image_sizes' );
