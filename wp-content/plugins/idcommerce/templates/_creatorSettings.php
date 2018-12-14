<div class="memberdeck">
	<form method="POST" action="" id="payment-settings" class="payment-settings">
		<?php include_once IDC_PATH.'templates/_mdProfileTabs.php'; ?>
		<ul class="md-box-wrapper full-width cf">
			<?php if (isset($epp_fes) && $epp_fes) { ?>
			<li class="md-box half">
				<div class="md-profile paypal-settings">
					<?php
					echo apply_filters('md_payment_settings_error', ''); ?>
					<!-- echo $output; -->
					<h3><?php _e('Paypal', 'memberdeck'); ?></h3>
					<p> <?php _e('Add your paypal email id ', 'memberdeck'); ?></p>
					<div class="form-row">
						<label for="paypal_email"><?php _e('Paypal Email', 'memberdeck'); ?></label>
						<input type="email" id="" name="paypal_email" class="required" value="<?php echo $paypal_email; ?>">
					</div>
				</div>
			</li>
			<?php } ?>
			
			<?php if ($enable_mailchimp) { ?>
			<li class="md-box half">
				<div class="md-profile mail-chimp">
					<h3><?php _e('Mailchimp', 'memberdeck'); ?></h3>
					<p>
					<?php _e('Sign up for a free ', 'memberdeck'); ?>
					<a href="http://eepurl.com/DqCdz"><?php _e('Mailchimp', 'memberdeck'); ?></a>
					<?php _e('account to build your own mailing list comprised of each of your supporters.', 'memberdeck'); ?>
					</p>
					<div class="form-row">
						<label for="mailchimp_key">
							<?php _e('Mailchimp API Key', 'memberdeck'); ?>
						</label>
						<input type="text" name="mail_settings[mailchimp_key]" id="mailchimp_key" value="<?php echo (isset($mail_settings['mailchimp_key']) ? $mail_settings['mailchimp_key'] : ''); ?>"/>
					</div>
					<div class="form-row">
						<label for="mailchimp_list">
							<?php _e('Mailchimp List ID', 'memberdeck'); ?>
						</label>
						<input type="text" name="mail_settings[mailchimp_list]" id="mailchimp_list" value="<?php echo (isset($mail_settings['mailchimp_list']) ? $mail_settings['mailchimp_list'] : ''); ?>"/>
					</div>
						
				</div>
			</li>
			<?php } ?>
			<?php do_action('md_payment_settings_extrafields', $payment_settings); ?>
			<?php do_action('md_payment_settings_sidebar'); ?>
			<li class="md-box">
				<div class="form-row">
                <div class="border-bottom"></div>
					<p><input type="submit" name="creator_settings_submit" id="creator_settings_submit" class="button-primary" value="<?php _e('Submit', 'memberdeck'); ?>"/></p>
				</div>
			</li>
		</ul>
	</form>
</div>