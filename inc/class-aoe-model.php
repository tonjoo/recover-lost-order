<?php

/**
 * Database model
 */
class AOE_Model {
	/**
	 * Get abandoned orders
	 *
	 * @param  array $options Query options.
	 * @param  bool  $get_count Get count or not.
	 * @return object          Order lists.
	 */
	public function get_abandoned_orders( $options = array(), $get_count = false ) {
		global $wpdb;

		$order_statuses = array( 'wc-on-hold', 'wc-pending' );
		$order_statuses_bacs = array( 'wc-on-hold', 'wc-pending', 'wc-completed' );

		$query = '';
		$param = array();

		if ( false === $get_count ) {
			$query .= 'SELECT result.order_id, result.order_date, result.payment, user.meta_value AS user_id, order_total.meta_value AS order_total FROM ( ';
		} else {
			$query .= 'SELECT COUNT(*) FROM ( ';
		}

		// querying orders besides BACS.
		$query .= 'SELECT posts.ID AS order_id, posts.post_date AS order_date, posts.post_status, posts.post_type, payment.meta_value AS payment ';
		$query .= "FROM $wpdb->posts posts ";
		$query .= "INNER JOIN $wpdb->postmeta payment ON ( posts.ID = payment.post_id AND payment.meta_key = %s AND payment.meta_value NOT IN (%s,%s) ) ";
		array_push( $param, '_payment_method', 'bacs', 'cheque' );
		$query .= 'WHERE posts.post_status IN (' . implode( ', ', array_fill( 0, count( $order_statuses ), '%s' ) ) . ') ';
		foreach ( $order_statuses as $status ) {
			array_push( $param, $status );
		}
		$query .= 'AND TIMESTAMPDIFF(SECOND, posts.post_date, %s) >= %d ';
		array_push( $param, date( 'Y-m-d H:i:s' ), aoe_get_threshold() );

		// querying orders with BACS.
		$query .= 'UNION ';
		$query .= 'SELECT posts.ID AS order_id, posts.post_date AS order_date, posts.post_status, posts.post_type, payment.meta_value AS payment ';
		$query .= "FROM $wpdb->posts posts ";
		$query .= "INNER JOIN $wpdb->postmeta payment ON ( posts.ID = payment.post_id AND payment.meta_key = %s AND payment.meta_value IN (%s,%s) ) ";
		array_push( $param, '_payment_method', 'bacs', 'cheque' );
		$query .= 'WHERE posts.post_status IN (' . implode( ', ', array_fill( 0, count( $order_statuses_bacs ), '%s' ) ) . ') ';
		foreach ( $order_statuses_bacs as $status ) {
			array_push( $param, $status );
		}
		$query .= 'AND TIMESTAMPDIFF(SECOND, posts.post_date, %s) >= %d ';
		array_push( $param, date( 'Y-m-d H:i:s' ), aoe_get_threshold( 'bacs' ) );

		$query .= ') AS result ';

		// join clause.
		$query .= "INNER JOIN $wpdb->postmeta user ON ( result.order_id = user.post_id AND user.meta_key = %s ) ";
		array_push( $param, '_customer_user' );
		$query .= "INNER JOIN $wpdb->postmeta abandoned ON ( result.order_id = abandoned.post_id AND abandoned.meta_key = %s ) ";
		array_push( $param, 'aoe_abandoned' );
		$query .= "INNER JOIN $wpdb->postmeta order_total ON ( result.order_id = order_total.post_id AND order_total.meta_key = %s ) ";
		array_push( $param, '_order_total' );

		// where clause.
		$query .= 'WHERE result.post_type = %s ';
		array_push( $param, 'shop_order' );
		if ( aoe_validate_date( aoe_get_option( 'start_date' ), 'Y-m-d' ) ) {
			$query .= 'AND result.order_date > %s ';
			array_push( $param, aoe_get_option( 'start_date' ) . ' 00:00:00' );
		}
		$query .= 'AND abandoned.meta_value = %s ';
		array_push( $param, '1' );
		if ( true === aoe_get_option( 'ignore_zero', true ) ) {
			$query .= 'AND order_total.meta_value > 0 ';
		}
		if ( false === aoe_get_option( 'apply_recover', true ) ) {
			$query .= 'AND NOT EXISTS ( SELECT * FROM ' . $wpdb->postmeta . ' AS is_recovered WHERE is_recovered.meta_key = %s AND is_recovered.meta_value IS NOT NULL AND is_recovered.post_id = result.order_id ) ';
			array_push( $param, 'aoe_recover_from' );
		}

		if ( false === $get_count ) {
			// orderby clause.
			if ( isset( $options['orderby'] ) && 'order_date' === $options['orderby'] ) {
				$query .= 'ORDER BY result.order_date ';
			} elseif ( isset( $options['orderby'] ) && 'user' === $options['orderby'] ) {
				$query .= 'ORDER BY user.meta_value ';
			} elseif ( isset( $options['orderby'] ) && 'order_total' === $options['orderby'] ) {
				$query .= 'ORDER BY cast(order_total.meta_value as unsigned) ';
			} elseif ( isset( $options['orderby'] ) && 'payment' === $options['orderby'] ) {
				$query .= 'ORDER BY result.payment ';
			} else {
				$query .= 'ORDER BY result.order_id ';
			}

			// order clause.
			if ( isset( $options['order'] ) && 'asc' === $options['order'] ) {
				$query .= 'ASC ';
			} else {
				$query .= 'DESC ';
			}

			// limit clause.
			$query .= 'LIMIT %d, %d ';
			array_push( $param, ( isset( $options['offset'] ) && is_integer( $options['offset'] ) ? $options['offset'] : 0 ), ( isset( $options['limit'] ) && is_integer( $options['limit'] ) ? $options['limit'] : 20 ) );
		}

		if ( false === $get_count ) {
			$results = $wpdb->get_results(
				$wpdb->prepare( $query, $param )
				, ARRAY_A
			);
		} else {
			$results = $wpdb->get_var(
				$wpdb->prepare( $query, $param )
			);
		}
		// echo $wpdb->prepare( $query, $param );
		return $results;
	}

