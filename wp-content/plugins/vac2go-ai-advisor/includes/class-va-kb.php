<?php
/**
 * Knowledge base files under kb/: unit cards (kb/units/<unit-id>.md).
 *
 * Cards carry a {src: document p.N} tag on every figure so each number can be audited
 * against the manufacturer's literature. Those tags are for people, not the model, so
 * card() strips them.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VA_KB {

	/** Matches one source tag and the space before it. */
	const SRC_TAG = '/[ \t]*\{src:[^}]*\}/';

	private static function dir() {
		return dirname( __DIR__ ) . '/kb';
	}

	/**
	 * The card as written, source tags included. Null for anything that is not a
	 * fleet unit id, so an id can never be turned into an arbitrary file path.
	 */
	public static function card_raw( $unit_id ) {
		if ( null === VA_Fleet::unit( $unit_id ) ) {
			return null;
		}
		$path = self::dir() . '/units/' . $unit_id . '.md';
		if ( ! is_readable( $path ) ) {
			return null;
		}
		return str_replace( array( "\r\n", "\r" ), "\n", (string) file_get_contents( $path ) );
	}

	/** The card as the model sees it: source tags removed. */
	public static function card( $unit_id ) {
		$raw = self::card_raw( $unit_id );
		return null === $raw ? null : self::strip_sources( $raw );
	}

	public static function strip_sources( $text ) {
		return preg_replace( self::SRC_TAG, '', (string) $text );
	}
}
