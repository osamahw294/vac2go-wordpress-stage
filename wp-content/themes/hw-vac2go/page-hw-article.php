<?php
/**
 * Template Name: HW Article
 *
 * Divi-free article template for the Blog + News/press-release buckets.
 * Loads per-slug content+typography from hw-articles/<slug>.php and renders it
 * through the shared header/footer. Typography is parametrized per page via
 * --art-* custom properties (measured from production) set inline on .hw-art.
 */
if(!defined('ABSPATH'))exit;

$hw_slug = get_post_field('post_name', get_queried_object_id());
$hw_file = get_stylesheet_directory().'/hw-articles/'.$hw_slug.'.php';
$hw = is_readable($hw_file) ? include $hw_file : null;
if(!is_array($hw)){ $hw = array('style'=>'','hero'=>null,'blocks'=>array()); }
$dir = get_stylesheet_directory_uri();
$ver = @filemtime(get_stylesheet_directory().'/assets/css/article.css');
// Above-the-fold image = LCP candidate (hero bg, else a leading full-bleed image). Preload it.
$hw_lcp = '';
if (!empty($hw['hero']['img'])) {
  $hw_lcp = $hw['hero']['img'];
} elseif (!empty($hw['blocks'][0]['fullbleed']) && !empty($hw['blocks'][0]['imgs'][0]['src'])) {
  $hw_lcp = $hw['blocks'][0]['imgs'][0]['src'];
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="preload" as="font" type="font/woff2" href="/wp-content/hw-fonts/f5.woff2" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="/wp-content/hw-fonts/f32.woff2" crossorigin>
<?php if($hw_lcp){ echo '<link rel="preload" as="image" href="'.esc_url($hw_lcp).'" fetchpriority="high">'."\n"; } ?>
<style id="hw-critical">:root{--red:#e01f30;--red-h:#dd3333}*{box-sizing:border-box}body.hw-page{margin:0;font-family:"Open Sans",Helvetica,Arial,sans-serif;color:#2d2d2d;overflow-x:hidden;padding-top:122px;background:#fff}.hw-wrap{width:100%;max-width:1350px;margin:0 auto;padding:0 30px}img{max-width:100%}#hw-header{position:fixed;top:0;left:0;right:0;z-index:99999}.hw-topbar{background:#444;height:47px;display:flex;align-items:center}.hw-topbar .hw-wrap{display:flex;align-items:center;justify-content:flex-start;gap:26px}.hw-topbar .hw-phone,.hw-topbar .hw-email{font-family:ABeeZee,Helvetica,Arial,sans-serif;font-size:18px;line-height:18px;color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:8px}.hw-topbar .hw-phone{font-weight:600}.hw-topbar .hw-email{font-weight:700}.hw-mainbar{background:#000;height:75px;display:flex;align-items:center}.hw-mainbar .hw-wrap{display:flex;align-items:flex-end;justify-content:space-between;height:75px}.hw-logo{display:flex;align-items:center;align-self:center;margin-left:4px}.hw-logo img{height:53px;width:auto;display:block}.hw-nav{display:flex;align-items:flex-end;gap:24px}.hw-nav ul{display:flex;align-items:flex-end;gap:22px;list-style:none;margin:0;padding:0}.hw-nav ul a{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:40px;color:#fff;text-decoration:none;text-transform:uppercase;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;padding:0 10px}.hw-caret{display:inline-block;width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid currentColor;opacity:.85}.hw-reserve{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:16px;color:#fff;text-decoration:none;text-transform:uppercase;background:var(--red);padding:12px 18px;border-radius:4px;display:inline-flex;align-items:center;gap:8px;white-space:nowrap}.hw-burger{display:none;flex-direction:column;justify-content:center;gap:5px;width:56px;height:44px;padding:0 13px;background:#4a4a4a;border:0;border-radius:6px;cursor:pointer}.hw-burger span{display:block;height:3px;width:100%;background:#fff;border-radius:2px}.hw-mobmenu{display:none}.hw-art-hero{width:100%;height:600px;background-position:center center;background-size:cover;background-repeat:no-repeat}.hw-art-fw{margin:0;width:100%;line-height:0}.hw-art-fw img{display:block;width:100%;height:auto}.hw-art-sec{padding:0 0 86px}.hw-art-row{padding:0}.hw-art-wrap{width:80%;max-width:1300px;margin:0 auto}.hw-art-imgwrap{width:90%;max-width:1300px;margin:0 auto}.hw-art{font-family:"Open Sans",Helvetica,Arial,sans-serif;text-align:left}.hw-art h1{font-size:36px;font-weight:700;line-height:36px;color:rgba(10,0,0,.75);text-align:left;margin:0;padding:0 0 10px}.hw-art h2{font-size:var(--art-h2-fs,26px);font-weight:var(--art-h2-fw,500);line-height:var(--art-h2-lh,26px);color:var(--art-h2-color,#333);text-align:left;margin:0;padding:0 0 10px}.hw-art h3{font-size:var(--art-h3-fs,22px);font-weight:var(--art-h3-fw,500);line-height:var(--art-h3-lh,22px);color:var(--art-h3-color,#333);text-align:left;margin:0;padding:0 0 10px}.hw-art h4{font-size:var(--art-h4-fs,18px);font-weight:var(--art-h4-fw,500);line-height:var(--art-h4-lh,18px);color:var(--art-h4-color,#333);text-align:left;margin:0;padding:0 0 10px}.hw-art p{font-size:var(--art-p-fs,17px);font-weight:var(--art-p-fw,500);line-height:var(--art-p-lh,23.8px);color:var(--art-p-color,#000);margin:0;padding:0 0 1em}.hw-art ul,.hw-art ol{font-size:var(--art-li-fs,17px);line-height:var(--art-li-lh,26px);color:var(--art-li-color,#000);list-style:disc;margin:0;padding:0 0 1em 17px}.hw-art ol{list-style:decimal}.hw-art li{font-size:var(--art-li-fs,17px);font-weight:var(--art-li-fw,400);line-height:var(--art-li-lh,26px);color:var(--art-li-color,#000);margin:0;padding:0}.hw-art a{color:var(--art-a-color,#e01f30);text-decoration:none}.hw-art a:hover{text-decoration:underline}.hw-art img{max-width:100%;height:auto}.hw-art figure{margin:0}.hw-art::after{content:"";display:table;clear:both}.hw-art .alignright{float:right;margin:0 0 0 15px;max-width:100%;height:auto}.hw-art .alignleft{float:left;margin:0 15px 0 0;max-width:100%;height:auto}.hw-art .aligncenter{display:block;margin-left:auto;margin-right:auto;height:auto}.hw-art .alignnone{max-width:100%;height:auto}.hw-art-imgrow{display:flex;margin:0;flex-wrap:wrap;align-items:flex-start}.hw-art-imgrow figure{margin:0;line-height:0}.hw-art-imgrow img{display:block;width:100%;height:auto}.hw-art-imgrow--fill{gap:0}.hw-art-imgrow--fill figure{flex:1 1 0;min-width:0}.hw-art-imgrow--sized{gap:20px;justify-content:center}.hw-art-imgrow--sized figure{flex:0 0 auto;max-width:100%}.hw-art-cols{display:flex;gap:30px;align-items:center}.hw-art-col{min-width:0}.hw-art-col>*:last-child{padding-bottom:0}.hw-art-col figure{margin:0;text-align:center}.hw-art-col img{max-width:100%;height:auto;display:inline-block}.hw-footer-bar{background:var(--red);color:#fff;font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:13px;line-height:1.5;text-align:center;padding:13px 20px}.hw-footer-bar a{color:#fff;text-decoration:underline}@media (max-width:980px){.hw-art-wrap{width:88%}}@media (max-width:767px){.hw-nav{display:none}.hw-burger{display:flex}body.hw-page{padding-top:149px}.hw-topbar{height:auto;min-height:85px;padding:9px 0}.hw-topbar .hw-wrap{flex-direction:row;flex-wrap:wrap;justify-content:center;gap:4px 14px;text-align:center;padding:0 16px}.hw-topbar .hw-phone,.hw-topbar .hw-email{justify-content:center}.hw-mainbar{height:64px}.hw-mainbar .hw-wrap{align-items:center}.hw-logo{align-self:center}.hw-logo img{height:35px}.hw-art-imgwrap{width:100%}.hw-art-imgrow--fill{flex-direction:column;gap:0}.hw-art-imgrow--sized{flex-direction:column;gap:16px;align-items:center}.hw-art-cols{flex-direction:column;gap:14px;align-items:stretch}.hw-art .alignright,.hw-art .alignleft{float:none;display:block;margin:0 auto 1em}}.hw-art .hw-art-btn{display:inline-block;background:var(--red);color:#fff;border:2px solid #fff;border-radius:3px;font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:20px;font-weight:500;line-height:1.7em;padding:6px 20px;text-decoration:none;text-transform:none}.hw-art .hw-art-btn:hover{text-decoration:none;color:#fff}.hw-art .hw-art-btn--lg{font-size:22px;padding:6.6px 22px}@media (min-width:768px){.hw-art-fw--crop{height:var(--hw-crop,721px);overflow:hidden}.hw-art-fw--crop img{width:100%;height:var(--hw-crop,721px);object-fit:cover;object-position:50% 0}}</style>
<link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css" media="print" onload="this.media='all';this.onload=null">
<link rel="stylesheet" href="<?php echo $dir; ?>/assets/css/home.css?v=<?php echo @filemtime(get_stylesheet_directory().'/assets/css/home.css'); ?>" media="print" onload="this.media='all';this.onload=null">
<link rel="stylesheet" href="<?php echo $dir; ?>/assets/css/article.css?v=<?php echo $ver; ?>" media="print" onload="this.media='all';this.onload=null">
<noscript><link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css"><link rel="stylesheet" href="<?php echo $dir; ?>/assets/css/home.css"><link rel="stylesheet" href="<?php echo $dir; ?>/assets/css/article.css"></noscript>
<?php wp_head(); ?>
</head>
<body <?php body_class('hw-page hw-art-page'); ?>>
<?php get_template_part('template-parts/site-header'); ?>
<main id="hw-main">
<?php
// Hero (background-image banner) if present.
if (!empty($hw['hero']) && !empty($hw['hero']['img'])) {
  $hh = !empty($hw['hero']['h']) ? (int)$hw['hero']['h'] : 600;
  printf('<div class="hw-art-hero" role="img" aria-label="%s" style="height:%dpx;background-image:url(%s)"></div>',
    esc_attr($hw['title'] ?? ''), $hh, esc_url($hw['hero']['img']));
}
$style = isset($hw['style']) ? $hw['style'] : '';
$blocks = isset($hw['blocks']) && is_array($hw['blocks']) ? $hw['blocks'] : array();

// Each captured module renders as its own Divi-style row inside one section.
// Full-bleed images break out of the section entirely (like a hero banner).
$secPB = isset($hw['secPB']) ? (int)$hw['secPB'] : 86; // baked trailing whitespace
$secOpen = false;
$openSec = function() use (&$secOpen,$secPB){ if(!$secOpen){ echo '<section class="hw-art-sec" style="padding-bottom:'.$secPB.'px">'; $secOpen=true; } };
$closeSec = function() use (&$secOpen){ if($secOpen){ echo '</section>'; $secOpen=false; } };

foreach ($blocks as $b) {
  $t = isset($b['t']) ? $b['t'] : 'html';
  $mt = isset($b['mt']) ? (int)$b['mt'] : 0;           // baked exact top-gap from production
  $rowStyle = ' style="padding-top:'.$mt.'px"';
  if ($t==='imgrow' && !empty($b['fullbleed'])) {
    $closeSec();
    $im = $b['imgs'][0];
    $cropH = !empty($im['crop']) ? (int)$im['crop'] : 0;
    $fwCls = $cropH ? 'hw-art-fw hw-art-fw--crop' : 'hw-art-fw';
    $fwStyle = 'margin-top:'.$mt.'px'.($cropH ? ';--hw-crop:'.$cropH.'px' : '');
    printf('<figure class="%s" style="%s"><img src="%s" alt="%s" width="%d" height="%d" decoding="async"></figure>',
      $fwCls, $fwStyle, esc_url($im['src']), esc_attr($im['alt']), (int)$im['w'], (int)$im['h']);
  } elseif ($t==='imgrow') {
    $openSec();
    $fill = !empty($b['fill']);
    $cls = $fill ? 'hw-art-imgrow hw-art-imgrow--fill' : 'hw-art-imgrow hw-art-imgrow--sized';
    echo '<div class="hw-art-row"'.$rowStyle.'><div class="hw-art-imgwrap"><div class="'.$cls.'">';
    foreach ($b['imgs'] as $im) {
      // Every figure carries its production display width; fill rows shrink to fit
      // (space-between yields the right gaps), sized rows stay at that width.
      printf('<figure style="width:%dpx"><img src="%s" alt="%s" width="%d" height="%d" loading="lazy" decoding="async"></figure>',
        (int)$im['dw'], esc_url($im['src']), esc_attr($im['alt']), (int)$im['w'], (int)$im['h']);
    }
    echo '</div></div></div>';
  } elseif ($t==='cols') {
    $openSec();
    echo '<div class="hw-art-row"'.$rowStyle.'><div class="hw-art-wrap"><article class="hw-art" style="'.esc_attr($style).'"><div class="hw-art-cols">';
    foreach ($b['cols'] as $c) {
      $w = max(1,(int)($c['w'] ?? 1));
      echo '<div class="hw-art-col" style="flex:'.$w.' 1 0px">';
      if (($c['type'] ?? '')==='img') {
        $im = $c['img'];
        printf('<figure><img src="%s" alt="%s" width="%d" height="%d" loading="lazy" decoding="async"></figure>',
          esc_url($im['src']), esc_attr($im['alt']), (int)$im['w'], (int)$im['h']);
      } else {
        echo $c['html']; // trusted
      }
      echo '</div>';
    }
    echo '</div></article></div></div>';
  } else { // html — one module = one row
    $openSec();
    echo '<div class="hw-art-row"'.$rowStyle.'><div class="hw-art-wrap"><article class="hw-art" style="'.esc_attr($style).'">';
    echo $b['html']; // trusted: extracted from production content
    echo '</article></div></div>';
  }
}
$closeSec();
?>
</main>
<?php get_template_part('template-parts/site-footer'); wp_footer(); ?>
</body>
</html>
