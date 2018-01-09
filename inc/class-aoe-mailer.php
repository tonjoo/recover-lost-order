<?php
/**
 * Emailer Class
 */
class AOE_Email extends WC_Email {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id = 'wc_abandoned_order';
		$this->title = __( 'Abandoned Order', 'aoe' );
		$this->description = __( 'Abandoned Order Notification emails are sent when a customer places an order but never complete it.', 'aoe' );

		$this->heading = apply_filters( 'aoe_email_heading', aoe_get_option( 'email_heading' ) );
		$this->subject = apply_filters( 'aoe_email_subject', aoe_get_option( 'email_subject' ) );

		$this->recipient = $this->get_option( 'recipient' );

		parent::__construct();

		add_action( 'aoe_send_email', array( $this, 'trigger' ), 10, 2 );
	}

	/**
	 * Action trigger to send email
	 *
	 * @param  int   $order_id   Order ID.
	 * @param  mixed $order      Order object.
	 * @return boolean           Email status, sent or not.
	 */
	public function trigger( $order_id, $order = false ) {

		if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( is_a( $order, 'WC_Order' ) ) {
			$this->object     = $order;
			$this->recipient  = $this->object->get_billing_email();
		}

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}
		// woohoo, send the email!
		return $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * Get Email HTML Template
	 *
	 * @return string HTML Content.
	 */
	public function get_content_html() {
		ob_start();
		global $aoe_functions;
		$order          = $this->object;
		$email_heading  = $this->get_heading();
		if ( isset( $aoe_functions ) ) {
			$recovery_url   = $aoe_functions->generate_recovery_url( $order->get_id() );
		}
		$sent_to_admin  = false;
		$plain_text     = false;
		$email          = $this;
		include apply_filters( 'aoe_email_template_path', PLUGIN_AOE_PATH . 'templates/emails/email-abandoned.php' );
		return ob_get_clean();
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'         => __( 'Enable/Disable', 'woocommerce' ),
				'type'          => 'checkbox',
				'label'         => __( 'Enable this email notification', 'woocommerce' ),
				'default'       => 'yes',
			),
			'subject' => array(
				'title'         => __( 'Subject', 'woocommerce' ),
				'type'          => 'text',
				'desc_tip'      => true,
				'description'   => sprintf( __( 'Available placeholders: %s', 'woocommerce' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
				'placeholder'   => $this->get_default_subject(),
				'default'       => '',
			),
			'heading' => array(
				'title'         => __( 'Email heading', 'woocommerce' ),
				'type'          => 'text',
				'desc_tip'      => true,
				'description'   => sprintf( __( 'Available placeholders: %s', 'woocommerce' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
				'placeholder'   => $this->get_default_heading(),
				'default'       => '',
			),
			'email_type' => array(
				'title'       => 'Email type',
				'type'        => 'select',
				'default'     => 'html',
				'class'       => 'email_type select2',
				'options'     => array(
					'html'      => 'HTML',
				),
			),
		);
	}

}
