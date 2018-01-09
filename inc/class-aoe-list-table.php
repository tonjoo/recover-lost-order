<?php

/**
 * Abandoned Order List Table
 */
class AOE_List_Table extends WP_List_Table {

	/**
	 * Model object
	 *
	 * @var object
	 */
	private $model;


	/**
	 * Functions object
	 *
	 * @var object
	 */
	private $functions;
	/**
	 * Constructor
	 */
	public function __construct() {
		global $aoe_functions;
		parent::__construct(
			array(
				'singular'  => __( 'Abandoned Order', 'aoe' ),
				'plural'    => __( 'Abandoned Orders', 'aoe' ),
				'ajax'      => false,
			)
		);
		$this->model        = new AOE_Model();
		$this->functions    = $aoe_functions;
		$this->process_bulk_action();
	}

	/*
	public function extra_tablenav( $which ) {
		if ( $which == 'top' ) {
			echo 'Top';
		}
		if ( $which == 'bottom' ) {
			echo 'Bottom';
		}
	}
	*/

	/**
	 * Set Table Columns
	 *
	 * @return array Table Columns
	 */
	public function get_columns() {
		$table_columns = array(
			'cb'            => '<input type="checkbox" />',
			'order_id'      => __( 'Order ID', 'aoe' ),
			'order_total'   => __( 'Total', 'aoe' ),
			'payment'       => __( 'Payment', 'aoe' ),
			'user'          => __( 'User', 'aoe' ),
			'order_date'    => __( 'Date', 'aoe' ),
			'is_email_sent' => __( 'Email Sent', 'aoe' ),
			'is_recovered'  => __( 'Recovered', 'aoe' ),
			'actions'       => __( 'Actions', 'aoe' ),
		);
		return $table_columns;
	}

	/**
	 * Set sortable columns
	 *
	 * @return array Sortable columns
	 */
	protected function get_sortable_columns() {
		$sortable_columns = array(
			'order_id'      => array( 'order_id', true ),
			'order_total'   => array( 'order_total', true ),
			'payment'       => array( 'payment', true ),
			'order_date'    => array( 'order_date', true ),
			'user'          => array( 'user', true ),
		);
		return $sortable_columns;
	}

	/**
	 * Set bulk actions
	 *
	 * @return array Actions.
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-send'    => __( 'Send Email', 'aoe' ),
			// 'bulk-remove'  => __( 'Remove', 'aoe' ), @TODO
		);
		return $actions;
	}

	/**
	 * Display no order text
	 */
	public function no_items() {
		esc_html_e( 'No abandoned order available.', 'aoe' );
	}

	/**
	 * Column Checkbox
	 *
	 * @param  array $item Item array.
	 */
	protected function column_cb( $item ) {
		return sprintf(
			'<label class="screen-reader-text" for="order_' . $item['order_id'] . '">' . sprintf( __( 'Select %s', 'aoe' ), $item['order_id'] ) . '</label>'
			. "<input type='checkbox' name='orders[]' id='order_{$item['order_id']}' value='{$item['order_id']}' />"
		);
	}

