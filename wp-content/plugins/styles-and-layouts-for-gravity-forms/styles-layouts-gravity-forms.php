<?php
/**
 * Plugin Name: Gravity Booster - Styles & Layouts for Gravity Forms
 * Plugin URI:  http://wpmonks.com/styles-layouts-gravity-forms
 * Description: Create beautiful styles for your gravity forms
 * Version:     6.0
 * Author:      Sushil Kumar
 * Author URI:  http://wpmonks.com/
 * Requires PHP: 7.4
 * Text Domain: styles-and-layouts-for-gravity-forms
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

define( 'GF_STLA_DIR', WP_PLUGIN_DIR . '/' . basename( __DIR__ ) );
define( 'GF_STLA_URL', plugins_url() . '/' . basename( __DIR__ ) );
define( 'GF_STLA_STORE_URL', 'https://wpmonks.com' );
define( 'GF_STLA_VERSION', '6.0' );

if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	include_once GF_STLA_DIR . '/admin-menu/EDD_SL_Plugin_Updater.php';
}

require_once 'helpers/utils/responsive.php';
require_once 'helpers/utils/class-gf-stla-review.php';

// Antispam helpers.
require_once GF_STLA_DIR . '/includes/antispam/helpers/stla-antispam-common-helpers.php';

require_once GF_STLA_DIR . '/admin-menu/class-stla-license-page.php';
require_once GF_STLA_DIR . '/admin-menu/class-stla-addons-page.php';
require_once GF_STLA_DIR . '/admin-menu/class-gf-stla-welcome-page.php';
require_once GF_STLA_DIR . '/includes/admin/fetch/stla-admin-fetch-content-area.php';
require_once GF_STLA_DIR . '/includes/admin/fetch/stla-admin-fetch-anispam.php';

// Antispam keyword files.
require_once GF_STLA_DIR . '/includes/antispam/keywords/stla-antispam-keyword-mark-spam.php';
require_once GF_STLA_DIR . '/includes/antispam/keywords/stla-antispam-keyword-precheck.php';

// Antispam Email Files.
require_once GF_STLA_DIR . '/includes/antispam/emails/class-stla-antispam-email-mark-spam.php';
require_once GF_STLA_DIR . '/includes/antispam/emails/class-stla-antispam-email-restrict-submission.php';

// Antispam restrict users.
require_once GF_STLA_DIR . '/includes/antispam/userRestrictions/stla-antispam-user-restrictions.php';




class Gravity_customizer_admin {

	/**
	 * The page trigger.
	 *
	 * @var string
	 */
	private $trigger;
	/**
	 * The form id.
	 *
	 * @var int
	 */
	private $stla_form_id;

	/**
	 * The styles added on froms.
	 *
	 * @var array
	 */
	private $form_styles_processed = array();

	/**
	 * Execute all the actions and filters.
	 */
	public function __construct() {
		global $wp_version;
		add_action( 'customize_register', array( $this, 'customize_register' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_controls_enqueue_scripts' ) );
		add_action( 'customize_preview_init', array( $this, 'customize_preview_init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		register_activation_hook( __FILE__, array( $this, 'gf_stla_welcome_screen_activate' ) );
		add_action( 'admin_init', array( $this, 'gf_stla_welcome_screen_do_activation_redirect' ) );
		add_action( 'customize_save_after', array( $this, 'customize_save_after' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_filter( 'gform_toolbar_menu', array( $this, 'gform_toolbar_menu' ), 10, 2 );
		add_action( 'gform_enqueue_scripts', array( $this, 'gform_enqueue_scripts' ), 10 );
		if ( class_exists( 'GFForms' ) ) {
			add_filter( 'template_include', array( $this, 'gf_stla_preview_template' ) );
			$this->trigger = 'stla-gravity-forms-customizer';
			// only load controls for this plugin.
			if ( isset( $_GET[ $this->trigger ] ) ) {
				if ( ! empty( $_GET['stla_form_id'] ) ) {
					$this->stla_form_id = sanitize_text_field( wp_unslash( $_GET['stla_form_id'] ) );
				}
				add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
			}
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'gform_addon_navigation', array( $this, 'add_menu_item' ) );
	}


	public function get_gravity_theme_dependencies( $form_id ) {

		$styles = array();
		if ( ! method_exists( 'GFCommon', 'output_default_css' ) || GFCommon::output_default_css() === false ) {
			return $styles;
		}

		if ( ! class_exists( 'GFAPI' ) ) {
			return $styles;
		}

		$form = GFAPI::get_form( $form_id );

		$slug = '';
		if ( isset( $form['theme'] ) ) {
			$slug = $form['theme'];
		}
		if ( empty( $slug ) || ! in_array( $slug, array( 'legacy', 'gravity-theme', 'orbital' ) ) ) {
			if ( method_exists( 'GFForms', 'get_default_theme' ) ) {

				$slug = GFForms::get_default_theme();
			}
		}

		$themes = array( $slug );

		if ( in_array( 'orbital', $themes ) ) {
			$styles[] = 'gravity_forms_orbital_theme';
			$styles[] = 'gravity_forms_theme_foundation';
			$styles[] = 'gravity_forms_theme_framework';
			$styles[] = 'gravity_forms_theme_reset';
		}

		if ( in_array( 'gravity-theme', $themes ) ) {
				$styles[] = 'gform_basic';
		}

		return $styles;
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {

		if ( is_admin() || defined( 'REST_REQUEST' ) || class_exists( 'GFAPI' ) ) {

			if ( ( ! isset( $_GET['page'] ) || 'stla_gravity_booster' !== $_GET['page'] ) ) {
				return;
			}
		}

		$gravity_theme_dependencies = array();
		if ( isset( $_GET['formId'] ) ) {
			$gravity_theme_dependencies = $this->get_gravity_theme_dependencies( sanitize_text_field( wp_unslash( $_GET['formId'] ) ) );
		}

		$gravity_theme_dependencies[] = 'wp-components';

		$asset_file = include GF_STLA_DIR . '/build/index.asset.php';

		wp_enqueue_style( 'stla-admin-styles', GF_STLA_URL . '/build/index.css', $gravity_theme_dependencies, GF_STLA_VERSION );

		// get the installed addons info.
		$addons_info = $this->get_booster_admin_js_addons_info();

		wp_enqueue_media();
		wp_enqueue_script( 'stla-admin-gravity-booster-js', GF_STLA_URL . '/build/index.js', $asset_file['dependencies'], $asset_file['version'], true );
		wp_enqueue_script( 'stla-admin-gravity-booster', GF_STLA_URL . '/build/index.js', $addons_info['dependencies'], $asset_file['version'], true );

		// Generate a nonce.
		$nonce = wp_create_nonce( 'stla_gravity_booster_nonce' );

		$form_id = 0;
		if ( ! empty( $_GET['formId'] ) ) {
			$form_id = sanitize_text_field( wp_unslash( $_GET['formId'] ) );
		}
		$panel_id = 'styler';
		if ( ! empty( $_GET['panelId'] ) ) {
			$panel_id = sanitize_text_field( wp_unslash( $_GET['panelId'] ) );
		}

		$section_id = '';
		if ( ! empty( $_GET['sectionId'] ) ) {
			$section_id = sanitize_text_field( wp_unslash( $_GET['sectionId'] ) );
		}

		$customizer_url = $this->_set_customizer_url( $form_id );
		$merge_tags     = array();
		$form           = GFAPI::get_form( $form_id );
		if ( $form ) {
			$merge_tags = GFCommon::get_merge_tags( $form['fields'], '', false );
		}

		// Pass the nonce to your React script using wp_localize_script().
		wp_localize_script(
			'stla-admin-gravity-booster-js',
			'stlaAdminGravityBooster',
			array(
				'nonce'         => $nonce,
				'formId'        => $form_id,
				'panelId'       => $panel_id,
				'sectionId'     => $section_id,
				'status'        => $addons_info['status'],
				'version'       => $addons_info['version'],
				'isRtl'         => is_rtl(),
				'customizerUrl' => $customizer_url,
				'adminUrl'      => get_admin_url(),
				'mergeTags'     => $merge_tags,
			)
		);
	}

	/**
	 * Get version and instalattion status of all the addons.
	 *
	 * @return array
	 */
	public function get_booster_admin_js_addons_info() {

		$asset_file        = include GF_STLA_DIR . '/build/index.asset.php';
		$js_dependencies   = $asset_file['dependencies'];
		$addon_dependecies = array();
		$status            = array();
		$version           = array();
		$addon_slugs       = array(
			'checkboxRadio' => 'styles-layouts-gf-checkbox-radio/styles-layouts-gf-checkbox-radio.php',
			'bootstrap'     => 'styles-layouts-bootstrap-design/styles-layouts-bootstrap-design.php',
			'material'      => 'styles-layouts-material-design/styles-layouts-material-design.php',
			'tooltips'      => 'styles-layouts-gf-tooltips/styles-layouts-gf-tooltips.php',
			'fieldIcons'    => 'styles-layouts-gf-field-icons/styles-layouts-gf-field-icons.php',
			'customThemes'  => 'styles-layouts-gf-custom-themes/styles-layouts-gf-custom-themes.php',
			'ai'            => 'styles-layouts-gf-ai/styles-layouts-gf-ai.php',
			'antispam'      => 'styles-layouts-gf-antispam/styles-layouts-gf-antispam.php',
		);

		foreach ( $addon_slugs as $name => $slug ) {
			if ( is_plugin_active( $slug ) ) {
				switch ( $name ) {
					case 'checkboxRadio':
						$status['checkboxRadio']  = 'active';
						$version['checkboxRadio'] = defined( 'STLA_CHECKBOX_RADIO_VERSION' ) ? STLA_CHECKBOX_RADIO_VERSION : '1.0';
						if ( (int) $version['checkboxRadio'] >= 2 ) {
							$addon_dependecies[] = 'stla-admin-checkbox-radio';
						}
						break;
					case 'bootstrap':
						$status['bootstrap']  = 'active';
						$version['bootstrap'] = defined( 'STLA_BOOTSTRAP_VERSION' ) ? STLA_BOOTSTRAP_VERSION : '1.0';
						if ( (int) $version['bootstrap'] >= 2 ) {
							$addon_dependecies[] = 'stla-admin-bootstrap';
						}
						break;
					case 'material':
						$status['material']  = 'active';
						$version['material'] = defined( 'STLA_MATERIAL_VERSION' ) ? STLA_MATERIAL_VERSION : '1.0';
						if ( (int) $version['material'] >= 6 ) {
							$addon_dependecies[] = 'stla-admin-material';
						}
						break;
					case 'tooltips':
						$status['tooltips']  = 'active';
						$version['tooltips'] = defined( 'GF_STLA_TOOLTIPS_VERSION' ) ? GF_STLA_TOOLTIPS_VERSION : '1.0';
						if ( (int) $version['tooltips'] >= 4 ) {
							$addon_dependecies[] = 'stla-admin-tooltips';
						}
						break;
					case 'fieldIcons':
						$status['fieldIcons']  = 'active';
						$version['fieldIcons'] = defined( 'GF_STLA_FIELD_ICONS_VERSION' ) ? GF_STLA_FIELD_ICONS_VERSION : '1.0';
						if ( (int) $version['fieldIcons'] >= 3 ) {
							$addon_dependecies[] = 'stla-admin-field-icons';
						}
						break;
					case 'customThemes':
						$status['customThemes']  = 'active';
						$version['customThemes'] = defined( 'GF_STLA_MY_THEME_VERSION' ) ? GF_STLA_MY_THEME_VERSION : '1.0';

						if ( (int) $version['customThemes'] >= 3 ) {
							$addon_dependecies[] = 'stla-admin-custom-themes';
						}
						break;
					case 'ai':
						$addon_dependecies[] = 'stla-admin-ai';
						$status['ai']        = 'active';
						$version['ai']       = defined( 'GF_STLA_AI_VERSION' ) ? GF_STLA_AI_VERSION : '1.0';
						break;
					case 'antispam':
						$addon_dependecies[] = 'stla-admin-antispam';
						$status['antispam']  = 'active';
						$version['antispam'] = defined( 'GF_STLA_ANTISPAM_VERSION' ) ? GF_STLA_ANTISPAM_VERSION : '1.0';
						break;
				}
			} else {
				$installed_plugins = get_plugins();
				if ( array_key_exists( $slug, $installed_plugins ) || in_array( $slug, $installed_plugins, true ) ) {
					$status[ $name ] = 'inActive';

				} else {

					$status[ $name ] = 'notInstalled';

				}
			}
		}

		if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
			$addon_dependecies[] = 'gform_gravityforms';

		}
		$dependencies = array_merge( $addon_dependecies, $js_dependencies );

		return array(
			'dependencies' => $dependencies,
			'status'       => $status,
			'version'      => $version,
		);
	}

	/**
	 * Add "Booster" menu item under Forms in dashboard.
	 *
	 * @param array $menu_items GF menu items.
	 * @return array
	 */
	public function add_menu_item( $menu_items ) {

		$menu_items[] = array(
			'name'       => 'stla_gravity_booster',
			'label'      => 'Booster',
			'callback'   => array( $this, 'stla_gravity_booster_submenu_callback' ),
			'permission' => 'edit_posts',
		);

		return $menu_items;
	}


	/**
	 * The content of Booster page at backend.
	 *
	 * @return void
	 */
	public function stla_gravity_booster_submenu_callback() {
		echo '<style>
		body.wp-admin {
			overflow: hidden;
		}
		body.wp-admin #adminmenumain{
			display: none;
		}
		body.wp-admin #wpcontent #wpadminbar{
			display: none;
		}
		#wpbody-content{
			overflow: hidden;
		}
		</style>';

		if ( ! empty( $_GET['formId'] ) ) {
			GFForms::enqueue_form_scripts( sanitize_text_field( wp_unslash( $_GET['formId'] ) ) );
		}

		echo '<div id="stla-gravity-booster"></div>';
	}

	/**
	 * Enqueue styles and scripts for customizer specifically.
	 *
	 * @return void
	 */
	public function wp_enqueue_scripts() {
		if ( is_customize_preview() ) {
			wp_enqueue_script( 'stla_frontend_wp', GF_STLA_URL . '/js/frontend.js', array( 'jquery', 'customize-preview' ), GF_STLA_VERSION, true );
		}
	}
	/**
	 * Runs when plugin is updated.
	 *
	 * @param array $upgrader_object WP upgrade instance.
	 * @param array $options update data.
	 * @return void
	 */
	public function stla_upgrade_completed( $upgrader_object, $options ) {
		// The path to our plugin's main file.
		$our_plugin = plugin_basename( __FILE__ );
		// If an update has taken place and the updated type is plugins and the plugins element exists.
		if ( 'update' === $options['action'] && 'plugin' === $options['type'] && isset( $options['plugins'] ) ) {
			// Iterate through the plugins being updated and check if ours is there.
			foreach ( $options['plugins'] as $plugin ) {
				if ( $plugin === $our_plugin ) {
					// Set a transient to record that our plugin has just been updated.
					if ( class_exists( 'RGFormsModel' ) ) {
						$forms       = RGFormsModel::get_forms( null, 'title ' );
						$field_names = array( 'padding', 'margin' );

						$field_types = array( 'form-wrapper', 'checkbox-inputs', 'confirmation-message', 'dropdown-fields', 'error-message', 'field-descriptions', 'field-labels', 'field-sub-labels', 'form-description', 'form-header', 'form-title', 'form-wrapper', 'list-field', 'paragraph-textarea', 'placeholders', 'radio-inputs', 'section-break-description', 'section-break-title', 'submit-button', 'text-fields' );

						foreach ( $forms as $form ) {
							$form_id      = $form->id;
							$stla_options = get_option( 'gf_stla_form_id_' . $form_id );
							if ( ! empty( $stla_options ) ) {
								// For setting margin and padding according to new layout.
								foreach ( $field_types as $field_type ) {
									foreach ( $field_names as $field_name ) {
										if ( isset( $stla_options[ $field_type ][ $field_name ] ) ) {
											$value  = trim( $stla_options[ $field_type ][ $field_name ] );
											$values = preg_split( '/[\s]+/', $value );
											$count  = count( $values );
											switch ( $count ) {
												case 4:
													$stla_options[ $field_type ][ $field_name . '-top' ]    = $values[0];
													$stla_options[ $field_type ][ $field_name . '-right' ]  = $values[1];
													$stla_options[ $field_type ][ $field_name . '-bottom' ] = $values[2];
													$stla_options[ $field_type ][ $field_name . '-left' ]   = $values[3];
													unset( $stla_options[ $field_type ][ $field_name ] );
													update_option( 'gf_stla_form_id_' . $form_id, $stla_options );
													break;

												case 3:
													$stla_options[ $field_type ][ $field_name . '-top' ]    = $values[0];
													$stla_options[ $field_type ][ $field_name . '-right' ]  = $values[1];
													$stla_options[ $field_type ][ $field_name . '-bottom' ] = $values[2];
													$stla_options[ $field_type ][ $field_name . '-left' ]   = $values[1];
													unset( $stla_options[ $field_type ][ $field_name ] );
													update_option( 'gf_stla_form_id_' . $form_id, $stla_options );
													break;

												case 2:
													$stla_options[ $field_type ][ $field_name . '-top' ]    = $values[0];
													$stla_options[ $field_type ][ $field_name . '-right' ]  = $values[1];
													$stla_options[ $field_type ][ $field_name . '-bottom' ] = $values[0];
													$stla_options[ $field_type ][ $field_name . '-left' ]   = $values[1];
													unset( $stla_options[ $field_type ][ $field_name ] );
													update_option( 'gf_stla_form_id_' . $form_id, $stla_options );
													break;

												case 1:
													$stla_options[ $field_type ][ $field_name . '-top' ]    = $values[0];
													$stla_options[ $field_type ][ $field_name . '-right' ]  = $values[0];
													$stla_options[ $field_type ][ $field_name . '-bottom' ] = $values[0];
													$stla_options[ $field_type ][ $field_name . '-left' ]   = $values[0];
													unset( $stla_options[ $field_type ][ $field_name ] );
													update_option( 'gf_stla_form_id_' . $form_id, $stla_options );
													break;
											}
										}
									}
								}
								// For removing placeholder padding.
								if ( isset( $stla_options['placeholders']['padding-top'] ) || isset( $stla_options['placeholders']['padding-bottom'] ) || isset( $stla_options['placeholders']['padding-left'] ) || isset( $stla_options['placeholders']['padding-right'] ) ) {
									unset( $stla_options['placeholders']['padding-top'] );
									unset( $stla_options['placeholders']['padding-bottom'] );
									unset( $stla_options['placeholders']['padding-left'] );
									unset( $stla_options['placeholders']['padding-right'] );
									update_option( 'gf_stla_form_id_' . $form_id, $stla_options );
								}

								// Change the gradient field value from hsl to hex.
								if ( isset( $stla_options['form-wrapper']['gradient-color-1'] ) ) {
									$gradient1 = $stla_options['form-wrapper']['gradient-color-1'];
									$is_hex    = preg_match( '/^#[0-9A-F]{6}$/i', $gradient1 );

									if ( 0 === $is_hex ) { // Not hex.
										$stla_options['form-wrapper']['gradient-color-1'] = $this->hsl_to_rgba( $gradient1, .5, .4 );
										update_option( 'gf_stla_form_id_' . $form_id, $stla_options );
									}
								}

								// Change the gradient field value from hsl to hex.
								if ( isset( $stla_options['form-wrapper']['gradient-color-1'] ) ) {
									$gradient2 = $stla_options['form-wrapper']['gradient-color-2'];
									$is_hex    = preg_match( '/^#[0-9A-F]{6}$/i', $gradient2 );

									if ( 0 === $is_hex ) { // Not hex.
										$stla_options['form-wrapper']['gradient-color-2'] = $this->hsl_to_rgba( $gradient2, .5, .4 );
										update_option( 'gf_stla_form_id_' . $form_id, $stla_options );
									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Enqueue scripts for Gravity forms.
	 *
	 * @param array $form The GF form array.
	 * @return void
	 */
	public function gform_enqueue_scripts( $form ) {

		if ( is_customize_preview() ) {
			wp_enqueue_style( 'stla_live_preview', GF_STLA_URL . '/css/live-preview.css', '', GF_STLA_VERSION );
		}

		$style_current_form = get_option( 'gf_stla_form_id_' . $form['id'] );

		// is_admin doesn't work in gutenberg. used REST_REQUEST for this.
		$css_form_id = $form['id'];

		if ( ! is_admin() && ! defined( 'REST_REQUEST' ) && ! $this->is_material_active( $css_form_id ) ) {

			// including styling file only once for each form.
			array_push( $this->form_styles_processed, $css_form_id );
			$main_class_object = $this;
			include 'display/class-styles.php';
			include 'display/class-fields-styles.php';
			// }
		}

		do_action( 'gf_stla_after_post_style_display', $this );
	}

	/**
	 * Check wether the material is active.
	 *
	 * @param int $form_id Form on which need to check the material.
	 * @return boolean
	 */
	public function is_material_active( $form_id ) {
		$is_active = false;

		$material_option  = get_option( 'gf_stla_form_id_material_design_' . $form_id );
		$material_enabled = isset( $material_option['enabled'] ) ? $material_option['enabled'] : false;

		$is_material_active = is_plugin_active( 'styles-layouts-material-design/styles-layouts-material-design.php' ) ? true : false;

		if ( $is_material_active && $material_enabled ) {
			$is_active = true;
		}
		return $is_active;
	}

	/**
	 *  Enqueue js file that autosaves the form selection in database.
	 *
	 * @since  v1.0
	 * @author Sushil Kumar
	 * @return void
	 */
	public function customize_controls_enqueue_scripts() {
		wp_enqueue_style( 'stla-customizer-css', GF_STLA_URL . '/css/customizer/stla-customizer-controls.css', '', GF_STLA_VERSION );
		wp_enqueue_style( 'stla-customizer-control-css', GF_STLA_URL . '/css/customizer-controls.css', '', GF_STLA_VERSION );
		wp_enqueue_script( 'gf_stla_auto_save_form', GF_STLA_URL . '/js/customizer-controls/auto-save-form.js', array( 'jquery' ), GF_STLA_VERSION, true );
		wp_enqueue_script( 'gf_stla_customize_controls', GF_STLA_URL . '/js/customizer-controls/customizer-controls.js', array( 'jquery' ), GF_STLA_VERSION, true );
	}

	/**
	 *  Shows live preview of css changes.
	 *
	 * @since  v1.0
	 * @author Sushil Kumar
	 * @return void
	 */
	public function customize_preview_init() {
		$current_form_id = get_option( 'gf_stla_select_form_id' );

		if ( ! $this->is_material_active( $current_form_id ) ) {

			wp_enqueue_script( 'gf_stla_show_live_changes', GF_STLA_URL . '/js/live-preview/live-preview-changes.js', array( 'jquery', 'customize-preview' ), GF_STLA_VERSION, true );
			wp_enqueue_script( 'gf_stla_customizer_edit_shortcuts', GF_STLA_URL . '/js/live-preview/edit-shortcuts.js', array( 'jquery', 'customize-preview' ), GF_STLA_VERSION, true );
		}
		wp_localize_script( 'gf_stla_show_live_changes', 'gf_stla_localize_current_form', array( 'formId' => $current_form_id ) );
		wp_localize_script( 'gf_stla_customizer_edit_shortcuts', 'gf_stla_localize_edit_shortcuts', array( 'formId' => $current_form_id ) );
	}


	/**
	 * Function that adds panels, sections, settings and controls.
	 *
	 * @param [type] $wp_customize WP customizer object.
	 * @return void
	 */
	public function customize_register( $wp_customize ) {
		if ( isset( $this->stla_form_id ) ) {
			update_option( 'gf_stla_select_form_id', $this->stla_form_id );
		}
		include 'helpers/fonts.php';
		$current_form_id = get_option( 'gf_stla_select_form_id' );
		$border_types    = array(
			'solid'  => 'Solid',
			'dotted' => 'Dotted',
			'dashed' => 'Dashed',
			'double' => 'Double',
			'groove' => 'Groove',
			'ridge'  => 'Ridge',
			'inset'  => 'Inset',
			'outset' => 'Outset',
		);
		$align_pos       = array(
			'left'    => 'Left',
			'center'  => 'Center',
			'right'   => 'Right',
			'justify' => 'Justify',
		);

		$font_style_choices = array(
			'bold'      => 'Bold',
			'italic'    => 'Italic',
			'uppercase' => 'Uppercase',
			'underline' => 'underline',
		);

		$wp_customize->add_panel(
			'gf_stla_panel',
			array(
				'title'       => __( 'Styles & Layouts Gravity Forms' , 'styles-and-layouts-for-gravity-forms'),
				'description' => '<p> Craft your Forms</p>', // Include html tags such as <p>.
				'priority'    => 160, // Mixed with top-level-section hierarchy.
			)
		);
		include 'includes/form-select.php';

		if ( ! isset( $_GET['autofocus'] ) || ( is_array( $_GET['autofocus'] ) && array_key_exists( 'panel', $_GET['autofocus'] ) && 'gf_stla_panel' !== $_GET['autofocus']['panel'] ) ) {
			$wp_customize->add_setting(
				'gf_stla_hidden_field_for_form_id',
				array(
					'default'   => $current_form_id,
					'transport' => 'postMessage',
					'type'      => 'option',
				)
			);

			$wp_customize->add_control(
				'gf_stla_hidden_field_for_form_id',
				array(
					'type'        => 'hidden',
					'priority'    => 10, // Within the section.
					'section'     => 'gf_stla_select_form_section', // Required, core or custom.
					'input_attrs' => array(
						'value' => $current_form_id,
						'id'    => 'gf_stla_hidden_field_for_form_id',
					),
				)
			);
		}
		include_once GF_STLA_DIR . '/helpers/customizer-controls/margin-padding.php';
		include_once GF_STLA_DIR . '/helpers/customizer-controls/class-stla-desktop-text-input-option.php';
		include_once GF_STLA_DIR . '/helpers/customizer-controls/tab-text-input.php';
		include_once GF_STLA_DIR . '/helpers/customizer-controls/mobile-text-input.php';
		include_once GF_STLA_DIR . '/helpers/customizer-controls/class-stla-text-alignment-option.php';
		include_once GF_STLA_DIR . '/helpers/customizer-controls/class-stla-font-style-option.php';
		include_once GF_STLA_DIR . '/helpers/customizer-controls/class-stla-customize-control-range-slider.php';
		include_once GF_STLA_DIR . '/helpers/customizer-controls/custom-controls.php';
		include_once GF_STLA_DIR . '/includes/customizer-addons.php';
		include_once GF_STLA_DIR . '/includes/general-settings.php';
		do_action( 'gf_stla_add_theme_section', $wp_customize, $current_form_id );
		include_once GF_STLA_DIR . '/includes/form-wrapper.php';
		include_once GF_STLA_DIR . '/includes/form-header.php';
		include_once GF_STLA_DIR . '/includes/form-title.php';
		include_once GF_STLA_DIR . '/includes/form-description.php';
		include_once GF_STLA_DIR . '/includes/field-labels.php';
		include_once GF_STLA_DIR . '/includes/field-sub-labels.php';
		include_once GF_STLA_DIR . '/includes/placeholders.php';
		include_once GF_STLA_DIR . '/includes/field-descriptions.php';
		include_once GF_STLA_DIR . '/includes/text-fields.php';
		include_once GF_STLA_DIR . '/includes/dropdown-fields.php';
		include_once GF_STLA_DIR . '/includes/radio-inputs.php';
		include_once GF_STLA_DIR . '/includes/checkbox-inputs.php';
		include_once GF_STLA_DIR . '/includes/paragraph-textarea.php';
		include_once GF_STLA_DIR . '/includes/section-break-title.php';
		include_once GF_STLA_DIR . '/includes/section-break-description.php';
		include_once GF_STLA_DIR . '/includes/list-field.php';
		include_once GF_STLA_DIR . '/includes/submit-button.php';
		include_once GF_STLA_DIR . '/includes/confirmation-message.php';
		include_once GF_STLA_DIR . '/includes/error-message.php';
	} // Main customizer function ends here.

	/**
	 * Check if the value exist in CSS properties.
	 *
	 * @param array  $setting The saved style settings.
	 * @param string $property The key to check.
	 * @return boolean
	 */
	public function is_css_not_set( $setting, $property ) {

		if ( isset( $setting[ $property ] ) && '' !== $setting[ $property ] ) {
			return false;
		}

		return true;
	}


	/**
	 * Retrieves the saved styles for a specific form, category, and optionally a field ID.
	 *
	 * @param int    $form_id   The ID of the Gravity Forms form.
	 * @param string $category  The category of the styles to retrieve.
	 * @param string $important Whether to add the '!important' flag to the styles.
	 * @param string $field_id  The ID of the field to retrieve styles for (optional).
	 *
	 * @return string The CSS styles for the specified form, category, and field.
	 */
	public function gf_sb_get_saved_styles( $form_id, $category, $important = '', $field_id = '' ) {

		if ( is_customize_preview() ) {
			$important = '';
		}

		$settings = get_option( 'gf_stla_form_id_' . $form_id );

		// get the styler settings specific to fields.
		if ( ! empty( $field_id ) ) {
			$settings = get_option( 'gf_stla_field_id_' . $form_id );

			// get the settings of field id whoes css has to be printed.
			$settings = $settings[ $field_id ];
		}

		$input_styles = '';

		if ( isset( $settings[ $category ]['font-style'] ) && '' === $field_id ) {
			$input_styles .= 'font-weight: normal' . $important . '; ';
		}

		if ( ! empty( $settings[ $category ]['font-style'] ) ) {
			$font_styles = explode( '|', $settings[ $category ]['font-style'] );

			foreach ( $font_styles as $value ) {
				switch ( $value ) {
					case 'bold':
						$input_styles .= 'font-weight: bold' . $important . '; ';
						break;
					case 'italic':
						$input_styles .= 'font-style: italic' . $important . '; ';
						break;
					case 'uppercase':
						$input_styles .= 'text-transform: uppercase' . $important . '; ';
						break;
					case 'underline':
						$input_styles .= 'text-decoration: underline' . $important . '; ';
						break;
					default:
						break;
				}
			}
		}
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'color' ) ? '' : 'color:' . $settings[ $category ]['color'] . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'background-color' ) ? '' : 'background-color:' . $settings[ $category ]['background-color'] . $important . ';';

		// Gradient for themes.
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'background-color1' ) ? '' : 'background:-webkit-linear-gradient(to left,' . $settings[ $category ]['background-color'] . ',' . $settings[ $category ]['background-color1'] . ') ' . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'background-color1' ) ? '' : 'background:linear-gradient(to left,' . $settings[ $category ]['background-color'] . ',' . $settings[ $category ]['background-color1'] . ') ' . $important . ';';

		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'width' ) ? '' : 'width:' . $settings[ $category ]['width'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['width'] ) . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'height' ) ? '' : 'height:' . $settings[ $category ]['height'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['height'] ) . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'title-position' ) ? '' : 'text-align:' . $settings[ $category ]['title-position'] . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'text-align' ) ? '' : 'text-align:' . $settings[ $category ]['text-align'] . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'line-height' ) ? '' : 'line-height:' . $settings[ $category ]['line-height'] . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'error-position' ) ? '' : 'text-align:' . $settings[ $category ]['error-position'] . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'description-position' ) ? '' : 'text-align:' . $settings[ $category ]['description-position'] . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'title-color' ) ? '' : 'color:' . $settings[ $category ]['title-color'] . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'font-color' ) ? '' : 'color:' . $settings[ $category ]['font-color'] . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'description-color' ) ? '' : 'color:' . $settings[ $category ]['description-color'] . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'button-color' ) ? '' : 'background-color:' . $settings[ $category ]['button-color'] . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'description-color' ) ? '' : 'color:' . $settings[ $category ]['description-color'] . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'font-family' ) ? '' : 'font-family:' . $settings[ $category ]['font-family'] . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'font-size' ) ? '' : 'font-size:' . $settings[ $category ]['font-size'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['font-size'] ) . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'max-width' ) ? '' : 'width:' . $settings[ $category ]['max-width'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['max-width'] ) . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'maximum-width' ) ? '' : 'width:' . $settings[ $category ]['maximum-width'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['maximum-width'] ) . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'margin' ) ? '' : 'margin:' . $this->gf_stla_add_px_to_padding_margin( $settings[ $category ]['margin'] ) . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'padding' ) ? '' : 'padding:' . $this->gf_stla_add_px_to_padding_margin( $settings[ $category ]['padding'] ) . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'border-size' ) ? '' : 'border-width:' . $settings[ $category ]['border-size'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['border-size'] ) . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'border-color' ) ? '' : 'border-color:' . $settings[ $category ]['border-color'] . $important . ';';

		if ( ! empty( $settings[ $category ]['border-size'] ) ) {
			$input_styles .= $this->is_css_not_set( $settings[ $category ], 'border-type' ) ? 'border-style:solid;' : 'border-style:' . $settings[ $category ]['border-type'] . $important . ';';
		}

		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'border-bottom' ) ? '' : 'border-bottom-style:' . $settings[ $category ]['border-bottom'] . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'border-bottom-size' ) ? '' : 'border-bottom-width:' . $settings[ $category ]['border-bottom-size'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['border-bottom-size'] ) . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'border-bottom-color' ) ? '' : 'border-bottom-color:' . $settings[ $category ]['border-bottom-color'] . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'background-image-url' ) ? '' : 'background: url(' . $settings[ $category ]['background-image-url'] . ') no-repeat ' . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'border-bottom-color' ) ? '' : 'border-bottom-color:' . $settings[ $category ]['border-bottom-color'] . ';';

		if ( isset( $settings[ $category ]['display'] ) ) {
			$input_styles .= $settings[ $category ]['display'] ? 'display:none ' . $important . ';' : '';
		}

		if ( isset( $settings[ $category ]['visibility'] ) ) {
			$input_styles .= $settings[ $category ]['visibility'] ? 'visibility: hidden ' . $important . ';' : '';
		}

		if ( isset( $settings[ $category ]['border-radius'] ) ) {
			$input_styles .= $this->is_css_not_set( $settings[ $category ], 'border-radius' ) ? '' : 'border-radius:' . $settings[ $category ]['border-radius'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['border-radius'] ) . $important . ';';

			$input_styles .= $this->is_css_not_set( $settings[ $category ], 'border-radius' ) ? '' : '-web-border-radius:' . $settings[ $category ]['border-radius'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['border-radius'] ) . $important . ';';
			$input_styles .= $this->is_css_not_set( $settings[ $category ], 'border-radius' ) ? '' : '-moz-border-radius:' . $settings[ $category ]['border-radius'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['border-radius'] ) . $important . ';';
		}

		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'custom-css' ) ? '' : stla_sanitize_custom_css( $settings[ $category ]['custom-css'] ) . ';';

		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'padding-left' ) ? '' : 'padding-left:' . $settings[ $category ]['padding-left'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['padding-left'] ) . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'padding-right' ) ? '' : 'padding-right:' . $settings[ $category ]['padding-right'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['padding-right'] ) . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'padding-top' ) ? '' : 'padding-top:' . $settings[ $category ]['padding-top'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['padding-top'] ) . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'padding-bottom' ) ? '' : 'padding-bottom:' . $settings[ $category ]['padding-bottom'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['padding-bottom'] ) . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'margin-left' ) ? '' : 'margin-left:' . $settings[ $category ]['margin-left'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['margin-left'] ) . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'margin-right' ) ? '' : 'margin-right:' . $settings[ $category ]['margin-right'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['margin-right'] ) . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'margin-top' ) ? '' : 'margin-top:' . $settings[ $category ]['margin-top'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['margin-top'] ) . $important . ';';
		$input_styles .= $this->is_css_not_set( $settings[ $category ], 'margin-bottom' ) ? '' : 'margin-bottom:' . $settings[ $category ]['margin-bottom'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['margin-bottom'] ) . $important . ';';

		return $input_styles;
	}

	/**
	 * Retrieves the saved styles for a Gravity Forms field on a tablet device.
	 *
	 * @param int    $form_id   The ID of the Gravity Forms form.
	 * @param string $category  The category of the field (e.g. 'input', 'label', etc.).
	 * @param string $important Whether to add the '!important' flag to the CSS.
	 * @param string $field_id  The ID of the field.
	 *
	 * @return string The CSS styles for the field on a tablet device.
	 */
	public function gf_sb_get_saved_styles_tab( $form_id, $category, $important = '', $field_id = '' ) {

		$settings = get_option( 'gf_stla_form_id_' . $form_id );

		// get the styler settings specific to fields.
		if ( ! empty( $field_id ) ) {
			$settings = get_option( 'gf_stla_field_id_' . $form_id );

			// get the settings of field id whoes css has to be printed.
			$settings = $settings[ $field_id ];
		}

		$input_styles  = '';
		$input_styles .= ! isset( $settings[ $category ]['width-tab'] ) ? '' : 'width:' . $settings[ $category ]['width-tab'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['width-tab'] ) . $important . ';';
		$input_styles .= ! isset( $settings[ $category ]['max-width-tab'] ) ? '' : 'width:' . $settings[ $category ]['max-width-tab'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['max-width-tab'] ) . $important . ';';
		$input_styles .= ! isset( $settings[ $category ]['maximum-width-tab'] ) ? '' : 'width:' . $settings[ $category ]['maximum-width-tab'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['maximum-width-tab'] ) . $important . ';';
		$input_styles .= ! isset( $settings[ $category ]['height-tab'] ) ? '' : 'height:' . $settings[ $category ]['height-tab'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['height-tab'] ) . $important . ';';
		$input_styles .= ! isset( $settings[ $category ]['font-size-tab'] ) ? '' : 'font-size:' . $settings[ $category ]['font-size-tab'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['font-size-tab'] ) . $important . ';';

		$input_styles .= ! isset( $settings[ $category ]['line-height-tab'] ) ? '' : 'line-height:' . $settings[ $category ]['line-height-tab'] . $important . ';';
		return $input_styles;
	}

	/**
	 * Retrieves the saved styles for a Gravity Forms field on a phone device.
	 *
	 * @param int    $form_id   The ID of the Gravity Forms form.
	 * @param string $category  The category of the field (e.g. 'input', 'label', etc.).
	 * @param string $important Whether to add the '!important' flag to the CSS.
	 * @param string $field_id  The ID of the field.
	 *
	 * @return string The CSS styles for the field on a phone device.
	 */
	public function gf_sb_get_saved_styles_phone( $form_id, $category, $important = '', $field_id = '' ) {

		$settings = get_option( 'gf_stla_form_id_' . $form_id );
		// get the styler settings specific to fields.
		if ( ! empty( $field_id ) ) {
			$settings = get_option( 'gf_stla_field_id_' . $form_id );

			// get the settings of field id whoes css has to be printed.
			$settings = $settings[ $field_id ];
		}

		$input_styles  = '';
		$input_styles .= ! isset( $settings[ $category ]['width-phone'] ) ? '' : 'width:' . $settings[ $category ]['width-phone'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['width-phone'] ) . $important . ';';
		$input_styles .= ! isset( $settings[ $category ]['max-width-phone'] ) ? '' : 'width:' . $settings[ $category ]['max-width-phone'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['max-width-phone'] ) . $important . ';';
		$input_styles .= ! isset( $settings[ $category ]['maximum-width-phone'] ) ? '' : 'width:' . $settings[ $category ]['maximum-width-phone'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['maximum-width-phone'] ) . $important . ';';
		$input_styles .= ! isset( $settings[ $category ]['height-phone'] ) ? '' : 'height:' . $settings[ $category ]['height-phone'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['height-phone'] ) . $important . ';';
		$input_styles .= ! isset( $settings[ $category ]['font-size-phone'] ) ? '' : 'font-size:' . $settings[ $category ]['font-size-phone'] . $this->gf_stla_add_px_to_value( $settings[ $category ]['font-size-phone'] ) . $important . ';';
		$input_styles .= ! isset( $settings[ $category ]['line-height-phone'] ) ? '' : 'line-height:' . $settings[ $category ]['line-height-phone'] . $important . ';';

		return $input_styles;
	}

	/**
	 * Function to add px if not available (not for padding and margin).
	 *
	 * @param string $value Wether to add px or custom value.
	 * @return string
	 */
	public function gf_stla_add_px_to_value( $value ) {
		$int_parsed = (int) $value;
		if ( ctype_digit( $value ) ) {
			$value = 'px';
		} else {
			$value = '';
		}
		return $value;
	}


	/**
	 * Function to add px if not available for padding and margin.
	 *
	 * @param string $value The unit value to add.
	 * @deprecated 4.0.0 No longer used.
	 * @return string
	 */
	public function gf_stla_add_px_to_padding_margin( $value ) {
		$margin_padding     = explode( ' ', $value );
		$new_margin_padding = '';
		foreach ( $margin_padding as $att_value ) {
			if ( ctype_digit( $att_value ) ) {
				$new_margin_padding .= $att_value . 'px ';
			} else {
				$new_margin_padding .= $att_value . ' ';
			}
		}
		return $new_margin_padding;
	}

	/**
	 * Convert HSL colors into RGBA (used to convert gradient colors), Opacity is fetched from database.
	 *
	 * @param int     $h Hue value.
	 * @param int     $s Saturation value.
	 * @param int     $l Light Value.
	 * @param boolean $to_hex Wether to convert in RGBA or HEX.
	 * @return string
	 */
	public function hsl_to_rgba( $h, $s, $l, $to_hex = true ) {
		$h /= 360;
		$r  = $l;
		$g  = $l;
		$b  = $l;
		$v  = ( $l <= 0.5 ) ? ( $l * ( 1.0 + $s ) ) : ( $l + $s - $l * $s );
		if ( $v > 0 ) {
			$m;
			$sv;
			$sextant;
			$fract;
			$vsf;
			$mid1;
			$mid2;

			$m       = $l + $l - $v;
			$sv      = ( $v - $m ) / $v;
			$h      *= 6.0;
			$sextant = floor( $h );
			$fract   = $h - $sextant;
			$vsf     = $v * $sv * $fract;
			$mid1    = $m + $vsf;
			$mid2    = $v - $vsf;

			switch ( $sextant ) {
				case 0:
					$r = $v;
					$g = $mid1;
					$b = $m;
					break;
				case 1:
					$r = $mid2;
					$g = $v;
					$b = $m;
					break;
				case 2:
					$r = $m;
					$g = $v;
					$b = $mid1;
					break;
				case 3:
					$r = $m;
					$g = $mid2;
					$b = $v;
					break;
				case 4:
					$r = $mid1;
					$g = $m;
					$b = $v;
					break;
				case 5:
					$r = $v;
					$g = $m;
					$b = $mid2;
					break;
			}
		}
		$r = round( $r * 255, 0 );
		$g = round( $g * 255, 0 );
		$b = round( $b * 255, 0 );

		if ( $to_hex ) {
			$r = ( $r < 15 ) ? '0' . dechex( $r ) : dechex( $r );
			$g = ( $g < 15 ) ? '0' . dechex( $g ) : dechex( $g );
			$b = ( $b < 15 ) ? '0' . dechex( $b ) : dechex( $b );
			return "#$r$g$b";
		}
	}

	/**
	 * Convert Hex to rgba
	 *
	 * @param string $hex_code the hex code.
	 * @param string $background_opacity The opacity to add.
	 * @return string
	 */
	public function hex_rgba( $hex_code, $background_opacity ) {

		if ( empty( $hex_code ) ) {
			return '';
		}

		$r               = '';
		$g               = '';
		$b               = '';
		list($r, $g, $b) = sscanf( $hex_code, '#%02x%02x%02x' );
		return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $background_opacity . ')';
	}

	/**
	 * Set Gradient properties for all browsers.
	 *
	 * @param string $gradient_color1 First Gradient Color.
	 * @param string $gradient_color2 Second Gradient Color.
	 * @param string $direction Gradient Direction.
	 * @return string
	 */
	public function set_gradient_properties( $gradient_color1, $gradient_color2, $direction ) {
		switch ( $direction ) {
			case 'left':
				$gradient_direction          = 'right,';
				$gradient_direction_safari   = 'left,';
				$gradient_direction_standard = 'to right,';
				break;
			case 'diagonal':
				$gradient_direction          = 'bottom right,';
				$gradient_direction_safari   = 'left top,';
				$gradient_direction_standard = 'to bottom right,';
				break;
			default:
				$gradient_direction          = '';
				$gradient_direction_safari   = '';
				$gradient_direction_standard = '';
		}
		// $gradient_css = 'background: linear-gradient(' . "$gradient_direction_standard" . "$gradient_color1" . ',' . $gradient_color2 . ');';
		$gradient_css  = 'background: linear-gradient(' . $gradient_direction_standard . ' ' . $gradient_color1 . ', ' . $gradient_color2 . ');';
		$gradient_css .= 'background: -o-linear-gradient( ' . $gradient_direction . ' ' . $gradient_color1 . ', ' . $gradient_color2 . ' );';
		$gradient_css .= 'background: -moz-linear-gradient( ' . $gradient_direction . ' ' . $gradient_color1 . ', ' . $gradient_color2 . ' );';
		$gradient_css .= 'background: -webkit-linear-gradient( ' . $gradient_direction_safari . ' ' . $gradient_color1 . ', ' . $gradient_color2 . ' );';
		return $gradient_css;
	}

	/**
	 * After activating the plugin set transit to redirect.
	 *
	 * @return void
	 */
	public function gf_stla_welcome_screen_activate() {
		set_transient( 'gf_stla_welcome_activation_redirect', true, 30 );
	}

	/**
	 * Redirect after the plugin is activated based on transit.
	 *
	 * @return void
	 */
	public function gf_stla_welcome_screen_do_activation_redirect() {
		// Bail if no activation redirect.
		if ( ! get_transient( 'gf_stla_welcome_activation_redirect' ) ) {
			return;
		}
		// Delete the redirect transient.
		delete_transient( 'gf_stla_welcome_activation_redirect' );
		// Bail if activating from network, or bulk.
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}
		// Redirect to welcome about page.
		wp_safe_redirect( add_query_arg( array( 'page' => 'stla-documentation' ), admin_url( 'admin.php' ) ) );
	}

	/**
	 * Check wether to reset the styles after customizer saved.
	 *
	 * @return void
	 */
	public function customize_save_after() {

		// Get name of style to be deleted.
		$style_to_be_deleted = get_option( 'gf_stla_general_settings' );
		if ( -1 !== $style_to_be_deleted['reset-styles'] || ! empty( $style_to_be_deleted['reset-styles'] ) ) {
			delete_option( 'gf_stla_form_id_' . $style_to_be_deleted['reset-styles'] );
			$style_to_be_deleted['reset-styles'] = -1;
			update_option( 'gf_stla_general_settings', $style_to_be_deleted );
		}
	}

	/**
	 * Check if the form is opened in frontend.
	 *
	 * @param object $form The GF form object.
	 * @return object
	 */
	public function gf_stla_show_css_frontend( $form ) {
		$this->is_this_frontend = true;
		return $form;
	}

	/**
	 * The admin notice when GF not installed.
	 *
	 * @return void
	 */
	public function admin_notices() {
		if ( ! class_exists( 'GFForms' ) ) {
			$class   = 'notice notice-error';
			$message = sprintf(
				/* translators: 1: opening anchor tag linking to gravityforms.com, 2: closing anchor tag. */
				__( '%1$sGravity Forms%2$s is not installed. Gravity Booster cannot work without Gravity Forms.', 'styles-and-layouts-for-gravity-forms' ),
				'<a href="https://www.gravityforms.com/">',
				'</a>'
			);
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses_post( $message ) );
		}
	}

	/**
	 * Adds Styles & Layouts to Toolbar in  Gravity form edit screen
	 *
	 * @param array $menu_items The GF editor menu items.
	 * @param int   $form_id The form id.
	 * @return array
	 */
	public function gform_toolbar_menu( $menu_items, $form_id ) {
		$menu_items['styles-layouts-gravity-forms'] = array(
			'icon'         => '<i class="fa fa-paint-brush fa-lg"></i>',
			'label'        => 'Styles & Layouts', // the text to display on the menu for this link.
			'title'        => 'Styles & Layouts', // the text to be displayed in the title attribute for this link.
			'url'          => $this->get_booster_url( $form_id ), // the URL this link should point to.
			'menu_class'   => 'sk-style', // Optional, class to apply to menu list item (useful for providing a custom icon).
			'link_class'   => 'my_custom_page' === rgget( 'page' ) ? 'gf_toolbar_active' : '*', // Class to apply to link (useful for specifying an active style when this link is the current page).
			'capabilities' => array( 'gravityforms_edit_forms' ), // The capabilities the user should possess in order to access this page.
			'priority'     => 500, // Optional, use this to specify the order in which this menu item should appear; if no priority is provided, the menu item will be append to end.
		);
		return $menu_items;
	}


	/**
	 * Add custom variables to the available query vars.
	 *
	 * @since 1.0.0
	 * @param array $vars Add query.
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = $this->trigger;
		return $vars;
	}


	/**
	 * If the right query var is present load the Gravity Forms preview template
	 *
	 * @param string $template the file url.
	 * @return string
	 */
	public function gf_stla_preview_template( $template ) {

		// Load this conditionally based on the query var.
		if ( get_query_var( $this->trigger ) ) {
			$template = GF_STLA_DIR . '/helpers/utils/html-template-preview.php';
		}
		return $template;
	}

	/**
	 * Set the booster url.
	 *
	 * @param int $form_id The form id.
	 * @return string
	 */
	private function get_booster_url( $form_id ) {
		$url = admin_url( 'admin.php' );
		$url = add_query_arg( 'page', 'stla_gravity_booster', $url );
		$url = add_query_arg( 'formId', $form_id, $url );

		return $url;
	}

	/**
	 * Set the customizer url.
	 *
	 * @param int $form_id the form id.
	 * @return string
	 */
	private function _set_customizer_url( $form_id ) {
		$url            = admin_url( 'customize.php' );
		$url            = add_query_arg( 'stla-gravity-forms-customizer', 'true', $url );
		$url            = add_query_arg( 'stla_form_id', $form_id, $url );
		$url            = add_query_arg( 'autofocus[panel]', 'gf_stla_panel', $url );
		$url            = add_query_arg(
			'url',
			wp_nonce_url(
				urlencode(
					add_query_arg(
						array(
							'stla_form_id'     => $form_id,
							'stla-gravity-forms-customizer' => 'true',
							'autofocus[panel]' => 'gf_stla_panel',
						),
						site_url()
					)
				),
				'preview-popup'
			),
			$url
		);
		$url            = add_query_arg(
			'return',
			urlencode(
				add_query_arg(
					array(
						'page' => 'gf_edit_forms',
						'id'   => $form_id,
					),
					admin_url( 'admin.php' )
				)
			),
			$url
		);
		$customizer_url = esc_url_raw( $url );
		return $customizer_url;
	}
}

register_activation_hook( __FILE__, 'stla_set_migrate_transient' );

/**
 * Set update transit.
 *
 * @return void
 */
function stla_set_migrate_transient() {
	set_transient( 'stla_updated', 1 );
}

add_action( 'plugins_loaded', 'stla_gravity_customizer_admin' );

function stla_gravity_customizer_admin() {
	new Gravity_customizer_admin();
	new Gf_Stla_Review();
}
