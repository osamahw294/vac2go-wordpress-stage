<?php

namespace DICA\Builder;

class Main {
	public function __construct() {
		if(!defined('DICA_MODULES_JSON_PATH')){
			define( 'DICA_MODULES_JSON_PATH', DICA_MAIN_PATH . 'Builder/Server/modules-json/' );
		}
		require_once __DIR__ . '/Server/PresetAttrsMapGlobal.php';
		add_filter( 'divi_conversion_presets_attrs_map', [ \DICA\Server\PresetAttrsMapGlobal::class, 'get_map' ], 10, 2 );
		$this->load_server();
		add_action( 'divi_visual_builder_assets_before_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_scripts' ] );
//		require_once __DIR__ . '/Server/Localizer.php';
	}

	protected function load_server() {
		require __DIR__ . '/vendor/autoload.php';
		require_once __DIR__ . '/Server/Modules/Main.php';
		require_once __DIR__ . '/Server/Utils/functions.php';
		require_once DICA_MAIN_DIR . '/functions/extends.php';
	}

	public function enqueue_scripts() {

		if ( ! et_builder_d5_enabled() && ! et_core_is_fb_enabled() ) {
			return;
		}

		$plugin_path  = plugin_dir_path( __DIR__ );
		$plugin_url   = plugin_dir_url( __DIR__ );
		$path         = 'Builder/Visual-Builder/build/';
		$assets_path  = 'Builder/Assets/';
		$handle       = 'dica-divi5-new';
		$dependencies = [];

		if ( file_exists( $plugin_path . $path . 'bundle.asset.php' ) ) {
			$dependencies = include_once $plugin_path . $path . 'bundle.asset.php';
		}

//		wp_enqueue_style(
//			$handle . '-lib',
//			$plugin_url . $assets_path . 'styles/df_lib_styles.css',
//			[ 'wp-components' ],
//			DICA_VERSION );

		wp_enqueue_style(
			$handle,
			$plugin_url . $path . 'styles/bundle.css',
			[ 'wp-components' ],
			DICA_VERSION );


		wp_enqueue_script(
			$handle,
			$plugin_url . $path . 'bundle.js',
			[
				'divi-module-library',
				'divi-vendor-wp-hooks',
			],
			1, true );
		// Pass server-built preset attrs map for all dica modules (single source of truth; no duplicate JS logic).
		$preset_attrs_maps = $this->get_dica_preset_attrs_maps_for_script();
		wp_localize_script( $handle, 'dicaPresetAttrsMaps', $preset_attrs_maps );
	}
		/**
	 * Get preset attrs map for every registered dica module (for VB "New Preset From Current Styles").
	 * Uses Divi's Conversion::get_preset_attrs_mapping() so PHP is the single source of truth.
	 *
	 * @return array<string, array> Map of module name => preset attrs map.
	 */
	protected function get_dica_preset_attrs_maps_for_script() {
		$out = [];
		if ( ! class_exists( '\ET\Builder\Packages\Conversion\Conversion' ) ) {
			return $out;
		}
		$registry = \WP_Block_Type_Registry::get_instance();
		$blocks   = $registry->get_all_registered();
		if ( empty( $blocks ) ) {
			return $out;
		}

		foreach ( $blocks as $name => $block ) {
			if ( strpos( $name, 'dica/' ) !== 0 ) {
				continue;
			}
			try {
				$map = \ET\Builder\Packages\Conversion\Conversion::get_preset_attrs_mapping( $name );
				if ( ! empty( $map ) ) {
					$out[ $name ] = $map;
				}
			} catch ( \Throwable $e ) {
				// Skip module if map cannot be built (e.g. missing block attrs).
				continue;
			}
		}
		return $out;
	}
	public function enqueue_frontend_scripts() {
		$plugin_path = plugin_dir_path( __DIR__ );
		$plugin_url  = plugin_dir_url( __DIR__ );
		$path        = 'Builder/Visual-Builder/build/';
		$handle      = 'dica-divi5-new';
		$assets_path = 'Builder/assets/';

//		wp_enqueue_style(
//			$handle . '-lib',
//			$plugin_url . $assets_path . 'styles/df_lib_styles.css',
//			[ 'wp-components' ],
//			DICA_VERSION );

		wp_enqueue_style(
			$handle,
			$plugin_url . $path . 'styles/bundle.css',
			[ 'wp-components' ],
			DICA_VERSION );

		// Enqueue Swiper CSS and JS for carousel functionality
		wp_enqueue_style( 'swiper-style', trailingslashit(plugin_dir_url(__DIR__)) . 'styles/swiper.min.css', array(), DICA_VERSION );
		wp_enqueue_script( 'swiper-script', trailingslashit(plugin_dir_url(__DIR__)) . 'scripts/swiper.min.js', array('jquery'), DICA_VERSION, true );

		// Enqueue the frontend JavaScript that contains carousel initialization
		wp_register_script( 'dica-frontend-script', trailingslashit(plugin_dir_url(__DIR__)) . 'scripts/frontend.js', array('jquery', 'swiper-script'), DICA_VERSION, true );
	}
}

new Main();
