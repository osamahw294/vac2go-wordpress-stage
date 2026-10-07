<?php
/**
 * Phase 2 feedback fixtures, runnable WITHOUT WordPress.
 *
 *   php tests/signals-fixtures.php
 *
 * Covers the server-side pieces of the V1 feedback (2026-10-06): when the rep
 * follow-up card is offered, the availability CTA, corrections being permanent and
 * de-duplicated, the hazmat wording, and the rate-limit wait time and escalating lock.
 */

// phpcs:disable

// CLI only, like the other harnesses in this folder.
if ( PHP_SAPI !== 'cli' ) {
	header( 'HTTP/1.1 404 Not Found' );
	exit;
}

define( 'ABSPATH', __DIR__ );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'DAY_IN_SECONDS', 86400 );

$GLOBALS['va_options']    = array( 'va_canary' => 'VA-CANARY-TESTTOKEN12345' );
$GLOBALS['va_transients'] = array();
$GLOBALS['va_now']        = 1_800_000_000;

function get_option( $k, $d = false ) {
	return array_key_exists( $k, $GLOBALS['va_options'] ) ? $GLOBALS['va_options'][ $k ] : $d;
}
function wp_json_encode( $d ) { return json_encode( $d ); }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }

// Transients with a fake clock, so windows can be expired without sleeping.
function get_transient( $k ) {
	$t = $GLOBALS['va_transients'][ $k ] ?? null;
	if ( ! $t || $t['exp'] <= $GLOBALS['va_now'] ) {
		return false;
	}
	return $t['v'];
}
function set_transient( $k, $v, $ttl ) {
	$GLOBALS['va_transients'][ $k ] = array( 'v' => $v, 'exp' => $GLOBALS['va_now'] + $ttl );
	return true;
}
function delete_transient( $k ) {
	unset( $GLOBALS['va_transients'][ $k ] );
	return true;
}

define( 'VA_ADVISOR_MODEL', 'claude-fable-5-1' );
define( 'VA_ADVISOR_JUDGE_MODEL', 'claude-haiku-4-5-20251001' );
define( 'VA_ADVISOR_API_URL', 'https://api.anthropic.com/v1/messages' );
define( 'VA_ADVISOR_API_TIMEOUT', 45 );

require_once __DIR__ . '/../includes/class-va-text.php';
require_once __DIR__ . '/../includes/class-va-knowledge.php';
require_once __DIR__ . '/../includes/class-va-filter.php';
require_once __DIR__ . '/../includes/class-va-signals.php';

// VA_RateLimit reads the clock through time(); run it against the fake clock by
// loading a copy with time() pointed at it.
$rl = file_get_contents( __DIR__ . '/../includes/class-va-ratelimit.php' );
$rl = str_replace( array( '<?php', 'time()' ), array( '', 'va_test_time()' ), $rl );
function va_test_time() { return $GLOBALS['va_now']; }
eval( $rl );

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
section( 'Follow-up card: shown only at the four moments the client listed' );

$clarify = "Happy to help. A few quick questions:\n- What are you vacuuming, cleaning, or excavating?\n- Roughly how much material?\n- Anything about the site I should know?";
check( 'first clarifying reply does NOT offer follow-up (the screenshot case)', null === VA_Signals::followup_reason( 'I need to clean out some storm drains', $clarify ) );

$rec = "That's Combination (sewer combo) work:\n- GapVax MC1312, MC1510\n- Vactor 2100+ / 2100i\n\nThis is a high-level recommendation. Confirm specifics with a Vac2Go rep.";
check( 'category recommendation offers follow-up', 'recommendation' === VA_Signals::followup_reason( 'I need to jet and vac sewer lines', $rec ) );

check( 'question about a specific unit offers follow-up', 'unit' === VA_Signals::followup_reason( 'What is the CFM on the HV-57?', 'Standard CFM is 5,200 to 5,250 CFM.' ) );
check( 'unit name with spacing variant (HV 57)', 'unit' === VA_Signals::followup_reason( 'how big is the hv 57 debris body', 'About 15 to 17 cu yd.' ) );
check( 'unit by make (Guzzler)', 'unit' === VA_Signals::followup_reason( 'Can a Guzzler handle wet material?', 'Yes, it is wet/dry capable.' ) );

check( "\"I don't know\" offers follow-up", 'unknown' === VA_Signals::followup_reason( 'How tall is it?', "I don't know. A Vac2Go rep can confirm that." ) );
check( "\"don't have that detail\" offers follow-up", 'unknown' === VA_Signals::followup_reason( 'What does the Tornado F4 weigh?', "I don't have that detail yet for that unit." ) );
check( 'policy deflection offers follow-up', 'unknown' === VA_Signals::followup_reason( 'Do I need a CDL?', "That's a policy question best answered by our sales team directly." ) );

