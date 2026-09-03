<?php
/*
 * Plugin Name: HW Delay Third-Party JS
 * Description: Delays marketing / reCAPTCHA / analytics scripts until first user
 *   interaction (or a fallback timeout), keeping ~900KB off the initial load so
 *   the LCP hero isn't bandwidth-starved. First-party jQuery/Divi/slider JS is
 *   never delayed. Add ?nodelay=1 to disable for debugging.
 */
if (!defined('ABSPATH')) exit;

add_action('template_redirect', function () {
    if (is_admin() || is_feed() || (defined('DOING_AJAX') && DOING_AJAX)) return;
    if (isset($_GET['nodelay'])) return;
    ob_start('hw_delay_js');
}, 9);

function hw_delay_js($html) {
    if (!$html || stripos($html, '</body>') === false) return $html;

    // Only these third-party scripts are delayed. Everything else loads normally.
    // NOTE: reCAPTCHA is deliberately NOT delayed — Gravity Forms needs grecaptcha.ready() at load
    // time to initialize form validation; delaying it broke form submission ("n.ready is not a function").
    $needles = array(
        'googletagmanager.com', 'gtag/js', 'gtm.js', 'google-analytics.com', 'analytics.js',
        'js.hs-scripts.com', 'js.hs-banner.com', 'js.hsforms.net', 'js.hsadspixel.net',
        'js.hscollectedforms.net', 'hs-analytics.net', '/leadin', 'leadin.js',
        'doubleclick.net', 'connect.facebook.net',
    );

    $delayed = 0;
    $html = preg_replace_callback('#<script\b[^>]*>.*?</script>#is', function ($m) use ($needles, &$delayed) {
        $tag = $m[0];
        if (stripos($tag, 'text/hwdelay') !== false) return $tag; // already delayed
        foreach ($needles as $n) {
            if (stripos($tag, $n) !== false) {
                $delayed++;
                $out = preg_replace('#\stype=(["\']).*?\1#i', '', $tag, 1);       // strip existing type
                $out = preg_replace('#<script\b#i', '<script type="text/hwdelay"', $out, 1);
                return $out;
            }
        }
        return $tag;
    }, $html);

    if (!$delayed) return $html;

    $loader = '<script>(function(){var done=false;function go(){if(done)return;done=true;'
        . 'var q=document.querySelectorAll(\'script[type="text/hwdelay"]\');var i=0;'
        . '(function next(){if(i>=q.length)return;var o=q[i++];var n=document.createElement("script");'
        . 'for(var a=0;a<o.attributes.length;a++){var at=o.attributes[a];if(at.name==="type")continue;'
        . 'n.setAttribute(at.name,at.value);}if(o.textContent)n.textContent=o.textContent;'
        . 'if(o.src){n.onload=n.onerror=next;o.parentNode.replaceChild(n,o);}'
        . 'else{o.parentNode.replaceChild(n,o);next();}})();}'
        . 'var ev=["mousemove","mousedown","keydown","touchstart","scroll","wheel"];'
        . 'function fire(){ev.forEach(function(e){window.removeEventListener(e,fire,{passive:true});});go();}'
        . 'ev.forEach(function(e){window.addEventListener(e,fire,{passive:true});});'
        . 'setTimeout(go,10000);})();</script>';
    return str_ireplace('</body>', $loader . '</body>', $html);
}
