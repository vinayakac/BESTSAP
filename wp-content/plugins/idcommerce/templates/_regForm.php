<div class="memberdeck">
	<form action="" method="POST" id="payment-form" name="reg-form" data-regkey="<?php echo (isset($reg_key) ? $reg_key : ''); ?>">
		<div id="logged-input" class="no">
			<div class="form-row third left">
				<label><?php echo apply_filters('idc_reg_label_first-name', __('First Name', 'memberdeck')); ?></label>
				<input type="text" size="20" class="first-name required" name="first-name" value="<?php echo apply_filters('idc_reg_first-name', (isset($user_firstname) ? $user_firstname : '')); ?>"/>
			</div>
			<div class="form-row twoforth">
				<label><?php echo apply_filters('idc_reg_label_last-name', __('Last Name', 'memberdeck')); ?></label>
				<input type="text" size="20" class="last-name required" name="last-name" value="<?php echo apply_filters('idc_reg_last-name', (isset($user_lastname) ? $user_lastname : '')); ?>"/>
			</div>
			<div class="form-row">
				<label><?php echo apply_filters('idc_reg_label_email', __('Email Address', 'memberdeck')); ?></label>
				<input type="email" size="20" class="email required" name="email" value="<?php echo apply_filters('idc_reg_email', (isset($email) ? $email : '')); ?>"/>
			</div>
			<div class="form-row">
				<label><?php echo apply_filters('idc_reg_pw', __('Password', 'memberdeck')); ?></label>
				<input type="password" size="20" class="pw required" name="pw"/>
			</div>
			<div class="form-row">
				<label><?php echo apply_filters('idc_reg_cpw', __('Re-enter Password', 'memberdeck')); ?></label>
				<input type="password" size="20" class="cpw required" name="cpw"/>
			</div>
			<div id="registration-form-extra-fields">
				<?php echo do_action('md_register_extrafields'); ?>
			</div>
		</div>
		<span class="payment-errors"></span>
		<button type="submit" id="id-reg-submit" class="submit-button"><?php _e('Complete Registration', 'memberdeck'); ?></button>
		<?php do_action('idc_below_register_form'); ?>
	</form>
</div>