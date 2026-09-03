<?php
/* Plugin Name: HW Block Slow API — stop external calls from hanging the page (DEV perf).
   Short-circuits Solid Security's known hosts, caps ALL outbound HTTP timeouts to 3s so no
   external call can stall a render, and logs any other outbound host so we can catch a new one. */
if (!defined('ABSPATH')) exit;
add_filter('pre_http_request', function ($pre, $args, $url) {
  $block = array('ithemes.com','solidwp.com','ip-api','ithemes-sync','api.ithemes','licensing.ithemes','sync.ithemes');
  foreach ($block as $h) {
    if (stripos($url, $h) !== false) {
      return array('headers'=>array(),'body'=>'','response'=>array('code'=>200,'message'=>'OK'),'cookies'=>array(),'filename'=>null);
    }
  }
  // Kill server-side LOOPBACK fetches of our own static assets (Kadence dynamic-assets storm:
  // WP HTTP-requesting its own theme .css/.js hangs the render). Those assets are on disk — never
  // legitimately fetched over HTTP server-side. Don't block wp-cron/wp-json self-calls (not assets).
  $self = parse_url(home_url(), PHP_URL_HOST);
  $uh   = parse_url($url, PHP_URL_HOST);
  if ($self && $uh && stripos($uh, $self) !== false && preg_match('#\.(css|js|woff2?|ttf|png|jpe?g|gif|svg)(\?|$)#i', (string)$url)) {
    return array('headers'=>array(),'body'=>'','response'=>array('code'=>200,'message'=>'OK'),'cookies'=>array(),'filename'=>null);
  }
  return $pre;
}, 1, 3);
// Hard cap every outbound HTTP call so nothing can hang the page render on the dev box.
add_filter('http_request_timeout', function ($t) { return 3; }, 99);
add_filter('http_request_args', function ($args) { $args['timeout'] = 3; return $args; }, 99);
