<div class="wrap">
	<h2><?php esc_html_e( 'Abandoned Order Report', 'aoe' ); ?></h2>
	<h2 class="nav-tab-wrapper">
		<a class="nav-tab" id="opt-dashboard-tab" href="<?php echo esc_url( admin_url( 'admin.php?page=abandoned_order_dashboard' ) ); ?>"><?php esc_html_e( 'Abandoned Orders', 'aoe' ); ?></a>
		<a class="nav-tab nav-tab-active" id="opt-report-tab" href="#"><?php esc_html_e( 'Report', 'aoe' ); ?></a>
		<a class="nav-tab" id="opt-setting-tab" href="<?php echo esc_url( admin_url( 'admin.php?page=abandoned_order_setting' ) ); ?>"><?php esc_html_e( 'Settings', 'aoe' ); ?></a>
	</h2>
	<div id="aoe-report">
		<form class="date-select">
			<span><?php esc_html_e( 'Select date range', 'aoe' ); ?> :</span>
			<input type="text" class="datepicker" name="start_date" value="<?php echo esc_attr( $start_date ); ?>">
			<span><?php esc_html_e( 'to', 'aoe' ); ?></span>
			<input type="text" class="datepicker" name="end_date" value="<?php echo esc_attr( $end_date ); ?>">
			<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">
			<input type="submit" value="Get Report" class="button">
		</form>
		<div class="report-wrapper">
			<?php if ( strtotime( $start_date ) >= strtotime( $end_date ) ) : ?>
				<p class="notice"><?php esc_html_e( 'Please select a valid date range.', 'aoe' ); ?></p>
			<?php else : ?>
				<table class="report-table">
					<tr>
						<td class="label"><?php esc_html_e( 'Abandoned Order', 'aoe' ); ?> :</td>
						<td class="value"><?php echo esc_html( $report['abandoned_order'] ); ?></td>
					</tr>
					<tr>
						<td class="label"><?php esc_html_e( 'Recovered Order', 'aoe' ); ?> :</td>
						<td class="value"><?php echo esc_html( $report['recovered_order'] ); ?></td>
					</tr>
					<tr>
						<td class="label"><?php esc_html_e( 'Total Recovered Order', 'aoe' ); ?> :</td>
						<td class="value"><?php echo wc_price( $report['recovered_order_total'] ); ?></td>
					</tr>
				</table>
			<?php endif; ?>
		</div>
	</div>
</div>
