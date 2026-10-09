<?php
/**
 * Unit card fixtures, runnable WITHOUT WordPress.
 *
 *   php tests/cards-fixtures.php
 *
 * Every fleet unit has a card in kb/units/, in the layout from kb/units/CARD-FORMAT.md.
 * The trace check is the important part: every number a card shows a customer must
 * exist in that unit's verified fact file. The fact files are working files, not in
 * git (phase-2/work/unit-facts/), so the trace check is skipped where they are absent.
 */

// phpcs:disable

if ( PHP_SAPI !== 'cli' ) {
	header( 'HTTP/1.1 404 Not Found' );
	exit;
}

define( 'ABSPATH', __DIR__ );
function get_option( $k, $d = false ) {
	return $d;
}
require_once __DIR__ . '/../includes/class-va-fleet.php';
require_once __DIR__ . '/../includes/class-va-kb.php';
require_once __DIR__ . '/../includes/class-va-knowledge.php';

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

$facts_dir = realpath( __DIR__ . '/../../../../phase-2/work/unit-facts' );
$trace     = false !== $facts_dir && is_dir( $facts_dir );
echo $trace ? "(trace check ON: {$facts_dir})\n" : "(trace check SKIPPED: fact files not present)\n";

/** Number tokens: 5,300 / 27 / 0.25 / 1/4 → "5300", "27", "0.25", "1", "4". */
function numbers_in( $text ) {
	$text = preg_replace( '/vac2go/i', '', $text ); // the brand name is not a figure
	preg_match_all( '/\d[\d,]*(?:\.\d+)?/', $text, $m );
	$out = array();
	foreach ( $m[0] as $n ) {
		$out[] = str_replace( ',', '', $n );
	}
	return array_values( array_unique( $out ) );
}

$sections_required = array( '## Key specs', '## Not in our literature' );
$banned            = array( '—' => 'em dash', 'patented' => '"patented"', 'warranty' => 'warranty', '$' => 'a price' );

foreach ( VA_Fleet::units() as $id => $u ) {
	$raw = VA_KB::card_raw( $id );
	check( "{$id}: card exists", null !== $raw );
	if ( null === $raw ) {
		continue;
	}
	check( "{$id}: title is the fleet name", 0 === strpos( $raw, '# ' . $u['name'] . "\n" ), strtok( $raw, "\n" ) );

	$is_thin = null === $u['fact_file'] || 'super-products-high-dump' === $id; // no literature of their own
	foreach ( $sections_required as $s ) {
		if ( '## Key specs' === $s && $is_thin ) {
			continue; // no literature of their own; these cards carry no spec list
		}
		check( "{$id}: has section {$s}", false !== strpos( $raw, $s ) );
	}

	// Every Key specs bullet that states a figure carries a source tag (CARD-FORMAT
	// rule 2). A line with no figure ("See the Guzzler Classic card") needs none.
	if ( preg_match( '/## Key specs\n(.*?)(\n## |\z)/s', $raw, $m ) ) {
		foreach ( preg_split( '/\n/', trim( $m[1] ) ) as $line ) {
			if ( 0 === strpos( ltrim( $line ), '- ' ) && array() !== numbers_in( $line ) ) {
				check( "{$id}: spec line has a {src:} tag", false !== strpos( $line, '{src:' ), $line );
			}
		}
	}

	$card = VA_KB::card( $id );
	check( "{$id}: source tags are stripped for the model", false === strpos( $card, '{src' ) );
	foreach ( $banned as $needle => $what ) {
		check( "{$id}: contains no {$what}", false === stripos( $card, $needle ) );
	}
	$words = str_word_count( $card );
	check( "{$id}: about 500 words at most (has {$words})", $words <= 560 );

	if ( ! $trace ) {
		continue;
	}
	// The source text this card may quote from.
	if ( 'gapvax-hv-57' === $id ) {
		// The HV-57 card comes from the Phase 1 knowledge base, frozen here, plus the
		// brochures the client sent with their feedback.
		$source = (string) file_get_contents( __DIR__ . '/fixtures/phase1-hv57.txt' )
			. (string) @file_get_contents( $facts_dir . '/' . $u['fact_file'] );
	} else {
		$files = array();
		if ( ! empty( $u['fact_file'] ) ) {
			$files[] = $u['fact_file'];
		}
		if ( 'super-products-high-dump' === $id ) {
			$files[] = 'camel-max.md';
		}
		if ( 'tractors' === $id ) {
			$files = array();
		}
		$source = '';
		foreach ( $files as $f ) {
			$source .= (string) @file_get_contents( $facts_dir . '/' . $f );
		}
	}
	// Numbers in unit names (MC1312, 70-BBL, 2100i, or another unit a card points to,
	// like the MC1510) come from the fleet list, not from a spec sheet.
	$source .= ' ' . $u['name'] . ' ' . implode( ' ', $u['aliases'] ) . ' ' . implode( ' ', array_column( VA_Fleet::units(), 'name' ) );
	$have    = numbers_in( $source );
	$missing = array();
	foreach ( numbers_in( $card ) as $n ) {
		if ( ! in_array( $n, $have, true ) ) {
			$missing[] = $n;
		}
	}
	check( "{$id}: every number traces to its fact file", array() === $missing, 'untraced: ' . implode( ', ', $missing ) );
}

echo "\n{$pass} passed, {$fail} failed\n";
exit( $fail ? 1 : 0 );
