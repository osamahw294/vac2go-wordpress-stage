<?php
/**
 * Title: CTA Banner
 * Slug: hw-starter/cta-banner
 * Categories: hw
 * Description: Full-width call-to-action banner with heading, short text, and a button on a brand background.
 * Keywords: cta, banner, call to action, contact
 * Viewport Width: 1280
 *
 * @package HW_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * DEV NOTES — CTA Banner pattern
 * ------------------------------
 * HOW TO USE: insert "CTA Banner" (Patterns -> Highwater), then edit copy and
 *   the button link in the Site Editor. Edit THIS file only to change the
 *   default copy for future inserts.
 * PLACEHOLDERS TO REPLACE: heading, text, and the button `href="#"`.
 * BRAND TOKENS THIS RELIES ON (from theme.json — restyle there, not here):
 *   - color: brand-secondary (section bg), base-white (heading/text on it),
 *            brand-accent (button bg), neutral-900 (button text)
 *   - spacing: 40 / 60 presets;  font size: x-large
 */
?>
<!-- wp:group {"tagName":"section","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"brand-secondary","textColor":"base-white","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull has-base-white-color has-brand-secondary-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"constrained","contentSize":"720px"}} -->
<div class="wp-block-group"><!-- wp:heading {"textAlign":"center","level":2,"textColor":"base-white","fontSize":"x-large"} -->
<h2 class="wp-block-heading has-text-align-center has-base-white-color has-text-color has-x-large-font-size"><?php echo esc_html_x( 'Ready to talk?', 'CTA banner heading', 'hw-starter' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><?php echo esc_html_x( 'Tell them what happens next in a single sentence. Replace this per client.', 'CTA banner text', 'hw-starter' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"brand-accent","textColor":"neutral-900"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-neutral-900-color has-brand-accent-background-color has-text-color has-background wp-element-button" href="#"><?php echo esc_html_x( 'Contact us', 'CTA banner button', 'hw-starter' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
