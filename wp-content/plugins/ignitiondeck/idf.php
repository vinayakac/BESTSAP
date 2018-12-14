<?php

//error_reporting(E_ALL);
//@ini_set('display_errors', 1);

/*
Plugin Name: IgnitionDeck Framework
URI: http://IgnitionDeck.com
Description: A crowdfunding and ecommerce for WordPress that helps you crowdfund, pre-order, and sell goods online.
Version: 1.2.8
Author: IgnitionDeck
Author URI: http://IgnitionDeck.com
License: GPL2
*/

define( 'IDF_PATH', plugin_dir_path(__FILE__) );

include_once 'idf-globals.php';
global $active_plugins;
include_once 'idf-update.php';
include_once 'classes/class-idf.php';
include_once 'idf-functions.php';
include_once 'idf-admin.php';
include_once 'idf-roles.php';
include_once 'idf-wp.php';
include_once 'idf-actions.php';
if (idf_has_idc()) {
	include_once 'idf-idc.php';
}
if (idf_has_idcf()) {
	include_once 'idf-idcf.php';
}
//include_once 'idf-stock-browser.php';

register_activation_hook(__FILE__, 'idf_activation');

function idf_activation() {
	idf_init_set_defaults();
	idf_init_transfer_key();
	if (!defined('ID_DEV_MODE') || 'ID_DEV_MODE' == false) {
		idf_update_products();
	}
}

function idf_init_set_defaults() {
	$platform = idf_platform();
	if (empty($platform)) {
		update_option('idf_commerce_platform', 'idc');
	}
	$version_array = array(
		'ignitiondeck-crowdfunding/ignitiondeck.php' => '1.5.12',
		'idcommerce/idcommerce.php' => '1.6.8'
	);
	set_transient('idf_plugin_versions', $version_array);
	set_site_transient('update_plugins', null);
}

function idf_init_transfer_key() {
	delete_option('idf_key_transfer');
	$key_transfer = get_option('idf_transfer_key');
	if (!$key_transfer) {
		$key_data = array(
			'keys' => array(
				'idcf_key' => '',
				'idc_key' => '',
			),
			'types' => array(
				'idcf_type' => 0,
				'idc_type' => 0,
			),
		);
		// Key transfer for IDCF
		$idcf_key = get_option('id_license_key');
		if (function_exists('idcf_license_key')) {
			$idcf_response = idcf_license_key($idcf_key);
			$idcf_valid = is_idcf_key_valid($idcf_response);
			if ($idcf_valid) {
				$key_data['types']['idcf_type'] = idcf_license_type($idcf_response);
				$key_data['keys']['idcf_key'] = $idcf_key;
			}
		}
		// Key transfer for IDC
		$idc_gen = get_option('md_receipt_settings');
		if (!empty($idc_gen)) {
			$idc_gen = maybe_unserialize($idc_gen);
			$idc_key = (isset($idc_gen['license_key']) ? $idc_gen['license_key'] : '');
			if (function_exists('idf_idc_validate_key')) {
				$idc_response = idf_idc_validate_key($idc_key);
				$idc_valid = is_idc_key_valid($idc_response);
				if ($idc_valid) {
					$key_data['types']['idc_type'] = idc_license_type();
					$key_data['keys']['idc_key'] = $idc_key;
				}
			}
		}
		$license_type = idf_parse_license($key_data);
		if ($license_type) {
			do_action('idf_transfer_key');
		}
	}
}

function idf_update_products() {
	$idc_installed = false;
	if (class_exists('ID_Member')) {
		if (function_exists('is_idc_licensed') && function_exists('was_idc_licensed')) {
			if (is_idc_licensed() || was_idc_licensed()) {
				$idc_installed = true;
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$idc_data = get_plugin_data(ABSPATH . 'wp-content/plugins/idcommerce/idcommerce.php');
			}
		}
	}
	$plugin_array = array(
		'ignitiondeck-crowdfunding/ignitiondeck.php',
		'idcommerce/idcommerce.php'
	);
	deactivate_plugins($plugin_array);
	idf_idcf_delivery(true);
	if ($idc_installed) {
		do_action('idc_force_update');
	}
}

