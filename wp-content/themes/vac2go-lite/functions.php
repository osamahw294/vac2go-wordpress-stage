<?php
if (!defined('ABSPATH')) exit;
add_action('wp_enqueue_scripts', function () {
  $d=get_stylesheet_directory_uri(); $p=get_stylesheet_directory();
  wp_enqueue_style('hw-bundle',$d.'/bundle.css',array(),filemtime($p.'/bundle.css'));      // render-blocking = no CLS
  wp_enqueue_script('hw-slider',$d.'/slider.js',array(),filemtime($p.'/slider.js'),true);
}, 5);
remove_action('wp_head','print_emoji_detection_script',7);
remove_action('wp_print_styles','print_emoji_styles');
remove_action('wp_head','wp_generator');
add_action('wp_enqueue_scripts',function(){foreach(['wp-block-library','wp-block-library-theme','classic-theme-styles','global-styles'] as $h)wp_dequeue_style($h);},100);
add_action('after_setup_theme',function(){add_theme_support('title-tag');add_theme_support('post-thumbnails');register_nav_menus(array('primary'=>'Primary'));});
function hw_render_home_body(){
  $b=file_get_contents(get_stylesheet_directory().'/body.html');
  $form=do_shortcode('[gravityform id="2" title="false" description="false" ajax="true"]');
  return str_replace('<!--HW_GF_FORM-->',$form,$b);
}
