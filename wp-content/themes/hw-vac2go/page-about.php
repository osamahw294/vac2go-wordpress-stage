<?php if(!defined('ABSPATH'))exit; ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="preload" as="font" type="font/woff2" href="/wp-content/hw-fonts/f25.woff2" crossorigin>
<style id="hw-critical">:root{--red:#e01f30;--red-h:#dd3333}*{box-sizing:border-box}body.hw-page{margin:0;font-family:"Open Sans",Helvetica,Arial,sans-serif;color:#2d2d2d;overflow-x:hidden;padding-top:122px;background:#fff}.hw-wrap{width:100%;max-width:1350px;margin:0 auto;padding:0 30px}img{max-width:100%}#hw-header{position:fixed;top:0;left:0;right:0;z-index:99999}.hw-topbar{background:#444;height:47px;display:flex;align-items:center}.hw-topbar .hw-wrap{display:flex;align-items:center;justify-content:flex-start;gap:26px}.hw-topbar .hw-phone,.hw-topbar .hw-email{font-family:ABeeZee,Helvetica,Arial,sans-serif;font-size:18px;line-height:18px;color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:8px}.hw-topbar .hw-phone{font-weight:600}.hw-topbar .hw-email{font-weight:700}.hw-mainbar{background:#000;height:75px;display:flex;align-items:center}.hw-mainbar .hw-wrap{display:flex;align-items:flex-end;justify-content:space-between;height:75px}.hw-logo{display:flex;align-items:center;align-self:center;margin-left:4px}.hw-logo img{height:53px;width:auto;display:block}.hw-nav{display:flex;align-items:flex-end;gap:24px}.hw-nav ul{display:flex;align-items:flex-end;gap:22px;list-style:none;margin:0;padding:0}.hw-nav ul a{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:40px;color:#fff;text-decoration:none;text-transform:uppercase;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;padding:0 10px}.hw-caret{display:inline-block;width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid currentColor;opacity:.85}.hw-reserve{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:16px;color:#fff;text-decoration:none;text-transform:uppercase;background:var(--red);padding:12px 18px;border-radius:4px;display:inline-flex;align-items:center;gap:8px;white-space:nowrap}.hw-burger{display:none;flex-direction:column;justify-content:center;gap:5px;width:56px;height:44px;padding:0 13px;background:#4a4a4a;border:0;border-radius:6px;cursor:pointer}.hw-burger span{display:block;height:3px;width:100%;background:#fff;border-radius:2px}.hw-mobmenu{display:none}
/* ===== page: about ===== */
/* hero */
.hw-about-hero{position:relative;height:420px;background-color:var(--red);background-size:cover;background-position:center;background-repeat:no-repeat;display:flex;align-items:center;justify-content:center}
.hw-about-hero h1{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-weight:700;font-size:50px;line-height:1;color:#fff;text-align:center;margin:0;padding:0 20px}
/* mission / vision */
.hw-mv{padding:57px 0 80px}
.hw-mv .hw-c-wrap{max-width:1152px}
.hw-mv-h{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:600;font-size:30px;line-height:30px;color:#333;text-align:left;margin:0 0 10px}
.hw-mv-p{font-family:Roboto,Helvetica,Arial,sans-serif;font-weight:500;font-size:19px;line-height:34.2px;color:#666;text-align:left;margin:0 0 19px}
.hw-mv-p.last{margin:0}
/* red band */
.hw-about-redband{background:var(--red);padding:56px 0;text-align:center}
.hw-about-redband p{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:700;font-size:60px;line-height:1.3;color:#fff;text-transform:uppercase;margin:0}
/* feature cards */
.hw-about-cards{max-width:1280px;margin:0 auto;padding:57px 30px}
.hw-about-cards .hw-card{display:flex;align-items:center;gap:40px;border:2px solid #e5e5e5;border-radius:20px;padding:25px 40px;margin:0 0 30px;box-shadow:0 12px 18px -6px rgba(0,0,0,.3);background:#fff}
.hw-about-cards .hw-card:last-child{margin-bottom:0}
.hw-about-cards .hw-card-ico{flex:0 0 300px;text-align:center}
.hw-about-cards .hw-card-ico img{max-width:100%;height:auto;display:inline-block}
.hw-about-cards .hw-card-body{flex:1}
.hw-about-cards .hw-card-body h2{font-family:Montserrat,Helvetica,Arial,sans-serif;font-weight:700;font-size:30px;line-height:1.1;color:var(--red);margin:0 0 10px;text-transform:uppercase}
.hw-about-cards .hw-card-body p{font-family:Montserrat,Helvetica,Arial,sans-serif;font-weight:500;font-size:16px;line-height:1.6;color:#666;margin:0}
/* truck + platform */
.hw-about-platform{display:flex;align-items:stretch;width:100%}
.hw-plat-img{flex:0 0 50%;background-color:#383838;background-size:cover;background-position:center;background-repeat:no-repeat;min-height:600px}
.hw-plat-text{flex:0 0 50%;background:#ededed;padding:100px 158px 100px 100px}
.hw-plat-h{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:600;font-size:30px;line-height:30px;color:#333;margin:0 0 12px}
.hw-plat-lead{font-family:"Open Sans",Helvetica,Arial,sans-serif;font-style:italic;font-weight:500;font-size:14px;line-height:23.8px;color:#666;margin:0 0 14px}
.hw-plat-p{font-family:Roboto,Helvetica,Arial,sans-serif;font-weight:500;font-size:15px;line-height:27px;color:#000;margin:0 0 15px}
.hw-plat-p.last{margin:0}
/* values */
.hw-about-values{background-color:#333;background-size:cover;background-position:center;background-repeat:no-repeat;padding:49px 0}
.hw-about-values .hw-c-wrap{max-width:1280px}
.hw-values-h{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:500;font-size:36px;line-height:36px;color:#fff;text-align:center;margin:0 0 52px}
.hw-values-list{max-width:900px;margin:0 auto}
.hw-value-item{margin:0}
.hw-value-item h2{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:700;font-size:26px;line-height:26px;color:var(--red);margin:0 0 8px}
.hw-value-item p{font-family:Poppins,Helvetica,Arial,sans-serif;font-weight:500;font-size:22px;line-height:39.6px;color:#fff;margin:0 0 22px}
/* form section overrides */
.hw-about-form .sub{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:18px;line-height:32.4px}
/* logos overrides */
.hw-about-logos .hw-logos-row{flex-wrap:nowrap;justify-content:flex-start;gap:30px;max-width:1152px;overflow:hidden}
.hw-about-logos .hw-logos-row img{max-height:none;width:206px;height:auto;object-fit:contain;flex:0 0 auto}
.hw-about-social{display:flex;justify-content:center;gap:6px;margin:34px 0 0}
.hw-about-social a{width:32px;height:32px;border-radius:3px;display:inline-flex;align-items:center;justify-content:center}
.hw-about-social svg{width:16px;height:16px;fill:#fff;display:block}
@media (max-width:767px){.hw-nav{display:none}.hw-burger{display:flex}body.hw-page{padding-top:149px}.hw-topbar{height:auto;min-height:85px;padding:9px 0}.hw-topbar .hw-wrap{flex-direction:row;flex-wrap:wrap;justify-content:center;gap:4px 14px;text-align:center;padding:0 16px}.hw-topbar .hw-phone,.hw-topbar .hw-email{justify-content:center}.hw-mainbar{height:64px}.hw-mainbar .hw-wrap{align-items:center}.hw-logo{align-self:center}.hw-logo img{height:35px}
.hw-about-hero{height:300px}.hw-about-hero h1{font-size:34px}
.hw-mv{padding:36px 0}.hw-mv-h{font-size:26px}.hw-mv-p{font-size:17px;line-height:1.7}
.hw-about-redband{padding:34px 0}.hw-about-redband p{font-size:34px}
.hw-about-cards{padding:36px 18px}.hw-about-cards .hw-card{flex-direction:column;gap:16px;padding:22px;text-align:center}.hw-about-cards .hw-card-ico{flex:none}.hw-about-cards .hw-card-ico img{width:200px}.hw-about-cards .hw-card-body h2{font-size:24px}
.hw-about-platform{flex-direction:column}.hw-plat-img{flex:none;min-height:280px}.hw-plat-text{flex:none;padding:40px 24px}
.hw-about-values{padding:40px 0}.hw-values-h{font-size:30px}.hw-value-item h2{font-size:23px}.hw-value-item p{font-size:19px;line-height:1.6}
.hw-about-logos .hw-logos-row{flex-wrap:wrap;justify-content:center;overflow:visible;gap:24px}.hw-about-logos .hw-logos-row img{width:150px}}</style>
<link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css" media="print" onload="this.media='all';this.onload=null">
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/content.css?v=<?php echo @filemtime(get_stylesheet_directory().'/assets/css/content.css'); ?>" media="print" onload="this.media='all';this.onload=null">
<noscript><link rel="stylesheet" href="/wp-content/hw-fonts/hw-fonts.css"><link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/content.css"></noscript>
<?php wp_head(); ?>
</head>
<body <?php body_class('hw-page hw-about'); ?>>
<?php get_template_part('template-parts/site-header'); ?>
<main id="hw-main">

  <!-- HERO -->
  <section class="hw-about-hero" style="background-image:url(/wp-content/uploads/2021/06/Trucks-for-Sale.jpg)">
    <h1>LEARN ABOUT VAC2GO</h1>
  </section>

  <!-- MISSION / VISION -->
  <section class="hw-mv">
    <div class="hw-c-wrap">
      <h2 class="hw-mv-h">Mission</h2>
      <p class="hw-mv-p">We strive to exceed expectations by providing best-in-class rental equipment and the highest quality customer experience in the industry.</p>
      <h2 class="hw-mv-h">Vision</h2>
      <p class="hw-mv-p last">We envision a future where our company is the go-to source for all industrial &amp; commercial equipment rental needs in North America, with a wide range of options and exceptional customer service, ensuring our customers have the best experience every time.</p>
    </div>
  </section>

  <!-- RED BAND -->
  <section class="hw-about-redband"><div class="hw-c-wrap"><p>Why Choose Vac2Go?</p></div></section>

  <!-- FEATURE CARDS -->
  <div class="hw-about-cards">
    <div class="hw-card">
      <div class="hw-card-ico"><img src="/wp-content/uploads/2026/07/PREMIUM-SERVICE-300x275.png" alt="Premium Service Experience" width="300" height="275" loading="lazy" decoding="async"></div>
      <div class="hw-card-body"><h2>Premium Service Experience</h2><p>Vac2Go is committed to delivering a premium service experience because we recognize our customers&#8217; need for rentals, accessories, and repairs to be easily accessible. We aim to simplify our customers&#8217; experience and exceed their expectations by offering comprehensive products, solutions, and support. Additionally, when you work with us, you have access to senior decision-makers, ensuring responsive and efficient service.</p></div>
    </div>
    <div class="hw-card">
      <div class="hw-card-ico"><img src="/wp-content/uploads/2024/08/Experience.jpg" alt="A Dedicated Rep Who Knows Your Job" width="250" height="250" loading="lazy" decoding="async"></div>
      <div class="hw-card-body"><h2>A Dedicated Rep Who Knows Your Job</h2><p>Every Vac2Go account is assigned a dedicated Regional Sales Manager, one person who knows your fleet needs, your job site, and your timeline. No call centers, no ticket queues, no starting over with someone new every time you call. When you need a truck fast or have a question about your rental, you&#8217;re talking to someone who already knows the answer.</p></div>
    </div>
    <div class="hw-card">
      <div class="hw-card-ico"><img src="/wp-content/uploads/2024/08/Availability.jpg" alt="Availability &amp; Urgency Response" width="250" height="250" loading="lazy" decoding="async"></div>
      <div class="hw-card-body"><h2>Availability &amp; Urgency Response</h2><p>Vac2Go ensures around-the-clock accessibility to provide flexibility and convenience for our customers&#8217; diverse scheduling needs. We value our customers and recognize the critical importance of meeting their needs promptly, especially given unpredictable project timelines.</p></div>
    </div>
    <div class="hw-card">
      <div class="hw-card-ico"><img src="/wp-content/uploads/2024/08/Diverse-Fleet.jpg" alt="Diverse Fleet" width="250" height="250" loading="lazy" decoding="async"></div>
      <div class="hw-card-body"><h2>Diverse Fleet</h2><p>Vac2Go&#8217;s fleet runs deep, not just wide. We carry equipment from specialty manufacturers like Keith Huber, GapVax, and Vermeer &#8211; brands built for serious hydro-excavation and industrial vacuum work, not a one-size-fits-all lineup. That depth means the right unit for your specific job, backed by real expertise in how each one performs.</p></div>
    </div>
    <div class="hw-card">
      <div class="hw-card-ico"><img src="/wp-content/uploads/2026/07/gps-300x275.png" alt="Know Exactly Where Your Unit Is" width="300" height="275" loading="lazy" decoding="async"></div>
      <div class="hw-card-body"><h2>Know Exactly Where Your Unit Is</h2><p>Every truck in the Vac2Go fleet comes equipped with GPS tracking, giving you real-time visibility into your equipment and crews in the field. No guessing when a unit will arrive, no wondering where it is mid-job &#8211; just a clear, live picture of your rental whenever you need it.</p></div>
    </div>
    <div class="hw-card">
      <div class="hw-card-ico"><img src="/wp-content/uploads/2024/08/Fleet-Maintenance.jpg" alt="Fleet Maintenance" width="250" height="250" loading="lazy" decoding="async"></div>
      <div class="hw-card-body"><h2>Fleet Maintenance</h2><p>At Vac2Go, we prioritize upholding a modern, well-maintained fleet of vacuum trucks. We value staying at the forefront of technology, meeting safety requirements, and minimizing downtime. The reliability of our fleet ensures operational stability, preventing potential costs such as increased wages, repair costs, and project delays, which can exceed tens of thousands of dollars due to equipment downtime.</p></div>
    </div>
    <div class="hw-card">
      <div class="hw-card-ico"><img src="/wp-content/uploads/2024/08/Expansive.jpg" alt="Expansive Footprint" width="250" height="250" loading="lazy" decoding="async"></div>
      <div class="hw-card-body"><h2>Expansive Footprint</h2><p>Vac2Go strategically places locations where demand for vacuum truck and hydro-excavation services is highest, not to build the biggest map, but to get equipment to your job site faster. A local branch means a truck dispatched from down the road, not routed through a centralized call center hundreds of miles away. That difference shows up in downtime avoided and jobs kept on schedule.</p></div>
    </div>
  </div>

  <!-- TRUCK + PLATFORM -->
  <section class="hw-about-platform">
    <div class="hw-plat-img" style="background-image:url(/wp-content/uploads/2026/03/DSC_0005-scaled.jpg)"></div>
    <div class="hw-plat-text">
      <h2 class="hw-plat-h">Vac2Go &#8211; The Nation&#8217;s Vacuum Truck &amp; Hydro-Excavation Rental Platform</h2>
      <p class="hw-plat-lead">Plus combination units, liquid and liquid-ring vacuum trucks, roll-offs, tractors, water trucks, and more &#8211; one place for every specialty rental need.</p>
      <p class="hw-plat-p">Founded in 2011, Vac2Go has grown from a single rental operation into a full-service specialty equipment platform. Now serving customers from 13 locations across 12 states. We help industrial, utility, municipal, environmental, and construction customers simplify operations by offering reliable, late-model equipment for both short- and long-term needs, eliminating the cost and burden of ownership. Vac2Go is registered in SAM.gov (UEI: [RUHNF6ARK4W7]), making us ready to support federal, state, and local government contracts and procurement requirements.</p>
      <p class="hw-plat-p">What sets Vac2Go apart is breadth: we&#8217;re not just a rental company. Our platform brings together rentals, equipment sales, service and repair, and parts and accessories, a single partner for the full lifecycle of your vacuum truck and hydro-excavation needs, no matter where you&#8217;re working.</p>
      <p class="hw-plat-p">Our extensive fleet features industry-leading manufacturers such as Vactor, Guzzler, Ledwell, Keith Huber, Super Products, GapVax, PresVac, Kaiser Premier, and Imperial Industries. Many of our units are equipped with standard or telescoping booms, as well as sludge pump offload systems, allowing us to support a wide range of job requirements.</p>
      <p class="hw-plat-p">Our investment in one of the industry&#8217;s most modern, late-model fleets means customers benefit from the latest technology, enhanced safety features, and dependable equipment that helps keep every job on schedule.</p>
      <p class="hw-plat-p">Every truck in our fleet is equipped with advanced GPS technology, giving customers real-time visibility into their equipment and crews in the field. Beyond rentals, Vac2Go offers a full line of vacuum truck accessories &#8211; hoses, fittings, reducers, replacement bags, cages, and more &#8211; along with operator training for teams new to vacuum truck operation or in need of a refresher.</p>
      <p class="hw-plat-p last">At Vac2Go, we are committed to reliability, safety, and best-in-class service. All equipment is maintained to strict OEM standards to ensure consistent performance and uptime. Our goal isn&#8217;t simply to provide equipment &#8212; it&#8217;s to be the dependable, single-source partner our customers can count on, wherever they operate and whatever the job requires.</p>
    </div>
  </section>

  <!-- VALUES -->
  <section class="hw-about-values" style="background-image:url(/wp-content/uploads/2024/05/6226836-scaled.jpg)">
    <div class="hw-c-wrap">
      <h2 class="hw-values-h">Vac2Go Values</h2>
      <div class="hw-values-list">
        <div class="hw-value-item"><h2>V &#8211; Value Our Team</h2><p>Treat all team members with respect and encourage open communication.</p></div>
        <div class="hw-value-item"><h2>A &#8211; Act with Integrity</h2><p>Act with honesty and transparency in all engagements.</p></div>
        <div class="hw-value-item"><h2>C &#8211; Customer Satisfaction</h2><p>Listen to our customers&#8217; needs and work urgently to provide solutions.</p></div>
        <div class="hw-value-item"><h2>2 &#8211; 2gether We Win!</h2><p>We accomplish our goals and objectives through teamwork.</p></div>
        <div class="hw-value-item"><h2>G &#8211; Growth Oriented</h2><p>We seek innovative ways to continuously improve our industry and ourselves.</p></div>
        <div class="hw-value-item"><h2>O &#8211; Outclass the Competition</h2><p>Exceed expectations with cutting-edge equipment and customer service.</p></div>
      </div>
    </div>
  </section>

  <!-- FORM -->
  <section class="hw-form-sec hw-about-form" style="background-image:url(/wp-content/uploads/2024/03/Red-Background.jpg)">
    <div class="hw-c-wrap">
      <h2>Need a unit or have a question? We can help.</h2>
      <p class="sub">Call our number <a href="tel:8558227246">1-855-VACS2GO</a> or send us a message in the form below.</p>
      <?php echo do_shortcode('[gravityform id="2" title="false" description="false" ajax="true"]'); ?>
    </div>
  </section>


</main>
<?php get_template_part('template-parts/site-footer'); wp_footer(); ?>
</body>
</html>