	/**
	 * Get abandoned orders ids
	 *
	 * @return array Abandoned order ids
	 */
	public function get_abandoned_orders_ids() {
		global $wpdb;

		$order_statuses = array( 'wc-on-hold', 'wc-pending' );
		$order_statuses_bacs = array( 'wc-on-hold' );

		$query = '';
		$param = array();

		$query .= 'SELECT DISTINCT result.order_id FROM ( ';

		// querying orders besides BACS.
		$query .= 'SELECT posts.ID AS order_id, posts.post_date AS order_date, posts.post_status, posts.post_type, payment.meta_value AS payment ';
		$query .= "FROM $wpdb->posts posts ";
		$query .= "INNER JOIN $wpdb->postmeta payment ON ( posts.ID = payment.post_id AND payment.meta_key = %s AND payment.meta_value NOT IN (%s,%s) ) ";
		array_push( $param, '_payment_method', 'bacs', 'cheque' );
		$query .= 'WHERE posts.post_status IN (' . implode( ', ', array_fill( 0, count( $order_statuses ), '%s' ) ) . ') ';
		foreach ( $order_statuses as $status ) {
			array_push( $param, $status );
		}
		$query .= 'AND TIMESTAMPDIFF(SECOND, posts.post_date, %s) >= %d ';
		array_push( $param, date( 'Y-m-d H:i:s' ), aoe_get_threshold() );

		// querying orders with BACS.
		$query .= 'UNION ';
		$query .= 'SELECT posts.ID AS order_id, posts.post_date AS order_date, posts.post_status, posts.post_type, payment.meta_value AS payment ';
		$query .= "FROM $wpdb->posts posts ";
		$query .= "INNER JOIN $wpdb->postmeta payment ON ( posts.ID = payment.post_id AND payment.meta_key = %s AND payment.meta_value IN (%s,%s) ) ";
		array_push( $param, '_payment_method', 'bacs', 'cheque' );
		$query .= 'WHERE posts.post_status IN (' . implode( ', ', array_fill( 0, count( $order_statuses_bacs ), '%s' ) ) . ') ';
		foreach ( $order_statuses_bacs as $status ) {
			array_push( $param, $status );
		}
		$query .= 'AND TIMESTAMPDIFF(SECOND, posts.post_date, %s) >= %d ';
		array_push( $param, date( 'Y-m-d H:i:s' ), aoe_get_threshold( 'bacs' ) );

		$query .= ') AS result ';

		// join clause.
		$query .= "INNER JOIN $wpdb->postmeta order_total ON ( result.order_id = order_total.post_id AND order_total.meta_key = %s ) ";
		array_push( $param, '_order_total' );

		// where clause.
		$query .= 'WHERE result.post_type = %s ';
		array_push( $param, 'shop_order' );
		if ( aoe_validate_date( aoe_get_option( 'start_date' ), 'Y-m-d' ) ) {
			$query .= 'AND result.order_date > %s ';
			array_push( $param, aoe_get_option( 'start_date' ) . ' 00:00:00' );
		}
		$query .= 'AND NOT EXISTS ( SELECT * FROM ' . $wpdb->postmeta . ' AS email WHERE email.meta_key = %s AND cast(email.meta_value as unsigned) > %s AND email.post_id = result.order_id ) ';
		array_push( $param, 'aoe_email_sent', 0 );
		if ( true === aoe_get_option( 'ignore_zero', true ) ) {
			$query .= 'AND order_total.meta_value > 0 ';
		}
		if ( false === aoe_get_option( 'apply_recover', true ) ) {
			$query .= 'AND NOT EXISTS ( SELECT * FROM ' . $wpdb->postmeta . ' AS is_recovered WHERE is_recovered.meta_key = %s AND is_recovered.meta_value IS NOT NULL AND is_recovered.post_id = result.order_id ) ';
			array_push( $param, 'aoe_recover_from' );
		}

		$results = $wpdb->get_col(
			$wpdb->prepare( $query, $param )
		);
		// echo $wpdb->prepare( $query, $param );
		return $results;
	}

