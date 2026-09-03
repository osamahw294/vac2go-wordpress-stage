<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles Gravity Forms submissions for anti-spam functionality.
 *
 * This class implements a singleton pattern to manage anti-spam processing
 * for Gravity Forms form submissions.
 *
 * @author Jasvir Singh
 * @since 5.21
 */
class Stla_Antispam_Keyword_Mark_spam {

	/**
	 * Stores the singleton instance of the class.
	 *
	 * @var self
	 * @access private
	 * @static
	 */
	private static $instance;


	/**
	 * Retrieves the singleton instance of the class.
	 *
	 * @return self The singleton instance of the class.
	 * @static
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Stla_Antispam_Keyword_Mark_spam ) ) {
			self::$instance = new Stla_Antispam_Keyword_Mark_spam();
		}
		return self::$instance;
	}

	/**
	 * Initializes the anti-spam keyword filter for Gravity Forms submissions.
	 *
	 * Adds a filter to check form submissions against predefined spam keywords
	 * and potentially mark entries as spam based on configured settings.
	 *
	 * @access public
	 */
	public function __construct() {
		add_filter( 'gform_entry_is_spam', array( $this, 'stla_handle_gf_submissions_for_anispam' ), 10, 3 );
	}

	/**
	 * Determines if a Gravity Forms entry should be marked as spam.
	 *
	 * @param bool  $is_spam Indicates whether the entry is currently considered spam.
	 * @param array $form    The Gravity Form form data.
	 * @param array $entry   The form submission entry data.
	 * @return bool Whether the entry should be marked as spam.
	 */
	public function stla_handle_gf_submissions_for_anispam( $is_spam, $form, $entry ) {

		// check for valid form id and entry id.
		if ( empty( $form['id'] ) || empty( $entry['id'] ) || $is_spam ) {
			return $is_spam;
		}

		// the form id.
		$form_id = absint( rgar( $form, 'id' ) );

		// Fetch saved anti-spam settings.
		$saved_antispam_settings = get_option( 'gf_stla_antispam_settings_' . $form_id, array() );

		// Validate presence of restriction and keywords setting.
		if ( empty( $saved_antispam_settings ) ||
			empty( $saved_antispam_settings['restriction'] ) ||
			empty( $saved_antispam_settings['restriction']['keywordsEnabled'] )
			) {
			return $is_spam;
		}

		// will run only when action is to mark submission as spam.
		$action_on_match = isset( $saved_antispam_settings['restriction']['onKeywordMatch'] ) ? $saved_antispam_settings['restriction']['onKeywordMatch'] : '';
		if ( empty( $action_on_match ) || 'markAsSpam' !== $action_on_match ) {
			return $is_spam;
		}

		// Extract and clean keywords from settings.
		$raw_keywords = isset( $saved_antispam_settings['restriction']['keywords'] ) ? $saved_antispam_settings['restriction']['keywords'] : '';
		if ( empty( $raw_keywords ) ) {
			return $is_spam;
		}

		// exclude these fields from checking.
		$radio_types    = Stla_Antispam_Common_Helpers::get_radio_types();
		$checkbox_types = Stla_Antispam_Common_Helpers::get_checkbox_types();
		$email_types    = Stla_Antispam_Common_Helpers::get_email_types();
		$select_types   = Stla_Antispam_Common_Helpers::get_select_types();
		$file_types     = Stla_Antispam_Common_Helpers::get_file_types();

		$exclude_fields = array_merge( $radio_types, $checkbox_types, $email_types, $select_types, $file_types );

		// check for all fields.
		foreach ( $form['fields'] as $field ) {

			// Skipping fields which are administrative or the wrong type.
			if ( $field->is_administrative() || in_array( $field->type, $exclude_fields, true ) ) {
				continue;
			}

			// Skipping fields which don't have a value.
			$value = $field->get_value_export( $entry );

			if ( empty( $value ) || ! is_string( $value ) ) {
				continue;
			}

			// normalize the value.
			$value = strtolower( $value );

			// match the keywords with the value.
			$has_value_containing_keywords = Stla_Antispam_Common_Helpers::match_keywords_with_value( $raw_keywords, $value );
			if ( true === $has_value_containing_keywords ) {

				if ( method_exists( 'GFCommon', 'set_spam_filter' ) ) {
						$spam_filter_message = sprintf(
							/* translators: %s: The blocked keyword */
							__( 'Blocked because the submission contained the blocked keyword: %s', 'styles-and-layouts-for-gravity-forms' ),
							esc_html( $raw_keywords )
						);
						GFCommon::set_spam_filter( $form_id, 'Gravity Booster Anti-spam', $spam_filter_message );
				}

				// mark as spam.
				return true;
			}
		}

		return $is_spam;
	}
}


function stla_intialize_antispam_keyword_mark_spam() {
	return Stla_Antispam_Keyword_Mark_spam::instance();
}

stla_intialize_antispam_keyword_mark_spam();
