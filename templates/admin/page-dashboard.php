<div class="wrap">
	<h2><?php esc_html_e( 'Abandoned Order List', 'aoe' ); ?></h2>
	<h2 class="nav-tab-wrapper">
		<a class="nav-tab nav-tab-active" id="opt-dashboard-tab" href="#"><?php esc_html_e( 'Abandoned Orders', 'aoe' ); ?></a>
		<a class="nav-tab" id="opt-report-tab" href="<?php echo esc_url( admin_url( 'admin.php?page=abandoned_order_report' ) ); ?>"><?php esc_html_e( 'Report', 'aoe' ); ?></a>
		<a class="nav-tab" id="opt-setting-tab" href="<?php echo esc_url( admin_url( 'admin.php?page=abandoned_order_setting' ) ); ?>"><?php esc_html_e( 'Settings', 'aoe' ); ?></a>
	</h2>
	<div id="aoe-order-list-table">			
		<div id="aoe-post-body">		
			<form id="aoe-user-list-form" method="get">
				<input type="hidden" name="page" value="<?php esc_attr_e( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ); ?>" />
				<?php
					$this->list_table->display();
				?>
			</form>
		</div>			
	</div>
</div>