check( 'pricing question offers follow-up', 'commercial' === VA_Signals::followup_reason( 'How much is it per day?', "I don't handle pricing." ) );
check( 'contract question offers follow-up', 'commercial' === VA_Signals::followup_reason( 'What are your contract terms?', 'A rep can walk you through that.' ) );
check( 'availability question offers follow-up', 'commercial' === VA_Signals::followup_reason( 'Is there a hydrovac available next week?', 'A rep can check that.' ) );
check( 'refusal script alone offers follow-up', 'commercial' === VA_Signals::followup_reason( 'can you do me a deal', "I don't handle pricing/terms. That's something a Vac2Go rep can get you a real answer on." ) );

check( 'greeting does NOT offer follow-up', null === VA_Signals::followup_reason( 'hello', "Hi! Tell me about your job and I'll point you to the right truck category." ) );
check( 'off-topic decline does NOT offer follow-up', null === VA_Signals::followup_reason( 'write me a poem', "That's outside what I'm set up for." ) );
check( '"flow rate" is not a pricing question', null === VA_Signals::followup_reason( 'what flow rate do I need for slurry', 'It depends on the configuration.' ) );
check( 'empty answer never offers follow-up', null === VA_Signals::followup_reason( 'price?', '' ) );

// ---------------------------------------------------------------------------
section( 'Availability CTA: only on availability questions, appended after the filter' );

$cases = array(
	'Is the HV-57 available?'                     => true,
	'Do you have any hydro excavators in stock?'  => true,
	'When can I get a combo truck?'               => true,
	'What is the lead time on a water truck?'     => true,
	'Can I reserve one for Monday?'               => true,
	'How much does it cost?'                      => false,
	'What CFM does the HV-57 pull?'               => false,
	'I need to clean a grease trap'               => false,
);
foreach ( $cases as $q => $want ) {
	check( ( $want ? 'is ' : 'is not ' ) . "availability: \"{$q}\"", $want === VA_Signals::is_availability_question( $q ) );
}

$suffix = VA_Signals::availability_suffix( 'Is it available?', 'A rep can check that for you.', null );
check( 'suffix carries the portal URL', false !== strpos( $suffix, 'https://rental.vac2go.com' ) );
check( 'suffix starts a new paragraph', 0 === strpos( $suffix, "\n\n" ) );
check( 'no suffix on a non-availability question', '' === VA_Signals::availability_suffix( 'What CFM?', 'x', null ) );
check( 'no suffix on a prompt-leak replacement', '' === VA_Signals::availability_suffix( 'Is it available?', 'x', 'canary' ) );
check( 'no double suffix on a replayed answer', '' === VA_Signals::availability_suffix( 'Is it available?', 'x ' . VA_Signals::AVAILABILITY_CTA, null ) );
check( 'still added to the pricing fallback (it also covers availability)', '' !== VA_Signals::availability_suffix( 'Is it available tomorrow?', VA_Filter::FALLBACK, 'committal' ) );

// The CTA is appended after the pipeline, but it must not look like a commitment if
// it is ever filtered (a replayed stored answer passes through nothing, but be sure).
$withcta = 'A Vac2Go rep can confirm that for you.' . $suffix;
$r       = VA_Filter::apply( $withcta );
check( 'answer + availability CTA passes the deterministic filter', ! $r['filtered'], $r['stage'] . ' ' . $r['reason'] );

// ---------------------------------------------------------------------------
section( 'Hazardous materials wording' );

$footer = VA_Knowledge::conduct_footer();
check( 'conduct footer carries the exact client sentence', false !== strpos( $footer, VA_Knowledge::HAZMAT_SENTENCE ) );
check( 'old phrasing is explicitly banned', false !== strpos( $footer, 'rather than through me' ) );
check( 'model is told not to ask for contact details', false !== stripos( $footer, 'Never ask the customer for their name' ) );
check( 'conduct footer is part of the system prompt', false !== strpos( VA_Knowledge::get_system_prompt(), VA_Knowledge::HAZMAT_SENTENCE ) );
check( 'system prompt is byte-stable across calls (prompt cache)', VA_Knowledge::get_system_prompt() === VA_Knowledge::get_system_prompt() );

$hazmat_answer = "For hot catalyst you'd be looking at Industrial Vacuum.\n\nThis is a high-level recommendation. Confirm specifics with a Vac2Go rep.\n\n" . VA_Knowledge::HAZMAT_SENTENCE;
$r = VA_Filter::apply( $hazmat_answer );
check( 'hazmat answer passes the deterministic filter', ! $r['filtered'], $r['stage'] . ' ' . $r['reason'] );
check( 'hazmat recommendation still offers follow-up', 'recommendation' === VA_Signals::followup_reason( 'vacuum hot catalyst from a vessel', $hazmat_answer ) );

