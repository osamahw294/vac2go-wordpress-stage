<?php
/* Plugin Name: HW Delay JS (front page) — defer real JS to first interaction (skips JSON/data + module scripts) */
if (!defined('ABSPATH')) exit;
add_action('template_redirect', function () {
  if (!is_front_page() && !is_home() && !is_page()) return;
  ob_start(function ($html) {
    if (stripos($html, '</body>') === false) return $html;
    $html = preg_replace_callback('#<script\b([^>]*)>(.*?)</script>#is', function ($m) {
      $attr = $m[1]; $body = $m[2];
      // only defer executable JavaScript; leave JSON/ld+json/importmap/module and markers alone
      if (preg_match('#\stype\s*=\s*["\']?([^"\'\s>]+)#i', $attr, $tm)) {
        $t = strtolower($tm[1]);
        if ($t !== 'text/javascript' && $t !== 'application/javascript' && $t !== 'text/ecmascript') return $m[0];
      }
      if (stripos($attr, 'hw-delay-loader') !== false) return $m[0];
      if (stripos($attr, 'hw-slider-js') !== false) return $m[0];
      if (stripos($attr, 'hw-burger-js') !== false) return $m[0];
      $attr = preg_replace('#\stype\s*=\s*(["\']).*?\1#i', '', $attr);
      $attr = preg_replace('#\ssrc\s*=#i', ' data-hwsrc=', $attr);
      return '<script type="text/hwdelay"' . $attr . '>' . $body . '</script>';
    }, $html);
    $loader = '<script id="hw-delay-loader">(function(){var done=false;function go(){if(done)return;done=true;var s=[].slice.call(document.querySelectorAll(\'script[type="text/hwdelay"]\'));(function run(i){if(i>=s.length)return;var o=s[i],n=document.createElement("script"),j;for(j=0;j<o.attributes.length;j++){var a=o.attributes[j];if(a.name==="type")continue;if(a.name==="data-hwsrc"){n.src=a.value;}else{n.setAttribute(a.name,a.value);}}if(o.textContent)n.textContent=o.textContent;var d2=false;function nxt(){if(d2)return;d2=true;run(i+1);}try{if(o.parentNode){if(n.src){n.onload=nxt;n.onerror=nxt;o.parentNode.replaceChild(n,o);}else{o.parentNode.replaceChild(n,o);nxt();}}else{nxt();}}catch(e){nxt();}})(0);}["mousedown","mousemove","scroll","touchstart","keydown","click","wheel"].forEach(function(e){window.addEventListener(e,go,{once:true,passive:true});});})();</script>';
    return str_replace('</body>', $loader . '</body>', $html);
  });
}, 0);
