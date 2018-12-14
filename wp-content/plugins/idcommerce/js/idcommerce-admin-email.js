jQuery(document).ready(function() {
	var customProductSelect = jQuery('select[name="custom_product_select"]');
	jQuery(customProductSelect).parent('.form-row').hide();
	var selName = jQuery('select[name="template_select"]').children('option:selected').attr('name');
	jQuery(document).trigger('idc_template_select', selName);
	jQuery('.email_text').hide();
	jQuery('.custom_product_status').hide();
	jQuery('div.' + selName).show();
	jQuery('#restore_default').attr('name', 'restore_default_' + selName);
	jQuery('#send_test').attr('name', 'send_test_' + selName);
	jQuery('select[name="template_select"]').change(function() {
		selName = jQuery(this).children('option:selected').attr('name');
		jQuery('#restore_default').attr('name', 'restore_default_' + selName);
		jQuery('#send_test').attr('name', 'send_test_' + selName);
		//console.log(selName);
		jQuery('.email_text').hide();
		jQuery('div.' + selName).show();
		jQuery(document).trigger('idc_template_select', selName);
	});
	jQuery(document).bind('idc_template_select', function(e, selName) {
		if (selName == 'custom_product_message') {
			jQuery(customProductSelect).parent('.form-row').show();
			jQuery('.custom_product_status').show();
			custom_product_select();
		}
		else {
			jQuery(customProductSelect).parent('.form-row').hide();
			jQuery('.custom_product_status').hide();
			jQuery('#restore_default').show();
		}
	});
	jQuery(customProductSelect).change(function(e) {
		custom_product_select();
	});
	function custom_product_select() {
		var selectedValue = jQuery(customProductSelect).children('option:selected');
		var customProduct = jQuery(selectedValue).val();
		var productStatus = jQuery(selectedValue).data('status');
		jQuery('.custom_product_text').hide();
		jQuery('.form-row.custom_product_message_' + customProduct + '_text').show();
		jQuery('button[name="restore_default_custom_product_message"]').hide();
		jQuery('select[name="custom_product_status"] option[value="' + productStatus + '"]').prop('selected', true);
		jQuery('#send_test').attr('name', 'send_test_' + selName + '_' + customProduct);
	}
});