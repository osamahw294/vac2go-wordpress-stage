<?php if(!defined('ABSPATH'))exit; ?>
<footer class="hw-footer">
  <div class="hw-foot-mq"><div class="hw-foot-track">
    <?php
    $hw_fbadges = array(
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
    for($hw_i=0;$hw_i<2;$hw_i++){ foreach($hw_fbadges as $bz){
      echo '<img class="hw-foot-badge" loading="lazy" decoding="async" src="/wp-content/uploads/'.$bz[0].'" alt="'.($hw_i? '' : esc_attr($bz[1])).'"'.($hw_i?' aria-hidden="true"':'').'>';
    }}
    ?>
  </div></div>
  <div class="hw-foot-social">
    <a href="https://www.facebook.com/Vac2Go" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 22v-8h2.7l.4-3.1h-3.1V8.9c0-.9.25-1.5 1.55-1.5H17V4.6c-.3 0-1.3-.1-2.45-.1-2.4 0-4.05 1.5-4.05 4.2v2.2H7.8V14h2.7v8z"/></svg></a>
    <a href="https://x.com/Vac2Go" target="_blank" rel="noopener" aria-label="X"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.2 3h3.3l-7.2 8.2L21.8 21h-6.6l-5.2-6.8L4.1 21H.8l7.7-8.8L2 3h6.8l4.7 6.2zm-1.16 16h1.83L7.1 4.9H5.14z"/></svg></a>
    <a href="https://www.linkedin.com/company/vac2go/" target="_blank" rel="noopener" aria-label="LinkedIn"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M4.98 3.5A2.5 2.5 0 002.5 6a2.5 2.5 0 002.48 2.5A2.5 2.5 0 007.5 6a2.5 2.5 0 00-2.52-2.5zM3 9h4v12H3zM9.5 9H13v1.7h.05c.5-.9 1.7-1.85 3.5-1.85 3.75 0 4.45 2.35 4.45 5.4V21H17v-5.7c0-1.35-.02-3.1-1.9-3.1-1.9 0-2.2 1.48-2.2 3v5.8H9.5z"/></svg></a>
    <a href="https://www.instagram.com/vac2gorentals/" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.2c3.2 0 3.6 0 4.85.07 1.17.05 1.8.25 2.23.42.56.22.96.48 1.38.9.42.42.68.82.9 1.38.17.42.37 1.06.42 2.23.06 1.25.07 1.63.07 4.8s-.01 3.55-.07 4.8c-.05 1.17-.25 1.8-.42 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.17-1.06.37-2.23.42-1.25.06-1.63.07-4.85.07s-3.6-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.42a3.7 3.7 0 01-1.38-.9 3.7 3.7 0 01-.9-1.38c-.17-.42-.37-1.06-.42-2.23C2.21 15.55 2.2 15.17 2.2 12s.01-3.55.07-4.8c.05-1.17.25-1.8.42-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.17 1.06-.37 2.23-.42C8.4 2.21 8.8 2.2 12 2.2zm0 3.05A6.75 6.75 0 1012 18.75 6.75 6.75 0 0012 5.25zm0 11.13A4.38 4.38 0 1112 7.62a4.38 4.38 0 010 8.76zm6.95-11.4a1.58 1.58 0 11-3.15 0 1.58 1.58 0 013.15 0z"/></svg></a>
    <a href="https://www.youtube.com/@Vac2Go/playlists" target="_blank" rel="noopener" aria-label="YouTube"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M23 12s0-3.05-.4-4.5a2.55 2.55 0 00-1.8-1.8C19.2 5.3 12 5.3 12 5.3s-7.2 0-8.8.4a2.55 2.55 0 00-1.8 1.8C1 9 1 12 1 12s0 3.05.4 4.5a2.55 2.55 0 001.8 1.8c1.6.4 8.8.4 8.8.4s7.2 0 8.8-.4a2.55 2.55 0 001.8-1.8c.4-1.45.4-4.5.4-4.5zM9.75 15.5v-7l6 3.5z"/></svg></a>
  </div>
  <div class="hw-footer-bar">&copy;2026 Vac2Go | Vacuum Truck Rental Company | 1-855-VACS2GO | <a href="https://vac2go.com/privacy-policy/">Privacy Policy</a> | SAM.gov Registered &#8211; UEI: [RUHNF6ARK4W7] | CAGE: [16TM7]</div>
</footer>
<style id="hw-foot-css">
.hw-footer{background:#fff}
.hw-foot-mq{max-width:1120px;margin:0 auto;padding:46px 10px 0;overflow:hidden;-webkit-mask-image:linear-gradient(90deg,transparent,#000 5%,#000 95%,transparent);mask-image:linear-gradient(90deg,transparent,#000 5%,#000 95%,transparent)}
.hw-foot-track{display:flex;align-items:center;width:max-content;animation:hw-foot-marquee 40s linear infinite}
.hw-foot-mq:hover .hw-foot-track{animation-play-state:paused}
.hw-foot-badge{height:82px;width:auto;flex:0 0 auto;margin-right:56px;object-fit:contain}
@keyframes hw-foot-marquee{from{transform:translateX(0)}to{transform:translateX(-50%)}}
@media(prefers-reduced-motion:reduce){.hw-foot-track{animation:none}}
.hw-foot-social{display:flex;justify-content:center;gap:10px;margin:26px 0 24px}
.hw-foot-social a{width:32px;height:32px;border-radius:4px;background:#e01f30;color:#fff;display:flex;align-items:center;justify-content:center;text-decoration:none}
.hw-foot-social a svg{width:16px;height:16px;display:block}
.hw-footer-bar{background:#e01f30;color:#fff;font-family:"Open Sans",Helvetica,Arial,sans-serif;font-size:13px;text-align:center;padding:12px 20px}
.hw-footer-bar a{color:#fff;text-decoration:underline}
@media(max-width:767px){.hw-foot-mq{padding-top:34px}.hw-foot-badge{height:58px;margin-right:40px}}
</style>
