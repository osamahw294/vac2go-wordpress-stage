<?php
/**
 * Block pattern registration.
 *
 * WHAT THIS FILE IS
 *   Registers the "Highwater" CATEGORY that our patterns are grouped under in
 *   the block inserter. The patterns THEMSELVES are NOT registered here — every
 *   .php file in the patterns/ folder is auto-discovered by WordPress (6.0+)
 *   via its header comment (Title / Slug / Categories). That is why adding a
 *   pattern needs no code changes here.
 *
 * WHEN YOU'D EDIT THIS FILE
 *   - Almost never. Only if you want a SECOND category (e.g. "hw-landing") — add
 *     another register_block_pattern_category() call, then reference its slug in
 *     the `Categories:` header of the relevant pattern files.
 *   TO ADD A PATTERN you edit patterns/, not this file.
 *
 * @package HW_Starter
 * @link    https://developer.wordpress.org/themes/features/block-patterns/  Block Patterns.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the "Highwater" pattern category.
 *
 * WHY IT EXISTS: without a registered category, patterns tagged `Categories: hw`
 * would have nowhere to appear in the inserter. This creates that bucket.
 * WHAT A DEV CHANGES HERE: the `hw` slug must match the `Categories:` header in
 * patterns/*.php. Change the label/description freely; change the slug only if
 * you also update every pattern file.
 *
 * @return void
 */
function hw_starter_register_pattern_category() {
	if ( ! function_exists( 'register_block_pattern_category' ) ) {
		return;
	}

	register_block_pattern_category(
		'hw',
		array(
			'label'       => _x( 'Highwater', 'Block pattern category', 'hw-starter' ),
			'description' => __( 'Reusable Highwater house patterns.', 'hw-starter' ),
		)
	);
}
add_action( 'init', 'hw_starter_register_pattern_category' );
