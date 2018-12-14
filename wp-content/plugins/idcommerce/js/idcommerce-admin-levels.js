//Get level-based creator permissions and check the boxes for them
function get_cperms(){
	jQuery.ajax({
		url: md_ajaxurl,
		type: 'POST',
		data: {action: 'idmember_get_cperms'},
		success: function(res) {
			//console.log(res);
			var cperms = JSON.parse(res);
			jQuery.each(cperms, function(k,v) {
				//console.log(v);
				//console.log(jQuery('.cassign[value="' + v + '"]').attr('checked', 'checked'));
				jQuery('.cassign[value="' + v + '"]').attr('checked', 'checked');
			});
			jQuery('#creator-permissions').change(function() {
				var temp = parseInt(jQuery("#creator-permissions").val());
				if (temp == '2') {
					jQuery("#allowed-creator-levels").show();
				} 
				else {
					jQuery("#allowed-creator-levels").hide();
				}	
			});
		}
	});
}
	
function get_levels() {
	jQuery.ajax({
		url: md_ajaxurl,
		type: 'POST',
		data: {action: 'idmember_get_levels'},
		success: function(res) {
			levels = JSON.parse(res);
			//console.log(levels);
			jQuery.each(levels, function() {
				// options for credit dropdown
				jQuery("#credit-assign").append(jQuery("<option/>", {
					value: this.id,
					text: this.level_name
				}));
				// options for level dropdown
				jQuery("#edit-level").append(jQuery("<option/>", {
					value: this.id,
					text: this.level_name
				}));
				jQuery("#default_product").append(jQuery("<option>", {
					value: this.id,
					text: this.level_name,
					selected: ((jQuery("#default_product").data('selected') == this.id) ? true : false)
				}));
				// options for level dropdown
				jQuery("#occ_level").append(jQuery("<option/>", {
					value: this.id,
					text: this.level_name
				}));
				// checkboxes for dasbhoard settings
				jQuery("#assign-checkbox").append(jQuery("<input/>", {
					type: 'checkbox',
					name: 'lassign[]',
					class: 'lassign',
					id: 'assign-' + this.id,
					value: this.id
				}));
				jQuery("#assign-checkbox").append(jQuery("<label/>", {
					text: this.level_name,
					for: 'assign-' + this.id
				}));
				jQuery("#assign-checkbox").append("<br/>");
				jQuery("select[name='export_product_choice']").append(jQuery("<option/>", {
					value: this.id,
					text: this.level_name
				}));
				// checkboxes for creator permissions
				jQuery("#allowed-creator-levels").append(jQuery("<input/>", {
					type: 'checkbox',
					name: 'cassign[]',
					class: 'cassign',
					id: 'cperm-' + this.id,
					value: this.id
				}));
				jQuery("#allowed-creator-levels").append(jQuery("<label/>", {
					text: this.level_name,
					for: 'cperm-' + this.id
				}));
				jQuery("#allowed-creator-levels").append("<br/>");
				// jQuery("select[name='export_product_choice']").append(jQuery("<option/>", {
				// 	value: this.id,
				// 	text: this.level_name
				// }));
				jQuery("#upgrade-levels").append(jQuery("<option/>", {
					value: this.id,
					text: this.level_name
				}));
			});
			jQuery("#edit-level").change(function() {
				var leveledit = parseInt(jQuery("#edit-level").val());
				if (jQuery(this).val() == idc_localization_strings.choose_product) {
					jQuery('.list-shortcode').hide();
					jQuery('input[name="create_page"]').removeAttr('disabled').removeAttr('checked');
				}
				else {
					jQuery('.list-shortcode').show();
					jQuery('input[name="create_page"]').attr('disabled', 'disabled');
				}
				if (leveledit) {
					//console.log(leveledit);
					jQuery("#product-type").val(levels[leveledit].product_type)
					jQuery("#level-name").val(levels[leveledit].level_name);
					jQuery("#level-price").val(levels[leveledit].level_price);
					jQuery("#credit-value").val(levels[leveledit].credit_value);
					jQuery("#txn-type").val(levels[leveledit].txn_type);
					jQuery("#level-type").val(levels[leveledit].level_type);
					if (levels[leveledit].recurring_type.length > 0) {
						jQuery("#recurring-type").val(levels[leveledit].recurring_type);
					}
					else {
						jQuery('#recurring-type').val('monthly');
					}
					var limit_term = levels[leveledit].limit_term;
					if (limit_term == 1) {
						jQuery('#limit_term').attr('checked', 'checked');
					}
					else {
						jQuery('#limit_term').removeAttr('checked');
					}
					jQuery('#term_length').val(levels[leveledit].term_length);
					jQuery("#plan").val(levels[leveledit].plan);
					jQuery('#license-count').val(levels[leveledit].license_count);
					var enable_renewals = levels[leveledit].enable_renewals;
					if (enable_renewals == 1) {
						jQuery('#enable_renewals').attr('checked', 'checked');
						jQuery('#renewal_price').parents('.form-input').show();
					}
					else {
						jQuery('#enable_renewals').removeAttr('checked');
						jQuery('#renewal_price').parents('.form-input').hide();
					}
					var enable_multiples = levels[leveledit].enable_multiples;
					if (enable_multiples == 1) {
						jQuery('#enable_multiples').attr('checked', 'checked');
					}
					else {
						jQuery('#enable_multiples').removeAttr('checked');
					}
					// check if level is combined
					if (levels[leveledit].combined_product !== '0') {
						jQuery("#combined_recurring_product").val(levels[leveledit].combined_product);
						jQuery(".combine-products-checkbox").removeClass('hide');
						jQuery("#enable_combine_products").attr('checked', 'checked');
						jQuery(".combine-products-selection").removeClass('hide');
					}
					else {
						jQuery("#combined_recurring_product").val("");
						jQuery("#enable_combine_products").removeAttr('checked');
						jQuery(".combine-products-selection").addClass('hide');
					}
					var custom_message = levels[leveledit].custom_message;
					if (custom_message == 1) {
						jQuery('#custom_message').attr('checked', 'checked');
					}
					else {
						jQuery('#custom_message').removeAttr('checked');
					}
					jQuery('#renewal_price').val(levels[leveledit].renewal_price);
					jQuery("#level-submit").val('Update');
					jQuery("#level-delete").show();
					jQuery(".list-shortcode").text(idc_localization_strings.purchase_form_shortcode + ': [idc_checkout product="' + leveledit + '"]');
				}
				else {
					jQuery('#product-type').val('purchase');
					jQuery("#level-name").val('');
					jQuery("#level-price").val('');
					jQuery("#credit-value").val(0);
					jQuery("#txn-type").val('capture');
					jQuery("#plan").val('');
					jQuery('#limit_term').removeAttr('checked');
					jQuery('#term_length').val('');
					jQuery('#license-count').val('');
					jQuery('#enable_renewals').removeAttr('checked');
					jQuery('#renewal_price').val('');
					jQuery('#enable_multiples').removeAttr('checked');
					jQuery("#level-submit").val('Create');
					jQuery("#level-delete").hide();
					jQuery("#custom_message").removeAttr('checked');
				}
				var type = jQuery("#level-type").val();
				if (type == 'recurring') {
					jQuery("#recurring-input").show();
					jQuery(".combine-products-checkbox").addClass('hide');
					jQuery(".combine-products-selection").addClass('hide');
				}
				else {
					jQuery("#recurring-input").hide();
					jQuery(".combine-products-checkbox").removeClass('hide');
				}
			});
			var type = jQuery("#level-type").val();
			jQuery("#level-type").change(function() {
				type = jQuery("#level-type").val();
				if (type == 'recurring') {
					jQuery("#recurring-input").show();
					jQuery(".combine-products-checkbox").addClass('hide');
					jQuery(".combine-products-selection").addClass('hide');
				}
				else {
					jQuery("#recurring-input").hide();
					jQuery(".combine-products-checkbox").removeClass('hide');
				}
			});
			jQuery('#enable_renewals').change(function() {
				if (jQuery('#enable_renewals').attr('checked') == 'checked') {
					jQuery('#renewal_price').parents('.form-input').show();
				}
				else {
					jQuery('#renewal_price').parents('.form-input').hide();
				}
			});
			jQuery('#select-upgradable-pathway').change(function(e) {
				// Get levels in a multi-select, but exclude the current selected level
				jQuery.ajax({
					url: md_ajaxurl,
					type: 'POST',
					data: {action: 'idc_pathway_details', pathway_id: jQuery('#select-upgradable-pathway').val()},
					success: function(res) {
						//console.log(res);
						json = JSON.parse(res);
						//jQuery('#upgrade-levels').html('');
						if (json.response == "success") {
							var pathways = json.upgrade_pathways;
							console.log('pathways: ', pathways);
							jQuery('#pathway-name').val(json.pathway_name);
							jQuery('#upgrade-levels').val(pathways);
						}
					}
				});
			});
			jQuery('#enable_combine_products').change(function(e) {
				if (jQuery(this).is(":checked")) {
					jQuery('.combine-products-selection').removeClass('hide');
				} else {
					jQuery('.combine-products-selection').addClass('hide');
				}
			});
			//Now that we know all level data is loaded, move onto creator permissions for levels
			get_cperms();
		}
	});
}