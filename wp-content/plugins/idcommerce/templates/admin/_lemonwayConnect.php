<div class="wrap memberdeck">
	<div class="icon32" id="icon-options-general"></div><h2 class="title"><?php _e('LemonWay Connect', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="http://forums.ignitiondeck.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large button-primary"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="http://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large button-primary"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<br style="clear: both;"/>
	<div class="postbox-container" style="width:60%; margin-right: 5%">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Application Settings', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<form method="POST" action="" id="idc_lemonway_settings" name="idc_lemonway_settings">
							<div class="form-select">
								<p>
									<label for="fee_type"><?php _e('Fee Type', 'memberdeck'); ?></label><br/>
									<select name="fee_type" id="fee_type">
										<option value="flat" <?php echo (isset($lemonway_settings['fee_type']) && $lemonway_settings['fee_type'] == 'flat' ? 'selected="selected"' : ''); ?>><?php _e('Flat Fee', 'memberdeck'); ?></option>
										<option value="percentage" <?php echo (isset($lemonway_settings['fee_type']) && $lemonway_settings['fee_type'] == 'percentage' ? 'selected="selected"' : ''); ?>><?php _e('Percentage', 'memberdeck'); ?></option>
									</select>
								</p>
							</div>
							<div class="form-input">
								<p>
									<label for="app_fee"><?php _e('Fee Amount (numeric characters only)', 'memberdeck'); ?></label><br/>
									<input type="text" name="app_fee" id="app_fee" value="<?php echo (isset($lemonway_settings['app_fee']) ? $lemonway_settings['app_fee'] : ''); ?>"/>
								</p>
							</div>
							<!-- <div class="form-check">
								<p>
									<input type="checkbox" name="dev_mode" id="dev_mode" <?php echo (isset($dev_mode) && $dev_mode == 1 ? 'checked="checked"' : ''); ?>/> <label for="dev_mode"><?php _e('Enable Development Mode', 'memberdeck'); ?></label>
								</p>
							</div> -->
							<div class="submit">
								<input type="submit" name="lemonway_connect_submit" id="submit" class="button button-primary"/>
							</div>
						</form>
					</div>
				</div>
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('User Management', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<form method="POST" action="" id="idc_lemonway_users" name="idc_lemonway_users">
							<div class="form-input">
								<p><?php _e('Use this option to revoke or clear credentials of Creators.', 'memberdeck'); ?></p>
								<label for="clear_creds"><?php _e('Revoke Credentials', 'memberdeck'); ?></label><br/>
								<select id="clear_creds" name="clear_creds">
									<option value=""><?php _e('Select User', 'memberdeck'); ?></option>
								</select>
							</div>
							<div class="submit">
								<input type="submit" name="lemonway_revoke" id="lemonway_revoke" class="button" value="<?php _e('Revoke Credentials', 'memberdeck'); ?>"/>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Begin Sidebar -->
	<div class="postbox-container" style="width:35%;">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Using LemonWay Connect', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<p><?php _e('Stripe Connect enables site owners to process transactions via Stripe Connect user accounts, and if desired, to charge a fee for doing so.', 'memberdeck'); ?></p>
						<p><?php _e('In order to use <a href="https://stripe.com/connect" target="_blank">Stripe Connect</a>, you will need a <a href="http://stripe.com" target="_blank">Stripe account</a> with an application created via the dashboard.', 'memberdeck'); ?></p>	
						<p><?php _e('When creating your Stripe Connect application, ensure that your URL\'s display as follows: ', 'memberdeck'); ?></p>
						<p><strong><?php _e('http://yourdomain.com/[dashboard-link]?payment_settings=1&ipn_handler=sc_return', 'memberdeck'); ?></strong></p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- End Sidebar -->
</div>