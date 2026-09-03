<?php
/**
 * Review Queue admin view.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Insufficient permissions.' );
}

$current_filter = isset( $_GET['va_filter'] ) ? sanitize_key( $_GET['va_filter'] ) : 'all';
$filters        = array(
	'all'       => 'All',
	'filtered'  => 'Flagged by filter',
	'incorrect' => 'Marked incorrect',
	'contact'   => 'Has contact info',
);

$table = new VA_List_Table();
$table->prepare_items();

$export_url = wp_nonce_url(
	admin_url( 'admin-post.php?action=va_export_csv&va_filter=' . rawurlencode( $current_filter ) ),
	'va_export_csv'
);
?>
<div class="wrap va-advisor-wrap">
	<h1 class="wp-heading-inline">Vac2Go Advisor Review Queue</h1>
	<a href="<?php echo esc_url( $export_url ); ?>" class="page-title-action">Export to CSV</a>
	<hr class="wp-header-end">

	<p class="description">Every question and answer is logged here, including real recommendations, "I don't know" replies, and refusals alike. Mark any answer incorrect and record how it should have been answered so the KB can be improved.</p>

	<ul class="subsubsub">
		<?php
		$i = 0;
		foreach ( $filters as $key => $label ) :
			$i++;
			$url = add_query_arg(
				array(
					'page'      => 'va-advisor',
					'va_filter' => $key,
				),
				admin_url( 'admin.php' )
			);
			$class = ( $current_filter === $key ) ? 'current' : '';
			?>
			<li>
				<a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $class ); ?>"><?php echo esc_html( $label ); ?></a><?php echo ( $i < count( $filters ) ) ? ' |' : ''; ?>
			</li>
		<?php endforeach; ?>
	</ul>

	<form method="get">
		<input type="hidden" name="page" value="va-advisor">
		<input type="hidden" name="va_filter" value="<?php echo esc_attr( $current_filter ); ?>">
		<?php $table->display(); ?>
	</form>
</div>
