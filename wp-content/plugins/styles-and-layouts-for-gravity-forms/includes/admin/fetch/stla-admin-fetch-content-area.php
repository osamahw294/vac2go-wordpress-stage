<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Responsible for all the ajax calls from admin builder.
 */
class Stla_Admin_Fetch_Content_Area {

	/**
	 * Instance of class.
	 *
	 * @var Stla_Admin_Fetch_Content_Area
	 */
	private static $instance;

	/**
	 * Execute all the actions and filters.
	 */
	public function __construct() {
		add_action( 'wp_ajax_stla_gravity_form_html', array( $this, 'stla_gravity_form_html' ) );
		add_action( 'wp_ajax_stla_gravity_form_confirmation_html', array( $this, 'stla_gravity_form_confirmation_html' ) );
		add_action( 'wp_ajax_stla_get_page_count', array( $this, 'stla_get_page_count' ) );
		add_action( 'wp_ajax_stla_styler_settings', array( $this, 'stla_styler_settings' ) );
		add_action( 'wp_ajax_stla_styler_fields_settings', array( $this, 'stla_styler_fields_settings' ) );

		add_action( 'wp_ajax_stla_get_forms_with_styling', array( $this, 'stla_get_forms_with_styling' ) );
		add_action( 'wp_ajax_stla_delete_forms_styles', array( $this, 'stla_delete_forms_styles' ) );
		add_action( 'wp_ajax_stla_save_styler_settings', array( $this, 'stla_save_styler_settings' ) );
		add_action( 'wp_ajax_stla_save_booster_settings', array( $this, 'stla_save_booster_settings' ) );
		add_action( 'wp_ajax_stla_general_settings', array( $this, 'stla_general_settings' ) );
		add_action( 'wp_ajax_stla_booster_settings', array( $this, 'stla_booster_settings' ) );
		add_action( 'wp_ajax_stla_form_fields_labels', array( $this, 'stla_form_fields_labels' ) );
		add_action( 'wp_ajax_stla_get_all_form_names', array( $this, 'stla_get_all_form_names' ) );
	}

	/**
	 * Main Plugin Instance
	 *
	 * Insures that only one instance of a plugin class exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 5.0
	 * @return Stla_Admin_Fetch_Content_Area Highlander Instance
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Stla_Admin_Fetch_Content_Area ) ) {
			self::$instance = new Stla_Admin_Fetch_Content_Area();
		}

		return self::$instance;
	}

	/**
	 * Returns the html for gravity form.
	 *
	 * @return void
	 */
	public function stla_gravity_form_html() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'stla_gravity_booster_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$form_id = isset( $_POST['formId'] ) ? sanitize_text_field( wp_unslash( $_POST['formId'] ) ) : 0;

		gravity_form( $form_id );

