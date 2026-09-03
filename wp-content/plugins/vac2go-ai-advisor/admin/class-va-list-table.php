<?php
/**
 * WP_List_Table subclass for the advisor review queue.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class VA_List_Table extends WP_List_Table {

	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'advisor_row',
				'plural'   => 'advisor_rows',
				'ajax'     => false,
			)
		);
	}

	public function get_columns() {
		return array(
			'created_at'       => 'When',
			'session_id'       => 'Session',
			'question'         => 'Question',
			'answer'           => 'Answer',
			'contact'          => 'Contact',
			'was_filtered'     => 'Filtered',
			'marked_incorrect' => 'Status / Correction',
		);
	}

	protected function get_sortable_columns() {
		return array(
			'created_at' => array( 'created_at', true ),
		);
	}

	protected function column_default( $item, $column_name ) {
		return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '';
	}

	protected function column_created_at( $item ) {
		return esc_html( mysql2date( 'Y-m-d H:i', $item['created_at'] ) );
	}

	protected function column_session_id( $item ) {
		$delete_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=va_delete_session&session_id=' . rawurlencode( $item['session_id'] ) ),
			'va_delete_session'
		);
		$html  = '<code title="' . esc_attr( $item['session_id'] ) . '">' . esc_html( substr( $item['session_id'], 0, 8 ) ) . '</code>';
		$html .= '<div class="row-actions"><span class="trash"><a href="' . esc_url( $delete_url ) . '" onclick="return confirm(\'Delete ALL rows for this session? This cannot be undone.\');">Delete session data</a></span></div>';
		return $html;
	}

	protected function column_question( $item ) {
		return $this->expandable( $item['question'], 160 );
	}

	protected function column_answer( $item ) {
		$html = $this->expandable( $item['answer'], 220 );
		if ( ! empty( $item['raw_model_answer'] ) ) {
			$html .= '<div class="va-raw"><em>Pre-filter model text:</em> ' . esc_html( $item['raw_model_answer'] ) . '</div>';
		}
		return $html;
	}

	protected function column_contact( $item ) {
		$bits = array_filter( array( $item['contact_name'], $item['contact_email'], $item['contact_phone'] ) );
		// Escape each field individually, then join with a real <br> (escaping the whole
		// joined string would turn the intended line break into a literal "<br>").
		return $bits
			? implode( '<br>', array_map( 'esc_html', $bits ) )
			: '<span class="va-muted">None</span>';
	}

	protected function column_was_filtered( $item ) {
		if ( (int) $item['was_filtered'] === 1 ) {
			return '<span class="va-badge va-badge-filtered" title="' . esc_attr( $item['filter_reason'] ) . '">filtered</span>';
		}
		return '<span class="va-muted">no</span>';
	}

	protected function column_marked_incorrect( $item ) {
		$id = (int) $item['id'];
		$status = '';
		if ( (int) $item['marked_incorrect'] === 1 ) {
			$status .= '<div class="va-badge va-badge-incorrect">marked incorrect</div>';
			$status .= '<div class="va-correction"><strong>Correction:</strong> ' . esc_html( $item['correction_text'] ) . '</div>';
		}
		$status .= '<div class="va-correct-ui">';
		$status .= '<button type="button" class="button button-small va-mark-btn" data-log-id="' . $id . '">Mark incorrect / edit</button>';
		$status .= '<div class="va-correct-form" style="display:none;">';
		$status .= '<textarea class="va-correction-text" rows="3" placeholder="How should this have been answered?">' . esc_textarea( $item['correction_text'] ) . '</textarea>';
		$status .= '<button type="button" class="button button-primary button-small va-save-correction" data-log-id="' . $id . '">Save correction</button> ';
		$status .= '<span class="va-correct-status"></span>';
		$status .= '</div></div>';
		return $status;
	}

	/**
	 * Truncated text with a details/summary expander.
	 */
	private function expandable( $text, $limit ) {
		$text = (string) $text;
		if ( mb_strlen( $text ) <= $limit ) {
			return esc_html( $text );
		}
		$short = esc_html( mb_substr( $text, 0, $limit ) ) . '…';
		return '<details><summary>' . $short . '</summary>' . esc_html( $text ) . '</details>';
	}

	public function prepare_items() {
		global $wpdb;
		$table = VA_DB::table();

		$per_page     = 25;
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		$filter = isset( $_GET['va_filter'] ) ? sanitize_key( $_GET['va_filter'] ) : 'all';
		$where  = VA_Admin::filter_where( $filter );

		$orderby = 'created_at';
		$order   = ( isset( $_GET['order'] ) && 'asc' === strtolower( $_GET['order'] ) ) ? 'ASC' : 'DESC';

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where}" );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$items = $wpdb->get_results(
			$wpdb->prepare(
				// $table/$where are internal, not user input; orderby/order are whitelisted above.
				"SELECT * FROM {$table} {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
				$per_page,
				$offset
			),
			ARRAY_A
		);

		$this->items           = $items ? $items : array();
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
				'total_pages' => (int) ceil( $total / $per_page ),
			)
		);
	}
}
