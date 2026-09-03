<?php if(!defined('ABSPATH'))exit;
/* page-trucks-for-sale.php — Divi-free rebuild of vac2go /trucks-for-sale/ (dev copy only).
   Gravity Form id=2 (VacContact) rendered via shortcode. */
$UP='/wp-content/uploads';
$FORMBG=$UP.'/2024/03/Red-Background.jpg';
$logos = array(
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
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="preload" as="font" type="font/woff2" href="/wp-content/hw-fonts/f5.woff2" crossorigin>
<style id="hw-critical">:root{--red:#e01f30;--red-h:#dd3333}*{box-sizing:border-box}body.hw-page{margin:0;font-family:"Open Sans",Helvetica,Arial,sans-serif;color:#2d2d2d;overflow-x:hidden;padding-top:122px;background:#fff}.hw-wrap{width:100%;max-width:1350px;margin:0 auto;padding:0 30px}img{max-width:100%}#hw-header{position:fixed;top:0;left:0;right:0;z-index:99999}.hw-topbar{background:#444;height:47px;display:flex;align-items:center}.hw-topbar .hw-wrap{display:flex;align-items:center;justify-content:flex-start;gap:26px}.hw-topbar .hw-phone,.hw-topbar .hw-email{font-family:ABeeZee,Helvetica,Arial,sans-serif;font-size:18px;line-height:18px;color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:8px}.hw-topbar .hw-phone{font-weight:600}.hw-topbar .hw-email{font-weight:700}.hw-mainbar{background:#000;height:75px;display:flex;align-items:center}.hw-mainbar .hw-wrap{display:flex;align-items:flex-end;justify-content:space-between;height:75px}.hw-logo{display:flex;align-items:center;align-self:center;margin-left:4px}.hw-logo img{height:53px;width:auto;display:block}.hw-nav{display:flex;align-items:flex-end;gap:24px}.hw-nav ul{display:flex;align-items:flex-end;gap:22px;list-style:none;margin:0;padding:0}.hw-nav ul a{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:40px;color:#fff;text-decoration:none;text-transform:uppercase;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;padding:0 10px}.hw-caret{display:inline-block;width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid currentColor;opacity:.85}.hw-reserve{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:16px;color:#fff;text-decoration:none;text-transform:uppercase;background:var(--red);padding:12px 18px;border-radius:4px;display:inline-flex;align-items:center;gap:8px;white-space:nowrap}.hw-burger{display:none;flex-direction:column;justify-content:center;gap:5px;width:56px;height:44px;padding:0 13px;background:#4a4a4a;border:0;border-radius:6px;cursor:pointer}.hw-burger span{display:block;height:3px;width:100%;background:#fff;border-radius:2px}.hw-mobmenu{display:none}
/* page: trucks-for-sale */
.hw-cwrap{width:100%;max-width:1280px;margin:0 auto;padding:0 30px}
.tfs-hero{background:var(--red);padding:16px 0}
.tfs-hero h1{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-weight:700;font-size:70px;line-height:1.1;color:#fff;text-align:center;margin:0}
.tfs-black{background:#000;padding:42px 0 56px}
.tfs-black h1{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-weight:700;font-size:42px;line-height:1.25;color:#fff;text-align:center;margin:0 0 30px}
.tfs-black .sm{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-weight:700;font-style:italic;font-size:24px;line-height:1.4;color:#fff;text-align:center;margin:0 auto 10px;max-width:1000px}
.tfs-chad{background:#fff;padding:57px 0}
.tfs-chad .row{display:flex;gap:40px;align-items:flex-start;max-width:1152px;margin:0 auto}
.tfs-chad .photo{flex:0 0 320px}
.tfs-chad .photo img{width:320px;height:320px;object-fit:cover;display:block}
.tfs-chad h4{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-weight:500;font-size:18px;color:#333;margin:0 0 2px}
.tfs-chad .pos{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-weight:500;font-size:14px;color:#aaa;margin:0 0 18px}
.tfs-chad p{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-weight:500;font-size:14px;color:#666;line-height:1.7;margin:0 0 14px;max-width:780px}
.tfs-fin{background:#ededed url(/wp-content/uploads/2024/05/4907157-scaled.jpg) center/cover no-repeat;padding:42px 0}
.tfs-fin .hw-cwrap{max-width:1152px}
.tfs-fin p{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:500;font-size:24px;line-height:1.5;color:#000;text-align:left;margin:0 0 22px}
.tfs-fin p:last-child{margin-bottom:0}
.tfs-fin a{color:var(--red);font-weight:700;text-decoration:none}
.hw-form-sec{background:#111 center/cover no-repeat;padding:57px 0;color:#fff}
.hw-form-sec h1,.hw-form-sec .hw-fh{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:600;font-size:40px;line-height:1.1;color:#fff;text-align:center;margin:0 0 10px}
.hw-form-sec .sub{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:500;font-size:18px;line-height:1.6;color:#fff;text-align:center;margin:0 0 26px}
.hw-form-sec .sub a{color:var(--red);font-weight:700;text-decoration:none}
.hw-logos{padding:46px 0 8px;background:#fff}
.hw-logos-row{display:flex;align-items:center;justify-content:center;gap:40px;flex-wrap:wrap;max-width:1200px;margin:0 auto}
.hw-logos-row img{height:auto;width:auto;max-height:120px;max-width:206px;object-fit:contain}
.hw-social{background:#fff;padding:4px 0 34px;text-align:center}
.hw-social a{display:inline-block;width:28px;height:28px;margin:0 5px;border-radius:4px;vertical-align:middle;line-height:0}
.hw-social svg{width:16px;height:16px;fill:#fff;margin:6px}
.hw-fb{background:#3b5998}.hw-tw{background:#1da1f2}.hw-li{background:#0077b5}.hw-ig{background:#ea2c59}.hw-yt{background:#c4302b}
@media (max-width:767px){.hw-nav{display:none}.hw-burger{display:flex}body.hw-page{padding-top:149px}.hw-topbar{height:auto;min-height:85px;padding:9px 0}.hw-topbar .hw-wrap{flex-direction:row;flex-wrap:wrap;justify-content:center;gap:4px 14px;text-align:center;padding:0 16px}.hw-topbar .hw-phone,.hw-topbar .hw-email{justify-content:center}.hw-mainbar{height:64px}.hw-mainbar .hw-wrap{align-items:center}.hw-logo{align-self:center}.hw-logo img{height:35px}.tfs-hero h1{font-size:44px}.tfs-black h1{font-size:30px}.tfs-black .sm{font-size:20px}.tfs-chad .row{flex-direction:column;align-items:center;text-align:center;gap:24px}.tfs-chad .photo{flex:none}.tfs-chad p{max-width:100%}.tfs-fin p{font-size:20px;text-align:center}.hw-form-sec h1,.hw-form-sec .hw-fh{font-size:30px}.hw-logos-row{gap:22px}.hw-logos-row img{max-width:150px}}</style>
<link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css" media="print" onload="this.media='all';this.onload=null">
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/forms.css?v=<?php echo @filemtime(get_stylesheet_directory().'/assets/css/forms.css'); ?>" media="print" onload="this.media='all';this.onload=null">
<noscript><link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css"><link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/forms.css"></noscript>
<?php wp_head(); ?>
</head>
<body <?php body_class('hw-page hw-tfs'); ?>>
<?php get_template_part('template-parts/site-header'); ?>
<main id="hw-main">

  <section class="tfs-hero"><div class="hw-cwrap"><h1>FOR SALE</h1></div></section>

  <section class="tfs-black"><div class="hw-cwrap">
    <h1>We currently do not have any units for sale.</h1>
    <p class="sm">New inventory is added regularly.</p>
    <p class="sm">Contact us today and we&#8217;ll let you know when the right truck becomes available or help source one that meets your needs.</p>
  </div></section>

  <section class="tfs-chad"><div class="hw-cwrap"><div class="row">
    <div class="photo"><img src="<?php echo $UP; ?>/2026/07/7.png" alt="Chad Naquin" width="320" height="320" loading="lazy" decoding="async"></div>
    <div class="info">
      <h4>Chad Naquin</h4>
      <p class="pos">Used Truck Sales</p>
      <p><strong>Interested in Buying a Truck?</strong><br>Talk with Chad about current inventory, upcoming trucks, financing options, or finding the right vacuum truck for your operation.</p>
      <p class="contact">(502) 699-4019 | <a href="mailto:cnaquin@vac2go.com" style="color:#666;text-decoration:underline">cnaquin@vac2go.com</a></p>
    </div>
  </div></div></section>

  <section class="tfs-fin"><div class="hw-cwrap">
    <p><strong>Looking for truck financing?</strong> You&#8217;re in the right place with the Transportation Finance division of BMO Bank N.A., one of North America&#8217;s leaders in this space. From customized leases and secured loans, we&#8217;re here to help ensure you&#8217;re always on the right path.</p>
    <p>Let us help to keep your operation moving with BMO. <a href="https://tfsapply.bmobusinesspro.com/#/landing-page" target="_blank" rel="noopener">Start Here</a></p>
  </div></section>

  <section class="hw-form-sec" style="background-image:url('<?php echo $FORMBG; ?>')">
    <div class="hw-cwrap" style="max-width:1180px">
      <h1>Ready to Purchase a Truck or Have Questions?</h1>
      <p class="sub">We&#8217;d love to hear from you! Call our number <strong><a href="tel:8558227246">1-855-VACS2GO</a></strong> or e-mail us your message in the form below.</p>
      <?php echo do_shortcode('[gravityform id="2" title="false" description="false" ajax="true"]'); ?>
    </div>
  </section>


</main>
<?php get_template_part('template-parts/site-footer'); wp_footer(); ?>
</body>
</html>
