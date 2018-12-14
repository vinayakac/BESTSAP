<div class="wrap">
	<div class="postbox-container" style="width:95%; margin-right: 5%">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<h3 class="hndle"><span><?php echo $tr_Deck_Builder; ?></span></h3>
					<div class="inside">
						<p style="width: 50%"><?php echo $tr_Select_Components; ?></p>
						<form method="POST" action="" id="idmsg-settings" name="idmsg-settings">
							<div class="form-select">
								<p>
									<label for="deck_select"><?php echo $tr_Create_Select; ?></label><br/>
									<select name="deck_select" id="deck_select">
										<option><?php echo $tr_New_Deck; ?></option>
									</select>
								</p>
							</div>
							<div class="form-input">
								<p>
									<label for="deck_title"><?php echo $tr_Deck_Title; ?></label><br/>
									<input type="text" name="deck_title" id="deck_title" class="deck-attr-text" value="" />
								</p>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_title" id="project_title" class="deck-attr" value="1"/>
								<label for="project_title"><?php echo $tr_Project_Title; ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_image" id="project_image" class="deck-attr" value="1"/>
								<label for="project_image"><?php echo $tr_Product_Image; ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_bar" id="project_bar" class="deck-attr" value="1"/>
								<label for="project_bar"><?php echo $tr_Percentage_Bar; ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_pledged" id="project_pledged" class="deck-attr" value="1"/>
								<label for="project_pledged"><?php echo $tr_Total_Raised; ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_goal" id="project_goal" class="deck-attr" value="1"/>
								<label for="project_goal"><?php echo $tr_Project_Goal; ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_pledgers" id="project_pledgers" class="deck-attr" value="1"/>
								<label for="project_pledgers"><?php echo $tr_Total_Orders; ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="days_left" id="days_left" class="deck-attr" value="1"/>
								<label for="days_left"><?php echo $tr_Days_To_Go; ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_end" id="project_end" class="deck-attr" value="1"/>
								<label for="project_end"><?php echo $tr_End_Date; ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_button" id="project_button" class="deck-attr" value="1"/>
								<label for="project_button"><?php echo $tr_Buy_Button; ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_description" id="project_description" class="deck-attr" value="1"/>
								<label for="project_description"><?php echo $tr_meta_project_description; ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_levels" id="project_levels" class="deck-attr" value="1"/>
								<label for="project_levels"><?php echo $tr_Levels; ?></label>
							</div>
							<div class="submit">
								<input type="submit" name="deck_submit" id="submit" class="button button-primary"/>
								<input type="submit" name="deck_delete" id="deck_delete" class="button" value="Delete Deck" style="display: none;"/>
							</div>
						</form>
					</div>
				</div>
				<div class="postbox">
					<h3 class="hndle"><span><?php echo $tr_General_Settings; ?></span></h3>
					<div class="inside">
						<form name="formSettings" action="" method="post">
							<ul>
								<li>
									<label for="theme_value" class="title"><?php echo $tr_Widget_Theme; ?></label>
									<a href="javascript:toggleDiv('hTheme');" class="idMoreinfo">[?]</a>
									<div id="hTheme" class="idMoreinfofull">
									<div class="idSSwrap"><span>IgnitionDeck (Light)</span><img src="<?php echo plugins_url('/images/help/ss-1.jpg', dirname(dirname(__FILE__))); ?>"></div>
									<div class="idSSwrap"><span>IgnitionDeck (Dark)</span><img src="<?php echo plugins_url('/images/help/ss-1d.jpg', dirname(dirname(__FILE__))); ?>"></div>
									<div class="idSSwrap"><span>Corporate</span><img src="<?php echo plugins_url('/images/help/ss-2.jpg', dirname(dirname(__FILE__))); ?>"></div>
									<div class="idSSwrap"><span>Clean (Light)</span><img src="<?php echo plugins_url('/images/help/ss-3.jpg', dirname(dirname(__FILE__))); ?>"></div>
									<div class="idSSwrap"><span>Clean (Dark)</span><img src="<?php echo plugins_url('/images/help/ss-3d.jpg', dirname(dirname(__FILE__))); ?>"></div>
									<div class="idSSwrap"><span>Skyscraper (Light)</span><img src="<?php echo plugins_url('/images/help/ss-4.jpg', dirname(dirname(__FILE__))); ?>"></div>
									<div class="idSSwrap"><span>Skyscraper (Dark)</span><img src="<?php echo plugins_url('/images/help/ss-4d.jpg', dirname(dirname(__FILE__))); ?>"></div>
									<div class="clear"></div>
									</div>
									<div><select name="theme_value" id="theme_value">
										<option <?php echo (isset($data) && $data->theme_value == "style1" ? 'selected="selected"' : '')?> value="style1"><?php echo $tr_IgnitionDeck_Light; ?></option>
										<option <?php echo (isset($data) && $data->theme_value == "style1-dark" ? 'selected="selected"' : '')?> value="style1-dark"><?php echo $tr_IgnitionDeck_Dark; ?></option>
										<option <?php echo (isset($data) && $data->theme_value == "style2" ? 'selected="selected"' : '')?> value="style2"><?php echo $tr_Clean; ?></option>
										<option <?php echo (isset($data) && $data->theme_value == "style2-dark" ? 'selected="selected"' : '')?> value="style2-dark"><?php echo $tr_Clean_Dark; ?></option>
										<option <?php echo (isset($data) && $data->theme_value == "style3" ? 'selected="selected"' : '')?> value="style3"><?php echo $tr_Skyscraper; ?></option>
										<option <?php echo (isset($data) && $data->theme_value == "style3-dark" ? 'selected="selected"' : '')?> value="style3-dark"><?php echo $tr_Skyscraper_Dark; ?></option>
										<option <?php echo (isset($data) && $data->theme_value == "style4" ? 'selected="selected"' : '')?> value="style4"><?php echo $tr_Corporate; ?></option>
										<?php do_action('id_skin'); ?>
									</select></div>
									<br/>
									<label for="skin-instructions" class="title"><?php echo $tr_Skin_Instructions; ?></label>
									<a href="javascript:toggleDiv('hSkin');" class="idMoreinfo">[?]</a>
									<div id="hSkin" class="idMoreinfofull">
										<p><?php _e('How to add Deck skins', 'ignitiondeck'); ?>:</p>
										<ol>
											<li><?php _e('Upload skin assets to the /skins directory via FTP', 'ignitiondeck'); ?>.</li>
											<li><?php _e('CSS file will be named ignitiondeck-skinname.css. Enter the &lsquo;skinname&rsquo; in the box and click &lsquo;Add Skin&rsquo;', 'ignitiondeck'); ?>.</li>
											<li><?php _e('To delete, select skin and click &lsquo;Delete Skin&rsquo;', 'ignitiondeck'); ?>.</li>
										</ol>
									</div>
									<br/>
									<div>
										<input type="submit" name="add-skin" id="add-skin" class="button" value="<?php echo $tr_Add_Skin; ?>"/>
										<input type="text" name="skin-name" id="skin-name"/>
									</div>
									<br/>
									<div>
										<input type="submit" name="delete-skin" id="delete-skin" class="button" value="<?php echo $tr_Delete_Skin; ?>"/>
										<select name="deleted-skin" id="deleted-skin">
											<option>-- <?php echo $tr_Delete_Skin; ?> --</option>
											<?php echo $deleted_skin_list; ?>
										</select>
									</div>	
								</li>
								
								<li>
									<div><input <?php echo (isset($data) && $data->id_widget_logo_on == 1 ? 'checked="checked"' : ''); ?> name="id_widget_logo_on" type="checkbox" id="id_widget_logo_on" class="main-setting" value="1" /> 
									<label for="id_widget_logo_on"><img src="<?php echo plugins_url('/images/ignitiondeck-menu.png', dirname(dirname(__FILE__))); ?>"><?php echo $tr_Ignition_Deck_Logo; ?></label>
									<a href="javascript:toggleDiv('hLogo');" class="idMoreinfo">[?]</a>
									<div id="hLogo" class="idMoreinfofull">
									<img src="<?php echo plugins_url('/images/help/powered-by-id.jpg', dirname(dirname(__FILE__))); ?>"><?php echo $tr_text_this_allow_deactive; ?>
									</div></div>
								</li>
								<li>
									<strong><?php echo $tr_Affiliate_Settings; ?></strong>
									<div>
									<label for="id_widget_link"><?php echo $tr_Affiliate_Link; ?></label>
									<a href="javascript:toggleDiv('hAffiliate');" class="idMoreinfo">[?]</a>
									<div id="hAffiliate" class="idMoreinfofull">
									<a href="http://www.shareasale.com/shareasale.cfm?merchantID=46545" alt="IgnitionDeck Affiliate" title="IgnitionDeck Affiliate Program" target="_blank">Click here</a> to sign up for our referral program, and paste your unique URL here. Set this to http://ignitiondeck.com for default setting.
									</div><br>
									<input name="id_widget_link" type="text" id="id_widget_link" value="<?php echo $affiliate_link; ?>" /> 
									</div>
								</li>
								
								<li>
									<div>
									<?php if(count($data) > 0) {?>
										<input class="button-primary" type="submit" name="btnIgnSettings" id="btnAddOrder" value="<?php echo $tr_Update; ?>" />
									<?php } else { ?>
										<input class="button-primary" type="submit" name="btnIgnSettings" id="btnAddOrder" value="<?php echo $tr_Add; ?>" />
									<?php } ?>
									</div>
								</li>
							</ul>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>