<div class="wrap memberdeck">
	<div class="icon32" id="icon-options-general"></div><h2 class="title"><?php _e('Order Details', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="http://forums.ignitiondeck.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large button-primary"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="http://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large button-primary"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<br style="clear: both;"/>
	<div class="postbox-container" style="width:48%; margin-right: 3%">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Order Details', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<div class="form-input">
							<p>
								<label><b><?php _e('Transaction Id', 'memberdeck'); ?></b></label><br/>
								<?php echo $idc_order_details->transaction_id ?>
							</p>
						</div>
						
						<div class="form-input">
							<p>
								<label><b><?php _e('Order Date', 'memberdeck'); ?></b></label><br/>
								<?php echo $idc_order_details->order_date ?>
							</p>
						</div>
						
						<div class="form-input">
							<p>
								<label><b><?php _e('Status', 'memberdeck'); ?></b></label><br/>
								<?php echo ucfirst($idc_order_details->status) ?>
							</p>
						</div>
						
						<div class="form-input">
							<p>
								<label><b><?php _e('Price', 'memberdeck'); ?></b></label><br/>
								<?php echo $idc_order_details->price ?>
							</p>
						</div>
						<div class="form-input">
							<p>
								<label><b><?php _e('Product ID', 'memberdeck'); ?></b></label><br/>
								<?php echo $idc_order_details->level_id ?>
							</p>
						</div>
						<div class="form-input">
							<p>
								<label><b><?php _e('Product Name', 'memberdeck'); ?></b></label><br/>
								<?php echo (isset($idc_level_details->level_name) ? $idc_level_details->level_name : '<i>'.__('Product Removed', 'memberdeck').'</i>'); ?>
							</p>
						</div>
						<div class="form-input">
							<p>
								<label><b><?php _e('Product Price', 'memberdeck'); ?></b></label><br/>
								<?php echo (isset($idc_level_details->level_price) ? $idc_level_details->level_price : '<i>'.__('Product Removed', 'memberdeck').'</i>'); ?>
							</p>
						</div>
						<div class="form-input">
							<p>
								<label><b><?php _e('Product Type', 'memberdeck'); ?></b></label><br/>
								<?php echo (isset($idc_level_details->level_type) ? ucfirst($idc_level_details->level_type) : '<i>'.__('Product Removed', 'memberdeck').'</i>'); ?>
							</p>
						</div>
						<div class="form-input">
							<p>
								<label><b><?php _e('Transaction Type', 'memberdeck'); ?></b></label><br/>
								<?php echo (isset($idc_level_details->txn_type) ? ucfirst($idc_level_details->txn_type) : '<i>'.__('Unknown', 'memberdeck').'</i>'); ?>
							</p>
						</div>
						<?php if (!empty($idc_order_gateway)) { ?>
							<div class="form-input">
								<p>
									<label><b><?php _e('Gateway', 'memberdeck'); ?></b></label><br/>
									<?php echo ucfirst($idc_order_gateway['gateway']); ?>
								</p>
							</div>
							<div class="form-input">
								<p>
									<label><b><?php _e('Currency Code', 'memberdeck'); ?></b></label><br/>
									<?php echo $idc_order_gateway['currency_code']; ?>
								</p>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="postbox-container" style="width:48%; margin-right: 0%">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('User Information', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<div class="form-select">
							<p>
								<label><b><?php _e('First Name', 'memberdeck'); ?></b></label><br/>
								<?php echo stripslashes(html_entity_decode($userdata->first_name)); ?>
							</p>
						</div>
						<div class="form-select">
							<p>
								<label><b><?php _e('Last Name', 'memberdeck'); ?></b></label><br/>
								<?php echo stripslashes(html_entity_decode($userdata->last_name)); ?>
							</p>
						</div>
						<div class="form-select">
							<p>
								<label><b><?php _e('Email Address', 'memberdeck'); ?></b></label><br/>
								<a href="mailto:<?php echo stripslashes(html_entity_decode($userdata->user_email)); ?>"><?php echo stripslashes(html_entity_decode($userdata->user_email)); ?></a>
							</p>
						</div>
						<?php if (isset($idcf_order_details) && !empty($idcf_order_details)) { ?>
							<div class="form-select">
								<p>
									<label><b><?php _e('Address', 'memberdeck'); ?></b></label><br/>
									<?php echo stripslashes(html_entity_decode($idcf_order_details->address)); ?>
								</p>
							</div>
							<div class="form-select">
								<p>
									<label><b><?php _e('City', 'memberdeck'); ?></b></label><br/>
									<?php echo stripslashes(html_entity_decode($idcf_order_details->city)); ?>
								</p>
							</div>
							<div class="form-select">
								<p>
									<label><b><?php _e('State', 'memberdeck'); ?></b></label><br/>
									<?php echo stripslashes(html_entity_decode($idcf_order_details->state)); ?>
								</p>
							</div>
							<div class="form-select">
								<p>
									<label><b><?php _e('Zip Code', 'memberdeck'); ?></b></label><br/>
									<?php echo stripslashes(html_entity_decode($idcf_order_details->zip)); ?>
								</p>
							</div>
							<div class="form-select">
								<p>
									<label><b><?php _e('Country', 'memberdeck'); ?></b></label><br/>
									<?php echo stripslashes(html_entity_decode($idcf_order_details->country)); ?>
								</p>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php if (isset($crowdfunding_project) && $crowdfunding_project) { ?>
	<div class="postbox-container" style="width:48%; margin-right: 0%">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Project Information', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<div>
							<p>
								<label><b><?php _e('Project Name', 'memberdeck'); ?></b></label><br/>
								<?php echo $project_name; ?>
							</p>
						</div>
						<div>
							<p>
								<label><b><?php _e('Level', 'memberdeck'); ?></b></label><br/>
								<?php echo absint($idcf_order_details->product_level); ?>
							</p>
						</div>
						<div>
							<p>
								<label><b><?php _e('Price', 'memberdeck'); ?></b></label><br/>
								<?php echo $level_price; ?>
							</p>
						</div>
						<?php //if (number_format($order_data->prod_price, 2) != number_format($level_price, 2)) { ?>
						<div>
							<p>
								<label><b><?php _e('Manual Amount', 'memberdeck'); ?></b></label><br/>
								<?php echo $idcf_order_details->prod_price; ?>
							</p>
						</div>
						<?php //} ?>
						<div>
							<p>
								<label><b><?php _e('Level Description', 'memberdeck'); ?></b></label><br/>
								<?php echo stripslashes(html_entity_decode($level_desc)); ?>
							</p>
						</div>
						<div>
							<p>
								<label><b><?php _e('Status', 'memberdeck'); ?></b></label><br/>
								<?php echo ($idcf_order_details->status == 'C' ? __('Complete', 'memberdeck') : __('Pending', 'memberdeck')); ?>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
</div>