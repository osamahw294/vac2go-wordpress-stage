<?php
/*
 * Plugin Name: HW WebP HTML Rewrite (server-agnostic)
 * Description: Rewrites uploads .jpg/.jpeg/.png URLs to their .webp sibling in the
 *   delivered HTML (src, srcset, inline background-image url(), AND preload hrefs),
 *   when the browser advertises image/webp and the .webp file exists. Never double-
 *   appends (negative lookahead on .webp). Nginx ignores .htaccess for static files,
 *   so HTML rewrite is the reliable delivery path on Nexcess.
 */
if (!defined('ABSPATH')) exit;

add_action('template_redirect', function () {
    if (is_admin() || is_feed() || (defined('DOING_AJAX') && DOING_AJAX)) return;
    if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'image/webp') === false) return;
    ob_start('hw_webp_rewrite');
}, 8);

function hw_webp_rewrite($html) {
    if (!$html) return $html;
    $u = wp_get_upload_dir();
    $baseurl = $u['baseurl'];
    $basedir = $u['basedir'];
    // Match an uploads image URL ending in .jpg/.jpeg/.png that is NOT already followed by .webp.
    $re = '#(' . preg_quote($baseurl, '#') . '/[^"\'\s)]+?\.(?:jpe?g|png))(?!\.webp)#i';
    return preg_replace_callback($re, function ($m) use ($baseurl, $basedir) {
        $url  = $m[1];
        $file = $basedir . substr($url, strlen($baseurl));
        return is_file($file . '.webp') ? $url . '.webp' : $url;
    }, $html);
}
