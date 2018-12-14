var idcPayVars = {};
// payment form stuff
var mc = memberdeck_mc;
var epp = memberdeck_epp;
var es = memberdeck_es;
var ecb = memberdeck_ecb;
var eauthnet = memberdeck_eauthnet;
var eppadap = memberdeck_eppadap;
var onlyStripe = (es === '1' && mc !== '1' && ecb !== '1' && epp !== '1' && eauthnet !== '1' && eppadap !== '1');
var scpk;

jQuery(document).ready(function() {
	var error_class = 'error';
	//tooltip checkoutform 
	jQuery('.checkout-tooltip').hover(function(){
		if (jQuery(this).hasClass("tooltip-active")) {
			jQuery(this).removeClass('tooltip-active');
			jQuery('.tooltip-text').css('height','0');
			jQuery('.tooltip-text').css('visibility','hidden');
		//jQuery('.tooltip-text').removeClass('tooltip-text-hover'); 
			jQuery('.checkout-tooltip i').removeClass('tooltip-color');
		} else {
			jQuery(this).addClass('tooltip-active');
			jQuery('.tooltip-text').css('visibility','visible');
			jQuery('.tooltip-text').css('height','30px');
			//jQuery('.tooltip-text').addClass('tooltip-text-hover');
			jQuery('.checkout-tooltip i').addClass('tooltip-color');
		}
    });
	jQuery('.tooltip-text i.close').hover(function(){     
        jQuery('.tooltip-text').removeClass('tooltip-text-hover'); 
		jQuery('.checkout-tooltip i').removeClass('tooltip-color');     
    });

	// shortcode button stuff
	jQuery('.idc_shortcode_button').click(function() {
		var lbSource = jQuery(this).data('source');
		//console.log('jQuery(this): ', jQuery(this), ', lbSource: "', jQuery.trim(lbSource), '"', ', jQuery(lbSource): ', jQuery(lbSource));
		if (lbSource.length > 0) {
			openLBGlobal(lbSource);
		}
	});
	jQuery('.idc_button_submit').click(function(e) {
		e.preventDefault();
		var price = parseFloat(jQuery('input[name="price"]').val());
		var action = jQuery('form[name="idc_button_checkout_form"]').attr('action');
		action = action + '?' + 'idc_button_submit=1' + '&price=' + price;
		// Check that inputted price is greater than or equal to level price
		if (parseFloat(jQuery('.idc-button-default-price').data('level-price')) > price) {
			jQuery('.button-error-placeholder .payment-errors').show();
			return false;
		} else {
			jQuery('.button-error-placeholder .payment-errors').hide();
		}
		jQuery('form[name="idc_button_checkout_form"]').attr('action', action).submit();
	});
	// For restoring default price, if input price is less than deafult price
	jQuery('input[name="price"]').change(function(e) {
		var price = parseFloat(jQuery(this).val());
		var default_price = parseFloat(jQuery('.idc-button-default-price').data('level-price'));
		if (default_price > price) {
			jQuery(this).val(default_price);
		}
	});

	// dashboard stuff
	if (jQuery('.dashboardmenu').length > 0) {
		if (jQuery('.dashboardmenu .active').length <= 0) {
			jQuery('.dashboardmenu li').eq(0).addClass('active');
		}
	}

	if (jQuery('form#payment-settings input').length <= 1) {
		jQuery('input[name="creator_settings_submit"]').hide();
	}
	
	// Vars and functions used only when it's a checkout form
	if (jQuery(".checkout-wrapper").length > 0) {
		var credits = jQuery("#payment-form").data('pay-by-credits');
		var type = jQuery("#payment-form").data('type');
		var limitTerm =  jQuery("#payment-form").data('limit-term');
		var termLength = jQuery('#payment-form').data('term-length');
		if (limitTerm) {
			jQuery('#payment-form #pay-with-paypal').hide();
			epp = 0;
		}
		var logged = jQuery("#payment-form #logged-input").hasClass('yes');
		var isFree = jQuery("#payment-form").data('free');
		var renewable = jQuery('#payment-form').data('renewable');
		if (es == '1') {
			var stripeSymbol = jQuery('#stripe-input').data('symbol');
		}
		var idset = jQuery("#payment-form #stripe-input").data('idset');
		var customerId = jQuery('#stripe-input').data('customer-id');
		var curSymbol = jQuery(".currency-symbol").children('sup').text();
		if (credits === 1) {
			var creditsLabel = jQuery('#finaldescCredits').data('credits-label');
		}
		var cbCurSymbol = jQuery('#finaldescCoinbase').data('cb-symbol');
		var txnType = jQuery("#payment-form").data('txn-type');
		function scPK() {
			var scpk = jQuery("#payment-form").data('scpk');
			idcPayVars.scpk = scpk;
			return scpk;
		}
		scpk = scPK();
		
		function claimPaypal() {
			var claim_paypal = jQuery("#payment-form").data('claimedpp');
			idcPayVars.claim_paypal = claim_paypal;
			return claim_paypal;
		}
		var claim_paypal = claimPaypal();
		var regPrice = jQuery('input[name="reg-price"]').val();
		var pwywPrice = parseFloat(jQuery('input[name="pwyw-price"]').val());
		var formattedPrice = jQuery(".product-price").text();
		if (txnType == 'preauth') {
			jQuery("#payment-form #pay-with-paypal").remove();
			removeCB();
			if (idc_elw == '1' && idc_lemonway_method == '3dsecure') {
				jQuery('#payment-form #pay-with-lemonway').remove();
			}
			no_methods();
		}
		if (type == 'recurring') {
			var recurring = jQuery("#payment-form").data('recurring');
			jQuery('#payment-form #pay-with-fd').remove();
			jQuery('#payment-form #pay-with-mc').remove();
			jQuery('#payment-form #pay-with-lemonway').remove();
			if (parseFloat(pwywPrice) >= 1 && parseFloat(regPrice) < parseFloat(pwywPrice)) {
				jQuery('#pay-with-stripe').remove();
			}
			no_methods();
		}
		if (isFree == 'free') {
			if (jQuery('.checkout-payment').hasClass('active')){
				jQuery('.checkout-payment').removeClass('active');
				jQuery('.checkout-confirmation').addClass('active');
			}
			// Showing the terms and checkout button
			if (jQuery('.idc-terms-checkbox').length > 0) {
				jQuery('.idc-terms-checkbox').show();
			}
			jQuery('.main-submit-wrapper').show();
			jQuery('.confirm-screen').show();
			jQuery('.checkout-header').hide();
			jQuery("#payment-form #id-main-submit").text(idc_localization_strings.continue);
		}
		else if (jQuery('#payment-form .pay_selector:visible').length > 1) {
			jQuery("#payment-form #id-main-submit").text(idc_localization_strings.choose_payment_method);
			jQuery("#payment-form #id-main-submit").attr("disabled", "disabled");
		}
		else {
			jQuery('#payment-form .pay_selector').hide();
			jQuery('#payment-form .checkout-header').hide();
			jQuery("#id-main-submit").removeAttr("disabled");
			if (jQuery('.checkout-payment').hasClass('active')){
				jQuery('.checkout-payment').removeClass('active');
				jQuery('.checkout-confirmation').addClass('active');
			}
			// Adding a class to .pay_selector children div, to fix an issue of selector going towards left or right in some themes
			jQuery(".pay_selector").parent('div').addClass('single-payment-selector');
			// Showing the terms and checkout button as there is no other payment gateway to be selected
			if (jQuery('.idc-terms-checkbox').length > 0) {
				jQuery('.idc-terms-checkbox').show();
			}
			jQuery('.main-submit-wrapper').show();
			jQuery('.confirm-screen').show();
			if (epp == 1 && txnType !== 'preauth') {
				jQuery("#payment-form #id-main-submit").text(idc_localization_strings.pay_with_paypal);
				jQuery("#payment-form #id-main-submit").attr("name", "submitPaymentPaypal");
				if (type == 'recurring') {
					jQuery("#ppload").load(memberdeck_pluginsurl + '/templates/_ppSubForm.php');
				}
				else {
					jQuery("#ppload").load(memberdeck_pluginsurl + '/templates/_ppForm.php');
				}
				jQuery("#payment-form #finaldescPayPal").show();
				jQuery("#payment-form #finaldescCredits").hide();
				jQuery("#payment-form #finaldescOffline").hide();
			}
			else if (mc == '1' && type !== 'recurring') {
				jQuery("#payment-form #pay-with-paypal").remove();
				jQuery("#payment-form #id-main-submit").text(idc_localization_strings.complete_checkout);
				jQuery("#payment-form #id-main-submit").attr("name", "submitPaymentMC");
				jQuery("#finaldescStripe").hide();
				jQuery("#finaldescPayPal").hide();
				jQuery("#finaldescCredits").hide();
				jQuery("#finaldescOffline").show();

				// var globalCurrency = jQuery("#finaldescOffline").data('currency');
				var globalCurrencySym = jQuery("#finaldescOffline").data('currency-symbol');
				setPriceText('mc', globalCurrencySym, formattedPrice);
			}
			else if (credits === 1) {
				jQuery("#payment-form #pay-with-paypal").remove();
				jQuery("#payment-form #id-main-submit").text(idc_localization_strings.complete_checkout);
				jQuery("#payment-form #id-main-submit").attr("name", "submitPaymentCredits");
				jQuery("#payment-form #finaldescCredits").show();
				jQuery("#payment-form #finaldescCoinbase").hide();
				jQuery("#payment-form #finaldescOffline").hide();
				
				var _credits_value = jQuery("#finaldescCredits .credit-value").text();
				jQuery('.currency-symbol').children('sup').html(creditsLabel)// +'</sup>' + _credits_value);
				jQuery('.currency-symbol').children('.product-price').html(_credits_value);
			}
			else if (ecb === '1') {
				jQuery("#payment-form #id-main-submit").text(idc_localization_strings.pay_with_coinbase);
				jQuery("#payment-form #id-main-submit").attr("name", "submitPaymentCoinbase");
				jQuery("#payment-form #finaldescCoinbase").show();
				jQuery("#payment-form #finaldescCredits").hide();
				jQuery("#payment-form #finaldescOffline").hide();
				jQuery("#finaldescPayPal").hide();

				jQuery('.currency-symbol').children('sup').text(cbCurSymbol);
			}
			else if (eppadap === '1') {
				jQuery("#payment-form #id-main-submit").text(idc_localization_strings.pay_with_paypal);
				jQuery("#payment-form #id-main-submit").attr("name", "submitPaymentPPAdaptive");
				
				jQuery(".pw").parents('.form-row').hide();
				jQuery(".cpw").parents('.form-row').hide();
				// Loading the form and setting the payment key
				if (type == 'recurring' || txnType == 'preauth') {
					jQuery("#ppload").load(memberdeck_pluginsurl + '/templates/_ppAdaptiveSubForm.php');
				}
				else {
					jQuery("#ppload").load(memberdeck_pluginsurl + '/templates/_ppAdaptiveForm.php');
				}
			
				jQuery("#payment-form #finaldescCoinbase").hide();
				jQuery("#payment-form #finaldescStripe").hide();
				jQuery("#payment-form #finaldescOffline").hide();
				jQuery("#payment-form #finaldescPayPal").show();
			}
			else {
				jQuery("#payment-form #pay-with-paypal").remove();
				jQuery("#payment-form #id-main-submit").text(idc_localization_strings.complete_checkout);
				jQuery("#payment-form #finaldescStripe").show();
				jQuery("#payment-form #finaldescCoinbase").hide();
				jQuery("#payment-form #finaldescOffline").hide();
				jQuery(".card-number, .card-cvc, card-expiry-month, card-expiry-year").addClass("required");
				if (idset != '1') {
					jQuery("#payment-form #stripe-input").show();
					jQuery(".pw").parents('.form-row').show();
					jQuery(".cpw").parents('.form-row').show();
				}
				if (jQuery('#payment-form .pay_selector').attr('id') == 'pay-with-stripe') {
					jQuery("#payment-form #id-main-submit").attr("name", "submitPaymentStripe");
					jQuery('.currency-symbol').children('sup').text(stripeSymbol);
				}
				else if (jQuery('#payment-form .pay_selector').attr('id') == 'pay-with-fd') {
					jQuery("#payment-form #id-main-submit").attr("name", "submitPaymentFD");
				}
			}
			no_methods();
		}
	}
	var dgFlow = '';

	jQuery('.link-terms-conditions a').click(function(e) {
		openLBGlobal(jQuery('.idc-terms-conditions'));
		return false;
	});
	jQuery('.link-privacy-policy a').click(function(e) {
		openLBGlobal(jQuery('.idc-privacy-policy'));
		return false;
	});
	
	// Calling lightbox for social sharing box if it exists
	if (jQuery('.idc_lightbox_attach').length > 0) {
		openLBGlobal(jQuery('.idc_lightbox_attach'));
	}
	jQuery('.pay_selector').click(function(e) {
		if(jQuery('.checkout-payment').hasClass('active')){
			jQuery('.checkout-payment').removeClass('active');
			jQuery('.checkout-confirmation').addClass('active');
		}
		// trigger anytime a payment method is selected
		jQuery(document).trigger('idcPaySelect');
		// Showing the terms and checkout button
		if (jQuery('.idc-terms-checkbox').length > 0) {
			jQuery('.idc-terms-checkbox').show();
		}
		jQuery('.main-submit-wrapper').show();
		jQuery('.confirm-screen').show();
	});
	// When Stripe Button is Clicked
	jQuery("#payment-form #pay-with-stripe").click(function(e) {
		e.preventDefault();
		setPriceText('stripe', stripeSymbol, formattedPrice);
		jQuery("#id-main-submit").removeAttr("disabled");
		if (type == 'recurring') {
			jQuery("#ppload").unload(memberdeck_pluginsurl + '/templates/_ppSubForm.php');
		}
		else {
			jQuery("#ppload").unload(memberdeck_pluginsurl + '/templates/_ppForm.php');
		}
		if (!idset) {
			jQuery("#stripe-input").show();
			jQuery(".pw").parents('.form-row').show();
			jQuery(".cpw").parents('.form-row').show();
			jQuery(".card-number, .card-cvc, card-expiry-month, card-expiry-year").addClass("required");
		}
		jQuery("#id-main-submit").attr("name", "submitPaymentStripe");
		jQuery("#id-main-submit").text(idc_localization_strings.complete_checkout);
		jQuery(".pay_selector").removeClass('active');
		jQuery(this).addClass("active");

		jQuery('.finaldesc').hide();
		jQuery("#finaldescStripe").show();
	});
	// When Paypal Button is Clicked
	jQuery("#payment-form #pay-with-paypal").click(function(e) {
		e.preventDefault();
		setPriceText('paypal', curSymbol, formattedPrice);
		jQuery("#id-main-submit").text(idc_localization_strings.pay_with_paypal);
		jQuery("#id-main-submit").attr("name", "submitPaymentPaypal");
		jQuery("#id-main-submit").removeAttr("disabled");
		if (type == 'recurring') {
			jQuery("#ppload").load(memberdeck_pluginsurl + '/templates/_ppSubForm.php');
		}
		else {
			jQuery("#ppload").load(memberdeck_pluginsurl + '/templates/_ppForm.php');
		}
		
		jQuery("#stripe-input").hide();
		jQuery(".pw").parents('.form-row').hide();
		jQuery(".cpw").parents('.form-row').hide();
		jQuery(".pay_selector").removeClass('active');
		jQuery(this).addClass("active");

		jQuery('.finaldesc').hide();
		jQuery("#finaldescPayPal").show();
        jQuery(".card-number, .card-cvc, .card-expiry-month, .card-expiry-year").removeClass("required");
	});
	// When First Data Button is Clicked
	jQuery("#payment-form #pay-with-fd").click(function(e) {
		e.preventDefault();
		/*if (curSymbol !== fdSymbol) {
			jQuery('.currency-symbol').text(fdSymbol);
		}*/
		setPriceText('fd', '$', formattedPrice);
		jQuery("#id-main-submit").removeAttr("disabled");
		if (type == 'recurring') {
			jQuery("#ppload").unload(memberdeck_pluginsurl + '/templates/_ppSubForm.php');
		}
		else {
			jQuery("#ppload").unload(memberdeck_pluginsurl + '/templates/_ppForm.php');
		}
		if (!idset) {
			jQuery("#stripe-input").show();
			jQuery(".pw").parents('.form-row').show();
			jQuery(".cpw").parents('.form-row').show();
			jQuery(".card-number, .card-cvc, card-expiry-month, card-expiry-year").addClass("required");
		}
		jQuery("#id-main-submit").attr("name", "submitPaymentFD");
		jQuery("#id-main-submit").text(idc_localization_strings.complete_checkout);
		jQuery(".pay_selector").removeClass('active');
		jQuery(this).addClass("active");

		jQuery('.finaldesc').hide();
		jQuery("#finaldescStripe").show();
	});
	jQuery('#payment-form #pay-with-mc').click(function(e) {
		e.preventDefault();
		var globalCurrencySym = jQuery("#finaldescOffline").data('currency-symbol');
		setPriceText('mc', globalCurrencySym, formattedPrice);
		jQuery("#id-main-submit").removeAttr("disabled");
		jQuery("#stripe-input").hide();
		jQuery("#id-main-submit").attr("name", "submitPaymentMC");
		jQuery("#id-main-submit").text(idc_localization_strings.complete_checkout);
		jQuery(".pay_selector").removeClass('active');
		jQuery(this).addClass("active");

		jQuery('.finaldesc').hide();
		jQuery("#finaldescOffline").show();
	});
	jQuery('#payment-form #pay-with-credits').click(function(e) {
		e.preventDefault();
		setPriceText('credits', creditsLabel, formattedPrice);
		
		jQuery("#id-main-submit").removeAttr("disabled");
		jQuery("#stripe-input").hide();
		jQuery("#id-main-submit").attr("name", "submitPaymentCredits");
		jQuery("#id-main-submit").text(idc_localization_strings.complete_checkout);
		jQuery(".pay_selector").removeClass('active');
		jQuery(this).addClass("active");

		jQuery('.finaldesc').hide();
		jQuery("#finaldescCredits").show();
	});
	// Coinbase payment gateway selected
	jQuery('#payment-form #pay-with-coinbase').click(function(e) {
		e.preventDefault();
		setPriceText('cb', cbCurSymbol, formattedPrice);
		jQuery("#id-main-submit").text(idc_localization_strings.pay_with_coinbase);
		jQuery("#id-main-submit").attr("name", "submitPaymentCoinbase");
		jQuery("#id-main-submit").removeAttr("disabled");
		jQuery("#id-main-submit").text(idc_localization_strings.pay_with_coinbase);
		
		jQuery(".pay_selector").removeClass('active');
		jQuery(this).addClass("active");
		
		jQuery("#stripe-input").hide();
		jQuery(".pw").parents('.form-row').hide();
		jQuery(".cpw").parents('.form-row').hide();
		jQuery(".pay_selector").removeClass('active');
		jQuery(this).addClass("active");
		
		jQuery('.finaldesc').hide();
		jQuery("#finaldescCoinbase").show();
        jQuery(".card-number, .card-cvc, .card-expiry-month, .card-expiry-year").removeClass("required");
	});
	// Authorize.Net payment gateway selected
	jQuery('#payment-form #pay-with-authorize').click(function(e) {
		e.preventDefault();
		setPriceText('authorize', '$', formattedPrice);
		jQuery("#id-main-submit").text(idc_localization_strings.complete_checkout);
		jQuery("#id-main-submit").attr("name", "submitPaymentAuthorize");
		jQuery("#id-main-submit").removeAttr("disabled");
		
		jQuery(".pay_selector").removeClass('active');
		jQuery(this).addClass("active");
		//console.log('idset: ', idset, ', customerId: ', customerId, ', !idset: ', !idset, ', !customerId: ', !customerId);
		if (!idset || !customerId) {
			jQuery("#stripe-input").show();
			jQuery(".pw").parents('.form-row').show();
			jQuery(".cpw").parents('.form-row').show();
			jQuery(".card-number, .card-cvc, card-expiry-month, card-expiry-year").addClass("required");
		}
		jQuery('.finaldesc').hide();
		jQuery("#finaldescStripe").show();
	});
	jQuery('#payment-form #pay-with-ppadaptive').click(function(e) {
		e.preventDefault();
		setPriceText('ppadaptive', curSymbol, formattedPrice);
		jQuery("#id-main-submit").removeAttr("disabled");
		jQuery("#id-main-submit").text(idc_localization_strings.pay_with_paypal);
		jQuery("#id-main-submit").attr("name", "submitPaymentPPAdaptive");
	    jQuery("#id-main-submit").removeAttr("disabled");
	    
		jQuery("#stripe-input").hide();
		jQuery(".pw").parents('.form-row').hide();
		jQuery(".cpw").parents('.form-row').hide();
	    jQuery(".pay_selector").removeClass('active');
	    jQuery(this).addClass("active");
		// Loading the form and setting the payment key
		if (type == 'recurring' || txnType == 'preauth') {
			jQuery("#ppload").load(memberdeck_pluginsurl + '/templates/_ppAdaptiveSubForm.php');
		}
		else {
			jQuery("#ppload").load(memberdeck_pluginsurl + '/templates/_ppAdaptiveForm.php');
		}
		//jQuery("#finaldescPPAdaptive").show();
		jQuery('.finaldesc').hide();
		jQuery("#finaldescPayPal").show();
	});
	function removeCB() {
		jQuery("#payment-form #pay-with-coinbase").remove();
		jQuery('#finaldescCoinbase').remove();
	}
	function no_methods() {
		var selCount = jQuery('#payment-form .pay_selector').length;
		if (selCount < 1) {
			if (isFree !== 'free') {
				jQuery(".finaldesc").hide();
				jQuery("#stripe-input").hide();
				jQuery('#payment-form #id-main-submit').text(idc_localization_strings.no_payments_available).attr('disabled', 'disabled');
			}
		}
		else if (selCount == 1) {
			jQuery('.payment-type-selector').hide();
			var showCC = 0;
			if (es == 1) {
				jQuery("#id-main-submit").attr("name", "submitPaymentStripe");
				showCC = 1;
			}
			else if (jQuery('#payment-form .pay_selector').attr('id') == 'pay-with-fd') {
				jQuery("#payment-form #id-main-submit").attr("name", "submitPaymentFD");
				showCC = 1;
			}
			else if (eauthnet == 1) {
				jQuery("#payment-form #id-main-submit").attr("name", "submitPaymentAuthorize");
				showCC = 1;
			}
			if (!idset && showCC == 1) {
				jQuery("#stripe-input").show();
				jQuery(".pw").parents('.form-row').show();
				jQuery(".cpw").parents('.form-row').show();
			}
			if (showCC == 1) {
				jQuery("#id-main-submit").text(idc_localization_strings.complete_checkout);
				jQuery("#finaldescStripe").show();
				jQuery(".card-number, .card-cvc, card-expiry-month, card-expiry-year").addClass("required");
				jQuery("#id-main-submit").removeAttr("disabled");
			}
			else {
				//jQuery("#id-main-submit").text("No Payment Options Available");
			}
		}
	}

	function check_email() {
		var email = jQuery("#payment-form .email").val();
		console.log(email);
		jQuery.ajax({
			url: memberdeck_ajaxurl,
			type: 'POST',
			data: {action: 'idmember_check_email', Email: email},
			success: function(res) {
				//console.log(res);
				var	json = JSON.parse(res);
				//console.log(json);
				var response = json.response;
				if (!logged && response == 'exists') {
					jQuery(".payment-errors").html("<span id=\"email-error\">" + idc_localization_strings.email_already_exists + "<br>" + idc_localization_strings.please + " <a class=\"login-redirect\" href=\"" + memberdeck_durl + "\">"+ idc_localization_strings.login +"</a></span>");
					jQuery("#id-main-submit").removeAttr("disabled");
					jQuery('#email-error .login-redirect').click(function(e) {
						e.preventDefault();
						jQuery('#payment-form').hide();
						jQuery('.login-form').show();
					});
				}
				else {
					jQuery(".payment-errors").html("");
					if (isFree !== 'free') {
						//console.log('not free');
						processPayment();
					}
					else {
						//console.log('free');
						processFree();
					}
				}
			}
		});
	}
	jQuery('.reveal-login').click(function(e) {
		e.preventDefault();
		jQuery('#payment-form').hide();
		jQuery('.disclaimer').hide();
		jQuery('.login-form').show();
	});
	jQuery('.hide-login').click(function(e) {
		e.preventDefault();
		jQuery('#payment-form').show();
		jQuery('.disclaimer').show();
		jQuery('.login-form').hide();
	});
	jQuery('.reveal-account').click(function(e) {
		e.preventDefault();
		jQuery(this).hide();
		jQuery('#create_account').show();
	});
	jQuery("#id-main-submit").click(function(e) {
		jQuery(document).trigger('idcCheckoutSubmit');
		if (es == '1' && isFree !== 'free') {
			if (scpk.length > 1) {
				//memberdeck_pk = scpk;
			}
			if (jQuery('.pay_selector').length > 1) {
				if (jQuery('#pay-with-stripe').hasClass('active')) {
					Stripe.setPublishableKey(memberdeck_pk);
				}
			}	
			else {
				Stripe.setPublishableKey(memberdeck_pk);
			}
		}
		e.preventDefault();
		jQuery("#id-main-submit").attr("disabled", "disabled").addClass('processing');
		var fname = jQuery(".first-name").val();
		var lname = jQuery(".last-name").val();
		var email = jQuery("#payment-form .email").val();
		var is_terms = ((jQuery('.idc-terms-checkbox').length > 0) ? true : false);
		var terms_checked = ((jQuery('.idc-terms-checkbox').length > 0) ? jQuery('.terms-checkbox-input').is(':checked') : '');
		
		var pw = jQuery(".pw").val();
		var cpw = jQuery(".cpw").val();
		var pid = jQuery("#payment-form").data('product');
		var error = false;
		if (!logged) {
			if (jQuery('.pw').is(':visible')) {
				if (pw !== cpw) {
					jQuery(".payment-errors").text(idc_localization_strings.pass_dont_match);
					jQuery('.pw').addClass(error_class);
					jQuery('.cpw').addClass(error_class);
					jQuery("#id-main-submit").removeAttr("disabled").removeClass('processing');
					jQuery("#id-main-submit").text(idc_localization_strings.continue);
					error = true;
				}
				else if (fname.length < 1 || lname.length < 1 || pw.length < 5 || validateEmail(email) == false) {
					if (pw.length < 1) { jQuery(".pw").addClass(error_class) } else { jQuery(".pw").removeClass(error_class) }
					
					jQuery(".payment-errors").append(idc_localization_strings.registeration_fields_error_text);
					jQuery("#id-main-submit").removeAttr("disabled");
					jQuery("#id-main-submit").text(idc_localization_strings.continue);
					error = true;
				}
				else {
					jQuery('.pw').removeClass(error_class);
					jQuery('.cpw').removeClass(error_class);
				}
			}
			else {
				if (fname.length < 1 || lname.length < 1 || validateEmail(email) == false) {
					jQuery(".payment-errors").append(idc_localization_strings.complete_all_fields);
					jQuery("#id-main-submit").removeAttr("disabled");
					jQuery("#id-main-submit").text(idc_localization_strings.continue);
					error = true;
				}
			}
			if (fname.length < 1) { jQuery(".first-name").addClass(error_class) } else { jQuery(".first-name").removeClass(error_class) }
			if (lname.length < 1) { jQuery(".last-name").addClass(error_class) } else { jQuery(".last-name").removeClass(error_class) }
			if (validateEmail(email) == false) { jQuery("#payment-form .email").addClass(error_class) } else { jQuery("#payment-form .email").removeClass(error_class) }
			if (!error) {
				jQuery(".payment-errors").html("");
				if (jQuery('#stripe-input').is(':visible')) {
					error = checkCreditCard();
				}
			}
		} else {
			// User is logged in, check credit card fields
			if (jQuery('#stripe-input').is(':visible')) {
				error = checkCreditCard();
			}
		}
		
		// Check if there is no error on this stage, then check for terms
		if (!error) {
			// Check if there is terms checkbox present whether it's checked or not
			if (is_terms) {			
				if (!terms_checked) {
					var terms_message = jQuery('#idc-hdn-error-terms-privacy').val();
					jQuery(".payment-errors").text(idc_localization_strings.accept_terms + " " + terms_message);
					jQuery("#id-main-submit").removeAttr("disabled");
					jQuery("#id-main-submit").text(idc_localization_strings.continue);
					var error = true;
				} else {
					//jQuery(".payment-errors").hide();
					jQuery("#id-main-submit").removeAttr("disabled");
					jQuery("#id-main-submit").text(idc_localization_strings.continue);
				}
			}
		}
		//console.log('error: ', error);
		if (error) {
			jQuery("#id-main-submit").removeClass('processing').removeAttr('disabled');
			return false;
		}
		else {
			check_email();
		}
	});
	function processFree() {
		var fname = jQuery(".first-name").val();
		var lname = jQuery(".last-name").val();
		var email = jQuery("#payment-form .email").val();
		var pw = jQuery(".pw").val();
		var cpw = jQuery(".cpw").val();
		var pid = jQuery("#payment-form").data('product');
		var customer = ({'product_id': pid,
					    	'first_name': fname,
							'last_name': lname,
							'email': email,
							'pw': pw});
		//console.log(customer);
        jQuery.ajax({
	    	url: memberdeck_ajaxurl,
	    	type: 'POST',
	    	data: {action: 'idmember_free_product', Customer: customer},
	    	success: function(res) {
	    		console.log(res);
	    		json = JSON.parse(res);
	    		if (json.response == 'success') {
	    			var product = json.product;
	    			window.location = memberdeck_durl + permalink_prefix + "idc_product=" + product;
	    			jQuery(document).trigger('idcFreeSuccess', customer);
	    		}
	    	}
		});
	}
	function processPayment() {
		var extraFields = jQuery('#extra_fields input');
		var fields = {'posts': {}};
		jQuery.each(extraFields, function(x, y) {
			var name = jQuery(this).attr('name');
			var type = jQuery(this).attr('type');
			if (type == 'checkbox' || type == 'radio') {
				if (jQuery(this).attr('checked') == 'checked') {
					value = jQuery(this).val();
					fields.posts[x] = {};
					fields.posts[x].name = name;
					fields.posts[x].value = value;
				}
			}
			else {
				value = encodeURIComponent(jQuery(this).val());
				fields.posts[x] = {};
				fields.posts[x].name = name;
				fields.posts[x].value = value;
			}
		});
		var queryString = '';
		jQuery.each(fields.posts, function() {
			queryString = queryString + '&' + this.name + '=' + this.value;
		});
		var pwywPrice = parseFloat(jQuery('input[name="pwyw-price"]').val());
		if (jQuery("#id-main-submit").attr("name") == "submitPaymentStripe") {
			jQuery(".payment-errors").text("");
			jQuery("#id-main-submit").text(idc_localization_strings.processing + '...');
			if (!idset) {
				var fname = jQuery(".first-name").val();
				var lname = jQuery(".last-name").val();
				try {
					Stripe.createToken({
			        number: jQuery(".card-number").val(),
			        cvc: jQuery(".card-cvc").val(),
			        exp_month: jQuery(".card-expiry-month").val(),
			        exp_year: jQuery(".card-expiry-year").val(),
			        name: fname + ' ' + lname,
			        address_zip: jQuery(".zip-code").val()
				    }, stripeResponseHandler);
				}
				catch(e) {
					jQuery('#id-main-submit').removeAttr('disabled').removeClass('processing');
					jQuery('#id-main-submit').text(idc_localization_strings.continue_checkout);
					jQuery(".payment-errors").text(idc_localization_strings.strip_credentials_problem_text);
				}
			}
			else {
				//jQuery("#id-main-submit").text(idc_localization_strings.processing + '...');
 				var pid = jQuery("#payment-form").data('product');
				var fname = jQuery(".first-name").val();
				var lname = jQuery(".last-name").val();
				var email = jQuery("#payment-form .email").val();
				var pw = jQuery(".pw").val();
				var customer = ({'product_id': pid,
							    	'first_name': fname,
									'last_name': lname,
									'email': email,
									'pw': pw});
				//console.log(customer);
		        jQuery.ajax({
			    	url: memberdeck_ajaxurl,
			    	type: 'POST',
			    	data: {action: 'idmember_create_customer', Source: 'stripe', Customer: customer, Token: 'customer', Fields: fields.posts, txnType: txnType, Renewable: renewable, PWYW: pwywPrice},
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
			    			jQuery(document).trigger('stripeSuccess', [orderID, custID, userID, product, paykey, fields, type]);
			    			// Code for Custom Goal: Sale
						    //_vis_opt_goal_conversion(201);
						    //_vis_opt_goal_conversion(202);
			    			// set a timeout for 1 sec to allow trigger time to fire
			    			setTimeout(function() {
			    				window.location = memberdeck_durl + permalink_prefix + "idc_product=" + product + "&paykey=" + paykey + queryString;
			    			}, 1000);
			    		}
			    		else {
			    			jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');
			    			var selectedItem = jQuery('.payment-type-selector .active').attr('id');
			    			if (selectedItem == 'pay-with-paypal') {
			    				jQuery('#id-main-submit').text(idc_localization_strings.pay_with_paypal);
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
		else if (jQuery("#id-main-submit").attr("name") == "submitPaymentFD") {
			jQuery(".payment-errors").text("");
			jQuery("#id-main-submit").text(idc_localization_strings.processing + '...');
			var pid = jQuery("#payment-form").data('product');
			var fname = jQuery(".first-name").val();
			var lname = jQuery(".last-name").val();
			var email = jQuery("#payment-form .email").val();
			var pw = jQuery(".pw").val();
			var card = jQuery('.card-number').val();
			var exp_month = jQuery('.card-expiry-month').val();
			var exp_year = jQuery('.card-expiry-year').val().slice(-2);
			var expiry = exp_month + exp_year;
			var customer = ({'product_id': pid,
						    	'first_name': fname,
								'last_name': lname,
								'email': email,
								'pw': pw});
			if (!idset) {
				var token = 'none';
			}
			else {
				var token = 'customer';
			}
			jQuery.ajax({
		    	url: memberdeck_ajaxurl,
		    	type: 'POST',
		    	data: {action: 'idmember_create_customer', Source: 'fd', Customer: customer, Token: token, Card: card, Expiry: expiry, Fields: fields.posts, txnType: txnType, Renewable: renewable, PWYW: pwywPrice},
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
		    			jQuery(document).trigger('fdSuccess', [orderID, custID, userID, product, paykey, fields, type]);
		    			// Code for Custom Goal: Sale
					    //_vis_opt_goal_conversion(201);
					    //_vis_opt_goal_conversion(202);
		    			// set a timeout for 1 sec to allow trigger time to fire
		    			setTimeout(function() {
		    				window.location = memberdeck_durl + permalink_prefix + "idc_product=" + product + "&paykey=" + paykey + queryString;
		    			}, 1000);
		    		}
		    		else {
		    			jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');
		    			var selectedItem = jQuery('.payment-type-selector .active').attr('id');
		    			if (selectedItem == 'pay-with-paypal') {
		    				jQuery('#id-main-submit').text(idc_localization_strings.pay_with_paypal);
		    			}
		    			else {
		    				jQuery('#id-main-submit').text(idc_localization_strings.continue_checkout);
		    			}
		    			jQuery(".payment-errors").text(json.message);
		    		}
		    	}
			});
		    return false;
		}
		else if (jQuery("#id-main-submit").attr("name") == "submitPaymentMC") {
			jQuery(".payment-errors").text("");
			jQuery("#id-main-submit").text(idc_localization_strings.processing + '...');
			var pid = jQuery("#payment-form").data('product');
			var fname = jQuery(".first-name").val();
			var lname = jQuery(".last-name").val();
			var email = jQuery("#payment-form .email").val();
			var pw = jQuery(".pw").val();
			var customer = ({'product_id': pid,
						    	'first_name': fname,
								'last_name': lname,
								'email': email,
								'pw': pw});
			if (!idset) {
				var token = 'none';
			}
			else {
				var token = 'customer';
			}
			jQuery.ajax({
		    	url: memberdeck_ajaxurl,
		    	type: 'POST',
		    	data: {action: 'idmember_create_customer', Source: 'mc', Customer: customer, Token: token, Fields: fields.posts, txnType: txnType, Renewable: renewable, PWYW: pwywPrice},
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
		    			jQuery(document).trigger('fdSuccess', [orderID, custID, userID, product, paykey, fields, type]);
		    			// Code for Custom Goal: Sale
					    //_vis_opt_goal_conversion(201);
					    //_vis_opt_goal_conversion(202);
		    			// set a timeout for 1 sec to allow trigger time to fire
		    			setTimeout(function() {
		    				window.location = memberdeck_durl + permalink_prefix + "idc_product=" + product + "&paykey=" + paykey + queryString;
		    			}, 1000);
		    		}
		    		else {
		    			jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');
		    			var selectedItem = jQuery('.payment-type-selector .active').attr('id');
		    			if (selectedItem == 'pay-with-paypal') {
		    				jQuery('#id-main-submit').text(idc_localization_strings.pay_with_paypal);
		    			}
		    			else {
		    				jQuery('#id-main-submit').text(idc_localization_strings.continue_checkout);
		    			}
		    			jQuery(".payment-errors").text(json.message);
		    		}
		    	}
			});
		    return false;
		}
		else if (jQuery("#id-main-submit").attr("name") == "submitPaymentAuthorize") {
			jQuery(".payment-errors").text("");
			jQuery("#id-main-submit").text(idc_localization_strings.processing + '...');
			var pid = jQuery("#payment-form").data('product');
			var fname = jQuery(".first-name").val();
			var lname = jQuery(".last-name").val();
			var email = jQuery("#payment-form .email").val();
			var pw = jQuery(".pw").val();
			var card = jQuery('.card-number').val();
			var exp_month = jQuery('.card-expiry-month').val();
			var exp_year = jQuery('.card-expiry-year').val().slice(-2);
			var expiry = exp_month + exp_year;
			var cc_code = jQuery('.card-cvc').val();
			var customer = ({'product_id': pid,
						    	'first_name': fname,
								'last_name': lname,
								'email': email,
								'pw': pw});
			if (!idset) {
				var token = 'none';
			}
			else {
				var token = 'customer';
			}
			jQuery.ajax({
		    	url: memberdeck_ajaxurl,
		    	type: 'POST',
		    	data: {action: 'idmember_create_customer', Source: 'authorize.net', Customer: customer, Token: token, Card: card, Expiry: expiry, CCode: cc_code, Fields: fields.posts, txnType: txnType, Renewable: renewable, PWYW: pwywPrice},
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
		    			jQuery(document).trigger('authorizeSuccess', [orderID, custID, userID, product, paykey, fields, type]);
		    			// Code for Custom Goal: Sale
					    //_vis_opt_goal_conversion(201);
					    //_vis_opt_goal_conversion(202);
		    			// set a timeout for 1 sec to allow trigger time to fire
		    			setTimeout(function() {
		    				window.location = memberdeck_durl + permalink_prefix + "idc_product=" + product + "&paykey=" + paykey + queryString;
		    			}, 1000);
		    		}
		    		else {
		    			jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');
		    			var selectedItem = jQuery('.payment-type-selector .active').attr('id');
		    			if (selectedItem == 'pay-with-paypal') {
		    				jQuery('#id-main-submit').text(idc_localization_strings.pay_with_paypal);
		    			}
		    			else {
		    				jQuery('#id-main-submit').text(idc_localization_strings.continue_checkout);
		    			}
		    			jQuery(".payment-errors").text(json.message);
		    		}
		    	}
			});
		    return false;
		}
		else if (jQuery("#id-main-submit").attr("name") == "submitPaymentCredits") {
			jQuery(".payment-errors").text("");
			jQuery("#id-main-submit").text(idc_localization_strings.processing + '...');
			
			var pid = jQuery("#payment-form").data('product');
			var fname = jQuery(".first-name").val();
			var lname = jQuery(".last-name").val();
			var customer = ({'product_id': pid,
				'first_name': fname,
				'last_name': lname});
			var extraFields = jQuery('#extra_fields input');
			var fields = {'posts': {}};
			jQuery.each(extraFields, function(x, y) {
				var name = jQuery(this).attr('name');
				var type = jQuery(this).attr('type');
				if (type == 'checkbox' || type == 'radio') {
					if (jQuery(this).attr('checked') == 'checked') {
						value = jQuery(this).val();
						fields.posts[x] = {};
						fields.posts[x].name = name;
						fields.posts[x].value = value;
					}
				}
				else {
					value = encodeURIComponent(jQuery(this).val());
					fields.posts[x] = {};
					fields.posts[x].name = name;
					fields.posts[x].value = value;
				}
			});
			jQuery.each(fields.posts, function() {
				queryString = queryString + '&' + this.name + '=' + this.value;
			});
			jQuery.ajax({
				url: memberdeck_ajaxurl,
				type: 'POST',
				data: {action: 'md_use_credit', Customer: customer, Token: 'customer', Fields: fields.posts, PWYW: pwywPrice},
				success: function(res) {
					console.log(res);
					json = JSON.parse(res);
					if (json) {
						//console.log(json);
						if (json.response == 'success') {
			    			var paykey = json.paykey;
			    			var product = json.product;
			    			var orderID = json.order_id;
			    			var userID = json.user_id;
			    			var type = json.type;
			    			var custID = null;
			    			jQuery(document).trigger('creditSuccess', [orderID, custID, userID, product, paykey, null, type]);
			    			
							setTimeout(function() {
			    				window.location = memberdeck_durl + permalink_prefix + "idc_product=" + product + "&paykey=" + paykey + queryString;
			    			}, 1000);
			    		}
			    		else {
			    			jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');
			    			var selectedItem = jQuery('.payment-type-selector .active').attr('id');			    			
		    				jQuery('#id-main-submit').text(idc_localization_strings.continue_checkout);
			    			jQuery(".payment-errors").text(json.message);
			    		}
					}
				}
			});
		}
		else if (jQuery("#id-main-submit").attr("name") == "submitPaymentCoinbase") {
			jQuery(".payment-errors").text("");
			// if user is logged in, then just trigger the Coinbase button
			jQuery(document).bind('coinbase_modal_closed', function(e, val) {
				jQuery('#id-main-submit').removeAttr('disabled').text(idc_localization_strings.continue_checkout).removeClass('processing');
			});
			
			var pid = jQuery("#payment-form").data('product');
			var fname = jQuery(".first-name").val();
			var lname = jQuery(".last-name").val();
			var email = jQuery("#payment-form .email").val();
			var pw = jQuery(".pw").val();
			var customer = ({'product_id': pid,
								'first_name': fname,
								'last_name': lname,
								'email': email,
								'pw': pw});
			var extraFields = jQuery('#extra_fields input');
			jQuery.each(extraFields, function(x, y) {
				var name = jQuery(this).attr('name');
				var type = jQuery(this).attr('type');
				if (type == 'checkbox' || type == 'radio') {
					if (jQuery(this).attr('checked') == 'checked') {
						value = jQuery(this).val();
						fields.posts[x] = {};
						fields.posts[x].name = name;
						fields.posts[x].value = value;
					}
				}
				else {
					value = encodeURIComponent(jQuery(this).val());
					fields.posts[x] = {};
					fields.posts[x].name = name;
					fields.posts[x].value = value;
				}
			});
			var queryString = '';
			jQuery.each(fields.posts, function() {
				queryString = queryString + '&' + this.name + '=' + this.value;
			});
			jQuery.ajax({
				url: memberdeck_ajaxurl,
				type: 'POST',
				data: {action: 'idmember_get_level', Level: pid},
				success: function(res) {
					var json_level = JSON.parse(res);
					var recPeriod = json_level.recurring_type;
					var pwywPrice = parseFloat(jQuery('input[name="pwyw-price"]').val());
					// If level is an upgrade, use the difference price
					var price = getLevelPrice(json, pwywPrice);
					// Calling ajax to get the button code
					jQuery.ajax({
						url: memberdeck_ajaxurl,
						type: 'POST',
						data: {action: 'idmember_get_coinbase_button', product_id: pid, product_name: json_level.level_name, product_price: price, product_currency: 'USD', fname: fname, lname: lname, email: email, transaction_type: ((type == 'recurring') ? 'recurring' : ''), recurring_period: recPeriod, query_string: queryString},
						success: function(res) {
							var json_b = JSON.parse(res);
							if (json_b.response == "success") {
								jQuery('#coinbaseload').html(json_b.button_code);
								jQuery(document).on('coinbase_button_loaded', function(event, code) {
									//console.log('#coinbaseload loaded');
									jQuery(document).trigger('coinbase_show_modal', json_b.code);
																			
									jQuery(document).on('coinbase_payment_complete', function(event, code){
										//console.log("Payment completed for button " + code);
										var product = jQuery("#payment-form").data('product');
										window.location = memberdeck_durl + permalink_prefix + "idc_product=" + pid + "&paykey=" + code + queryString;
									});
									
								});
							}
							else {
								var error = json_b.message;
								// now need to re-enable button and print error
								jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');
				    			var selectedItem = jQuery('.payment-type-selector .active').attr('id');			    			
			    				jQuery('#id-main-submit').text(idc_localization_strings.continue_checkout);
				    			jQuery(".payment-errors").text(error);
							}
						}
					});
				}
			});
		
			jQuery("#id-main-submit").text(idc_localization_strings.processing + '...').attr('disabled');
		}
		// Adaptive PayPal payments
		else if (jQuery("#id-main-submit").attr('name') == "submitPaymentPPAdaptive") {
			jQuery(".payment-errors").text("");
			var pid = jQuery("#payment-form").data('product');
	        var fname = jQuery(".first-name").val();
	        var lname = jQuery(".last-name").val();
	        var email = jQuery("#payment-form .email").val();
	        var pw = jQuery(".pw").val();
	        var customer = ({'product_id': pid,
	                  'first_name': fname,
	                  'last_name': lname,
	                  'email': email,
	                  'pw': pw});
			// Calling ajax to get the button code
			jQuery.ajax({
				url: memberdeck_ajaxurl,
				type: 'POST',
				data: {action: 'idmember_get_ppadaptive_paykey', product_id: pid, Customer: customer, Type: ((type == 'recurring') ? 'recurring' : ''), PWYW: pwywPrice, txnType: txnType, Renewable: renewable, queryString: queryString},
				success: function(res) {
					console.log(res);
					var json = JSON.parse(res);
					if (json.response == "success") {
						//alert('device: ' + getDevice());
						var device = getDevice();
						console.log('device: ' + device);
						if (device == "mobile") {
							var paypal_url = memberdeck_paypal_adaptive + '?expType=mini&paykey=' + json.token;
							console.log('Loading PayPal page');
							// jQuery('.checkout-header').append('<br><code>log: Loading PayPal page</code><br/>');
							loadPayPalPage(paypal_url, json.return_address);
							// dgFlow = new PAYPAL.apps.DGFlowMini({ trigger: 'ppAdapSubmitBtn' });
						} else {
							dgFlow = new PAYPAL.apps.DGFlow({ trigger: 'ppAdapSubmitBtn' });
						}
						
						if (type == 'recurring' || txnType == 'preauth') {
							jQuery('#preapprovalkey').val(json.token);
							jQuery('#ppAdaptiveForm').attr('action', memberdeck_paypal_adaptive_preapproval);
						} else {
							jQuery('#paykey').val(json.token);
							jQuery('#ppAdaptiveForm').attr('action', memberdeck_paypal_adaptive);
						}
						if (device !== "mobile") {
							jQuery('#ppAdapSubmitBtn').trigger('click');
						}
					}
					else {
						jQuery('#id-main-submit').removeAttr('disabled').removeClass('processing').text(idc_localization_strings.pay_with_paypal);
						jQuery(".payment-errors").text(json.message);
					}
				}
			});
			
			jQuery("#id-main-submit").text(idc_localization_strings.processing + '...').attr('disabled');
		}
		else if (jQuery("#id-main-submit").attr("name") == "submitPaymentPaypal") {
			//console.log('paypal');
			jQuery("#id-main-submit").text(idc_localization_strings.processing + '...');
			var cCode = jQuery('#payment-form').data('currency-code');
			var fname = jQuery(".first-name").val();
			var lname = jQuery(".last-name").val();
			var email = jQuery("#payment-form .email").val();
			var pw = jQuery(".pw").val();
			var cpw = jQuery(".cpw").val();
			var pid = jQuery("#payment-form").data('product');
			var pwywPrice = parseFloat(jQuery('input[name="pwyw-price"]').val());
			jQuery.ajax({
		    	url: memberdeck_ajaxurl,
		    	type: 'POST',
		    	data: {action: 'idmember_get_level', Level: pid},
		    	success: function(res) {
		    		//console.log(res);
		    		json = JSON.parse(res);
		    		//console.log(json);
		    		//return false;
		    		if (json) {
		    			//console.log(json);
		    			if (idcPayVars.claim_paypal !== null && idcPayVars.claim_paypal.length > 1) {
	    					memberdeck_pp = idcPayVars.claim_paypal;
	    				}
	    				setLevelPrice(json, pwywPrice);
		    			if (type == 'recurring') {
		    				var recPeriod = json.recurring_type.charAt(0).toUpperCase();
		    				jQuery('#buyform input#pp-times').val(1);
		    				jQuery('#buyform input#pp-recurring').val(recPeriod);
		    			}
	    				jQuery('#buyform').attr('action', memberdeck_paypal);
	    				jQuery('#buyform input[name="currency_code"]').val(cCode);
	    				jQuery('#buyform input[name="item_number"]').val(json.id);
	    				jQuery('#buyform input[name="item_name"]').val(json.level_name);
			    		jQuery('#buyform input[name="return"]').val(memberdeck_returnurl + permalink_prefix + 'ppsuccess=1');
			    		jQuery('#buyform input[name="cancel_return"]').val(memberdeck_returnurl + permalink_prefix + 'ppsuccess=0');
			    		jQuery('#buyform input[name="notify_url"]').val(memberdeck_siteurl + permalink_prefix + 'memberdeck_notify=pp&email=' + email + queryString);
			    		jQuery('#buyform input[name="business"]').val(memberdeck_pp);
		    			jQuery("#buyform").submit();
		    		}
		    	}
			});
		}
		jQuery(document).trigger('idcPaymentChecksAfter', [pwywPrice, queryString, fields]);
	}
	function stripeResponseHandler(status, response) {
		var pwywPrice = parseFloat(jQuery('input[name="pwyw-price"]').val());
		var extraFields = jQuery('#extra_fields input');
		var fields = {'posts': {}};
		jQuery.each(extraFields, function(x, y) {
			var name = jQuery(this).attr('name');
			var type = jQuery(this).attr('type');
			if (type == 'checkbox' || type == 'radio') {
				if (jQuery(this).attr('checked') == 'checked') {
					value = jQuery(this).val();
					fields.posts[x] = {};
					fields.posts[x].name = name;
					fields.posts[x].value = value;
				}
			}
			else {
				value = encodeURIComponent(jQuery(this).val());
				fields.posts[x] = {};
				fields.posts[x].name = name;
				fields.posts[x].value = value;
			}
		});
		var queryString = '';
		jQuery.each(fields.posts, function() {
			queryString = queryString + '&' + this.name + '=' + encodeURIComponent(this.value);
		});
	    if (response.error) {
	        jQuery(".payment-errors").text(response.error.message);
	        jQuery(".submit-button").removeAttr("disabled").removeClass('processing');
	        jQuery('#id-main-submit').text(idc_localization_strings.continue_checkout);
	    } else {
	    	jQuery("#id-main-submit").text(idc_localization_strings.processing + '...');
	        var formy = jQuery("#payment-form");
	        var token = response["id"];
	        //console.log(token);
	        formy.append('<input type="hidden" name="stripeToken" value="' + token + '"/>');
	        var pid = jQuery("#payment-form").data('product');
			var fname = jQuery(".first-name").val();
			var lname = jQuery(".last-name").val();
			var email = jQuery("#payment-form .email").val();
			var pw = jQuery(".pw").val();
			var customer = ({'product_id': pid,
						    	'first_name': fname,
								'last_name': lname,
								'email': email,
								'pw': pw});
			//console.log(customer);
	        jQuery.ajax({
		    	url: memberdeck_ajaxurl,
		    	type: 'POST',
		    	data: {action: 'idmember_create_customer', Source: 'stripe', Customer: customer, Token: token, Fields: fields.posts, txnType: txnType, Renewable: renewable, PWYW: pwywPrice},
		    	success: function(res) {
		    		console.log(res);
		    		json = JSON.parse(res);
		    		if (json.response == 'success') {
		    			var paykey = json.paykey;
		    			var product = json.product;
		    			var orderID = json.order_id;
			    		var userID = json.user_id;
			    		var type = json.type;
			    		jQuery(document).trigger('stripeSuccess', [orderID, userID, product, paykey, fields, type]);
		    			// Code for Custom Goal: Sale
					    //_vis_opt_goal_conversion(201);
					    //_vis_opt_goal_conversion(202);
		    			// set a timeout for 1 sec to allow trigger time to fire
		    			setTimeout(function() {
		    				window.location = memberdeck_durl + permalink_prefix + "idc_product=" + product + "&paykey=" + paykey + queryString;
		    			}, 1000);
		    		}
		    		else {
		    			jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');
		    			var selectedItem = jQuery('.payment-type-selector .active').attr('id');
		    			if (selectedItem == 'pay-with-paypal') {
		    				jQuery('#id-main-submit').text(idc_localization_strings.pay_with_paypal);
		    			}
		    			else {
		    				jQuery('#id-main-submit').text(idc_localization_strings.continue_checkout);
		    			}
		    			jQuery(".payment-errors").text(json.message);
		    		}
		    	}
			});
	        //formy.get(0).submit();
	    }
	}
	jQuery("form[name='reg-form']").submit(function(e) {
		e.preventDefault();
		jQuery(".payment-errors").text("");
		jQuery("#id-reg-submit").attr("disabled", "disabled");
		var fname = jQuery(".first-name").val();
		var lname = jQuery(".last-name").val();
		var email = jQuery("#payment-form .email").val();
		var pw = jQuery(".pw").val();
		var cpw = jQuery(".cpw").val();
		var regkey = jQuery("form[name='reg-form']").data('regkey');
		//console.log(regkey);
		var update = true;
		if (regkey == undefined || regkey == '') {
			//console.log(uid);
			//jQuery(".payment-errors").text("There was an error processing your registration. Please contact site administrator for assistance");
			update = false;
		}

		if (pw !== cpw) {
			jQuery(".payment-errors").text(idc_localization_strings.passwords_mismatch_text);
			jQuery("#id-reg-submit").removeAttr("disabled").removeClass('processing');
			var error = true;
		}
		
		if (fname.length < 1 || lname.length < 1 || validateEmail(email) == false || pw.length < 5) {
			jQuery(".payment-errors").append(idc_localization_strings.registeration_fields_error_text);
			jQuery("#id-reg-submit").removeAttr("disabled").removeClass('processing');
			var error = true;
		}
		//console.log('update: ' + update);
		if (error == true) {
			//console.log('error');
			return false;
		}

		else if (update == true) {
			var user = ({'regkey': regkey,
				'first_name': fname,
				'last_name': lname,
				'email': email,
				'pw': pw});
			jQuery.ajax({
				url: memberdeck_ajaxurl,
				type: 'POST',
				data: {action: 'idmember_update_user', User: user},
				success: function(res) {
					//console.log(res);
					json = JSON.parse(res);
					if (json.response == 'success') {
						window.location = memberdeck_durl + permalink_prefix + 'account_updated=1';
					}
					else {
						//console.log(json.message);
						if (json.message) {
							jQuery('.payment-errors').text(json.message);
						}
						else {
							jQuery(".payment-errors").text(idc_localization_strings.error_in_processing_registration_text);
						}
					}
				}
			});
		}
		else {
			var user = ({'first_name': fname,
				'last_name': lname,
				'email': email,
				'pw': pw});
			// Getting extra fields if any
			var extraFields = jQuery('[id^="registration-form-extra-fields"] input');
			var fields = {'posts': {}};
			jQuery.each(extraFields, function(x, y) {
				var name = jQuery(this).attr('name');
				var type = jQuery(this).attr('type');
				if (type == 'checkbox' || type == 'radio') {
					if (jQuery(this).attr('checked') == 'checked') {
						value = jQuery(this).val();
						fields.posts[x] = {};
						fields.posts[x].name = name;
						fields.posts[x].value = value;
					}
				}
				else {
					value = encodeURIComponent(jQuery(this).val());
					fields.posts[x] = {};
					fields.posts[x].name = name;
					fields.posts[x].value = value;
				}
			});
			
			jQuery.ajax({
				url: memberdeck_ajaxurl,
				type: 'POST',
				data: {action: 'memberdeck_insert_user', User: user, Fields: fields.posts},
				success: function(res) {
					console.log(res);
					json = JSON.parse(res);
					if (json.response == 'success') {
						window.location = memberdeck_durl + permalink_prefix + 'account_created=1';
					}
					else {
						//console.log(json.message);
						jQuery("#id-reg-submit").removeAttr("disabled");
						if (json.message) {
							jQuery('.payment-errors').text(json.message);
						}
						else {
							jQuery(".payment-errors").text(idc_localization_strings.error_in_processing_registration_text);
						}
					}
				}
			})
		}
	});
	function validateEmail(email) {
	    var validate = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	    return validate.test(email);
		// "to avoid syntax color changing for now. #RemoveIt
	}
	function checkCreditCard() {
		//console.log('checkCreditCard() called');
		var error = false;
		// if Credit card field exists
		if (jQuery(".card-number").length > 0) {
			//console.log('credit card exists');
			var card_number = jQuery(".card-number");
			var card_cvc = jQuery(".card-cvc");
			var card_expiry_month = jQuery(".card-expiry-month");
			var card_expiry_year = jQuery(".card-expiry-year");
			var zip_code = jQuery(".zip-code");
			
			// Credit card number field
			if (jQuery(card_number).val().length < 10) {
				jQuery(card_number).addClass(error_class);
				jQuery(card_number).siblings('.error-info').show();
				error = true;
			} else {
				//console.log('.card-number: ', jQuery(card_number).val());
				jQuery(card_number).removeClass(error_class);
				jQuery(card_number).siblings('.error-info').hide();
			}
			// Card CVC field
			if (jQuery(card_cvc).val().length < 1) {
				//console.log('cvc is less than 1');
				jQuery(card_cvc).addClass(error_class);
				jQuery(card_cvc).siblings('.error-info').show();
				error = true;
			} else {
				//console.log('.card_cvc: ', jQuery(card_cvc).val());
				jQuery(card_cvc).removeClass(error_class);
				jQuery(card_cvc).siblings('.error-info').hide();
			}
			// Card Expiry date Month field
			if (jQuery(card_expiry_month).val().length < 1) {
				jQuery(card_expiry_month).addClass(error_class);
				error = true;
			} else if (jQuery(card_expiry_month).val().length > 2) {
				jQuery(card_expiry_month).addClass(error_class);
				error = true;
			} else {
				//console.log('card_expiry_month: ', jQuery(card_expiry_month).val());
				jQuery(card_expiry_month).removeClass(error_class);
			}
			// Card Expiry date Year field
			if (jQuery(card_expiry_year).val().length < 1) {
				jQuery(card_expiry_year).addClass(error_class);
				error = true;
			} else if (jQuery(card_expiry_year).val().length > 4) {
				jQuery(card_expiry_year).addClass(error_class);
				error = true;
			} else {
				//console.log('card_expiry_year: ', jQuery(card_expiry_year).val());
				jQuery(card_expiry_year).removeClass(error_class);
			}
			// Zip code check, if exists
			if (zip_code.length > 0) {
				if (jQuery(zip_code).val().length < 1) {
					jQuery(zip_code).addClass(error_class);
					jQuery(zip_code).siblings('.error-info').show();
					error = true;
				} else {
					jQuery(zip_code).removeClass(error_class);
					jQuery(zip_code).siblings('.error-info').hide();
				}
			}	
		}
		return error;
	}

	function setLevelPrice(json, pwywPrice) {
		var price = getLevelPrice(json, pwywPrice);
		jQuery('#buyform input#pp-price').val(price);
		return;
	}

	function getLevelPrice(json, pwywPrice) {
		if (jQuery('[name="upgrade-level-price"]').length > 0) {
			var level_price = jQuery('[name="upgrade-level-price"]').val();
		}
		else {
			if (renewable) {
				var level_price = json.renewal_price;
			} else {
				var level_price = json.level_price;
				level_price = level_price || 0;
			}
		}
		if (parseFloat(pwywPrice) > 0 && parseFloat(pwywPrice) > parseFloat(level_price)) {
			var price = pwywPrice;
		}
		else {
			var price = level_price;
		}
		return price;
	}

	/* To return which device it is */
	function getDevice(detail_device) {
		var user_agent = navigator.userAgent.toLowerCase();
		var device = '', device_os = '';
		// if it's iOS device
		if (user_agent.match(/iPhone|iPad|iPod/i)) {
			device_os = 'ios';
			device = 'mobile';
		}
		else if (user_agent.match(/Android/i)) {
			device_os = 'android';
			device = 'mobile';
		}
		else if (user_agent.match(/BlackBerry/i)) {
			device_os = 'blackberry';
			device = 'mobile';
		}
		else if (user_agent.match(/Opera Mini/i)) {
			device_os = 'unknown';
			device = 'mobile';
		}
		else if (user_agent.match(/IEMobile/i)) {
			device_os = 'windows';
			device = 'mobile';
		}
		else {
			device_os = 'desktop';
			device = 'desktop';
		}
		
		// Now return device
		if (detail_device !== undefined && detail_device == true) {
			return device_os;
		} else {
			return device;
		}
	}
	jQuery('#idc-downloads .inactive').click(function(e) {
		e.preventDefault();
		jQuery('.oneclick').click(function() {
			var ttInfoLink = jQuery(this).attr('href');
			window.location.href = ttInfoLink;
		});
		// If tooltip is open, don't close it by clicking anywhere on .inactive except the .tt-close button
		if (jQuery('.buy-tooltip').data('closing') == "open") {
			return false;
		}
		resetTT();
		// Check that tooltip is just closed, then prevent from going further
		if (jQuery('.buy-tooltip').data('closing') == "processing") {
			jQuery('.buy-tooltip').data('closing', 'closed');
			return false;
		}
		var levelID = jQuery(this).data('levelid');
		var pid = jQuery(this).data('pid');
		var infoLink = jQuery(this).children('.inactive-item').attr('href');
		var tooltipWrapper = jQuery(this).children('.tooltip-wrapper');
		if (levelID > 0) {
			var offset = jQuery(this).offset();
			//console.log(offset);
			var top = offset.top;
			//console.log('top ' + top);
			var left = offset.left;
			//console.log('left ' + left);
			var height = jQuery(this).height();
			//console.log('height: ' + height);
			var width = jQuery(this).width();
			//console.log('width: ' + width);
			var ttHeight = jQuery('.buy-tooltip').height();
			//console.log('ttheight: ' + ttHeight);
			//console.log(top + (height / 2) - (ttHeight));
			var bwidth = jQuery('.components button').width();
			var bheight = jQuery('.components button').height();
			var bPaddingTop = jQuery('.components button').css('padding-top').replace('px', '');
			var ttWidth = jQuery('.buy-tooltip').width();
			var ttPaddingTop = jQuery('.buy-tooltip').css('padding-top').replace('px', '');
			var ttPaddingLeft = jQuery('.buy-tooltip').css('padding-left').replace('px', '');
			ttTotalTop = ttPaddingTop * 2;
			
			var show_tt = true;
			jQuery.ajax({
				url: memberdeck_ajaxurl,
				type: 'POST',
				data: {action: 'md_level_data', level_id: levelID, get_instant_checkout: 'true'},
				success: function(res) {
					//console.log(res);
					json = JSON.parse(res);
					if (json) {
						var instant_checkout = json.instant_checkout;
						json = json.level;
						//console.log(json);
						jQuery('.buy-tooltip').data('levelid', levelID);
						jQuery('.buy-tooltip').data('pid', pid);
						var tt = jQuery('.buy-tooltip');
						jQuery(tt).find('.tt-product-name').text(json.level_name);
						jQuery(tt).find('.tt-price').text(json.level_price);
						jQuery(tt).find('.tt-credit-value').text(json.credit_value);
						// Removing the option to pay by credits if it's less than 0, else adding that option if removed
						if (json.credit_value <= 0) {
							jQuery('[name="occ_method"]').children('option[value="credit"]').remove();
						}
						else {
							// Add only if user have credits
							var user_credits = parseFloat(jQuery('.credits-avail').data('credits-available'));
							if (user_credits >= json.credit_value) {
								if (jQuery('[name="occ_method"]').children('option[value="credit"]').length <= 0) {
									jQuery('[name="occ_method"]').append(jQuery("<option/>", {
										value: 'credit',
										text: idc_localization_strings.pay_with_credits
									}));
								}
							}
						}
						// If instant checkout is not enabled and purchase with credits not available either, redirect to infoLink
						if (instant_checkout == false) {
							//console.log('instant checkout not available');
							if (json.credit_value <= 0) {
								show_tt = false;
								window.location = infoLink;
							}
						}
						// If only one default option is left, then rename it to No Payment Options, else make first option to Select Payment Options
						if (jQuery('[name="occ_method"]').children(':enabled').length == 1) {
							jQuery('[name="occ_method"]').children('option[value=""]').html(idc_localization_strings.no_payment_options);
							// instant checkout not available and no credits so redirecting...
							show_tt = false;
							window.location = infoLink;
						}
						else if (jQuery('[name="occ_method"]').children(':enabled').length > 1) {
							// Renaming value="" options to Select Payment Options if it was rename earlier
							jQuery('[name="occ_method"]').children('option[value=""]').html(idc_localization_strings.select_payment_option);
						}
						
						if (json.credit_value > 1) {
							var credit_text_el = jQuery(tt).find('.credit-text');
							credit_text_el.text(credit_text_el.data('credit-label-p'));
						}
						else {
							var credit_text_el = jQuery(tt).find('.credit-text');
							credit_text_el.text(credit_text_el.data('credit-label-s'));
						}
						jQuery('.tt-more').attr('href', infoLink);
						if (show_tt) {
							jQuery(tt).show().data('closing', 'open');
							jQuery(tooltipWrapper).append(jQuery('.buy-tooltip'));
							jQuery(window).trigger('tt_open', [tooltipWrapper, json]);
						}
					}
				}
			})
		}
		else {
			window.location.href = infoLink;
		}
	});
	jQuery('.tt-close').click(function(e) {
		e.preventDefault();
		//if (!jQuery('.buy-tooltip').is(':hover') && jQuery('.buy-tooltip').is(':visible')) {
			//console.log('leave');
			jQuery('.buy-tooltip').data('closing', 'processing');
			resetTT();
			//jQuery('.inner-tooltip').replaceAll('.buy-tooltip');

		//}
	});
	function resetTT() {
		jQuery('.buy-tooltip').data('levelid', null);
		jQuery('.buy-tooltip').data('pid', null);
		var tt = jQuery('.buy-tooltip');
		//jQuery('.tooltip-wrapper').html('<div class="inner-tooltip"></div>');
		jQuery('.buy-tooltip').hide();
		jQuery(tt).find('.tt-product-name').text('');
		jQuery(tt).find('.tt-price').text('');
		jQuery(tt).find('.tt-credit-value').text('');
		jQuery(tt).find('.tt-more').attr('href', '');
	}
	jQuery('select[name="occ_method"]').change(function() {
		if (jQuery('select[name="occ_method"]').val().length > 0) {
			jQuery('.md_occ').removeAttr('disabled');
		}
		else {
			jQuery('.md_occ').attr('disabled', 'disabled');
		}
	});
	jQuery('.md_occ').click(function(e) {
		e.preventDefault();
		jQuery(this).attr('disabled', 'disabled').addClass('processing');
		jQuery(this).text(idc_localization_strings.processing);
		var payMethod = jQuery('select[name="occ_method"]').val();
		//console.log(payMethod);
		var levelid = jQuery('.buy-tooltip').data('levelid');
		var pid = jQuery('.buy-tooltip').data('pid');
		var fname = jQuery('.md-firstname').text();
		var lname = jQuery('.md-lastname').text();
		var customer = ({'product_id': levelid,
	    	'first_name': fname,
			'last_name': lname});
		var fields = [{'name': 'project_id', 'value': pid}, {'name': 'project_level', 'value': 0}];
		if (payMethod == 'cc') {
			jQuery.ajax({
		    	url: memberdeck_ajaxurl,
		    	type: 'POST',
		    	data: {action: 'idmember_create_customer', Source: null, Customer: customer, Token: 'customer', Fields: fields, txnType: null},
		    	success: function(res) {
		    		//console.log(res);
		    		json = JSON.parse(res);
		    		if (json.response == 'success') {
		    			var paykey = json.paykey;
		    			var product = json.product;
		    			var orderID = json.order_id;
		    			var userID = json.user_id;
		    			var type = json.type;
		    			var custID = json.customer_id;
		    			jQuery(document).trigger('stripeSuccess', [orderID, custID, userID, product, paykey, null, type]);
		    			//location.reload();
						window.location = "?idc_product=" + product + "&paykey=" + paykey;
		    		}
		    		else {
		    			jQuery('.md_occ').removeAttr('disabled').removeClass('processing');
		    			jQuery('.md_occ').text('Confirm');
		    		}
		    	}
			});
		}
		else if (payMethod == 'credit') {
			jQuery.ajax({
				url: memberdeck_ajaxurl,
				type: 'POST',
				data: {action: 'md_use_credit', Customer: customer, Token: 'customer', PWYW: pwywPrice},
				success: function(res) {
					//console.log(res);
					json = JSON.parse(res);
					if (json) {
						//console.log(json);
						if (json.response == 'success') {
			    			var paykey = json.paykey;
			    			var product = json.product;
			    			var orderID = json.order_id;
			    			var userID = json.user_id;
			    			var type = json.type;
			    			var custID = null;
			    			jQuery(document).trigger('creditSuccess', [orderID, custID, userID, product, paykey, null, type]);
			    			//location.reload();
							window.location = "?idc_product=" + product + "&paykey=" + paykey;
			    		}
			    		else {
			    			jQuery('.md_occ').removeAttr('disabled').removeClass('processing');
			    		}
					}
				}
			});
		}
		else {
			jQuery('.md_occ').removeAttr('disabled').removeClass('processing');
		}
	});

	/* Check for PP Adaptive Completion */
	if (jQuery('div#idc_ppadap_return').length > 0) {
		if (window != top) {
			top.location.replace(document.location);
		}
	}

	/* Edit Profile js */

	jQuery('select[name="sub_list"]').change(function() {
		var planID = jQuery(this).children('option:selected').val();
		if (planID !== '0') {
			var plan = jQuery(this).children('option:selected').text();
			//console.log(planID);
			jQuery('button[name="cancel_sub"]').removeAttr('disabled').show();
		}
		else {
			jQuery('button[name="cancel_sub"]').attr('disabled', 'disabled').hide();
		}
	});
	jQuery('button[name="cancel_sub"]').click(function(e) {
		e.preventDefault();
		jQuery('.sub_response').text('').removeClass().addClass('sub_response');
		var planID = jQuery('select[name="sub_list"]').children('option:selected').val();
		var plan = jQuery('select[name="sub_list"]').children('option:selected').text();
		var userID = jQuery('select[name="sub_list"]').data('userid');
		
		var selectedOptionValue = jQuery('select[name="sub_list"]').val();
		var paymentGateway = jQuery('select[name="sub_list"] option[value="'+ selectedOptionValue +'"]').data('gateway');
		jQuery.ajax({
			url: memberdeck_ajaxurl,
			type: 'POST',
			data: {action: 'idc_cancel_sub', plan_id: planID, plan: plan, user_id: userID, payment_gateway: paymentGateway},
			success: function(res) {
				//console.log(res);
				if (res) {
					var json = JSON.parse(res);
					if (json.status == 'success') {
						jQuery('select[name="sub_list"] option:selected').remove();
						if (jQuery('select[name="sub_list"] option').size()  == 1) {
							jQuery('button[name="cancel_sub"]').attr('disabled', 'disabled').hide();
						}
					}
					else {

					}
					jQuery('.sub_response').text(json.message).addClass(json.status);
				}
			}
		});
	});

	/* Bridge js */

	// First, let's apply MemberDeck links to to standard IgnitionDeck widgets
	jQuery.ajax({
		url: memberdeck_ajaxurl,
		type: 'POST',
		data: {action: 'mdid_project_list'},
		success: function(res) {
			//console.log(res);
			// would trigger 0 if !$crowdfunding
			if (res !== '0') {
				json = JSON.parse(res);
				//console.log(json);
				jQuery.each(json, function(k, v) {
					//console.log('k: ' + k + ', v: ' + v);
					jQuery.each(jQuery('.id-full, #ign-product-levels, .widget_level_container'), function() {
						var widget = jQuery(this);
						var projectID = jQuery(this).data('projectid');
						if (v && projectID == v.id) {
							// Let's transform the links
							var fhDecks = jQuery(this).find('.level-binding');
							jQuery.each(fhDecks, function(k, v) {
								var href = jQuery(this).attr('href');
								if (href && href.indexOf('mdid') == -1) {
									var withMD = href.replace('prodid', 'mdid_checkout');
									jQuery(this).attr('href', withMD);
								}
							});
							var deckSource = jQuery(this).attr('id');
							if (deckSource && deckSource.indexOf('ign-product-levels') !== -1) {
								// 500
								/*jQuery('.ign-supportnow a').click(function(e) {
									e.preventDefault();
									jQuery('html, body').animate({
										scrollTop: jQuery(widget).offset().top
									}, 1000, function() {
										
									});
									jQuery(window).bind('mousewheel', function() {
										jQuery('html, body').stop();
									});
								});*/
							}
							else {
								/*jQuery(this).find('.btn-container a').click(function(e) {
									e.preventDefault();
									jQuery('html, body').animate({
										scrollTop: jQuery(widget).offset().top
									}, 1000, function() {
										
									});
									jQuery(window).bind('mousewheel', function() {
										jQuery('html, body').stop();
									});
								});*/
							}
							/*if (jQuery(this).find('.level-binding').length == 0) {
								//console.log(jQuery(this).find('.level-binding').length);
								jQuery.each(jQuery(this).find('.level-group'), function(k) {
									//console.log(this);
									var level = k + 1;
									jQuery(this).wrap('<a class="level-binding" href="?mdid_checkout=' + v + '&level=' + level + '"/>');
								});
							}*/
						}
					});	
				});
			}
		}
	});

	/* Payment Settings js */

	// hide payment settings butotn if the form is empty
	if (jQuery('.payment-settings').length > 0) {
		if (jQuery('.payment-settings input').length <= 1) {
			jQuery('.payment-settings .submit').hide();
		}
	}

	/* MDID File Upload */
	jQuery('input[name="ide_fes_file_upload_submit"]').click(function(e) {
		//e.preventDefault();
		jQuery('.required').removeClass('error');
		var error = false;
		jQuery.each(jQuery('form[name="ide_fes_file_upload_form"] input'), function() {
			if (jQuery(this).val().length <= 0) {
				if (jQuery(this).attr('type') != 'submit') {
					jQuery('.required').addClass('error');
					console.log(this);
					error = true;
				}
			}
		});
		if (error) {
			return false;
		}
	});

	/* MDID Backer List */
	if (jQuery('.content_tabs').length > 0) {
		var backers = jQuery(this).find('.ign_backer_list').data('count');
		if (backers == undefined || backers == 0) {
			jQuery('#backers_tab').hide();
		}
	}
	jQuery('.backer_list_more a').click(function(e) {
		e.preventDefault;
		var first = jQuery(this).data('first');
		jQuery(this).data('first', parseInt(first) + 20);
		var last = jQuery(this).data('last');
		jQuery(this).data('last', parseInt(last) + 20);
		var total = jQuery(this).data('total');
		if (total <= last + 20) {
			jQuery(this).hide();
		}
		var project = jQuery(this).data('project');
		var vars = {First: first, Last: last, Project: project};
		jQuery.ajax({
			url: memberdeck_ajaxurl,
			type: 'POST',
			data: {action: 'mdid_show_more_backers', Vars: vars},
			success: function(res) {
				//console.log(res);
				if (res) {
					var json = JSON.parse(res);
					jQuery('.ign_backer_list li').last().after(json);
					jQuery('.ign_backer_list li.new_backer_item').fadeIn('slow').removeClass('new_backer_item');
					jQuery(document).trigger('backer_list_more');
				}
			}
		});
	});

	/* Login form validations */
	if (jQuery('.md-requiredlogin').length > 0) {
		console.log('its here. md-requiredlogin');
		jQuery('.md-requiredlogin input[name="wp-submit"]').click(function(e) {
			var error = false;
			var blank_username = false;
			var blank_password = false;
			if (jQuery('.md-requiredlogin input[name="log"]').val() === "") {
				error = true;
				blank_username = true;
			}
			if (jQuery('.md-requiredlogin input[name="pwd"]').val() === "") {
				error = true;
				blank_password = true;
			}
			// there is an error, output it
			if (error && (blank_username || blank_password)) {
				jQuery('.md-requiredlogin .error.blank-field').removeClass('hide');
				return false;
			}
			else if (error) {
				return false;
			}
			return true;
		});
	}
	
	/* memberdeck edge on dashboard for tooltip  
	
		var wrapperW = jQuery('.memberdeck').outerWidth(true);
		var boxW = jQuery('.tooltip-wrapper').width();
		var boxPosX = jQuery('.tooltip-wrapper').position().left;
		var touched = wrapperW - (boxW + boxPosX);
		console.log('wrapperW',wrapperW);
		console.log('boxW',boxW);
		console.log('boxPosX',boxPosX);
		console.log('touched',touched);
		if( touched <= 0 ){
		   jQuery('.tooltip-wrapper').css('left', '11px'); 
		   jQuery('.memberdeck .buy-tooltip').addClass('buy-tooltip-hidden');
		}
 	/* section ends */
 
	 /* Validation for Edit Profile screen */
	jQuery('#edit-profile-submit').click(function(e) {
		var error = false;
		var email = jQuery('.email').val();
		if (validateEmail(email) == false) {
			error = true;
			jQuery('.email').addClass('error');
		} else {
			jQuery('.email').removeClass('error');
		}
		
		// if there are errors, scroll to the first error
		if (error) {
			var firstErrorElement = jQuery('.error').get(0);
			var firstElementOffset = jQuery(firstErrorElement).offset().top - 55;
			jQuery("html, body").animate({ scrollTop: firstElementOffset }, 500);
		}
		
		return !error;
	});
	
	
	/* PayPal Adaptive function for making payments using mobile devices */
	function loadPayPalPage(paypalURL, returnURL) {
		var ua = navigator.userAgent;
		var pollingInterval = 0;
		var win;
		// mobile device
		if (true /*ua.match(/iPhone|iPod|Android|Blackberry.*WebKit/i)*/) {
			//VERY IMPORTANT - You must use '_blank' and NOT name the window if you want it to work with chrome ios on iphone
			//See this bug report from google explaining the issue: https://code.google.com/p/chromium/issues/detail?id=136610
			// jQuery('.checkout-header').append('<code>log: Opening window</code><br/>');
			win = window.open(paypalURL, '_blank');
			// chrome.windows.create({url: paypalURL, type: 'popup'});
			// jQuery('.checkout-header').append('<code>log: Win: '+ win +'</code><br/>');
			// jQuery('.checkout-header').append('<code>log: Well, its done, window opened</code><br/>');
			pollingInterval = setInterval(function() {
				if (win && win.closed) {
					// jQuery('.checkout-header').append('<code>log: Win is present. win.closed: '+ win.closed +'</code><br/>');
					clearInterval(pollingInterval);
					returnFromPayPal(returnURL);
				}
			}, 1000);
		} else {
			//Desktop device
			var width = 400,
				height = 550,
				left,
				top;
			if (window.outerWidth) {
				left = Math.round((window.outerWidth - width) / 2) + window.screenX;
				top = Math.round((window.outerHeight - height) / 2) + window.screenY;
			} else if (window.screen.width) {
				left = Math.round((window.screen.width - width) / 2);
				top = Math.round((window.screen.height - height) / 2);
			}
			//VERY IMPORTANT - You must use '_blank' and NOT name the window if you want it to work with chrome ios on iphone
			//See this bug report from google explaining the issue: https://code.google.com/p/chromium/issues/detail?id=136610
			win = window.open(paypalURL, '_blank', 'top=' + top + ', left=' + left +
				', width=' + width + ', height=' + height +
				', location=0, status=0, toolbar=0, menubar=0, resizable=0, scrollbars=1');
			pollingInterval = setInterval(function() {
				if (win && win.closed) {
					clearInterval(pollingInterval);
					returnFromPayPal(returnURL);
				}
			}, 1000);
		}
	}
	var returnFromPayPal = function(returnURL) {
		location.replace(returnURL);
		// Here you would need to pass on the payKey to your server side handle (use session variable) to call the PaymentDetails API to make sure Payment has been successful
		// based on the payment status- redirect to your success or cancel/failed page
	}
});

function setPriceText(gateway, symbol, formattedPrice) {
	if (gateway == "credits") {
		var _credits_value = jQuery("#finaldescCredits .credit-value").text();
		jQuery('.currency-symbol').children('sup').text(symbol);
		jQuery('.currency-symbol .product-price').text(_credits_value);
	} else {
		if (jQuery('.currency-symbol').children('sup').text() !== symbol) {
			jQuery('.currency-symbol').children('sup').text(symbol);
		}
		jQuery('.currency-symbol .product-price').text(formattedPrice);
	}
}