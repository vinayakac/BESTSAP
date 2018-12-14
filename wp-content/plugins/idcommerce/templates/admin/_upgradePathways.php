<div class="wrap memberdeck">
	<div class="icon32" id="icon-md"></div><h2 class="title"><?php _e('ID Commerce Settings', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="http://forums.ignitiondeck.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large button-primary"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="http://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large button-primary"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<div class="md-settings-container">
		<div class="postbox-container" style="width:70%; margin-right: 2%">
			<div class="metabox-holder">
				<div class="meta-box-sortables" style="min-height:0;">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e('Upgrade Pathways', 'memberdeck'); ?></span> <a id="product-settings" class="md_help_link">help</a></h3>
						<div id="product-settings-help" class="info inside" style="display: none;">
							<p><strong><?php _e('Create Pathway', 'memberdeck'); ?></strong>: <?php _e('To create a pathway, select a product from dropdown, then in the multi-select shown, select products to which the current product could be upgraded', 'memberdeck'); ?></p>
  						</div>
						<div class="inside">
							<form method="POST" action="" id="idmember-settings" name="idmember-settings">
								<div class="columns" style="width: 49%; margin-right: 3%">
									<div class="form-input">
										<label for="edit-level"><?php _e('Edit pathway', 'memberdeck'); ?></label>
										<select id="select-upgradable-pathway" name="select-upgradable-pathway">
											<option value=""><?php _e('Choose Pathway', 'memberdeck'); ?></option>
											<?php foreach ($pathways as $pathway) { ?>
												<option value="<?php echo $pathway->id ?>"><?php echo $pathway->pathway_name ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="form-input">
										<label for="pathway-name"><?php _e('Pathway name', 'memberdeck'); ?></label>
										<input type="text" id="pathway-name" name="pathway-name" />
									</div>
								</div>
								
								<div class="columns" style="width: 47%;">
									<div class="form-input">
										<label for="txn-type"><?php _e('Select product upgrades', 'memberdeck'); ?></label>
										<select name="upgrade-levels[]" id="upgrade-levels" multiple="multiple" style="width: 100%;">
										</select>
									</div>
								</div>
								<div class="submit">
									<input type="submit" name="pathway-submit" id="pathway-submit" class="button-primary" value="<?php _e('Save Pathways', 'memberdeck'); ?>"/>
									<input type="submit" name="pathway-delete" id="pathway-delete" class="button button" value="<?php _e('Delete Pathways', 'memberdeck'); ?>"/>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>