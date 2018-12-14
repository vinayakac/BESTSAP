<?php

// Declare MD Global Variables

/*
1. Crowdfunding Enabled?
*/

$crowdfunding = false;
if (class_exists('IDF') && class_exists('ID_Project')) {
	$platform = idf_platform();
	if ($platform == 'idc') {
		$crowdfunding = true;
	}
}

/*
2. Default Timezone
*/

$tz = get_option('timezone_string');

/*
3. S3
*/
$s3 = 0;
$general = get_option('md_receipt_settings');
if (!empty($general)) {
	if (!is_array($general)) {
		$settings = unserialize($general);
	} else {
		$settings = $general;
	}
	if (is_array($settings)) {
		if (isset($settings['s3'])) {
			$s3 = $settings['s3'];
		}
		else {
			$s3 = 0;
		}
	}
}
/**
 4. Global Currency display options
 */
$global_currency = get_option('idc_global_currency');

/**
5. Global Permalink Structure
*/
$permalink_structure = get_option('permalink_structure');

/**
6. Stripe API Version
*/
global $stripe_api_version;
$stripe_api_version = "2014-05-19";

/**
7. Combined Purchases
*/
global $combined_purchases;
$combined_purchases = true;
?>