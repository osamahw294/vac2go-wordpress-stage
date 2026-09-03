<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages the singleton instance for handling anti-spam functionality in the Styles & Layouts for Gravity Forms admin.
 *
 * This class implements the singleton pattern to ensure only one instance of the anti-spam handler is created.
 */
class Stla_Admin_Fetch_Anispam {




	/**
	 * Stores the singleton instance of the Stla_Admin_Fetch_Anispam class.
	 *
	 * @var Stla_Admin_Fetch_Anispam|null
	 * @static
	 * @access private
	 */
	private static $instance;

	/**
	 * Retrieves or creates the singleton instance of the Stla_Admin_Fetch_Anispam class.
	 *
	 * @return Stla_Admin_Fetch_Anispam The singleton instance of the class.
	 * @static
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Stla_Admin_Fetch_Anispam ) ) {
			self::$instance = new Stla_Admin_Fetch_Anispam();
		}
		return self::$instance;
	}

	/**
	 * Initializes the anti-spam settings by registering the AJAX action for handling anti-spam settings.
	 *
	 * This constructor sets up the WordPress AJAX action to handle anti-spam settings
	 * when the corresponding AJAX request is made.
	 *
	 * @since 5.21
	 * @access public
	 */
	public function __construct() {
		add_action( 'wp_ajax_stla_anit_spam_settings', array( $this, 'stla_anit_spam_settings' ) );
		add_action( 'wp_ajax_stla_save_antispam_settings', array( $this, 'stla_save_antispam_settings' ) );
		add_action( 'wp_ajax_stla_antispam_user_roles_data', array( $this, 'stla_antispam_user_roles_data' ) );
	}

	public function stla_antispam_user_roles_data() {
		global $wp_roles;

		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'stla_gravity_booster_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$form_id = isset( $_POST['formId'] ) ? sanitize_text_field( wp_unslash( $_POST['formId'] ) ) : '';
		if ( empty( $form_id ) ) {
			wp_send_json_error( 'Invalid form ID' );
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = wp_roles();
		}

		$all_roles    = isset( $wp_roles->roles ) ? $wp_roles->roles : array();
		$roles_for_js = array();

		foreach ( $all_roles as $role_key => $role_data ) {

			// Skip the 'administrator' role from optoins.
			if ( 'administrator' === $role_key ) {
				continue;
			}

			$roles_for_js[] = array(
				'value' => $role_key,
				'label' => translate_user_role( $role_data['name'] ),
			);
		}

		wp_send_json_success( $roles_for_js );
	}

	/**
	 * Saves anti-spam settings for a specific Gravity Form.
	 *
	 * Validates the request using a nonce, checks for valid form ID and anti-spam settings,
	 * and updates the settings in the WordPress options table. Returns the saved settings
	 * or an error if the save operation fails.
	 *
	 * @since 5.21
	 * @access public
	 *
	 * @return void Sends a JSON response with the saved anti-spam settings or an error.
	 */
	public function stla_save_antispam_settings() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'stla_gravity_booster_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$form_id           = isset( $_POST['formId'] ) ? sanitize_text_field( wp_unslash( $_POST['formId'] ) ) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Raw JSON, sanitized per leaf after json_decode() below.
		$antispam_settings = isset( $_POST['antiSpamSettings'] ) ? wp_unslash( $_POST['antiSpamSettings'] ) : '';

		if ( empty( $antispam_settings ) || empty( $form_id ) ) {
			wp_send_json_error( 'Invalid anti-spam settings' );
		}

		$antispam_settings = json_decode( $antispam_settings, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error( 'Error decoding the anti-spam settings JSON' );
		}

		$antispam_settings = Stla_Admin_Fetch_Content_Area::sanitize_settings_recursive( $antispam_settings );

		$has_uploaded = update_option( 'gf_stla_antispam_settings_' . $form_id, $antispam_settings );

		if ( false === $has_uploaded ) {
			wp_send_json_error( 'Failed to save anti-spam settings' );
		}

		$saved_antispam_settings = get_option( 'gf_stla_antispam_settings_' . $form_id, array() );

		wp_send_json_success( $saved_antispam_settings );
	}

	/**
	 * Retrieves anti-spam settings for a specific Gravity Form.
	 *
	 * Validates the request using a nonce, checks for a valid form ID, and returns
	 * the saved anti-spam settings for the specified form. If no settings exist,
	 * returns an empty array.
	 *
	 * @since 5.21
	 * @access public
	 *
	 * @return void Sends a JSON response with the anti-spam settings or an error.
	 */
	public function stla_anit_spam_settings() {

		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'stla_gravity_booster_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$form_id = isset( $_POST['formId'] ) ? sanitize_text_field( wp_unslash( $_POST['formId'] ) ) : '';
		if ( ! $form_id ) {
			wp_send_json_error( 'Invalid form id' );
		}

		$saved_antispam_settings = get_option( 'gf_stla_antispam_settings_' . $form_id, array() );

		if ( empty( $saved_antispam_settings ) ) {
			$saved_antispam_settings = array();
		}

		wp_send_json_success( $saved_antispam_settings );
	}
}

function stla_intialize_fetch_anispam() {
	return Stla_Admin_Fetch_Anispam::instance();
}

stla_intialize_fetch_anispam();
