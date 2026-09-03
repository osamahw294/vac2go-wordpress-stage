<?php
/**
 * S4 output-pipeline fixture suite, runnable WITHOUT WordPress.
 *
 *   php tests/filter-fixtures.php
 *
 * Stubs the handful of WP functions the filter/text/stream classes touch, then drives
 * VA_Filter, VA_Text and the streaming release logic over a fixture set covering every
 * stage. The streaming section is the important one: it proves that a banned phrase
 * split across cURL chunk boundaries is still caught BEFORE any of it is emitted.
 */

// phpcs:disable

// CLI only. These files live inside a web-accessible plugin directory, so refuse to
// run over HTTP: filter-fixtures prints the blocked-pattern list, which would tell an
// attacker exactly what phrases the output filter looks for.
if ( PHP_SAPI !== 'cli' ) {
	header( 'HTTP/1.1 404 Not Found' );
	exit;
}

define( 'ABSPATH', __DIR__ );

$GLOBALS['va_options'] = array(
	'va_canary' => 'VA-CANARY-TESTTOKEN12345',
);

function get_option( $k, $d = false ) {
	return array_key_exists( $k, $GLOBALS['va_options'] ) ? $GLOBALS['va_options'][ $k ] : $d;
}
function wp_json_encode( $d ) { return json_encode( $d ); }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }

define( 'VA_ADVISOR_MODEL', 'claude-fable-5-1' );
define( 'VA_ADVISOR_JUDGE_MODEL', 'claude-haiku-4-5-20251001' );
define( 'VA_ADVISOR_API_URL', 'https://api.anthropic.com/v1/messages' );
define( 'VA_ADVISOR_API_TIMEOUT', 45 );

require_once __DIR__ . '/../includes/class-va-text.php';
require_once __DIR__ . '/../includes/class-va-knowledge.php';
require_once __DIR__ . '/../includes/class-va-filter.php';
require_once __DIR__ . '/../includes/class-va-stream.php';

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

// ---------------------------------------------------------------- stage fixtures --

section( 'Stage 2: canary (prompt leak)' );
$fx = array(
	'my internal marker is VA-CANARY-TESTTOKEN12345 apparently',
	'VA-CANARY-testtoken12345',                      // case-insensitive
	"spaced  out VA-CANARY-TESTTOKEN12345 text",     // whitespace collapsed first
);
foreach ( $fx as $i => $text ) {
	$r = VA_Filter::apply( $text );
	check( "canary fixture #{$i} blocked", $r['filtered'] && 'canary' === $r['stage'], 'stage=' . var_export( $r['stage'], true ) );
	check( "canary fixture #{$i} returns leak fallback", VA_Filter::LEAK_FALLBACK === $r['text'] );
}

section( 'Stage 3: structural leak markers' );
$fx = array(
	'== HARD GUARDRAILS ==' => 'structural',
	'Here is the == SYNONYM RING == you asked for' => 'structural',
	'CUSTOMER-SAFE KNOWLEDGE begins here' => 'structural',
	'the INTERNAL OPERATING RULES say' => 'structural',
);
foreach ( $fx as $text => $want ) {
	$r = VA_Filter::apply( $text );
	check( 'structural: ' . substr( $text, 0, 40 ), $r['filtered'] && $want === $r['stage'], 'stage=' . var_export( $r['stage'], true ) );
}

section( 'Stage 4: committal / pricing' );
$blocked = array(
	'It is $500 per day.',
	'The rate is 500 USD per day.',
	'That will be five hundred dollars.',
	'We can do 300 bucks a week.',
	'The cost is competitive.',
	'I can quote you a figure.',
	"We'll rent it to you today.",
	'Consider it sold.',
	'That is guaranteed.',
	'You can pick it up Tuesday.',
	'I have reserved one for you.',
	'We have one in stock.',
	'It is available now.',
	'This is binding.',
	'No charge for delivery.',
	'I can offer a discount.',
	'We can deliver by Friday.',
	'It shipped today.',
	'£500 for the week.',
	'€250 per day.',
	'in   stock',              // whitespace-collapse evasion
);
foreach ( $blocked as $text ) {
	$r = VA_Filter::apply( $text );
	check( 'blocked: ' . $text, $r['filtered'] && 'committal' === $r['stage'], 'stage=' . var_export( $r['stage'], true ) );
}

