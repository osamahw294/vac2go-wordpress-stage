<?php
/**
 * Title: Testimonial — Quote
 * Slug: hw-starter/testimonial
 * Categories: hw
 * Description: A single centered testimonial quote with attribution on a soft surface.
 * Keywords: testimonial, quote, review, social proof
 * Viewport Width: 1280
 *
 * @package HW_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * DEV NOTES — Testimonial pattern
 * -------------------------------
 * HOW TO USE: insert "Testimonial — Quote" (Patterns -> Highwater), then swap
 *   in a real client quote and attribution in the Site Editor. Edit THIS file
 *   only to change the default placeholder quote for future inserts.
 * PLACEHOLDERS TO REPLACE: the quote paragraph and the <cite> attribution.
 * BRAND TOKENS THIS RELIES ON (from theme.json — restyle there, not here):
 *   - color: neutral-100 (soft section background)
 *   - spacing: 60 preset;  uses the core "Large" quote style (is-style-large)
 */
?>
<!-- wp:group {"tagName":"section","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"neutral-100","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull has-neutral-100-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:quote {"className":"is-style-large"} -->
<blockquote class="wp-block-quote is-style-large"><!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><?php echo esc_html_x( '“They rebuilt our site and our leads doubled. Replace this quote with a real client testimonial.”', 'Testimonial quote', 'hw-starter' ); ?></p>
<!-- /wp:paragraph --><cite><?php echo esc_html_x( 'Client Name, Company', 'Testimonial attribution', 'hw-starter' ); ?></cite></blockquote>
<!-- /wp:quote --></section>
<!-- /wp:group -->
