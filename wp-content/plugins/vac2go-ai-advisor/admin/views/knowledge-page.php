<?php
/**
 * Knowledge admin view: what the advisor knows, category by category and unit by unit.
 *
 * Read-only. The knowledge lives in the plugin's kb/ files (categories built from the
 * client's Round 2 entries, unit cards built from the manufacturer literature), which
 * are reviewed and versioned like code. Wrong answers are fixed per question with a
 * correction in the Review Queue.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Insufficient permissions.' );
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view selector.
$selected = isset( $_GET['unit'] ) ? sanitize_key( wp_unslash( $_GET['unit'] ) ) : '';
$unit     = '' !== $selected ? VA_Fleet::unit( $selected ) : null;
$base_url = admin_url( 'admin.php?page=va-advisor-knowledge' );
$tokens   = function ( $text ) {
	return (int) ceil( strlen( (string) $text ) / 4 );
};
?>
<div class="wrap va-advisor-wrap">
	<h1>Vac2Go Advisor Knowledge</h1>

<?php if ( null !== $unit ) : ?>
	<p><a href="<?php echo esc_url( $base_url ); ?>">&larr; All categories and units</a></p>
	<h2><?php echo esc_html( $unit['name'] ); ?></h2>
	<p>
		<strong>Category:</strong> <?php echo esc_html( VA_Fleet::categories()[ $unit['category'] ]['name'] ); ?>
		&nbsp; <strong>Website group:</strong> <?php echo esc_html( $unit['website_group'] ); ?>
		&nbsp; <strong>Size:</strong> about <?php echo (int) $tokens( VA_KB::card( $selected ) ); ?> tokens
	</p>
	<p><strong>Sources:</strong> <?php echo esc_html( implode( '; ', $unit['sources'] ) ); ?></p>
	<p><strong>Names that resolve to this unit:</strong> <?php echo esc_html( implode( ', ', $unit['aliases'] ) ); ?></p>
	<p class="description">The card as written. The <code>{src: …}</code> tags show where each figure comes from; they are removed before the advisor sees the card.</p>
	<pre style="white-space:pre-wrap;background:#fff;border:1px solid #dcdcde;padding:12px;max-width:960px"><?php echo esc_html( (string) VA_KB::card_raw( $selected ) ); ?></pre>

<?php else : ?>
	<p class="description">
		Everything the advisor answers from. Every request carries the rules plus a short core: each category's summary and the fleet list (about <?php echo (int) $tokens( VA_Knowledge::get_system_prompt() ); ?> tokens).
		When a conversation is about a category, that category's <strong>knowledge pack</strong> (its full questions and answers plus its unit cards) is added, at most <?php echo (int) VA_KB::MAX_PACKS; ?> per conversation.
		To change what the advisor says about something specific, use a correction in the Review Queue.
	</p>

	<h2>Categories</h2>
	<table class="widefat striped" style="max-width:960px">
		<thead><tr><th>Category</th><th>Source</th><th>Units</th><th>Pack size</th></tr></thead>
		<tbody>
		<?php foreach ( VA_Fleet::categories() as $cid => $c ) : ?>
			<?php
			$text  = (string) VA_KB::category( $cid );
			$draft = 0 === strpos( substr( $text, strlen( '# ' . $c['name'] . "\n\n" ) ), 'Draft.' );
			?>
			<tr>
				<td><strong><?php echo esc_html( $c['name'] ); ?></strong></td>
				<td><?php echo $draft ? '<span class="va-badge va-badge-incorrect">draft, awaiting client entries</span>' : 'Client Round 2 entries'; ?></td>
				<td><?php echo (int) count( VA_Fleet::units_in( $cid ) ); ?></td>
				<td>about <?php echo (int) $tokens( VA_KB::pack( $cid ) ); ?> tokens</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<h2>Units (<?php echo (int) count( VA_Fleet::units() ); ?>)</h2>
	<table class="widefat striped" style="max-width:960px">
		<thead><tr><th>Unit</th><th>Category</th><th>Sources</th><th>Card size</th></tr></thead>
		<tbody>
		<?php foreach ( VA_Fleet::units() as $uid => $u ) : ?>
			<tr>
				<td><a href="<?php echo esc_url( add_query_arg( 'unit', $uid, $base_url ) ); ?>"><strong><?php echo esc_html( $u['name'] ); ?></strong></a></td>
				<td><?php echo esc_html( VA_Fleet::categories()[ $u['category'] ]['name'] ); ?></td>
				<td><?php echo esc_html( implode( '; ', $u['sources'] ) ); ?></td>
				<td>about <?php echo (int) $tokens( VA_KB::card( $uid ) ); ?> tokens</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
</div>
