<?php
/**
 * Plugin Name: Abandoned Order Email
 * Plugin URI: http://www.tonjoostudio.com/
 * Description: Automatically sent email for user who abandon their order (did not complete payment)
 * Version: 1.0
 * Author: tonjoo
 * Author URI: http://www.tonjoostudio.com/
 * License: GPLv2
 * Text Domain: aoe
 * Contributor: Todi Adiyatmo Wijoyo, Gama Unggul Priambada, Arif Rohman Hakim
 */

define( 'PLUGIN_AOE', 'plugin-abandon-order-email' );
define( 'PLUGIN_AOE_PATH', plugin_dir_path( __FILE__ ) );
define( 'AOE_DEBUG', false );

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once  ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ;
}
require_once  PLUGIN_AOE_PATH . 'inc/helper.php';
require_once  PLUGIN_AOE_PATH . 'inc/class-aoe-model.php';
require_once  PLUGIN_AOE_PATH . 'inc/class-aoe-functions.php';
require_once  PLUGIN_AOE_PATH . 'inc/class-aoe-scheduler.php';
require_once  PLUGIN_AOE_PATH . 'inc/class-aoe-list-table.php';
require_once  PLUGIN_AOE_PATH . 'admin-page.php';

/**
 * Add Abandoned Order Email to WC Email
 *
 * @param array $email_classes Email classes.
 */
function add_abandoned_order_woocommerce_email( $email_classes ) {
	require_once  PLUGIN_AOE_PATH . 'inc/class-aoe-mailer.php';
	$email_classes['AOE_Email'] = new AOE_Email();
	return $email_classes;
}
add_filter( 'woocommerce_email_classes', 'add_abandoned_order_woocommerce_email' );

/**
 * Load Text Domain
 */
function aoe_load_textdomain() {
	load_plugin_textdomain( 'aoe', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}
add_action( 'plugins_loaded', 'aoe_load_textdomain' );

/**
 * Function to read URL with token
 */
function aoe_read_url() {
	if ( aoe_is_woocommerce_active() ) {
		global $aoe_functions;
		if ( isset($aoe_functions) ) {
			$aoe_functions->read_url();
		}
	}
}
add_action( 'wp_loaded', 'aoe_read_url' );

/**
 * Function to save old order_id to recovered order.
 *
 * @param  integer $order_id New order id.
 */
function aoe_recover_order( $order_id ) {
	if ( aoe_is_woocommerce_active() ) {
		global $aoe_functions;
		$aoe_functions->recover_order( $order_id );
	}
}
add_action( 'woocommerce_thankyou', 'aoe_recover_order', 10, 1 );

/**
 * Set default start date on plugin activation
 */
function aoe_plugin_activate( $plugin ) {
	if ( plugin_basename( __FILE__ ) === $plugin ) {
		if ( false === aoe_get_option( 'start_date', false ) ) {
			aoe_set_option( 'start_date', date( 'Y-m-d', strtotime( '-1 months' ) ) );
		}
		exit( wp_redirect( admin_url( 'admin.php?page=abandoned_order_setting' ) ) );
	}
}
add_action( 'activated_plugin', 'aoe_plugin_activate' );
