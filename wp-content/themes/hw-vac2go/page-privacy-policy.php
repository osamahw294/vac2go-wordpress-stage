<?php
/**
 * Divi-free template for the Privacy Policy page (and any standard legal page assigned
 * to slug `privacy-policy`). Two-column: prose content + right sidebar (Search, Recent
 * Posts, Recent Comments) to match production. Reuses the shared site header/footer.
 */
if(!defined('ABSPATH'))exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style id="hw-critical">:root{--red:#e01f30;--red-h:#dd3333}*{box-sizing:border-box}body.hw-page{margin:0;font-family:"Open Sans",Helvetica,Arial,sans-serif;color:#2d2d2d;overflow-x:hidden;padding-top:122px;background:#fff}.hw-wrap{width:100%;max-width:1350px;margin:0 auto;padding:0 30px}img{max-width:100%}#hw-header{position:fixed;top:0;left:0;right:0;z-index:99999}.hw-topbar{background:#444;height:47px;display:flex;align-items:center}.hw-topbar .hw-wrap{display:flex;align-items:center;justify-content:flex-start;gap:26px}.hw-topbar .hw-phone,.hw-topbar .hw-email{font-family:ABeeZee,Helvetica,Arial,sans-serif;font-size:18px;line-height:18px;color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:8px}.hw-topbar .hw-phone{font-weight:600}.hw-topbar .hw-email{font-weight:700}.hw-mainbar{background:#000;height:75px;display:flex;align-items:center}.hw-mainbar .hw-wrap{display:flex;align-items:flex-end;justify-content:space-between;height:75px}.hw-logo{display:flex;align-items:center;align-self:center;margin-left:4px}.hw-logo img{height:53px;width:auto;display:block}.hw-nav{display:flex;align-items:flex-end;gap:24px}.hw-nav ul{display:flex;align-items:flex-end;gap:22px;list-style:none;margin:0;padding:0}.hw-nav ul a{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:40px;color:#fff;text-decoration:none;text-transform:uppercase;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;padding:0 10px}.hw-caret{display:inline-block;width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid currentColor;opacity:.85}.hw-reserve{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:16px;color:#fff;text-decoration:none;text-transform:uppercase;background:var(--red);padding:12px 18px;border-radius:4px;display:inline-flex;align-items:center;gap:8px;white-space:nowrap}.hw-burger{display:none;flex-direction:column;justify-content:center;gap:5px;width:56px;height:44px;padding:0 13px;background:#4a4a4a;border:0;border-radius:6px;cursor:pointer}.hw-burger span{display:block;height:3px;width:100%;background:#fff;border-radius:2px}.hw-mobmenu{display:none}
/* page: privacy / legal */
.hw-legal-outer{max-width:1212px;margin:0 auto;padding:44px 30px 64px}
.hw-legal{display:flex;gap:0;align-items:flex-start}
.hw-legal-main{flex:1 1 auto;min-width:0;padding-right:64px}
.hw-legal-side{flex:0 0 240px}
.hw-legal-main{font-family:"Open Sans",Helvetica,Arial,sans-serif}
.hw-legal-title{font-size:30px;line-height:1;font-weight:500;color:#333;margin:0 0 20px}
.hw-legal-main h2{font-size:26px;line-height:1.05;font-weight:500;color:#333;margin:22px 0 0}
.hw-legal-main h3{font-size:20px;line-height:1.2;font-weight:600;color:#333;margin:18px 0 0}
.hw-legal-main p{font-size:14px;line-height:23.8px;font-weight:500;color:#666;margin:0 0 24px}
.hw-legal-main strong,.hw-legal-main b{font-weight:700;color:#333}
.hw-legal-main a{color:var(--red);text-decoration:none}
.hw-legal-main a:hover{text-decoration:underline}
.hw-legal-main ul,.hw-legal-main ol{font-size:14px;line-height:23.8px;color:#666;margin:0 0 24px;padding:0 0 0 30px}
.hw-legal-main li{font-size:14px;line-height:23.8px;font-weight:500;color:#666;margin:0 0 4px}
.hw-legal-side .hw-search{display:flex;border:1px solid #d6d6d6;margin:0 0 30px}
.hw-legal-side .hw-search input[type=search]{flex:1;min-width:0;border:0;padding:9px 12px;font-size:14px;font-family:"Open Sans",Helvetica,Arial,sans-serif;background:#fff;color:#333}
.hw-legal-side .hw-search button{border:0;background:#f2f2f2;color:#333;padding:0 16px;font-size:13px;cursor:pointer;font-family:"Open Sans",Helvetica,Arial,sans-serif}
.hw-legal-side h3{font-size:18px;line-height:1.2;font-weight:600;color:#333;margin:0 0 14px}
.hw-widget{margin:0 0 30px}
.hw-widget ul{list-style:none;margin:0;padding:0}
.hw-widget li{margin:0 0 12px;font-size:14px;line-height:1.4}
.hw-widget a{color:#666;text-decoration:none}
.hw-widget a:hover{color:var(--red)}
.hw-widget .hw-empty{font-size:14px;color:#999}
.hw-footer-bar{background:var(--red);color:#fff;font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:13px;line-height:1.5;text-align:center;padding:13px 20px}.hw-footer-bar a{color:#fff;text-decoration:underline}
@media (max-width:767px){.hw-nav{display:none}.hw-burger{display:flex}body.hw-page{padding-top:149px}.hw-topbar{height:auto;min-height:85px;padding:9px 0}.hw-topbar .hw-wrap{flex-direction:row;flex-wrap:wrap;justify-content:center;gap:4px 14px;text-align:center;padding:0 16px}.hw-topbar .hw-phone,.hw-topbar .hw-email{justify-content:center}.hw-mainbar{height:64px}.hw-mainbar .hw-wrap{align-items:center}.hw-logo{align-self:center}.hw-logo img{height:35px}.hw-legal{flex-direction:column}.hw-legal-main{padding-right:0;margin-bottom:36px}.hw-legal-side{flex:1 1 auto;width:100%}.hw-legal-title{font-size:26px}}</style>
<link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css" media="print" onload="this.media='all';this.onload=null">
<noscript><link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css"></noscript>
<?php wp_head(); ?>
</head>
<body <?php body_class('hw-page hw-legal-page'); ?>>
<?php get_template_part('template-parts/site-header'); ?>
<main id="hw-main">
  <div class="hw-legal-outer">
    <div class="hw-legal">
      <div class="hw-legal-main">
        <h1 class="hw-legal-title"><?php the_title(); ?></h1>
        <?php while (have_posts()) : the_post(); the_content(); endwhile; ?>
      </div>
      <aside class="hw-legal-side">
        <form class="hw-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
          <input type="search" name="s" placeholder="Search &hellip;" value="<?php echo esc_attr(get_search_query()); ?>" aria-label="Search">
          <button type="submit">Search</button>
        </form>
        <div class="hw-widget">
          <h3>Recent Posts</h3>
          <?php
          $hw_recent = new WP_Query(array('post_type'=>'post','posts_per_page'=>5,'post_status'=>'publish','ignore_sticky_posts'=>true,'no_found_rows'=>true));
          if ($hw_recent->have_posts()) : ?>
          <ul>
            <?php while ($hw_recent->have_posts()) : $hw_recent->the_post(); ?>
            <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
            <?php endwhile; ?>
          </ul>
          <?php else : ?><p class="hw-empty">No posts yet.</p><?php endif; wp_reset_postdata(); ?>
        </div>
        <div class="hw-widget">
          <h3>Recent Comments</h3>
          <?php
          $hw_comments = get_comments(array('number'=>5,'status'=>'approve'));
          if ($hw_comments) : ?>
          <ul>
            <?php foreach ($hw_comments as $hw_c) : ?>
            <li><a href="<?php echo esc_url(get_comment_link($hw_c)); ?>"><?php echo esc_html(get_the_title($hw_c->comment_post_ID)); ?></a></li>
            <?php endforeach; ?>
          </ul>
          <?php else : ?><p class="hw-empty">No comments to show.</p><?php endif; ?>
        </div>
      </aside>
    </div>
  </div>
</main>
<?php get_template_part('template-parts/site-footer'); wp_footer(); ?>
</body>
</html>
