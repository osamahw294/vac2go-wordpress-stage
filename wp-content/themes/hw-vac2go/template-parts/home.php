<?php if(!defined('ABSPATH'))exit; ?>
<?php get_template_part('template-parts/site-header'); ?>
<main id="hw-main">
  <section class="hw-hero" id="hw-hero">
    <div class="hw-slide hw-slide0 hw-active">
      <img class="hw-hero-img" src="/wp-content/uploads/2026/02/DSC_0003-hero-1280.webp" srcset="/wp-content/uploads/2026/02/DSC_0003-hero-828.webp 828w, /wp-content/uploads/2026/02/DSC_0003-hero-1280.webp 1280w" sizes="100vw" fetchpriority="high" decoding="async" alt="">
      <div class="hw-hero-overlay"></div>
      <div class="hw-hero-inner">
        <h1 class="hw-hero-title">The Nation&#8217;s Vacuum Truck &amp; Hydro-Excavation Rental Platform</h1>
        <p class="hw-hero-sub">One partner for rentals, sales, service, and parts &#8212; 13 locations across 12 states, serving industrial, utility, municipal, and construction customers since 2011.</p>
        <a class="hw-hero-btn" href="https://vac2go.com/contact/">Reserve a Truck</a>
      </div>
    </div>
    <div class="hw-slide" data-bg="/wp-content/uploads/2024/03/Our-Mission.jpg.webp">
      <div class="hw-hero-overlay"></div>
      <div class="hw-hero-inner">
        <h1 class="hw-hero-title">VAC2GO MISSION</h1>
        <p class="hw-hero-sub">We strive to exceed expectations by providing best-in-class rental equipment and the highest quality customer experience in the industry.</p>
      </div>
    </div>
    <div class="hw-slide" data-bg="/wp-content/uploads/2024/03/Trucks-For-Sale.jpg.webp">
      <div class="hw-hero-overlay"></div>
      <div class="hw-hero-inner">
        <h1 class="hw-hero-title">RENT YOUR NEXT VACUUM TRUCK FROM US!</h1>
        <p class="hw-hero-sub">Vac2Go provides vacuum truck rentals to companies and contractors across the United States!</p>
        <a class="hw-hero-btn" href="https://vac2go.com/contact/">RENT THIS TRUCK</a>
      </div>
    </div>
    <div class="hw-slide" data-bg="/wp-content/uploads/2026/06/web-scaled.jpg.webp">
      <div class="hw-hero-overlay"></div>
      <div class="hw-hero-inner">
        <h1 class="hw-hero-title">START A NEW CAREER!</h1>
        <p class="hw-hero-sub">Check out our current job openings and take the next step in your career with Vac2Go. Join us and be part of something great!</p>
        <a class="hw-hero-btn" href="https://workforcenow.adp.com/mascsr/default/mdf/recruitment/recruitment.html?cid=8dd1ebd8-4d34-4d62-b43f-ea145a1202d9&amp;ccId=19000101_000001&amp;lang=en_US">APPLY NOW</a>
      </div>
    </div>
    <div class="hw-slide" data-bg="/wp-content/uploads/2025/11/DSC_2927-scaled.jpg.webp">
      <div class="hw-hero-overlay"></div>
      <div class="hw-hero-inner">
        <h1 class="hw-hero-title">WE DON&#8217;T JUST RENT VACUUM TRUCKS&#8230; WE SELL THEM TOO!</h1>
        <a class="hw-hero-btn" href="https://vac2go.com/trucks-for-sale/">VIEW OUR CURRENT INVENTORY</a>
      </div>
    </div>
    <button class="hw-arrow hw-prev" type="button" aria-label="Previous slide">&#8249;</button>
    <button class="hw-arrow hw-next" type="button" aria-label="Next slide">&#8250;</button>
    <div class="hw-dots">
      <button class="hw-dot hw-dot-active" type="button" aria-label="Slide 1"></button>
      <button class="hw-dot" type="button" aria-label="Slide 2"></button>
      <button class="hw-dot" type="button" aria-label="Slide 3"></button>
      <button class="hw-dot" type="button" aria-label="Slide 4"></button>
      <button class="hw-dot" type="button" aria-label="Slide 5"></button>
    </div>
    <script id="hw-slider-js">(function(){var h=document.getElementById('hw-hero');if(!h)return;if(window.matchMedia('(max-width:767px)').matches)return;var sl=[].slice.call(h.querySelectorAll('.hw-slide')),dt=[].slice.call(h.querySelectorAll('.hw-dot')),c=0,timer;function show(n){n=(n+sl.length)%sl.length;sl[c].classList.remove('hw-active');sl[n].classList.add('hw-active');if(dt[c])dt[c].classList.remove('hw-dot-active');if(dt[n])dt[n].classList.add('hw-dot-active');c=n;}function start(){clearInterval(timer);timer=setInterval(function(){show(c+1);},7000);}function nav(n){show(n);start();}dt.forEach(function(d,i){d.addEventListener('click',function(){nav(i);});});var p=h.querySelector('.hw-prev'),nx=h.querySelector('.hw-next');if(p)p.addEventListener('click',function(){nav(c-1);});if(nx)nx.addEventListener('click',function(){nav(c+1);});function lz(){sl.forEach(function(s){var b=s.getAttribute('data-bg');if(b){s.style.backgroundImage="url("+b+")";s.removeAttribute('data-bg');}});}if('requestIdleCallback' in window){requestIdleCallback(lz,{timeout:3000});}else{setTimeout(lz,1500);}start();})();</script>
  </section>

  <section class="hw-s1">
    <div class="hw-s1-grid">
      <div class="hw-s1-left">
        <h4 class="hw-s1-h4">ONE PLACE. EVERY NEED.</h4>
        <div class="hw-s1-list"><span>&gt; RENTALS</span><span>&gt; SALES</span><span>&gt; SERVICE &amp; REPAIR</span><span>&gt; PARTS &amp; ACCESSORIES</span></div>
        <p class="hw-s1-para">Since 2011, Vac2Go has grown into a full-service specialty rental platform &#8212; 13 locations in 12 states, backed by every major manufacturer brand in the industry. From rentals to sales, service, and parts, we&#8217;re a single partner for every stage of your equipment&#8217;s life.</p>
        <a class="hw-s1-btn" href="https://vac2go.com/contact/">READY TO RENT A TRUCK? START HERE.</a>
        <a class="hw-s1-btn" href="https://vac2go.com/rent-a-truck/">HOW TO RENT A TRUCK FROM VAC2GO</a>
      </div>
      <div class="hw-s1-right">
        <h3 class="hw-s1-righth">VACUUM TRUCK &amp; SPECIALTY EQUIPMENT RENTALS ACROSS THE UNITED STATES.</h3>
        <img loading="lazy" decoding="async" src="https://vac2go.com/wp-content/uploads/2025/01/Rental-Side-Image-1-1280x768-1.jpg" width="605" height="363" alt="Vacuum truck">
      </div>
    </div>
  </section>

  <section class="hw-s2">
    <div class="hw-s2-grid">
      <div class="hw-s2-col">
        <h3 class="hw-s2-h">Need to Rent a Truck?</h3>
        <img loading="lazy" decoding="async" src="https://vac2go.com/wp-content/uploads/2025/01/Vactor-Combo.jpg" width="320" height="229" alt="Rent a truck">
        <a class="hw-s2-btn" href="https://vac2go.com/vac-truck-rentals/">RENT A TRUCK</a>
      </div>
      <div class="hw-s2-col">
        <h3 class="hw-s2-h">Come Join our Team!</h3>
        <img loading="lazy" decoding="async" src="https://vac2go.com/wp-content/uploads/2025/10/DSC_0007-scaled.jpg" width="320" height="213" alt="Join our team">
        <a class="hw-s2-btn" href="https://vac2go.com/careers/">APPLY TODAY</a>
      </div>
      <div class="hw-s2-col">
        <h3 class="hw-s2-h">Need Parts &amp; Accessories?</h3>
        <img loading="lazy" decoding="async" src="https://vac2go.com/wp-content/uploads/2025/01/Vac2Go-Accessories.png" width="320" height="229" alt="Parts and accessories">
        <a class="hw-s2-btn" href="https://vactruckstore.com/">SHOP ACCESSORIES</a>
      </div>
    </div>
  </section>

  <section class="hw-s3">
    <h2 class="hw-s3-h">Brands We Offer</h2>
    <div class="hw-s3-underline"></div>
    <div class="hw-s3-mq"><div class="hw-s3-track">
      <?php
      $hw_brands = array(
        array('2023/02/Vactor-Logo.jpg','Vactor'),
        array('2023/02/Super-Products.jpg','Super Products'),
        array('2023/02/Presvac-Logo.jpg','PresVac'),
        array('2023/02/Tornado-Logo.jpg','Tornado'),
        array('2023/02/Shellvac.jpg','Schellvac'),
        array('2023/03/Dragon-Logo.jpg','Dragon'),
        array('2023/03/GapVax.jpg','GapVax'),
        array('2023/03/Guzzler.jpg','Guzzler'),
        array('2023/03/Kaiser-Logo.jpg','Kaiser'),
        array('2023/03/Keith-Huber.jpg','Keith Huber'),
        array('2023/03/CTOS-Logo.jpg','CTOS'),
        array('2025/02/Boss-Vac-Logo.jpg','Boss Vac'),
        array('2025/04/TruVac-Logo.jpg','TruVac'),
        array('2025/09/Vermeer-Logo-300x169.jpg','Vermeer'),
      );
      // render twice for a seamless infinite loop; prod-mirror host loads reliably on staging
      for($hw_i=0;$hw_i<2;$hw_i++){ foreach($hw_brands as $bz){
        echo '<img class="hw-s3-logo" loading="lazy" decoding="async" src="/wp-content/uploads/'.$bz[0].'" alt="'.($hw_i? '' : esc_attr($bz[1])).'"'.($hw_i?' aria-hidden="true"':'').'>';
      }}
      ?>
    </div></div>
  </section>

  <section class="hw-s4">
    <div class="hw-s4-inner">
      <h2 class="hw-s4-h">We Have A HUGE Fleet of Trucks For All of Your Rental Needs!</h2>
      <p class="hw-s4-sub">Every truck is GPS-tracked &#8211; know exactly where your unit is, in real time.</p>
    </div>
  </section>

  <section class="hw-s5">
    <div class="hw-s5-grid">
      <div class="hw-s5-card"><img loading="lazy" decoding="async" src="https://vac2go.com/wp-content/uploads/2021/06/Industrial-Vacuums-1.jpg" width="403" height="242" alt="Industrial Vacuum Trucks"><h4 class="hw-s5-h">Industrial Vacuum Trucks</h4><a class="hw-s5-btn" href="https://vac2go.com/industrial-vacuum/">LEARN MORE</a></div>
      <div class="hw-s5-card"><img loading="lazy" decoding="async" src="https://vac2go.com/wp-content/uploads/2021/06/Hydroexcavators.jpg" width="403" height="242" alt="Hydro Excavators"><h4 class="hw-s5-h">Hydro Excavators</h4><a class="hw-s5-btn" href="https://vac2go.com/hydro-excavators/">LEARN MORE</a></div>
      <div class="hw-s5-card"><img loading="lazy" decoding="async" src="https://vac2go.com/wp-content/uploads/2021/06/Combination-Units.jpg" width="403" height="242" alt="Combination Units"><h4 class="hw-s5-h">Combination Units</h4><a class="hw-s5-btn" href="https://vac2go.com/combination-units-3/">LEARN MORE</a></div>
      <div class="hw-s5-card"><img loading="lazy" decoding="async" src="https://vac2go.com/wp-content/uploads/2021/06/Liquid-Vac-1.jpg" width="403" height="242" alt="Liquid Vacuum Trucks"><h4 class="hw-s5-h">Liquid Vacuum Trucks</h4><a class="hw-s5-btn" href="https://vac2go.com/liquid-vacuum-trucks/">LEARN MORE</a></div>
      <div class="hw-s5-card"><img loading="lazy" decoding="async" src="https://vac2go.com/wp-content/uploads/2021/06/Liquid-Ring-Vac-Trucks.jpg" width="403" height="231" alt="Liquid Ring Vacuum Trucks"><h4 class="hw-s5-h">Liquid Ring Vacuum Trucks</h4><a class="hw-s5-btn" href="https://vac2go.com/liquid-ring-vacuum-trucks/">LEARN MORE</a></div>
      <div class="hw-s5-card"><img loading="lazy" decoding="async" src="https://vac2go.com/wp-content/uploads/2024/11/Water-Truck.jpg" width="403" height="242" alt="Additional Rental Equipment"><h4 class="hw-s5-h">Additional Rental Equipment</h4><a class="hw-s5-btn" href="https://vac2go.com/additional-rental-equipment/">LEARN MORE</a></div>
      <div class="hw-s5-card"><img loading="lazy" decoding="async" src="https://vac2go.com/wp-content/uploads/2025/02/Vac2Go-Pull-Behind-Equipment.jpg" width="403" height="242" alt="Pull-Behind Equipment"><h4 class="hw-s5-h">Pull-Behind Equipment</h4><a class="hw-s5-btn" href="https://vac2go.com/pull-behind-equipment/">LEARN MORE</a></div>
    </div>
  </section>

  <section class="hw-s6">
    <div class="hw-s6-grid">
      <div class="hw-s6-left">
        <img class="hw-s6-logo" src="https://vac2go.com/wp-content/uploads/2022/06/Vac2GoNewLogoFinalWhite.png" width="200" alt="Vac2Go">
        <p class="hw-s6-info"><span class="hw-red">Phone:</span> <a href="tel:1-855-822-7246">1-855-VACS2GO</a><br>11120 Plantside Drive<br>Louisville, KY 40299</p>
        <div class="hw-s6-social">
          <a href="https://www.facebook.com/Vac2Go" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 22v-8h2.7l.4-3.1h-3.1V8.9c0-.9.25-1.5 1.55-1.5H17V4.6c-.3 0-1.3-.1-2.45-.1-2.4 0-4.05 1.5-4.05 4.2v2.2H7.8V14h2.7v8z"/></svg></a>
          <a href="https://www.instagram.com/vac2gorentals/" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.2c3.2 0 3.6 0 4.85.07 1.17.05 1.8.25 2.23.42.56.22.96.48 1.38.9.42.42.68.82.9 1.38.17.42.37 1.06.42 2.23.06 1.25.07 1.63.07 4.8s-.01 3.55-.07 4.8c-.05 1.17-.25 1.8-.42 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.17-1.06.37-2.23.42-1.25.06-1.63.07-4.85.07s-3.6-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.42a3.7 3.7 0 01-1.38-.9 3.7 3.7 0 01-.9-1.38c-.17-.42-.37-1.06-.42-2.23C2.21 15.55 2.2 15.17 2.2 12s.01-3.55.07-4.8c.05-1.17.25-1.8.42-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.17 1.06-.37 2.23-.42C8.4 2.21 8.8 2.2 12 2.2zm0 3.05A6.75 6.75 0 1012 18.75 6.75 6.75 0 0012 5.25zm0 11.13A4.38 4.38 0 1112 7.62a4.38 4.38 0 010 8.76zm6.95-11.4a1.58 1.58 0 11-3.15 0 1.58 1.58 0 013.15 0z"/></svg></a>
          <a href="https://www.linkedin.com/company/vac2go/" target="_blank" rel="noopener" aria-label="LinkedIn"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M4.98 3.5A2.5 2.5 0 002.5 6a2.5 2.5 0 002.48 2.5A2.5 2.5 0 007.5 6a2.5 2.5 0 00-2.52-2.5zM3 9h4v12H3zM9.5 9H13v1.7h.05c.5-.9 1.7-1.85 3.5-1.85 3.75 0 4.45 2.35 4.45 5.4V21H17v-5.7c0-1.35-.02-3.1-1.9-3.1-1.9 0-2.2 1.48-2.2 3v5.8H9.5z"/></svg></a>
        </div>
        <p class="hw-s6-mission"><strong>Our Mission:</strong> At Vac2Go we strive to exceed expectations by providing best-in-class rental equipment and the highest quality customer experience in the industry.</p>
      </div>
      <div class="hw-s6-right">
        <h2 class="hw-s6-h">Contact Vac2Go Today</h2>
        <?php echo do_shortcode('[gravityform id="2" title="false" description="false" ajax="true"]'); ?>
      </div>
    </div>
  </section>

  <section class="hw-s7">
    <div class="hw-s7-grid">
      <div class="hw-s7-left">
        <h2 class="hw-s7-h">Need a unit or have a question?<br>We can help &#8211; fast.</h2>
        <p>Every Vac2Go account gets a dedicated Regional Sales Manager, one person who knows your job and your timeline. Call anytime &#8211; we&#8217;re available 24/7 nationwide.</p>
        <p>Our fleet includes wet/dry vacuum trucks, 407/412 DOT-certified liquid vacuum trucks, hydro excavators, combination units, liquid ring systems, tankers, roll-offs, and two-box trailers, plus specialty equipment like Vactor Dense Phase, Guzzler XCR, and Keith Huber Knight units.</p>
        <p>Every truck is GPS-tracked, giving you real-time visibility into your equipment and crew in the field.</p>
        <a class="hw-s7-btn" href="https://vac2go.com/contact/">RENT A TRUCK HERE!</a>
      </div>
      <div class="hw-s7-right">
        <h3 class="hw-s7-loc-h">Our Locations</h3>
        <ul class="hw-s7-locs"><li>Alabama</li><li>Arizona</li><li>Florida</li><li>Georgia</li><li>Indiana</li><li>Kentucky</li><li>New Jersey</li><li>Ohio</li><li>South Carolina</li><li>Tennessee</li><li>Texas</li><li>Utah</li></ul>
      </div>
    </div>
  </section>

  <footer class="hw-s8">
    <div class="hw-s8-mq"><div class="hw-s8-track">
      <?php
      $hw_badges = array(
        array('2024/05/argosy.jpg','Argosy'),
        array('2024/05/NASTT-Logo.jpg','NASTT'),
        array('2024/05/wjta-logo.jpg','WJTA'),
        array('2025/10/UTCA_New-logo-white-circle-300x291.jpg','UTCA'),
        array('2026/03/AZUCA_logo-300x164.png','AZUCA'),
        array('2026/07/Sam.gov-Vendor-300x275.png','SAM.gov Vendor'),
        array('2024/05/chamber-logo.jpg','Chamber of Commerce'),
        array('2025/07/BIF-Logo.jpg','BIF'),
        array('2024/05/Fast-5000.jpg','Fast 5000'),
        array('2024/05/Fast-50-1.jpg','Fast 50'),
      );
      for($hw_i=0;$hw_i<2;$hw_i++){ foreach($hw_badges as $bz){
        echo '<img class="hw-s8-badge" loading="lazy" decoding="async" src="/wp-content/uploads/'.$bz[0].'" alt="'.($hw_i? '' : esc_attr($bz[1])).'"'.($hw_i?' aria-hidden="true"':'').'>';
      }}
      ?>
    </div></div>
    <div class="hw-s8-social">
      <a href="https://www.facebook.com/Vac2Go" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 22v-8h2.7l.4-3.1h-3.1V8.9c0-.9.25-1.5 1.55-1.5H17V4.6c-.3 0-1.3-.1-2.45-.1-2.4 0-4.05 1.5-4.05 4.2v2.2H7.8V14h2.7v8z"/></svg></a>
      <a href="https://x.com/Vac2Go" target="_blank" rel="noopener" aria-label="X"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.2 3h3.3l-7.2 8.2L21.8 21h-6.6l-5.2-6.8L4.1 21H.8l7.7-8.8L2 3h6.8l4.7 6.2zm-1.16 16h1.83L7.1 4.9H5.14z"/></svg></a>
      <a href="https://www.linkedin.com/company/vac2go/" target="_blank" rel="noopener" aria-label="LinkedIn"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M4.98 3.5A2.5 2.5 0 002.5 6a2.5 2.5 0 002.48 2.5A2.5 2.5 0 007.5 6a2.5 2.5 0 00-2.52-2.5zM3 9h4v12H3zM9.5 9H13v1.7h.05c.5-.9 1.7-1.85 3.5-1.85 3.75 0 4.45 2.35 4.45 5.4V21H17v-5.7c0-1.35-.02-3.1-1.9-3.1-1.9 0-2.2 1.48-2.2 3v5.8H9.5z"/></svg></a>
      <a href="https://www.instagram.com/vac2gorentals/" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.2c3.2 0 3.6 0 4.85.07 1.17.05 1.8.25 2.23.42.56.22.96.48 1.38.9.42.42.68.82.9 1.38.17.42.37 1.06.42 2.23.06 1.25.07 1.63.07 4.8s-.01 3.55-.07 4.8c-.05 1.17-.25 1.8-.42 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.17-1.06.37-2.23.42-1.25.06-1.63.07-4.85.07s-3.6-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.42a3.7 3.7 0 01-1.38-.9 3.7 3.7 0 01-.9-1.38c-.17-.42-.37-1.06-.42-2.23C2.21 15.55 2.2 15.17 2.2 12s.01-3.55.07-4.8c.05-1.17.25-1.8.42-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.17 1.06-.37 2.23-.42C8.4 2.21 8.8 2.2 12 2.2zm0 3.05A6.75 6.75 0 1012 18.75 6.75 6.75 0 0012 5.25zm0 11.13A4.38 4.38 0 1112 7.62a4.38 4.38 0 010 8.76zm6.95-11.4a1.58 1.58 0 11-3.15 0 1.58 1.58 0 013.15 0z"/></svg></a>
      <a href="https://www.youtube.com/@Vac2Go/playlists" target="_blank" rel="noopener" aria-label="YouTube"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M23 12s0-3.05-.4-4.5a2.55 2.55 0 00-1.8-1.8C19.2 5.3 12 5.3 12 5.3s-7.2 0-8.8.4a2.55 2.55 0 00-1.8 1.8C1 9 1 12 1 12s0 3.05.4 4.5a2.55 2.55 0 001.8 1.8c1.6.4 8.8.4 8.8.4s7.2 0 8.8-.4a2.55 2.55 0 001.8-1.8c.4-1.45.4-4.5.4-4.5zM9.75 15.5v-7l6 3.5z"/></svg></a>
    </div>
    <div class="hw-s8-bar">&copy;2026 Vac2Go | Vacuum Truck Rental Company | 1-855-VACS2GO | <a href="https://vac2go.com/privacy-policy/">Privacy Policy</a> | SAM.gov Registered &#8211; UEI: [RUHNF6ARK4W7] | CAGE: [16TM7]</div>
  </footer>
</main>
