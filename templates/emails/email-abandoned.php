<?php
/**
 * Abandoned order email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

do_action( 'woocommerce_email_header', $email_heading );

ob_start();
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
$order_details = ob_get_clean();

global $woocommerce;
if ( version_compare( $woocommerce->version, '3.0', '>=' ) ) {
	$payment_method = $order->get_payment_method();
} else {
	$payment_method = get_post_meta( $order->id, '_payment_method', true );
}
$bank_details = '';
if ( 'bacs' === $payment_method ) {
	$methods = WC()->payment_gateways->payment_gateways();
	$bacs_class = $methods['bacs'];
	$bank_details .= '<h2>' . __( 'Our Bank Details', 'aoe' ) . '</h2>';
	foreach ( $bacs_class->account_details as $bank ) {
		$bank_details .= '<h3>' . $bank['account_name'] . '</h3>';
		$bank_details .= '<ul>';
		if ( ! empty( $bank['bank_name'] ) ) {
			$bank_details .= '<li>' . __( 'Bank','aoe' ) . ': <strong>' . $bank['bank_name'] . '</strong></li>';
		}
		if ( ! empty( $bank['account_number'] ) ) {
			$bank_details .= '<li>' . __( 'Account number','aoe' ) . ': <strong>' . $bank['account_number'] . '</strong></li>';
		}
		if ( ! empty( $bank['sort_code'] ) ) {
			$bank_details .= '<li>' . __( 'Sort code','aoe' ) . ': <strong>' . $bank['sort_code'] . '</strong></li>';
		}
		if ( ! empty( $bank['iban'] ) ) {
			$bank_details .= '<li>' . __( 'IBAN','aoe' ) . ': <strong>' . $bank['iban'] . '</strong></li>';
		}
		if ( ! empty( $bank['bic'] ) ) {
			$bank_details .= '<li>' . __( 'BIC','aoe' ) . ': <strong>' . $bank['bic'] . '</strong></li>';
		}
		$bank_details .= '</ul>';
	}
	$content = aoe_get_option( 'email_body_bacs' );
} else {
	$content = aoe_get_option( 'email_body' );
}

$content = str_replace( '{bank_details}', $bank_details, $content );
$content = str_replace( '{order_id}', $order->get_id(), $content );
$content = str_replace( '{order_details}', $order_details, $content );
$content = preg_replace( '/{recovery_url "([^}]+)"}/i', '<a href="' . $recovery_url . '">$1</a>', $content );
$content = str_replace( '{site_name}', get_bloginfo( 'name' ), $content );

echo apply_filters( 'the_content', $content );

do_action( 'woocommerce_email_footer' );

