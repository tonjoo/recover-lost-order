<div class="wrap">
	<h1><?php esc_html_e( 'Abandoned Order Setting', 'aoe' ); ?></h1>
	<h2 class="nav-tab-wrapper">
		<a class="nav-tab" id="opt-dashboard-tab" href="<?php echo esc_url( admin_url( 'admin.php?page=abandoned_order_dashboard' ) ); ?>"><?php esc_html_e( 'Abandoned Orders', 'aoe' ); ?></a>
		<a class="nav-tab" id="opt-report-tab" href="<?php echo esc_url( admin_url( 'admin.php?page=abandoned_order_report' ) ); ?>"><?php esc_html_e( 'Report', 'aoe' ); ?></a>
		<a class="nav-tab nav-tab-active" id="opt-setting-tab" href="#"><?php esc_html_e( 'Settings', 'aoe' ); ?></a>
	</h2>
	<div id="aoe-setting">
		<form action="" method="post">
			<div class="form-section">
				<h4 class="form-section-title"><?php esc_html_e( 'General Setting', 'aoe' ); ?></h4>
				<div class="input-row">
					<label for="input-interval"><?php esc_html_e( 'Order Date Threshold (BACS)', 'aoe' ); ?> :</label>
					<div class="input-field">
						<input type="number" value="<?php echo esc_html( aoe_get_option( 'bacs_threshold_value' ) ); ?>" name="bacs_threshold_value" id="input_interval" min="1" style="width: 70px;display: inline-block;vertical-align: top;">
						<select name="bacs_threshold_unit" style="display: inline-block;vertical-align: top;">
							<option <?php echo 'minute' === aoe_get_option( 'bacs_threshold_unit' ) ? 'selected' : ''; ?> value="minute"><?php esc_html_e( 'Minute(s)', 'aoe' ); ?></option>
							<option <?php echo 'hour' === aoe_get_option( 'bacs_threshold_unit' ) ? 'selected' : ''; ?> value="hour"><?php esc_html_e( 'Hour(s)', 'aoe' ); ?></option>
							<option <?php echo 'day' === aoe_get_option( 'bacs_threshold_unit' ) ? 'selected' : ''; ?> value="day"><?php esc_html_e( 'Day(s)', 'aoe' ); ?></option>
							<option <?php echo 'week' === aoe_get_option( 'bacs_threshold_unit' ) ? 'selected' : ''; ?> value="week"><?php esc_html_e( 'Week(s)', 'aoe' ); ?></option>
						</select>
					</div>
					<div class="helper">
						<?php esc_html_e( 'Threshold time after the order date which categorized as an abandoned order. Only for orders with BACS payment method.', 'aoe' ); ?>
					</div>
				</div>
				<div class="input-row">
					<label for="input-interval"><?php esc_html_e( 'Order Date Threshold (non BACS)', 'aoe' ); ?> :</label>
					<div class="input-field">
						<input type="number" value="<?php echo esc_html( aoe_get_option( 'threshold_value' ) ); ?>" name="threshold_value" id="input_interval" min="1" style="width: 70px;display: inline-block;vertical-align: top;">
						<select name="threshold_unit" style="display: inline-block;vertical-align: top;">
							<option <?php echo 'minute' === aoe_get_option( 'threshold_unit' ) ? 'selected' : ''; ?> value="minute"><?php esc_html_e( 'Minute(s)', 'aoe' ); ?></option>
							<option <?php echo 'hour' === aoe_get_option( 'threshold_unit' ) ? 'selected' : ''; ?> value="hour"><?php esc_html_e( 'Hour(s)', 'aoe' ); ?></option>
							<option <?php echo 'day' === aoe_get_option( 'threshold_unit' ) ? 'selected' : ''; ?> value="day"><?php esc_html_e( 'Day(s)', 'aoe' ); ?></option>
							<option <?php echo 'week' === aoe_get_option( 'threshold_unit' ) ? 'selected' : ''; ?> value="week"><?php esc_html_e( 'Week(s)', 'aoe' ); ?></option>
						</select>
					</div>
					<div class="helper">
						<?php esc_html_e( 'Threshold time after the order date which categorized as an abandoned order. Only for orders other than BACS payment method.', 'aoe' ); ?>
					</div>
				</div>
				<div class="input-row">
					<label for="input-recover"><?php esc_html_e( 'Sent recovery email on recovered order', 'aoe' ); ?> :</label>
					<div class="input-field">
						<input type="checkbox" value="yes" name="apply_recover" id="input-recover" <?php echo true === aoe_get_option( 'apply_recover' ) ? 'checked' : ''; ?>>
					</div>
					<div class="helper">
						<?php esc_html_e( 'If turned off (default), only new order will get abandoned email notification. When turned on all abandoned order (new and recovery) will get an abandoned order email notification.', 'aoe' ); ?>
					</div>
				</div>
				<div class="input-row">
					<label for="input-ignore-zero"><?php echo sprintf( __( 'Ignore Orders with Total %s', 'aoe' ), wc_price( 0 ) ); ?> :</label>
					<div class="input-field">
						<input type="checkbox" value="yes" name="ignore_zero" id="input-ignore-zero" <?php echo true === aoe_get_option( 'ignore_zero' ) ? 'checked' : ''; ?>>
					</div>
					<div class="helper">
						<?php esc_html_e( 'Ignore orders with zero order total.', 'aoe' ); ?>
					</div>
				</div>
				<div class="input-row">
					<label for="input-start-date"><?php esc_html_e( 'Abandoned order search starting date', 'aoe' ); ?> :</label>
					<div class="input-field">
						<input id="input-start-date" type="text" class="datepicker-setting" name="start_date" value="<?php echo esc_attr( aoe_get_option( 'start_date' ) ); ?>">
					</div>
					<div class="helper">
						<?php esc_html_e( 'The plugin will only search for an abandoned order newer than the given start date.', 'aoe' ); ?>
					</div>
				</div>
				<div class="input-row">
					<label for="input-debug"><?php esc_html_e( 'Debug Mode', 'aoe' ); ?> :</label>
					<div class="input-field">
						<input type="checkbox" value="yes" name="debug" id="input-debug" <?php echo true === aoe_get_option( 'debug' ) ? 'checked' : ''; ?>>
					</div>
					<div class="helper">
						<?php esc_html_e( 'Enable debug mode?', 'aoe' ); ?>
					</div>
				</div>
			</div>
			<div class="form-section">
				<h4 class="form-section-title"><?php esc_html_e( 'Email Setting', 'aoe' ); ?></h4>
				<div class="input-row">
					<label for="input-email-heading"><?php esc_html_e( 'Email Heading', 'aoe' ); ?> :</label>
					<div class="input-field">
						<input type="text" value="<?php echo esc_attr( aoe_get_option( 'email_heading' ) ); ?>" name="email_heading" id="input-email-heading">
					</div>
					<div class="helper">
						<?php esc_html_e( 'Customer email heading.', 'aoe' ); ?>
					</div>
				</div>
				<div class="input-row">
					<label for="input-email-subject"><?php esc_html_e( 'Email Subject', 'aoe' ); ?> :</label>
					<div class="input-field">
						<input type="text" value="<?php echo esc_attr( aoe_get_option( 'email_subject' ) ); ?>" name="email_subject" id="input-email-subject">
					</div>
					<div class="helper">
						<?php esc_html_e( 'Customer email subject.', 'aoe' ); ?>
					</div>
				</div>
				<div class="input-row">
					<div class="input-field-full">
						<?php esc_html_e( 'Email Body for Order with BACS Payment Method', 'aoe' ); ?> :
						<?php
						$settings = array(
							'editor_height' => 300,
							'media_buttons' => false,
						);
						echo wp_editor( html_entity_decode( aoe_get_option( 'email_body_bacs' ) ), 'email_body_bacs', $settings );
						?>
					</div>
					<div class="helper helper-full">
						<p><?php esc_html_e( 'Email body for BACS payment orders. You can use these shortcodes for the content:', 'aoe' ); ?></p>
						<ul>
							<li><?php echo sprintf( __( '%s - generates list of bank accounts.', 'aoe' ), '<strong>{bank_details}</strong>' ); ?></li>
							<li><?php echo sprintf( __( '%s - generates order number.', 'aoe' ), '<strong>{order_id}</strong>' ); ?></li>
							<li><?php echo sprintf( __( '%s - generates the order details table.', 'aoe' ), '<strong>{order_details}</strong>' ); ?></li>
							<li><?php echo sprintf( __( '%s - generates site name.', 'aoe' ), '<strong>{site_name}</strong>' ); ?></li>
						</ul>
					</div>
				</div>
				<div class="input-row">
					<div class="input-field-full">
						<?php esc_html_e( 'Email Body for Order with non-BACS Payment Method', 'aoe' ); ?> :
						<?php
						$settings = array(
							'editor_height' => 300,
							'media_buttons' => false,
						);
						echo wp_editor( html_entity_decode( aoe_get_option( 'email_body' ) ), 'email_body', $settings );
						?>
					</div>
					<div class="helper helper-full">
						<p><?php esc_html_e( 'Email body for non-BACS payment orders. You can use these shortcodes for the content:', 'aoe' ); ?></p>
						<ul>
							<li><?php echo sprintf( __( '%s - generates order number.', 'aoe' ), '<strong>{order_id}</strong>' ); ?></li>
							<li><?php echo sprintf( __( '%s - generates the order details table.', 'aoe' ), '<strong>{order_details}</strong>' ); ?></li>
							<li><?php echo sprintf( __( '%s - generates site name.', 'aoe' ), '<strong>{site_name}</strong>' ); ?></li>
							<li><?php echo sprintf( __( '%s - generates link for recover the order.', 'aoe' ), '<strong>{recovery_url "link text"}</strong>' ); ?></li>
						</ul>
					</div>
				</div>
			</div>
			<?php wp_nonce_field( 'save_setting', 'aoe_do' ); ?>
			<input type="submit" value="Save Setting" class="button button-primary">
		</form>
	</div>
</div>
