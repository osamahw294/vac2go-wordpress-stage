<?php
/**
 * Text normalization helpers. Applied to incoming customer text before logging and
 * before it reaches the model, so obfuscated input (zero-width characters, Unicode
 * confusables via NFKC) is visible in the review queue exactly as processed.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VA_Text {

	/**
	 * Normalize a string: Unicode NFKC (when intl is present), strip zero-width
	 * characters (U+200B, U+200C, U+200D, U+FEFF), normalize line endings.
	 */
	public static function normalize( $text ) {
		$text = (string) $text;

		if ( class_exists( 'Normalizer' ) ) {
			$n = Normalizer::normalize( $text, Normalizer::FORM_KC );
			if ( false !== $n && null !== $n ) {
				$text = $n;
			}
		}

		// Zero-width space / non-joiner / joiner / BOM.
		$text = str_replace( array( "\xE2\x80\x8B", "\xE2\x80\x8C", "\xE2\x80\x8D", "\xEF\xBB\xBF" ), '', $text );

		// Normalize line endings.
		$text = str_replace( array( "\r\n", "\r" ), "\n", $text );

		return $text;
	}

	/**
	 * Normalize plus collapse runs of spaces/tabs (used by the filter pipeline before
	 * pattern matching so "in   stock" cannot dodge "\bin stock\b").
	 */
	public static function normalize_for_matching( $text ) {
		$text = self::normalize( $text );
		$text = preg_replace( '/[ \t]{2,}/', ' ', $text );
		return $text;
	}

	/**
	 * Replace em dashes with context-appropriate punctuation. Cosmetic only; never
	 * flags or replaces the message. Guarantees the no-em-dash rule regardless of
	 * model compliance.
	 */
	public static function strip_em_dashes( $text ) {
		if ( false === strpos( $text, "\xE2\x80\x94" ) ) {
			return $text;
		}
		// " — " between clauses reads best as ", ".
		$text = str_replace( array( ' — ', ' —', '— ' ), array( ', ', ',', ', ' ), $text );
		// Any survivors (unspaced) become a simple dash.
		$text = str_replace( '—', ' - ', $text );
		return $text;
	}
}
