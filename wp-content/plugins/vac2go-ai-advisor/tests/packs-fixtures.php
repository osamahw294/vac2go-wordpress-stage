<?php
/**
 * Knowledge delivery fixtures, runnable WITHOUT WordPress.
 *
 *   php tests/packs-fixtures.php
 *
 * The prompt is: rules + core knowledge (always), then reviewed corrections, then
 * knowledge packs (one category's Q&A plus its unit cards) for the categories the
 * conversation is about. Packs are chosen deterministically from the conversation and
 * appended in order of first mention, so the cached prefix stays stable turn to turn.
 */

// phpcs:disable

if ( PHP_SAPI !== 'cli' ) {
	header( 'HTTP/1.1 404 Not Found' );
	exit;
}

define( 'ABSPATH', __DIR__ );
$GLOBALS['va_options'] = array( 'va_canary' => 'VA-CANARY-TESTTOKEN12345' );
function get_option( $k, $d = false ) {
	return array_key_exists( $k, $GLOBALS['va_options'] ) ? $GLOBALS['va_options'][ $k ] : $d;
}

require_once __DIR__ . '/../includes/class-va-text.php';
require_once __DIR__ . '/../includes/class-va-fleet.php';
require_once __DIR__ . '/../includes/class-va-kb.php';
require_once __DIR__ . '/../includes/class-va-knowledge.php';

$pass = 0;
$fail = 0;
function check( $label, $ok, $detail = '' ) {
	global $pass, $fail;
	if ( $ok ) {
		$pass++;
		echo "  PASS  {$label}\n";
	} else {
		$fail++;
		echo "  FAIL  {$label}" . ( $detail ? "\n          {$detail}" : '' ) . "\n";
	}
}
function section( $t ) {
	echo "\n== {$t} ==\n";
}

// ---------------------------------------------------------------------------
section( 'Pack selection' );
check( 'nothing named → no packs', array() === VA_KB::select_packs( array(), 'I need a truck' ) );
check( 'a category word picks that pack', array( 'hydro-excavator' ) === VA_KB::select_packs( array(), 'I need a hydrovac for daylighting' ) );
check( 'a unit name picks its category pack', array( 'combination' ) === VA_KB::select_packs( array(), 'What is the CFM on the MC1510?' ) );
check( 'an old supplier name picks the right pack', array( 'water' ) === VA_KB::select_packs( array(), "Bergey's Water Truck details?" ) );

$history = array( 'Do you rent a sewer combo?', 'Yes, that is our Combination category: GapVax MC1510, Huber SC 1009, ...' );
check( 'packs stay for the rest of the conversation', array( 'combination' ) === VA_KB::select_packs( $history, 'and how much water does it carry?' ) );
check( 'a new category is appended after earlier ones', array( 'combination', 'hydro-excavator' ) === VA_KB::select_packs( $history, 'what about a hydrovac instead?' ) );
check( 'the advisor naming a category in its answer loads that pack', array( 'liquid-ring' ) === VA_KB::select_packs( array( 'refinery tank with benzene vapors', 'That calls for a liquid ring unit.' ), 'ok tell me more' ) );

$long = array( 'sewer combo?', 'Combination.', 'and a hydrovac?', 'Hydro Excavator.', 'also a tanker', 'Tanker.' );
$four = VA_KB::select_packs( $long, 'and a roll-off box?' );
check( 'at most 3 packs', 3 === count( $four ), json_encode( $four ) );
check( 'over the cap, the earliest pack is dropped', array( 'hydro-excavator', 'tanker', 'roll-off' ) === $four, json_encode( $four ) );
check( 'selection is deterministic', VA_KB::select_packs( $long, 'x' ) === VA_KB::select_packs( $long, 'x' ) );