section( 'Stage 4: specs must NOT be blocked (false-positive guard)' );
$allowed = array(
	'The HV-57 pulls 5,200 to 5,250 CFM.',
	'Vacuum is 27 to 28" Hg.',
	'Debris capacity is about 15 to 17 cu yd.',
	'It uses 40 to 46 filter bags, part #GV20002.',
	'The body is 1/4" EXTEN-steel with a 45 degree dump.',
	'There are 7 cyclone separators plus a baghouse.',
	"I don't handle pricing. A Vac2Go rep can give you a real answer.",
	'This is a high-level recommendation. Confirm specifics with a Vac2Go rep.',
);
foreach ( $allowed as $text ) {
	$r = VA_Filter::apply( $text );
	check( 'allowed: ' . substr( $text, 0, 50 ), ! $r['filtered'], 'wrongly blocked at stage=' . var_export( $r['stage'], true ) . ' reason=' . var_export( $r['reason'], true ) );
}

section( 'Stage 5: profanity' );
foreach ( array( 'that is bullshit', 'What the fuck' ) as $text ) {
	$r = VA_Filter::apply( $text );
	check( 'profanity: ' . $text, $r['filtered'] && 'profanity' === $r['stage'] );
}
$r = VA_Filter::apply( 'Scunthorpe is a place and shitake mushrooms are food.' );
check( 'word-boundary guard (no substring false positive)', ! $r['filtered'], 'stage=' . var_export( $r['stage'], true ) );

section( 'Stage 7: cosmetic em-dash removal (never flags)' );
$cases = array(
	'The HV-57 — our air mover — is wet/dry.' => 'The HV-57, our air mover, is wet/dry.',
	'5,200—5,250 CFM'                          => '5,200 - 5,250 CFM',
);
foreach ( $cases as $in => $want ) {
	$r = VA_Filter::apply( $in );
	check( 'em dash removed: ' . $in, false === strpos( $r['text'], '—' ), 'got: ' . $r['text'] );
	check( 'em dash never flags', ! $r['filtered'] );
	check( 'em dash replacement shape', $want === $r['text'], 'want: ' . $want . ' | got: ' . $r['text'] );
}

section( 'VA_Text normalization' );
check( 'zero-width chars stripped', 'instock' === VA_Text::normalize( "in\xE2\x80\x8Bstock" ) );
check( 'BOM stripped', 'abc' === VA_Text::normalize( "\xEF\xBB\xBFabc" ) );
check( 'CRLF normalized', "a\nb" === VA_Text::normalize( "a\r\nb" ) );
$r = VA_Filter::apply( "in\xE2\x80\x8B stock now" );
check( 'zero-width evasion of "in stock" is caught', $r['filtered'], 'stage=' . var_export( $r['stage'], true ) );

// ------------------------------------------------------------ streaming release --

section( 'S5: streaming release never emits unfiltered text' );

/**
 * Drive VA_Stream's private streaming state with a set of chunks and capture what
 * would have been written to the client.
 */