	/**
	 * Get Order ID from token
	 *
	 * @param  string $token Token.
	 * @return mixed         Order ID.
	 */
	public function get_order_id_from_token( $token ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s", array( 'aoe_recovery_token', $token ) ) );
	}

	/**
	 * Get abandoned order ids within given range
	 *
	 * @param  string $start_date Start date.
	 * @param  string $end_date   End date.
	 * @return array              Order IDs.
	 */
	public function get_abandoned_orders_ids_with_range( $start_date, $end_date ) {
		global $wpdb;

		$order_statuses = array( 'wc-on-hold', 'wc-pending' );
		$order_statuses_bacs = array( 'wc-on-hold', 'wc-pending', 'wc-completed' );

		$query = '';
		$param = array();

		$query .= 'SELECT DISTINCT result.order_id FROM ( ';

		// querying orders besides BACS.
		$query .= 'SELECT posts.ID AS order_id, posts.post_date AS order_date, posts.post_status, posts.post_type, payment.meta_value AS payment ';
		$query .= "FROM $wpdb->posts posts ";
		$query .= "INNER JOIN $wpdb->postmeta payment ON ( posts.ID = payment.post_id AND payment.meta_key = %s AND payment.meta_value NOT IN (%s,%s) ) ";
		array_push( $param, '_payment_method', 'bacs', 'cheque' );
		$query .= 'WHERE posts.post_status IN (' . implode( ', ', array_fill( 0, count( $order_statuses ), '%s' ) ) . ') ';
		foreach ( $order_statuses as $status ) {
			array_push( $param, $status );
		}
		$query .= 'AND TIMESTAMPDIFF(SECOND, posts.post_date, %s) >= %d ';
		array_push( $param, date( 'Y-m-d H:i:s' ), aoe_get_threshold() );

		// querying orders with BACS.
		$query .= 'UNION ';
		$query .= 'SELECT posts.ID AS order_id, posts.post_date AS order_date, posts.post_status, posts.post_type, payment.meta_value AS payment ';
		$query .= "FROM $wpdb->posts posts ";
		$query .= "INNER JOIN $wpdb->postmeta payment ON ( posts.ID = payment.post_id AND payment.meta_key = %s AND payment.meta_value IN (%s,%s) ) ";
		array_push( $param, '_payment_method', 'bacs', 'cheque' );
		$query .= 'WHERE posts.post_status IN (' . implode( ', ', array_fill( 0, count( $order_statuses_bacs ), '%s' ) ) . ') ';
		foreach ( $order_statuses_bacs as $status ) {
			array_push( $param, $status );
		}
		$query .= 'AND TIMESTAMPDIFF(SECOND, posts.post_date, %s) >= %d ';
		array_push( $param, date( 'Y-m-d H:i:s' ), aoe_get_threshold( 'bacs' ) );

		$query .= ') AS result ';

		// join clause.
		$query .= "INNER JOIN $wpdb->postmeta order_total ON ( result.order_id = order_total.post_id AND order_total.meta_key = %s ) ";
		array_push( $param, '_order_total' );
		$query .= "INNER JOIN $wpdb->postmeta abandoned ON ( result.order_id = abandoned.post_id AND abandoned.meta_key = %s AND abandoned.meta_value = %s ) ";
		array_push( $param, 'aoe_abandoned', '1' );

		// where clause.
		$query .= 'WHERE result.post_type = %s ';
		array_push( $param, 'shop_order' );
		if ( aoe_validate_date( aoe_get_option( 'start_date' ), 'Y-m-d' ) ) {
			$query .= 'AND result.order_date > %s ';
			array_push( $param, aoe_get_option( 'start_date' ) . ' 00:00:00' );
		}
		$query .= 'AND result.order_date BETWEEN %s AND %s ';
		array_push( $param, date( 'Y-m-d', strtotime( $start_date ) ) . ' 00:00:00', date( 'Y-m-d', strtotime( $end_date ) ) . ' 23:59:59' );
		if ( true === aoe_get_option( 'ignore_zero', true ) ) {
			$query .= 'AND order_total.meta_value > 0 ';
		}
		if ( false === aoe_get_option( 'apply_recover', true ) ) {
			$query .= 'AND NOT EXISTS ( SELECT * FROM ' . $wpdb->postmeta . ' AS is_recovered WHERE is_recovered.meta_key = %s AND is_recovered.meta_value IS NOT NULL AND is_recovered.post_id = result.order_id ) ';
			array_push( $param, 'aoe_recover_from' );
		}

		$results = $wpdb->get_col(
			$wpdb->prepare( $query, $param )
		);
		// print_r( $wpdb->prepare( $query, $param ) );
		return $results;
	}

