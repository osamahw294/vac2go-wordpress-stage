<?php
/**
 * Template Name: HW Equipment Rental
 *
 * Divi-free rental / product equipment page (hero + red title band +
 * 2-col photo/features + dark buttons). Data-driven from post meta
 * `_hw_data` (JSON, type=rental). Owned by the Equipment bucket.
 * Reuses shared site-header / site-footer; does not modify home.css.
 */
if (!defined('ABSPATH')) exit;

if (!function_exists('hw_eq_kses')) {
  function hw_eq_kses($html) {
    $allowed = array(
      'p' => array(), 'strong' => array('style' => true), 'b' => array('style' => true), 'em' => array(), 'i' => array(),
      'br' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(),
      'h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(),
      'span' => array('style' => true), 'a' => array('href' => true, 'target' => true, 'rel' => true),
    );
    return wp_kses($html, $allowed);
  }
}

$hw = get_post_meta(get_the_ID(), '_hw_data', true);
$d  = is_string($hw) ? json_decode($hw, true) : (is_array($hw) ? $hw : null);
if (!is_array($d)) $d = array();
$g = get_stylesheet_directory_uri();
$hero = !empty($d['hero']) ? $d['hero'] : (!empty($d['heroImg']) ? $d['heroImg'] : '');
$left = !empty($d['leftImage']) ? $d['leftImage'] : '';
$buttons = isset($d['buttons']) && is_array($d['buttons']) ? $d['buttons'] : array();
// Resolve hero to an attachment so we can emit a responsive srcset (mobile loads a small variant).
$heroId = 0;
if ($hero) { $heroId = attachment_url_to_postid(home_url($hero)); if (!$heroId) $heroId = attachment_url_to_postid($hero); }
// Preload the mobile-sized variant so the LCP image is small (only when we resolved sizes).
$heroPreload = '';
if ($heroId) {
  $srcset = wp_get_attachment_image_srcset($heroId, 'large');
  $med = wp_get_attachment_image_url($heroId, 'large');
  if ($med) { $heroPreload = '<link rel="preload" as="image" href="'.esc_url($med).'"'.($srcset ? ' imagesrcset="'.esc_attr($srcset).'" imagesizes="100vw"' : '').' fetchpriority="high">'; }
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php echo $heroPreload; ?>
<style id="hw-critical">:root{--red:#e01f30;--red-h:#dd3333}*{box-sizing:border-box}body.hw-page{margin:0;font-family:"Open Sans",Helvetica,Arial,sans-serif;color:#2d2d2d;overflow-x:hidden;padding-top:122px;background:#e8e8e8}.hw-wrap{width:100%;max-width:1350px;margin:0 auto;padding:0 30px}img{max-width:100%}#hw-header{position:fixed;top:0;left:0;right:0;z-index:99999}.hw-topbar{background:#444;height:47px;display:flex;align-items:center}.hw-topbar .hw-wrap{display:flex;align-items:center;justify-content:flex-start;gap:26px}.hw-topbar .hw-phone,.hw-topbar .hw-email{font-family:ABeeZee,Helvetica,Arial,sans-serif;font-size:18px;line-height:18px;color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:8px}.hw-topbar .hw-phone{font-weight:600}.hw-topbar .hw-email{font-weight:700}.hw-mainbar{background:#000;height:75px;display:flex;align-items:center}.hw-mainbar .hw-wrap{display:flex;align-items:flex-end;justify-content:space-between;height:75px}.hw-logo{display:flex;align-items:center;align-self:center;margin-left:4px}.hw-logo img{height:53px;width:auto;display:block}.hw-nav{display:flex;align-items:flex-end;gap:24px}.hw-nav ul{display:flex;align-items:flex-end;gap:22px;list-style:none;margin:0;padding:0}.hw-nav ul a{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:40px;color:#fff;text-decoration:none;text-transform:uppercase;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;padding:0 10px}.hw-caret{display:inline-block;width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid currentColor;opacity:.85}.hw-reserve{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:16px;color:#fff;text-decoration:none;text-transform:uppercase;background:var(--red);padding:12px 18px;border-radius:4px;display:inline-flex;align-items:center;gap:8px;white-space:nowrap}.hw-burger{display:none;flex-direction:column;justify-content:center;gap:5px;width:56px;height:44px;padding:0 13px;background:#4a4a4a;border:0;border-radius:6px;cursor:pointer}.hw-burger span{display:block;height:3px;width:100%;background:#fff;border-radius:2px}.hw-mobmenu{display:none}.hw-footer-bar{background:var(--red);color:#fff;font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:13px;line-height:1.5;text-align:center;padding:13px 20px}.hw-footer-bar a{color:#fff;text-decoration:underline}
/* rental */
.hw-eqr-hero{display:block;width:100%;height:auto;aspect-ratio:1440/678;object-fit:cover;object-position:center}.hw-eqr-band{background:var(--red);padding:52px 0;text-align:center}.hw-eqr-band h1{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:36px;line-height:1.3;font-weight:500;color:#fff;margin:0 0 8px}.hw-eqr-band p{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:20px;line-height:1.5;font-weight:500;color:#fff;margin:0 auto;max-width:1000px}.hw-eqr-sec{background:#ededed}.hw-eqr-cols{display:grid;grid-template-columns:1fr 1fr;align-items:stretch}.hw-eqr-photo{background-position:center;background-size:cover;background-repeat:no-repeat;min-height:420px}.hw-eqr-content{padding:56px 60px}.hw-eqr-right{font-family:"Open Sans",Helvetica,Arial,sans-serif;color:#333}.hw-eqr-right h1{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:30px;line-height:1.2;font-weight:700;color:#333;margin:0 0 18px}.hw-eqr-right h2,.hw-eqr-right h3{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:17px;line-height:1.4;font-weight:500;color:#333;margin:0 0 10px}.hw-eqr-right p{font-size:15px;line-height:1.7;font-weight:300;color:#333;margin:0 0 10px}.hw-eqr-right ul{margin:0 0 16px;padding:0 0 0 18px;list-style:disc}.hw-eqr-right li{font-size:15px;line-height:1.73;font-weight:300;color:#000;margin:0}.hw-eqr-right a{color:#333;text-decoration:underline}.hw-eqr-right strong{font-weight:700}.hw-eqr-btns{margin-top:26px;display:flex;flex-direction:column;align-items:flex-start;gap:14px}.hw-eqr-btn{display:inline-block;background:#383838;color:#fff;font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:14px;font-weight:500;line-height:1;text-transform:uppercase;text-decoration:none;padding:17px 30px;border:1px solid #383838}.hw-eqr-btn:hover{background:#fff;color:#383838}
.hw-eqr-video{background:#ededed;text-align:center;padding:6px 20px 44px}.hw-eqr-video a{display:inline-block;max-width:451px;width:100%;line-height:0}.hw-eqr-video img{display:block;width:100%;height:auto;aspect-ratio:1498/843;border:0}
.hw-eqr-gray{background:#606060;padding:15px 0}.hw-eqr-gray .hw-eq-inner{padding-top:29px;padding-bottom:29px}.hw-eqr-gray h2{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:26px;line-height:33.8px;font-weight:500;color:#fff;text-align:left;margin:0 0 35px}.hw-eqr-gray .hw-eqr-gbody{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:15px;line-height:27px;font-weight:500;color:#fff}.hw-eqr-gray .hw-eqr-gbody p{margin:0}
.hw-eqr-extra{background:#fff;padding:86px 30px}.hw-eqr-extra-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:63px;width:100%;max-width:1152px;margin:0 auto}.hw-eqr-extra-grid img{display:block;width:100%;height:auto;aspect-ratio:1125/727;object-fit:cover}
@media (max-width:767px){.hw-nav{display:none}.hw-burger{display:flex}body.hw-page{padding-top:149px}.hw-topbar{height:auto;min-height:85px;padding:9px 0}.hw-topbar .hw-wrap{flex-direction:row;flex-wrap:wrap;justify-content:center;gap:4px 14px;text-align:center;padding:0 16px}.hw-topbar .hw-phone,.hw-topbar .hw-email{justify-content:center}.hw-mainbar{height:64px}.hw-mainbar .hw-wrap{align-items:center}.hw-logo{align-self:center}.hw-logo img{height:35px}.hw-eqr-hero{height:auto;aspect-ratio:16/11}.hw-eqr-band{padding:34px 0}.hw-eqr-band h1{font-size:28px}.hw-eqr-band p{font-size:18px}.hw-eqr-cols{grid-template-columns:1fr}.hw-eqr-photo{min-height:280px;aspect-ratio:16/11}.hw-eqr-content{padding:34px 22px}.hw-eqr-right h1{font-size:26px}.hw-eqr-btn{width:100%;text-align:center}.hw-eqr-video{padding:0 16px 30px}.hw-eqr-gray h2{font-size:24px;line-height:1.3}.hw-eqr-extra{padding:40px 20px}.hw-eqr-extra-grid{grid-template-columns:1fr;gap:22px;max-width:420px}}</style>
<link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css" media="print" onload="this.media='all';this.onload=null">
<link rel="stylesheet" href="<?php echo $g; ?>/assets/css/home.css?v=<?php echo @filemtime(get_stylesheet_directory().'/assets/css/home.css'); ?>" media="print" onload="this.media='all';this.onload=null">
<link rel="stylesheet" href="<?php echo $g; ?>/assets/css/equipment.css?v=<?php echo @filemtime(get_stylesheet_directory().'/assets/css/equipment.css'); ?>" media="print" onload="this.media='all';this.onload=null">
<noscript><link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css"><link rel="stylesheet" href="<?php echo $g; ?>/assets/css/home.css"><link rel="stylesheet" href="<?php echo $g; ?>/assets/css/equipment.css"></noscript>
<?php wp_head(); ?>
</head>
<body <?php body_class('hw-page hw-eqr'); ?>>
<?php get_template_part('template-parts/site-header'); ?>
<main id="hw-main">

  <?php if ($heroId): ?>
  <?php echo wp_get_attachment_image($heroId, 'large', false, array('class' => 'hw-eqr-hero', 'alt' => $d['title'] ?? '', 'fetchpriority' => 'high', 'decoding' => 'async', 'sizes' => '100vw')); ?>
  <?php elseif ($hero): ?>
  <img class="hw-eqr-hero" src="<?php echo esc_url($hero); ?>" alt="<?php echo esc_attr($d['title'] ?? ''); ?>" fetchpriority="high" decoding="async"<?php if (!empty($d['heroDim'][0])) echo ' width="'.intval($d['heroDim'][0]).'" height="'.intval($d['heroDim'][1]).'"'; ?>>
  <?php endif; ?>

  <?php if (!empty($d['title']) || !empty($d['subtitle'])): ?>
  <section class="hw-eqr-band"><div class="hw-eq-inner">
    <?php if (!empty($d['title'])): ?><h1><?php echo esc_html($d['title']); ?></h1><?php endif; ?>
    <?php if (!empty($d['subtitle'])): ?><p><?php echo esc_html($d['subtitle']); ?></p><?php endif; ?>
  </div></section>
  <?php endif; ?>

  <section class="hw-eqr-sec">
    <div class="hw-eqr-cols">
      <div class="hw-eqr-photo"<?php if ($left) echo ' style="background-image:url('.esc_url($left).')"'; ?> role="img" aria-label="<?php echo esc_attr($d['title'] ?? ''); ?>"></div>
      <div class="hw-eqr-content">
        <div class="hw-eqr-right"><?php echo hw_eq_kses($d['rightHtml'] ?? ''); ?></div>
        <?php if ($buttons): ?>
        <div class="hw-eqr-btns">
          <?php foreach ($buttons as $b): if (empty($b['t'])) continue; ?>
          <a class="hw-eqr-btn" href="<?php echo esc_url($b['href'] ?? '#'); ?>"><?php echo esc_html($b['t']); ?></a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php if (!empty($d['video']['src'])): ?>
    <div class="hw-eqr-video">
      <a href="<?php echo esc_url($d['video']['href'] ?? '#'); ?>" target="_blank" rel="noopener"><img src="<?php echo esc_url($d['video']['src']); ?>"<?php if (!empty($d['video']['w'])) echo ' width="'.intval($d['video']['w']).'" height="'.intval($d['video']['h']).'"'; ?> alt="<?php echo esc_attr(($d['title'] ?? '').' training video'); ?>" loading="lazy" decoding="async"></a>
    </div>
    <?php endif; ?>
  </section>

  <?php $extra = isset($d['extraImages']) && is_array($d['extraImages']) ? $d['extraImages'] : array(); if ($extra): ?>
  <section class="hw-eqr-extra"><div class="hw-eqr-extra-grid">
    <?php foreach ($extra as $im): if (empty($im['src'])) continue; ?>
    <img src="<?php echo esc_url($im['src']); ?>"<?php if (!empty($im['w'])) echo ' width="'.intval($im['w']).'" height="'.intval($im['h']).'"'; ?> alt="<?php echo esc_attr($im['alt'] ?? ($d['title'] ?? '')); ?>" loading="lazy" decoding="async">
    <?php endforeach; ?>
  </div></section>
  <?php endif; ?>

  <?php if (!empty($d['grayTitle']) || !empty($d['grayHtml'])): ?>
  <section class="hw-eqr-gray"><div class="hw-eq-inner">
    <?php if (!empty($d['grayTitle'])): ?><h2><?php echo esc_html($d['grayTitle']); ?></h2><?php endif; ?>
    <?php if (!empty($d['grayHtml'])): ?><div class="hw-eqr-gbody"><?php echo hw_eq_kses($d['grayHtml']); ?></div><?php endif; ?>
  </div></section>
  <?php endif; ?>

</main>
<?php get_template_part('template-parts/site-footer'); wp_footer(); ?>
</body>
</html>
