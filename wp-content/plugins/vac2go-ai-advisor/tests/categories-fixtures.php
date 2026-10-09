<?php
/**
 * Category knowledge fixtures, runnable WITHOUT WordPress.
 *
 *   php tests/categories-fixtures.php
 *
 * kb/categories/ holds one file per advisor category. Eight are built from the
 * client's Round 2 entries by phase-2/tools/build_categories.py (word for word apart
 * from the logged edits in EDITS.md); Industrial Vacuum and Water are drafts until the
 * client sends their entries. Where the Round 2 source text is present (it is a working
 * file, not in git), every number in every summary is traced back to it.
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
function numbers_in( $text ) {
	$text = preg_replace( '/vac2go/i', '', $text );
	preg_match_all( '/\d[\d,]*(?:\.\d+)?/', $text, $m );
	return array_values( array_unique( array_map( function ( $n ) {
		return str_replace( ',', '', $n );
	}, $m[0] ) ) );
}

$dir     = __DIR__ . '/../kb/categories';
$round2  = realpath( __DIR__ . '/../../../../phase-2/work/extracted-text/kb-round2.txt' );
$facts   = realpath( __DIR__ . '/../../../../phase-2/work/unit-facts' );
$sources = '';
if ( $round2 ) {
	$sources .= file_get_contents( $round2 );
}
if ( $facts ) {
	foreach ( glob( $facts . '/*.md' ) as $f ) {
		$sources .= file_get_contents( $f );
	}
}
echo $round2 ? "(trace check ON)\n" : "(trace check SKIPPED: Round 2 source text not present)\n";

$questions = 0;
foreach ( VA_Fleet::categories() as $id => $c ) {
	$path = "{$dir}/{$id}.md";
	check( "{$id}: file exists", is_readable( $path ) );
	if ( ! is_readable( $path ) ) {
		continue;
	}
	$text = file_get_contents( $path );
	check( "{$id}: title is the category name", 0 === strpos( $text, '# ' . $c['name'] . "\n" ) );
	check( "{$id}: has a Summary", false !== strpos( $text, "## Summary\n" ) );
	check( "{$id}: no em dashes", false === strpos( $text, '—' ) );
	$questions += substr_count( $text, '**Q: ' );

	if ( in_array( $id, array( 'industrial-vacuum', 'water' ), true ) ) {
		check( "{$id}: marked as a draft", 0 === strpos( substr( $text, strlen( '# ' . $c['name'] . "\n\n" ) ), 'Draft.' ) );
	}

	if ( $round2 && preg_match( "/## Summary\n\n(.*?)\n\n/s", $text, $m ) ) {
		$have    = numbers_in( $sources );
		$missing = array_diff( numbers_in( $m[1] ), $have );
		check( "{$id}: every number in the summary traces to the client's material", array() === $missing, implode( ', ', $missing ) );
	}
}
check( 'all 173 Round 2 questions are present', 173 === $questions, "found {$questions}" );

$edits = (string) @file_get_contents( "{$dir}/EDITS.md" );
check( 'EDITS.md logs 9 edits', 9 === substr_count( $edits, '- **Was:**' ) );
check( 'EDITS.md lists the 4 advisor notes', 4 === preg_match_all( '/^- \*\*[A-Za-z -]+\*\*, after "/m', $edits ) );

echo "\n{$pass} passed, {$fail} failed\n";
exit( $fail ? 1 : 0 );
