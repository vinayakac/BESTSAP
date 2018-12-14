<div class="wrap memberdeck">
	<div class="icon32" id="icon-options-general"></div><h2 class="title"><?php _e('Order Edit', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="http://forums.ignitiondeck.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large button-primary"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="http://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large button-primary"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<br style="clear: both;"/>
	<div class="postbox-container" style="width:50%; margin-right: 5%">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Edit', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<form method="POST" action="" id="idc_lemonway_settings" name="idc_lemonway_settings">
							<div class="form-input">
								<p>
									<label for="status"><?php _e('Status', 'memberdeck'); ?></label><br/>
									<select name="status" id="status">
										<option <?php echo (($order_data->status == 'active' ? 'selected="selected"' : ''))?> value="active"><?php _e('Active', 'memberdeck'); ?></option>
										<option <?php echo (($order_data->status == 'cancelled' ? 'selected="selected"' : ''))?> value="cancelled"><?php _e('Cancelled', 'memberdeck'); ?></option>
									</select>
								</p>
							</div>
							<div class="form-input">
								<p>
									<label for="order_date"><?php _e('Order Date', 'memberdeck'); ?></label><br/>
									<input type="text" class="idc-attach-datepicker textbox" id="order_date" name="order_date" value="<?php echo $datetime->format('m/d/Y') ?>" />
								</p>
							</div>
							<div class="submit">
								<input class="button button-primary" type="submit" name="btn_update_order" id="btn_update_order" value="<?php _e('Update Order', 'memberdeck'); ?>" />
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>