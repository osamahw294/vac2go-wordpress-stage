<?php

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

/**
 * Manages anti-spam keyword pre-checking functionality for Gravity Forms.
 *
 * This class implements a singleton pattern to handle keyword-based form validation
 * and potential blocking of form submissions containing specific keywords.
 *
 * @author Jasvir Singh
 * @since 5.21
 */
class Stla_Antispam_Kyeword_Precheck {

	/**
	 * Stores the singleton instance of the Stla_Antispam_Kyeword_Precheck class.
	 *
	 * @var Stla_Antispam_Kyeword_Precheck|null
	 * @access private
	 * @static
	 */
	private static $instance;

	/**
	 * Retrieves the singleton instance of the Stla_Antispam_Kyeword_Precheck class.
	 *
	 * @return Stla_Antispam_Kyeword_Precheck The singleton instance of the class.
	 * @static
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Stla_Antispam_Kyeword_Precheck ) ) {
			self::$instance = new Stla_Antispam_Kyeword_Precheck();
		}
		return self::$instance;
	}

	/**
	 * Initializes the anti-spam keyword pre-check functionality.
	 *
	 * Hooks the keyword matching validation method into the Gravity Forms
	 * validation process to enable keyword-based form submission blocking.
	 *
	 * @since 5.21
	 */
	public function __construct() {
		add_filter( 'gform_validation', array( $this, 'block_form_on_keyword_match' ), 10 );
	}

	/**
	 * Validates and blocks form submissions based on predefined keyword matches.
	 *
	 * This method checks form input fields against a list of blocked keywords
	 * and prevents form submission if a match is found, according to the form's
	 * anti-spam settings.
	 *
	 * @param array $validation_result The current form validation result.
	 * @return array Modified validation result with potential form blocking.
	 */
	public function block_form_on_keyword_match( $validation_result ) {

		if ( empty( $validation_result['form'] ) ) {
			return $validation_result;
		}

		$form_id = rgar( $validation_result['form'], 'id' );

		// Get settings.
		$saved_antispam_settings = get_option( 'gf_stla_antispam_settings_' . $form_id, array() );

		// Validate presence of restriction and keywords setting.
		if ( empty( $saved_antispam_settings ) ||
		empty( $saved_antispam_settings['restriction'] ) ||
		empty( $saved_antispam_settings['restriction']['keywordsEnabled'] ) ) {
			return $validation_result;
		}

		// will run only when action is to restrict the submission.
		$action_on_match = isset( $saved_antispam_settings['restriction']['onKeywordMatch'] ) ? $saved_antispam_settings['restriction']['onKeywordMatch'] : '';
		if ( empty( $action_on_match ) || 'restrictSubmission' !== $action_on_match ) {
			return $validation_result;
		}

		// Extract and clean keywords from settings.
		$raw_keywords = isset( $saved_antispam_settings['restriction']['keywords'] ) ? $saved_antispam_settings['restriction']['keywords'] : '';
		if ( empty( $raw_keywords ) ) {
			return $validation_result;
		}

		// Prepare form for validation.
		$form = $validation_result['form'];

		// keyword validation message.
		$validation_message = ! empty( $saved_antispam_settings['restriction']['blockedKeywordsMessage'] ) ? $saved_antispam_settings['restriction']['blockedKeywordsMessage'] : 'This field contains a blocked keyword.';

		// exclude these fields from checking.
		$radio_types    = Stla_Antispam_Common_Helpers::get_radio_types();
		$checkbox_types = Stla_Antispam_Common_Helpers::get_checkbox_types();
		$email_types    = Stla_Antispam_Common_Helpers::get_email_types();
		$select_types   = Stla_Antispam_Common_Helpers::get_select_types();
		$file_types     = Stla_Antispam_Common_Helpers::get_file_types();

		$exclude_fields = array_merge( $radio_types, $checkbox_types, $email_types, $select_types, $file_types );

		foreach ( $form['fields'] as $index => $field ) {
			// Skip admin-only fields or unsupported input types.
			if ( $field->is_administrative() || in_array( $field->type, $exclude_fields, true ) ) {
				continue;
			}

			// Get field value.
			$value = rgpost( "input_{$field->id}" );
			if ( empty( $value ) ) {
				continue;
			}

				// Match against keywords.
			if ( Stla_Antispam_Common_Helpers::match_keywords_with_value( $raw_keywords, $value ) && ! $field->failed_validation ) {
				$validation_result['is_valid'] = false;

				// Mark this field as failed.
				$validation_result['form']['fields'][ $index ]->failed_validation  = true;
				$validation_result['form']['fields'][ $index ]->validation_message = esc_html( $validation_message );
			}
		}

		return $validation_result;
	}
}

function stla_intialize_antispam_keyword_precheck() {
	return Stla_Antispam_Kyeword_Precheck::instance();
}

stla_intialize_antispam_keyword_precheck();