// Review finding 1: the cap must never drop the category the customer is asking about now.
$overview = array( 'what do you rent?', 'We cover Industrial Vacuum, Hydro Excavator, Combination, Liquid Vacuum, Liquid Ring, trailers, tankers, roll-off boxes, tractors and water trucks.' );
$p = VA_KB::select_packs( $overview, 'ok, hydrovac' );
check( 'after an overview listing every category, "ok, hydrovac" still carries the Hydro Excavator pack', in_array( 'hydro-excavator', $p, true ) && count( $p ) <= 3, json_encode( $p ) );
$poorfits = array( 'I need a hydrovac for potholing', 'A hydro excavator fits. A combo, a liquid vacuum truck or a tanker would be poor fits.' );
$p = VA_KB::select_packs( $poorfits, 'What is the water capacity on the Vactor Paradigm hydro excavator?' );
check( 'a category named early and again now is kept over later passing mentions', in_array( 'hydro-excavator', $p, true ) && count( $p ) <= 3, json_encode( $p ) );
check( 'when the current message names nothing, the sticky set is unchanged', VA_KB::select_packs( $history, 'thanks' ) === VA_KB::select_packs( $history, 'ok' ) );
// Load test 2026-10-09: an answer listing poor fits pushed out the category the
// conversation was about, and "How much vacuum and airflow does it have?" got "I don't
// have the figures". Over the cap, what the customer asked about and each answer's lead
// category outrank categories an answer only mentioned in passing.
$silo = array(
	'Cleaning out a grain silo, dry product, a lot of it.',
	"For a large dry grain silo cleanout, an industrial vacuum truck is the right fit. Vac2Go's units in that category:\n\nGapVax HV-57\nGuzzler Classic\n\nThis is a high-level recommendation. Confirm specifics with a Vac2Go rep.",
	'Is there anything it would not be a good fit for?',
	'An industrial vacuum is not the right call for precision daylighting or utility locating (hydro excavator), high-pressure sewer line jetting (combination unit), or hauling large volumes of clean liquid where only pumping is needed (liquid vacuum).',
);
$p = VA_KB::select_packs( $silo, 'How much vacuum and airflow does it have?' );
check( 'the subject of the conversation outlasts categories an answer named in passing', in_array( 'industrial-vacuum', $p, true ) && count( $p ) <= 3, json_encode( $p ) );
$gas = array(
	'We need to expose buried gas and fiber lines before we bore under a road.',
	'For exposing live gas and fiber lines ahead of a road bore, a Hydro Excavator is the right category.',
	'How does that compare with a hydro excavator for this job?',
	'The category I recommended is the hydro excavator. An industrial vacuum only recovers loose material, a combo jets sewer lines, and a hydro excavation trailer suits small jobs.',
);
$p = VA_KB::select_packs( $gas, 'Do I need a CDL to drive it?' );
check( 'a category the customer asked about outlasts later passing mentions', in_array( 'hydro-excavator', $p, true ) && count( $p ) <= 3, json_encode( $p ) );

$p = VA_KB::select_packs( array(), 'Compare a combo, a hydrovac, a tanker and a roll-off box.' );
check( 'a message naming more than 3 categories keeps 3 of them', 3 === count( $p ), json_encode( $p ) );

// Review finding 3: the transcript read must cover the whole session.
check( 'transcript limit covers the default session cap', VA_KB::transcript_limit( 40 ) >= 40 );
check( 'transcript limit follows a raised session cap', VA_KB::transcript_limit( 120 ) >= 120 );
check( 'transcript limit with no session cap is still bounded but generous', VA_KB::transcript_limit( 0 ) >= 200 );

