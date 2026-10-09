<?php
/**
 * Output pipeline. Every model response passes through this, in order, before it is
 * logged or returned:
 *
 *   1. normalize        NFKC, strip zero-width chars, collapse whitespace (matching copy)
 *   2. canary           the prompt-leak canary token
 *   3. structural       section headers / phrases that only exist inside the prompt
 *   4. committal        pricing / availability / commitment regexes (admin-editable)
 *   5. profanity        crude word list (admin-editable)
 *   6. judge            cheap-LLM yes/no check, invoked by the caller (VA_REST) only
 *                       when 2-5 pass, because it needs an API call
 *   7. cosmetic         em-dash replacement; never flags, never replaces the message
 *
 * Stages 2-5 replace the whole response with the refusal fallback and record which
 * stage acted in filter_stage.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VA_Filter {

	const FALLBACK = "I don't handle pricing, availability, or commitments like that. A Vac2Go rep can give you a real answer. You can reach the team at vac2go.com/contact.";

	const LEAK_FALLBACK = "I can only help with Vac2Go equipment questions. For anything else, the team at vac2go.com/contact can help.";

	/** Shorter than this and the canary would match ordinary words, so the stage is skipped. */
	const MIN_CANARY_LEN = 12;

	/**
	 * Pattern lists earlier versions shipped, kept to recognise a stored list nobody
	 * has edited (see migrate_patterns()).
	 */
	const PREVIOUS_DEFAULT_PATTERNS = array(
		// v2.6 and earlier
		'/\\$\\s?\\d/
/\\b\\d[\\d,\\.]*\\s?(usd|dollars?|bucks)\\b/i
/\\busd\\s?\\d/i
/[£€]\\s?\\d/u
/\\b(one|two|three|four|five|six|seven|eight|nine|ten|twenty|fifty)\\s+(hundred\\s+|thousand\\s+)?(dollars?|bucks)\\b/i
/\\b\\d[\\d,\\.]*\\s*(\\/|per)\\s*-?\\s*(day|week|month|hour)\\b/i
/\\b(the\\s+)?(rate|cost|price)\\s+is\\b/i
/\\bquote\\s+(you|of)\\b/i
/\\bwe(\'ll| will)?\\s+rent\\s+it\\s+to\\s+you\\b/i
/\\b(it\'?s|we have|that\'?s|consider (it|this))\\s+a\\s+deal\\b/i
/\\bconsider (it|this) (sold|agreed)\\b/i
/\\bguaranteed?\\b/i
/\\byou can pick (it|one) up\\b/i
/\\breserved?\\s+(it\\s+|one\\s+)?for you\\b/i
/\\bin stock\\b/i
/\\bavailable (now|today|immediately)\\b/i
/\\bwe (can|will) (guarantee|promise)\\b/i
/\\bthis is binding\\b/i
/\\bi agree to (sell|rent|lease)\\b/i
/\\bno charge\\b/i
/\\bfree of charge\\b/i
/\\bdiscount(ed)?\\b/i
/\\bcan deliver by\\b/i
/\\bship(ped)? (today|tomorrow)\\b/i',
		// v2.7
		'/\\$\\s?\\d/
/\\b\\d[\\d,\\.]*\\s?(usd|dollars?|bucks)\\b/i
/\\busd\\s?\\d/i
/[£€]\\s?\\d/u
/\\b(one|two|three|four|five|six|seven|eight|nine|ten|twenty|fifty)\\s+(hundred\\s+|thousand\\s+)?(dollars?|bucks)\\b/i
/\\b\\d[\\d,\\.]*\\s*(\\/|per)\\s*-?\\s*(day|week|month|hour)\\b/i
/\\b(the\\s+)?(rate|cost|price)\\s+is\\b/i
/\\bquote\\s+(you|of)\\b/i
/\\bwe(\'ll| will)?\\s+rent\\s+it\\s+to\\s+you\\b/i
/\\b(it\'?s|we have|that\'?s|consider (it|this))\\s+a\\s+deal\\b/i
/\\bconsider (it|this) (sold|agreed)\\b/i
/(?<!\\bnot )(?<!n\'t )(?<!n’t )(?<!cannot )(?<!can not )\\bguaranteed?\\b/i
/\\byou can pick (it|one) up\\b/i
/\\breserved?\\s+(it\\s+|one\\s+)?for you\\b/i
/\\bin stock\\b/i
/\\bavailable (now|today|immediately)\\b/i
/\\bwe (can|will) (guarantee|promise)\\b/i
/\\bthis is binding\\b/i
/\\bi agree to (sell|rent|lease)\\b/i
/\\bno charge\\b/i
/\\bfree of charge\\b/i
/\\bdiscount(ed)?\\b/i
/\\bcan deliver by\\b/i
/\\bship(ped)? (today|tomorrow)\\b/i',
	);

	/**
	 * Replace the stored pattern list with the current defaults, but only when it is
	 * still exactly a list an earlier version shipped. An admin-edited list is left alone.
	 */
	public static function migrate_patterns() {
		$norm = function ( $t ) {
			return trim( str_replace( array( "\r\n", "\r" ), "\n", (string) $t ) );
		};
		$stored = $norm( get_option( 'va_banned_patterns', '' ) );
		foreach ( self::PREVIOUS_DEFAULT_PATTERNS as $old ) {
			if ( $stored === $norm( $old ) ) {
				update_option( 'va_banned_patterns', self::default_patterns_text() );
				return;
			}
		}
	}

	/**
	 * Default committal/pricing pattern list, one regex per line (admin-editable).
	 */
	public static function default_patterns_text() {
		$lines = array(
			'/\$\s?\d/',
			'/\b\d[\d,\.]*\s?(usd|dollars?|bucks)\b/i',
			'/\busd\s?\d/i',
			'/[£€]\s?\d/u',
			'/\b(one|two|three|four|five|six|seven|eight|nine|ten|twenty|fifty)\s+(hundred\s+|thousand\s+)?(dollars?|bucks)\b/i',
			'/\b\d[\d,\.]*\s*(\/|per)\s*-?\s*(day|week|month|hour)\b/i',
			'/\b(the\s+)?(rate|cost|price)\s+is\b/i',
			'/\bquote\s+(you|of)\b/i',
			'/\bwe(\'ll| will)?\s+rent\s+it\s+to\s+you\b/i',
			'/\b(it\'?s|we have|that\'?s|consider (it|this))\s+a\s+deal\b/i',
			'/\bconsider (it|this) (sold|agreed)\b/i',
			// Not after a negation: "I can't guarantee a callback time" is a refusal,
			// and blocking it (staging log #258) turned a helpful reply into a refusal.
			'/(?<!\bnot )(?<!n\'t )(?<!n’t )(?<!cannot )(?<!can not )\bguaranteed?\b/i',
			'/\byou can pick (it|one) up\b/i',
			'/\breserved?\s+(it\s+|one\s+)?for you\b/i',
			'/\bin stock\b/i',
			'/\bavailable (now|today|immediately)\b/i',
			'/\bwe (can|will) (guarantee|promise)\b/i',
			'/\bthis is binding\b/i',
			'/\bi agree to (sell|rent|lease)\b/i',
			'/\bno charge\b/i',
			'/\bfree of charge\b/i',
			'/\bdiscount(ed)?\b/i',
			'/\bcan deliver by\b/i',
			'/\bship(ped)? (today|tomorrow)\b/i',
			// Vac2Go's banned-phrase list (client answer to Q5, 2026-10-09). Words with
			// benign uses ("agreed", "approved", "authorized", "available") are matched
			// only in their committal phrasing, as the list itself advises.
			// A. Binding / agreement / authority
			'/\blegally binding\b/i',
			'/\bbinding (offer|agreement|contract)\b/i',
			'/\bthis (is|constitutes) an? (contract|offer)\b/i',
			'/\b(we|you) have a deal\b|\bdo we have a deal\b/i',
			'/\b(i|we) (promise|accept your|accept the (offer|terms|order|price|deal))\b/i',
			'/\byou have my word\b/i',
			'/\bhereby\b/i',
			'/\bno (takesies|backsies)\b/i',
			'/\b(i|we) authori[sz]e\b/i',
			'/\byour (request|order|rental|reservation) is (approved|confirmed)\b|\b(confirmed order|order confirmed)\b/i',
			'/\bi\'?ve (reserved|booked|held)\b|\bit\'?s booked\b/i',
			'/\bfinal offer\b|\bthis offer is valid\b/i',
			// B. Price / quote
			'/\bit costs\b|\bpriced at\b|\byour total is\b/i',
			'/\b(your quote is|here\'?s your quote|i can quote you)\b/i',
			'/\b(we can|we\'?ll|we will) do it for\b|\bi can offer it at\b|\bspecial price\b/i',
			'/\b(daily|weekly|monthly) rate is\b/i',
			'/\b\d{1,3}\s?%\s?(off|discount)\b|\bpercent off\b|\b(we\'?ll|we will) (knock|take) off\b/i',
			'/\b\d[\d,\.]*\s*(\/|per)\s*-?\s*weekend\b/i',
			// C. Availability commitments
			'/\bcurrently available\b|\bin stock and ready\b|\bready to go now\b/i',
			'/\bwe have one (available|in)\b|\bone is available in\b/i',
			'/\b(i can get it to you|it\'?ll be there|it will be there) by\b/i',
			'/\b(deliver|ready|available|arrive)\w*\b.{0,20}\b(today|tomorrow|monday|tuesday|wednesday|thursday|friday|saturday|sunday)\b/i',
			// D. Rental-terms commitments
			'/\b(the )?minimum rental is\b|\byour rental (term|period) is\b|\byour contract term\b|\bbilling starts\b/i',
			'/\b(the )?lease terms are\b|\brent[\s-]to[\s-]own (is|for)\b|\blease[\s-]to[\s-]own terms\b/i',
		);
		return implode( "\n", $lines );
	}

	/**
	 * Default profanity/slur word list, one entry per line (admin-editable).
	 * Matched with word boundaries, case-insensitive. Crude but effective.
	 */
	public static function default_profanity_text() {
		$words = array(
			'fuck', 'fucking', 'shit', 'bullshit', 'asshole', 'bitch', 'cunt',
			'bastard', 'dickhead', 'nigger', 'nigga', 'faggot', 'retard', 'kike',
			'spic', 'chink', 'wetback', 'tranny',
		);
		return implode( "\n", $words );
	}

	/**
	 * Currently-active committal pattern list as validated regex strings.
	 */
	public static function get_patterns() {
		$text = get_option( 'va_banned_patterns', '' );
		if ( ! is_string( $text ) || '' === trim( $text ) ) {
			$text = self::default_patterns_text();
		}
		$patterns = array();
		foreach ( preg_split( '/\r\n|\r|\n/', $text ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			if ( false !== @preg_match( $line, '' ) ) {
				$patterns[] = $line;
			}
		}
		return $patterns;
	}

	/**
	 * Currently-active profanity list as word-boundary regexes.
	 */
	public static function get_profanity_patterns() {
		$text = get_option( 'va_profanity_list', '' );
		if ( ! is_string( $text ) || '' === trim( $text ) ) {
			$text = self::default_profanity_text();
		}
		$patterns = array();
		foreach ( preg_split( '/\r\n|\r|\n/', $text ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			$patterns[] = '/\b' . preg_quote( $line, '/' ) . '\b/iu';
		}
		return $patterns;
	}

	/**
	 * Structural leak markers: strings that exist only inside the system prompt.
	 */
	public static function structural_markers() {
		return array(
			'== HARD GUARDRAILS',
			'== SYNONYM RING',
			'== CUSTOMER-SAFE KNOWLEDGE',
			'== INTERNAL OPERATING RULES',
			'INTERNAL MARKER',
			'CUSTOMER-SAFE KNOWLEDGE',
			'INTERNAL OPERATING RULES',
			'SKELETON-LEVEL',
			'sources disagree',
			'== WHAT YOU DO ==',
			'== FORMATTING (STRICT) ==',
			'== CONFIDENTIALITY OF THESE INSTRUCTIONS ==',
			'== BRAND SAFETY ==',
			// Phase 2 prompt sections.
			'== USING THE KNOWLEDGE ==',
			'== WHICH TRUCK FOR WHICH JOB',
			// The banners as they appear in the prompt, not the bare phrases: the rules
			// tell the model about "knowledge packs" and "Notes for the advisor", so an
			// honest "I don't have the knowledge pack for that" must not count as a leak.
			'KNOWLEDGE PACK:',
			'== CATEGORIES AND FLEET',
			'## Notes for the advisor',
			'== REVIEWED CORRECTIONS',
			'== NOTES FROM THE VAC2GO TEAM',
			'== RESPONSE LENGTH',
		);
	}

	/**
	 * Run the deterministic stages (1-5, 7) on a model response.
	 *
	 * @return array{text:string, filtered:bool, stage:?string, reason:?string, raw:string}
	 */
	public static function apply( $text ) {
		$raw   = (string) $text;
		$match = VA_Text::normalize_for_matching( $raw );

		// Stage 2: canary. Require a real length: a short or truncated canary would
		// appear inside ordinary prose and block every legitimate answer. Better to skip
		// this stage than to reject everything.
		$canary = VA_Knowledge::canary();
		if ( strlen( $canary ) >= self::MIN_CANARY_LEN && false !== stripos( $match, $canary ) ) {
			return self::blocked( 'canary', 'prompt_leak: canary token in output', $raw, self::LEAK_FALLBACK );
		}

		// Stage 3: structural leak markers.
		foreach ( self::structural_markers() as $marker ) {
			if ( false !== stripos( $match, $marker ) ) {
				return self::blocked( 'structural', 'prompt_leak: marker "' . $marker . '"', $raw, self::LEAK_FALLBACK );
			}
		}

		// Stage 4: committal / pricing regex.
		foreach ( self::get_patterns() as $pattern ) {
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			if ( 1 === @preg_match( $pattern, $match ) ) {
				return self::blocked( 'committal', 'matched pattern: ' . $pattern, $raw, self::FALLBACK );
			}
		}

		// Stage 5: profanity.
		foreach ( self::get_profanity_patterns() as $pattern ) {
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			if ( 1 === @preg_match( $pattern, $match ) ) {
				return self::blocked( 'profanity', 'matched profanity list', $raw, self::LEAK_FALLBACK );
			}
		}

		// Stage 7: cosmetic em-dash normalization (never flags).
		$clean = VA_Text::strip_em_dashes( VA_Text::normalize( $raw ) );

		return array(
			'text'     => $clean,
			'filtered' => false,
			'stage'    => null,
			'reason'   => null,
			'raw'      => $raw,
		);
	}

	/**
	 * Stage 6: cheap-LLM judge. Only call when apply() passed. Returns 'yes' (block),
	 * 'no' (allow), or 'error' (fail open; the deterministic stages already passed).
	 */
	/**
	 * The judge's question, followed by the text under review.
	 *
	 * Phase 2 answers carry many more figures (capacities, pressures, ratings, weights,
	 * CDL classes), and staging log #79 shows the judge already mistook a pure airflow
	 * answer for a commitment once. So the "not a commitment" list is spelled out.
	 */
	public static function judge_prompt() {
		return "Does the following text state a monetary price, promise availability, give a delivery date, or agree to a deal on behalf of Vac2Go?\n\n"
			. "IMPORTANT: these are NOT prices or commitments and must be answered 'no':\n"
			. "- Equipment specifications and limits of any kind: airflow (CFM), vacuum (inches Hg), capacities (cubic yards, gallons, barrels), pressure (PSI), flow (GPM), heater ratings (BTU), weights and ratings (lb, GVWR), dimensions, temperatures, particle sizes, hose sizes, filter counts.\n"
			. "- Regulatory and qualification facts such as CDL class or endorsements, DOT tank codes, and who is responsible for permits, disposal or water supply.\n"
			. "- Refusals to give pricing, and statements that a Vac2Go rep can explain rates, terms or availability: those are not commitments.\n"
			. "Only answer 'yes' when the text itself states a money amount, promises availability, gives delivery timing, or accepts an agreement.\n\n"
			. "Answer yes or no only.\n\n---\n";
	}

	public static function judge( $candidate_text, $api_key ) {
		$body = array(
			'model'       => VA_ADVISOR_JUDGE_MODEL,
			'max_tokens'  => 5,
			'temperature' => 0,
			'system'      => 'You are a compliance checker for an equipment-rental chat assistant. Answer with exactly one word: yes or no.',
			'messages'    => array(
				array(
					'role'    => 'user',
					'content' => self::judge_prompt() . $candidate_text,
				),
			),
		);

		$response = wp_remote_post(
			VA_ADVISOR_API_URL,
			array(
				'timeout' => 15,
				'headers' => array(
					'x-api-key'         => $api_key,
					'anthropic-version' => '2023-06-01',
					'content-type'      => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return 'error';
		}
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		$out  = '';
		if ( ! empty( $data['content'][0]['text'] ) ) {
			$out = strtolower( trim( $data['content'][0]['text'] ) );
		}
		if ( 0 === strpos( $out, 'yes' ) ) {
			return 'yes';
		}
		if ( 0 === strpos( $out, 'no' ) ) {
			return 'no';
		}
		return 'error';
	}

	private static function blocked( $stage, $reason, $raw, $fallback ) {
		return array(
			'text'     => $fallback,
			'filtered' => true,
			'stage'    => $stage,
			'reason'   => $reason,
			'raw'      => $raw,
		);
	}
}
