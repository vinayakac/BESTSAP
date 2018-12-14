var idcf_idc_selected_level = '';
jQuery(document).ready(function() {
	var choice = jQuery("input[name='protect-choice']:checked").val();
	//console.log(choice);
	if (choice == 'no' || choice == undefined || choice == 0) {
		jQuery("#level-check").hide();
	}
	else {
		jQuery("#level-check").show();
	}
	jQuery("input[name='protect-choice']").click(function() {
		var choice = jQuery("input[name='protect-choice']:checked").val();
		console.log(choice);
		if (choice == 'no' || choice == undefined || choice == 0) {
			jQuery("#level-check").hide();
		}
		else {
			jQuery("#level-check").show();
		}
	});
	jQuery.ajax({
		url: md_ajaxurl,
		type: 'POST',
		data: {action: 'idmember_get_levels'},
		success: function(res) {
			//console.log(res);
			json = JSON.parse(res);
			jQuery.each(json, function() {
				jQuery("#md-level").append('<option value="' + this.id + '">' + this.level_name + '</option>');
			});
			var mdLevel = jQuery("#md-level").val();
			if (mdLevel > 0) {
				jQuery(".md-product").text(mdLevel);
				jQuery('#md-level').change(function() {
					mdLevel = jQuery("#md-level").val();
					jQuery(".md-product").text(mdLevel);
				});
			}
		}
	});
	
	/***** For IDC product popup in IDCF Project creation *****/
	jQuery(document).on('change', '[name*="level_idc_product"]', function(e) {
		var level_no = jQuery(this).attr('id').replace('level_idc_product_', '');
		if (jQuery(this).val() == "add_new_product") {
			//addProductLightbox();
			openLBGlobal(jQuery('.idc_add_product_popup'));
			jQuery(this).parents('.projectmeta-levelbox').children('.ign_level_selected_option').find('#idc_product_selected_' + level_no).val('');
			idcf_idc_selected_level = jQuery(this);
		} else {
			// console.log('level_no: ', level_no);
			// console.log(jQuery(this));
			var hidden_selected = jQuery(this).parents('.projectmeta-levelbox').children('.ign_level_selected_option').find('#idc_product_selected_' + level_no);
			if (jQuery(this).val() !== "") {
				jQuery(hidden_selected).val('old_level');
			} else {
				jQuery(hidden_selected).val('');
			}
		}
	});
	// Changing id of 1st level IDC product <select>
	jQuery('[id="level_idc_product[]"]').attr('id', 'level_idc_product_1');
	jQuery('label[for="level_idc_product[]"]').attr('for', 'level_idc_product_1');
	// jQuery('[id="idc_product_selected_1"]').val('');
	// if popup markup exists
	if (jQuery('#id-idc-add-product-popup').length > 0) {
		jQuery('#id-idc-add-product-popup').hide();
		
		// Event called after a new level is added in Project
		jQuery(document).on('idcfAddLevelAfter', function (e, element_number) {
			var clone = jQuery(jQuery('.ign_idc_level_select').get(0)).clone();
			jQuery(clone).find('[name="level_idc_product_1"]').attr('name', 'level_idc_product_' + element_number);
			jQuery(clone).find('[id="level_idc_product_1"]').attr('id', 'level_idc_product_' + element_number);
			jQuery(clone).find('label[for="level_idc_product_1"]').attr('for', 'level_idc_product_' + element_number);
			// Cloning the hidden field div
			var clone_hidden = jQuery(jQuery('.ign_level_selected_option').get(0)).clone();
			jQuery(clone_hidden).find('[id="idc_product_selected_1"]').attr('id', 'idc_product_selected_' + element_number);
			jQuery(clone_hidden).find('[name="idc_product_selected_1"]').attr('name', 'idc_product_selected_' + element_number);
			// Adding this select to newly added level
			jQuery('.projectmeta-levelbox[level="'+ element_number +'"]').children('.ign_projectmeta_reward_title').after(jQuery(clone));
			// Adding the hidden field after the select
			jQuery('.projectmeta-levelbox[level="'+ element_number +'"]').children('.ign_idc_level_select').after(jQuery(clone_hidden));
		});
		
		// For adding some JS to popup
		get_levels();
		jQuery('#renewal_price').parents('.form-input').hide();
		// Creating a new product
		jQuery('#idc-new-level-submit').click(function(e) {
			e.preventDefault();
			// Create Product using ajax, and then close the popup box with the new level selected
			jQuery.ajax({
				url: md_ajaxurl,
				type: 'POST',
				data: {
					action: 'idc_id_add_product',
					'level-name': jQuery('[name="level-name"]').val(),
					'level-price': jQuery('[name="level-price"]').val(),
					'product-type': jQuery('[name="product-type"]').val(),
					'credit-value': jQuery('[name="credit-value"]').val(),
					'txn-type': jQuery('[name="txn-type"]').val(),
					'level-type': jQuery('[name="level-type"]').val(),
					'recurring-type': jQuery('[name="recurring-type"]').val(),
					'plan': jQuery('[name="plan"]').val(),
					'limit_term': ((jQuery('[name="limit_term"]').is(":checked")) ? '1' : ''),
					'term_length': jQuery('[name="term_length"]').val(),
					'license-count': jQuery('[name="license-count"]').val(),
					'enable_renewals': ((jQuery('[name="enable_renewals"]').is(":checked")) ? '1' : ''),
					'renewal_price': jQuery('[name="renewal_price"]').val(),
					'enable_multiples': ((jQuery('[name="enable_multiples"]').is(":checked")) ? '1' : ''),
					'create_page': ((jQuery('[name="create_page"]').is(":checked")) ? '1' : ''),
					'enable_combine_products': ((jQuery('[name="enable_combine_products"]').is(":checked")) ? '1' : ''),
					'combined_recurring_product': jQuery('[name="combined_recurring_product"]').val()
				},
				success: function(res) {
					console.log(res);
					json = JSON.parse(res);
					if (json.response == "success") {
						jQuery('.ign_idc_level_select').each(function(index, element) {
							var level_option = jQuery(this).find('option[value="'+ json.product_id +'"]');
							var level_select = jQuery(this).find('[name="level_idc_product_'+ (index + 1) +'"]');
							console.log('level_option.length: ', level_option.length);
							if (level_option.length < 1) {
								console.log('level name: ', jQuery('#level-name').val());
								jQuery(level_select).append(jQuery("<option/>", {
									value: json.product_id,
									text: jQuery('#level-name').val()
								}));
								console.log('json.product_id: ', json.product_id);
								console.log('idcf_idc_selected_level: ', idcf_idc_selected_level);
								jQuery(idcf_idc_selected_level).val(json.product_id);
							}
						});
						// Close popup
						closeLBGlobal();
						// var level_no = jQuery(idcf_idc_selected_level).attr('id').replace('level_idc_product_', '');
						// console.log('level_no: ', level_no, ', jQuery(\'#idc_product_selected_\' + level_no): ', jQuery('#idc_product_selected_' + level_no));
						// jQuery('#idc_product_selected_' + level_no).val('new_level');
						idcf_idc_selected_level = '';
					}
				}
			});
		});
		// Loading levels on edit
		if (jQuery('#hiddenaction').val() == "editpost") {
			// Add new levels product boxes
			var levels_count = parseFloat(jQuery('[name="level-count"]').val());
			if (levels_count > 0) {
				// Getting levels associated with this project
				var post_id = jQuery('#post_ID').val();
				jQuery.ajax({
					url: md_ajaxurl,
					type: 'POST',
					data: {action: 'idc_id_get_project_levels', postId: post_id, levelsCount: levels_count},
					success: function(res) {
						console.log('idc_id_get_project_levels: ', res);
						json = JSON.parse(res);
						for (var i=0 ; i < levels_count ; i++) {
							// As level 1 product popup is already there, we need select option for higher levels
							if (i > 0) {
								var element_number = i + 1;
								var clone = jQuery(jQuery('.ign_idc_level_select').get(0)).clone();
								jQuery(clone).find('[name="level_idc_product_1"]').attr('name', 'level_idc_product_' + element_number);
								jQuery(clone).find('[id="level_idc_product_1"]').attr('id', 'level_idc_product_' + element_number);
								jQuery(clone).find('label[for="level_idc_product_1"]').attr('for', 'level_idc_product_' + element_number);
								// Cloning the hidden field div
								var clone_hidden = jQuery(jQuery('.ign_level_selected_option').get(0)).clone();
								jQuery(clone_hidden).find('[id="idc_product_selected_1"]').attr('id', 'idc_product_selected_' + element_number);
								jQuery(clone_hidden).find('[name="idc_product_selected_1"]').attr('name', 'idc_product_selected_' + element_number);
								// Adding this select to newly added level
								jQuery('.projectmeta-levelbox[level="'+ element_number +'"]').children('.ign_projectmeta_reward_title').after(jQuery(clone));
								jQuery('#level_idc_product_' + element_number).val(json.project_products[i]);
								// Adding the hidden field after the select
								jQuery('.projectmeta-levelbox[level="'+ element_number +'"]').children('.ign_idc_level_select').after(jQuery(clone_hidden));
								jQuery('#idc_product_selected_' + element_number).val('old_level');
							}
							else {
								jQuery('#level_idc_product_1').val(json.project_products[0]);
							}
						}
					}
				});
			}
		}
	}
	// Load products when Project is edited
	
	// Loading script with get_levels() function
	//jQuery('body').load(idc_pluginurl + '/js/idcommerce-admin-level.js');
});