// ---------------------------------------------------------------------------
section( 'Pack contents' );
$combo = VA_KB::pack( 'combination' );
check( 'pack carries the category Q&A', false !== strpos( $combo, 'Is a combination sewer cleaner the same thing as a combination sewer jetter?' ) );
foreach ( VA_Fleet::units_in( 'combination' ) as $uid ) {
	check( "combination pack carries the {$uid} card", false !== strpos( $combo, '# ' . VA_Fleet::unit( $uid )['name'] ) );
}
check( 'pack has no source tags', false === strpos( $combo, '{src' ) );
// Client answer to Q13: Two Box Roll-Off Trailers sit under Roll-Off only.
check( 'Two Box Roll-Off Trailers are in the Roll-Off pack, not the Trailer pack', false === strpos( VA_KB::pack( 'trailer' ), '# Two Box Roll-Off Trailers' ) && false !== strpos( VA_KB::pack( 'roll-off' ), '# Two Box Roll-Off Trailers' ) );
check( 'unknown category → empty pack', '' === VA_KB::pack( 'nope' ) );
foreach ( VA_Fleet::categories() as $cid => $c ) {
	$tokens = (int) ( strlen( VA_KB::pack( $cid ) ) / 4 );
	check( "{$cid} pack is under ~16k tokens (~{$tokens})", $tokens > 200 && $tokens < 16000 );
}

// ---------------------------------------------------------------------------
section( 'Core knowledge (sent every turn)' );
$core = VA_KB::core();
foreach ( VA_Fleet::units() as $u ) {
	check( "core lists {$u['name']}", false !== strpos( $core, $u['name'] ) );
}
foreach ( VA_Fleet::categories() as $cid => $c ) {
	check( "core has the {$c['name']} summary", false !== strpos( $core, '### ' . $c['name'] ) );
}
check( 'core carries customer words for categories (synonyms)', false !== stripos( $core, 'hydrovac' ) && false !== stripos( $core, 'sewer combo' ) );
check( 'core has no source tags', false === strpos( $core, '{src' ) );
// Staging test 2026-10-08: the Liquid Ring recommendation listed the Huber Scrubber as a
// unit, and a potholing trailer answer listed the Two Box Roll-Off Trailers.
$units_line = function ( $name ) use ( $core ) {
	return preg_match( '/### ' . preg_quote( $name, '/' ) . '\n.*?\nVac2Go units: ([^\n]*)/s', $core, $m ) ? $m[1] : '';
};
check( 'Liquid Ring units do not include the Scrubber (a support skid)', false === strpos( $units_line( 'Liquid Ring' ), 'Huber Scrubber' ) );
check( 'Trailer units do not include the Two Box Roll-Off Trailers', false === strpos( $units_line( 'Trailer' ), 'Two Box Roll-Off Trailers' ) );
// Client answer to Q13: the Huber Scrubber is a Liquid Vacuum unit.
check( 'the Huber Scrubber is listed with the Liquid Vacuum units', false !== strpos( $units_line( 'Liquid Vacuum' ), 'Huber Scrubber' ) );
check( 'each unit is a unit of exactly one category in the core', ( function () use ( $core ) {
	preg_match_all( '/^Vac2Go units: (.*)$/m', $core, $m );
	$all = array();
	foreach ( $m[1] as $line ) {
		$all = array_merge( $all, explode( ', ', rtrim( $line, '.' ) ) );
	}
	return 46 === count( $all ) && 46 === count( array_unique( $all ) );
} )() );
check( 'core is under ~12k tokens', strlen( $core ) / 4 < 12000, (string) ( strlen( $core ) / 4 ) );

// ---------------------------------------------------------------------------
section( 'Rules' );
$rules = VA_Knowledge::get_system_prompt();
check( 'no Phase 1 "one fully-detailed unit" framing', false === stripos( $rules, 'one fully-detailed' ) && false === stripos( $rules, 'limited detail in this trial' ) );
check( 'keeps the recommendation caveat sentence', false !== strpos( $rules, 'This is a high-level recommendation. Confirm specifics with a Vac2Go rep.' ) );
check( 'keeps the hazmat closing sentence', false !== strpos( $rules, VA_Knowledge::HAZMAT_SENTENCE ) );
check( 'keeps the canary marker', false !== strpos( $rules, 'VA-CANARY-TESTTOKEN12345' ) );
check( 'figures only from the knowledge (Q10)', false !== stripos( $rules, 'Quote a figure only if the knowledge contains it' ) );
check( 'never invent or extrapolate figures', false !== stripos( $rules, 'never invent' ) );
check( 'unit cards win over general category figures', false !== stripos( $rules, "use the unit card's figure" ) );
check( 'off-list names are handled', false !== stripos( $rules, 'not on our current list' ) );
check( 'no "route them" wording for the model to echo', false === stripos( $rules, 'route them' ) );