	/**
	 * Column Order ID
	 *
	 * @param  array $item Item array.
	 */
	protected function column_order_id( $item ) {
		$order_id = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'post.php?post=' . $item['order_id'] . '&action=edit' ),
			'<strong>#' . $item['order_id'] . '</strong>'
		);
		$recover_from = get_post_meta( $item['order_id'], 'aoe_recover_from', true );
		if ( ! empty( $recover_from ) ) {
			$order_id .= sprintf( __( '<br>(recover from #%d)','aoe' ), $recover_from );
		}
		return $order_id;
	}

	/**
	 * Column Order Total
	 *
	 * @param  array $item Item array.
	 */
	protected function column_order_total( $item ) {
		global  $woocommerce;
		$order = wc_get_order( $item['order_id'] );
		if ( is_a( $order, 'WC_Order' ) ) {
			return wc_price( $order->get_total() );
		}
		return '-';
	}

	/**
	 * Column Order Total
	 *
	 * @param  array $item Item array.
	 */
	protected function column_payment( $item ) {
		return $item['payment'];
	}

	/**
	 * Column User
	 *
	 * @param  array $item Item array.
	 */
	protected function column_user( $item ) {
		$user = get_userdata( $item['user_id'] );
		if ( $user ) {
			return sprintf(
				'<a href="%s">%s</a><br>%s',
				admin_url( 'user-edit.php?user_id=' . $item['user_id'] . '&wp_http_referer=%2Fwp-admin%2Fadmin.php?page=abandoned_order_dashboard' ),
				$user->display_name,
				$user->user_email
			);
		}
	}

	/**
	 * Column Order Date
	 *
	 * @param  array $item Item array.
	 */
	protected function column_order_date( $item ) {
		return $item['order_date'];
	}

	/**
	 * Column Email Sent
	 *
	 * @param  array $item Item array.
	 */
	protected function column_is_email_sent( $item ) {
		$is_email_sent = get_post_meta( $item['order_id'], 'aoe_email_sent', true );
		if ( intval( $is_email_sent ) > 0 ) {
			return '<div class="email_sent"><span class="dashicons dashicons-yes"></span></div>';
		} else {
			return '<div class="email_sent"><span class="dashicons dashicons-no-alt"></span></div>';
		}
	}

	/**
	 * Column Email Sent
	 *
	 * @param  array $item Item array.
	 */
	protected function column_is_recovered( $item ) {
		global $woocommerce;
		if ( 'bacs' === $item['payment'] || 'cheque' === $item['payment'] ) {
			$order = wc_get_order( $item['order_id'] );
			if ( is_a( $order, 'WC_Order' ) ) {
				$status = $order->get_status();
			}
			if ( 'completed' === $status ) {
				return sprintf( '<div class="recovered"><span class="dashicons dashicons-yes"></span><span class="aoe-tooltip">%s</span></div>', esc_attr__( 'Recovered', 'aoe' ) );
			} elseif ( 'processing' === $status ) {
				return sprintf( '<div class="recovered"><span class="dashicons dashicons-clock"></span><span class="aoe-tooltip">%s</span></div>', esc_attr__( 'Waiting', 'aoe' ) );
			} else {
				return sprintf( '<div class="recovered"><span class="dashicons dashicons-no-alt"></span><span class="aoe-tooltip">%s</span></div>', esc_attr__( 'Not Recovered', 'aoe' ) );
			}
		} else {
			$recovered_order = get_post_meta( $item['order_id'], 'aoe_recovered', true );
			$status = '';
			if ( ! empty( $recovered_order ) ) {
				$order = wc_get_order( $recovered_order );
				if ( is_a( $order, 'WC_Order' ) ) {
					$status = $order->get_status();
				}
			}
			if ( 'completed' === $status ) {
				return sprintf( '<div class="recovered"><span class="dashicons dashicons-yes"></span><span class="aoe-tooltip">%s</span></div>', esc_attr__( 'Recovered', 'aoe' ) );
			} elseif ( 'on-hold' === $status || 'pending' === $status || 'processing' === $status ) {
				return sprintf( '<div class="recovered"><span class="dashicons dashicons-clock"></span><span class="aoe-tooltip">%s</span></div>', esc_attr__( 'Waiting', 'aoe' ) );
			} else {
				return sprintf( '<div class="recovered"><span class="dashicons dashicons-no-alt"></span><span class="aoe-tooltip">%s</span></div>', esc_attr__( 'Not Recovered', 'aoe' ) );
			}
		}
	}

	/**
	 * Column Actions
	 *
	 * @param  array $item Item array.
	 */
	protected function column_actions( $item ) {
		$is_email_sent = get_post_meta( $item['order_id'], 'aoe_email_sent', true );
		$button = '';
		if ( intval( $is_email_sent ) == 0 || true === aoe_get_option( 'debug' ) ) {
			$button .= sprintf(
				'<a class="button action" href="%s"><span class="dashicons dashicons-email"></span><span class="aoe-tooltip">%s</span></a>',
				wp_nonce_url( admin_url( 'admin.php?page=abandoned_order_dashboard&order_id=' . $item['order_id'] ), 'send_email', 'aoe_do' ),
				esc_html__( 'Send Email', 'aoe' )
			) . ' ';
		}
		/*
		@TODO
		$button .= sprintf(
			'<a class="button action" href="%s"><span class="dashicons dashicons-trash"></span><span class="aoe-tooltip">%s</span></a>',
			wp_nonce_url( admin_url( 'admin.php?page=abandoned_order_dashboard&order_id=' . $item['order_id'] ), 'remove', 'aoe_do' ),
			esc_html__( 'Remove', 'aoe' )
		);
		*/
		return $button;
	}

	/**
	 * Process Bulk Actions
	 */
	public function process_bulk_action() {
		if ( 'bulk-send' === $this->current_action() ) {
			global $aoe_functions;
			$success = 0;
			$failed = 0;
			foreach ( $_GET['orders'] as $order ) {
				if ( $aoe_functions->send_single_email( $order ) ) {
					$success++;
				} else {
					$failed++;
				}
			}
			aoe_add_notice( sprintf( __( '%1$d emails sent and %2$d failed.', 'aoe' ), $success, $failed ), 'updated' );
			wp_safe_redirect( admin_url( 'admin.php?page=abandoned_order_dashboard&notice' ) );
			exit;
		}
	}

	/**
	 * Prepare data
	 */
	public function prepare_items() {
		$orderby    = ( isset( $_GET['orderby'] ) ) ? esc_sql( sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ) : 'order_date'; // input var okay.
		$order      = ( isset( $_GET['order'] ) ) ? esc_sql( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) : 'desc';
		$per_page   = $this->get_items_per_page( 'orders_per_page' );
		$total      = $this->model->get_abandoned_orders( array(), true );
		$current_page = $this->get_pagenum();

		$args = array(
			'orderby'   => $orderby,
			'order'     => $order,
			'limit'     => $per_page,
			'offset'    => ( $per_page * $current_page) - $per_page,
		);
		$orders = $this->model->get_abandoned_orders( $args );
		$this->_column_headers  = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$this->items            = $orders;
		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total / $per_page ),
			)
		);
	}

}
