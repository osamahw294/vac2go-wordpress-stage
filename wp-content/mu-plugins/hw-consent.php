<?php
/*
Plugin Name: HW Consent & Opt-Out (Vac2Go)
Description: In-house US notice-and-opt-out consent layer for the Vac2Go rebuild. Provides a compliant notice banner (no dismiss=accept dark pattern), a persistent "Do Not Sell or Share My Personal Information" control, Global Privacy Control (GPC) honoring, and Google Consent Mode v2 signaling so Google tags respect an opt-out. DRAFT for review — confirm approach with Vac2Go/counsel before launch. Reversible: delete this file.
*/
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* 1) Google Consent Mode v2 default — emitted as early as possible so it precedes GTM / Google tags.
      US model: advertising granted by DEFAULT, flipped to DENIED when the visitor opts out or sends GPC. */
add_action( 'wp_head', function () { ?>
<script>/* HW consent-mode default */
(function(){window.dataLayer=window.dataLayer||[];function g(){dataLayer.push(arguments);}window.gtag=window.gtag||g;
function ck(n){try{var m=document.cookie.match('(^|;)\\s*'+n+'\\s*=\\s*([^;]+)');return m?m[2]:''}catch(e){return ''}}
var gpc=(navigator.globalPrivacyControl===true);var out=(ck('hw_dns')==='1'||gpc);var s=out?'denied':'granted';
g('consent','default',{ad_storage:s,ad_user_data:s,ad_personalization:s,analytics_storage:'granted',wait_for_update:500});
window.hwConsent={optedOut:out,gpc:gpc};})();
</script>
<?php }, -9999 );

