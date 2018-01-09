<?php

if ( ! function_exists( 'aoe_get_option' ) ) {
	/**
	 * Get setting value
	 *
	 * @param  string $item    Item name.
	 * @param  string $default Default value.
	 * @return mixed          Returned value.
	 */
	function aoe_get_option( $item, $default = '' ) {
		$defaults = apply_filters( 'aoe_default_options', array() );
		if ( 'email_body' === $item ) {
			return get_option( 'aoe_email_body', $defaults['email_body'] );
		} elseif ( 'email_body_bacs' === $item ) {
			return get_option( 'aoe_email_body_bacs', $defaults['email_body_bacs'] );
		}
		$settings = get_option( 'aoe_setting', array() );
		if ( isset( $settings[ $item ] ) ) {
			return $settings[ $item ];
		} elseif ( isset( $defaults[ $item ] ) ) {
			return $defaults[ $item ];
		}
		return $default;
	}
}

if ( ! function_exists( 'aoe_set_option' ) ) {
	/**
	 * Set setting value
	 *
	 * @param  string $item  Item name.
	 * @param  string $value Value.
	 */
	function aoe_set_option( $item, $value ) {
		if ( 'email_body' === $item ) {
			update_option( 'aoe_email_body', $value );
		} elseif ( 'email_body_bacs' === $item ) {
			update_option( 'aoe_email_body_bacs', $value );
		}
		$settings = get_option( 'aoe_setting', apply_filters( 'aoe_default_options', array() ) );
		$settings[ $item ] = $value;
		update_option( 'aoe_setting', $settings );
	}
}

/**
 * Set default options
 *
 * @param  array $options Options.
 * @return array $options Options.
 */
function aoe_set_default_options( $options ) {
	$options = array(
		'threshold_value'   => 1,
		'threshold_unit'    => 'hour',
		'bacs_threshold_value' => 1,
		'bacs_threshold_unit'  => 'day',
		'apply_recover'     => false,
		'ignore_zero'       => true,
		'debug'             => false,
		'email_heading'     => 'Complete Your Purchase',
		'email_subject'     => 'Complete Your Purchase',
		'email_body'  => 'Your order still on-hold until we confirm payment has been received. Your order details are shown below for your reference:

{order_details}

<h3>Recover Order</h3>

Please use this link to recover your order :
{recovery_url "Recover my order"}

Thank You for shopping with {site_name}',
		'email_body_bacs'  => 'Your order still on-hold until we confirm payment has been received. Please make sure you complete your purchase with paying your order to these available bank accounts:

{bank_details}

Your order details are shown below for your reference:

{order_details}

Thank You for shopping with {site_name}',
	);
	return $options;
}
add_filter( 'aoe_default_options', 'aoe_set_default_options' );

if ( ! function_exists( 'aoe_get_threshold' ) ) {
	/**
	 * Get interval in seconds
	 *
	 * @return int Interval in seconds.
	 */
	function aoe_get_threshold( $payment = '' ) {
		if ( 'bacs' === $payment ) {
			$value = aoe_get_option( 'bacs_threshold_value' );
			if ( 0 === intval( $value ) ) {
				$value = 1;
			}
			$unit = aoe_get_option( 'bacs_threshold_unit' );
		} else {
			$value = aoe_get_option( 'threshold_value' );
			if ( 0 === intval( $value ) ) {
				$value = 1;
			}
			$unit = aoe_get_option( 'threshold_unit' );
		}
		if ( 'minute' === $unit ) {
			$interval = intval( $value ) * MINUTE_IN_SECONDS;
		} elseif ( 'hour' === $unit ) {
			$interval = intval( $value ) * HOUR_IN_SECONDS;
		} elseif ( 'week' === $unit ) {
			$interval = intval( $value ) * WEEK_IN_SECONDS;
		} else {
			$interval = intval( $value ) * DAY_IN_SECONDS;
		}
		return $interval;
	}
}

if ( ! function_exists( 'aoe_validate_date' ) ) {
	/**
	 * Validate date string
	 *
	 * @param  string $date   Date string.
	 * @param  string $format Date format.
	 * @return boolean        Date valid or not.
	 */
	function aoe_validate_date( $date, $format = 'Y/m/d' ) {
		$d = DateTime::createFromFormat( $format, $date );
		return $d && $d->format( $format ) === $date;
	}
}

/**
 * Check if woocommerce active.
 *
 * @return boolean active or not.
 */
function aoe_is_woocommerce_active() {
	return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true );
}

/**
 * Flash Notices
 */
class AOE_Flash_Notice {

	/**
	 * Add Notice Message
	 *
	 * @param string $message Messages.
	 * @param string $class   Notice type.
	 */
	public static function add_notice( $message, $class = '' ) {
		$allowed_classes = array( 'error', 'updated' );
		if ( ! in_array( $class, $allowed_classes, true ) ) {
			$class = 'updated';
		}
		$flash_messages = maybe_unserialize( get_option( 'aoe_flash_notice', array() ) );
		$flash_messages[ $class ][] = $message;
		update_option( 'aoe_flash_notice', $flash_messages );
	}

	/**
	 * Show Notices
	 */
	public static function show_notices() {
		$flash_notice = maybe_unserialize( get_option( 'aoe_flash_notice', '' ) );
		if ( is_array( $flash_notice ) ) {
			foreach ( $flash_notice as $class => $messages ) {
				foreach ( $messages as $message ) {
					?><div class="<?php echo esc_attr( $class ); ?>"><p><?php echo esc_html( $message ); ?></p></div>
					<?php
				}
			}
		}
		delete_option( 'aoe_flash_notice' );
	}
}

if ( class_exists( 'AOE_Flash_Notice' ) && ! function_exists( 'aoe_add_notice' ) ) {
	/**
	 * Add Notices
	 *
	 * @param string $message Messages.
	 * @param string $class   Notice type.
	 */
	function aoe_add_notice( $message, $class = null ) {
		AOE_Flash_Notice::add_notice( $message, $class );
	}
}
