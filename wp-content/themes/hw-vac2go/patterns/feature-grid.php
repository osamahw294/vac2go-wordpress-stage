<?php
/**
 * Title: Feature Grid — 3 Columns
 * Slug: hw-starter/feature-grid
 * Categories: hw
 * Description: A three-column grid of services or features, each with an icon-style heading and short description.
 * Keywords: features, services, grid, columns
 * Viewport Width: 1280
 *
 * @package HW_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * DEV NOTES — Feature Grid pattern (3 columns)
 * --------------------------------------------
 * HOW TO USE: insert "Feature Grid — 3 Columns" (Patterns -> Highwater), then
 *   edit the card titles/text in the Site Editor. Edit THIS file only to change
 *   the default copy, or to change the NUMBER of cards (add/remove entries in
 *   the $hw_starter_features array below — the loop renders one column each).
 * PLACEHOLDERS TO REPLACE: the section heading and every card title/text.
 * BRAND TOKENS THIS RELIES ON (from theme.json — restyle there, not here):
 *   - each column uses the "Card" block style (is-style-hw-card) from
 *     inc/block-styles.php, which pulls neutral-100 / base-white tokens
 *   - spacing: 40 / 50 / 60 presets;  font sizes: x-large, large
 *
 * Cards are built from a small data array so the markup stays DRY and every
 * string is individually translatable.
 */
$hw_starter_features = array(
	array(
		'title' => esc_html_x( 'First service', 'Feature grid card title', 'hw-starter' ),
		'text'  => esc_html_x( 'A short benefit-led description of this service. Replace per client.', 'Feature grid card text', 'hw-starter' ),
	),
	array(
		'title' => esc_html_x( 'Second service', 'Feature grid card title', 'hw-starter' ),
		'text'  => esc_html_x( 'A short benefit-led description of this service. Replace per client.', 'Feature grid card text', 'hw-starter' ),
	),
	array(
		'title' => esc_html_x( 'Third service', 'Feature grid card title', 'hw-starter' ),
		'text'  => esc_html_x( 'A short benefit-led description of this service. Replace per client.', 'Feature grid card text', 'hw-starter' ),
	),
);
?>
<!-- wp:group {"tagName":"section","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:heading {"textAlign":"center","level":2,"fontSize":"x-large"} -->
<h2 class="wp-block-heading has-text-align-center has-x-large-font-size"><?php echo esc_html_x( 'What we do', 'Feature grid section heading', 'hw-starter' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"var:preset|spacing|40","left":"var:preset|spacing|40"},"margin":{"top":"var:preset|spacing|50"}}}} -->
<div class="wp-block-columns" style="margin-top:var(--wp--preset--spacing--50)">
	<?php foreach ( $hw_starter_features as $hw_starter_feature ) : ?>
	<!-- wp:column {"className":"is-style-hw-card"} -->
	<div class="wp-block-column is-style-hw-card"><!-- wp:heading {"level":3,"fontSize":"large"} -->
	<h3 class="wp-block-heading has-large-font-size"><?php echo $hw_starter_feature['title']; // Already escaped above. ?></h3>
	<!-- /wp:heading -->

	<!-- wp:paragraph -->
	<p><?php echo $hw_starter_feature['text']; // Already escaped above. ?></p>
	<!-- /wp:paragraph --></div>
	<!-- /wp:column -->
	<?php endforeach; ?>
</div>
<!-- /wp:columns --></section>
<!-- /wp:group -->
