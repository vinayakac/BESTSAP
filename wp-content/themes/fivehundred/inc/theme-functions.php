<?php
/**
 * Template function for Fivehundred framework
 *
 * @package WordPress
 * @subpackage Fivehundred
 * @since Fivehundred 2.0.4
 */

/********************************************************************************************************
 *  Functions starting loop
 ********************************************************************************************************/
function idcf_projects() {
	global $project_loop;
	$project_loop->idcf_projects();
}

function idcf_wp_projects($args) {
	global $project_loop;
	$project_loop->idcf_wp_projects($args);
}

function idcf_have_projects() {
	global $project_loop;
	return $project_loop->idcf_have_projects();
}

function idcf_get_project() {
	global $post, $project, $project_loop;
	$id = $post->ID;
	// $content = the_project_content($id);
	$project_id = get_post_meta($id, 'ign_project_id', true);
	$id_project = new ID_Project($project_id);
	$project = $id_project->the_project();
	$project_loop->idcf_set_projects();
	return $project;
}
function idcf_the_project() {
	global $project_loop;
	$project_loop->idcf_the_project();
}

function idcf_get_project_title() {
	global $project_loop;
	return $project_loop->idcf_project_title(false);
}
function idcf_the_project_title() {
	global $project_loop;
	$project_loop->idcf_project_title();
}

function idcf_get_short_description() {
	global $project_loop;
	return $project_loop->idcf_project_short_description(false);
}
function idcf_the_short_description() {
	global $project_loop;
	$project_loop->idcf_project_short_description(true);
}

function idcf_get_raised_fund($featured = false, $no_markup = false) {
	global $project_loop;
	return $project_loop->idcf_project_raised_fund($featured, false, $no_markup);
}
function idcf_the_raised_fund($featured = false, $no_markup = false) {
	global $project_loop;
	$project_loop->idcf_project_raised_fund($featured, true, $no_markup);
}

function idcf_get_total_pledgers($featured = false, $no_markup = false) {
	global $project_loop;
	return $project_loop->idcf_project_total_pledgers($featured, false, $no_markup);
}
function idcf_the_total_pledgers($featured = false, $no_markup = false) {
	global $project_loop;
	$project_loop->idcf_project_total_pledgers($featured, true, $no_markup);
}

function idcf_get_the_goal($featured = false, $no_markup = false) {
	global $project_loop;
	return $project_loop->idcf_get_project_goal($featured, false, $no_markup);
}
function idcf_the_goal($featured = false, $no_markup = false) {
	global $project_loop;
	$project_loop->idcf_get_project_goal($featured, true, $no_markup);
}

function idcf_get_the_content() {
	global $project_loop;
	$content = $project_loop->idcf_the_content();
	return $content;
}

function idcf_get_project_progress_bar() {
	global $project_loop;
	return $project_loop->idcf_project_progress_bar(false);
}
function idcf_project_progress_bar() {
	global $project_loop;
	$project_loop->idcf_project_progress_bar();
}

function idcf_get_project_end_date($no_markup = false) {
	global $project_loop;
	return $project_loop->idcf_project_end_date(false, $no_markup);
}
function idcf_project_end_date($no_markup = false) {
	global $project_loop;
	$project_loop->idcf_project_end_date(true, $no_markup);
}

function idcf_get_funded_percent($round_figure = false) {
	global $project_loop;
	return $project_loop->idcf_project_funded_percent(false, $round_figure);
}
function idcf_the_funded_percent($round_figure = false) {
	global $project_loop;
	$project_loop->idcf_project_funded_percent(true, $round_figure);
}

function idcf_get_days_left($only_days = false) {
	global $project_loop;
	return $project_loop->idcf_project_days_left(false, false, $only_days);
}
function idcf_the_days_left($only_days = false) {
	global $project_loop;
	$project_loop->idcf_project_days_left(false, true, $only_days);
}

function idcf_get_project_author_name() {
	global $project_loop;
	return $project_loop->idcf_project_author_name(false);
}
function idcf_the_project_author_name() {
	global $project_loop;
	$project_loop->idcf_project_author_name();
}

function idcf_get_project_video() {
	global $project_loop;
	return $project_loop->idcf_project_video(false);
}
function idcf_the_project_video() {
	global $project_loop;
	$project_loop->idcf_project_video();
}

function idcf_get_project_image_url() {
	global $project_loop;
	return $project_loop->idcf_project_image_url(false);
}
function idcf_the_project_image_url() {
	global $project_loop;
	$project_loop->idcf_project_image_url();
}

/********************************************************************************************************
 *  Functions For Social buttons
 ********************************************************************************************************/

/**
 * Function to echo Facebook button markup
 */
function idcf_social_facebook_button($echo = false) {
	$markup = '<div id="fb-root"></div><div id="share-fb" class="fb-like social-share social-button" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false"></div>';
	return apply_filters('idcf_function_markup_echo', $markup, $echo);
}

/**
 * Function to echo Twitter button markup
 */
function idcf_social_twitter_button($echo = false) {
	$markup = '<div id="share-twitter" class="social-share social-button"><a href="https://twitter.com/share" class="twitter-share-button">'.__('Tweet', 'fivehundred').'</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>';

	return apply_filters('idcf_function_markup_echo', $markup, $echo);
}

