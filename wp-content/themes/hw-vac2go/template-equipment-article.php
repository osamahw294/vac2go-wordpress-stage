<?php
/**
 * Template Name: HW Equipment Article
 *
 * Divi-free long-form article page (full-width hero + prose body).
 * Data-driven from post meta `_hw_data` (JSON, type=article).
 * Owned by the Equipment bucket. Reuses shared header/footer.
 */
if (!defined('ABSPATH')) exit;

if (!function_exists('hw_eqa_kses')) {
  function hw_eqa_kses($html) {
    $a = array('style' => true);
    $allowed = array(
      'p' => array(), 'strong' => array(), 'b' => array(), 'em' => array(), 'i' => array(), 'u' => array(),
      'br' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(), 'blockquote' => array(),
      'h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(), 'h5' => array(),
      'span' => array(), 'a' => array('href' => true, 'target' => true, 'rel' => true),
      'figure' => array(), 'img' => array('src' => true, 'alt' => true, 'width' => true, 'height' => true, 'loading' => true, 'decoding' => true),
    );
    return wp_kses($html, $allowed);
  }
}

$hw = get_post_meta(get_the_ID(), '_hw_data', true);
$d  = is_string($hw) ? json_decode($hw, true) : (is_array($hw) ? $hw : null);
if (!is_array($d)) $d = array();
$g = get_stylesheet_directory_uri();
$hero = !empty($d['hero']) ? $d['hero'] : '';
$heroId = 0;
if ($hero) { $heroId = attachment_url_to_postid(home_url($hero)); if (!$heroId) $heroId = attachment_url_to_postid($hero); }
$heroPreload = '';
if ($heroId) { $srcset = wp_get_attachment_image_srcset($heroId, 'large'); $med = wp_get_attachment_image_url($heroId, 'large'); if ($med) $heroPreload = '<link rel="preload" as="image" href="'.esc_url($med).'"'.($srcset ? ' imagesrcset="'.esc_attr($srcset).'" imagesizes="100vw"' : '').' fetchpriority="high">'; }
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php echo $heroPreload; ?>
<style id="hw-critical">:root{--red:#e01f30;--red-h:#dd3333}*{box-sizing:border-box}body.hw-page{margin:0;font-family:"Open Sans",Helvetica,Arial,sans-serif;color:#2d2d2d;overflow-x:hidden;padding-top:122px;background:#fff}.hw-wrap{width:100%;max-width:1350px;margin:0 auto;padding:0 30px}img{max-width:100%}#hw-header{position:fixed;top:0;left:0;right:0;z-index:99999}.hw-topbar{background:#444;height:47px;display:flex;align-items:center}.hw-topbar .hw-wrap{display:flex;align-items:center;justify-content:flex-start;gap:26px}.hw-topbar .hw-phone,.hw-topbar .hw-email{font-family:ABeeZee,Helvetica,Arial,sans-serif;font-size:18px;line-height:18px;color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:8px}.hw-topbar .hw-phone{font-weight:600}.hw-topbar .hw-email{font-weight:700}.hw-mainbar{background:#000;height:75px;display:flex;align-items:center}.hw-mainbar .hw-wrap{display:flex;align-items:flex-end;justify-content:space-between;height:75px}.hw-logo{display:flex;align-items:center;align-self:center;margin-left:4px}.hw-logo img{height:53px;width:auto;display:block}.hw-nav{display:flex;align-items:flex-end;gap:24px}.hw-nav ul{display:flex;align-items:flex-end;gap:22px;list-style:none;margin:0;padding:0}.hw-nav ul a{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:40px;color:#fff;text-decoration:none;text-transform:uppercase;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;padding:0 10px}.hw-caret{display:inline-block;width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid currentColor;opacity:.85}.hw-reserve{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:16px;color:#fff;text-decoration:none;text-transform:uppercase;background:var(--red);padding:12px 18px;border-radius:4px;display:inline-flex;align-items:center;gap:8px;white-space:nowrap}.hw-burger{display:none;flex-direction:column;justify-content:center;gap:5px;width:56px;height:44px;padding:0 13px;background:#4a4a4a;border:0;border-radius:6px;cursor:pointer}.hw-burger span{display:block;height:3px;width:100%;background:#fff;border-radius:2px}.hw-mobmenu{display:none}.hw-footer-bar{background:var(--red);color:#fff;font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:13px;line-height:1.5;text-align:center;padding:13px 20px}.hw-footer-bar a{color:#fff;text-decoration:underline}
.hw-eqr-hero{display:block;width:100%;aspect-ratio:1440/574;object-fit:cover;object-position:center}body.hw-eqa #hw-main{background:#fff}.hw-eqa-body{max-width:1212px;margin:0 auto;padding:52px 30px 64px;font-family:"Open Sans",Helvetica,Arial,sans-serif;color:#666;background:#fff}.hw-eqa-body h1{font-size:36px;line-height:1.15;font-weight:700;color:rgba(10,0,0,.75);margin:0 0 30px}.hw-eqa-body h2,.hw-eqa-body h4{font-size:36px;line-height:1.15;font-weight:700;color:var(--red);margin:56px 0 46px}.hw-eqa-body h3{font-size:22px;line-height:1.3;font-weight:700;color:#333;margin:30px 0 12px}.hw-eqa-body p{font-size:20px;line-height:1.75;font-weight:500;color:#666;margin:0 0 22px}.hw-eqa-body strong,.hw-eqa-body b{font-weight:700;color:#333}.hw-eqa-body h1 b,.hw-eqa-body h1 strong,.hw-eqa-body h2 b,.hw-eqa-body h2 strong,.hw-eqa-body h3 b,.hw-eqa-body h3 strong,.hw-eqa-body h4 b,.hw-eqa-body h4 strong{color:inherit;font-weight:inherit}.hw-eqa-body a{color:var(--red);text-decoration:none}.hw-eqa-body ul,.hw-eqa-body ol{margin:0 0 22px;padding:0 0 0 22px}.hw-eqa-body li{font-size:20px;line-height:1.75;font-weight:500;color:#666;margin:0 0 8px}
@media (max-width:767px){.hw-nav{display:none}.hw-burger{display:flex}body.hw-page{padding-top:149px}.hw-topbar{height:auto;min-height:85px;padding:9px 0}.hw-topbar .hw-wrap{flex-direction:row;flex-wrap:wrap;justify-content:center;gap:4px 14px;text-align:center;padding:0 16px}.hw-topbar .hw-phone,.hw-topbar .hw-email{justify-content:center}.hw-mainbar{height:64px}.hw-mainbar .hw-wrap{align-items:center}.hw-logo{align-self:center}.hw-logo img{height:35px}.hw-eqr-hero{aspect-ratio:16/10}.hw-eqa-body{padding:32px 22px 44px}.hw-eqa-body h1{font-size:27px}.hw-eqa-body h2{font-size:24px}}</style>
<link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css" media="print" onload="this.media='all';this.onload=null">
<link rel="stylesheet" href="<?php echo $g; ?>/assets/css/home.css?v=<?php echo @filemtime(get_stylesheet_directory().'/assets/css/home.css'); ?>" media="print" onload="this.media='all';this.onload=null">
<link rel="stylesheet" href="<?php echo $g; ?>/assets/css/equipment.css?v=<?php echo @filemtime(get_stylesheet_directory().'/assets/css/equipment.css'); ?>" media="print" onload="this.media='all';this.onload=null">
<noscript><link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css"><link rel="stylesheet" href="<?php echo $g; ?>/assets/css/home.css"><link rel="stylesheet" href="<?php echo $g; ?>/assets/css/equipment.css"></noscript>
<?php wp_head(); ?>
</head>
<body <?php body_class('hw-page hw-eqa'); ?>>
<?php get_template_part('template-parts/site-header'); ?>
<main id="hw-main">
  <?php if ($heroId): ?>
  <?php echo wp_get_attachment_image($heroId, 'large', false, array('class' => 'hw-eqr-hero', 'alt' => $d['title'] ?? '', 'fetchpriority' => 'high', 'decoding' => 'async', 'sizes' => '100vw')); ?>
  <?php elseif ($hero): ?>
  <img class="hw-eqr-hero" src="<?php echo esc_url($hero); ?>" alt="<?php echo esc_attr($d['title'] ?? ''); ?>" fetchpriority="high" decoding="async">
  <?php endif; ?>
  <?php
  // Strip whitespace/nbsp-only <p> and heading blocks so section spacing matches
  // production (the WYSIWYG source has empty separator paragraphs Divi rendered as
  // module gaps; here they'd double the space around headings).
  $hw_body = $d['body'] ?? '';
  $hw_body = str_replace(
    array('<p>&nbsp;</p>', '<p><span>&nbsp;</span></p>', '<h4>&nbsp;</h4>', '<h3>&nbsp;</h3>', '<h2>&nbsp;</h2>'),
    '',
    $hw_body
  );
  ?>
  <article class="hw-eqa-body"><?php echo hw_eqa_kses($hw_body); ?></article>
</main>
<?php get_template_part('template-parts/site-footer'); wp_footer(); ?>
</body>
</html>