	/**
	 * Ger recovered order ids within given range
	 *
	 * @param  string $start_date Start date.
	 * @param  string $end_date   End date.
	 * @return array              Order IDs.
	 */
	public function get_recovered_orders_ids_with_range( $start_date, $end_date ) {
		global $wpdb;
		$abandoned_order_ids = $this->get_abandoned_orders_ids_with_range( $start_date, $end_date );

		if ( empty( $abandoned_order_ids ) ) {
			return array();
		}

		$query = '';
		$param = array();

		$query .= 'SELECT DISTINCT order_id FROM ( ';
		$query .= 'SELECT posts.ID AS order_id, recovered_order.post_status AS order_status ';
		$query .= "FROM $wpdb->posts posts ";
		$query .= "INNER JOIN $wpdb->postmeta recovered ON ( posts.ID = recovered.post_id AND recovered.meta_key = %s ) ";
		array_push( $param, 'aoe_recovered' );
		$query .= "INNER JOIN $wpdb->posts recovered_order ON ( recovered.meta_value = recovered_order.ID ) ";
		$query .= 'UNION ';
		$query .= 'SELECT posts.ID AS order_id, posts.post_status AS order_status ';
		$query .= "FROM $wpdb->posts posts ";
		$query .= ') AS result ';

		$abandoned_clause = implode( ', ', array_fill( 0, count( $abandoned_order_ids ), '%d' ) );
		$query .= 'WHERE result.order_id IN (' . $abandoned_clause . ') ';
		foreach ( $abandoned_order_ids as $id ) {
			array_push( $param, $id );
		}
		$query .= 'AND result.order_status = %s ';
		array_push( $param, 'wc-completed' );

		$results = $wpdb->get_col(
			$wpdb->prepare( $query, $param )
		);
		// print_r( $wpdb->prepare( $query, $param ) );
		return $results;
	}

	public function get_total_recovered_with_range( $start_date, $end_date ) {
		global $wpdb;
		$recovered_order_ids = $this->get_recovered_orders_ids_with_range( $start_date, $end_date );

		if ( empty( $recovered_order_ids ) ) {
			return 0;
		}

		$query = '';
		$param = array();

		$query .= 'SELECT SUM(CAST(order_total.meta_value AS UNSIGNED)) AS order_id ';
		$query .= "FROM $wpdb->posts posts ";
		$query .= "INNER JOIN $wpdb->postmeta order_total ON ( posts.ID = order_total.post_id ) ";

		$recovered_clause = implode( ', ', array_fill( 0, count( $recovered_order_ids ), '%d' ) );
		$query .= 'WHERE posts.ID IN (' . $recovered_clause . ') ';
		foreach ( $recovered_order_ids as $id ) {
			array_push( $param, $id );
		}
		$query .= 'AND order_total.meta_key = %s ';
		array_push( $param, '_order_total' );

		$results = $wpdb->get_var(
			$wpdb->prepare( $query, $param )
		);
		// print_r( $wpdb->prepare( $query, $param ) );
		return $results;
	}
}
