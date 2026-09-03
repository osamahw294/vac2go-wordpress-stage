<?php
/* Plugin Name: Highwater Magic Login  Description: One-time admin login links minted by the Highwater maintenance app. */
add_action('init', function(){
  if (empty($_GET['hw_login'])) return;
  // Ignore browser prefetch / prerender so the ONE-TIME token isn't spent early.
  $purpose = '';
  foreach (array('HTTP_SEC_PURPOSE','HTTP_PURPOSE','HTTP_X_PURPOSE','HTTP_X_MOZ') as $h) { if (!empty($_SERVER[$h])) { $purpose = $_SERVER[$h]; break; } }
  if ($purpose && (stripos($purpose,'prefetch')!==false || stripos($purpose,'prerender')!==false || stripos($purpose,'preview')!==false)) return;
  $t = preg_replace('/[^a-f0-9]/','', (string) $_GET['hw_login']);
  if (strlen($t) < 20) return;
  $key = 'hw_login_' . $t;
  // Read straight from the DB (bypass any persistent object cache, which can hide
  // an option the command line just wrote). Reusable within its short window so a
  // second tab / redirect / auto-open doesn't "burn" the link.
  global $wpdb;
  $exp = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $key));
  if ($exp === null || (int)$exp < time()) { if ($exp !== null) { delete_option($key); } wp_die('This login link is invalid or has expired. Please generate a new one from the maintenance app.'); }
  $ids = get_users(array('role'=>'administrator','number'=>1,'fields'=>'ID','orderby'=>'ID'));
  if (empty($ids)) { $ids = get_users(array('number'=>1,'fields'=>'ID','orderby'=>'ID')); }
  $u = !empty($ids) ? get_user_by('id', $ids[0]) : null;
  if (!$u || !user_can($u->ID, 'manage_options')) { wp_die('No administrator account was found to log in as.'); }
  wp_set_current_user($u->ID);
  wp_set_auth_cookie($u->ID, false);
  wp_safe_redirect(admin_url());
  exit;
});
