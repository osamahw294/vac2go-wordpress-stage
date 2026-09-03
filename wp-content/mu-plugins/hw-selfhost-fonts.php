<?php
/*
 * Plugin Name: HW Self-Hosted Fonts
 * Description: Removes the render-blocking EXTERNAL Google Fonts (Poppins/Open Sans) and serves the
 *   SAME fonts self-hosted from /wp-content/hw-fonts/ instead — identical look, no cross-origin
 *   round-trips, no font-swap reflow. font-display:swap is baked into hw-fonts.css.
 */
if (!defined('ABSPATH')) exit;

// 1) Drop Divi/builder external Google Font stylesheets.
add_action('wp_enqueue_scripts', function () {
    foreach (array('divi-fonts','et-builder-googlefonts-cached','et-builder-googlefonts','et-gf-poppins','google-fonts-1','google-fonts') as $h) {
        wp_dequeue_style($h);
        wp_deregister_style($h);
    }
}, 9999);

// 2) Safety net: strip any leftover fonts.googleapis.com <link> from the head.
add_action('template_redirect', function () {
    if (is_admin()) return;
    ob_start(function ($html) {
        return $html ? preg_replace('#<link[^>]*fonts\.googleapis\.com[^>]*>#i', '', $html) : $html;
    });
}, 7);

// 3) Load the self-hosted @font-face CSS (same families, local files) + preconnect-free.
add_action('wp_head', function () {
    $css = '/wp-content/hw-fonts/hw-fonts.css';
    echo "\n<link rel=\"stylesheet\" href=\"" . esc_url($css) . "\" media=\"all\">\n";
}, 2);
