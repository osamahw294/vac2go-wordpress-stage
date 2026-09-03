<?php if(!defined('ABSPATH'))exit;
/* page-truck-repair.php — Divi-free rebuild of vac2go /truck-repair/ (dev copy only).
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
/* page: truck-repair */
.tr-hero{display:flex;align-items:stretch;min-height:560px}
.tr-hero .img{flex:1 1 50%;background:url(/wp-content/uploads/2021/06/Vac2Go-Truck-Repair.jpg) center/cover no-repeat;min-height:360px}
.tr-hero .panel{flex:1 1 50%;background:#ededed;padding:44px 58px;display:flex;flex-direction:column;justify-content:center}
.tr-hero .panel .lead{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:600;font-style:italic;font-size:30px;line-height:1.2;color:#333;text-align:center;margin:0 0 20px}
.tr-hero .panel h1{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:600;font-size:30px;line-height:1.2;color:#333;text-align:left;margin:0 0 10px}
.tr-hero .panel h2{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:500;font-size:17px;line-height:1.3;color:#333;text-align:left;margin:0 0 10px}
.tr-hero .panel ul{margin:0;padding-left:20px;list-style:disc}
.tr-hero .panel li{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:500;font-size:15px;line-height:1.9;color:#000}
.tr-black{background:#000;padding:22px 0}
.tr-black p{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:500;font-size:28px;line-height:1.35;color:#fff;text-align:center;max-width:1000px;margin:0 auto}
.tr-red{background:var(--red);padding:30px 0}
.tr-red h1{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:500;font-size:36px;line-height:1.25;color:#fff;text-align:center;margin:0}
.hw-form-sec{background:#111 center/cover no-repeat;padding:57px 0;color:#fff}
.hw-form-sec .hw-fh{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:700;font-size:40px;line-height:1.1;color:#fff;text-align:center;margin:0 0 20px}
.tr-form .row{display:flex;gap:40px;align-items:flex-start;max-width:1280px;margin:0 auto;padding:0 30px}
.tr-form .left{flex:0 0 38%;text-align:center;font-family:Poppins,Helvetica,Arial,sans-serif}
.tr-form .left .logo{max-width:312px;width:100%;height:auto;margin:0 auto 24px;display:block}
.tr-form .left .phone{font-weight:700;font-size:18px;line-height:1.9;color:#fff;margin:0 0 18px}
.tr-form .left .phone a{color:var(--red);text-decoration:none}
.tr-form .left .mission{font-weight:500;font-size:16px;line-height:1.6;color:#fff;margin:20px 0 0}
.tr-form .right{flex:1 1 62%}
.hw-social{text-align:center;line-height:0}
.hw-social a{display:inline-block;width:28px;height:28px;margin:0 5px;border-radius:4px;vertical-align:middle;line-height:0}
.hw-social svg{width:16px;height:16px;fill:#fff;margin:6px}
.hw-fb{background:#3b5998}.hw-tw{background:#1da1f2}.hw-li{background:#0077b5}.hw-ig{background:#ea2c59}.hw-yt{background:#c4302b}
.hw-social-red a{background:var(--red)}
.tr-form .left .hw-social{background:transparent;padding:0;margin:0 0 6px}
.hw-logos{padding:46px 0 8px;background:#fff}
.hw-logos-row{display:flex;align-items:center;justify-content:center;gap:40px;flex-wrap:wrap;max-width:1200px;margin:0 auto}
.hw-logos-row img{height:auto;width:auto;max-height:120px;max-width:206px;object-fit:contain}
.hw-social-foot{background:#fff;padding:4px 0 34px}
@media (max-width:767px){.hw-nav{display:none}.hw-burger{display:flex}body.hw-page{padding-top:149px}.hw-topbar{height:auto;min-height:85px;padding:9px 0}.hw-topbar .hw-wrap{flex-direction:row;flex-wrap:wrap;justify-content:center;gap:4px 14px;text-align:center;padding:0 16px}.hw-topbar .hw-phone,.hw-topbar .hw-email{justify-content:center}.hw-mainbar{height:64px}.hw-mainbar .hw-wrap{align-items:center}.hw-logo{align-self:center}.hw-logo img{height:35px}.tr-hero{flex-direction:column}.tr-hero .img{min-height:260px}.tr-hero .panel{padding:30px 22px}.tr-hero .panel .lead,.tr-hero .panel h1{font-size:24px}.tr-black p{font-size:22px}.tr-red h1{font-size:26px}.tr-form .row{flex-direction:column;gap:28px;padding:0 18px}.tr-form .left,.tr-form .right{flex:none;width:100%}.hw-form-sec .hw-fh{font-size:30px}.hw-logos-row{gap:22px}.hw-logos-row img{max-width:150px}}</style>
<link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css" media="print" onload="this.media='all';this.onload=null">
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/forms.css?v=<?php echo @filemtime(get_stylesheet_directory().'/assets/css/forms.css'); ?>" media="print" onload="this.media='all';this.onload=null">
<noscript><link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css"><link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/forms.css"></noscript>
<?php wp_head(); ?>
</head>
<body <?php body_class('hw-page hw-tr'); ?>>
<?php get_template_part('template-parts/site-header'); ?>
<main id="hw-main">

  <section class="tr-hero">
    <div class="img" role="img" aria-label="Vacuum truck repair welding"></div>
    <div class="panel">
      <p class="lead">Quality repairs completed right the first time to minimize downtime and keep your project on schedule.</p>
      <h1>Vacuum Truck Repair</h1>
      <h2>SOME OF THE SERVICES WE OFFER:</h2>
      <ul>
        <li>Tank/ Bag House/ Cyclone Relining and Repair</li>
        <li>Electrical Repair</li>
        <li>Transfer Case and Blower Repair/Replacement</li>
        <li>Hydraulic Repair</li>
        <li>Paint</li>
        <li>New Components Installation</li>
        <li>Complete Truck Rebuilds</li>
        <li>Regular Preventative Maintenance Programs</li>
      </ul>
    </div>
  </section>

  <section class="tr-black"><div class="hw-cwrap">
    <p>Vac2Go is an Authorized Service Center for GapVax, Kaiser Premier, Keith Huber Corporation, Paccar, and Samsara.</p>
  </div></section>

  <section class="tr-red"><div class="hw-cwrap">
    <h1>Get the most life out of your investment. Call us today to discuss your needs! <strong>1-855-VACS2GO</strong></h1>
  </div></section>

  <section class="hw-form-sec tr-form" style="background-image:url('<?php echo $FORMBG; ?>')">
    <div class="row">
      <div class="left">
        <img class="logo" src="<?php echo $UP; ?>/2022/06/Vac2GoNewLogoFinalWhite.png" alt="Vac2Go - We've Got Your Vac" width="1113" height="305" loading="lazy" decoding="async">
        <p class="phone">Phone: <a href="tel:8558227246">1-855-VACS2GO</a><br><a href="https://goo.gl/maps/iEYT22EKfCKj6Dba9" target="_blank" rel="noopener" style="color:var(--red);text-decoration:none">11120 Plantside Drive</a><br><a href="https://goo.gl/maps/iEYT22EKfCKj6Dba9" target="_blank" rel="noopener" style="color:var(--red);text-decoration:none">Louisville, KY 40299</a></p>
        <div class="hw-social hw-social-red">
          <a class="hw-fb" href="https://www.facebook.com/Vac2Go" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M13 3h4V0h-4a5 5 0 0 0-5 5v3H5v3h3v10h3V11h3l1-3h-4V5a1 1 0 0 1 1-1z"/></svg></a>
          <a class="hw-tw" href="https://twitter.com/Vac2Go" target="_blank" rel="noopener" aria-label="Twitter"><svg viewBox="0 0 24 24"><path d="M23 4.9c-.8.4-1.7.6-2.6.8a4.5 4.5 0 0 0 2-2.5c-.9.5-1.9.9-2.9 1.1a4.5 4.5 0 0 0-7.7 4.1A12.8 12.8 0 0 1 2.7 3.6a4.5 4.5 0 0 0 1.4 6 4.5 4.5 0 0 1-2-.6v.1a4.5 4.5 0 0 0 3.6 4.4 4.5 4.5 0 0 1-2 .1 4.5 4.5 0 0 0 4.2 3.1A9 9 0 0 1 1 20.3a12.7 12.7 0 0 0 6.9 2c8.3 0 12.8-6.9 12.8-12.8v-.6c.9-.6 1.6-1.4 2.3-2z"/></svg></a>
          <a class="hw-li" href="https://www.linkedin.com/company/2587870" target="_blank" rel="noopener" aria-label="LinkedIn"><svg viewBox="0 0 24 24"><path d="M4.98 3.5A2.5 2.5 0 1 1 5 8.5a2.5 2.5 0 0 1 0-5zM3 9h4v12H3zM9 9h3.8v1.7h.1c.5-1 1.8-2 3.7-2 4 0 4.7 2.6 4.7 6V21h-4v-5.3c0-1.3 0-2.9-1.8-2.9s-2 1.4-2 2.8V21H9z"/></svg></a>
        </div>
        <p class="mission"><strong>Our Mission:</strong> At Vac2Go we strive to exceed expectations by providing best-in-class rental equipment and the highest quality customer experience in the industry.</p>
      </div>
      <div class="right">
        <h2 class="hw-fh">Contact Vac2Go Today</h2>
        <?php echo do_shortcode('[gravityform id="2" title="false" description="false" ajax="true"]'); ?>
      </div>
    </div>
  </section>


</main>
<?php get_template_part('template-parts/site-footer'); wp_footer(); ?>
</body>
</html>
