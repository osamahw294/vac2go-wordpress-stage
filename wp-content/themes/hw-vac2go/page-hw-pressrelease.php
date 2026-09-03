<?php
/* Template Name: HW Press Release
 * Divi-free location-announcement / press-release page. Data-driven from _hw_pr meta.
 * Part of the Location bucket. Reuses shared site-header/site-footer + location.css. */
if(!defined('ABSPATH'))exit;
$pid = get_the_ID();
$raw = get_post_meta($pid,'_hw_pr',true);
$d   = $raw ? json_decode($raw,true) : null;
$css = @file_get_contents(get_stylesheet_directory().'/assets/css/location.css');
$strip = function($h){ return $h===null?'':preg_replace('#https?://(www\.)?vac2go\.com#i','',$h); };
$banner = ($d && !empty($d['banner_img'])) ? $strip($d['banner_img']) : '';
$banner_webp = ($banner && !preg_match('/\.avif$/i',$banner)) ? $banner.'.webp' : '';
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="preload" as="font" type="font/woff2" href="/wp-content/hw-fonts/f5.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/wp-content/hw-fonts/f32.woff2" crossorigin>
<?php if($banner_webp): ?><link rel="preload" as="image" href="<?php echo esc_url($banner_webp); ?>" type="image/webp" fetchpriority="high"><?php elseif($banner): ?><link rel="preload" as="image" href="<?php echo esc_url($banner); ?>" fetchpriority="high"><?php endif; ?>
<style id="hw-critical"><?php echo $css; ?></style>
<link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css" media="print" onload="this.media='all';this.onload=null">
<noscript><link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css"></noscript>
<?php wp_head(); ?>
</head>
<body <?php body_class('hw-page hw-pr'); ?>>
<?php get_template_part('template-parts/site-header'); ?>
<main id="hw-main">
  <?php if($d): ?>
    <?php if($banner): ?>
    <section class="hw-pr-banner"><picture><?php if($banner_webp): ?><source type="image/webp" srcset="<?php echo esc_url($banner_webp); ?>"><?php endif; ?><img src="<?php echo esc_url($banner); ?>"<?php if(!empty($d['banner_w'])) echo ' width="'.intval($d['banner_w']).'" height="'.intval($d['banner_h']).'"'; ?> alt="<?php echo esc_attr(get_the_title()); ?>" fetchpriority="high" decoding="async"></picture></section>
    <?php endif; ?>
    <section class="hw-pr-body"><div class="hw-loc-wrap">
      <article class="hw-pr-article"><?php echo $strip($d['body_html']); ?></article>
    </div></section>
  <?php else: ?>
    <section class="hw-pr-body"><div class="hw-loc-wrap"><article class="hw-pr-article"><?php the_content(); ?></article></div></section>
  <?php endif; ?>
</main>
<?php get_template_part('template-parts/site-footer'); wp_footer(); ?>
</body>
</html>
