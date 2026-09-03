<?php
/**
 * Divi-free template for the /news/ index. Renders the fleet's article + press-release
 * pages as a styled news index (alternating full-width bands: linked title, excerpt +
 * READ MORE, featured image) to match production. Reuses the shared site header/footer.
 *
 * Source: on this rebuild the news items are PAGES built with the article/press-release
 * templates (there is no native `post` archive for them), so we gather those pages and
 * derive each card's image + excerpt from their stored data.
 */
if(!defined('ABSPATH'))exit;

/** Build one card's image URL + plain-text excerpt from a page's stored data. */
function hw_news_card($post_id){
  $tpl = get_post_meta($post_id,'_wp_page_template',true);
  $img = ''; $raw = '';
  if($tpl==='page-hw-pressrelease.php'){
    $d = json_decode(get_post_meta($post_id,'_hw_pr',true), true);
    if(is_array($d)){ $img = $d['banner_img'] ?? ''; $raw = $d['body_html'] ?? ''; }
  } elseif($tpl==='template-equipment-article.php'){
    $d = get_post_meta($post_id,'_hw_data',true); $d = is_string($d) ? json_decode($d,true) : $d;
    if(is_array($d)){ $img = $d['hero'] ?? ''; $raw = $d['body'] ?? ''; }
  } else { // page-hw-article.php
    $slug = get_post_field('post_name', $post_id);
    $f = get_stylesheet_directory().'/hw-articles/'.$slug.'.php';
    if(is_readable($f)){
      $d = include $f;
      if(is_array($d)){
        if(!empty($d['hero']['img'])) $img = $d['hero']['img'];
        if(!empty($d['blocks'])){
          foreach($d['blocks'] as $b){
            if(!$img && ($b['t']??'')==='imgrow' && !empty($b['imgs'][0]['src'])) $img = $b['imgs'][0]['src'];
            if(($b['t']??'')==='html' && !empty($b['html'])) $raw .= ' '.$b['html'];
            if(str_word_count(wp_strip_all_tags($raw)) > 160) break;
          }
        }
      }
    }
  }
  // Excerpt: match production — show the first real body paragraph(s) in FULL, ending on a
  // sentence boundary (never a mid-sentence "…" cut), and INCLUDE the press-release dateline
  // (the date prod shows). Source structure is inconsistent (headline/sub can be <p> OR <h2>,
  // the dateline can sit in an <h1>/<h2> or be glued to "FOR IMMEDIATE RELEASE"), so we:
  //   1) build ordered [tag,text] blocks, stripping inner tags WITHOUT spaces (keeps dropcaps:
  //      "F<span>ORT</span>" -> "FORT") and dropping the "FOR IMMEDIATE RELEASE" label;
  //   2) start at the first date-bearing block (the dateline) if one appears near the top,
  //      else the first substantive body paragraph;
  //   3) assemble forward, skipping headline/sub-headline headings + title duplicates, to ~40 words;
  //   4) trim to whole sentences.
  $blocks = array();
  if(preg_match_all('#<(p|h1|h2|h3|h4)\b[^>]*>(.*?)</\1>#is', (string)$raw, $bmm, PREG_SET_ORDER)){
    foreach($bmm as $bm){
      $tag  = strtolower($bm[1]);
      $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($bm[2]), ENT_QUOTES)));
      $text = trim(preg_replace('/^(for immediate release|press release)\s*[:\-\x{2013}\x{2014}]?\s*/iu', '', $text));
      if($text !== '') $blocks[] = array($tag, $text);
    }
  }
  if(!$blocks){ // no block tags — fall back to flattened body text
    $flat = trim(preg_replace('/\s+/', ' ', html_entity_decode(preg_replace('/<[^>]+>/', ' ', (string)$raw), ENT_QUOTES)));
    if($flat !== '') $blocks[] = array('p', $flat);
  }
  $title  = trim(wp_strip_all_tags(get_the_title($post_id)));
  $dateRe = '/\b(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]*\.?\s+\d{1,2}(?:st|nd|rd|th)?,?\s+\d{4}\b|\b\d{1,2}(?:st|nd|rd|th)?\s+(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]*,?\s+\d{4}\b|\b(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]*\.?\s+\d{4}\b/i';
  $is_titlish = function($t) use ($title){ return $title !== '' && stripos($title, $t) !== false && str_word_count($t) <= str_word_count($title); };
  $is_skip_head = function($tag,$t) use ($dateRe){ return in_array($tag, array('h1','h2','h3','h4'), true) && !preg_match($dateRe,$t) && str_word_count($t) < 25; };
  $n = count($blocks); $start = -1;
  for($i=0;$i<min($n,6);$i++){ if(preg_match($dateRe,$blocks[$i][1])){ $start=$i; break; } }
  if($start < 0){
    for($i=0;$i<$n;$i++){ list($tg,$tx)=$blocks[$i];
      if($is_titlish($tx) || $is_skip_head($tg,$tx) || str_word_count($tx) < 6) continue; $start=$i; break; }
    if($start < 0) $start = 0;
  }
  $acc = '';
  for($i=$start;$i<$n && str_word_count($acc) < 40;$i++){ list($tg,$tx)=$blocks[$i];
    if($i > $start && ($is_titlish($tx) || $is_skip_head($tg,$tx))) continue;
    $acc = ($acc==='') ? $tx : $acc.' '.$tx;
  }
  $excerpt = trim($acc);
  if(str_word_count($excerpt) > 80 && preg_match_all('/.*?[.!?](?=\s|$)/u', $excerpt, $sm) && !empty($sm[0])){
    $out = '';
    foreach($sm[0] as $s){ $cand = $out==='' ? trim($s) : $out.' '.trim($s); if(str_word_count($cand) > 72 && $out !== '') break; $out = $cand; }
    if($out !== '') $excerpt = $out;
  }
  if(str_word_count($excerpt) > 95) $excerpt = wp_trim_words($excerpt, 95, '…');
  $img = preg_replace('#https?://(www\.)?vac2go\.com#i', '', (string)$img);
  return array('img'=>$img, 'excerpt'=>trim($excerpt));
}

