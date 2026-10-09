<?php
/**
 * Fleet and alias fixtures, runnable WITHOUT WordPress.
 *
 *   php tests/fleet-fixtures.php
 *
 * The fleet is the 41 units on vac2go.com/vac-truck-rentals (checked 2026-10-08).
 * Every name a customer or an older document might use must resolve: the website
 * names, the Phase 1 prompt's names (which mostly named suppliers: CTOS, Bergey's,
 * Dragon, ITI, Benlee, "Keith Huber"), and names visitors actually typed in Phase 1.
 */

// phpcs:disable

if ( PHP_SAPI !== 'cli' ) {
	header( 'HTTP/1.1 404 Not Found' );
	exit;
}

define( 'ABSPATH', __DIR__ );
require_once __DIR__ . '/../includes/class-va-fleet.php';

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
function units_of( $text ) {
	return VA_Fleet::resolve( $text )['units'];
}
function cats_of( $text ) {
	return VA_Fleet::resolve( $text )['categories'];
}

// ---------------------------------------------------------------------------
echo "\n== The fleet itself ==\n";
$units = VA_Fleet::units();
// 41 website units plus 5 distinct models from Vac2Go's equipment catalog (client answer
// to Q7, 2026-10-09): GapVax MC1312, Guzzcavator, Vac Jet Rodding, CTOS 70-BBL Liquid
// Vacuum, Huber Dominator SS.
check( 'exactly 46 units', 46 === count( $units ), 'got ' . count( $units ) );
check( 'exactly 10 categories', 10 === count( VA_Fleet::categories() ) );
foreach ( $units as $id => $u ) {
	check( "{$id} has a known category", isset( VA_Fleet::categories()[ $u['category'] ] ), $u['category'] );
}
$per_group = array();
foreach ( $units as $u ) {
	$per_group[ $u['website_group'] ] = ( $per_group[ $u['website_group'] ] ?? 0 ) + 1;
}
$want = array( 'Industrial Vacuum' => 10, 'Hydro Excavators' => 9, 'Combination' => 5, 'Liquid Vacuum' => 5, 'Liquid Ring' => 3, 'Pull-Behind' => 6, 'Additional' => 3, 'Equipment catalog' => 5 );
check( 'website group counts match vac2go.com (10/9/5/5/3/6/3), plus 5 catalog-only units', $per_group == $want, json_encode( $per_group ) );

// ---------------------------------------------------------------------------
echo "\n== Every website name resolves to its own unit ==\n";
$website = array(
	'GapVax HV-57' => 'gapvax-hv-57', 'Guzzler Classic' => 'guzzler-classic', 'Guzzler Dense Phase' => 'guzzler-dense-phase',
	'Guzzler High-Rail' => 'guzzler-high-rail', 'Guzzler XCR' => 'guzzler-xcr', 'Huber AM30 HD' => 'huber-am30-hd',
	'Huber AM36' => 'huber-am36', 'PresVac PowerVac' => 'presvac-powervac', 'Super Products High Dump' => 'super-products-high-dump',
	'Super Products Supersucker' => 'super-products-supersucker', 'GapVax HV-33' => 'gapvax-hv-33', 'GapVax HV-56' => 'gapvax-hv-56',
	'Huber Baron HX' => 'huber-baron-hx', 'Kaiser Premier CV Series' => 'kaiser-premier-cv-series', 'Schellvac SVHX' => 'schellvac-svhx',
	'Super Products Mud Dog 1200' => 'super-products-mud-dog-1200', 'Tornado F4' => 'tornado-f4', 'Truvac HXX' => 'truvac-hxx',
	'Vactor Paradigm' => 'vactor-paradigm', 'GapVax MC1510' => 'gapvax-mc1510', 'Huber SC 1009' => 'huber-sc-1009',
	'Huber SC 1512' => 'huber-sc-1512', 'Super Products Camel Max Series' => 'super-products-camel-max-series',
	'Vactor 2100 Plus' => 'vactor-2100-plus', 'Huber Berringer PD' => 'huber-berringer-pd', 'Huber Dominator' => 'huber-dominator',
	'Huber Scrubber' => 'huber-scrubber', 'Imperial Industries High Volume Pump' => 'imperial-industries-high-volume-pump',
	'Portable Restroom Truck' => 'portable-restroom-truck', 'Huber Berringer Liquid Ring' => 'huber-berringer-liquid-ring',
	'Huber King Vac' => 'huber-king-vac', 'Huber Knight' => 'huber-knight', '130 BBL Tankers' => '130-bbl-tankers',
	'Bossvac Hydrovac Trailer' => 'bossvac-hydrovac-trailer', 'GapVax Combo G7 Trailer Jetter' => 'gapvax-combo-g7-trailer-jetter',
	'Kaiser Premier TerraVac' => 'kaiser-premier-terravac', 'Two Box Roll-Off Trailers' => 'two-box-roll-off-trailers',
	'Vermeer LP XDT Vacuum Excavator' => 'vermeer-lp-xdt-vacuum-excavator', 'Roll-Off Trucks' => 'roll-off-trucks',
	'Tractors' => 'tractors', 'Water Trucks' => 'water-trucks',
);
check( 'the website list has 41 names', 41 === count( $website ) );
foreach ( $website as $name => $id ) {
	check( "website name \"{$name}\" → {$id}", in_array( $id, units_of( "Tell me about the {$name}." ), true ), json_encode( units_of( $name ) ) );
	check( "unit {$id} exists", isset( $units[ $id ] ) );
}

