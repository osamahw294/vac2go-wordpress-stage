<?php if(!defined('ABSPATH'))exit;
/* page-meet-the-team.php — Divi-free rebuild of vac2go /meet-the-team/ (dev copy only) */
$UP='/wp-content/uploads';
$FORMBG=$UP.'/2024/03/Red-Background.jpg';
$PAT=$UP.'/2024/05/4907157-scaled.jpg';
$H='/wp-content/uploads/2026/07/'; // headshots dir
$sales=array(
  array('Chad Naquin','Director of Used Truck & Field Sales','Leads the regional sales manager team and used-truck sales.','(502) 699-4019','cnaquin@vac2go.com','7.png'),
  array('Alexandra Townsend','National Account Manager','Builds and manages national partnerships across every branch.','(856) 955-1350','atownsend@vac2go.com','9.png'),
  array('Bill Morris','Inside Sales Representative','Reaches out to prospects to introduce Vac2Go’s rental options.','(502) 699-4024','bmorris@vac2go.com','8.png'),
  array('Ashleigh Hamilton','Customer Support Specialist','Handles onboarding and keeps your account running smoothly.','(502) 699-4028','ahamilton@vac2go.com','10.png'),
);
$leadership=array(
  array('Chad Kalland','Chief Executive Officer','1.png'),
  array('Kevin Podmore','Chief Operating Officer','Meet-The-Team-Photos-2.png'),
  array('Allison Jamison Woosley','Chief Financial Officer','3.png'),
  array('Dustin Culverhouse','Chief Revenue Officer','4.png'),
);
$ops=array(
  array('Jessica Mobley','Strategic Asset Manager','5.png'),
  array('Tim Capps','Fleet Manager','11.png'),
  array('Caitlin Tudor','Safety Manager','6.png'),
);
$branchteam=array(
  array('ALABAMA','https://vac2go.com/alabama/','Anthony St. Andre','Aaron Baisden'),
  array('ARIZONA','https://vac2go.com/arizona','Jamie Bernzweig','George Keathley'),
  array('FLORIDA','https://vac2go.com/florida','Hunter Geis','Donald Larson'),
  array('GEORGIA','https://vac2go.com/georgia/','Steven St. Andre','Darren Miles'),
  array('INDIANA','https://vac2go.com/indiana/','Patrick Splon','George Limberopoulos'),
  array('KENTUCKY','https://vac2go.com/kentucky/','Jeff Wells','Billy Prather'),
  array('NEW JERSEY','https://vac2go.com/new-jersey/','Jason Mears','Ridge Maignan'),
  array('OHIO','https://vac2go.com/ohio/','Kriss Stepp','Josh Miller'),
  array('SOUTH CAROLINA','https://vac2go.com/south-carolina/','Steven St. Andre','Darren Miles'),
  array('TENNESSEE','https://vac2go.com/tennessee/','Mickey Still','Ray Green'),
  array('TEXAS','https://vac2go.com/texas/','Greg Brown','Darius Brown'),
  array('UTAH','https://vac2go.com/utah/','Layne Williams','LaDell Bishop'),
);
$locations=array(
  array('Alabama','https://vac2go.com/alabama/'),array('Arizona','https://vac2go.com/arizona'),
  array('Florida','https://vac2go.com/florida'),array('Georgia','https://vac2go.com/georgia/'),
  array('Indiana','https://vac2go.com/indiana/'),array('Kentucky','https://vac2go.com/kentucky/'),
  array('New Jersey','https://vac2go.com/new-jersey/'),array('Ohio','https://vac2go.com/ohio/'),
  array('South Carolina','https://vac2go.com/south-carolina/'),array('Tennessee','https://vac2go.com/tennessee/'),
  array('Texas','https://vac2go.com/texas/'),array('Utah','https://vac2go.com/utah/'),
);
$logos=array(
  array($UP.'/2024/05/argosy.jpg',288,219,'Argosy'),
  array($UP.'/2024/05/NASTT-Logo.jpg',288,219,'NASTT Member'),
  array($UP.'/2024/05/wjta-logo.jpg',288,219,'WJTA'),
  array($UP.'/2025/10/UTCA_New-logo-white-circle-300x291.jpg',300,291,'UTCA'),
  array($UP.'/2026/03/AZUCA_logo-300x164.png',300,164,'AZuCA'),
  array($UP.'/2026/07/Sam.gov-Vendor-300x275.png',300,275,'SAM.gov Vendor'),
  array($UP.'/2024/05/chamber-logo.jpg',288,219,'Chamber of Commerce'),
  array($UP.'/2025/07/BIF-Logo.jpg',288,219,'BIF'),
  array($UP.'/2024/05/Fast-5000.jpg',288,219,'Fast 5000'),
  array($UP.'/2024/05/Fast-50-1.jpg',288,219,'Fast 50'),
);
function hw_tcard($m,$H,$detail){ ?>
  <div class="hw-tcard">
    <div class="pho"><img src="<?php echo $H.$m[count($m)-1]; ?>" alt="<?php echo esc_attr($m[0]); ?>" width="1080" height="1080" loading="lazy" decoding="async"></div>
    <p class="nm"><?php echo esc_html($m[0]); ?></p>
    <p class="ttl"><?php echo esc_html($m[1]); ?></p>
    <?php if($detail): ?>
      <p class="blurb"><?php echo esc_html($m[2]); ?></p>
      <hr>
      <p class="ph"><?php echo esc_html($m[3]); ?></p>
      <p class="em"><a href="mailto:<?php echo esc_attr($m[4]); ?>"><?php echo esc_html($m[4]); ?></a></p>
    <?php endif; ?>
  </div>
<?php }
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="preload" as="font" type="font/woff2" href="/wp-content/hw-fonts/f5.woff2" crossorigin>
<style id="hw-critical">:root{--red:#e01f30;--red-h:#dd3333}*{box-sizing:border-box}body.hw-page{margin:0;font-family:"Open Sans",Helvetica,Arial,sans-serif;color:#2d2d2d;overflow-x:hidden;padding-top:122px;background:#fff}.hw-wrap{width:100%;max-width:1350px;margin:0 auto;padding:0 30px}img{max-width:100%}#hw-header{position:fixed;top:0;left:0;right:0;z-index:99999}.hw-topbar{background:#444;height:47px;display:flex;align-items:center}.hw-topbar .hw-wrap{display:flex;align-items:center;justify-content:flex-start;gap:26px}.hw-topbar .hw-phone,.hw-topbar .hw-email{font-family:ABeeZee,Helvetica,Arial,sans-serif;font-size:18px;line-height:18px;color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:8px}.hw-topbar .hw-phone{font-weight:600}.hw-topbar .hw-email{font-weight:700}.hw-mainbar{background:#000;height:75px;display:flex;align-items:center}.hw-mainbar .hw-wrap{display:flex;align-items:flex-end;justify-content:space-between;height:75px}.hw-logo{display:flex;align-items:center;align-self:center;margin-left:4px}.hw-logo img{height:53px;width:auto;display:block}.hw-nav{display:flex;align-items:flex-end;gap:24px}.hw-nav ul{display:flex;align-items:flex-end;gap:22px;list-style:none;margin:0;padding:0}.hw-nav ul a{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:40px;color:#fff;text-decoration:none;text-transform:uppercase;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;padding:0 10px}.hw-caret{display:inline-block;width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid currentColor;opacity:.85}.hw-reserve{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:16px;color:#fff;text-decoration:none;text-transform:uppercase;background:var(--red);padding:12px 18px;border-radius:4px;display:inline-flex;align-items:center;gap:8px;white-space:nowrap}.hw-burger{display:none;flex-direction:column;justify-content:center;gap:5px;width:56px;height:44px;padding:0 13px;background:#4a4a4a;border:0;border-radius:6px;cursor:pointer}.hw-burger span{display:block;height:3px;width:100%;background:#fff;border-radius:2px}.hw-mobmenu{display:none}
/* page: meet-the-team */
.hw-mtt-title .hw-c-wrap,.hw-team-sec .hw-c-wrap{max-width:1152px}
.hw-mtt-title{background:#fff;padding:44px 0 34px;text-align:center}
.hw-mtt-title h1{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:700;font-size:50px;line-height:1.1;color:var(--red);margin:0 0 16px}
.hw-mtt-title p{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-weight:500;font-size:20px;line-height:1.5;color:#000;margin:2px 0 0}
.hw-team-sec{padding:57px 0;background:#fff}
.hw-team-sec.pat{background:#f4f4f4 center/cover url('<?php echo $PAT; ?>')}
.hw-team-h{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-weight:500;font-size:26px;line-height:1.2;color:#333;margin:0 0 30px;text-align:left}
.hw-tgrid{display:grid;gap:28px}
.hw-tgrid.g4{grid-template-columns:repeat(4,1fr)}
.hw-tgrid.g3{grid-template-columns:repeat(3,1fr);max-width:864px;margin:0 auto}
.hw-tgrid>*{min-width:0}
.hw-tcard{text-align:center;font-family:"Open Sans",Helvetica,Arial,sans-serif;min-width:0}
.hw-tcard .pho{aspect-ratio:1/1;overflow:hidden;margin:0 0 16px}
.hw-tcard .pho img{width:100%;height:100%;object-fit:cover;display:block}
.hw-tcard .nm{font-weight:700;font-size:16px;line-height:1.3;color:#000;margin:0}
.hw-tcard .ttl{font-style:italic;font-size:16px;line-height:1.3;color:#000;margin:6px 0 0}
.hw-tcard .blurb{font-size:16px;line-height:1.5;color:#000;margin:14px 0 0;min-height:72px}
.hw-tcard hr{border:0;border-top:1px solid #000;margin:14px 0}
.hw-tcard .ph{font-size:16px;line-height:1.4;color:#000;margin:0}
.hw-tcard .em{font-size:16px;line-height:1.4;margin:2px 0 0}
.hw-tcard .em a{color:#333;text-decoration:underline}
.hw-btgrid{display:grid;grid-template-columns:repeat(6,1fr);gap:16px;max-width:1152px;margin:0 auto}
.hw-btcard{border:1px solid #000;padding:16px 8px;text-align:center;font-family:"Open Sans",Helvetica,Arial,sans-serif}
.hw-btcard .bh{color:var(--red);font-weight:700;font-size:14px;line-height:1.35;margin:0 0 14px;text-decoration:none;display:block}
.hw-btcard .nm{font-weight:700;font-size:15px;line-height:1.3;color:#333;margin:0}
.hw-btcard .rl{font-size:15px;line-height:1.3;color:#333;margin:0 0 14px}
.hw-btcard .rl:last-child{margin-bottom:0}
.hw-form-sec{background:#111 center/cover no-repeat;padding:57px 0;color:#fff}
.hw-form-sec .hw-c-wrap{max-width:1080px}
.hw-form-sec h1{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:600;font-size:40px;line-height:1.1;color:#fff;text-align:center;margin:0 0 10px}
.hw-form-sec .sub{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:500;font-size:18px;line-height:1.6;color:#fff;text-align:center;margin:0 0 26px}
.hw-form-sec .sub a{color:var(--red);font-weight:700;text-decoration:none}
.hw-band{background:#000;color:#fff;padding:56px 0}
.hw-band .hw-c-wrap{max-width:1240px;display:flex;align-items:stretch;gap:0}
.hw-band-l{flex:0 0 57%;padding-right:52px;border-right:1px solid #4f4f4f}
.hw-band-l h2{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:700;font-size:40px;line-height:1.2;color:#fff;text-align:center;margin:0}
.hw-band-l .subttl{margin:0 0 24px}
.hw-band-l p{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:18px;line-height:1.6;color:#fff;margin:0 0 18px}
.hw-band-l .hw-btn{margin-top:10px;font-size:18px}
.hw-band-r{flex:1;padding-left:52px}
.hw-band-r h2{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:700;font-size:40px;line-height:1.2;color:#fff;margin:0 0 22px}
.hw-band-r ul{list-style:disc;margin:0;padding-left:22px}
.hw-band-r li{margin:0 0 9px}
.hw-band-r li,.hw-band-r a{color:var(--red);font-family:"Open Sans",Helvetica,Arial,sans-serif;font-weight:700;font-size:18px;line-height:1.3;text-decoration:none}
@media (max-width:767px){.hw-nav{display:none}.hw-burger{display:flex}body.hw-page{padding-top:149px}.hw-topbar{height:auto;min-height:85px;padding:9px 0}.hw-topbar .hw-wrap{flex-direction:row;flex-wrap:wrap;justify-content:center;gap:4px 14px;text-align:center;padding:0 16px}.hw-topbar .hw-phone,.hw-topbar .hw-email{justify-content:center}.hw-mainbar{height:64px}.hw-mainbar .hw-wrap{align-items:center}.hw-logo{align-self:center}.hw-logo img{height:35px}.hw-mtt-title h1{font-size:34px}.hw-tgrid.g4{grid-template-columns:repeat(2,1fr)}.hw-tgrid.g3{grid-template-columns:repeat(2,1fr);max-width:460px}.hw-btgrid{grid-template-columns:repeat(2,1fr)}.hw-form-sec h1{font-size:30px}.hw-band .hw-c-wrap{flex-direction:column}.hw-band-l{flex:none;padding-right:0;border-right:0;border-bottom:1px solid #4f4f4f;padding-bottom:30px;margin-bottom:30px}.hw-band-r{padding-left:0}}</style>
<link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css" media="print" onload="this.media='all';this.onload=null">
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/content.css?v=<?php echo @filemtime(get_stylesheet_directory().'/assets/css/content.css'); ?>" media="print" onload="this.media='all';this.onload=null">
<noscript><link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css"><link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/content.css"></noscript>
<?php wp_head(); ?>
</head>
<body <?php body_class('hw-page hw-mtt'); ?>>
<?php get_template_part('template-parts/site-header'); ?>
<main id="hw-main">

  <section class="hw-mtt-title">
    <div class="hw-c-wrap">
      <h1>Meet The Vac2Go Team</h1>
      <p>The people behind your rentals, service, and support.</p>
      <p>Call us anytime at 1-855-822-7246 (1-855-VACS2GO).</p>
    </div>
  </section>

  <section class="hw-team-sec pat">
    <div class="hw-c-wrap">
      <h2 class="hw-team-h">Your Sales and Support Team</h2>
      <div class="hw-tgrid g4">
        <?php foreach($sales as $m) hw_tcard($m,$H,true); ?>
      </div>
    </div>
  </section>

  <section class="hw-team-sec">
    <div class="hw-c-wrap">
      <h2 class="hw-team-h">Leadership</h2>
      <div class="hw-tgrid g4">
        <?php foreach($leadership as $m) hw_tcard($m,$H,false); ?>
      </div>
    </div>
  </section>

  <section class="hw-team-sec pat">
    <div class="hw-c-wrap">
      <h2 class="hw-team-h">Operations and Fleet</h2>
      <div class="hw-tgrid g3">
        <?php foreach($ops as $m) hw_tcard($m,$H,false); ?>
      </div>
    </div>
  </section>

  <section class="hw-team-sec">
    <div class="hw-c-wrap">
      <h2 class="hw-team-h">Your Branch Team</h2>
      <div class="hw-btgrid">
        <?php foreach($branchteam as $bt): ?>
        <div class="hw-btcard">
          <a class="bh" href="<?php echo esc_url($bt[1]); ?>"><?php echo esc_html($bt[0]); ?><br>BRANCH &rarr;</a>
          <p class="nm"><?php echo esc_html($bt[2]); ?></p>
          <p class="rl">Sales</p>
          <p class="nm"><?php echo esc_html($bt[3]); ?></p>
          <p class="rl">Operations</p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="hw-form-sec" style="background-image:url('<?php echo $FORMBG; ?>')">
    <div class="hw-c-wrap">
      <h1>Need a unit or have a question? We can help.</h1>
      <p class="sub">Call our number <strong><a href="tel:8558227246">1-855-VACS2GO</a></strong> or send us a message in the form below.</p>
      <?php echo do_shortcode('[gravityform id="2" title="false" description="false" ajax="true"]'); ?>
    </div>
  </section>

  <section class="hw-band">
    <div class="hw-c-wrap">
      <div class="hw-band-l">
        <h2>Need a unit or have a question?</h2>
        <h2 class="subttl">We can help - fast.</h2>
        <p>Every Vac2Go account gets a dedicated Regional Sales Manager, one person who knows your job and your timeline. Call anytime - we&#8217;re available 24/7 nationwide.</p>
        <p>Our fleet includes wet/dry vacuum trucks, 407/412 DOT-certified liquid vacuum trucks, hydro excavators, combination units, liquid ring systems, tankers, roll-offs, and two-box trailers, plus specialty equipment like Vactor Dense Phase, Guzzler XCR, and Keith Huber Knight units.</p>
        <p>Every truck is GPS-tracked, giving you real-time visibility into your equipment and crew in the field.</p>
        <a class="hw-btn" href="https://vac2go.com/contact/">RENT A TRUCK HERE!</a>
      </div>
      <div class="hw-band-r">
        <h2>Our Locations</h2>
        <ul>
          <?php foreach($locations as $loc): ?>
          <li><a href="<?php echo esc_url($loc[1]); ?>"><?php echo esc_html($loc[0]); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </section>

</main>
<?php get_template_part('template-parts/site-footer'); wp_footer(); ?>
</body>
</html>