$hw_news = new WP_Query(array(
  'post_type'      => 'page',
  'post_status'    => 'publish',
  'posts_per_page' => -1,
  'orderby'        => 'date',
  'order'          => 'DESC',
  'no_found_rows'  => true,
  'meta_query'     => array(array(
    'key'     => '_wp_page_template',
    'value'   => array('page-hw-article.php','template-equipment-article.php','page-hw-pressrelease.php'),
    'compare' => 'IN',
  )),
));
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style id="hw-critical">:root{--red:#e01f30;--red-h:#dd3333}*{box-sizing:border-box}body.hw-page{margin:0;font-family:"Open Sans",Helvetica,Arial,sans-serif;color:#2d2d2d;overflow-x:hidden;padding-top:122px;background:#fff}.hw-wrap{width:100%;max-width:1350px;margin:0 auto;padding:0 30px}img{max-width:100%}#hw-header{position:fixed;top:0;left:0;right:0;z-index:99999}.hw-topbar{background:#444;height:47px;display:flex;align-items:center}.hw-topbar .hw-wrap{display:flex;align-items:center;justify-content:flex-start;gap:26px}.hw-topbar .hw-phone,.hw-topbar .hw-email{font-family:ABeeZee,Helvetica,Arial,sans-serif;font-size:18px;line-height:18px;color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:8px}.hw-topbar .hw-phone{font-weight:600}.hw-topbar .hw-email{font-weight:700}.hw-mainbar{background:#000;height:75px;display:flex;align-items:center}.hw-mainbar .hw-wrap{display:flex;align-items:flex-end;justify-content:space-between;height:75px}.hw-logo{display:flex;align-items:center;align-self:center;margin-left:4px}.hw-logo img{height:53px;width:auto;display:block}.hw-nav{display:flex;align-items:flex-end;gap:24px}.hw-nav ul{display:flex;align-items:flex-end;gap:22px;list-style:none;margin:0;padding:0}.hw-nav ul a{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:40px;color:#fff;text-decoration:none;text-transform:uppercase;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;padding:0 10px}.hw-caret{display:inline-block;width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid currentColor;opacity:.85}.hw-reserve{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:16px;color:#fff;text-decoration:none;text-transform:uppercase;background:var(--red);padding:12px 18px;border-radius:4px;display:inline-flex;align-items:center;gap:8px;white-space:nowrap}.hw-burger{display:none;flex-direction:column;justify-content:center;gap:5px;width:56px;height:44px;padding:0 13px;background:#4a4a4a;border:0;border-radius:6px;cursor:pointer}.hw-burger span{display:block;height:3px;width:100%;background:#fff;border-radius:2px}.hw-mobmenu{display:none}
/* page: news index */
.hw-news-item{padding:57.6px 0}
.hw-news-item:nth-child(odd){background:#f4f4f4}
.hw-news-item:nth-child(even){background:#fff}
.hw-news-wrap{max-width:1212px;margin:0 auto;padding:0 30px}
.hw-news-title{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:36px;line-height:1.1;font-weight:700;color:rgba(10,0,0,.75);margin:0 0 40px}
.hw-news-title a{color:inherit;text-decoration:none}
.hw-news-title a:hover{color:var(--red)}
.hw-news-row{display:flex;gap:64px;align-items:flex-start}
.hw-news-text{flex:1 1 0;min-width:0}
.hw-news-media{flex:1 1 0;min-width:0}
.hw-news-item.hw-news-noimg .hw-news-text{flex:1 1 100%}
.hw-news-text p{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:17px;line-height:23.8px;font-weight:500;color:#000;margin:0 0 20px}
.hw-news-media img{display:block;width:100%;height:auto;aspect-ratio:3/2;object-fit:cover}
.hw-news-btn{display:inline-block;background:var(--red);color:#fff;border:2px solid #bababa;border-radius:3px;font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:20px;font-weight:500;line-height:1.7em;padding:6px 20px;text-decoration:none;margin-top:6px}
.hw-news-btn:hover{color:#fff;background:var(--red-h)}
.hw-news-empty{max-width:1212px;margin:0 auto;padding:80px 30px;font-size:18px;color:#666;text-align:center}
.hw-footer-bar{background:var(--red);color:#fff;font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:13px;line-height:1.5;text-align:center;padding:13px 20px}.hw-footer-bar a{color:#fff;text-decoration:underline}
@media (max-width:767px){.hw-nav{display:none}.hw-burger{display:flex}body.hw-page{padding-top:149px}.hw-topbar{height:auto;min-height:85px;padding:9px 0}.hw-topbar .hw-wrap{flex-direction:row;flex-wrap:wrap;justify-content:center;gap:4px 14px;text-align:center;padding:0 16px}.hw-topbar .hw-phone,.hw-topbar .hw-email{justify-content:center}.hw-mainbar{height:64px}.hw-mainbar .hw-wrap{align-items:center}.hw-logo{align-self:center}.hw-logo img{height:35px}.hw-news-title{font-size:26px;margin:0 0 24px}.hw-news-row{flex-direction:column;gap:22px}.hw-news-media{order:-1}}</style>
<link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css" media="print" onload="this.media='all';this.onload=null">
<noscript><link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css"></noscript>
<?php wp_head(); ?>
</head>
<body <?php body_class('hw-page hw-news-page'); ?>>
<?php get_template_part('template-parts/site-header'); ?>
<main id="hw-main">
<?php if ($hw_news->have_posts()) : while ($hw_news->have_posts()) : $hw_news->the_post();
  $hw_c = hw_news_card(get_the_ID());
  $hw_noimg = empty($hw_c['img']) ? ' hw-news-noimg' : '';
  ?>
  <section class="hw-news-item<?php echo $hw_noimg; ?>">
    <div class="hw-news-wrap">
      <h2 class="hw-news-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
      <div class="hw-news-row">
        <div class="hw-news-text">
          <?php if ($hw_c['excerpt'] !== '') : ?><p><?php echo esc_html($hw_c['excerpt']); ?></p><?php endif; ?>
          <a class="hw-news-btn" href="<?php the_permalink(); ?>">READ MORE</a>
        </div>
        <?php if (!empty($hw_c['img'])) : ?>
        <div class="hw-news-media">
          <img src="<?php echo esc_url($hw_c['img']); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy" decoding="async">
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
<?php endwhile; wp_reset_postdata(); else : ?>
  <p class="hw-news-empty">No news items yet.</p>
<?php endif; ?>
</main>
<?php get_template_part('template-parts/site-footer'); wp_footer(); ?>
</body>
</html>
