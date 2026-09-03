<?php if(!defined('ABSPATH'))exit;
/* page-contact.php — Divi-free rebuild of vac2go /contact/ (dev copy only) */
$UP='/wp-content/uploads';
$BG=$UP.'/2024/03/Red-Background.jpg';
$branches = array(
  array('name'=>'Alabama Branch','addr'=>array('3914 Hamilton Blvd','Theodore, Alabama 36582'),
    'ops'=>'Aaron Baisden','opsE'=>'abaisden@vac2go.com','opsP'=>'(251) 440-3146',
    'rsm'=>'Anthony St. Andre','rsmE'=>'astandre@vac2go.com','rsmP'=>'(251) 440-3133',
    'map'=>'https://www.google.com/maps?ll=30.554453,-88.131167&z=16&t=m&hl=en&gl=US&mapclient=embed&q=3914+Hamilton+Blvd+Theodore,+AL+36582',
    'img'=>$UP.'/2024/04/Vac2Go-Alabama-Office.jpg','w'=>1008,'h'=>576,'gray'=>false),
  array('name'=>'Arizona Branch','addr'=>array('3600 S 7th Avenue','Phoenix, AZ 85041'),
    'ops'=>'George Keathley','opsE'=>'gkeathley@vac2go.com','opsP'=>'(602) 641-6210',
    'rsm'=>'Jamie Bernzweig','rsmE'=>'jbernzweig@vac2go.com','rsmP'=>'(602) 325-5446',
    'map'=>'https://www.google.com/maps/place/3600+S+7th+Ave,+Phoenix,+AZ+85041/@33.4131003,-112.0850887,17z/data=!3m1!4b1!4m6!3m5!1s0x872b11bb27aa1103:0xee4894ec1c7d8888!8m2!3d33.4130958!4d-112.0825138!16s%2Fg%2F11t7726hcw',
    'img'=>$UP.'/2025/08/IMG_9062-1-scaled.jpg','w'=>2560,'h'=>1440,'gray'=>false),
  array('name'=>'Florida (Fort Myers)','addr'=>array('6110 Idlewild St.','Fort Myers, FL 33966'),
    'ops'=>'Donald Larson','opsE'=>'dlarson@vac2go.com','opsP'=>'(407) 232-6259',
    'rsm'=>'Hunter Geis','rsmE'=>'hgeis@vac2go.com','rsmP'=>'(407) 232-6255',
    'map'=>'https://www.google.com/maps/place/6110+Idlewild+St,+Fort+Myers,+FL+33966/data=!4m2!3m1!1s0x88db6abc6897b155:0xf47e1c37f7c02bf2?sa=X&ved=1t:242&ictx=111',
    'img'=>$UP.'/2026/06/South-Florida-Branch.jpg','w'=>1076,'h'=>717,'gray'=>true),
  array('name'=>'Florida (Orlando)','addr'=>array('500 Codisco Way','Sanford, FL 32771'),
    'ops'=>'Donald Larson','opsE'=>'dlarson@vac2go.com','opsP'=>'(407) 232-6259',
    'rsm'=>'Hunter Geis','rsmE'=>'hgeis@vac2go.com','rsmP'=>'(407) 232-6255',
    'map'=>'https://www.google.com/maps/place/Vac2Go+-+Sanford,+FL/data=!4m2!3m1!1s0x0:0x39c3b1f5bf642342?sa=X&ved=1t:2428&ictx=111',
    'img'=>$UP.'/2024/04/Vac2Go-Florida-Office.jpg','w'=>1008,'h'=>576,'gray'=>true),
  array('name'=>'Georgia','addr'=>array('1210 Commerce Drive','Madison, GA 30650'),
    'ops'=>'Darren Miles','opsE'=>'dmiles@vac2go.com','opsP'=>'(839) 232-1236',
    'rsm'=>'Steven St. Andre','rsmE'=>'sstandre@vac2go.com','rsmP'=>'(839) 232-1212',
    'map'=>'https://www.google.com/maps/dir/38.2009344,-85.5506944/1210+Commerce+Dr,+Madison,+GA+30650/@35.8716669,-87.7632511,7z/data=!3m1!4b1!4m9!4m8!1m1!4e1!1m5!1m1!1s0x88f686850af367db:0x31a676bb638ab1ea!2m2!1d-83.472446!2d33.544855',
    'img'=>$UP.'/2026/04/IMG_0720-1-scaled.jpeg','w'=>2560,'h'=>1707,'gray'=>true),
  array('name'=>'Indiana Branch','addr'=>array('601 E. 112th Avenue','Crown Point, IN 46307'),
    'ops'=>'George Limberopoulos','opsE'=>'glimberopoulos@vac2go.com','opsP'=>'(219) 706-7824',
    'rsm'=>'Patrick Splon','rsmE'=>'psplon@vac2go.com','rsmP'=>'(219) 359-3314',
    'map'=>'https://www.google.com/maps?ll=41.414258,-87.326572&z=15&t=m&hl=en&gl=US&mapclient=embed&cid=6174831392278415923',
    'img'=>$UP.'/2024/04/Vac2Go-Indiana-Office.jpg','w'=>1008,'h'=>576,'gray'=>false),
  array('name'=>'Kentucky Branch (CORPORATE)','addr'=>array('11120 Plantside Drive','Louisville, KY 40299'),
    'ops'=>'Billy Prather','opsE'=>'bprather@vac2go.com','opsP'=>'(502) 699-4015',
    'rsm'=>'Jeff Wells','rsmE'=>'jwells@vac2go.com','rsmP'=>'(502) 699-4029',
    'map'=>'https://www.google.com/maps?ll=38.209359,-85.549837&z=16&t=m&hl=en&gl=US&mapclient=embed&q=11120+Plantside+Dr+Louisville,+KY+40299',
    'img'=>$UP.'/2024/04/Vac2Go-Kentucky-Office-1.jpg','w'=>1008,'h'=>576,'gray'=>false),
  array('name'=>'New Jersey Branch','addr'=>array('265 Jessup Rd.','West Deptford, NJ 08086'),
    'ops'=>'Ridge Maignan','opsE'=>'rmaignan@vac2go.com','opsP'=>'(856) 955-1456',
    'rsm'=>'Jason Mears','rsmE'=>'jmears@vac2go.com','rsmP'=>'(540) 246-4850',
    'map'=>'https://maps.app.goo.gl/kcG4KSFhjCsXdcYf6',
    'img'=>$UP.'/2024/11/NJ-Office-1.png','w'=>1031,'h'=>580,'gray'=>false),
  array('name'=>'Ohio Branch','addr'=>array('5275 Louisville St. NE,','Louisville, OH 44641'),
    'ops'=>'Josh Miller','opsE'=>'jmiller@vac2go.com','opsP'=>'(440) 287-1687',
    'rsm'=>'Kriss Stepp','rsmE'=>'kstepp@vac2go.com','rsmP'=>'(440) 287-1687',
    'map'=>'https://www.google.com/maps/place/5275+Louisville+St+NE,+Louisville,+OH+44641/data=!4m2!3m1!1s0x8836cfac14303493:0x588ff76c1c2decab?sa=X&ved=0ahUKEwio7Ybc1oLZAhWIvVMKHdLFACcQ8gEIKDAA',
    'img'=>$UP.'/2024/04/Vac2Go-Ohio-Office.jpg','w'=>1008,'h'=>576,'gray'=>false),
  array('name'=>'South Carolina Branch','addr'=>array('489 E. Springdale Road','Rock Hill, SC 29730'),
    'ops'=>'Darren Miles','opsE'=>'dmiles@vac2go.com','opsP'=>'(839) 232-1236',
    'rsm'=>'Steven St. Andre','rsmE'=>'sstandre@vac2go.com','rsmP'=>'(839) 232-1212',
    'map'=>'https://www.google.com/maps?ll=34.917881,-80.966792&z=9&t=m&hl=en&gl=US&mapclient=embed&q=489+E+Springdale+Rd+Rock+Hill,+SC+29730',
    'img'=>$UP.'/2025/08/Screenshot-2025-08-29-101948.png','w'=>746,'h'=>419,'gray'=>false),
  array('name'=>'Tennessee Branch','addr'=>array('1525 Three Place','Memphis, TN 38116'),
    'ops'=>'Ray Green','opsE'=>'dgreen@vac2go.com','opsP'=>'(901) 455-2465',
    'rsm'=>'Mickey Still','rsmE'=>'mstill@vac2go.com','rsmP'=>'(901) 455-2464',
    'map'=>'https://www.google.com/maps?ll=35.056797,-90.01246&z=16&t=m&hl=en&gl=US&mapclient=embed&q=1525+3+Pl+Memphis,+TN+38116',
    'img'=>$UP.'/2024/04/Vac2Go-Tennessee-Office.jpg','w'=>1008,'h'=>576,'gray'=>false),
  array('name'=>'Texas Branch','addr'=>array('1150 Hall Court','Deer Park, TX 77536'),
    'ops'=>'Darius Brown','opsE'=>'dbrown@vac2go.com','opsP'=>'(346) 460-5566',
    'rsm'=>'Greg Brown','rsmE'=>'gbrown@vac2go.com','rsmP'=>'(346) 460-5522',
    'map'=>'https://www.google.com/maps?ll=29.702607,-95.097046&z=16&t=m&hl=en&gl=US&mapclient=embed&q=1150+Hall+Ct+Deer+Park,+TX+77536',
    'img'=>$UP.'/2024/04/Vac2Go-Texas-Office.jpg','w'=>1008,'h'=>576,'gray'=>false),
  array('name'=>'Utah Branch','addr'=>array('758 W. 1500 N.','Salt Lake City, UT 84116'),
    'ops'=>'LaDell Bishop','opsE'=>'lbishop@vac2go.com','opsP'=>'(385) 235-7895',
    'rsm'=>'Layne Williams','rsmE'=>'lwilliams@vac2go.com','rsmP'=>'(385) 213-7690',
    'map'=>'https://www.google.com/maps/place/Vac2Go+%E2%80%93+Industrial+Vacuum+Truck+Rental+Salt+Lake+City/data=!4m2!3m1!1s0x0:0xc0a1feb4b8538a41?sa=X&ved=1t:2428&ictx=111',
    'img'=>$UP.'/2026/03/Screenshot-2026-03-27-111841.png','w'=>1320,'h'=>649,'gray'=>false),
);
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
/* page: contact */
.hw-form-sec{background:#111 center/cover no-repeat;padding:57px 0;color:#fff}
.hw-form-sec .hw-c-wrap{max-width:1080px;width:100%;margin:0 auto;padding:0 30px}
.hw-form-sec h1{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:600;font-size:40px;line-height:1.1;color:#fff;text-align:center;margin:0 0 10px}
.hw-form-sec .sub{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:500;font-size:18px;line-height:1.6;color:#fff;text-align:center;margin:0 0 26px}
.hw-form-sec .sub a{color:var(--red);font-weight:700;text-decoration:none}
.hw-branch{padding:40px 0;background:#000}
.hw-branch.gray{background:#565656}
.hw-branch .hw-c-wrap{width:100%;max-width:1280px;margin:0 auto;padding:0 30px;display:flex;align-items:center;gap:40px}
.hw-branch-info{flex:1 1 50%;text-align:center;color:#fff;font-family:Poppins,Helvetica,Arial,sans-serif}
.hw-branch-info h2{font-weight:700;font-size:24px;line-height:24px;color:var(--red);margin:0 0 18px}
.hw-branch-info p{font-weight:500;font-size:20px;line-height:36px;color:#fff;margin:0 0 18px}
.hw-branch-info p.addr span{color:#e01f30}
.hw-branch-info a.mail{color:#ed0013;text-decoration:underline}
.hw-branch-info .contact{color:#ed0013}
.hw-branch-info .hw-btn{margin-top:6px}
.hw-branch-photo{flex:0 0 50%;aspect-ratio:1008/576}
.hw-branch-photo img{width:100%;height:100%;object-fit:cover;display:block}
@media (max-width:767px){.hw-nav{display:none}.hw-burger{display:flex}body.hw-page{padding-top:149px}.hw-topbar{height:auto;min-height:85px;padding:9px 0}.hw-topbar .hw-wrap{flex-direction:row;flex-wrap:wrap;justify-content:center;gap:4px 14px;text-align:center;padding:0 16px}.hw-topbar .hw-phone,.hw-topbar .hw-email{justify-content:center}.hw-mainbar{height:64px}.hw-mainbar .hw-wrap{align-items:center}.hw-logo{align-self:center}.hw-logo img{height:35px}.hw-form-sec h1{font-size:30px}.hw-branch .hw-c-wrap{flex-direction:column;gap:22px}.hw-branch-info{flex:none;width:100%}.hw-branch-photo{flex:none;width:100%}.hw-branch-info h2{font-size:22px}.hw-branch-info p{font-size:18px;line-height:30px}}</style>
<link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css" media="print" onload="this.media='all';this.onload=null">
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/content.css?v=<?php echo @filemtime(get_stylesheet_directory().'/assets/css/content.css'); ?>" media="print" onload="this.media='all';this.onload=null">
<noscript><link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css"><link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/content.css"></noscript>
<?php wp_head(); ?>
</head>
<body <?php body_class('hw-page hw-contact'); ?>>
<?php get_template_part('template-parts/site-header'); ?>
<main id="hw-main">

  <section class="hw-form-sec" style="background-image:url('<?php echo $BG; ?>')">
    <div class="hw-c-wrap">
      <h1>Need a unit or have a question? We can help.</h1>
      <p class="sub">Call our number <strong><a href="tel:8558227246">1-855-822-7246 (VACS2GO)</a></strong> or send us a message in the form below.</p>
      <?php echo do_shortcode('[gravityform id="2" title="false" description="false" ajax="true"]'); ?>
      <p class="hw-form-privacy" style="font-size:13px;line-height:1.5;text-align:center;margin:16px auto 0;max-width:640px;color:#cfcfcf">By submitting this form you agree to our <a href="/privacy-policy/" style="color:var(--red);font-weight:600;text-decoration:underline">Privacy Policy</a>. We use the information you provide only to respond to your inquiry.</p>
    </div>
  </section>

  <div class="hw-branches">
  <?php foreach($branches as $i=>$b): ?>
    <section class="hw-branch<?php echo ($i%2==1)?' gray':''; ?>">
      <div class="hw-c-wrap">
        <div class="hw-branch-info">
          <h2><?php echo esc_html($b['name']); ?></h2>
          <p class="addr"><strong>Address:</strong><br><span><?php echo esc_html($b['addr'][0]); ?></span><br><span><?php echo esc_html($b['addr'][1]); ?></span></p>
          <p><strong>Operations Manager:</strong> <?php echo esc_html($b['ops']); ?></p>
          <p class="contact"><a class="mail" href="mailto:<?php echo esc_attr($b['opsE']); ?>"><?php echo esc_html($b['opsE']); ?></a> | <?php echo esc_html($b['opsP']); ?></p>
          <p><strong>Regional Sales Manager:</strong> <?php echo esc_html($b['rsm']); ?></p>
          <p class="contact"><a class="mail" href="mailto:<?php echo esc_attr($b['rsmE']); ?>"><?php echo esc_html($b['rsmE']); ?></a> | <?php echo esc_html($b['rsmP']); ?></p>
          <a class="hw-btn" href="<?php echo esc_url($b['map']); ?>" target="_blank" rel="noopener">MAP LOCATION</a>
        </div>
        <div class="hw-branch-photo">
          <img src="<?php echo $b['img']; ?>" alt="Vac2Go <?php echo esc_attr($b['name']); ?>" width="<?php echo $b['w']; ?>" height="<?php echo $b['h']; ?>" loading="<?php echo $i<1?'eager':'lazy'; ?>" decoding="async">
        </div>
      </div>
    </section>
  <?php endforeach; ?>
  </div>

</main>
<?php get_template_part('template-parts/site-footer'); wp_footer(); ?>
</body>
</html>
