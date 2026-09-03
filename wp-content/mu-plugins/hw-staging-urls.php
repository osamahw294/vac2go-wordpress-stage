<?php
/* Plugin Name: HW Staging URL rewrite
   Keeps navigation ON the staging host: rewrites hardcoded https://vac2go.com/ links to
   relative (/...) so the tester never gets bounced to production. ONLY acts on the
   *.nxcli.io staging host — it is a complete no-op on the real vac2go.com production host,
   so it is safe to leave in place through go-live. */
if (!defined('ABSPATH')) exit;
add_action('template_redirect', function () {
  $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
  if (stripos($host, 'nxcli.io') === false) return; // staging only; never touch production output
  ob_start(function ($html) {
    if (stripos($html, '</body>') === false) return $html;
    return str_replace(
      array('https://www.vac2go.com/', 'http://www.vac2go.com/', 'https://vac2go.com/', 'http://vac2go.com/'),
      '/',
      $html
    );
  });
}, 0);