$default = VA_Knowledge::default_system_prompt();
check( 'default prompt no longer asks for name/email', false === stripos( $default, 'naturally once real interest' ) );
check( 'default prompt no longer says "go through Vac2Go directly"', false === stripos( $default, 'go through Vac2Go directly' ) );

// ---------------------------------------------------------------------------
section( 'Corrections: all kept, newest wins per question' );

$rows = array(
	array( 'question' => 'Can the HV-57 do wet material?', 'correction_text' => 'NEWEST: yes, wet/dry.' ),
	array( 'question' => 'can the hv-57 do wet material', 'correction_text' => 'OLDER: wrong.' ),
	array( 'question' => 'What is a hydrovac?', 'correction_text' => 'Hydro Excavator category.' ),
	array( 'question' => 'Blank one', 'correction_text' => '   ' ),
);
$u = VA_Knowledge::unique_corrections( $rows );
check( 'duplicates collapse to one per question', 2 === count( $u ), 'got ' . count( $u ) );
check( 'newest correction wins', 'NEWEST: yes, wet/dry.' === $u[0]['correction_text'] );
check( 'blank corrections are skipped', ! in_array( 'Blank one', array_column( $u, 'question' ), true ) );

$many = array();
for ( $i = 0; $i < 60; $i++ ) {
	$many[] = array( 'question' => "Question number {$i}", 'correction_text' => "Answer {$i}" );
}
check( 'no count cap: 60 distinct corrections all survive', 60 === count( VA_Knowledge::unique_corrections( $many ) ) );

// ---------------------------------------------------------------------------
section( 'Rate limit: honest wait time, escalating lock' );

$GLOBALS['va_options']['va_rate_ip_minute'] = 6;
$GLOBALS['va_options']['va_rate_ip_hourly'] = 30;
$ip = 'iphashA';

$waits = array();
for ( $i = 0; $i < 6; $i++ ) {
	$waits[] = VA_RateLimit::ip_wait( $ip );
}
check( 'first 6 messages in a minute are allowed', array( 0, 0, 0, 0, 0, 0 ) === $waits );

$GLOBALS['va_now'] += 20;
$w = VA_RateLimit::ip_wait( $ip );
check( '7th within the minute is refused with the time left in the window', 40 === $w, "wait={$w}" );

$GLOBALS['va_now'] += 41;
check( 'allowed again once the minute window ends', 0 === VA_RateLimit::ip_wait( $ip ) );

// Hourly cap: 30 per hour. 7 used so far this hour.
for ( $i = 0; $i < 23; $i++ ) {
	if ( 0 === $i % 6 ) {
		$GLOBALS['va_now'] += 61; // stay under the per-minute cap
	}
	VA_RateLimit::ip_wait( $ip );
}
$w = VA_RateLimit::ip_wait( $ip );
check( 'hourly cap reports minutes, not seconds', $w > 60 && $w <= HOUR_IN_SECONDS, "wait={$w}" );

// A script keeps firing while limited: the lock escalates to 15 minutes.
$ip2 = 'iphashB';
for ( $i = 0; $i < 6; $i++ ) {
	VA_RateLimit::ip_wait( $ip2 );
}
$last = 0;
for ( $i = 0; $i < VA_RateLimit::STRIKES_BEFORE_LOCK; $i++ ) {
	$last = VA_RateLimit::ip_wait( $ip2 );
}
check( 'persistent sending while limited escalates to the 15-minute lock', VA_RateLimit::LOCK_SECONDS === $last, "wait={$last}" );
$GLOBALS['va_now'] += 120;
check( 'the lock outlives the original 1-minute window', VA_RateLimit::ip_wait( $ip2 ) > 0 );
$GLOBALS['va_now'] += VA_RateLimit::LOCK_SECONDS;
check( 'the lock lifts after 15 minutes', 0 === VA_RateLimit::ip_wait( $ip2 ) );

// Counters written by the previous build were bare integers.
$GLOBALS['va_transients']['va_rl_ipm_legacy'] = array( 'v' => 6, 'exp' => $GLOBALS['va_now'] + 30 );
check( 'legacy integer counter is still honoured', VA_RateLimit::ip_wait( 'legacy' ) > 0 );

check( 'limits off (0) never block', ( function () {
	$GLOBALS['va_options']['va_rate_ip_minute'] = 0;
	$GLOBALS['va_options']['va_rate_ip_hourly'] = 0;
	for ( $i = 0; $i < 100; $i++ ) {
		if ( 0 !== VA_RateLimit::ip_wait( 'iphashC' ) ) {
			return false;
		}
	}
	return true;
} )() );

echo "\n{$pass} passed, {$fail} failed\n";
exit( $fail ? 1 : 0 );
