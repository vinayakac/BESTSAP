<div class="ignitiondeck idc_lightbox idc_add_product_popup mfp-hide">
	<div class="lb_wrapper memberdeck">
		<form method="POST" action="" id="idmember-settings" name="idmember-settings">
			<div class="columns" style="width: 49%; margin-right: 3%;">
				<div class="form-input">
					<label for="level-name"><?php _e('Product Name', 'memberdeck'); ?></label>
					<input type="text" name="level-name" id="level-name" value=""/>
				</div>
				<div class="form-input">
					<label for="level-price"><?php _e('Product Price', 'memberdeck'); ?></label>
					<input type="text" name="level-price" id="level-price" value=""/>
				</div>
				<div class="form-input">
					<label for="product-type"><?php _e('Product Type', 'memberdeck'); ?></label>
					<select name="product-type" id="product-type">
						<option value="purchase"><?php _e('Purchase', 'memberdeck'); ?></option>
						<!--<option value="donation"><?php _e('Donation', 'memberdeck'); ?></option>-->
						<?php echo do_action('idc_product_type'); ?>
					</select>
				</div>
				<div class="form-input">
					<label for="credit-value"><?php echo apply_filters('idc_credits_label', __('Credit', 'memberdeck'), false); ?> <?php _e('Value', 'memberdeck'); ?></label>
					<input type="number" name="credit-value" id="credit-value" min="0" value="0"/>
				</div>				
			</div>
			<div class="columns" style="width: 47%;">
				<div class="form-input">
					<label for="txn-type"><?php _e('Transaction Type', 'memberdeck'); ?></label>
					<select name="txn-type" id="txn-type">
						<option value="capture"><?php _e('Order', 'memberdeck'); ?></option>
						<option value="preauth"><?php _e('Pre-Order', 'memberdeck'); ?></option>
					</select>
				</div>	
				<div class="form-input">
					<label for="level-type"><?php _e('License Type', 'memberdeck'); ?></label>
					<select name="level-type" id="level-type">
						<option value="standard"><?php _e('Standard', 'memberdeck'); ?></option>
						<option value="recurring"><?php _e('Recurring', 'memberdeck'); ?></option>
						<option value="lifetime"><?php _e('Lifetime', 'memberdeck'); ?></option>
					</select>
				</div>
				<div id="recurring-input" class="form-input" style="display: none;">
					<label for="recurring-type"><?php _e('Recurring Type', 'memberdeck'); ?></label>
					<select name="recurring-type" id="recurring-type">
						<option value="weekly"><?php _e('Weekly', 'memberdeck'); ?></option>
						<option value="monthly"><?php _e('Monthly', 'memberdeck'); ?></option>
						<option value="annual"><?php _e('Annual', 'memberdeck'); ?></option>
					</select>
					<?php if (!$es) {
						echo '<div style="display: none">';
					} ?>
					<br/>
					<label for="plan"><?php _e('Stripe Plan Name', 'memberdeck'); ?><br/><?php _e('*can only be used once', 'memberdeck'); ?></label>
					<input type="text" name="plan" id="plan" value=""/>
					<?php if (!$es) {
						echo '</div>';
					} ?>
					<br/>
					<div class="inline">
						<input type="checkbox" name="limit_term" id="limit_term" value="1"/>
						<label for="limit_term"><?php _e('Limit Number of Payments', 'memberdeck'); ?></label>
					</div>
					<label for="term_length"><?php _e('Number of Payments', 'memberdeck'); ?></label>
					<input type="text" name="term_length" id="term_length" value="" />
				</div>
				<div class="form-input">
					<label for="license-count"><?php _e('Licenses per download', 'memberdeck'); ?></label>
					<input type="number" name="license-count" id="license-count" value="" />
				</div>
				<div class="form-input inline">
					<input type="checkbox" name="enable_renewals" id="enable_renewals" value="1"/> <label for="enable_renewals"><?php _e('Enable Renewals', 'memberdeck'); ?></label>
				</div>
				<div class="form-input hide">
					<label for="renewal_price"><?php _e('Renewal Price', 'memberdeck'); ?></label>
					<input type="text" name="renewal_price" id="renewal_price" value="" />
				</div>
				<div class="form-input inline">
					<input type="checkbox" name="enable_multiples" id="enable_multiples" value="1"/> <label for="enable_multiples"><?php _e('Enable Multiples', 'memberdeck'); ?></label>
				</div>
				<div class="form-input inline">
					<input type="checkbox" name="create_page" id="create_page" value="1"/> <label for="create_page"><?php _e('Create Checkout Page', 'memberdeck'); ?></label>
				</div>
				<?php if (isset($es) && $es == '1') { ?>
					<div class="form-input inline combine-products-checkbox" style="display:none;">
						<input type="checkbox" name="enable_combine_products" id="enable_combine_products" value="1" /> <label for="enable_combine_products"><?php _e('Add a subscription?', 'memberdeck'); ?></label>
					</div>
					<div class="form-input combine-products-selection hide" style="display:none;">
						<label for="renewal_price"><?php _e('Select Recurring Product', 'memberdeck'); ?></label>
						<select name="combined_recurring_product" id="combined_recurring_product">
							<option value="" selected="selected"><?php _e('Select Product', 'memberdeck'); ?></option>
							<?php foreach ($recurring_levels as $recurring_level) { ?>
								<option value="<?php echo $recurring_level->id ?>"><?php echo $recurring_level->level_name ?></option>
							<?php } ?>
						</select>
					</div>
				<?php } ?>
			</div>
			<div class="submit">
				<input type="button" name="idc_new_level_submit" id="idc-new-level-submit" class="button-primary" value="<?php _e('Create', 'memberdeck'); ?>"/>
				<!--<input type="submit" name="level-delete" id="level-delete" class="button button" value="<?php _e('Delete', 'memberdeck'); ?>"/>-->
			</div>
		</form>
	</div>
</div>