<?php
/**
 * Nearest-branch lookup (change request of 2026-10-20, Change 1).
 *
 * Resolves a location as a visitor wrote it (ZIP code, "City, ST", bare city, bare
 * state, or a metro nickname) to coordinates from bundled US Census data, then picks
 * the closest of Vac2Go's branches by straight-line (haversine) distance. Entirely
 * offline: no HTTP request, no key, no rate limit.
 *
 * The model never does the geography: it calls the find_nearest_branch tool with the
 * visitor's words and writes the answer around what this returns.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VA_Geo {

	const EARTH_RADIUS_MILES = 3958.8;

	/** Places no branch serves: Alaska, Hawaii (section 2.8) and US territories. */
	const NOT_SERVED = array(
		'AK' => 'Alaska',
		'HI' => 'Hawaii',
		'PR' => 'Puerto Rico',
	);

	/**
	 * ZIP prefixes that belong to places no branch serves (the ZIP file carries no
	 * state): Alaska 995-999, Hawaii 967-968, Puerto Rico 006-009 (incl. the Virgin
	 * Islands), Guam and the Pacific territories 969.
	 */
	const NOT_SERVED_ZIP = '/^(99[5-9]|96[789]|00[6-9])/';

	/**
	 * Metro nicknames and regional shorthand (section 2.5), each to a representative
	 * city. Regions too broad to place on one city ("southwest Ohio", "the
	 * panhandle") are deliberately absent: the advisor asks instead of guessing.
	 */
	const ALIASES = array(
		'dfw'                 => 'Dallas, TX',
		'the metroplex'       => 'Dallas, TX',
		'metroplex'           => 'Dallas, TX',
		'h town'              => 'Houston, TX',
		'htown'               => 'Houston, TX',
		'satx'                => 'San Antonio, TX',
		'rio grande valley'   => 'McAllen, TX',
		'rgv'                 => 'McAllen, TX',
		'permian basin'       => 'Midland, TX',
		'golden triangle'     => 'Beaumont, TX',
		'the bay area'        => 'San Francisco, CA',
		'bay area'            => 'San Francisco, CA',
		'silicon valley'      => 'San Jose, CA',
		'socal'               => 'Los Angeles, CA',
		'southern california' => 'Los Angeles, CA',
		'la'                  => 'Los Angeles, CA',
		'inland empire'       => 'Riverside, CA',
		'central valley'      => 'Fresno, CA',
		'vegas'               => 'Las Vegas, NV',
		'valley of the sun'   => 'Phoenix, AZ',
		'front range'         => 'Denver, CO',
		'wasatch front'       => 'Salt Lake City, UT',
		'slc'                 => 'Salt Lake City, UT',
		'treasure valley'     => 'Boise, ID',
		'puget sound'         => 'Seattle, WA',
		'chicagoland'         => 'Chicago, IL',
		'chi town'            => 'Chicago, IL',
		'chitown'             => 'Chicago, IL',
		'the twin cities'     => 'Minneapolis, MN',
		'twin cities'         => 'Minneapolis, MN',
		'quad cities'         => 'Davenport, IA',
		'metro detroit'       => 'Detroit, MI',
		'motor city'          => 'Detroit, MI',
		'indy'                => 'Indianapolis, IN',
		'cincy'               => 'Cincinnati, OH',
		'stl'                 => 'St. Louis, MO',
		'kc'                  => 'Kansas City, MO',
		'okc'                 => 'Oklahoma City, OK',
		'nola'                => 'New Orleans, LA',
		'the big easy'        => 'New Orleans, LA',
		'music city'          => 'Nashville, TN',
		'atl'                 => 'Atlanta, GA',
		'the triangle'        => 'Raleigh, NC',
		'research triangle'   => 'Raleigh, NC',
		'the triad'           => 'Greensboro, NC',
		'piedmont triad'      => 'Greensboro, NC',
		'lowcountry'          => 'Charleston, SC',
		'hampton roads'       => 'Norfolk, VA',
		'tidewater'           => 'Norfolk, VA',
		'nova'                => 'Arlington, VA',
		'northern virginia'   => 'Arlington, VA',
		'dmv'                 => 'Washington, DC',
		'dc'                  => 'Washington, DC',
		'washington dc'       => 'Washington, DC',
		'washington d c'      => 'Washington, DC',
		'district of columbia' => 'Washington, DC',
		'delmarva'            => 'Salisbury, MD',
		'philly'              => 'Philadelphia, PA',
		'lehigh valley'       => 'Allentown, PA',
		'south jersey'        => 'Camden, NJ',
		'north jersey'        => 'Newark, NJ',
		'jersey shore'        => 'Toms River, NJ',
		'nyc'                 => 'New York, NY',
		'the big apple'       => 'New York, NY',
		'long island'         => 'Hempstead, NY',
		'hudson valley'       => 'Poughkeepsie, NY',
		'capital region'      => 'Albany, NY',
		'upstate new york'    => 'Syracuse, NY',
		'upstate ny'          => 'Syracuse, NY',
		'western new york'    => 'Buffalo, NY',
		'central florida'     => 'Orlando, FL',
		'south florida'       => 'Miami, FL',
		'tampa bay'           => 'Tampa, FL',
		'space coast'         => 'Melbourne, FL',
		'emerald coast'       => 'Destin, FL',
		// Census names that differ from everyday use.
		'honolulu'            => 'Urban Honolulu, HI',
	);

	/** Words around a place that are not part of it: "near Tulsa", "the Tulsa area". */
	const NOISE = '/\b(i am|im|i m|we are|were|located|based|in|near|around|outside|outside of|close to|by|the city of|city of|area|metro|region|zip|zip code|code)\b/';

	private static $branches = null;
	private static $main     = '855-822-7246';
	private static $states   = null;

	// ------------------------------------------------------------- branches --

	/** Branches from kb/branches.json, each with lat/lon from its ZIP centroid. */
	public static function branches() {
		if ( null !== self::$branches ) {
			return self::$branches;
		}
		$data           = json_decode( (string) file_get_contents( self::dir() . '/branches.json' ), true );
		self::$main     = (string) $data['main_number'];
		self::$branches = array();
		foreach ( $data['branches'] as $b ) {
			$z = self::zip( $b['zip'] );
			if ( null !== $z ) {
				$b['lat']         = $z[0];
				$b['lon']         = $z[1];
				self::$branches[] = $b;
			}
		}
		return self::$branches;
	}

	public static function main_number() {
		self::branches();
		return self::$main;
	}

	// --------------------------------------------------------------- lookup --

	/**
	 * The nearest branch to a location as written by a visitor.
	 *
	 * @return array status 'found' (branch, resolved_as, distance_miles), 'multiple'
	 *               (branches: a bare state with two branches), 'not_served'
	 *               (place: Alaska, Hawaii, territories), or 'unresolved'.
	 */
	public static function nearest( $location ) {
		$place = self::resolve( $location );
		if ( null === $place ) {
			return array( 'status' => 'unresolved', 'query' => (string) $location );
		}
		if ( isset( self::NOT_SERVED[ $place['state'] ] ) ) {
			return array( 'status' => 'not_served', 'place' => self::NOT_SERVED[ $place['state'] ], 'query' => (string) $location );
		}
		if ( ! empty( $place['bare_state'] ) ) {
			$in_state = array_values( array_filter( self::branches(), function ( $b ) use ( $place ) {
				return $b['state'] === $place['state'];
			} ) );
			if ( count( $in_state ) > 1 ) {
				return array( 'status' => 'multiple', 'resolved_as' => $place['label'], 'branches' => $in_state, 'query' => (string) $location );
			}
			if ( 1 === count( $in_state ) ) {
				return array( 'status' => 'found', 'resolved_as' => $place['label'], 'branch' => $in_state[0], 'distance_miles' => 0, 'query' => (string) $location );
			}
		}
		$best = null;
		$dist = INF;
		foreach ( self::branches() as $b ) {
			$d = self::haversine( $place['lat'], $place['lon'], $b['lat'], $b['lon'] );
			if ( $d < $dist ) {
				$dist = $d;
				$best = $b;
			}
		}
		return array( 'status' => 'found', 'resolved_as' => $place['label'], 'branch' => $best, 'distance_miles' => (int) round( $dist ), 'query' => (string) $location );
	}

	/** Miles from a location to one branch (tests and diagnostics). */
	public static function distance_to( $location, $branch_id ) {
		$place = self::resolve( $location );
		foreach ( self::branches() as $b ) {
			if ( $b['id'] === $branch_id && null !== $place ) {
				return (int) round( self::haversine( $place['lat'], $place['lon'], $b['lat'], $b['lon'] ) );
			}
		}
		return null;
	}

	/**
	 * What the model receives: the branch name, phone and address, never a distance
	 * (section 2.9), with an instruction for each outcome.
	 */
	public static function for_model( array $r ) {
		$pick = function ( $b ) {
			return array( 'name' => $b['name'], 'phone' => $b['phone'], 'address' => $b['address'] );
		};
		switch ( $r['status'] ) {
			case 'found':
				return array(
					'status'      => 'found',
					'location'    => $r['resolved_as'],
					'branch'      => $pick( $r['branch'] ),
					'instruction' => 'Name this branch as the closest one and give its phone number. Do not mention distance or drive time.',
				);
			case 'multiple':
				return array(
					'status'      => 'multiple',
					'location'    => $r['resolved_as'],
					'branches'    => array_map( $pick, $r['branches'] ),
					'instruction' => 'This state has more than one branch. Ask which city or ZIP code the customer is in, or give both branches with their phone numbers.',
				);
			case 'not_served':
				return array(
					'status'      => 'not_served',
					'location'    => $r['place'],
					'main_number' => '1-' . self::main_number(),
					'instruction' => "Say plainly that Vac2Go doesn't currently have a branch serving " . $r['place'] . ', and give the national line so the team can talk through the options. Name no branch.',
				);
			default:
				return array(
					'status'      => 'unresolved',
					'instruction' => 'The location could not be identified. Do not guess a branch: ask which city and state, or which ZIP code, the customer is in.',
				);
		}
	}

	// ----------------------------------------------------------- resolution --

	/**
	 * Coordinates for a location, in the order of section 2.5: ZIP, "City, ST", bare
	 * state, alias, bare city (most populous match). Null when nothing matches.
	 *
	 * @return array{lat:float,lon:float,state:string,label:string,bare_state?:bool}|null
	 */
	public static function resolve( $location, $use_aliases = true ) {
		$raw = trim( (string) $location );
		if ( '' === $raw ) {
			return null;
		}

		// 1. A 5-digit ZIP code anywhere in the text.
		if ( preg_match( '/\b(\d{5})(?:-\d{4})?\b/', $raw, $m ) ) {
			if ( preg_match( self::NOT_SERVED_ZIP, $m[1] ) ) {
				$st = preg_match( '/^99[5-9]/', $m[1] ) ? 'AK' : ( preg_match( '/^96[78]/', $m[1] ) ? 'HI' : 'PR' );
				return array( 'lat' => 0.0, 'lon' => 0.0, 'state' => $st, 'label' => $m[1] );
			}
			$z = self::zip( $m[1] ) ?? self::zip_near( $m[1] );
			if ( null !== $z ) {
				return array( 'lat' => $z[0], 'lon' => $z[1], 'state' => '', 'label' => $m[1] );
			}
		}

		$text = self::key( $raw );

		// 2. Alias table (before city matching, so "LA" is not Louisiana's "la").
		$bare = trim( preg_replace( '/\s+/', ' ', preg_replace( self::NOISE, ' ', $text ) ) );
		// Alias targets are plain "City, ST" and resolve without another alias lookup.
		foreach ( $use_aliases ? array( $text, $bare ) : array() as $t ) {
			if ( isset( self::ALIASES[ $t ] ) ) {
				return self::resolve( self::ALIASES[ $t ], false );
			}
		}

		// 3. "City, ST" / "City, State" / "City ST": a state at the end.
		// State names run to three words ("district of columbia"); try each split.
		$states = self::states();
		$words  = explode( ' ', $bare );
		for ( $n = 1; $n <= 3 && $n < count( $words ); $n++ ) {
			$st = self::state_code( implode( ' ', array_slice( $words, -$n ) ) );
			if ( null === $st ) {
				continue;
			}
			$p = self::place( implode( ' ', array_slice( $words, 0, -$n ) ), $st );
			if ( null !== $p ) {
				return $p;
			}
		}

		// 4. A bare state ("Georgia", "GA"): the in-state branch if there is one,
		// otherwise the population-weighted centre of the state.
		$st = self::state_code( $bare );
		if ( null !== $st && isset( $states[ $st ] ) ) {
			return array( 'lat' => $states[ $st ]['lat'], 'lon' => $states[ $st ]['lon'], 'state' => $st, 'label' => $states[ $st ]['name'], 'bare_state' => true );
		}
		if ( null !== $st && isset( self::NOT_SERVED[ $st ] ) ) {
			return array( 'lat' => 0.0, 'lon' => 0.0, 'state' => $st, 'label' => self::NOT_SERVED[ $st ] );
		}

		// 5. A bare city name: the most populous match (section 2.6).
		return self::place( $bare, null );
	}

	/** The normalised form names are matched on (same as build_geo.py). */
	public static function key( $name ) {
		$k = strtolower( (string) $name );
		$k = preg_replace( "/[.'’]/u", '', $k );
		$k = trim( preg_replace( '/[^a-z0-9]+/', ' ', $k ) );
		$k = preg_replace( '/^saint /', 'st ', $k );
		$k = preg_replace( '/ saint /', ' st ', $k );
		$k = preg_replace( '/^sainte /', 'ste ', $k );
		$k = preg_replace( '/^ft /', 'fort ', $k );
		$k = preg_replace( '/^mt /', 'mount ', $k );
		return $k;
	}

	// ------------------------------------------------------------- data files --

	private static function dir() {
		return dirname( __DIR__ ) . '/kb';
	}

	/** Lat/lon of a ZIP code area, or null. */
	private static function zip( $zip ) {
		$fh = fopen( self::dir() . '/geo/zcta.tsv', 'r' );
		if ( ! $fh ) {
			return null;
		}
		$out = null;
		while ( false !== ( $line = fgets( $fh ) ) ) {
			if ( 0 === strncmp( $line, $zip . "\t", 6 ) ) {
				$c   = explode( "\t", rtrim( $line ) );
				$out = array( (float) $c[1], (float) $c[2] );
				break;
			}
		}
		fclose( $fh );
		return $out;
	}

	/**
	 * A ZIP with no ZIP code area of its own (a PO box or single-building ZIP): the
	 * numerically nearest area with the same first three digits, which is the same
	 * postal district.
	 */
	private static function zip_near( $zip ) {
		$fh = fopen( self::dir() . '/geo/zcta.tsv', 'r' );
		if ( ! $fh ) {
			return null;
		}
		$best = null;
		$gap  = PHP_INT_MAX;
		while ( false !== ( $line = fgets( $fh ) ) ) {
			if ( 0 === strncmp( $line, substr( $zip, 0, 3 ), 3 ) ) {
				$c = explode( "\t", rtrim( $line ) );
				$g = abs( (int) $c[0] - (int) $zip );
				if ( $g < $gap ) {
					$gap  = $g;
					$best = array( (float) $c[1], (float) $c[2] );
				}
			}
		}
		fclose( $fh );
		return $best;
	}

	/** The most populous place with this key (in this state, when given). */
	private static function place( $key, $state ) {
		if ( '' === $key ) {
			return null;
		}
		$fh = fopen( self::dir() . '/geo/places.tsv', 'r' );
		if ( ! $fh ) {
			return null;
		}
		$best  = null;
		$score = array( -1, -1 );
		$want  = $key . "\t";
		$len   = strlen( $want );
		while ( false !== ( $line = fgets( $fh ) ) ) {
			if ( 0 !== strncmp( $line, $want, $len ) ) {
				continue;
			}
			$c = explode( "\t", rtrim( $line ) );
			if ( null !== $state && $c[1] !== $state ) {
				continue;
			}
			// Population first; land area only between places with no estimate (CDPs).
			$s = array( (int) $c[4], (int) $c[5] );
			if ( $s > $score ) {
				$score = $s;
				$best  = $c;
			}
		}
		fclose( $fh );
		if ( null === $best ) {
			return null;
		}
		return array( 'lat' => (float) $best[2], 'lon' => (float) $best[3], 'state' => $best[1], 'label' => self::title( $key ) . ', ' . $best[1] );
	}

	/** States: code => (name, lat, lon), from kb/geo/states.tsv. */
	private static function states() {
		if ( null !== self::$states ) {
			return self::$states;
		}
		self::$states = array();
		foreach ( file( self::dir() . '/geo/states.tsv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) as $line ) {
			$c                     = explode( "\t", $line );
			self::$states[ $c[0] ] = array( 'name' => $c[1], 'lat' => (float) $c[2], 'lon' => (float) $c[3] );
		}
		return self::$states;
	}

	/** "ga", "georgia" -> "GA"; null when not a state. */
	private static function state_code( $text ) {
		$t = trim( (string) $text );
		if ( 2 === strlen( $t ) ) {
			$code = strtoupper( $t );
			return ( isset( self::states()[ $code ] ) || isset( self::NOT_SERVED[ $code ] ) ) ? $code : null;
		}
		foreach ( self::states() as $code => $s ) {
			if ( self::key( $s['name'] ) === $t ) {
				return $code;
			}
		}
		foreach ( self::NOT_SERVED as $code => $name ) {
			if ( self::key( $name ) === $t ) {
				return $code;
			}
		}
		return null;
	}

	private static function title( $key ) {
		return ucwords( $key );
	}

	private static function haversine( $lat1, $lon1, $lat2, $lon2 ) {
		$p1 = deg2rad( $lat1 );
		$p2 = deg2rad( $lat2 );
		$dp = deg2rad( $lat2 - $lat1 );
		$dl = deg2rad( $lon2 - $lon1 );
		$a  = sin( $dp / 2 ) ** 2 + cos( $p1 ) * cos( $p2 ) * sin( $dl / 2 ) ** 2;
		return 2 * self::EARTH_RADIUS_MILES * asin( min( 1.0, sqrt( $a ) ) );
	}
}
