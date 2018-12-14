<div class="wrap idf ignitiondeck">
	<div class="icon32" id="icon-idf"></div><h2 class="title"><?php _e('IgnitionDeck Framework', 'idf'); ?></h2>
	<div class="help">
		<a href="http://forums.ignitiondeck.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large button-primary"><?php _e('Support', 'idf'); ?></button></a>
		<a href="http://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large button-primary"><?php _e('Documentation', 'idf'); ?></button></a>
	</div>
	<div class="md-settings-container">
		<div class="postbox-container" style="width:95%; margin-right: 5%">
			<div class="metabox-holder">
				<div class="meta-box-sortables" style="min-height:0;">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e('IgnitionDeck Account', 'idf'); ?></span></h3>
						<div class="inside">
							<div class="form-select form-row">
								<?php if (!isset($idf_registered) || !$idf_registered) { ?>
								<p><?php _e('Register for an IgnitionDeck account in order to activate crowdfunding functionality.', 'idf'); ?></p>
								<a href="http://ignitiondeck.com/id/id-launchpad-checkout/" class="button button-large button-primary" id="id_account" name="id_account"><?php _e('Register', 'idf'); ?></a>
								<?php } else { ?>
								<div class="getting_started">
									<p><?php _e('You have registered successfully, and IgnitionDeck has been installed and activated.', 'idf'); ?></p>
									<p><?php _e('Here\'s how to get started using IgnitionDeck:', 'idf'); ?></p>
									<ol>
										<li><a href="<?php echo site_url('wp-admin/themes.php'); ?>"><?php _e('Activate Theme 500', 'idf'); ?></a> <?php _e(', if you wish to use it', 'idf'); ?></li>
										<li><a href="<?php echo site_url('wp-admin/admin.php?page=ignitiondeck'); ?>"><?php _e('Configure IgnitionDeck Crowdfunding', 'idf'); ?></a>&nbsp;&nbsp;&nbsp;<a target="_blank" href="http://ignitiondeck.com/id/documentation/quickstart/"><i class="fa fa-file-text-o"></i></a></li>
										<li><a href="<?php echo site_url('wp-admin/post-new.php?post_type=ignition_product'); ?>"><?php _e('Create your first project', 'idf'); ?></a>&nbsp;&nbsp;&nbsp;<a target="_blank" href="http://ignitiondeck.com/id/documentation/ignitiondeck-crowdfunding/create-a-project/"><i class="fa fa-file-text-o"></i></a></li>
										<li><?php _e('Ready to accept pledges', 'idf'); ?>? <a target="_blank" href="http://ignitiondeck.com/id/ignitiondeck-pricing/"><?php _e('Upgrade to a premium version of the plugins', 'idf'); ?></a> <?php _e('to activate a payment gateway.', 'idf'); ?></li>
										<li><?php _e('Use the commerce selector below to choose an eCommerce platform for use with IgnitionDeck Crowdfunding (IDCF)', 'idf'); ?></li>
									</ol>
									<button id="idf_reset_account" class="button button-secondary"><?php _e('Disconnect Account', 'idf'); ?></button>
								</div>
								<?php } ?>
							</div>
						</div>
					</div>
					<?php if (class_exists('ID_Project')) { ?>
					<div class="postbox">
						<h3 class="hndle"><span><?php _e('IDCF Commerce Settings', 'idf'); ?></span></h3>
						<div class="inside">
							<form id="idf_commerce" name="idf_commerce" method="POST" action="">
								<p><?php _e('Select a commerce platform for use with IgnitionDeck Crowdfunding', 'idf'); ?></br></p>
								<p><em><?php _e('Note: Use of IgnitionDeck Commerce and/or WooCommerce requires purchase of an IgnitionDeck License.', 'idf'); ?></em></br></p>
								<div class="form-select form-row">
									<label for="commerce_selection" style="font-weight: bold;"><?php _e('Commerce Platform', 'idf'); ?></label>
									<p>
										<select name="commerce_selection" id="commerce_selection">
											<?php if (in_array('idc', $platforms)) { ?>
												<option value="idc" <?php echo (isset($platform) && $platform == 'idc' ? 'selected="selected"' : ''); ?>><?php _e('IgnitionDeck Commerce', 'idf'); ?></option>
											<?php } if (in_array('wc', $platforms)) { ?>
												<option value="wc" <?php echo (isset($platform) && $platform == 'wc' ? 'selected="selected"' : ''); ?>><?php _e('WooCommerce', 'idf'); ?></option>
											<?php } if (in_array('edd', $platforms) && idf_has_edd()) { ?>
												<option value="edd" <?php echo (isset($platform) && $platform == 'edd' ? 'selected="selected"' : ''); ?>><?php _e('Easy Digital Downloads', 'idf'); ?></option>
											<?php } ?>
											<option value="legacy" <?php echo (!isset($platform) || $platform == 'legacy' ? 'selected="selected"' : ''); ?>><?php _e('Legacy IgnitionDeck', 'idf'); ?></option>
										</select>
									</p>
								</div>
								<div class="form-input">
									<input type="submit" name="commerce_submit" class="button button-primary" value="<?php _e('Save', 'idf'); ?>"/>
								</div>
							</form>
							</br>
						</div>
					</div>
					<?php } ?>
					<?php if ($super) { ?>
					<div class="postbox">
						<h3 class="hndle"><span><?php _e('License Settings', 'idf'); ?></span></h3>
						<div class="inside">
							<form name="licenseSettings" action="" method="post">
								<p><?php _e('Entering your <a href="http://ignitiondeck.com/id/documentation/ignitiondeck-crowdfunding/setup-ignitiondeck/ignitiondeck-license-keys/" target="_blank">license key</a> will enable automatic updates via the WordPress admin for the duration of your license period.', 'idf'); ?></p>
								<ul>
									<?php if (idf_has_idcf()) { ?>
									<li>
										<label for="idcf_license_key" class=""><i class="fa fa-key"></i> <?php _e('IDCF License Key', 'idf'); ?></label><br/>
										<input type="text" name="idcf_license_key" id="idcf_license_key" value="<?php echo (isset($idcf_license_key) ? $idcf_license_key : ''); ?>"/>
									</li>
									<?php } ?>
									<?php if (idf_has_idc()) { ?>
									<li>
										<label for="idc_license_key" class=""><i class="fa fa-key"></i> <?php _e('IDC License Key', 'memberdeck'); ?></label><br/>
										<input type="text" name="idc_license_key" id="idc_license_key" value="<?php echo (isset($idc_license_key) ? $idc_license_key : ''); ?>"/>
									</li>
									<?php } ?>
									<li>
										<button class="button button-primary"><?php _e('Validate', 'ignitiondeck'); ?></button>
									</li>
								</ul>
							</form>
							<div class="license_validation">
								<p>
									<?php echo ($is_pro || $is_basic || $is_idc_licensed ? '<i class="fa fa-check"></i>'.__(' License Valid for', 'ignitiondeck').$type_msg : 'You are using IgnitionDeck Basic.<br/><a href="http://ignitiondeck.com/id/ignitiondeck-pricing/?utm_source=licensepage&utm_medium=link&utm_campaign=freemium" target="_blank">Upgrade now</a> to receive support, commerce, and free updates for a year.'); ?>
								</p>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>