		// Make sure to exit after outputting the HTML.
		wp_die();
	}

	/**
	 * Show confirmation message.
	 *
	 * @return void
	 */
	public function stla_gravity_form_confirmation_html() {

		require_once GFCommon::get_base_path() . '/form_display.php';

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'stla_gravity_booster_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$form_id = isset( $_POST['formId'] ) ? sanitize_text_field( wp_unslash( $_POST['formId'] ) ) : 0;
		$form    = GFAPI::get_form( $form_id );

		$confirmation = GFFormDisplay::get_confirmation_message( reset( $form['confirmations'] ), $form, array(), array() );

		echo wp_kses_post( $confirmation );

		// Make sure to exit after outputting the HTML.
		wp_die();
	}

	/**
	 * No of pages.
	 *
	 * @return void
	 */
	public function stla_get_page_count() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'stla_gravity_booster_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$form_id   = isset( $_POST['formId'] ) ? sanitize_text_field( wp_unslash( $_POST['formId'] ) ) : 0;
		$form_data = GFAPI::get_form( $form_id );

		$page_count = count( GFAPI::get_fields_by_type( $form_data, array( 'page' ) ) );

		wp_send_json_success( $page_count );
	}



	/**
	 * Return all the forms to show in header.
	 *
	 * @return void
	 */
	public function stla_get_all_form_names() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'stla_gravity_booster_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$all_forms_data = GFAPI::get_forms();

		$form_data_with_required_fields = array();

		foreach ( $all_forms_data as $form_data ) {
			$form_data_with_required_fields[] = array(
				'id'    => $form_data['id'],
				'title' => $form_data['title'],
			);
		}

		wp_send_json_success( $form_data_with_required_fields );
	}

	/**
	 * Returns the styler settings
	 *
	 * @return void
	 */
	public function stla_styler_settings() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'stla_gravity_booster_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$form_id = isset( $_POST['formId'] ) ? sanitize_text_field( wp_unslash( $_POST['formId'] ) ) : '';

		$settings = get_option( 'gf_stla_form_id_' . $form_id );
		$settings = empty( $settings ) ? array() : $settings;

		wp_send_json_success( $settings );
	}

	/**
	 * Return the field settings of active field in styler
	 *
	 * @return void
	 */
	public function stla_styler_fields_settings() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'stla_gravity_booster_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$form_id = isset( $_POST['formId'] ) ? sanitize_text_field( wp_unslash( $_POST['formId'] ) ) : '';

		$settings = get_option( 'gf_stla_field_id_' . $form_id );
		$settings = empty( $settings ) ? array() : $settings;

		wp_send_json_success( $settings );
	}

	/**
	 * Returns all the forms which have styles applied.
	 *
	 * @return void
	 */
	public function stla_get_forms_with_styling() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'stla_gravity_booster_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$styled_forms = $this->get_forms_with_styles();

		wp_send_json_success( $styled_forms );
	}

	/**
	 * Get the forms on which the styles are applied.
	 *
	 * @return array
	 */
	public function get_forms_with_styles() {

		$styled_forms = array();
		// Get all gravity forms created by user.
		if ( class_exists( 'RGFormsModel' ) ) {
			$forms = RGFormsModel::get_forms( null, 'title' );

			$styled_forms[] = array(
				'label' => '---Select form --',
				'value' => '-1',
			);

			foreach ( $forms as $form ) {

				$style_current_form = get_option( 'gf_stla_form_id_' . $form->id );

				// check if form has field specific styles.
				if ( empty( $style_current_form ) ) {
					$style_current_form = get_option( 'gf_stla_field_id_' . $form->id );
				}

				if ( ! empty( $style_current_form ) ) {

					$styled_forms[] = array(
						'label' => $form->title,
						'value' => $form->id,
					);

				}
			}
		} else {
			$styled_forms[] = array(
				'label' => 'Gravity Forms not installed',
				'value' => '-1',
			);
		}
		return $styled_forms;
	}

	/**
	 * Deletes Forms styles and return the updated form list.
	 *
	 * @return void
	 */
	public function stla_delete_forms_styles() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'stla_gravity_booster_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$form_id = isset( $_POST['formId'] ) ? sanitize_text_field( wp_unslash( $_POST['formId'] ) ) : 0;

		delete_option( 'gf_stla_form_id_' . $form_id );
		delete_option( 'gf_stla_field_id_' . $form_id );

		$styled_forms = $this->get_forms_with_styles();

		wp_send_json_success( $styled_forms );
	}

	/**
	 * Recursively sanitizes decoded JSON settings, stripping tags/scripts from every scalar leaf.
	 *
	 * The 'custom-css' key is a special case: it holds legitimate raw CSS (selectors, combinators,
	 * @media queries, etc.) that sanitize_text_field() would corrupt, so it is passed to
	 * stla_sanitize_custom_css() instead, which neutralises the one sequence able to break out of a
	 * <style> element while leaving CSS syntax intact. A non-string 'custom-css' value is not a real
	 * CSS field, so it falls through to normal recursion rather than being stored as-is.
	 *
	 * @param mixed $data The decoded settings data (array or scalar).
	 * @return mixed The sanitized data.
	 */
	public static function sanitize_settings_recursive( $data ) {

		if ( is_array( $data ) ) {
			$sanitized = array();
			foreach ( $data as $key => $value ) {
				if ( 'custom-css' === $key && is_string( $value ) ) {
					$sanitized[ $key ] = stla_sanitize_custom_css( $value );
				} else {
					$sanitized[ $key ] = self::sanitize_settings_recursive( $value );
				}
			}
			return $sanitized;
		}

		if ( is_string( $data ) ) {
			return sanitize_text_field( $data );
		}

		return $data;
	}

	/**
	 * Save all the styler settings on save button.
	 *
	 * @return void
	 */
	public function stla_save_styler_settings() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'stla_gravity_booster_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$form_id         = isset( $_POST['formId'] ) ? sanitize_text_field( wp_unslash( $_POST['formId'] ) ) : 0;
		/*
		 * These three values are JSON documents, so they are deliberately not passed through
		 * sanitize_text_field() here. Doing so is what CVE-2026-12477 exploited: the encoded
		 * form of a payload ("<") contains no HTML for the sanitizer to strip, and
		 * json_decode() then turned it back into a live "<". Each decoded leaf is sanitized
		 * instead, immediately below, by sanitize_settings_recursive().
		 */
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Raw JSON, sanitized per leaf after json_decode() on the next lines.
		$styler_settings = isset( $_POST['stylerSettings'] ) ? wp_unslash( $_POST['stylerSettings'] ) : '';
		$styler_settings = json_decode( $styler_settings, true );
		$styler_settings = self::sanitize_settings_recursive( $styler_settings );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Raw JSON, sanitized per leaf after json_decode() on the next lines.
		$general_settings = isset( $_POST['generalSettings'] ) ? wp_unslash( $_POST['generalSettings'] ) : '';
		$general_settings = json_decode( $general_settings, true );
		$general_settings = self::sanitize_settings_recursive( $general_settings );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Raw JSON, sanitized per leaf after json_decode() on the next lines.
		$styler_fields_settings = isset( $_POST['stylerFieldsSettings'] ) ? wp_unslash( $_POST['stylerFieldsSettings'] ) : '';
		$styler_fields_settings = json_decode( $styler_fields_settings, true );
		$styler_fields_settings = self::sanitize_settings_recursive( $styler_fields_settings );

		update_option( 'gf_stla_form_id_' . $form_id, $styler_settings );
		update_option( 'gf_stla_general_settings' . $form_id, $general_settings );
		update_option( 'gf_stla_field_id_' . $form_id, $styler_fields_settings );

		// Get styler settings.
		$styler_settings = get_option( 'gf_stla_form_id_' . $form_id );
		$styler_settings = empty( $styler_settings ) ? array() : $styler_settings;

		// Get styler field specific settings.
		$styler_field_settings = get_option( 'gf_stla_field_id_' . $form_id );
		$styler_field_settings = empty( $styler_field_settings ) ? array() : $styler_field_settings;

		// Get general settings.
		$general_settings = get_option( 'gf_stla_general_settings' . $form_id );
		$general_settings = empty( $general_settings ) ? array() : $general_settings;

		$saved_settings = array(
			'stylerSettings'      => $styler_settings,
			'stylerFieldSettings' => $styler_field_settings,
			'generalSettings'     => $general_settings,
		);

		wp_send_json_success( $saved_settings );
	}

	/**
	 * Save all the settings on save button.
	 *
	 * @return void
	 */
	public function stla_save_booster_settings() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'stla_gravity_booster_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		// Validate booster settings.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Raw JSON, sanitized per leaf after json_decode() below.
		$booster_settings = isset( $_POST['boosterSettings'] ) ? wp_unslash( $_POST['boosterSettings'] ) : '';
		if ( empty( $booster_settings ) ) {
			wp_send_json_error( 'Booster Settings are empty' );
		}
		// Save booster settings.
		$booster_settings = json_decode( $booster_settings, true );
		$booster_settings = self::sanitize_settings_recursive( $booster_settings );
		update_option( 'gf_stla_booster_settings', $booster_settings );
		// Get booster settings.
		$settings = $this->stla_get_booster_settings();
		wp_send_json_success( $settings );
	}


	/**
	 * Sidebar general settings.
	 */
	public function stla_general_settings() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'stla_gravity_booster_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$form_id = isset( $_POST['formId'] ) ? sanitize_text_field( wp_unslash( $_POST['formId'] ) ) : '';

		$settings = get_option( 'gf_stla_general_settings' . $form_id );
		$settings = empty( $settings ) ? array() : $settings;

		wp_send_json_success( $settings );
	}

	/**
	 * Sidebar booster settings.
	 */
	public function stla_booster_settings() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'stla_gravity_booster_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}
		// Get the booster settings.
		$settings = $this->stla_get_booster_settings();

		wp_send_json_success( $settings );
	}

	/**
	 * Retrieves the Booster settings for the Gravity Forms plugin.
	 *
	 * This function fetches the license status for various Booster addons and the
	 * overall Booster settings, and returns them as an array.
	 *
	 * @return array The Booster settings, including license status and other settings.
	 */
	public function stla_get_booster_settings() {

		$license_status_keys = array( 'custom_themes_addon_license_status', 'ai_addon_license_status', 'field_icons_addon_license_status', 'gravity_forms_tooltips_addon_license_status', 'bootstrap_design_addon_license_status', 'gravity_forms_checkbox_radio_license_status', 'material_design_addon_license_status' );

		$license_status = array();
		foreach ( $license_status_keys as $license_status_key ) {
			$license_status[ $license_status_key ] = get_option( $license_status_key );
		}

		$stla_licenses = get_option( 'stla_licenses' );
		$stla_licenses = empty( $stla_licenses ) ? array() : $stla_licenses;
		$licenses      = array(
			'licenses' => array(
				'keys'   => $stla_licenses,
				'status' => $license_status,
			),

		);

		$booster_settings = get_option( 'gf_stla_booster_settings' );
		$booster_settings = empty( $booster_settings ) ? array() : $booster_settings;
		$settings         = array_merge( $licenses, $booster_settings );

		return $settings;
	}

	/**
	 * Fetch form fields Labels settings.
	 */
	public function stla_form_fields_labels() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'stla_gravity_booster_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$form_id = isset( $_POST['formId'] ) ? sanitize_text_field( wp_unslash( $_POST['formId'] ) ) : '';

		if ( empty( $form_id ) ) {
			wp_send_json_error( 'Form id not selected' );
		}
		require_once GFCommon::get_base_path() . '/form_display.php';

		$form = GFAPI::get_form( $form_id );

		$field_labels   = array();
		$complex_fields = array( 'name', 'address', 'email' );

		$form_fields = $form['fields'];

		foreach ( $form_fields as $field ) {

			$supported_styler_settings = array();
			$field_content             = GFFormDisplay::get_field_content( $field, '', true, $form_id, $form );

			if ( str_contains( $field_content, 'gfield_label' ) ) {
				array_push( $supported_styler_settings, 'field-labels' );
			}

			if ( str_contains( $field_content, 'gfield_description' ) ) {
				array_push( $supported_styler_settings, 'field-descriptions' );
			}

			if ( str_contains( $field_content, 'gfield_list' ) || str_contains( $field_content, 'gfield_list_group' ) ) {
				array_push( $supported_styler_settings, 'list-field-table', 'list-field-heading', 'list-field-cell', 'list-field-cell-container' );
			}

			if ( str_contains( $field_content, 'ginput_container_radio' ) ) {
				array_push( $supported_styler_settings, 'radio-inputs' );
			}

			if ( str_contains( $field_content, 'ginput_container_checkbox' ) ) {
				array_push( $supported_styler_settings, 'checkbox-inputs' );
			}

			if ( str_contains( $field_content, 'gform-field-label--type-sub' ) ) {
				array_push( $supported_styler_settings, 'field-sub-labels' );
			}

			if ( str_contains( $field_content, 'gfield_select' ) ) {
				array_push( $supported_styler_settings, 'dropdown-fields' );
			}

			if ( str_contains( $field_content, 'gsection' ) ) {
				array_push( $supported_styler_settings, 'section-break-title', 'section-break-description' );
			}

			if ( str_contains( $field_content, 'ginput_container_textarea' ) ) {
				array_push( $supported_styler_settings, 'paragraph-textarea' );
			}

			if ( str_contains( $field_content, "type='text'" ) || str_contains( $field_content, "type='email'" ) || str_contains( $field_content, "type='password'" ) || str_contains( $field_content, "type='tel'" ) || str_contains( $field_content, "type='url'" ) || str_contains( $field_content, "type='number'" ) ) {
				array_push( $supported_styler_settings, 'text-fields' );
			}

			$field_labels[] = array(
				'id'                      => $field->id,
				'label'                   => $field->label,
				'type'                    => $field->type,
				'supportedStylerControls' => $supported_styler_settings,
			);

			if ( in_array( $field->type, $complex_fields ) ) {
				$state_field_id   = false;
				$country_field_id = false;
				$name_prefix_id   = false;

				if ( 'address' === $field->type ) {
					$country_field_id = floatval( $field['id'] . '.6' );
					$address_type     = $field['addressType'];
					if ( 'international' !== $address_type ) {
						$state_field_id = floatval( $field['id'] . '.4' );
					}
				}

				if ( 'name' === $field->type ) {
					$name_prefix_id = floatval( $field['id'] . '.2' );

				}

				if ( ! empty( $field['inputs'] ) ) {
					foreach ( $field['inputs'] as  $sub_field ) {

						$is_hidden    = ! empty( $sub_field['isHidden'] ) ? $sub_field['isHidden'] : false;
						$sub_field_id = floatval( $sub_field['id'] );

						// state field id is only set if its not international ( then it will be dropdown)
						// country field id will always be dropdown.. skip loop in both cases
						// name prefix is always dropdown, so ignored.
						if ( $state_field_id === $sub_field_id || $country_field_id === $sub_field_id || $name_prefix_id === $sub_field_id ) {
							continue;
						}

						if ( ! $is_hidden && ! isset( $child_input['choices'] ) ) {
							$field_labels[] = array(
								'id'    => $sub_field_id,
								'label' => $sub_field['label'],
								'type'  => $field->type,
							);

						}
					}
				}
			}
		}

		wp_send_json_success( $field_labels );
	}
}




/**
 * The main function responsible for returning The Highlander Plugin
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @since 3.0
 * @return {class} Highlander Instance
 */
function stla_initalize_admin_fetch() {
	return Stla_Admin_Fetch_Content_Area::instance();
}

stla_initalize_admin_fetch();