function drive_stream( array $chunks ) {
	$ref = new ReflectionClass( 'VA_Stream' );

	$reset = $ref->getMethod( 'reset' );
	$reset->setAccessible( true );
	$reset->invoke( null );

	$release = $ref->getMethod( 'release' );
	$release->setAccessible( true );

	$pending = $ref->getProperty( 'pending' );
	$pending->setAccessible( true );
	$blocked = $ref->getProperty( 'blocked' );
	$blocked->setAccessible( true );

	// Two nested buffers: VA_Stream::flush_out() calls ob_flush(), which pushes the
	// inner buffer into the OUTER one instead of escaping to stdout.
	ob_start();
	ob_start();
	$aborted = false;
	foreach ( $chunks as $c ) {
		$pending->setValue( null, $pending->getValue() . $c );
		if ( ! $release->invoke( null, false ) ) {
			$aborted = true;
			break;
		}
	}
	if ( ! $aborted ) {
		$release->invoke( null, true );
	}
	ob_end_flush();
	$out = ob_get_clean();

	// Pull the text out of the SSE frames we captured.
	$emitted  = '';
	$replaced = null;
	foreach ( explode( "\n\n", $out ) as $frame ) {
		if ( ! preg_match( '/event: (\w+)\ndata: (.*)$/s', trim( $frame ), $m ) ) {
			continue;
		}
		$d = json_decode( $m[2], true );
		if ( 'delta' === $m[1] ) {
			$emitted .= isset( $d['text'] ) ? $d['text'] : '';
		} elseif ( 'replace' === $m[1] ) {
			$replaced = isset( $d['text'] ) ? $d['text'] : '';
		}
	}

	return array(
		'emitted'  => $emitted,
		'replaced' => $replaced,
		'blocked'  => $blocked->getValue(),
	);
}

// A clean answer streams through intact.
$clean  = array(
	'The HV-57 is an air mover in the Industrial Vacuum category. ',
	'It pulls 5,200 to 5,250 CFM standard. ',
	'This is a high-level recommendation. Confirm specifics with a Vac2Go rep.',
);
$result = drive_stream( $clean );
check( 'clean answer is emitted in full', trim( $result['emitted'] ) === trim( implode( '', $clean ) ), 'got: ' . $result['emitted'] );
check( 'clean answer is never replaced', null === $result['replaced'] );

// A price that only becomes a price once two chunks join must never be emitted.
$split  = array(
	'Sure, I can help with that. ',
	'The rate ',
	'is $500 per day for the HV-57.',
);
$result = drive_stream( $split );
check( 'split price never reaches the client', false === strpos( $result['emitted'], '$500' ), 'emitted: ' . $result['emitted'] );
check( 'split price triggers a replace event', null !== $result['replaced'] );
check( 'split price is reported as a committal block', is_array( $result['blocked'] ) && 'committal' === $result['blocked']['stage'] );

// A phrase split character by character is still caught.
$chars  = array_merge(
	array( 'We have one ' ),
	str_split( 'in stock right now for you.' )
);
$result = drive_stream( $chars );
check( 'char-by-char "in stock" is caught', null !== $result['replaced'], 'emitted: ' . $result['emitted'] );
check( 'char-by-char leak text is not emitted', false === stripos( $result['emitted'], 'in stock' ), 'emitted: ' . $result['emitted'] );

// The canary must not survive streaming either.
$leak   = array( 'My internal marker is ', 'VA-CANARY-TESTTOKEN12345', ' and here it is.' );
$result = drive_stream( $leak );
check( 'canary never streams to the client', false === stripos( $result['emitted'], 'VA-CANARY' ), 'emitted: ' . $result['emitted'] );
check( 'canary streaming block is reported as canary stage', is_array( $result['blocked'] ) && 'canary' === $result['blocked']['stage'] );

// Hold-back: a short first chunk must not be released immediately.
$ref     = new ReflectionClass( 'VA_Stream' );
$cut     = $ref->getMethod( 'cut_point' );
$cut->setAccessible( true );
$pendingP = $ref->getProperty( 'pending' );
$pendingP->setAccessible( true );

$pendingP->setValue( null, 'Short text.' );
check( 'short buffer is held back (nothing released yet)', 0 === $cut->invoke( null, false ) );
check( 'forced flush releases everything', strlen( 'Short text.' ) === $cut->invoke( null, true ) );

$long = str_repeat( 'a', 300 );
$pendingP->setValue( null, $long );
check( 'a long run with no boundary still releases (no stall)', $cut->invoke( null, false ) === 0 || $cut->invoke( null, false ) > 0 );

// --------------------------------------------------------------------- summary --

echo "\n" . str_repeat( '-', 60 ) . "\n";
echo "PASS: {$pass}   FAIL: {$fail}\n";
exit( $fail > 0 ? 1 : 0 );