// Branch-by-state mapping from Vac2Go (2026-10-15): every branch with its phone and the
// states it serves, so "I'm in St. Louis" gets the Tennessee branch, not "no branch".
$branches = array(
	'Kentucky branch (502) 699-4029. Serves: Kentucky, West Virginia, Tennessee (Middle and East).',
	'South Carolina branch (839) 232-1212. Serves: South Carolina, North Carolina, Virginia, Maryland, Georgia (East).',
	'Tennessee branch (901) 455-2464. Serves: Missouri, Kansas, Oklahoma, Arkansas, Tennessee (West, Memphis area).',
	'Ohio branch (440) 287-1687. Serves: Ohio, Pennsylvania, New York.',
	'Alabama branch (251) 440-3133. Serves: Alabama, Mississippi, Georgia (West), Florida (panhandle).',
	'Texas branch (346) 460-5522. Serves: Texas, Louisiana.',
	'Indiana branch (219) 359-3314. Serves: Indiana, Illinois, Michigan, Wisconsin, Minnesota, Iowa, North Dakota, South Dakota, Nebraska (East).',
	'Florida branch, Fort Myers and Orlando (407) 232-6255. Serves: Florida (peninsula).',
	'New Jersey branch (540) 246-4850. Serves: New Jersey, Delaware, Connecticut, Rhode Island, Massachusetts, Vermont, New Hampshire, Maine.',
	'Arizona branch (602) 325-5446. Serves: Arizona, California, Nevada, New Mexico.',
	'Utah branch (385) 213-7690. Serves: Utah, Colorado, Idaho, Wyoming, Montana, Washington, Oregon, Nebraska (West).',
);
foreach ( $branches as $b ) {
	check( "core lists the branch and its states: " . strtok( $b, '(' ), false !== strpos( $core, $b ) );
}
$served = array();
foreach ( $branches as $b ) {
	preg_match( '/Serves: ([^.]+)\./', $b, $m );
	foreach ( explode( ', ', preg_replace( '/\s*\([^)]*\)/', '', $m[1] ) ) as $st ) {
		$served[ trim( $st ) ] = true;
	}
}
// Addresses and regional sales managers, as published on vac2go.com/contact (2026-10-15).
foreach ( array(
	'Address: 11120 Plantside Drive, Louisville, KY 40299. Regional sales manager: Jeff Wells.',
	'Address: 1525 Three Place, Memphis, TN 38116. Regional sales manager: Mickey Still.',
	'Address: 3600 S 7th Avenue, Phoenix, AZ 85041. Regional sales manager: Jamie Bernzweig.',
	'Addresses: 6110 Idlewild St., Fort Myers, FL 33966; 500 Codisco Way, Sanford, FL 32771 (Orlando). Regional sales manager: Hunter Geis.',
	'1210 Commerce Drive, Madison, GA 30650',
) as $a ) {
	check( "core has the address: {$a}", false !== strpos( $core, $a ) );
}
check( 'the mapping covers 48 states (all but Alaska and Hawaii)', 48 === count( $served ), (string) count( $served ) );
check( 'core says not to tell a listed state there is no branch', false !== stripos( $core, 'never say Vac2Go has no branch' ) );
check( 'core gives the main number for places no branch serves', false !== stripos( $core, 'Alaska, Hawaii' ) );
check( 'location names include served states and branch cities', array() === array_diff( array( 'Missouri', 'Colorado', 'Fort Myers', 'Orlando', 'Kentucky' ), VA_KB::location_names() ), implode( ', ', VA_KB::location_names() ) );
check( 'branch locations are no longer deflected as a policy question', false === stripos( $rules, 'regional or international coverage, branch locations' ) );
check( 'branch questions name the branch that serves the state', false !== stripos( $rules, 'branch that serves' ) );

