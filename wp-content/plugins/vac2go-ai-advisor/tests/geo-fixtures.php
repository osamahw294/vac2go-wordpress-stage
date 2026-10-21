<?php
/**
 * Nearest-branch lookup fixtures, runnable WITHOUT WordPress.
 *
 *   php tests/geo-fixtures.php
 *
 * The cases are the change request's test table (2026-10-20, section 2.12), whose
 * expected branches were computed with haversine against the branch ZIP centroids,
 * plus the resolution rules of sections 2.5 to 2.8.
 */

// phpcs:disable

if ( PHP_SAPI !== 'cli' ) {
	header( 'HTTP/1.1 404 Not Found' );
	exit;
}

define( 'ABSPATH', __DIR__ );
require_once __DIR__ . '/../includes/class-va-geo.php';
require_once __DIR__ . '/../includes/class-va-tools.php';

$pass = 0;
$fail = 0;
function check( $label, $ok, $detail = '' ) {
	global $pass, $fail;
	if ( $ok ) {
		$pass++;
	} else {
		$fail++;
		echo "  FAIL  {$label}" . ( $detail ? "\n          {$detail}" : '' ) . "\n";
	}
}
function wp_json_encode_stub( $v ) {
	return json_encode( $v );
}
function branch_of( $location ) {
	$r = VA_Geo::nearest( $location );
	return 'found' === $r['status'] ? $r['branch']['id'] : $r['status'];
}

echo "\n== Change request test table (section 2.12) ==\n";
$table = array(
	'Nashville, TN'    => 'kentucky',
	'Tulsa, OK'        => 'tennessee',
	'74103'            => 'tennessee',
	'Cincinnati, OH'   => 'kentucky',
	'Atlanta, GA'      => 'georgia',
	'Philadelphia, PA' => 'new-jersey',
	'Richmond, VA'     => 'new-jersey',
	'Worcester, MA'    => 'new-jersey',
	'Chicago, IL'      => 'indiana',
	'Miami, FL'        => 'florida-fort-myers',
	'Denver, CO'       => 'utah',
	'Reno, NV'         => 'utah',
	'Houston, TX'      => 'texas',
	'Anchorage, AK'    => 'not_served',
	'Houston'          => 'texas',
	'southwest Ohio'   => 'unresolved',
);
foreach ( $table as $in => $want ) {
	$got = branch_of( $in );
	check( "\"{$in}\" -> {$want}", $want === $got, "got {$got}" );
}

echo "\n== Distances match the ticket (internal only) ==\n";
$r = VA_Geo::nearest( 'Nashville, TN' );
check( 'Nashville to Kentucky is about 155 miles', abs( $r['distance_miles'] - 155 ) <= 3, (string) $r['distance_miles'] );
check( 'Nashville to Tennessee is about 197 miles', abs( VA_Geo::distance_to( 'Nashville, TN', 'tennessee' ) - 197 ) <= 3, (string) VA_Geo::distance_to( 'Nashville, TN', 'tennessee' ) );
check( 'Philadelphia to New Jersey is about 7 miles (11 from the Census place centre)', abs( VA_Geo::distance_to( 'Philadelphia, PA', 'new-jersey' ) - 7 ) <= 5, (string) VA_Geo::distance_to( 'Philadelphia, PA', 'new-jersey' ) );

