<?php

function idc_helix() {
	if (class_exists('Helix')) {
		return true;
	}
	return false;
}

add_action('wp_enqueue_scripts', 'idc_helix_scripts');

function idc_helix_scripts() {
	wp_register_style('idc_idhelix_css', plugins_url('css/idc_idhelix.css', __FILE__));
	wp_enqueue_style('idc_idhelix_css');
}
add_action('idhelix_above_icon_menu', 'idc_helix_icons');

function idc_helix_icons() {
	global $crowdfunding;
	$current_user = wp_get_current_user();
	$prefix = (function_exists('idf_querystring_prefix') ? idf_get_querystring_prefix() : '?');
	$durl = md_get_durl();
	ob_start();
	include_once('templates/_idcHelixIcons.php');
	$content = ob_get_contents();
	ob_end_clean();
	echo $content;
}

add_action('idhelix_after_login_form', 'idc_helix_menu');

function idc_helix_menu() {
	global $crowdfunding;
	$current_user = wp_get_current_user();
	$prefix = (function_exists('idf_querystring_prefix') ? idf_get_querystring_prefix() : '?');
	$durl = md_get_durl();
	$project_count = ID_Project::count_user_projects($current_user->ID);
	ob_start();
	include_once('templates/_idcHelixMenu.php');
	$content = ob_get_contents();
	ob_end_clean();
	echo $content;
}

/**
 * Filter from helix, to get the dashboard id from IDC, or any other plugin
 */
add_filter('idhelix_dashboard_id', 'idc_get_dashboard_id_helix', 2);
function idc_get_dashboard_id_helix($dash_id) {
	return md_get_did();
}

/**
 * Filter from Helix to get the redirection url for registration, note that action=register will be appened to this URL by helix
 */
add_filter('idhelix_dashboard_url', 'idc_dashboard_url_helix', 2);
function idc_dashboard_url_helix($redirect_to) {
	$durl = md_get_durl();
	if (!empty($durl)) {
		$redirect_to = $durl;
	}
	return $redirect_to;
}

/**
 * Filter for displaying any text with the user avatar, in case of IDC user credits will be displayed
 */
add_filter('idhelix_credits_display_text', 'idc_user_credits_text_helix', 2, 2);
function idc_user_credits_text_helix($text, $user_id) {
	$member = new ID_Member($user_id);
	$credits = $member->get_user_credits();
	if ($credits > 0) {
		$text = '<p>'.$credits.' '.__('Credits', 'memberdeck').'</p>';
	}
	return $text;
}

add_filter('idhelix_avatar_link', 'idc_helix_avatar_link');

function idc_helix_avatar_link($link) {
	$prefix = (function_exists('idf_querystring_prefix') ? idf_get_querystring_prefix() : '?');
	return md_get_durl().$prefix.'edit-profile=1';
}
?>