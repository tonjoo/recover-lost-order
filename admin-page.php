<?php

/**
 * Abandoned Order Admin
 */
class AOE_Admin {

	/**
	 * List Table Object
	 *
	 * @var [type]
	 */
	protected $list_table;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $aoe_functions;
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'init_action' ) );
		$this->functions = $aoe_functions;
	}

	/**
	 * Enqueue Scripts
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();
		if ( in_array( $screen->id, array( 'abandoned-order_page_abandoned_order_dashboard', 'abandoned-order_page_abandoned_order_report', 'abandoned-order_page_abandoned_order_setting' ), true ) ) {
			wp_register_style( 'jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' );
			wp_enqueue_style( 'jquery-ui' );
			wp_enqueue_style( 'aoe-admin-style', plugins_url( '/assets/css/admin.css', __FILE__ ), array( 'jquery-ui' ), false );
			wp_enqueue_script( 'aoe-admin-js', plugins_url( '/assets/js/admin.js', __FILE__ ) , array( 'jquery', 'jquery-ui-datepicker' ), '', true );
		}
	}

	/**
	 * Register Menu
	 */
	public function register_menu() {
		add_menu_page( __( 'Abandoned Order' ), __( 'Abandoned Order' ), 'manage_options', 'abandoned_order', 'aoe_dashboard_handler', 'dashicons-email' );
		$page_hook = add_submenu_page( 'abandoned_order', __( 'Abandoned Orders', 'aoe' ), __( 'Orders', 'aoe' ), 'manage_options', 'abandoned_order_dashboard', array( $this, 'page_dashboard_handler' ) );
		add_submenu_page( 'abandoned_order', __( 'Abandoned Order Report', 'aoe' ), __( 'Report', 'aoe' ), 'manage_options', 'abandoned_order_report', array( $this, 'page_report_handler' ) );
		add_submenu_page( 'abandoned_order', __( 'Abandoned Order Settings', 'aoe' ), __( 'Settings', 'aoe' ), 'manage_options', 'abandoned_order_setting', array( $this, 'page_setting_handler' ) );
		remove_submenu_page( 'abandoned_order', 'abandoned_order' );
		add_action( 'load-' . $page_hook, array( $this, 'dashboard_screen_options' ) );
	}

	/**
	 * Dashboard Screen Option
	 */
	public function dashboard_screen_options() {
		$args = array(
			'label'     => __( 'Orders Per Page', 'aoe' ),
			'default'   => 20,
			'option'    => 'orders_per_page',
		);
		add_screen_option( 'per_page', $args );
	}

	/**
	 * Init Actions
	 */
	public function init_action() {
		if ( isset( $_REQUEST['aoe_do'] ) ) { // Input var okay.
			if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['aoe_do'] ) ), 'save_setting' ) ) { // Input var okay.
				$this->save_setting();
			} elseif ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['aoe_do'] ) ), 'send_email' ) ) { // Input var okay.
				$this->send_email();
			}
		}
		if ( isset( $_REQUEST['notice'] ) ) {
			add_action( 'admin_notices', array( 'AOE_Flash_Notice', 'show_notices' ) );
		}
	}

	/**
	 * Save settings
	 */
	private function save_setting() {
		if ( isset( $_POST['threshold_value'] ) ) { // Input var okay.
			aoe_set_option( 'threshold_value', intval( $_POST['threshold_value'] ) ); // Input var okay.
		}
		if ( isset( $_POST['threshold_unit'] ) ) { // Input var okay.
			aoe_set_option( 'threshold_unit', sanitize_text_field( wp_unslash( $_POST['threshold_unit'] ) ) ); // Input var okay.
		}
		if ( isset( $_POST['bacs_threshold_value'] ) ) { // Input var okay.
			aoe_set_option( 'bacs_threshold_value', intval( $_POST['bacs_threshold_value'] ) ); // Input var okay.
		}
		if ( isset( $_POST['bacs_threshold_unit'] ) ) { // Input var okay.
			aoe_set_option( 'bacs_threshold_unit', sanitize_text_field( wp_unslash( $_POST['bacs_threshold_unit'] ) ) ); // Input var okay.
		}
		if ( isset( $_POST['apply_recover'] ) && 'yes' === $_POST['apply_recover'] ) { // Input var okay.
			aoe_set_option( 'apply_recover', true );
		} else {
			aoe_set_option( 'apply_recover', false );
		}
		if ( isset( $_POST['ignore_zero'] ) && 'yes' === $_POST['ignore_zero'] ) { // Input var okay.
			aoe_set_option( 'ignore_zero', true );
		} else {
			aoe_set_option( 'ignore_zero', false );
		}
		if ( isset( $_POST['debug'] ) && 'yes' === $_POST['debug'] ) { // Input var okay.
			aoe_set_option( 'debug', true );
		} else {
			aoe_set_option( 'debug', false );
		}
		if ( isset( $_POST['start_date'] ) ) { // Input var okay.
			aoe_set_option( 'start_date', wp_unslash( $_POST['start_date'] ) );
		}
		if ( isset( $_POST['email_heading'] ) ) { // Input var okay.
			aoe_set_option( 'email_heading', $_POST['email_heading'] );
		}
		if ( isset( $_POST['email_subject'] ) ) { // Input var okay.
			aoe_set_option( 'email_subject', $_POST['email_subject'] );
		}
		if ( isset( $_POST['email_body'] ) ) { // Input var okay.
			aoe_set_option( 'email_body', wp_unslash( $_POST['email_body'] ) );
		}
		if ( isset( $_POST['email_body_bacs'] ) ) { // Input var okay.
			aoe_set_option( 'email_body_bacs', wp_unslash( $_POST['email_body_bacs'] ) );
		}
		aoe_add_notice( __( 'Settings Updated', 'aoe' ), 'updated' );
		wp_safe_redirect( admin_url( 'admin.php?page=abandoned_order_setting&notice' ) );
	}

	/**
	 * Trigger send email individually
	 */
	private function send_email() {
		if ( isset( $_REQUEST['order_id'] ) ) { // Input var okay.
			global $aoe_functions;
			if ( $aoe_functions->send_single_email( sanitize_text_field( wp_unslash( $_REQUEST['order_id'] ) ) ) ) {
				aoe_add_notice( __( 'Email sent!', 'aoe' ), 'updated' );
			} else {
				aoe_add_notice( __( 'Email failed to send.', 'aoe' ), 'error' );
			}
		}
		wp_safe_redirect( admin_url( 'admin.php?page=abandoned_order_dashboard&notice' ) );
	}

	public function page_no_woocommerce() {
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Abandoned Order List', 'aoe' ); ?></h2>
			<div class="notice notice-error">
				<p>
					<?php esc_html_e( 'Abandoned order plugin needs Woocommerce to be active.', 'aoe' ); ?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Handler for displaying AOE Dashboard
	 */
	public function page_dashboard_handler() {
		if ( ! aoe_is_woocommerce_active() ) {
			return $this->page_no_woocommerce();
		}
		$this->list_table = new AOE_List_Table();
		$this->list_table->prepare_items();

		include 'templates/admin/page-dashboard.php';
	}

	/**
	 * Handler for displaying AOE Report Page
	 */
	public function page_report_handler() {
		if ( ! aoe_is_woocommerce_active() ) {
			return $this->page_no_woocommerce();
		}
		$start_date = isset( $_GET['start_date'] ) && aoe_validate_date( $_GET['start_date'] ) ? $_GET['start_date'] : date( 'Y/m/d', strtotime( '-1 months' ) );
		$end_date = isset( $_GET['end_date'] ) && aoe_validate_date( $_GET['end_date'] ) ? $_GET['end_date'] : date( 'Y/m/d' );
		$report = $this->functions->get_report( $start_date, $end_date );
		include 'templates/admin/page-report.php';
	}

	/**
	 * Handler for displaying AOE Setting Page
	 */
	public function page_setting_handler() {
		if ( ! aoe_is_woocommerce_active() ) {
			return $this->page_no_woocommerce();
		}
		include 'templates/admin/page-setting.php';
	}

}

new AOE_Admin();
