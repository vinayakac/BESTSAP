jQuery(document).ready(function($) {
	// If test is checked for Gateway settings
	if (jQuery('#test').is(':checked')) {
		jQuery('.lemonway-live').hide();
	} else {
		jQuery('.lemonway-test').hide();
	}

	jQuery('#test').click(function(e) {
		if (jQuery('#test').is(':checked')) {
			jQuery('.lemonway-live').hide();
			jQuery('.lemonway-test').show();
		} else {
			jQuery('.lemonway-test').hide();
			jQuery('.lemonway-live').show();
		}
	});
});