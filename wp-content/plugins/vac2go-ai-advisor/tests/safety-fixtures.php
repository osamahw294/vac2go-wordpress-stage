<?php
/**
 * Safety-layer fixtures for Phase 2, runnable WITHOUT WordPress.
 *
 *   php tests/safety-fixtures.php
 *
 * Covers the two Phase 1 false refusals found in the staging logs (#258: "I can't
 * guarantee a callback time" tripped the `guarantee` pattern; #79: the judge read a
 * pure CFM answer as a commitment), leak markers for the Phase 2 prompt sections,
 * contact details typed into the chat, and a sweep of every knowledge-base answer and
 * unit-card line through the deterministic filter: legitimate knowledge must never be
 * blocked when the advisor repeats it.
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
function update_option( $k, $v ) {
	$GLOBALS['va_options'][ $k ] = $v;
	return true;
}
function wp_json_encode( $d ) { return json_encode( $d ); }

require_once __DIR__ . '/../includes/class-va-text.php';
require_once __DIR__ . '/../includes/class-va-fleet.php';
require_once __DIR__ . '/../includes/class-va-kb.php';
require_once __DIR__ . '/../includes/class-va-knowledge.php';
require_once __DIR__ . '/../includes/class-va-filter.php';
require_once __DIR__ . '/../includes/class-va-signals.php';
require_once __DIR__ . '/../includes/class-va-rest.php';

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
function blocked( $text ) {
	return VA_Filter::apply( $text )['filtered'];
}

echo "\n== Negated commitments are not commitments (Phase 1 log #258) ==\n";
foreach ( array(
	"I can't guarantee a callback time, but a rep can reach out.",
	'I can’t guarantee a callback time.',
	'I cannot guarantee that figure for your truck.',
	"I won't guarantee anything about availability.",
	'Availability is not guaranteed.',
	"I can't promise a delivery date.",
) as $t ) {
	check( "not blocked: \"{$t}\"", ! blocked( $t ), VA_Filter::apply( $t )['reason'] ?? '' );
}
foreach ( array(
	'We guarantee the truck will be on site Monday.',
	'That rate is guaranteed for you.',
	'Guaranteed availability next week.',
) as $t ) {
	check( "still blocked: \"{$t}\"", blocked( $t ) );
}

echo "\n== Stored pattern lists are migrated only when untouched ==\n";
$GLOBALS['va_options']['va_banned_patterns'] = VA_Filter::OLD_DEFAULT_PATTERNS;
VA_Filter::migrate_patterns();
check( 'an untouched Phase 1 list is replaced by the current defaults', VA_Filter::default_patterns_text() === get_option( 'va_banned_patterns' ) );
$GLOBALS['va_options']['va_banned_patterns'] = "/\\bmy custom\\b/i\n/\\bguaranteed?\\b/i";
VA_Filter::migrate_patterns();
check( 'an admin-edited list is left alone', "/\\bmy custom\\b/i\n/\\bguaranteed?\\b/i" === get_option( 'va_banned_patterns' ) );
unset( $GLOBALS['va_options']['va_banned_patterns'] );

echo "\n== Phase 2 prompt sections count as leaks ==\n";
foreach ( array( '== USING THE KNOWLEDGE ==', 'KNOWLEDGE PACK: Combination', '== CATEGORIES AND FLEET', '## Notes for the advisor', '== REVIEWED CORRECTIONS', '== NOTES FROM THE VAC2GO TEAM' ) as $m ) {
	$r = VA_Filter::apply( "Sure, here it is: {$m} ..." );
	check( "leak marker \"{$m}\" is caught", $r['filtered'] && 'structural' === $r['stage'], json_encode( $r['stage'] ) );
}

// Review finding 2: honest "I don't have that" replies that mention the phrases must pass.
foreach ( array( "I don't have the knowledge pack for Liquid Ring in front of me, so a rep can confirm.", 'The notes for the advisor say to confirm with a rep.' ) as $t ) {
	check( "an honest reply is not treated as a leak: \"{$t}\"", ! blocked( $t ), VA_Filter::apply( $t )['reason'] ?? '' );
}

echo "\n== The judge is told what is not a commitment (Phase 1 log #79) ==\n";
$jp = VA_Filter::judge_prompt();
foreach ( array( 'CFM', 'Hg', 'gallons', 'PSI', 'GPM', 'BTU', 'GVWR', 'CDL' ) as $w ) {
	check( "judge prompt names {$w} as a spec, not a commitment", false !== stripos( $jp, $w ) );
}
check( 'judge prompt says "a rep can explain rates" is not a commitment', false !== stripos( $jp, 'rep' ) && false !== stripos( $jp, 'not commitments' ) );

echo "\n== Contact details typed into the chat bring up the follow-up form ==\n";
check( 'an email address in the message → contact', 'contact' === VA_Signals::followup_reason( 'John, john@example.com, please have a rep call', 'Thanks.' ) );
check( 'a phone number in the message → contact', 'contact' === VA_Signals::followup_reason( 'call me at 555-0100 or (502) 699-4019', 'Thanks.' ) );
check( 'no contact details → no contact signal', 'contact' !== VA_Signals::followup_reason( 'What is the CFM on the MC1510?', 'Thanks.' ) );
check( 'a spec figure is not mistaken for a phone number', null === VA_Signals::followup_reason( 'can it lift 1,500 gal and 5,300 cfm?', 'Thanks.' ) );
// Review finding 4: a size range is not a phone number.
check( 'a size range (400-1500 gallons) is not a phone number', 'contact' !== VA_Signals::followup_reason( 'I need a tank in the 400-1500 gallon range', 'Thanks.' ) );
check( 'a full phone number with an area code still counts', 'contact' === VA_Signals::followup_reason( 'reach me on 502-699-4019', 'Thanks.' ) );

echo "\n== The off-topic pre-screen (staging test 2026-10-08) ==\n";
check( 'pre-screen runs on the first message of a conversation', VA_REST::prescreen_applies( true ) );
check( 'pre-screen never runs mid-conversation ("dust" was declined as off-topic)', ! VA_REST::prescreen_applies( false ) );
foreach ( array( 'dust', 'Compare the Camel 900, 1200 and 1600.', 'fly ash in a hopper', 'Tell me about the Huber Knight', 'need a hydrovac' ) as $t ) {
	check( "clearly on topic: \"{$t}\"", VA_REST::clearly_relevant( $t ) );
}
check( 'an unrelated request is not waved through', ! VA_REST::clearly_relevant( 'write me a poem about the sea' ) );

echo "\n== Every knowledge-base line passes the filter ==\n";
$lines = array();
foreach ( VA_Fleet::categories() as $cid => $c ) {
	foreach ( explode( "\n", (string) VA_KB::category( $cid ) ) as $l ) {
		$lines[] = array( "category {$cid}", $l );
	}
}
foreach ( VA_Fleet::units() as $uid => $u ) {
	foreach ( explode( "\n", (string) VA_KB::card( $uid ) ) as $l ) {
		$lines[] = array( "card {$uid}", $l );
	}
}
$hits = array();
foreach ( $lines as $pair ) {
	list( $where, $l ) = $pair;
	$t = trim( preg_replace( '/^(#+|\*\*Q:|- )\s*/', '', trim( $l ) ) );
	if ( '' === $t || 0 === strpos( trim( $l ), '#' ) || 0 === stripos( $t, 'Notes for the advisor' ) ) {
		continue; // headings are prompt structure, not answer text
	}
	$r = VA_Filter::apply( $t );
	if ( $r['filtered'] ) {
		$hits[] = "{$where}: [{$r['stage']}] " . substr( $t, 0, 110 );
	}
}
check( 'no knowledge-base line is blocked (' . count( $lines ) . ' lines swept)', array() === $hits, implode( "\n          ", $hits ) );

echo "\n{$pass} passed, {$fail} failed\n";
exit( $fail ? 1 : 0 );
