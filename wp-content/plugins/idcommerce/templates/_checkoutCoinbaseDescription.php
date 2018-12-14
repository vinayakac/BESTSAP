<div id="finaldescCoinbase" class="finaldesc" style="display:none; word-wrap: none;" data-cb-symbol="<?php echo $cb_symbol; ?>">
	<p>
		<?php _e('You will be directed to Coinbase to authenticate and complete your payment of', 'memberdeck'); ?> 
		<?php echo (isset($level_price) ? apply_filters('idc_price_format', $level_price) : ''); ?> 
		<span class="currency-symbol"><?php echo $cb_currency; ?></span>.
	</p>
	<?php if (isset($combined_purchase_gateways['cb']) && $combined_purchase_gateways['cb']) { ?>
		<p class="combined-product-desc"><?php __('Recurring product', 'memberdeck').' <b>'.$combined_level->level_name.'</b> '.__('is combined with this Product', 'memberdeck'); ?></p>
	<?php } ?>
</div>