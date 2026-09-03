<?php
/**
 * Custom block styles.
 *
 * WHAT THIS FILE IS
 *   Registers "style variations" that appear in the block editor's Styles tab
 *   for a block (e.g. a "Card" look for Group). Registering here only creates
 *   the OPTION and its CSS class; the actual look is CSS in
 *   assets/css/child.css under the "Block styles" section — the two must stay
 *   paired.
 *
 * WHEN YOU'D EDIT THIS FILE
 *   - Adding a new variation?  ->  register_block_style() here, THEN add a
 *     `.is-style-<name>` rule in child.css. Both steps are required.
 *   - Removing one?  ->  delete both the registration and its CSS.
 *   The `name` becomes the CSS class `is-style-<name>`; the `label` is what
 *   editors see. Keep names prefixed `hw-` to avoid clashing with Kadence/core.
 *
 * @package HW_Starter
 * @link    https://developer.wordpress.org/reference/functions/register_block_style/  register_block_style().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the child theme's block style variations.
 *
 * WHY IT EXISTS: gives editors a couple of on-brand looks (Outline Pill button,
 * Card group/column, Rounded image) as one-click options instead of custom CSS
 * per page. WHAT A DEV CHANGES HERE: add/remove register_block_style() calls;
 * remember each `name` needs a matching `.is-style-<name>` rule in child.css.
 *
 * @return void
 */
function hw_starter_register_block_styles() {
	if ( ! function_exists( 'register_block_style' ) ) {
		return;
	}

	register_block_style(
		'core/button',
		array(
			'name'  => 'hw-outline-pill',
			'label' => __( 'Outline Pill', 'hw-starter' ),
		)
	);

	register_block_style(
		'core/group',
		array(
			'name'  => 'hw-card',
			'label' => __( 'Card', 'hw-starter' ),
		)
	);

	register_block_style(
		'core/image',
		array(
			'name'  => 'hw-rounded',
			'label' => __( 'Rounded', 'hw-starter' ),
		)
	);
}
add_action( 'init', 'hw_starter_register_block_styles' );
