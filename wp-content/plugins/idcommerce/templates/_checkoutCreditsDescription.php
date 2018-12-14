<div id="finaldescCredits" class="finaldesc" style="display:none; word-wrap: none;" data-credits-label="<?php echo apply_filters('idc_credits_label', __('credits', 'memberdeck'), ($credit_value > 1 ? true : false)); ?>">
	<p>
		<?php _e('This product costs', 'memberdeck'); ?> 
		<span class="credit-value"><?php echo apply_filters('idc_price_format', $credit_value) ?></span> 
		<span class="currency-symbol"><?php echo apply_filters('idc_credits_label', __('credits', 'memberdeck'), ($credit_value > 1 ? true : false)); ?></span>.
		<br /> 
		<?php _e('Your current account '.strtolower(apply_filters('idc_credits_label', __('credits', 'memberdeck'), true)).' '.((!empty($user_data) && isset($user_data->credits) && $user_data->credits > 1) ? 'are' : 'is'), 'memberdeck'); ?> 
		<?php echo apply_filters('idc_price_format', (!empty($user_data) && isset($user_data->credits) ? $user_data->credits : 0)); ?>. 
		<?php _e('After the purchase your remaining '.strtolower(apply_filters('idc_credits_label', 'credits', true)).' will be') ?> 
		<?php echo apply_filters('idc_price_format', (!empty($user_data) && isset($user_data->credits) ? ($user_data->credits - $credit_value) : 0)); ?>.
	</p>
</div>