// ---------------------------------------------------------------------------
echo "\n== Phase 1 prompt names (mostly suppliers) resolve ==\n";
$legacy = array(
	'GapVax HV57' => 'gapvax-hv-57', 'Guzzler High Rail' => 'guzzler-high-rail', 'Keith Huber AM36' => 'huber-am36',
	'Super Products SuperSucker' => 'super-products-supersucker', 'CTOS Tornado F4' => 'tornado-f4', 'GapVax HV33' => 'gapvax-hv-33',
	'GapVax HV56' => 'gapvax-hv-56', 'Keith Huber Baron' => 'huber-baron-hx', 'Kaiser CV Series' => 'kaiser-premier-cv-series',
	'Super Products Mud Dog Air' => 'super-products-mud-dog-1200', 'HXX (TruVac/Vactor)' => 'truvac-hxx',
	// Vac2Go synonym ring (Q5)
	'Shellvac' => 'schellvac-svhx', 'Kaiser Premium' => 'kaiser-premier-cv-series', 'Guzzler' => 'guzzler-classic', 'XCR with cyclone' => 'guzzler-xcr',
	'Super Sucker' => 'super-products-supersucker', 'High Dump' => 'super-products-high-dump', 'Huber air mover' => 'huber-am30-hd', 'Knight' => 'huber-knight',
	'Camel' => 'super-products-camel-max-series', 'GapVax combo' => 'gapvax-mc1510', 'hydrovac trailer' => 'bossvac-hydrovac-trailer', 'trailer jetter' => 'gapvax-combo-g7-trailer-jetter',
	'150 BBL' => '130-bbl-tankers', 'Dragon vac trailer' => '130-bbl-tankers', 'cable roll off' => 'roll-off-trucks', 'roll-off trailer' => 'two-box-roll-off-trailers', 'truck tractor' => 'tractors',
	'Keith Huber SC1512' => 'huber-sc-1512', 'Vactor 2100+' => 'vactor-2100-plus', 'Vactor 2100i' => 'vactor-2100-plus',
	'Keith Huber Dominator SS' => 'huber-dominator-ss',
	'Keith Huber Scrubber' => 'huber-scrubber', 'High Volume Pump' => 'imperial-industries-high-volume-pump',
	'Keith Huber King Vac' => 'huber-king-vac', 'Keith Huber Knight' => 'huber-knight', 'BossVac BV500' => 'bossvac-hydrovac-trailer',
	'GapVax G7 Jetter Trailer' => 'gapvax-combo-g7-trailer-jetter', 'Kaiser Premier Terravac' => 'kaiser-premier-terravac',
	'Vermeer LPXDT Vacuum Excavator' => 'vermeer-lp-xdt-vacuum-excavator', 'Dragon 130-BBL Tanker' => '130-bbl-tankers',
	'ITI SS Code Tanker' => '130-bbl-tankers', 'Keith Huber 130-BBL Tanker' => '130-bbl-tankers', 'BTE Roll Off' => 'roll-off-trucks',
	'Benlee Two-Box Roll-Off Trailer' => 'two-box-roll-off-trailers', "Bergey's Roll Off" => 'roll-off-trucks',
	'CTOS Roll Off' => 'roll-off-trucks', 'Galfab Roll Off' => 'roll-off-trucks', 'Palmer Roll Off' => 'roll-off-trucks',
	"Bergey's Day Cab" => 'tractors', 'Kenworth T880 Day Cab' => 'tractors', 'Peterbilt 579' => 'tractors',
	"Bergey's Water Truck" => 'water-trucks', 'CTOS Water Truck' => 'water-trucks', 'ITI Water Truck' => 'water-trucks',
);
foreach ( $legacy as $name => $id ) {
	check( "old name \"{$name}\" → {$id}", in_array( $id, units_of( $name ), true ), json_encode( units_of( $name ) ) );
}
check( 'Keith Huber Berringer → both Berringer units', array() === array_diff( array( 'huber-berringer-pd', 'huber-berringer-liquid-ring' ), units_of( 'Keith Huber Berringer' ) ) );

