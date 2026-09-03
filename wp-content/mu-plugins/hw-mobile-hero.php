<?php
/* Plugin Name: HW Mobile Hero — phone static-render hero (pixel-neutral)
 * Phones (<=767px): hero renders slide 1 only, from CSS (no slider-JS gating), bg WebP + preload.
 * Desktop keeps the full slider untouched. */
if (!defined('ABSPATH')) exit;
define('HW_MH_MOB', 'wp-content/uploads/2026/02/DSC_0003-hero-1280.webp');

add_action('wp_head', function () {
  if (!is_front_page() && !is_home()) return;
  $mob = home_url('/'.HW_MH_MOB);
  echo "\n<link rel=\"preload\" as=\"image\" href=\"".esc_url($mob)."\" media=\"(max-width:767px)\" fetchpriority=\"high\" type=\"image/webp\">\n";
  ?>
<style id="hw-mobile-hero">
@media (max-width:767px){
  .et_pb_fullwidth_slider_0 .et_pb_slides{position:relative!important;height:auto!important}
  .et_pb_fullwidth_slider_0 .et_pb_slide:not(.et_pb_slide_0){display:none!important}
  .et_pb_fullwidth_slider_0 .et_pb_slide_0{
    position:relative!important;opacity:1!important;display:block!important;
    -webkit-animation:none!important;animation:none!important;transition:none!important;
    background-image:url('<?php echo esc_url($mob); ?>')!important;
    background-size:cover!important;background-position:50% 50%!important;
  }
  .et_pb_fullwidth_slider_0 .et-pb-controllers,
  .et_pb_fullwidth_slider_0 .et-pb-slider-arrows,
  .et_pb_fullwidth_slider_0 .et_pb_slider_arrows{display:none!important}
}
</style>
  <?php
}, 5);
