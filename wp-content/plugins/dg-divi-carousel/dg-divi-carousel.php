<?php
/*
Plugin Name: DiviGear Carousel
Description: Easily add stunning, responsive, and fully customizable content, card, image, testimonial, logo, and team carousels to your Divi website.
Version:     5.1.0
Author:      DiviGear
Author URI:  https://www.divigear.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: dica-divi-carousel
Domain Path: /languages
*/
defined( 'ABSPATH' ) || exit;

if(!defined('DICA_VERSION')) {
    define('DICA_VERSION', '5.1.0');
}
if ( !defined('DICA_MAIN_DIR')) {
    define( "DICA_MAIN_DIR", __DIR__ );
}
if ( ! defined( 'DICA_MAIN_PATH' ) ) {
	define( 'DICA_MAIN_PATH', plugin_dir_path( __FILE__ ) );
}
if(!defined('DICA_ASSETS_DIR')) {
	define('DICA_ASSETS_DIR', trailingslashit(plugin_dir_url(__FILE__)) . 'assets/');
}
if(!defined('DICA_ASSETS_DIR_PATH')) {
	define('DICA_ASSETS_DIR_PATH', plugin_dir_path( __FILE__ ) . 'assets/');
}
if ( ! defined( 'DICA_MODULES_JSON_PATH' ) ) {
	define( 'DICA_MODULES_JSON_PATH', DICA_MAIN_PATH . 'Builder/Server/modules-json/' );
}


if ( ! function_exists( 'dica_initialize_extension' ) ):
/**
 * Creates the extension's main class instance.
 *
 * @since 1.0.0
 */
function dica_initialize_extension() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/DiviCarousel.php';
}
add_action( 'divi_extensions_init', 'dica_initialize_extension' );

endif;

///**
// * Load plugin text domain for translations
// */
function dica_load_textdomain() {
    load_plugin_textdomain( 'dica-divi-carousel', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'dica_load_textdomain' );
//
function dica_scripts() {
	wp_enqueue_style( 'dica-builder-styles', DICA_ASSETS_DIR . 'css/dica-builder.css', [], DICA_VERSION );
    wp_enqueue_style('dica-lightbox-styles', trailingslashit(plugin_dir_url(__FILE__)) .  'styles/light-box-styles.css', array(), DICA_VERSION);
    wp_enqueue_style('swipe-style', trailingslashit(plugin_dir_url(__FILE__)) .  'styles/swiper.min.css', array(), DICA_VERSION);
    wp_enqueue_script('swipe-script', trailingslashit(plugin_dir_url(__FILE__)) . 'scripts/swiper.min.js' , array('jquery'), DICA_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'dica_scripts' );

// include plugin settings
if(file_exists(__DIR__ . '/core/init.php')) {
	add_action('init', function (){
    require_once ( __DIR__ . '/core/init.php');
	});
    /**
     * Deprecated
     * After update process complete
     */

    add_action( '`plugins_loaded`', 'dica_upgrade_function' );

    function dica_upgrade_function( ) {

        $old_option = get_option( 'dgcm__settings' );

        if ($old_option !== false && $old_option['dgcm__text_field_1'] === 'activate' ) {
            if( get_option( 'dg_settings' ) === false ) {
                update_option('dg_settings', array(
                    'dgdc_license_key_setting' => $old_option['dgcm__text_field_0'],
                    'dgdc_license_key_status' => 'active'
                ));
            }
        }

    }
}
//
//
///**
// * Filter through the selector and remove the wrapper if any
// *
// * @param String $selector the css selector
// * @param String $function_name
// */
add_filter('et_pb_set_style_selector', 'dica_remove_css_selector_wrapper', 999, 2);
function dica_remove_css_selector_wrapper($selector, $function_name) {
    if (strpos($selector, 'dica_')) {
        $selector = str_replace( '.et-db ', "", $selector );
        $selector = str_replace( '#et-boc ', "", $selector );
        $selector = str_replace( '.et-l ', "", $selector );
    }
    return $selector;
}

function dgdc_custom_plugin_meta($links, $plugin_file) {
	static $plugin;
	if (!isset($plugin)) {
		// Adjust this path if needed to match your actual plugin slug
		$plugin = plugin_basename(__DIR__) . '/' . plugin_basename(__DIR__) . '.php';
	}

	if ($plugin_file === $plugin) {
		// Ensure textdomain is loaded for plugin meta translations
		if ( ! is_textdomain_loaded( 'dica-divi-carousel' ) ) {
			load_plugin_textdomain( 'dica-divi-carousel', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		$links[] = '<a href="https://www.divigear.com/plugins/carousel-module/" target="_blank">' . __('View Details', 'dica-divi-carousel') . '</a>';
		$links[] = '<a href="https://www.divigear.com/docs/" target="_blank">' . __('Documentation', 'dica-divi-carousel') . '</a>';
		$links[] = '<a href="https://www.trustpilot.com/review/divigear.com" target="_blank">' . __('Leave a Review', 'dica-divi-carousel') . '</a>';
	}
	return $links;
}
add_filter('plugin_row_meta', 'dgdc_custom_plugin_meta', 10, 2);

//****************************************//
//**************   DIVI 5   **************//
//****************************************//
require plugin_dir_path( __FILE__ ) . 'Builder/Main.php';