// Vac2Go's equipment catalog (Q7): these are real fleet units.
foreach ( array( 'GapVax MC1312' => 'gapvax-mc1312', 'Guzzcavator' => 'guzzcavator', 'Vac Jet Rodding' => 'vac-jet-rodding', 'CTOS 70-BBL Liquid Vacuum' => 'ctos-70-bbl-liquid-vacuum', 'Keith Huber Dominator SS' => 'huber-dominator-ss' ) as $name => $id ) {
	check( "catalog unit \"{$name}\" → {$id}", in_array( $id, units_of( $name ), true ), json_encode( units_of( $name ) ) );
	check( "catalog unit \"{$name}\" is not reported as off-list", array() === VA_Fleet::resolve( $name )['off_list'] );
}
// The catalog lists these on one line with the website unit.
check( 'Vactor 2100i → Vactor 2100 Plus (catalog: "Vactor 2100+ / 2100i")', in_array( 'vactor-2100-plus', units_of( 'the 2100i' ), true ) );
check( 'Mud Dog Air → Mud Dog 1200 (catalog: "Mud Dog / Mud Dog Air")', in_array( 'super-products-mud-dog-1200', units_of( 'Mud Dog Air' ), true ) );

// Brands Vac2Go does not carry (synonym ring): a category, never a unit.
foreach ( array( 'Vacmaster 3000' => 'industrial-vacuum', 'a Vac-Con sewer truck' => 'combination', 'Tellus trailer' => 'trailer' ) as $name => $cat ) {
	$r = VA_Fleet::resolve( $name );
	check( "off-list \"{$name}\" → category {$cat}, no unit", in_array( $cat, $r['categories'], true ) && array() === $r['units'], json_encode( $r ) );
	check( "off-list \"{$name}\" is reported as a brand Vac2Go doesn't carry", array() !== $r['off_list'], json_encode( $r['off_list'] ) );
}

// The tagging sheet's "Unit(s) Covered" column spells a few names differently.
$tagging = array(
	'Huber Baron HX' => 'huber-baron-hx', 'Huber SC 1009; Huber SC 1512' => 'huber-sc-1009', 'Huber AM36; Huber AM30 HD' => 'huber-am30-hd',
	'Huber Berringer PD; Huber Berringer Liquid Ring' => 'huber-berringer-liquid-ring', 'Bossvac Hydrovac Trailer' => 'bossvac-hydrovac-trailer',
	'Guzzler Classic; Guzzler XCR; Guzzler High-Rail' => 'guzzler-high-rail', 'Super Products Supersucker; Super Products High Dump' => 'super-products-high-dump',
	'Kaiser Premier CV Series' => 'kaiser-premier-cv-series', 'Super Products Mud Dog 1200' => 'super-products-mud-dog-1200',
	'Vermeer LP XDT' => 'vermeer-lp-xdt-vacuum-excavator', 'Imperial Industries High Volume Pump' => 'imperial-industries-high-volume-pump',
	'130 BBL Tankers' => '130-bbl-tankers', 'Two Box Roll-Off Trailers' => 'two-box-roll-off-trailers', 'Roll-Off Trucks' => 'roll-off-trucks',
	'Tractors' => 'tractors', 'Water Trucks' => 'water-trucks', 'GapVax HV-56' => 'gapvax-hv-56', 'Super Products Camel Max Series' => 'super-products-camel-max-series',
);
foreach ( $tagging as $name => $id ) {
	check( "tagging-sheet name \"{$name}\" → {$id}", in_array( $id, units_of( $name ), true ), json_encode( units_of( $name ) ) );
}

