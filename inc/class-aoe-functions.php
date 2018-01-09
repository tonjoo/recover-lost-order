<?php

/**
 * Core functions.
 */
class AOE_Functions {

	public function __construct() {
		$this->model = new AOE_Model();
	}

	/**
	 * Send single email
	 *
	 * @param  int $order_id Order ID.
	 */
	public function send_single_email( $order_id ) {
		global $woocommerce;
		$mailer = WC()->mailer();
		$mails = $mailer->get_emails();
		if ( isset( $mails['AOE_Email'] ) && $mails['AOE_Email']->trigger( $order_id ) ) { // fire email trigger.
			update_post_meta( $order_id, 'aoe_email_sent', 1 );
			update_post_meta( $order_id, 'aoe_recovery_token', $this->generate_recovery_token( $order_id ) );
			do_action( 'aoe_email_sent', $order_id );
			return true;
		}
		return false;
	}

	/**
	 * Generate recover token
	 *
	 * @param  int $order_id Order ID.
	 * @return string   Token.
	 */
	public function generate_recovery_token( $order_id ) {
		if ( ! $order_id ) {
			return;
		}
		return md5( $order_id );
	}

	/**
	 * Generate recovery URL
	 *
	 * @param  int $order_id Order ID.
	 */
	public function generate_recovery_url( $order_id ) {
		if ( ! $order_id ) {
			return;
		}
		$token = $this->generate_recovery_token( $order_id );
		if ( ! $token ) {
			return;
		}
		$token_name = apply_filters( 'aoe_token_variable', 'abandoned_order' );
		return site_url() . '?' . $token_name . '=' . $token;
	}

	/**
	 * Validate token on URL.
	 *
	 * @param  string $token Token.
	 * @return mixed         OrderID if token found, false if not.
	 */
	private function validate_token( $token ) {
		$order_id = $this->model->get_order_id_from_token( $token );
		if ( $order_id && md5( $order_id ) === $token ) {
			return $order_id;
		}
		return false;
	}

	/**
	 * Function to read token on current URL. Get the order id and create recovery cart based on old order.
	 */
	public function read_url() {
		$token_name = apply_filters( 'aoe_token_variable', 'abandoned_order' );
		if ( isset( $_GET[ $token_name ] ) ) { // Input var okay.
			$order_id = $this->validate_token( sanitize_text_field( wp_unslash( $_GET[ $token_name ] ) ) ); // Input var okay.
			if ( $order_id ) {
				global $woocommerce;
				global $wp_session;
				$order = wc_get_order( $order_id );

				if ( is_a( $order, 'WC_Order' ) ) {

					$items = $order->get_items();
					$coupons = $order->get_used_coupons();

					// empty current cart.
					$woocommerce->cart->empty_cart();

					// add products to cart.
					foreach ( $items as $item ) {
						if ( version_compare( $woocommerce->version, '3.0', '>=' ) ) {
							$product_id = $item->get_product_id();
							$quantity = $item->get_quantity();
							$variation_id = $item->get_variation_id();
						} else {
							$product_id = $item['product_id'];
							$quantity = $item['quantity'];
							$variation_id = $item['variation_id'];
						}
						// manually add to cart.
						$woocommerce->cart->add_to_cart( $product_id, $quantity, $variation_id );
					}

					// apply coupons.
					if ( ! empty( $coupons ) ) {
						foreach ( $coupons as $coupon ) {
							$woocommerce->cart->add_discount( $coupon );
						}
					}

					// save old order id on session.
					setcookie( 'abandoned_order_id', $order_id, 0, '/' );

					do_action( 'aoe_recovered_cart', $order_id );

					wp_safe_redirect( $woocommerce->cart->get_checkout_url() );
					exit;
				}
			}
			wp_safe_redirect( site_url() );
			exit;
		}
	}

	/**
	 * Function to save old order_id to recovered order.
	 *
	 * @param  integer $order_id New order id.
	 */
	public function recover_order( $order_id ) {
		global $woocommerce;
		if ( isset( $_COOKIE['abandoned_order_id'] ) ) { // Input var okay.
			$abandoned_order_id = sanitize_text_field( wp_unslash( $_COOKIE['abandoned_order_id'] ) ); // Input var okay.
			update_post_meta( $order_id, 'aoe_recover_from', $abandoned_order_id );
			update_post_meta( $abandoned_order_id, 'aoe_recovered', $order_id );
			delete_post_meta( $abandoned_order_id, 'aoe_recovery_token' );
			unset( $_COOKIE['abandoned_order_id'] ); // Input var okay.
			do_action( 'aoe_recovered_order', $order_id, $abandoned_order_id );
		}
	}

	/**
	 * Get report data
	 *
	 * @param  string $start_date Start date.
	 * @param  string $end_date   End date.
	 * @return array              Reports
	 */
	public function get_report( $start_date, $end_date ) {
		if ( strtotime( $start_date ) < strtotime( $end_date ) ) {
			$report = array(
				'abandoned_order'       => 0,
				'recovered_order'       => 0,
				'recovered_order_total' => 0,
			);
			$abandoned_order_ids = $this->model->get_abandoned_orders_ids_with_range( $start_date, $end_date );
			$recovered_order_ids = $this->model->get_recovered_orders_ids_with_range( $start_date, $end_date );
			$recovered_order_total = $this->model->get_total_recovered_with_range( $start_date, $end_date );
			$report['abandoned_order'] = count( $abandoned_order_ids );
			$report['recovered_order'] = count( $recovered_order_ids );
			$report['recovered_order_total'] = (float) $recovered_order_total;
			return $report;
		}
	}

}

global $aoe_function;
$aoe_functions = new AOE_Functions();
