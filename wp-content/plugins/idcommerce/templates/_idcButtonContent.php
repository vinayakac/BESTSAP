<div class="ignitiondeck idc_lightbox idc_button_lightbox mfp-hide">
	<div class="project_image" style="background-image: url('<?php echo (isset($args['thumb']) ? $args['thumb'] : '') ?>');">
    	<div class="aspect_ratio_maker"></div>
    </div>
    <div class="lb_wrapper memberdeck">
		<div class="form_header">
			<strong><?php _e('Step 1:', 'memberdeck'); ?></strong> <?php _e('Confirm your purchase of', 'memberdeck'); ?> <em><?php echo $level->level_name; ?></em>
		</div>
		<div class="form">
			<form action="" method="POST" name="idc_button_checkout_form">
				<div class="form-row inline left twothird total">
					<label for="price"><?php _e('Total', 'memberdeck'); ?></label>
					<input type="text" class="total" name="price" id="price" value="" placeholder="<?php _e('Default Price', 'memberdeck'); ?>: <?php echo $currency_symbol.apply_filters('idc_price_format', $level->level_price) ?>" />
					<span class="idc-button-default-price hide" data-level-price="<?php echo $level->level_price ?>"></span>
				</div>
				<div class="form-row text">
					<p>
						<?php // echo description; ?>
					</p>
				</div>
				<div class="button-error-placeholder">
					<span class="payment-errors" style="display:none;"><?php _e('Input price is less than Product default price') ?></span>
				</div>
				<div class="form-hidden">
					<input type="hidden" name="product_id" value="<?php echo $product_id; ?>"/>
				</div>
				<div class="form-row submit">
					<input type="submit" name="idc_button_submit" class="btn idc_button_submit" value="<?php _e('Next Step', 'memberdeck'); ?>"/>
				</div>
			</form>
		</div>
	</div>
</div>