/* 2) Notice banner + Do-Not-Sell control + opt-out logic (footer). */
add_action( 'wp_footer', function () { ?>
<style id="hw-consent-css">
#hw-cbanner{position:fixed;left:0;right:0;bottom:0;z-index:2147483000;background:#141414;color:#fff;padding:16px 20px;box-shadow:0 -4px 18px rgba(0,0,0,.35);font-family:Roboto,Helvetica,Arial,sans-serif;display:none}
#hw-cbanner.show{display:block}
#hw-cbanner .hw-cb-in{max-width:1180px;margin:0 auto;display:flex;align-items:center;gap:18px;flex-wrap:wrap;justify-content:space-between}
#hw-cbanner p{margin:0;font-size:14px;line-height:1.55;flex:1 1 480px}
#hw-cbanner a{color:#ff8a94;text-decoration:underline}
#hw-cbanner .hw-cb-btns{display:flex;gap:10px;flex:0 0 auto;flex-wrap:wrap}
#hw-cbanner button,#hw-cbanner .hw-cb-link{font-family:Poppins,Helvetica,Arial,sans-serif;font-size:14px;font-weight:600;border:0;border-radius:4px;padding:11px 18px;cursor:pointer;white-space:nowrap}
#hw-cbanner .hw-cb-accept{background:var(--red,#e01f30);color:#fff}
#hw-cbanner .hw-cb-optout{background:transparent;color:#fff;border:1px solid #777;text-decoration:none;display:inline-flex;align-items:center}
#hw-dns-link{cursor:pointer}
#hw-dns-toast{position:fixed;left:50%;bottom:24px;transform:translateX(-50%);z-index:2147483001;background:#0a7d33;color:#fff;padding:12px 20px;border-radius:6px;font-family:Roboto,Arial,sans-serif;font-size:14px;display:none;box-shadow:0 6px 18px rgba(0,0,0,.3)}
@media(max-width:640px){#hw-cbanner .hw-cb-in{flex-direction:column;align-items:stretch;gap:12px}#hw-cbanner .hw-cb-btns{justify-content:center}}
</style>
<div id="hw-cbanner" role="dialog" aria-label="Privacy notice">
  <div class="hw-cb-in">
    <p>We use cookies and similar technologies for site analytics and advertising, and we work with partners as described in our <a href="/privacy-policy/">Privacy Policy</a>. You can opt out of the sale or sharing of your personal information at any time.</p>
    <div class="hw-cb-btns">
      <button type="button" class="hw-cb-accept" id="hw-cb-accept">Accept</button>
      <a class="hw-cb-optout" href="/do-not-sell/" id="hw-cb-optout">Do Not Sell or Share My Info</a>
    </div>
  </div>
</div>
<div id="hw-dns-toast" role="status"></div>
<script>/* HW consent + opt-out logic */
(function(){
  function ck(n){try{var m=document.cookie.match('(^|;)\\s*'+n+'\\s*=\\s*([^;]+)');return m?m[2]:''}catch(e){return ''}}
  function setck(n,v,d){try{var e=new Date();e.setTime(e.getTime()+d*864e5);document.cookie=n+'='+v+';expires='+e.toUTCString()+';path=/;SameSite=Lax'}catch(e){}}
  function g(){window.dataLayer=window.dataLayer||[];dataLayer.push(arguments);}
  var gpc=(navigator.globalPrivacyControl===true);
  function applyOptOut(silent){
    setck('hw_dns','1',365);
    g('consent','update',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied'});
    dataLayer.push({event:'hw_do_not_sell'}); // GTM hook: gate HubSpot/Pearl Diver ad+identity tags on this event
    if(!silent){var t=document.getElementById('hw-dns-toast');if(t){t.textContent='Your opt-out preference has been saved for this browser.';t.style.display='block';setTimeout(function(){t.style.display='none'},4000);}}
  }
  // GPC = binding opt-out signal: honor automatically
  if(gpc && ck('hw_dns')!=='1'){ applyOptOut(true); }
  // First-visit banner (skip if already acknowledged or already opted out)
  if(ck('hw_ack')!=='1' && ck('hw_dns')!=='1'){ var b=document.getElementById('hw-cbanner'); if(b){setTimeout(function(){b.classList.add('show')},400);} }
  var acc=document.getElementById('hw-cb-accept'); if(acc){acc.addEventListener('click',function(){setck('hw_ack','1',365);var b=document.getElementById('hw-cbanner');if(b)b.classList.remove('show');});}
  // "Do Not Sell" from the banner opt-out button (also its own page) — mark opt-out on click, then follow the link
  var ob=document.getElementById('hw-cb-optout'); if(ob){ob.addEventListener('click',function(){applyOptOut(false);setck('hw_ack','1',365);});}
  // Persistent footer link: inject into the site footer bar
  function injectFooterLink(){
    if(document.getElementById('hw-dns-link'))return;
    var bar=document.querySelector('.hw-footer-bar')||document.querySelector('.hw-foot-legal')||document.querySelector('footer');
    if(!bar)return;
    var sep=document.createTextNode('  |  ');
    var a=document.createElement('a'); a.id='hw-dns-link'; a.href='/do-not-sell/'; a.textContent='Do Not Sell or Share My Personal Information'; a.style.color='inherit'; a.style.textDecoration='underline';
    a.addEventListener('click',function(){ applyOptOut(false); });
    bar.appendChild(sep); bar.appendChild(a);
  }
  if(document.readyState!=='loading')injectFooterLink(); else document.addEventListener('DOMContentLoaded',injectFooterLink);
  // Expose for the /do-not-sell/ page button
  window.hwDoNotSell=function(){applyOptOut(false);};
})();
</script>
<?php } );

/* 3) Pearl Diver identity resolution — CONSENT-GATED port (replaces production's hardcoded, unconsented tag).
      Loads ldc.js ONLY when the visitor has NOT opted out and GPC is not asserted. Disclosed in the
      Privacy Policy, opt-out via "Do Not Sell", and GPC-suppressed here. pid/aid are subscription-specific
      (pulled from production 2026-08-28) — verify they are current at launch. */
add_action( 'wp_footer', function () { ?>
<script>/* HW Pearl Diver consent-gated loader */
(function(){
  function ck(n){try{var m=document.cookie.match('(^|;)\\s*'+n+'\\s*=\\s*([^;]+)');return m?m[2]:''}catch(e){return ''}}
  if(ck('hw_dns')==='1' || navigator.globalPrivacyControl===true){return;} /* opted out / GPC -> do NOT load identity resolution */
  var s=document.createElement('script');s.async=true;
  s.src='https://tag.pearldiver.io/ldc.js?pid=dcbe22363d8179062f802fcdca32e681&aid=16de03f2';
  document.head.appendChild(s);
})();
</script>
<?php } );