//add_action( 'idc_force_update', 'idf_textdomain' );
function idf_textdomain() {
	// this function will automatically update IDC in the background
	load_plugin_textdomain( 'idf', false, dirname( plugin_basename( __FILE__ ) ).'/languages/' );
	require( ABSPATH . 'wp-content/plugins/idcommerce/idcommerce-update.php');
	require( ABSPATH . 'wp-admin/includes/plugin.php' );
	$idc_data = get_plugin_data(ABSPATH . '/wp-content/plugins/idcommerce/idcommerce.php');
	if (!empty($idc_data)) {
		$update_data = idc_update_info('basic_check', array('slug' => 'idcommerce/idcommerce.php', 'version' => $idc_data['Version']));
		$response = unserialize($update_data['body']);
		if (isset($response->package) && is_admin()) {
			require( ABSPATH . 'wp-admin/update.php' );
			require( ABSPATH . 'wp-admin/includes/file.php' );
			require( ABSPATH . 'wp-admin/includes/misc.php' );
			$plugin_args = array(
				'plugin' => 'idcommerce/idcommerce.php',
				'url' => admin_url().'update.php?action=upgrade-plugin&plugin='.urlencode('idcommerce/idcommerce.php'),
				'title' => __('Update Plugin'),
				'nonce' => 'upgrade-plugin_'.'idcommerce/idcommerce.php',
			);
			$upgrader = new Plugin_Upgrader(new Plugin_Upgrader_Skin($plugin_args));
			//$upgrader->upgrade('idcommerce/idcommerce.php');
		}
	}
}

add_action('wp_enqueue_scripts', 'idf_lightbox');
add_action('login_enqueue_scripts', 'idf_lightbox');

function idf_lightbox() {
	if (function_exists('get_plugin_data')) {
		$idf_data = get_plugin_data(__FILE__);
	}
	wp_register_script('idf-lite', plugins_url('js/idf-lite.js', __FILE__));
	wp_register_script('idf', plugins_url('js/idf.js', __FILE__));
	wp_register_style('magnific', plugins_url('lib/magnific/magnific.css', __FILE__));
	wp_register_script('magnific', plugins_url('lib/magnific/magnific.js', __FILE__));
	wp_register_style('idf', plugins_url('css/idf.css', __FILE__));
	wp_register_script('idf-stock-browser', plugins_url('js/idf-stock-browser.js', __FILE__));
	wp_enqueue_script('jquery');
	if (idf_enable_checkout()) {
		$checkout_url = '';
		$platform = idf_platform();
		if ($platform == 'wc' && !is_admin()) {
			if (class_exists('WooCommerce')) {
				global $woocommerce;
				$checkout_url = $woocommerce->cart->get_checkout_url();
			}
		}
		else if ($platform == 'edd' && class_exists('Easy_Digital_Downloads') && !is_admin()) {
			$checkout_url = edd_get_checkout_uri();
		}
		wp_enqueue_style('magnific');
		wp_enqueue_style('idf');
		wp_enqueue_script('idf');
		wp_enqueue_script('magnific');
		if ($platform == 'legacy' || $platform == 'wc') {
			wp_register_script('idflegacy-js', plugins_url('js/idf-legacy.js', __FILE__));
			wp_enqueue_script('idflegacy-js');
		}
		wp_localize_script('idf', 'idf_platform', $platform);
		// Let's set the ajax url
		$idf_ajaxurl = site_url('/wp-admin/admin-ajax.php');
		wp_localize_script('idf', 'idf_siteurl', site_url());
		wp_localize_script('idf', 'idf_ajaxurl', $idf_ajaxurl);
		if (isset($checkout_url)) {
			wp_localize_script('idf', 'idf_checkout_url', $checkout_url);
		}
		if (isset($idf_data['Version'])) {
			wp_localize_script('idf', 'idf_version', $idf_data['Version']);
		}
	}
	else {
		wp_enqueue_script('idf-lite');
		if (isset($idf_data['Version'])) {
			wp_localize_script('idf-lite', 'idf_version', $idf_data['Version']);
		}
	}
	wp_enqueue_script('idf-stock-browser');
}

function idf_font_awesome() {
	wp_register_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css');
	wp_enqueue_style('font-awesome');
}
add_action('wp_enqueue_scripts', 'idf_font_awesome');
add_action('admin_enqueue_scripts', 'idf_font_awesome');
?>