/**
 * Function to echo LinkedIn button markup
 */
function idcf_social_linkedin_button($echo = false) {
	$markup = '<div id="share-linkedin" class="social-share social-button"><script src="//platform.linkedin.com/in.js" type="text/javascript"></script>
			<script type="IN/Share"></script></div>';
	return apply_filters('idcf_function_markup_echo', $markup, $echo);
}

/**
 * Function to echo Google button markup
 */
function idcf_social_google_button($echo = false) {
	$markup = '<div id="share-google" class="social-share social-button"><script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script><g:plusone size="medium"></g:plusone></div>';
	return apply_filters('idcf_function_markup_echo', $markup, $echo);
}

/**
 * Function to echo Pinterest button markup
 */
function idcf_social_pinterest_button($post_id, $echo = false) {
	$thumbnail = ID_Project::get_project_thumbnail($post_id);
	$markup = '<div id="share-pinterest" class="social-share social-button"><a href="http://pinterest.com/pin/create/button/?url='.currentPageURL().'&media='.$thumbnail.'" class="pin-it-button" count-layout="horizontal"><img border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a><script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script></div>';
	return apply_filters('idcf_function_markup_echo', $markup, $echo);
}

/**
 * Function get generate Embed code button with social sharing buttons
 */
function idcf_embed_code_button($project_id, $echo = false) {
	$markup = '<div id="share-embed" class="social-share"><i class="fa fa-code"></i></div>
				<div class="embed-box social-share" style="display: none;">
					<code>&#60;iframe frameBorder="0" scrolling="no" src="'.home_url().'/?ig_embed_widget=1&product_no='.(isset($project_id) ? $project_id : '').'" width="214" height="366"&#62;&#60;/iframe&#62;</code>
				</div>
				<div class="clear"></div>';
	return apply_filters('idcf_function_markup_echo', $markup, $echo);
}

/********************************************************************************************************
 *  Functions For Levels
 ********************************************************************************************************/

/**
 * Function for giving level price markup
 */
function idcf_fivehundred_level_price($post_id, $level, $type = null, $echo = false) {
	if (empty($type)) {
		$type = get_post_meta($post_id, 'ign_project_type', true);
	}
	$markup = '<div class="level-price">';
	if ($type !== 'pwyw' && $level->meta_price > 0) {
		$markup .= apply_filters('id_price_selection', $level->meta_price, $post_id);
	}
	$markup .= '</div>';

	return apply_filters('idcf_function_markup_echo', $markup, $echo);
}

/**
 * Function for level description markup
 */
function idcf_fivehundred_level_desc($level, $echo = false) {
	$markup = '<div class="ign-level-desc">
					'.$level->meta_short_desc.'
				</div>';
	return apply_filters('idcf_function_markup_echo', $markup, $echo);
}

/**
 * Function for giving level limit markup
 */
function idcf_fivehundred_level_limit($level, $echo = false) {
	if ($level->meta_limit !== '' && $level->meta_limit > 0) {
		$markup = '<div class="ign-level-counts">
						<span>'.__('Limit', 'fivehundred').': '.$level->meta_count.' of '.$level->meta_limit.' '.__('taken', 'fivehundred').'.</span>
					</div>';
		return apply_filters('idcf_function_markup_echo', $markup, $echo);
	}
	return '';
}

/**
 * Function for giving the title of level with its price
 */
function idcf_fivehundred_level_title($post_id, $level, $type = null, $echo = false) {
	if (empty($type)) {
		$type = get_post_meta($post_id, 'ign_project_type', true);
	}
	$markup = '<div class="ign-level-title">
					<span> '.$level->meta_title.'</span>
					'.idcf_fivehundred_level_price($post_id, $level, $type).'
					<div class="clear"></div>
				</div>';
	return apply_filters('idcf_function_markup_echo', $markup, $echo);
}

/**
 * Function for level group markup calling multiple already build functions
 */
function idcf_fivehundred_level_group($post_id, $level, $type = null, $echo = false) {
	if (empty($type)) {
		$type = get_post_meta($post_id, 'ign_project_type', true);
	}
	$markup = '<div class="level-group">
					'.idcf_fivehundred_level_title($post_id, $level, $type).'
					'.idcf_fivehundred_level_desc($level).'
				
					'.idcf_fivehundred_level_limit($level);
	ob_start();
	do_action('id_after_level');
	$markup .= ob_get_contents();
	ob_end_clean();
	$markup .= '</div>';

	return apply_filters('idcf_function_markup_echo', $markup, $echo);
}

/********************************************************************************************************
 *  General purpose functions
 ********************************************************************************************************/

/**
 * Functions for IgnitionDeck Powered by logo
 */
function idcf_powered_by_logo($settings = null, $echo = true) {
	if (empty($settings)) {
		$settings = getSettings();
	}
	if ($settings->id_widget_logo_on == 1) {
		$markup = '<div id="poweredbyID"><span><a href="http://www.ignitiondeck.com" title="Crowdfunding Wordpress Theme by IgnitionDeck"></a></span></div>';
		return apply_filters('idcf_function_markup_echo', $markup, $echo);
	}
}
?>