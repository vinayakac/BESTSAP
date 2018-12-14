jQuery(document).ready(function() {
	jQuery(document).bind('idc_lightbox_level_select', function(e, clickLevel) {
		// this is fired when we click on a project level on a project deck
		selLevel = jQuery('.idc_lightbox:visible select[name="level_select"] option[data-order="'+ clickLevel +'"]').val();
		disablePWYWRecurring(selLevel);
		setActiveLevel(selLevel);
	});

	jQuery('.idc_lightbox select[name="level_select"]').change(function(e) {
		// this is fired when we change the lightbox level selection
		if (jQuery(this).has(':visible')) {
			selLevel = jQuery(this).val();
			disablePWYWRecurring(selLevel);
			setActiveLevel(selLevel);
		}
	});

	function setActiveLevel(selLevel) {
		// we run this every time we need to update the lightbox values and data
		var trueLevel = jQuery('.level_select option[value="' + selLevel + '"]');
		jQuery(trueLevel).prop('selected', true);
		var truePrice = jQuery(trueLevel).data('price');
		var trueDesc = jQuery(trueLevel).data('desc');
		jQuery('.idc_lightbox input[name="total"]').val(truePrice);
		jQuery('.idc_lightbox:visible .text p').text(trueDesc);
		jQuery('.idc_lightbox:visible span.total').data('value', truePrice).text(truePrice);
	}

	function disablePWYWRecurring(selLevel) {
		// this function ensures that recurring levels have a fixed price
		level_type = jQuery('.level_select option[value="'+ selLevel +'"]').data('idc-level-type');
		if (onlyStripe) {
			if (level_type == "recurring") {
				jQuery('#total').attr('readonly', 'readonly');
			} else {
				jQuery('#total').removeAttr('readonly')
			}
		}
	}

	/* from idcf */

	jQuery(document).bind('idc_lightbox_general', function(e) {
		// this is fired when we click on a generic support now button
		var selLevel = jQuery('.idc_lightbox:visible select[name="level_select"]').val();
		disablePWYWRecurring(selLevel);
		setActiveLevel(selLevel);
		/*var levelDesc = jQuery('.idc_lightbox:visible select[name="level_select"] :selected').data('desc');
		var levelPrice = jQuery('.idc_lightbox:visible select[name="level_select"] :selected').data('price');
		jQuery('.idc_lightbox:visible .text p').text(levelDesc);
		jQuery('.idc_lightbox input[name="total"]').val(levelPrice);
		jQuery('.idc_lightbox:visible span.total').data('value', levelPrice).text(levelPrice);*/
	});
	jQuery('.idc_lightbox select[name="level_select"]').change(function(e) {
		// this is fired when we change the lightbox level selection
		/*if (jQuery(this).has(':visible')) {
			//console.log(e);
			selLevel = jQuery(this).val();
			levelDesc = jQuery('.idc_lightbox:visible select[name="level_select"] :selected').data('desc');
			levelPrice = jQuery('.idc_lightbox:visible select[name="level_select"] :selected').data('price');
			jQuery('.idc_lightbox:visible .text p').text(levelDesc);
			jQuery('.idc_lightbox input[name="total"]').val(levelPrice);
			jQuery('.idc_lightbox:visible span.total').data('value', levelPrice).text(levelPrice);
		}*/
	});
	jQuery(document).bind('idc_lightbox_level_select', function(e, clickLevel) {
		/*
		selLevel = jQuery('.idc_lightbox:visible select[name="level_select"] option[data-order="'+ clickLevel +'"]').val();
		//console.log('clickLevel: ', clickLevel, 'selLevel: ', selLevel);
		if (selLevel !== undefined && selLevel > 0) {
			levelDesc = jQuery('.idc_lightbox:visible select[name="level_select"] option[value="'+ selLevel +'"]').data('desc');
			levelPrice = jQuery('.idc_lightbox:visible select[name="level_select"] option[value="'+ selLevel +'"]').data('price');
			//console.log('levelDesc: ', levelDesc, 'levelPrice: ', levelPrice);
			jQuery('.idc_lightbox:visible .text p').text(levelDesc);
			jQuery('.idc_lightbox input[name="total"]').val(levelPrice);
			jQuery('.idc_lightbox:visible span.total').data('value', levelPrice).text(levelPrice);
			// console.log('selecting selLevel: ', selLevel);
			jQuery('.idc_lightbox:visible .level_select').val(selLevel);
			// console.log('selected selLevel: ', selLevel);
			jQuery('.lb_level_submit').removeAttr('disabled');
		}
		else {
			jQuery('.idc_lightbox:visible .text p').text('');
			jQuery('.idc_lightbox input[name="total"]').val(0);
			jQuery('.idc_lightbox:visible span.total').data('value', 0).text('0');
			jQuery('.lb_level_submit').attr('disabled','disabled');
		}
		*/
	});
});