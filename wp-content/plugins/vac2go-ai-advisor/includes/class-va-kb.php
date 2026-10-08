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

	// ------------------------------------------------------------ categories --

	/** A category file (kb/categories/<id>.md), or null for an unknown id. */
	public static function category( $category_id ) {
		if ( ! isset( VA_Fleet::categories()[ $category_id ] ) ) {
			return null;
		}
		$path = self::dir() . '/categories/' . $category_id . '.md';
		if ( ! is_readable( $path ) ) {
			return null;
		}
		return str_replace( array( "\r\n", "\r" ), "\n", (string) file_get_contents( $path ) );
	}

	/** The category's "## Summary" paragraph. */
	public static function category_summary( $category_id ) {
		$text = (string) self::category( $category_id );
		return preg_match( "/## Summary\n\n(.*?)(\n\n|\z)/s", $text, $m ) ? trim( $m[1] ) : '';
	}

	// --------------------------------------------------------------- packs --

	/** Most knowledge packs one conversation carries at once. */
	const MAX_PACKS = 3;

	/**
	 * Knowledge packs for a conversation: the categories it names, in order of first
	 * mention, across every earlier question and answer plus the current message.
	 *
	 * Recomputed from the whole conversation each turn, so a pack stays once it has
	 * been mentioned and new packs are always appended at the end. That keeps the
	 * prompt prefix byte-stable from turn to turn, which is what makes it cacheable.
	 * The advisor's own answers count: when it recommends a category, the next turn
	 * carries that category's detail.
	 *
	 * @param string[] $history Earlier questions and answers, oldest first.
	 * @return string[] Category ids, at most MAX_PACKS (the earliest dropped first).
	 */
	public static function select_packs( array $history, $current ) {
		$order = array();
		foreach ( array_merge( $history, array( (string) $current ) ) as $text ) {
			foreach ( VA_Fleet::resolve( $text )['categories'] as $c ) {
				if ( ! in_array( $c, $order, true ) ) {
					$order[] = $c;
				}
			}
		}
		return array_slice( $order, -self::MAX_PACKS );
	}

	/** One category's full Q&A plus every unit card in it. Empty for an unknown id. */
	public static function pack( $category_id ) {
		$cat = self::category( $category_id );
		if ( null === $cat ) {
			return '';
		}
		$name  = VA_Fleet::categories()[ $category_id ]['name'];
		$cards = array();
		foreach ( VA_Fleet::units_in( $category_id ) as $uid ) {
			$card = self::card( $uid );
			if ( null !== $card ) {
				$cards[] = trim( $card );
			}
		}
		return "==================== KNOWLEDGE PACK: {$name} ====================\n\n"
			. trim( $cat ) . "\n\n"
			. "== {$name}: unit cards ==\n\n"
			. implode( "\n\n", $cards ) . "\n";
	}

	/** The text of several packs, in the order given. */
	public static function packs_text( array $category_ids ) {
		$out = array();
		foreach ( $category_ids as $c ) {
			$p = self::pack( $c );
			if ( '' !== $p ) {
				$out[] = $p;
			}
		}
		return implode( "\n", $out );
	}

	// ---------------------------------------------------------------- core --

	/**
	 * Knowledge sent on every turn: each category's summary, the words customers use
	 * for it, and Vac2Go's units in it; then the names that are not on the current
	 * fleet. Enough to recommend a category and route a question; the detail comes
	 * in the packs.
	 */
	public static function core() {
		$out = array( '== CATEGORIES AND FLEET (always available) ==' );
		foreach ( VA_Fleet::categories() as $cid => $c ) {
			$units = array();
			foreach ( VA_Fleet::units_in( $cid ) as $uid ) {
				$units[] = VA_Fleet::unit( $uid )['name'];
			}
			$out[] = "### {$c['name']}\n"
				. 'Customer words: ' . implode( ', ', $c['aliases'] ) . ".\n"
				. self::category_summary( $cid ) . "\n"
				. 'Vac2Go units: ' . implode( ', ', $units ) . '.';
		}
		$off = array();
		foreach ( VA_Fleet::off_list() as $o ) {
			$names = array();
			foreach ( $o['categories'] as $c ) {
				$names[] = VA_Fleet::categories()[ $c ]['name'];
			}
			$off[] = $o['name'] . ' (closest category: ' . implode( ' or ', $names ) . ')';
		}
		if ( $off ) {
			$out[] = "### Not on Vac2Go's current list\n" . implode( '; ', $off ) . '.';
		}
		return implode( "\n\n", $out ) . "\n";
	}
}
