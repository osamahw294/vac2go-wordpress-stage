<?php
/**
 * The canonical fleet (kb/fleet.json) and name resolution.
 *
 * resolve() finds which fleet units and categories a piece of text mentions, so the
 * advisor can attach the right knowledge pack. It is deterministic and costs no model
 * call: names are matched on word boundaries after normalisation, so "HV-57",
 * "hv 57" and "hv57" are the same name, and "Bergey's Water Truck" (an old supplier
 * name) lands on the Water Trucks unit.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VA_Fleet {

	/** Parsed fleet.json, loaded once per request. */
	private static $data = null;

	/** Compiled alias patterns: list of array( regex, type, id ). */
	private static $patterns = null;

	private static function data() {
		if ( null === self::$data ) {
			$json       = file_get_contents( dirname( __DIR__ ) . '/kb/fleet.json' );
			self::$data = json_decode( (string) $json, true );
			if ( ! is_array( self::$data ) ) {
				self::$data = array( 'categories' => array(), 'units' => array(), 'off_list' => array() );
			}
		}
		return self::$data;
	}

	/** @return array<string,array> unit id => unit. */
	public static function units() {
		return self::data()['units'];
	}

	/** @return array<string,array> category id => category. */
	public static function categories() {
		return self::data()['categories'];
	}

	/** Names that are not on vac2go.com, each with its closest categories. */
	public static function off_list() {
		return self::data()['off_list'];
	}

	public static function unit( $id ) {
		$units = self::units();
		return isset( $units[ $id ] ) ? $units[ $id ] : null;
	}

	/** Unit ids in a category, including units listed there through 'also_in'. */
	public static function units_in( $category ) {
		$out = array();
		foreach ( self::units() as $id => $u ) {
			if ( $u['category'] === $category || in_array( $category, $u['also_in'] ?? array(), true ) ) {
				$out[] = $id;
			}
		}
		return $out;
	}

	/**
	 * Lowercase; drop apostrophes ("Bergey's" → "bergeys"); everything else that is not
	 * a letter or digit becomes a space; split letter/digit runs ("hv57" → "hv 57") so
	 * hyphenated, spaced and run-together forms all compare equal.
	 */
	public static function normalize( $text ) {
		$t = mb_strtolower( (string) $text );
		$t = str_replace( array( "'", '’', '‘' ), '', $t );
		$t = preg_replace( '/[^a-z0-9]+/u', ' ', $t );
		$t = preg_replace( '/(?<=[a-z])(?=[0-9])|(?<=[0-9])(?=[a-z])/', ' ', $t );
		return trim( preg_replace( '/\s+/', ' ', $t ) );
	}

	private static function patterns() {
		if ( null !== self::$patterns ) {
			return self::$patterns;
		}
		$p    = array();
		$data = self::data();
		$add  = function ( $alias, $type, $id ) use ( &$p ) {
			$a = self::normalize( $alias );
			if ( '' !== $a ) {
				// Whole words only, with an optional plural "s".
				$p[] = array( '/(?<![a-z0-9])' . preg_quote( $a, '/' ) . 's?(?![a-z0-9])/', $type, $id );
			}
		};
		foreach ( $data['units'] as $id => $u ) {
			$add( $u['name'], 'unit', $id );
			foreach ( $u['aliases'] as $a ) {
				$add( $a, 'unit', $id );
			}
		}
		// Categories match their listed phrases only, never their bare display name:
		// "Water" alone would fire on "water lines", "Combination" on any combination.
		foreach ( $data['categories'] as $id => $c ) {
			foreach ( $c['aliases'] as $a ) {
				$add( $a, 'category', $id );
			}
		}
		foreach ( $data['off_list'] as $i => $o ) {
			$add( $o['name'], 'off_list', $i );
			foreach ( $o['aliases'] as $a ) {
				$add( $a, 'off_list', $i );
			}
		}
		self::$patterns = $p;
		return $p;
	}

	/**
	 * Units, categories and off-list names mentioned in $text.
	 *
	 * A matched unit also contributes its category (and any 'also_in' categories).
	 * Results are in fleet.json order, so the same text always resolves the same way.
	 *
	 * @return array{units:string[], categories:string[], off_list:array<int,array>}
	 */
	public static function resolve( $text ) {
		$norm  = self::normalize( $text );
		$units = array();
		$cats  = array();
		$off   = array();
		$data  = self::data();

		foreach ( self::patterns() as $pat ) {
			list( $regex, $type, $id ) = $pat;
			if ( ! preg_match( $regex, $norm ) ) {
				continue;
			}
			if ( 'unit' === $type ) {
				$units[ $id ] = true;
				$u            = $data['units'][ $id ];
				$cats[ $u['category'] ] = true;
				foreach ( $u['also_in'] ?? array() as $c ) {
					$cats[ $c ] = true;
				}
			} elseif ( 'category' === $type ) {
				$cats[ $id ] = true;
			} else {
				$off[ $id ] = true;
				foreach ( $data['off_list'][ $id ]['categories'] as $c ) {
					$cats[ $c ] = true;
				}
			}
		}

		return array(
			'units'      => array_values( array_filter( array_keys( $data['units'] ), function ( $id ) use ( $units ) {
				return isset( $units[ $id ] );
			} ) ),
			'categories' => array_values( array_filter( array_keys( $data['categories'] ), function ( $id ) use ( $cats ) {
				return isset( $cats[ $id ] );
			} ) ),
			'off_list'   => array_values( array_intersect_key( $data['off_list'], $off ) ),
		);
	}
}
