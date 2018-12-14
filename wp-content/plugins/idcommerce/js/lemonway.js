jQuery(document).ready(function($) {
	// If we are on Checkout page
	if (jQuery(".checkout-wrapper").length > 0) {
		var idset = jQuery("#payment-form #stripe-input").data('idset');
		var customerId = jQuery('#stripe-input').data('customer-id');
		var txnType = jQuery("#payment-form").data('txn-type');
		var renewable = jQuery('#payment-form').data('renewable');
		var type = jQuery("#payment-form").data('type');
		var limitTerm =  jQuery("#payment-form").data('limit-term');
		var termLength = jQuery('#payment-form').data('term-length');
		var formattedPrice = jQuery(".product-price").text();
	
		// Remove lemonway if it's not enabled
		// console.log('idc_elw: ', idc_elw);
		if (idc_elw == "0") {
			jQuery('#payment-form #pay-with-lemonway').parent('div').remove();
		} else {
			// If only one gateway is active, i.e., LemonWay
			// console.log("jQuery('.pay_selector:visible').length: ", jQuery('.pay_selector:visible').length);
			if (jQuery('.pay_selector:visible').length <= 1) {
				lemonWaySelected(formattedPrice, idc_localization_strings.complete_checkout, idc_lemonway_method);
			}
		}

		// When card type is selected
		jQuery('#card-type-hidden').val('0');
		jQuery('#card_type_select').change(function(e) {
			jQuery('#card-type-hidden').val(jQuery(this).val());
		});

		jQuery('.pay_selector').click(function(e) {
			if (jQuery(this).attr('id') !== "pay-with-lemonway") {
				jQuery('.card-type-field').hide();
			}
		});
		// LemonWay payment gateway selected
		jQuery('#payment-form #pay-with-lemonway').click(function(e) {
			e.preventDefault();
			lemonWaySelected(formattedPrice, idc_localization_strings.complete_checkout, idc_lemonway_method);
			jQuery(this).addClass('active');
			jQuery('.finaldesc').hide();
			jQuery("#finaldescStripe").show();
		});
		
		jQuery(document).on('idcPaymentChecksAfter', function(e, pwywPrice, queryString, fields) {
			e.preventDefault();
			// LemonWay payments submit
			if (jQuery("#id-main-submit").attr("name") == "submitPaymentLemonWay") {
				jQuery(".payment-errors").text("");
				jQuery("#id-main-submit").text(idc_localization_strings.processing + "...");
				var pid = jQuery("#payment-form").data('product');
				var fname = jQuery(".first-name").val();
				var lname = jQuery(".last-name").val();
				var email = jQuery("#payment-form .email").val();
				var pw = jQuery(".pw").val();
				var card = jQuery('.card-number').val();
				var exp_month = jQuery('.card-expiry-month').val();
				var exp_year = jQuery('.card-expiry-year').val();
				if (exp_year.length == 2) {
					exp_year = '20' + exp_year;
				}
				var expiry = exp_month + '/' + exp_year;
				var cc_code = jQuery('.card-cvc').val();
				var customer = ({'product_id': pid,
							    	'first_name': fname,
									'last_name': lname,
									'email': email,
									'pw': pw});
				console.log('idset: ', idset, ', customerId: ', customerId);
				if (!idset || !customerId) {
					var token = 'none';
				}
				else {
					var token = 'customer';
				}
				// If we are using 3D secure method
				if (idc_lemonway_method == "3dsecure") {
					console.log('before sending ajax request');
					// Getting wallet_id/token and redirecting to LemonWay page if payment method is 3D secure
					jQuery.ajax({
						url: memberdeck_ajaxurl,
						type: 'POST',
						data: {action: 'idc_lemonway_checkout_method_details', Customer: customer, PWYW: pwywPrice, Renewable: renewable, ProductId: pid, txnType: txnType, queryString: queryString, Fields: fields.posts},
						success: function(res) {
							// console.log('json response: ', res);
							json = JSON.parse(res);
							// If we have got the wallet_id and token successfully
							if (json.response == "success") {
								// Placing the values in the form, and submitting it
								// jQuery('#idc_lemonway_3dsecure_form').attr('action', json.form_action);
								// jQuery('#idc_lemonway_3dsecure_form input[name="DATA"]').val(json.data_value);
								// jQuery('#idc_lemonway_3dsecure_form input[name="VISA"]').trigger('click');
								jQuery('#ppload').html(json.form).css('display', 'none');
								var card_type = jQuery('#card_type_select').val();
								var card_name = jQuery('#card_type_select option[value="'+ card_type +'"]').html();
								card_name = card_name.toUpperCase();
								jQuery('#ppload input[name="'+ card_name +'"]').trigger('click');
								// jQuery(paymentForm).attr('id', 'idc_lemonway_3ds_form');
								// console.log('visa_button: ', jQuery('#idc_lemonway_3ds_form input[name="VISA"]'));
							}
							else {
								jQuery('#id-main-submit').removeAttr('disabled').val(idc_localization_strings.complete_checkout).removeClass('processing');
								jQuery(".payment-errors").text(json.message);
							}
						}
					});
				}
				// Non 3d secure method
				else {
					jQuery.ajax({
						url: memberdeck_ajaxurl,
						type: 'POST',
						data: {action: 'idmember_create_customer', Source: 'lemonway', Customer: customer, Token: token, Card: card, Expiry: expiry, CCode: cc_code, Fields: fields.posts, txnType: txnType, Renewable: renewable, PWYW: pwywPrice},
						success: function(res) {
							console.log(res);
							json = JSON.parse(res);
							if (json.response == 'success') {
								var paykey = json.paykey;
								var product = json.product;
								var orderID = json.order_id;
								var userID = json.user_id;
								var type = json.type;
								var custID = json.customer_id;
								jQuery(document).trigger('lemonWaySuccess', [orderID, custID, userID, product, paykey, fields, type]);
								// set a timeout for 1 sec to allow trigger time to fire
								setTimeout(function() {
									window.location = memberdeck_durl + permalink_prefix + "idc_product=" + product + "&paykey=" + paykey + queryString;
								}, 1000);
							}
							else {
								jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');
								var selectedItem = jQuery('.payment-type-selector .active').attr('id');
								if (selectedItem == 'pay-with-paypal') {
									jQuery('#id-main-submit').text('Pay with Paypal');
								}
								else {
									jQuery('#id-main-submit').text(idc_localization_strings.continue_checkout);
								}
								jQuery(".payment-errors").text(json.message);
							}
						}
					});
				}
				return false;
			}
		});
	}

	// If KYC document is present on the page
	// if (jQuery('#kyc_document').length > 0) {
	// 	jQuery('#payment-settings').attr('enctype','multipart/form-data');
	// 	// jQuery('#kyc_document_uploaded').val('yes');
	// }

	// jQuery('#kyc_document').change(function(e) {
	// 	// e.preventDefault();
	// 	console.log("jQuery('#kyc_document').val(): ", jQuery('#kyc_document').val());
	// 	jQuery('#kyc_document_uploaded').val('yes');
	// 	// jQuery('#payment-settings').submit();
	// });

	jQuery('#upload_kyc_document').click(function(e) {
		// e.preventDefault();
		// console.log('clicked');
		jQuery('#payment-settings').attr('enctype','multipart/form-data');
		jQuery('#kyc_document_uploaded').val('yes');
		jQuery('#payment-settings').submit();
	});

	function lemonWaySelected(formattedPrice, complete_checkout_text, idc_lemonway_method) {
		var lemonWaySymbol = jQuery('#finaldescStripe').data('currency-symbol');
		// console.log('lemonWaySymbol: ', lemonWaySymbol);
		setPriceText('lemonway', lemonWaySymbol, formattedPrice);
		jQuery("#id-main-submit").text(complete_checkout_text);
		jQuery("#id-main-submit").attr("name", "submitPaymentLemonWay");
		jQuery("#id-main-submit").removeAttr("disabled");
		
		jQuery(".pay_selector").removeClass('active');
		jQuery(this).addClass("active");

		// console.log('idset: ', idset, ', customerId: ', customerId);
		jQuery('.card-type-field').show();
		if (!idset || !customerId) {
			jQuery(".pw").parents('.form-row').show();
			jQuery(".cpw").parents('.form-row').show();
			if (idc_lemonway_method == "non3dsecure") {
				jQuery("#stripe-input").show();
				jQuery(".card-number, .card-cvc, card-expiry-month, card-expiry-year").addClass("required");
			} else {
				jQuery("#stripe-input").hide();
				jQuery(".card-number, .card-cvc, card-expiry-month, card-expiry-year").removeClass("required");
				// jQuery("#ppload").load(memberdeck_pluginsurl + '/templates/_lemonwayCheckoutForm.php');
			}
		} else {
			jQuery("#stripe-input").hide();
			jQuery(".card-type-field").hide();
			jQuery(".card-number, .card-cvc, card-expiry-month, card-expiry-year").removeClass("required");
		}
	}
});