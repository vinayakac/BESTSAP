<div id="finaldescStripe" class="finaldesc" data-currency-symbol="<?php echo ((isset($es) && $es == 1) ? $stripe_symbol : $cc_currency_symbol); ?>" style="display:none;">
	<?php _e('Your card will be billed', 'memberdeck'); ?> 
	<?php echo (isset($level_price) ? apply_filters('idc_price_format', $level_price) : ''); ?> 
	<span class="currency-symbol"><?php echo ((isset($es) && $es == 1) ? $stripe_currency : $cc_currency); ?></span> 
	<?php echo (isset($type) && $type == 'recurring' && isset($limit_term) && $limit_term == '1' ? __('in ', 'memberdeck').$term_length.' ' : ''); ?>
	<?php echo (isset($type) && $type == 'recurring' ? $recurring : ''); ?> 
	<?php echo (isset($type) && $type == 'recurring' && isset($limit_term) && $limit_term == '1' ? __('installments', 'memberdeck') : ''); ?> 
	<?php echo (isset($customer_id) ? __('using the card on file', 'memberdeck') : ''); ?> 
	<?php _e('and will appear on your statement as', 'memberdeck'); ?>: <em><?php echo (isset($coname) ? $coname : ''); ?></em>.
	
	<?php if (isset($combined_purchase_gateways['cc']) && $combined_purchase_gateways['cc']) { ?>
		<p class="combined-product-desc"><?php echo __('Recurring product', 'memberdeck').' <b>'.$combined_level->level_name.'</b> '.__('is combined with this Product', 'memberdeck'); ?></p>
	<?php } ?>
</div>