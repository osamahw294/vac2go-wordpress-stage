<?php
/**
 * Spend accounting fixtures, runnable WITHOUT WordPress.
 *
 *   php tests/spend-fixtures.php
 *
 * The advisor's model is Claude Fable 5.1: $10 per million input tokens, $50 output,
 * $0.25 cache read, and cache writes at 1.25x input (5-minute TTL). The plugin
 * originally shipped $3 / $15 / $0.30, which understated spend about threefold.
 */

// phpcs:disable

if ( PHP_SAPI !== 'cli' ) {
	header( 'HTTP/1.1 404 Not Found' );
	exit;
}

define( 'ABSPATH', __DIR__ );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'DAY_IN_SECONDS', 86400 );

$GLOBALS['va_options'] = array();
function get_option( $k, $d = false ) {
	return array_key_exists( $k, $GLOBALS['va_options'] ) ? $GLOBALS['va_options'][ $k ] : $d;
}
function update_option( $k, $v ) {
	$GLOBALS['va_options'][ $k ] = $v;
	return true;
}

require_once __DIR__ . '/../includes/class-va-db.php';
require_once __DIR__ . '/../includes/class-va-ratelimit.php';

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
function near( $a, $b ) {
	return abs( $a - $b ) < 0.00001;
}
$zero = array( 'input' => 0, 'output' => 0, 'cache_creation' => 0, 'cache_read' => 0 );

echo "\n== Default prices are Claude Fable 5.1's ==\n";
check( '1M input tokens cost $10', near( VA_DB::spend_for( array_merge( $zero, array( 'input' => 1000000 ) ) ), 10.0 ) );
check( '1M output tokens cost $50', near( VA_DB::spend_for( array_merge( $zero, array( 'output' => 1000000 ) ) ), 50.0 ) );
check( '1M cache-read tokens cost $0.25', near( VA_DB::spend_for( array_merge( $zero, array( 'cache_read' => 1000000 ) ) ), 0.25 ) );
check( '1M cache-write tokens cost $12.50 (1.25x input)', near( VA_DB::spend_for( array_merge( $zero, array( 'cache_creation' => 1000000 ) ) ), 12.5 ) );

$turn = array( 'input' => 404, 'output' => 256, 'cache_creation' => 954, 'cache_read' => 3170 );
$cost = VA_DB::spend_for( $turn );
check( 'an average staging turn costs about 3 cents', $cost > 0.025 && $cost < 0.035, (string) $cost );

echo "\n== Old default prices are migrated, custom prices are kept ==\n";
$GLOBALS['va_options'] = array( 'va_price_in_per_m' => 3.0, 'va_price_out_per_m' => 15.0, 'va_price_cache_read_per_m' => 0.30 );
VA_DB::migrate_prices();
check( 'old default input price becomes 10', near( (float) get_option( 'va_price_in_per_m' ), 10.0 ) );
check( 'old default output price becomes 50', near( (float) get_option( 'va_price_out_per_m' ), 50.0 ) );
check( 'old default cache-read price becomes 0.25', near( (float) get_option( 'va_price_cache_read_per_m' ), 0.25 ) );

$GLOBALS['va_options'] = array( 'va_price_in_per_m' => 7.5, 'va_price_out_per_m' => 15.0, 'va_price_cache_read_per_m' => 0.30 );
VA_DB::migrate_prices();
check( 'an admin-edited price set is left alone', near( (float) get_option( 'va_price_in_per_m' ), 7.5 ) && near( (float) get_option( 'va_price_out_per_m' ), 15.0 ) );

echo "\n== Daily ceiling in US dollars ==\n";
check( 'under 80% of the dollar ceiling is ok', 'ok' === VA_RateLimit::budget_state( 10.0, 20.0 ) );
check( 'at 80% of the dollar ceiling warns', 'warn' === VA_RateLimit::budget_state( 16.0, 20.0 ) );
check( 'at 100% of the dollar ceiling is over', 'over' === VA_RateLimit::budget_state( 20.0, 20.0 ) );
check( 'a ceiling of 0 means unlimited', 'ok' === VA_RateLimit::budget_state( 9999.0, 0.0 ) );

echo "\n== Stats helpers ==\n";
check( 'cache hit rate = cache reads / all input-side tokens', near( VA_DB::cache_hit_rate( array( 'input' => 100, 'output' => 999, 'cache_creation' => 100, 'cache_read' => 800 ) ), 0.8 ) );
check( 'cache hit rate with no traffic is 0', near( VA_DB::cache_hit_rate( $zero ), 0.0 ) );
check( 'average cost per conversation', near( VA_DB::per_conversation( 3.0, 12 ), 0.25 ) );
check( 'average cost with no conversations is 0', near( VA_DB::per_conversation( 3.0, 0 ), 0.0 ) );

echo "\n{$pass} passed, {$fail} failed\n";
exit( $fail ? 1 : 0 );