// ---------------------------------------------------------------------------
echo "\n== What Phase 1 visitors actually typed ==\n";
$typed = array(
	"What's the debris capacity on the Vactor Paradigm" => 'vactor-paradigm',
	'Can you tell me about "Keith Huber Baron"?' => 'huber-baron-hx',
	'What oil does the blower take on the Kaiser Premier TerraVac' => 'kaiser-premier-terravac',
	'Please give me details of "Bergey\'s Water Truck"' => 'water-trucks',
	'Can you guide me on GapVax HV56? This one is pretty famous' => 'gapvax-hv-56',
	'can you give me details about the hv57?' => 'gapvax-hv-57',
	'What is the exact debris tank capacity, weight, and maintenance schedule for the Guzzler XCR?' => 'guzzler-xcr',
	'the Vactor 2100i you rent' => 'vactor-2100-plus',
	'Keith Huber Berringer Liquid Ring' => 'huber-berringer-liquid-ring',
);
foreach ( $typed as $text => $id ) {
	check( "typed \"{$text}\" → {$id}", in_array( $id, units_of( $text ), true ), json_encode( units_of( $text ) ) );
}

// ---------------------------------------------------------------------------
echo "\n== Customer words for categories ==\n";
$words = array(
	'Do you guys rent air movers with a baghouse?' => 'industrial-vacuum',
	'I need a hydrovac for daylighting gas lines' => 'hydro-excavator',
	'potholing near fiber' => 'hydro-excavator',
	'looking for a sewer combo' => 'combination',
	'need a jet vac for storm drains' => 'combination',
	'honey truck for septic' => 'liquid-vacuum',
	'do you have a liquid ring for the refinery' => 'liquid-ring',
	'I need a vacuum tanker' => 'tanker',
	'two roll-off boxes' => 'roll-off',
	'just a day cab tractor to pull it' => 'tractor',
	'I need to transport water, do you have a water truck?' => 'water',
	'something towable, a trailer would be fine' => 'trailer',
	// Eval 2026-10-09: these loaded no pack and the advisor said it had no figures.
	'Question about Combination equipment: what water pressure do the units run?' => 'combination',
	'Can the jetter damage clay pipe?' => 'combination',
);
foreach ( $words as $text => $cat ) {
	check( "\"{$text}\" → category {$cat}", in_array( $cat, cats_of( $text ), true ), json_encode( cats_of( $text ) ) );
}
check( '"a combination of sand and water" is not the Combination category', ! in_array( 'combination', cats_of( 'a combination of sand and water' ), true ) );

// ---------------------------------------------------------------------------
echo "\n== No false matches on ordinary words ==\n";
$plain = array(
	'I need a truck',
	'Digging around water lines is a standard job',
	'We have a classic problem with clay',
	'the night shift crew can sit with the truck',
	'What can you do?',
	'hello',
	'How much does it cost per day?',
	'we need to vacuum a lot of material quickly',
	'the site has a high fence',
);
foreach ( $plain as $text ) {
	$r = VA_Fleet::resolve( $text );
	check( "\"{$text}\" matches no unit", array() === $r['units'], json_encode( $r['units'] ) );
}
check( '"water lines" is not the Water category', ! in_array( 'water', cats_of( 'Digging around water lines' ), true ) );

// ---------------------------------------------------------------------------
echo "\n== Matching details ==\n";
check( 'hyphen, space and no-space forms are equal (HV-57, HV 57, hv57)', units_of( 'HV-57' ) === units_of( 'hv 57' ) && units_of( 'hv57' ) === units_of( 'HV-57' ) && array( 'gapvax-hv-57' ) === units_of( 'hv57' ) );
check( 'HV-57 does not also match HV-56 or HV-33', array( 'gapvax-hv-57' ) === units_of( 'Is the HV-57 good for fly ash?' ) );
check( 'two units in one message both resolve', array() === array_diff( array( 'huber-sc-1512', 'gapvax-mc1510' ), units_of( 'SC1512 vs MC1510?' ) ) );
check( 'a unit also yields its category', in_array( 'combination', cats_of( 'MC1510' ), true ) );
check( 'resolve() is deterministic (same order every time)', VA_Fleet::resolve( 'MC1510 and the Paradigm' ) === VA_Fleet::resolve( 'MC1510 and the Paradigm' ) );

echo "\n{$pass} passed, {$fail} failed\n";
exit( $fail ? 1 : 0 );