// Client answer to Q10: only knowledge figures; differences as a range; every figure
// followed by "depends on the configuration"; compare only on confirmed figures.
check( 'differing figures are given as a range', false !== stripos( $rules, 'as a range' ) );
check( 'every quoted figure is followed by the configuration note', false !== stripos( $rules, 'every figure you quote' ) && false !== stripos( $rules, 'depends on the configuration' ) );
check( 'comparisons only when every compared unit has the figure', false !== stripos( $rules, 'only when every unit you compare' ) );
check( 'no 6-bullet cap that splits unit lists', false === stripos( $rules, 'never more than 6 bullets' ) );
check( 'typed contact details are pointed at the follow-up form', false !== stripos( $rules, 'Want a rep to follow up?' ) );
check( 'admin notes are appended when set', ( function () {
	$GLOBALS['va_options']['va_admin_notes'] = 'Mention the spring promotion never.';
	$ok = false !== strpos( VA_Knowledge::get_system_prompt(), 'Mention the spring promotion never.' );
	unset( $GLOBALS['va_options']['va_admin_notes'] );
	return $ok;
} )() );
check( 'the old stored va_system_prompt no longer overrides the rules', ( function () {
	$GLOBALS['va_options']['va_system_prompt'] = 'OLD PHASE 1 PROMPT';
	$ok = false === strpos( VA_Knowledge::get_system_prompt(), 'OLD PHASE 1 PROMPT' );
	unset( $GLOBALS['va_options']['va_system_prompt'] );
	return $ok;
} )() );
check( 'rules + core are byte-stable (prompt cache)', VA_Knowledge::get_system_prompt() === VA_Knowledge::get_system_prompt() );
// The formatting rule has to show the character to forbid it; nothing else may use it.
check( 'the only em dash is the one the formatting rule names', 1 === substr_count( $rules, '—' ) && false !== strpos( $rules, '(the — character)' ) && false === strpos( $core, '—' ) );

// ---------------------------------------------------------------------------
section( 'System blocks and caching' );
$GLOBALS['va_options']['va_corrections_in_prompt'] = 0; // no DB here; corrections tested in signals-fixtures
$b0 = VA_Knowledge::get_system_blocks( array() );
$b1 = VA_Knowledge::get_system_blocks( array( 'combination' ) );
$b2 = VA_Knowledge::get_system_blocks( array( 'combination', 'hydro-excavator' ) );
check( 'no packs → one block', 1 === count( $b0 ) );
check( 'block 1 is identical with or without packs (shared cached prefix)', $b0[0] === $b1[0] && $b1[0] === $b2[0] );
check( 'block 1 holds rules and core', false !== strpos( $b0[0]['text'], 'This is a high-level recommendation.' ) && false !== strpos( $b0[0]['text'], 'GapVax MC1510' ) );
check( 'packs go in a later block', 2 === count( $b1 ) && false !== strpos( $b1[1]['text'], '# Super Products Camel Max Series' ) );
check( 'adding a pack only appends (earlier pack text unchanged as a prefix)', 0 === strpos( $b2[1]['text'], rtrim( $b1[1]['text'] ) ) );
check( 'every block is cache-marked', array() === array_filter( $b2, function ( $b ) {
	return ( $b['cache_control']['type'] ?? '' ) !== 'ephemeral';
} ) );
check( 'never more than 4 cache breakpoints', count( $b2 ) <= 4 );

// Round 1 Groups B and C (job matching, industries) are always available (client answer to Q12).
check( 'core carries the job-matching answers', false !== strpos( $core, 'I need to clean out a sludge pit' ) );
check( 'core carries the industries', false !== strpos( $core, 'Refineries' ) );

echo "\n{$pass} passed, {$fail} failed\n";
exit( $fail ? 1 : 0 );
