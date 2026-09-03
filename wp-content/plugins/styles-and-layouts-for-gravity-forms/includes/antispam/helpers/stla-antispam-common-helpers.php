<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for common helper functions of antispam.
 *
 * @author Jasvir Singh
 * @since 5.21
 */
class Stla_Antispam_Common_Helpers {


	/**
	 * Retrieves an array of field types that are considered checkboxes.
	 *
	 * @return array List of checkbox-like field types in Gravity Forms.
	 */
	public static function get_checkbox_types() {
		return array( 'checkbox', 'post_tags', 'post_custom_field', 'post_category', 'quantity', 'option', 'consent', 'multi_choice', 'image_choice' );
	}

	/**
	 * Retrieves an array of field types that are considered radio buttons.
	 *
	 * @return array List of radio button-like field types in Gravity Forms.
	 */
	public static function get_radio_types() {
		return array( 'radio', 'post_tags', 'post_custom_field', 'post_category', 'option', 'shipping', 'product', 'image_choice', 'multi_choice' );
	}

	/**
	 * Retrieves an array of field types that are considered select fields.
	 *
	 * @return array List of select field types in Gravity Forms.
	 */
	public static function get_select_types() {
		return array( 'select', 'post_tags', 'post_custom_field', 'post_category', 'quantity', 'option', 'shipping', 'product' );
	}

	/**
	 * Retrieves an array of field types that are considered file upload fields.
	 *
	 * @return array List of file upload field types in Gravity Forms.
	 */
	public static function get_file_types() {
		return array( 'fileupload', 'post_image' );
	}

	/**
	 * Retrieves an array of field types that are considered email fields.
	 *
	 * @return array List of email field types in Gravity Forms.
	 */
	public static function get_email_types() {
		return array( 'email' );
	}
	/**
	 * Checks if any keywords match a given value.
	 *
	 * @param array|string $keywords Keywords to match against the value.
	 * @param string       $value    The value to check for keyword matches.
	 * @return bool True if a keyword is found, false otherwise.
	 */
	public static function match_keywords_with_value( $keywords, $value ) {
		// on invalid input return false.
		if ( empty( $keywords ) || empty( $value ) ) {
			return false;
		}

		// Convert the value to lowercase using mb_* for multibyte strings.
		// to support the multilangual characters.
		$value = mb_strtolower( $value, 'UTF-8' );

		// if string is passed the convert to array..
		if ( is_string( $keywords ) ) {
			// Split keywords by new line and trim.
			$keywords = preg_split( '/\r\n|\r|\n/', $keywords );
			$keywords = array_filter( array_map( 'sanitize_text_field', array_map( 'trim', $keywords ) ) );

			if ( empty( $keywords ) ) {
				return false;
			}
		}

		// loop for all the keywords.
		foreach ( $keywords as $keyword ) {
			$normalized_keyword = mb_strtolower( trim( $keyword ), 'UTF-8' );

				// Match full word boundaries by surrounding keyword with non-letter characters.
			$pattern = '/(?<!\p{L})' . preg_quote( $normalized_keyword, '/' ) . '(?!\p{L})/u';

			// Check if the keyword is in the value.
			if ( preg_match( $pattern, $value ) ) {

				// mark as spam.
				return true;
			}
		}

		// if not found return false.
		return false;
	}

	/**
	 * Checks if an email matches any of the provided rules.
	 *
	 * @param string $email     The email address to check.
	 * @param string $raw_rules A string of rules to match against the email.
	 * @return bool True if the email matches any rule, false otherwise.
	 */
	public static function does_email_match_any( $email, $raw_rules ) {

		if ( empty( $raw_rules ) || empty( $email ) ) {
			return false;
		}

		// normalize the email.
		$email = trim( strtolower( $email ) );

		// Normalize all types of line endings to Unix-style (\n).
		// This ensures consistent splitting regardless of whether the input uses \r\n (Windows), \r (old Mac), or \n (Unix/Linux).
		$normalized = str_replace( array( "\r\n", "\r" ), "\n", $raw_rules );

		// Split the normalized string into an array of rules.
		$raw_items = preg_split( '/[\n,]+/', $normalized );

		// Remove any empty or whitespace-only items.
		$rules = array_filter( array_map( 'trim', $raw_items ) );

		foreach ( $rules as $rule ) {
			$regex = self::wildcard_to_regex( $rule );
			if ( preg_match( $regex, $email ) ) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Converts a wildcard pattern into a regex pattern for email matching.
	 *
	 * Supported wildcard:
	 * - `*` matches any number of characters (including none).
	 *
	 * Examples:
	 * - *@gmail.com → matches any email ending in @gmail.com
	 * - admin@*     → matches any email starting with admin@
	 * - *@*.edu     → matches any email with a .edu domain, including subdomains
	 *
	 * @param string $pattern The wildcard email pattern.
	 * @return string A regex pattern ready to be used with preg_match().
	 */
	public static function wildcard_to_regex( $pattern ) {

		// Trim any accidental whitespace around the pattern.
		$pattern = trim( $pattern );

		// If the pattern is empty, return a regex that matches only an empty string.
		// Prevents accidental matches if empty rules are saved.
		if ( empty( $pattern ) ) {
			return '/^$/i';
		}

		// Escape all regex special characters so the string is treated literally.
		$escaped = preg_quote( $pattern, '/' );

		// Replace escaped wildcard (*) with regex pattern (.*) to match any character(s).
		$regex = str_replace( '\*', '.*', $escaped );

		// Return the final regex, wrapped with ^ (start) and $ (end) for exact matching.
		// 'i' modifier makes it case-insensitive.
		return '/^' . $regex . '$/i';
	}
}
