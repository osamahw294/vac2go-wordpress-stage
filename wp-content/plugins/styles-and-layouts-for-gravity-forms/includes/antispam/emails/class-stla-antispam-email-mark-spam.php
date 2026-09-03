<?php
/**
 * Handles email validation logic for marking entries as spam
 * based on allow or deny list configurations in STLA Antispam settings.
 *
 * This class hooks into Gravity Forms submission handling and
 * marks entries as spam based on configured email filters.
 *
 * @package STLA_Antispam
 * @author      Jasvir Singh
 * @since       5.21
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

/**
 * Singleton class for managing email spam marking functionality.
 *
 * Provides a single instance of the Stla_Antispam_Email_Mark_Spam class
 * to ensure only one instance is created and used throughout the plugin.
 */
class Stla_Antispam_Email_Mark_Spam {

	/**
	 * Stores the singleton instance of the Stla_Antispam_Email_Mark_Spam class.
	 *
	 * @var Stla_Antispam_Email_Mark_Spam|null
	 * @static
	 */
	private static $instance;

	/**
	 * Retrieves or creates the singleton instance of the Stla_Antispam_Email_Mark_Spam class.
	 *
	 * Ensures only one instance of the class is created and returned.
	 * If no instance exists, a new instance is instantiated.
	 *
	 * @return Stla_Antispam_Email_Mark_Spam The singleton instance of the class.
	 * @static
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Stla_Antispam_Email_Mark_Spam ) ) {
			self::$instance = new Stla_Antispam_Email_Mark_Spam();
		}
		return self::$instance;
	}

	/**
	 * Initializes the anti-spam email validation by hooking into Gravity Forms entry spam detection.
	 *
	 * Attaches the email validation method to the Gravity Forms spam detection filter,
	 * enabling custom email-based spam prevention for form submissions.
	 */
	public function __construct() {
		add_filter( 'gform_entry_is_spam', array( $this, 'stla_handle_gf_submissions_for_anispam' ), 10, 3 );
	}

	/**
	 * Determines if a Gravity Form submission should be marked as spam.
	 *
	 * Checks and evaluates form submissions to identify potential spam entries
	 * based on specific criteria.
	 *
	 * @param bool  $is_spam Indicates whether the entry is currently marked as spam.
	 * @param array $form    The Gravity Form configuration details.
	 * @param array $entry   The submitted form entry data.
	 * @return bool           Whether the entry should be marked as spam.
	 */
	public function stla_handle_gf_submissions_for_anispam( $is_spam, $form, $entry ) {

		// check for valid form id and entry id.
		if ( empty( $form['id'] ) || empty( $entry['id'] ) || $is_spam ) {
			return $is_spam;
		}

		// the form id.
		$form_id = rgar( $form, 'id' );

		// Fetch saved anti-spam settings.
		$saved_antispam_settings = get_option( 'gf_stla_antispam_settings_' . $form_id, array() );

		// Validate presence of restriction and emails setting.
		if ( empty( $saved_antispam_settings ) ||
			empty( $saved_antispam_settings['restriction'] ) ||
			empty( $saved_antispam_settings['restriction']['emailsValidationEnabled'] )
			) {
			return $is_spam;
		}

		// Determines the email validation mode for anti-spam settings.
		$emails_validation_mode = ! empty( $saved_antispam_settings['restriction']['emailsValidationMode'] ) ? $saved_antispam_settings['restriction']['emailsValidationMode'] : 'denyList';

		// Allowed saved emails.
		$saved_allowed_emails = ! empty( $saved_antispam_settings['restriction']['allowedEmails'] ) ? $saved_antispam_settings['restriction']['allowedEmails'] : '';

		// Denied saved emails.
		$saved_denied_emails = ! empty( $saved_antispam_settings['restriction']['deniedEmails'] ) ? $saved_antispam_settings['restriction']['deniedEmails'] : '';

		$email_action = ! empty( $saved_antispam_settings['restriction']['onEmailsMatch'] ) ? $saved_antispam_settings['restriction']['onEmailsMatch'] : 'restrictSubmission';

		// If mode is allowList and no allowed emails are set, skip.
		if ( 'allowList' === $emails_validation_mode && empty( $saved_allowed_emails ) ) {
			return $is_spam;
		}

		// If mode is denyList and no denied emails are set, skip.
		if ( 'denyList' === $emails_validation_mode && empty( $saved_denied_emails ) ) {
			return $is_spam;
		}

		// Exit early if action is restrictSubmission — handle elsewhere.
		if ( 'restrictSubmission' === $email_action ) {
			// Custom handling will occur in a separate hook.
			return $is_spam;
		}

		// Check if the entry is spam.
		foreach ( $form['fields'] as $field ) {
			if ( 'email' === $field->type ) {
				$email_value = rgar( $entry, (string) $field->id );
				if ( empty( $email_value ) ) {
					continue;
				}

				// Do the matching.
				if ( 'denyList' === $emails_validation_mode ) {
					if ( Stla_Antispam_Common_Helpers::does_email_match_any( $email_value, $saved_denied_emails ) ) {
						return true; // mark as spam.
					}
				} elseif ( 'allowList' === $emails_validation_mode ) {
					if ( ! Stla_Antispam_Common_Helpers::does_email_match_any( $email_value, $saved_allowed_emails ) ) {
						return true; // mark as spam if not allowed.
					}
				}
			}
		}

		return $is_spam;
	}
}

// Initialize the class.
Stla_Antispam_Email_Mark_Spam::instance();
