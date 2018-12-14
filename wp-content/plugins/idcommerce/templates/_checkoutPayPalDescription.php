<div id="finaldescPayPal" class="finaldesc" style="display:none; word-wrap: none;">
	<p>
		<?php _e('You will be redirected to PayPal to complete your payment of', 'memberdeck'); ?> 
		<?php echo (isset($level_price) ? apply_filters('idc_price_format', $level_price) : ''); ?> 
		<span class="currency-symbol"><?php echo $pp_currency; ?></span>. 
		<?php (!is_user_logged_in() ? __('Once complete, check your email for registration information', 'memberdeck').'.' : ''); ?>
	</p>

	<?php if (isset($combined_purchase_gateways['pp']) && $combined_purchase_gateways['pp']) { ?>
		<p class="combined-product-desc"><?php echo __('Recurring product', 'memberdeck').' <b>'.$combined_level->level_name.'</b> '.__('is combined with this Product', 'memberdeck'); ?></p>
	<?php } ?>
</div>