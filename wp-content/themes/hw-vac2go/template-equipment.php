<?php
/**
 * Template Name: HW Equipment For Sale
 *
 * Divi-free "FOR SALE" truck page. Data-driven from post meta `_hw_data`
 * (JSON). Owned by the Equipment bucket. Reuses the shared site-header /
 * site-footer template parts; does NOT modify home.css / header / footer.
 */
if (!defined('ABSPATH')) exit;

if (!function_exists('hw_eq_kses')) {
  function hw_eq_kses($html) {
    $allowed = array(
      'p' => array(), 'strong' => array(), 'b' => array(), 'em' => array(), 'i' => array(),
      'br' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(),
      'h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(),
      'span' => array('style' => true), 'a' => array('href' => true, 'target' => true, 'rel' => true),
    );
    // permit inline color on strong/span for the red price/phone
    $allowed['strong']['style'] = true; $allowed['b']['style'] = true;
    return wp_kses($html, $allowed);
  }
}

$hw = get_post_meta(get_the_ID(), '_hw_data', true);
$d  = is_string($hw) ? json_decode($hw, true) : (is_array($hw) ? $hw : null);
if (!is_array($d)) $d = array();

$g = get_stylesheet_directory_uri();

// gallery: compute the tallest displayed ratio (box height at 100% width)
$gallery = isset($d['gallery']) && is_array($d['gallery']) ? $d['gallery'] : array();
$ratio = 0.66; // fallback (landscape)
foreach ($gallery as $im) { if (!empty($im['w']) && !empty($im['h'])) { $r = $im['h'] / $im['w']; if ($r > $ratio) $ratio = $r; } }
$ratioPct = round($ratio * 100, 3);

