<?php
/**
 * Handles restricted email submissions for Gravity Forms.
 *
 * This file hooks into the `gform_validation` filter to prevent form submission
 * based on email allowlist or denylist rules defined in the anti-spam settings.
 *
 * @package STLA_Antispam
 * @author Jasvir Singh
 * @since 5.21
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

/**
 * Singleton class for managing email spam marking functionality.
 *
 * Provides a single instance of the Stla_Antispam_Email_Restrict_Submission class
 * to ensure only one instance is created and used throughout the plugin.
 */
class Stla_Antispam_Email_Restrict_Submission {


	/**
	 * Stores the singleton instance of the Stla_Antispam_Email_Restrict_Submission class.
	 *
	 * @var Stla_Antispam_Email_Restrict_Submission|null
	 * @static
	 */
	private static $instance;

	/**
	 * Retrieves the singleton instance of the Stla_Antispam_Email_Restrict_Submission class.
	 *
	 * This method ensures only one instance of the class is created and provides
	 * global access to that instance.
	 *
	 * @return Stla_Antispam_Email_Restrict_Submission The singleton instance of the class.
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Stla_Antispam_Email_Restrict_Submission ) ) {
			self::$instance = new Stla_Antispam_Email_Restrict_Submission();
		}
		return self::$instance;
	}

	/**
	 * Initializes the email restriction functionality by hooking into Gravity Forms validation.
	 *
	 * Adds a filter to the Gravity Forms validation process to enable email-based submission restrictions.
	 */
	public function __construct() {
		add_filter( 'gform_validation', array( $this, 'restrict_submission_for_valid_emails' ), 10 );
	}

	/**
	 * Validates and restricts form submissions based on email address restrictions.
	 *
	 * @param array $validation_result The Gravity Forms validation result array.
	 * @return array Modified validation result with potential blocking of submission.
	 */
	public function restrict_submission_for_valid_emails( $validation_result ) {
		if ( empty( $validation_result['form'] ) ) {
			return $validation_result;
		}

		// $form    = $validation_result['form'];
		$form_id = rgar( $validation_result['form'], 'id' );

		// Fetch saved anti-spam settings.
		$saved_antispam_settings = get_option( 'gf_stla_antispam_settings_' . $form_id, array() );

		// Bail if settings are missing or email restriction is disabled.
		if (
		empty( $saved_antispam_settings ) ||
		empty( $saved_antispam_settings['restriction'] ) ||
		empty( $saved_antispam_settings['restriction']['emailsValidationEnabled'] )
		) {
			return $validation_result;
		}

		// Check if the selected action is "restrictSubmission".
		$email_action = $saved_antispam_settings['restriction']['onEmailsMatch'] ?? 'restrictSubmission';
		if ( 'restrictSubmission' !== $email_action ) {
			return $validation_result;
		}

		// Fetch validation mode and corresponding email list.
		$emails_validation_mode = $saved_antispam_settings['restriction']['emailsValidationMode'] ?? 'denyList';
		$saved_allowed_emails   = $saved_antispam_settings['restriction']['allowedEmails'] ?? '';
		$saved_denied_emails    = $saved_antispam_settings['restriction']['deniedEmails'] ?? '';
		$validation_message     = $saved_antispam_settings['restriction']['blockedEmailsMessage'] ?? 'This email address is not allowed for submission.';

		// If required list is empty, skip.
		if ( 'allowList' === $emails_validation_mode && empty( $saved_allowed_emails ) ) {
			return $validation_result;
		} elseif ( 'denyList' === $emails_validation_mode && empty( $saved_denied_emails ) ) {
			return $validation_result;
		}

		$block_submission = false;

		// Loop through all email fields.
		foreach ( $validation_result['form']['fields'] as &$field ) {
			if ( 'email' !== $field->type ) {
				continue;
			}

			$email_value = rgpost( "input_{$field->id}" );

			if ( empty( $email_value ) ) {
				continue;
			}

			// Run validation check based on mode.
			if ( 'denyList' === $emails_validation_mode ) {
				if ( Stla_Antispam_Common_Helpers::does_email_match_any( $email_value, $saved_denied_emails ) ) {
					$block_submission = true;
				}
			} elseif ( 'allowList' === $emails_validation_mode ) {
				if ( ! Stla_Antispam_Common_Helpers::does_email_match_any( $email_value, $saved_allowed_emails ) ) {
					$block_submission = true;
				}
			}

			if ( $block_submission ) {
				// Invalidate field and set custom message.
				$field->failed_validation  = true;
				$field->validation_message = $validation_message;

				// Invalidate the whole form.
				$validation_result['is_valid'] = false;
			}
		}

		unset( $field );

		// Pass the updated form back.
		return $validation_result;
	}
}

// Initialize the singleton instance.
Stla_Antispam_Email_Restrict_Submission::instance();
