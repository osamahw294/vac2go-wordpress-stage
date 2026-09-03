<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages user role restrictions for Gravity Forms preview and display.
 *
 * This class provides functionality to control form access based on user roles,
 * implementing singleton pattern for managing form preview restrictions and
 * validating user permissions for viewing specific forms.
 *
 * @author Jasvir Singh
 * @since 5.22
 */
class Stla_Antispam_User_Restrictions {

	/**
	 * Stores the singleton instance of the Stla_Antispam_User_Restrictions class.
	 *
	 * @var Stla_Antispam_User_Restrictions|null
	 * @access private
	 * @static
	 */
	private static $instance;


	/**
	 * Retrieves the singleton instance of the Stla_Antispam_User_Restrictions class.
	 *
	 * Creates a new instance if one does not already exist, ensuring only one
	 * instance of the class is created throughout the application.
	 *
	 * @return Stla_Antispam_User_Restrictions The singleton instance of the class.
	 * @static
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Stla_Antispam_User_Restrictions ) ) {
			self::$instance = new Stla_Antispam_User_Restrictions();
		}
		return self::$instance;
	}

	public function __construct() {
		add_filter( 'gform_form_args', array( $this, 'handle_form_display' ), 10 );
		add_filter( 'gform_form_not_found_message', array( $this, 'handle_form_not_found' ), 10, 2 );
	}

	/**
	 * Handles the display of a message when a form is not found.
	 *
	 * This method is triggered when a Gravity Form cannot be located or accessed,
	 * and provides a custom message based on form preview validation.
	 *
	 * @param string $message The default not found message.
	 * @param int    $form_id The ID of the form that was not found.
	 * @return string Modified message indicating form access restrictions.
	 */
	public function handle_form_not_found( $message, $form_id ) {
		// should only work on frontend.
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return $message;
		}

		$is_preview_valid_for_user = self::validate_form_preview_request( $form_id );
		if ( $is_preview_valid_for_user ) {
			return $message;
		}

		$user_validation_message = self::get_user_role_validation_setting( $form_id, 'userRolesValidationMessage' );
		if ( is_wp_error( $user_validation_message ) || empty( $user_validation_message ) || ! is_string( $user_validation_message ) ) {
			$user_validation_message = 'You are not allowed to view this form';
		}

		return "<p class=\"gform_not_found\">$user_validation_message</p>";
	}

	/**
	 * Handles form preview restrictions and validation.
	 *
	 * Checks if the current user is allowed to preview a specific Gravity Form
	 * by validating user roles and preview access permissions.
	 *
	 * @param array $form_args The arguments for the form being previewed.
	 * @return array Modified form arguments, potentially with form_id set to 0 if access is restricted.
	 */
	public function handle_form_display( $form_args ) {
		// should only work on frontend and for adminstrators.
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return $form_args;
		}

		if ( empty( $form_args['form_id'] ) ) {
			return $form_args;
		}

		// make sure form id is always an integer.
		$form_id = isset( $form_args['form_id'] ) ? absint( $form_args['form_id'] ) : 0;
		if ( empty( $form_id ) ) {
			return $form_args;
		}

		$is_preview_valid_for_user = self::validate_form_preview_request( $form_id );
		if ( $is_preview_valid_for_user ) {
			return $form_args;
		}

		// if user does not have any of the allowed user roles, then do not show the form.
		$form_args['form_id'] = 0;
		return $form_args;
	}

	/**
	 * Validates whether a form preview request is allowed for a specific form.
	 *
	 * This method checks if the current user has permission to preview a given form
	 * based on user role restrictions. It determines whether the form preview should
	 * be displayed or blocked.
	 *
	 * @param int $form_id The ID of the Gravity Form to validate for preview.
	 * @return bool True if the form preview is valid for the user, false otherwise.
	 */
	public static function validate_form_preview_request( $form_id ) {

		$allowed_user_roles = self::get_user_role_validation_setting( $form_id, 'allowedUserRolesToViewForm' );

		// if invalid or no antispam data is saved then show the form.
		if ( is_wp_error( $allowed_user_roles ) || empty( $allowed_user_roles ) ) {
			return true;
		}

		// if form is allowed to specific user role then logged out user can not see the form.
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// Get the current user roles.
		$user       = wp_get_current_user();
		$user_roles = ! empty( $user->roles ) ? $user->roles : array();

		foreach ( $user_roles as $role ) {
			// if user has any of the allowed user roles, then show the form.
			if ( in_array( $role, $allowed_user_roles, true ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Retrieves a specific user role validation setting for a given form.
	 *
	 * This method fetches the configuration for user role restrictions from the antispam settings
	 * for a specific Gravity Form. It allows retrieving different types of user role validation
	 * settings based on the provided key.
	 *
	 * @param int    $form_id                The ID of the Gravity Form to retrieve settings for.
	 * @param string $user_validation_setting The specific user validation setting to retrieve.
	 * @return array|bool|WP_Error The requested user role validation setting, or false/error if not found.
	 */
	public static function get_user_role_validation_setting( $form_id, $user_validation_setting ) {

		if ( ! $form_id ) {
			return new WP_Error( 'invalid_form_id', 'Invalid form ID.' );
		}

		$antispam_settings = get_option( 'gf_stla_antispam_settings_' . $form_id, array() );

		if ( empty( $antispam_settings ) || ! is_array( $antispam_settings ) || empty( $antispam_settings['restriction'] ) || ! is_array( $antispam_settings['restriction'] ) ) {
			return new WP_Error( 'invalid_data_saved', 'Invalid data saved in antispam settings.' );
		}

		$restriction_settings = $antispam_settings['restriction'];

		// Check if the setting is enabled and has the required user roles.
		if ( empty( $restriction_settings['userRolesValidationEnabled'] ) ) {
			return new WP_Error( 'invalid_data_saved', 'Invalid user data saved in antispam settings.' );
		}

		$settings_data = ! empty( $restriction_settings[ $user_validation_setting ] ) ? $restriction_settings[ $user_validation_setting ] : false;

		// always allow the administrator to view the form.
		if ( 'allowedUserRolesToViewForm' === $user_validation_setting ) {
			if ( is_array( $settings_data ) ) {
				$settings_data[] = 'administrator';
			} else {
				$settings_data = array( 'administrator' );
			}
		}

		return $settings_data;
	}
}

if ( class_exists( 'Stla_Antispam_User_Restrictions' ) ) {

	Stla_Antispam_User_Restrictions::instance();
}
