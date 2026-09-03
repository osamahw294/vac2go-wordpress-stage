<?php
/**
 * Stats admin view: today's traffic, tokens, spend, filter/judge/error activity.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Insufficient permissions.' );
}

global $wpdb;
$table    = VA_DB::table();
$midnight = gmdate( 'Y-m-d 00:00:00', current_time( 'timestamp' ) );

$turns_today  = VA_DB::count_today();
$tokens       = VA_DB::tokens_today();
$spend        = VA_DB::estimated_spend_today();
$ceiling      = (int) get_option( 'va_daily_token_ceiling', 0 );
$total_tokens = $tokens['input'] + $tokens['output'] + $tokens['cache_creation'] + $tokens['cache_read'];

// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- table name is internal.
$stage_rows = $wpdb->get_results( $wpdb->prepare( "SELECT filter_stage, COUNT(*) c FROM {$table} WHERE created_at >= %s AND filter_stage IS NOT NULL GROUP BY filter_stage", $midnight ), ARRAY_A );
$error_rows = $wpdb->get_results( $wpdb->prepare( "SELECT error_type, COUNT(*) c FROM {$table} WHERE created_at >= %s AND error_type IS NOT NULL AND error_type <> '' GROUP BY error_type", $midnight ), ARRAY_A );
$top_ips    = $wpdb->get_results( $wpdb->prepare( "SELECT LEFT(ip_hash,12) ip, COUNT(*) c FROM {$table} WHERE created_at >= %s AND ip_hash IS NOT NULL GROUP BY ip_hash ORDER BY c DESC LIMIT 10", $midnight ), ARRAY_A );
// phpcs:enable

$breaker_until  = (int) get_option( 'va_breaker_until', 0 );
$breaker_reason = get_option( 'va_breaker_last_reason', '' );
?>
<div class="wrap va-advisor-wrap">
	<h1>Vac2Go Advisor Stats (today)</h1>

	<?php if ( ! get_option( 'va_enabled', 1 ) ) : ?>
		<div class="notice notice-error inline"><p><strong>The advisor is currently DISABLED</strong> (kill switch in Settings).</p></div>
	<?php endif; ?>
	<?php if ( $breaker_until > time() ) : ?>
		<div class="notice notice-warning inline"><p><strong>Circuit breaker active</strong> until <?php echo esc_html( gmdate( 'H:i:s', $breaker_until ) ); ?> UTC. Last reason: <?php echo esc_html( $breaker_reason ); ?></p></div>
	<?php endif; ?>

	<table class="widefat striped" style="max-width:640px">
		<tbody>
			<tr><td><strong>Turns logged today</strong></td><td><?php echo (int) $turns_today; ?></td></tr>
			<tr><td><strong>Input tokens</strong></td><td><?php echo number_format( $tokens['input'] ); ?></td></tr>
			<tr><td><strong>Output tokens</strong></td><td><?php echo number_format( $tokens['output'] ); ?></td></tr>
			<tr><td><strong>Cache creation tokens</strong></td><td><?php echo number_format( $tokens['cache_creation'] ); ?></td></tr>
			<tr><td><strong>Cache read tokens</strong></td><td><?php echo number_format( $tokens['cache_read'] ); ?></td></tr>
			<tr><td><strong>Total vs ceiling</strong></td><td><?php echo number_format( $total_tokens ); ?> / <?php echo $ceiling > 0 ? number_format( $ceiling ) : 'unlimited'; ?></td></tr>
			<tr><td><strong>Estimated spend</strong></td><td>$<?php echo esc_html( number_format( $spend, 4 ) ); ?></td></tr>
		</tbody>
	</table>

	<h2>Filter and judge activity</h2>
	<table class="widefat striped" style="max-width:640px">
		<thead><tr><th>Stage</th><th>Blocks today</th></tr></thead>
		<tbody>
		<?php if ( $stage_rows ) : foreach ( $stage_rows as $r ) : ?>
			<tr><td><?php echo esc_html( $r['filter_stage'] ); ?></td><td><?php echo (int) $r['c']; ?></td></tr>
		<?php endforeach; else : ?>
			<tr><td colspan="2">No filter activity today.</td></tr>
		<?php endif; ?>
		</tbody>
	</table>

	<h2>Error types</h2>
	<table class="widefat striped" style="max-width:640px">
		<thead><tr><th>Error type</th><th>Count today</th></tr></thead>
		<tbody>
		<?php if ( $error_rows ) : foreach ( $error_rows as $r ) : ?>
			<tr><td><?php echo esc_html( $r['error_type'] ); ?></td><td><?php echo (int) $r['c']; ?></td></tr>
		<?php endforeach; else : ?>
			<tr><td colspan="2">No errors today.</td></tr>
		<?php endif; ?>
		</tbody>
	</table>

	<h2>Top IP hashes by volume</h2>
	<table class="widefat striped" style="max-width:640px">
		<thead><tr><th>IP hash (truncated)</th><th>Turns today</th></tr></thead>
		<tbody>
		<?php if ( $top_ips ) : foreach ( $top_ips as $r ) : ?>
			<tr><td><code><?php echo esc_html( $r['ip'] ); ?></code></td><td><?php echo (int) $r['c']; ?></td></tr>
		<?php endforeach; else : ?>
			<tr><td colspan="2">No traffic today.</td></tr>
		<?php endif; ?>
		</tbody>
	</table>
</div>
