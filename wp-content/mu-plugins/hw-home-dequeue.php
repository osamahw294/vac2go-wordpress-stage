<?php
/* Plugin Name: HW Home Dequeue — front page loads only the CSS it needs */
if (!defined('ABSPATH')) exit;
add_filter('kadence_theme_google_fonts_enabled', '__return_false');
add_filter('kadence_google_fonts_enabled', '__return_false');
add_action('wp_enqueue_scripts', function () {
  if (!is_front_page() && !is_home() && !is_page()) return;
  global $wp_styles;
  if (empty($wp_styles->registered)) return;
  $keep = array('hw-', 'gravity', 'gform', 'cookie-notice', 'recaptcha');
  foreach (array_keys($wp_styles->registered) as $h) {
    $k = false; foreach ($keep as $s) { if (stripos($h, $s) !== false) { $k = true; break; } }
    if (!$k) wp_dequeue_style($h);
  }
}, 999);
