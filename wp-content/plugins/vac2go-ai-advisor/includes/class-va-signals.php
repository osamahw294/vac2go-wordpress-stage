<?php
/**
 * Per-turn signals for the widget: whether this answer is the right moment to offer
 * a rep follow-up, and whether the question was about availability.
 *
 * Deterministic on purpose. The system prompt already pins the model to fixed
 * phrasing at exactly the moments that matter (the recommendation caveat sentence,
 * "I don't know", the "I don't handle..." refusal), so matching those phrases is
 * reliable, costs nothing, and adds no latency. A classifier call would do the same
 * job slower and could fail.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VA_Signals {

	/**
	 * Appended by the server to answers to availability questions. Added AFTER the
	 * filter and judge have run, never by the model: the judge reads "live
	 * availability" as an availability promise and would replace the whole answer.
	 */
	const AVAILABILITY_CTA = 'You can also sign up for an account to view our live availability on our rental portal: https://rental.vac2go.com';

	/**
	 * Unit and make names from the knowledge base. A mention in the question means the
	 * customer is asking about a specific unit.
	 */
	const UNIT_PATTERN = '/\b(hv[\s-]?(33|56|57)|mc\s?(1312|1510)|sc\s?1512|am\s?36|gap\s?vax|guzzler|guzzcavator|keith huber|huber|pres\s?vac|power\s?vac|super\s?sucker|super products|mud dog|camel max|vactor|tru\s?vac|hxx|ctos|tornado|kaiser|terravac|schellvac|svhx|baron|berringer|dominator|king vac|knight|boss\s?vac|bv\s?500|vermeer|lpxdt|dragon|iti|bte|benlee|bergey\'?s?|galfab|palmer|kenworth|t880|peterbilt|579)\b/i';

	/**
	 * Questions about price, contracts or terms.
	 */
	const COMMERCIAL_PATTERN = '/(\$|\b(price|pricing|priced|cost|costs|(rental|day|daily|weekly|monthly|hourly) rates?|quote|quotes|how much|per (day|week|month|hour)|contract|contracts|terms|lease|leasing|lease[\s-]to[\s-]own|rent[\s-]to[\s-]own|deposit|insurance|delivery fee|deliver(y|ed)? cost|invoice|billing|discount|buy|purchase|for sale|used units?)\b)/i';

	/**
	 * Questions about whether, when or where a unit can be had.
	 */
	const AVAILABILITY_PATTERN = '/\b(avail\w*|in stock|on hand|in (your |the )?(yard|inventory|fleet) (now|today|right now)|lead[\s-]?time|how soon|when can (i|we|you)|(can|could) (i|we) get (one|it|a|an)|do you (guys )?have (any|one|a|an)|have (any|one) (open|free|ready)|ready to go|book(ing)?|reserve|reservation|schedule a (unit|truck)|this week|next week|tomorrow|asap)\b/i';

	/**
	 * An email address, or a phone number with its area code, typed into the chat. A
	 * bare seven-digit form is deliberately not matched: "400-1500 gallons" looks the
	 * same, and a customer leaving a number for a callback gives the area code.
	 */
	const CONTACT_PATTERN = '/[A-Z0-9._%+-]+@[A-Z0-9-]+(\.[A-Z0-9-]+)+|(\+?1[\s.-]?)?(\(\d{3}\)\s?|\b\d{3}[\s.-])\d{3}[\s.-]\d{4}\b/i';

	/**
	 * The answer could not be given.
	 */
	const UNKNOWN_PATTERN = '/(\bI don[\'’]?t know\b|\bdon[\'’]?t have (that|this|those|the|any|enough) (detail|details|information|info|data|specs?)\b|\bcan[\'’]?t speak to\b|\bnot able to (answer|confirm)\b|\bpolicy question\b|\bbeyond what I can\b)/i';

	/**
	 * Why this answer should offer a rep follow-up, or null when it should not.
	 *
	 * @return string|null contact | recommendation | unit | unknown | commercial
	 */
	public static function followup_reason( $question, $answer ) {
		$q = (string) $question;
		$a = (string) $answer;

		if ( '' === trim( $a ) ) {
			return null;
		}

		// A visitor typing their details into the chat wants a rep: show the form,
		// which is the one place those details actually reach the team.
		if ( preg_match( self::CONTACT_PATTERN, $q ) ) {
			return 'contact';
		}

		// The recommendation caveat is mandatory on every category recommendation.
		if ( false !== stripos( $a, 'high-level recommendation' ) ) {
			return 'recommendation';
		}
		if ( self::is_commercial_question( $q ) || false !== stripos( $a, "I don't handle" ) || false !== stripos( $a, 'I don’t handle' ) ) {
			return 'commercial';
		}
		if ( preg_match( self::UNKNOWN_PATTERN, $a ) ) {
			return 'unknown';
		}
		if ( preg_match( self::UNIT_PATTERN, $q ) ) {
			return 'unit';
		}
		return null;
	}

	/**
	 * Pricing, contracts, terms, or availability.
	 */
	public static function is_commercial_question( $question ) {
		$q = (string) $question;
		return (bool) preg_match( self::COMMERCIAL_PATTERN, $q ) || self::is_availability_question( $q );
	}

	public static function is_availability_question( $question ) {
		return (bool) preg_match( self::AVAILABILITY_PATTERN, (string) $question );
	}

	/**
	 * The availability CTA to append to this answer, or '' when it does not apply.
	 *
	 * Skipped for prompt-leak replacements (those are a security response, not an
	 * answer) and when the answer already carries it (an idempotent replay).
	 */
	public static function availability_suffix( $question, $answer, $stage = null ) {
		if ( ! self::is_availability_question( $question ) ) {
			return '';
		}
		if ( in_array( $stage, array( 'canary', 'structural' ), true ) ) {
			return '';
		}
		if ( false !== stripos( (string) $answer, 'rental.vac2go.com' ) ) {
			return '';
		}
		return "\n\n" . self::AVAILABILITY_CTA;
	}
}
