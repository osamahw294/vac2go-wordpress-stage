<?php
/**
 * Title: Hero — Heading, Subtext, CTA
 * Slug: hw-starter/hero
 * Categories: hw
 * Description: Centered hero with a display heading, supporting text, and a call-to-action button.
 * Keywords: hero, banner, intro, cta
 * Viewport Width: 1280
 *
 * @package HW_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * DEV NOTES — Hero pattern
 * ------------------------
 * HOW TO USE: insert "Hero — Heading, Subtext, CTA" from the block inserter
 *   (Patterns tab -> Highwater), then edit the copy and the button links right
 *   in the Site Editor. Edit THIS file only to change the DEFAULT starting copy
 *   for future inserts.
 * PLACEHOLDERS TO REPLACE: the heading, subtext, and both button `href="#"`.
 * BRAND TOKENS THIS RELIES ON (from theme.json — restyle there, not here):
 *   - color: brand-primary (primary button bg), base-white (button text),
 *            neutral-100 (section background)
 *   - the secondary button uses the "Outline Pill" block style
 *     (is-style-hw-outline-pill) registered in inc/block-styles.php
 *   - spacing: 40 / 70 presets;  font sizes: huge, large
 */
?>
<!-- wp:group {"tagName":"section","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"backgroundColor":"neutral-100","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull has-neutral-100-background-color has-background" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"constrained","contentSize":"760px"}} -->
<div class="wp-block-group"><!-- wp:heading {"textAlign":"center","level":1,"fontSize":"huge"} -->
<h1 class="wp-block-heading has-text-align-center has-huge-font-size"><?php echo esc_html_x( 'A headline that earns the click', 'Hero pattern heading', 'hw-starter' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size"><?php echo esc_html_x( 'One or two clear sentences describing the offer and who it is for. Replace this placeholder copy per client.', 'Hero pattern subtext', 'hw-starter' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"brand-primary","textColor":"base-white"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-base-white-color has-brand-primary-background-color has-text-color has-background wp-element-button" href="#"><?php echo esc_html_x( 'Get started', 'Hero pattern button', 'hw-starter' ); ?></a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-hw-outline-pill"} -->
<div class="wp-block-button is-style-hw-outline-pill"><a class="wp-block-button__link wp-element-button" href="#"><?php echo esc_html_x( 'Learn more', 'Hero pattern secondary button', 'hw-starter' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