echo "\n== Input forms (section 2.5) ==\n";
foreach ( array(
	'Nashville, Tennessee'   => 'kentucky',
	'nashville tn'           => 'kentucky',
	'Saint Louis, MO'        => 'indiana', // 246 mi vs Tennessee 249
	'St. Louis'              => 'indiana',
	'Ft. Myers, FL'          => 'florida-fort-myers',
	'near Tulsa'             => 'tennessee',
	'I am in 30303'          => 'georgia',
	'DFW'                    => 'texas',
	'the Bay Area'           => 'utah', // 626 mi vs Arizona 683
	'Chicagoland'            => 'indiana',
	'upstate New York'       => 'new-jersey', // Syracuse: 226 mi vs Ohio 302
	'Washington DC'          => 'new-jersey',
	'D.C.'                   => 'new-jersey',
	'Georgia'                => 'georgia',
	'Ohio'                   => 'ohio',
	'Colorado'               => 'utah',
	'Honolulu'               => 'not_served',
	'Hawaii'                 => 'not_served',
	'99501'                  => 'not_served',
	'San Juan, PR'           => 'not_served',
	'asdfgh'                 => 'unresolved',
	''                       => 'unresolved',
	'Springfield'            => 'tennessee', // Springfield, MO is the most populous
	'Memphis'                => 'tennessee',
	'Jacksonville'           => 'florida-orlando', // Jacksonville, FL: 107 mi
) as $in => $want ) {
	$got = branch_of( $in );
	check( "\"{$in}\" -> {$want}", $want === $got, "got {$got}" );
}

echo "\n== Bare Florida has two branches ==\n";
$fl = VA_Geo::nearest( 'Florida' );
check( 'bare "Florida" asks which part of Florida', 'multiple' === $fl['status'] && 2 === count( $fl['branches'] ), json_encode( $fl ) );

echo "\n== Result shape (section 2.9) ==\n";
$r = VA_Geo::nearest( 'Houston, TX' );
check( 'a found result names the branch and its phone', ! empty( $r['branch']['name'] ) && ! empty( $r['branch']['phone'] ) );
$public = VA_Geo::for_model( $r );
check( 'what the model sees carries no distance value', ! array_key_exists( 'distance_miles', $public ) && ! preg_match( '/\\d+\\s*(mi|miles)\\b/', json_encode( $public ) ), json_encode( $public ) );
check( 'what the model sees for Alaska gives the national line', false !== strpos( json_encode( VA_Geo::for_model( VA_Geo::nearest( 'Anchorage, AK' ) ) ), '855-822-7246' ) );
check( 'what the model sees for unresolved says to ask', false !== stripos( json_encode( VA_Geo::for_model( VA_Geo::nearest( 'southwest Ohio' ) ) ), 'ask' ) );

echo "\n== No network (section 2.11) ==\n";
$src = (string) file_get_contents( __DIR__ . '/../includes/class-va-geo.php' );
check( 'the lookup makes no HTTP request', ! preg_match( '/wp_remote_|curl_|file_get_contents\(\s*[\'"]https?:|fsockopen/', $src ) );

echo "\n== The tool the model calls ==\n";
$defs = VA_Tools::definitions();
check( 'one tool is offered: find_nearest_branch', 1 === count( $defs ) && 'find_nearest_branch' === $defs[0]['name'] );
check( 'it takes the location as written', array( 'location' ) === $defs[0]['input_schema']['required'] );
check( 'definitions are byte-stable (prompt cache)', wp_json_encode_stub( VA_Tools::definitions() ) === wp_json_encode_stub( VA_Tools::definitions() ) );
$run = VA_Tools::run( 'find_nearest_branch', array( 'location' => 'Nashville, TN' ) );
check( 'the result names the Kentucky branch and its phone', false !== strpos( $run['content'], 'Kentucky' ) && false !== strpos( $run['content'], '(502) 699-4029' ), $run['content'] );
check( 'the result carries no distance', false === strpos( $run['content'], 'distance_miles' ) );
check( 'the lookup is logged: query and branch', 'Nashville, TN' === $run['log']['location_query'] && 'kentucky' === $run['log']['location_result'] );
$run = VA_Tools::run( 'find_nearest_branch', array( 'location' => 'southwest Ohio' ) );
check( 'an unresolved lookup is logged as unresolved', 'unresolved' === $run['log']['location_result'] );
$run = VA_Tools::run( 'find_nearest_branch', array( 'location' => 'Anchorage, AK' ) );
check( 'an Alaska lookup is logged as not served', 'not_served' === $run['log']['location_result'] );
$run = VA_Tools::run( 'no_such_tool', array() );
check( 'an unknown tool returns an error result', ! empty( $run['is_error'] ) );

echo "\n{$pass} passed, {$fail} failed\n";
exit( $fail ? 1 : 0 );
