<?php
/* Template Name: HW Location
 * Divi-free state location page. Data-driven from the _hw_data post meta (see hw-loc-extract.js).
 * Owns: this file + assets/css/location.css. Reuses shared site-header / site-footer. */
if(!defined('ABSPATH'))exit;
$pid = get_the_ID();
$raw = get_post_meta($pid,'_hw_data',true);
$d   = $raw ? json_decode($raw,true) : null;
$css = @file_get_contents(get_stylesheet_directory().'/assets/css/location.css');

$strip = function($h){ return $h===null?'':preg_replace('#https?://(www\.)?vac2go\.com#i','',$h); };
$frac  = function($w){ if(strpos((string)$w,'_')!==false){ list($a,$b)=explode('_',$w); if($b>0) return $a/$b; } return 1; };
$gform = ($d && !empty($d['gform_id'])) ? preg_replace('/\D/','',$d['gform_id']) : '4';
$hero_img = ($d && !empty($d['hero_img'])) ? $strip($d['hero_img']) : '';
$hero_webp = ($hero_img && !preg_match('/\.avif$/i',$hero_img)) ? $hero_img.'.webp' : '';

$render_rows = function($rows) use ($frac,$strip){
  if(empty($rows)) return;
  foreach($rows as $row){
    $cols = isset($row['cols'])?$row['cols']:[];
    $widths = isset($row['colWidths'])?$row['colWidths']:[];
    if(empty($cols)) continue;
    $tpl=[]; foreach($cols as $i=>$c){ $w=isset($widths[$i])?$widths[$i]:''; $tpl[]=round($frac($w)*1000).'fr'; }
    $gt = count($cols)>1 ? implode(' ',$tpl) : '1fr';
    echo '<div class="hw-loc-row" style="grid-template-columns:'.$gt.'">';
    foreach($cols as $c){
      echo '<div class="hw-loc-col">';
      if(isset($c['map']) && $c['map']){
        echo '<iframe class="hw-loc-map" src="'.esc_url($c['map']).'" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Location map"></iframe>';
      } elseif(isset($c['html'])){
        echo $strip($c['html']);
      }
      echo '</div>';
    }
    echo '</div>';
  }
};

/* shared association logos (identical across every location page) */
$logos = ($d && !empty($d['logo_rows'])) ? $d['logo_rows'] : [
  ['src'=>'/wp-content/uploads/2024/05/argosy.jpg','alt'=>'Argosy'],
  ['src'=>'/wp-content/uploads/2024/05/NASTT-Logo.jpg','alt'=>'NASTT'],
  ['src'=>'/wp-content/uploads/2024/05/wjta-logo.jpg','alt'=>'WJTA'],
  ['src'=>'/wp-content/uploads/2025/10/UTCA_New-logo-white-circle-300x291.jpg','alt'=>'UTCA'],
  ['src'=>'/wp-content/uploads/2026/03/AZUCA_logo-300x164.png','alt'=>'AZUCA'],
  ['src'=>'/wp-content/uploads/2026/07/Sam.gov-Vendor-300x275.png','alt'=>'SAM.gov Vendor'],
  ['src'=>'/wp-content/uploads/2024/05/chamber-logo.jpg','alt'=>'Chamber of Commerce'],
  ['src'=>'/wp-content/uploads/2025/07/BIF-Logo.jpg','alt'=>'Best In Finance'],
  ['src'=>'/wp-content/uploads/2024/05/Fast-5000.jpg','alt'=>'Fast 5000'],
  ['src'=>'/wp-content/uploads/2024/05/Fast-50-1.jpg','alt'=>'Fast 50'],
];
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="preload" as="font" type="font/woff2" href="/wp-content/hw-fonts/f5.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/wp-content/hw-fonts/f32.woff2" crossorigin>
<?php if($hero_webp): ?><link rel="preload" as="image" href="<?php echo esc_url($hero_webp); ?>" type="image/webp" fetchpriority="high"><?php elseif($hero_img): ?><link rel="preload" as="image" href="<?php echo esc_url($hero_img); ?>" fetchpriority="high"><?php endif; ?>
<style id="hw-critical"><?php echo $css; ?></style>
<link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css" media="print" onload="this.media='all';this.onload=null">
<noscript><link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css"></noscript>
<?php wp_head(); ?>
</head>
<body <?php body_class('hw-page hw-loc'); ?>>
<?php get_template_part('template-parts/site-header'); ?>
<main id="hw-main">

  <?php if($d): ?>
  <section class="hw-loc-hero">
    <?php if($hero_img): ?><picture class="hw-loc-hero-pic"><?php if($hero_webp): ?><source type="image/webp" srcset="<?php echo esc_url($hero_webp); ?>"><?php endif; ?><img class="hw-loc-hero-bg" src="<?php echo esc_url($hero_img); ?>" alt="" fetchpriority="high" decoding="async"></picture><?php endif; ?>
    <div class="hw-loc-hero-inner">
      <?php if(!empty($d['hero_title'])): ?><h2 class="hw-loc-hero-title"><?php echo esc_html($d['hero_title']); ?></h2><?php endif; ?>
      <?php if(!empty($d['hero_intro_html'])): ?><div class="hw-loc-hero-intro"><?php echo $strip($d['hero_intro_html']); ?></div><?php endif; ?>
      <?php if(!empty($d['hero_btn_text'])): ?><a class="hw-loc-hero-btn" href="<?php echo esc_attr($d['hero_btn_href']?:'#contact'); ?>"><?php echo esc_html($d['hero_btn_text']); ?></a><?php endif; ?>
    </div>
  </section>

  <?php if(!empty($d['redbar_html'])): ?>
  <section class="hw-loc-redbar"><div class="hw-loc-wrap"><?php echo $strip($d['redbar_html']); ?></div></section>
  <?php endif; ?>

  <?php if(!empty($d['contact_rows'])): ?>
  <section id="contact" class="hw-loc-contact"><div class="hw-loc-wrap"><?php $render_rows($d['contact_rows']); ?></div></section>
  <?php endif; ?>

  <?php if(!empty($d['content_sections'])): foreach($d['content_sections'] as $csec): if(empty($csec)) continue; ?>
  <section class="hw-loc-content"><div class="hw-loc-wrap"><?php $render_rows($csec); ?></div></section>
  <?php endforeach; endif; ?>

  <section class="hw-loc-form"><div class="hw-loc-wrap">
    <div class="hw-loc-row" style="grid-template-columns:1fr 1fr">
      <div class="hw-loc-col">
        <?php if(!empty($d['form_title_html'])): ?><div class="hw-loc-form-title"><?php echo $strip($d['form_title_html']); ?></div><?php endif; ?>
        <?php if(!empty($d['form_intro_html'])): ?><div class="hw-loc-form-intro"><?php echo $strip($d['form_intro_html']); ?></div><?php endif; ?>
      </div>
      <div class="hw-loc-col"><?php echo do_shortcode('[gravityform id="'.$gform.'" title="false" description="false" ajax="true"]'); ?></div>
    </div>
  </div></section>
  <?php endif; /* $d */ ?>

</main>
<?php get_template_part('template-parts/site-footer'); wp_footer(); ?>
</body>
</html>
