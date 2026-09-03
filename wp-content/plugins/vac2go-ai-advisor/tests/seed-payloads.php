<?php
/**
 * Seed the two attacker payloads S9 needs, straight into the advisor log.
 *
 *   wp eval-file wp-content/plugins/vac2go-ai-advisor/tests/seed-payloads.php --skip-plugins=false
 *
 * Why not just send them through /chat? Because the per-IP rate limiter will refuse a
 * burst, and a rate-limited request is deliberately never logged, so no row would
 * exist to render. S9 tests how the ADMIN UI renders stored attacker text; how that
 * text reached the database is irrelevant to what is being verified.
 *
 * Safe to run repeatedly: it reuses one fixed session id and clears it first, so it
 * never accumulates junk. Remove the rows afterwards with:
 *
 *   wp eval-file .../tests/seed-payloads.php clean --skip-plugins=false
 *
 * Note the bare word 'clean', with no leading dashes: WP-CLI claims anything starting
 * with -- as its own flag, so it never reaches this script.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$session = '00000000-0000-4000-8000-00000000dead';
$argv_in = (array) ( $args ?? array() );
$clean   = in_array( 'clean', $argv_in, true ) || in_array( '--clean', $argv_in, true );

VA_DB::delete_session( $session );

if ( $clean ) {
	echo "Removed seeded payload rows for session {$session}\n";
	return;
}

$rows = array(
	array(
		'question' => '<img src=x onerror="window.__vaXss=1"> which vacuum truck do I need',
		'answer'   => 'Stored XSS probe. If you can read this as text in the Review Queue, output escaping is working.',
	),
	array(
		'question' => '=HYPERLINK("http://evil","click") which vacuum truck do I need',
		'answer'   => '=1+1 formula probe. The CSV export must prefix this with an apostrophe.',
	),
);

foreach ( $rows as $i => $r ) {
	$id = VA_DB::insert_turn(
		array(
			'session_id'   => $session,
			'request_id'   => null,
			'turn_index'   => $i,
			'question'     => $r['question'],
			'answer'       => $r['answer'],
			'was_filtered' => 0,
			'ip_hash'      => str_repeat( '0', 64 ),
			'user_agent'   => 'seed-payloads.php',
		)
	);
	echo ( $id ? "inserted row {$id}: " : 'FAILED: ' ) . substr( $r['question'], 0, 44 ) . "\n";
}

echo "\nSeeded. Run the admin suite, then clean up with:\n";
echo "  wp eval-file wp-content/plugins/vac2go-ai-advisor/tests/seed-payloads.php clean --skip-plugins=false\n";