$formId = isset($d['formId']) && $d['formId'] !== '' ? preg_replace('/[^0-9]/', '', $d['formId']) : '';
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php if (!empty($gallery[0]['src'])): ?>
<link rel="preload" as="image" href="<?php echo esc_url($gallery[0]['src']); ?>" fetchpriority="high">
<?php endif; ?>
<style id="hw-critical">:root{--red:#e01f30;--red-h:#dd3333}*{box-sizing:border-box}body.hw-page{margin:0;font-family:"Open Sans",Helvetica,Arial,sans-serif;color:#2d2d2d;overflow-x:hidden;padding-top:122px;background:#e8e8e8}.hw-wrap{width:100%;max-width:1350px;margin:0 auto;padding:0 30px}img{max-width:100%}#hw-header{position:fixed;top:0;left:0;right:0;z-index:99999}.hw-topbar{background:#444;height:47px;display:flex;align-items:center}.hw-topbar .hw-wrap{display:flex;align-items:center;justify-content:flex-start;gap:26px}.hw-topbar .hw-phone,.hw-topbar .hw-email{font-family:ABeeZee,Helvetica,Arial,sans-serif;font-size:18px;line-height:18px;color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:8px}.hw-topbar .hw-phone{font-weight:600}.hw-topbar .hw-email{font-weight:700}.hw-mainbar{background:#000;height:75px;display:flex;align-items:center}.hw-mainbar .hw-wrap{display:flex;align-items:flex-end;justify-content:space-between;height:75px}.hw-logo{display:flex;align-items:center;align-self:center;margin-left:4px}.hw-logo img{height:53px;width:auto;display:block}.hw-nav{display:flex;align-items:flex-end;gap:24px}.hw-nav ul{display:flex;align-items:flex-end;gap:22px;list-style:none;margin:0;padding:0}.hw-nav ul a{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:40px;color:#fff;text-decoration:none;text-transform:uppercase;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;padding:0 10px}.hw-caret{display:inline-block;width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid currentColor;opacity:.85}.hw-reserve{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:16px;color:#fff;text-decoration:none;text-transform:uppercase;background:var(--red);padding:12px 18px;border-radius:4px;display:inline-flex;align-items:center;gap:8px;white-space:nowrap}.hw-burger{display:none;flex-direction:column;justify-content:center;gap:5px;width:56px;height:44px;padding:0 13px;background:#4a4a4a;border:0;border-radius:6px;cursor:pointer}.hw-burger span{display:block;height:3px;width:100%;background:#fff;border-radius:2px}.hw-mobmenu{display:none}.hw-footer-bar{background:var(--red);color:#fff;font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:13px;line-height:1.5;text-align:center;padding:13px 20px}.hw-footer-bar a{color:#fff;text-decoration:underline}
/* equipment */
.hw-eq-banner{background:var(--red);padding:15px 0}.hw-eq-banner h1{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:70px;line-height:70px;font-weight:700;color:#fff;text-align:center;margin:0;padding:0 0 10px}.hw-eq-sec{background:#fff;padding:29px 0}.hw-eq-inner{width:100%;max-width:1152px;margin:0 auto;padding:0 30px}.hw-eq-subtitle{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:26px;line-height:26px;font-weight:700;color:rgba(10,0,0,.75);text-align:center;margin:0 0 40px;padding:0 0 10px}.hw-eq-cols{display:grid;grid-template-columns:1fr 1fr;column-gap:64px;align-items:start}.hw-eq-left{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:19px;line-height:34.2px;font-weight:400;color:#000}.hw-eq-left p{margin:0 0 12px}.hw-eq-left strong{font-weight:700}.hw-eq-left h2{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:26px;line-height:26px;font-weight:500;color:var(--red);margin:14px 0 6px}.hw-eq-left ul{margin:0 0 12px;padding:0 0 0 19px;list-style:disc}.hw-eq-left li{font-size:19px;line-height:26px;font-weight:600;color:#000;margin:0}.hw-eq-call{margin-top:30px}.hw-eq-call h1{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:30px;line-height:42px;font-weight:700;color:#333;margin:0}.hw-eq-gal{position:relative;width:100%}.hw-eq-box{position:relative;width:100%;overflow:hidden}.hw-eq-slide{position:absolute;top:0;left:0;width:100%;opacity:0;transition:opacity .6s ease;pointer-events:none}.hw-eq-slide img{width:100%;height:auto;display:block}.hw-eq-slide.on{opacity:1;pointer-events:auto}.hw-eq-dots{position:absolute;left:0;right:0;bottom:10px;display:flex;justify-content:center;gap:9px;z-index:3}.hw-eq-dot{width:9px;height:9px;border-radius:50%;background:rgba(0,0,0,.35);border:0;padding:0;cursor:pointer}.hw-eq-dot.on{background:#fff;box-shadow:0 0 0 1px rgba(0,0,0,.45)}.hw-eq-gal.hw-eq-single .hw-eq-dots{display:none}.hw-eq-inq{background:#f2f2f2;padding:58px 0}.hw-eq-inq .hw-eq-intro{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:22px;line-height:39.6px;font-weight:700;color:#000;margin:0 0 26px}
@media (max-width:767px){.hw-nav{display:none}.hw-burger{display:flex}body.hw-page{padding-top:149px}.hw-topbar{height:auto;min-height:85px;padding:9px 0}.hw-topbar .hw-wrap{flex-direction:row;flex-wrap:wrap;justify-content:center;gap:4px 14px;text-align:center;padding:0 16px}.hw-topbar .hw-phone,.hw-topbar .hw-email{justify-content:center}.hw-mainbar{height:64px}.hw-mainbar .hw-wrap{align-items:center}.hw-logo{align-self:center}.hw-logo img{height:35px}.hw-eq-banner h1{font-size:46px;line-height:50px}.hw-eq-inner{padding:0 20px}.hw-eq-subtitle{font-size:24px;line-height:28px;margin:0 0 30px}.hw-eq-cols{grid-template-columns:1fr;row-gap:36px}.hw-eq-call h1{font-size:28px;line-height:38px}.hw-eq-inq .hw-eq-intro{font-size:22px;line-height:36px}}</style>
<link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css" media="print" onload="this.media='all';this.onload=null">
<link rel="stylesheet" href="<?php echo $g; ?>/assets/css/home.css?v=<?php echo @filemtime(get_stylesheet_directory().'/assets/css/home.css'); ?>" media="print" onload="this.media='all';this.onload=null">
<link rel="stylesheet" href="<?php echo $g; ?>/assets/css/equipment.css?v=<?php echo @filemtime(get_stylesheet_directory().'/assets/css/equipment.css'); ?>" media="print" onload="this.media='all';this.onload=null">
<noscript><link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css"><link rel="stylesheet" href="<?php echo $g; ?>/assets/css/home.css"><link rel="stylesheet" href="<?php echo $g; ?>/assets/css/equipment.css"></noscript>
<?php wp_head(); ?>
</head>
<body <?php body_class('hw-page hw-eq'); ?>>
<?php get_template_part('template-parts/site-header'); ?>
<main id="hw-main">

  <section class="hw-eq-banner"><div class="hw-eq-inner"><h1><?php echo esc_html(!empty($d['banner']) ? $d['banner'] : 'FOR SALE'); ?></h1></div></section>

  <section class="hw-eq-sec"><div class="hw-eq-inner">
    <?php if (!empty($d['subtitle'])): ?><h2 class="hw-eq-subtitle"><?php echo esc_html($d['subtitle']); ?></h2><?php endif; ?>
    <div class="hw-eq-cols">
      <div class="hw-eq-colL">
        <div class="hw-eq-left"><?php echo hw_eq_kses($d['specsHtml'] ?? ''); ?></div>
        <?php if (!empty($d['callHtml'])): ?><div class="hw-eq-call"><?php echo hw_eq_kses($d['callHtml']); ?></div><?php endif; ?>
      </div>
      <div class="hw-eq-colR">
        <?php if ($gallery): $single = count($gallery) < 2; ?>
        <div class="hw-eq-gal<?php echo $single ? ' hw-eq-single' : ''; ?>" id="hw-eq-gal">
          <div class="hw-eq-box" style="padding-top:<?php echo esc_attr($ratioPct); ?>%">
            <?php foreach ($gallery as $i => $im): ?>
            <div class="hw-eq-slide<?php echo $i === 0 ? ' on' : ''; ?>">
              <img src="<?php echo esc_url($im['src']); ?>"<?php if (!empty($im['w']) && !empty($im['h'])) echo ' width="'.intval($im['w']).'" height="'.intval($im['h']).'"'; ?> alt="<?php echo esc_attr($im['alt'] ?? ''); ?>"<?php echo $i === 0 ? ' fetchpriority="high" decoding="async"' : ' loading="lazy" decoding="async"'; ?>>
            </div>
            <?php endforeach; ?>
          </div>
          <?php if (!$single): ?>
          <div class="hw-eq-dots"><?php foreach ($gallery as $i => $im): ?><button class="hw-eq-dot<?php echo $i === 0 ? ' on' : ''; ?>" type="button" aria-label="Slide <?php echo $i + 1; ?>"></button><?php endforeach; ?></div>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div></section>

  <?php if (!empty($d['inquiryIntro']) || $formId): ?>
  <section class="hw-eq-inq"><div class="hw-eq-inner">
    <?php if (!empty($d['inquiryIntro'])): ?><p class="hw-eq-intro"><strong><?php echo esc_html($d['inquiryIntro']); ?></strong></p><?php endif; ?>
    <?php if ($formId) echo do_shortcode('[gravityform id="'.$formId.'" title="false" description="false" ajax="true"]'); ?>
  </div></section>
  <?php endif; ?>

</main>
<?php if ($gallery && count($gallery) > 1): ?>
<script>(function(){var g=document.getElementById('hw-eq-gal');if(!g)return;var s=[].slice.call(g.querySelectorAll('.hw-eq-slide')),d=[].slice.call(g.querySelectorAll('.hw-eq-dot')),i=0,t;function go(n){s[i].classList.remove('on');d[i]&&d[i].classList.remove('on');i=(n+s.length)%s.length;s[i].classList.add('on');d[i]&&d[i].classList.add('on');}function auto(){t=setInterval(function(){go(i+1);},5000);}d.forEach(function(b,n){b.addEventListener('click',function(){clearInterval(t);go(n);auto();});});if(s.length>1)auto();})();</script>
<?php endif; ?>
<?php get_template_part('template-parts/site-footer'); wp_footer(); ?>
</body>
</html>
