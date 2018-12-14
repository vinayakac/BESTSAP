<?php
add_action('the_title', 'idc_dashboard_titles');

function idc_dashboard_titles($title) {
	global $post;
	if (!is_admin()) {
		if (isset($post->ID) && $post->ID == md_get_did()) {
			return apply_filters('idc_dashboard_title', $title);
		}
	}
	return $title;
}

/**
 * Filter called from idc_init_checks() in idc-functions
 */
function idc_renew($content) {
	$product_id = $_GET['idc_renew'];
	//ob_start();
	$content = do_shortcode('[idc_checkout product="'.$product_id.'"]');
	return $content;
	//$content = ob_get_contents();
	//ob_end_clean();
	return $content;
}

/**
 * Filter called from idc_init_checks() in idc-functions
 */
function idc_orders_list($content) {
	global $crowdfunding, $global_currency;
	$permalink_structure = get_option('permalink_structure');
	if (empty($permalink_structure)) {
		$prefix = '&';
	}
	else {
		$prefix = '?';
	}
	$default_timezone = get_option('timezone_string');
	if(empty($default_timezone)){
		$default_timezone = "UTC";
	}
	// The call for cancelling the order/pledge
	if (isset($_GET['cancel_pledge'])) {
		// Restoring the credits, getting level details for the credits
		$level = ID_Member_Level::get_level($_GET['level']);
		if (!empty($level)) {
			$order = new ID_Member_Order($_GET['cancel_pledge']);
			if (!empty($order)) {
				// Getting order details
				$the_order = $order->get_order();
				if (!empty($the_order)) {
					$user_id = $the_order->user_id;
					$credits = $level->credit_value;

					// Now removing the order
					$order->delete_order();
					ID_Order::delete_order($_GET['pay_id']);
					mdid_remove_order($_GET['cancel_pledge']);
					// Adding those credits back to user's
					ID_Member::add_credits($the_order->user_id, $credits);
				}
			}
		}
	}
	ob_start();
	echo '<div class="memberdeck">';
	include_once IDC_PATH.'templates/_mdProfileTabs.php';
	$levels = ID_Member_Level::get_levels();
	include_once IDC_PATH.'templates/_orderList.php';
	if (isset($_GET['view_receipt'])) {
		$current_user = wp_get_current_user();
		$order_id = $_GET['view_receipt'];
		$order = new ID_Member_Order($order_id);
		$last_order = $order->get_order();
		if ($last_order->user_id == $current_user->ID) {
			$i = 0;
			foreach ($levels as $level) {
				$level_id = $level->id;
				if ($last_order->level_id == $level_id) {
					$order_level_key = $i;
					break;
				}
				$i++;
			}
			$thumbnail = apply_filters('idc_order_level_thumbnail', null, $last_order);
			// $currency_symbol = ID_Member_Order::get_order_currency_sym($order_id);
			$price = apply_filters('idc_order_price', $last_order->price, $last_order->id);
			
			include_once 'templates/_orderLightbox.php';
		}
	}
	echo '</div>';
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

/**
 * Filter called from memberdeck_profile_check() in idc-functions
 */
function memberdeck_profile_form($content) {
	ob_start();
	global $first_data;
	global $stripe_api_version;
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;
	$nicename = $current_user->display_name;
	$user_firstname = $current_user->user_firstname;
	$user_lastname = $current_user->user_lastname;
	$email = $current_user->user_email;
	$usermeta = get_user_meta($user_id);
	$url = $current_user->user_url;
	if (isset($usermeta['description'][0]))
		$description = $usermeta['description'][0];
	$url = $current_user->user_url;
	if (isset($usermeta['twitter'][0]))
		$twitter = $usermeta['twitter'][0];
	if (isset($usermeta['facebook']))
		$facebook = $usermeta['facebook'][0];
	if (isset($usermeta['google']))
		$google = $usermeta['google'][0];
	$show_subscriptions = false;
	$settings = get_option('memberdeck_gateways');
	if (isset($settings)) {
		$es = (isset($settings['es']) ? $settings['es'] : 0);
		$ecb = (isset($settings['ecb']) ? $settings['ecb'] : 0);
		$eauthnet = (isset($settings['eauthnet']) ? $settings['eauthnet'] : '0');
		if (isset($first_data) && $first_data) {
			$efd = (isset($settings['efd']) ? $settings['efd'] : 0);
		}
		if ($es == 1) {
			$customer_id = customer_id();
			if (!empty($customer_id)) {
				$has_subscription = ID_Member_Subscription::has_subscription($user_id);
				if (!empty($has_subscription)) {
					$show_subscriptions = true;
				}
			}
		}
		else if (isset($efd) && $efd == 1) {
			$fd_card_details = fd_customer_id();
			if (!empty($fd_card_details)) {
				$customer_id = $fd_card_details['fd_token'];
			}
		}
		else if ($eauthnet == 1) {
			$authnet_customer_ids = authnet_customer_id();
			if (!empty($authnet_customer_ids)) {
				$authorizenet_payment_profile_id = $authnet_customer_ids['authorizenet_payment_profile_id'];
				$authorizenet_profile_id = $authnet_customer_ids['authorizenet_profile_id'];
				$customer_id = $authorizenet_payment_profile_id;
				if (!empty($authorizenet_profile_id) && !empty($authorizenet_payment_profile_id)) {
					if (empty($has_subscription)) {
						$has_subscription = ID_Member_Subscription::has_subscription($user_id);
						if (!$show_subscriptions) {
							$show_subscriptions = true;
						}
					}
				}
			}
		}
	}

	$general = get_option('md_receipt_settings');
	if ($show_subscriptions) {
		if ($eauthnet == 1) {
			$plans = array();

			// Requiring the library of Authorize.Net
			require("lib/AuthorizeNet/vendor/authorizenet/authorizenet/AuthorizeNet.php");
			define("AUTHORIZENET_API_LOGIN_ID", $settings['auth_login_id']);
			define("AUTHORIZENET_TRANSACTION_KEY", $settings['auth_transaction_key']);
			if ($settings['test'] == '1') {
				define("AUTHORIZENET_SANDBOX", true);
			} else {
				define("AUTHORIZENET_SANDBOX", false);
			}

			$subscriptions = $has_subscription;
			foreach ($subscriptions as $subscription) {
				if ($subscription->source == 'authorize.net') {
					// Checking if this is a Auth.Net subscription by using API
					$subscriptionARB = new AuthorizeNetARB;
					$response_subscription = $subscriptionARB->getSubscriptionStatus($subscription->subscription_id);
					if ($response_subscription->isOk()) {
						// This means that subscription exists so it's of Auth.Net
						// Check it's status, whether it's cancelled or not
						if ($response_subscription->xml->status != 'canceled') {
							$plan = array();
							$plan['id'] = $subscription->subscription_id;
			
							// Getting level name to be used as Subscription Name
							$level = ID_Member_Level::get_level($subscription->level_id);
							$plan['plan_id'] = $level->level_name;
							$plan['gateway'] = 'authorize.net';
							$plans[] = $plan;
						}
					}
				}
			}
		}
		else {
			$sk = stripe_sk();
			if (!class_exists('Stripe')) {
				require_once 'lib/Stripe.php';
			}
			Stripe::setApiKey($sk);
			Stripe::setApiVersion($stripe_api_version);
			try {
				$subscriptions = Stripe_Customer::retrieve($customer_id)->subscriptions->all();
			}
			catch (Stripe_InvalidRequestError $e) {
				//
			}
			catch (Exception $e) {
				//
			}
			if (!empty($subscriptions)) {
				$plans = array();
				foreach ($subscriptions->data as $sub) {
					if ($sub->status == 'active') {
						$plan = array();
						$plan_id = $sub->plan->id;
						$plan['id'] = $sub->id;
						$plan['plan_id'] = $plan_id;
						$plan['gateway'] = 'stripe';
						$plans[] = $plan;
					}
				}
			}
		}

		// If coinbase is active, then get its subscriptions
		if ($ecb == 1) {

		}
	}

	$instant_checkout = instant_checkout();
	// $show_icc = get_user_meta($user_id, 'customer_id', true);
	$show_icc = allow_instant_checkout();
	//$instant_checkout = get_user_meta($user_id, 'instant_checkout', true);
	// Getting the shortcode button lightbox image
	if (isset($_POST['edit-profile-submit'])) {
		$user_firstname = esc_attr($_POST['first-name']);
		$user_lastname = esc_attr($_POST['last-name']);
		$email = esc_attr($_POST['email']);
		$nicename = esc_attr($_POST['nicename']);
		$description = esc_attr($_POST['description']);
		$url = esc_attr($_POST['url']);
		$twitter = esc_attr($_POST['twitter']);
		$facebook = esc_attr($_POST['facebook']);
		$google = esc_attr($_POST['google']);
		if (isset($_POST['pw'])) {
			$pw = esc_attr($_POST['pw']);
		}
		if (isset($_POST['cpw'])) {
			$cpw = esc_attr($_POST['cpw']);
		}
		$description = esc_attr($_POST['description']);
		if (isset($_POST['instant_checkout'])) {
			$instant_checkout = absint($_POST['instant_checkout']);
		}
		else {
			$instant_checkout = 0;
		}
		// Storing checkout image for Button shortcode
		if (isset($_FILES['button_checkout_image']) && $_FILES['button_checkout_image']['size'] > 0) {
			$checkout_image_name = sanitize_text_field($_FILES['button_checkout_image']['name']);
			$wp_upload_dir = wp_upload_dir();
			if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
			$file = wp_handle_upload($_FILES['button_checkout_image'], array('test_form' => false));
			$filetype = wp_check_filetype(basename($file['file']), null);
			$title = preg_replace('/\.[^.]+$/', '', basename($file['file']));
			$attachment = array(
		    	'guid' => $wp_upload_dir['url'] . '/' . basename( $file['file'] ), 
		    	'post_mime_type' => $filetype['type'],
		    	'post_title' => $checkout_image_name,
		    	'post_content' => '',
		    	'post_status' => 'inherit',
		    	'post_author' => $user_id
		  	);
		  	$insert = wp_insert_attachment($attachment, $file['file']);
		  	// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			// Generate the metadata for the attachment, and update the database record.
			$attach_data = wp_generate_attachment_metadata( $insert, $file['file'] );
			wp_update_attachment_metadata( $insert, $attach_data );
			update_user_meta($user_id, 'idc_button_checkout_image', $insert);
		}
	}
	$idc_button_checkout_image = get_user_meta($user_id, 'idc_button_checkout_image', true);

	if (isset($pw) && $pw !== $cpw) {
		$error = __('Passwords do not match', 'memberdeck');
	}
	else if (isset($_GET['edited'])) {
		$success = __('Profile Updated!', 'memberdeck');
	}

	// Setting error if there is one
	if (isset($_GET['error_msg'])) {
		$error = $_GET['error_msg'];
	}

	include 'templates/_editProfile.php';
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

/**
 * Filter called from move_to_protect() in idc-functions
 */
function idmember_protect_singular($content) {
	ob_start();
	global $post;
	if (is_user_logged_in()) {
		if (is_multisite()) {
			require (ABSPATH . WPINC . '/pluggable.php');
			$current_user = wp_get_current_user();
			$md_user_levels = null;
			if (!empty($current_user)) {
				$user_id = $current_user->ID;
				$md_user = ID_Member::user_levels($user_id);
				if (!empty($md_user)) {
					$md_user_levels = unserialize($md_user->access_level);
				}
			}
		}
		else {
			$md_user_levels = ID_Member::get_user_levels();
		}
	}
	// Check if the Post/Project is under a protected Category/tag, if yes, then we need to test if user have the products
	// that allows viewing of this Post/Projects
	if (idc_in_protected_category($post->ID)) {
		// See if user is allowed to view that category
		if ( ! idc_user_allowed_term_access($post->ID, '', 'category', (isset($md_user_levels) ? $md_user_levels : null)) ) {
			include_once 'templates/_protectedPage.php';
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
	}
	else if (idc_in_protected_tag($post->ID)) {
		// See if user is allowed to view that tag
		if ( ! idc_user_allowed_term_access($post->ID, '', 'tag', (isset($md_user_levels) ? $md_user_levels : null)) ) {
			include_once 'templates/_protectedPage.php';
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
	}

	if (isset($post->ID)) {
		$post_id = $post->ID;
		list($user_post_access, $message_code) = idc_user_allowed_post_access($post_id, '', (isset($md_user_levels) ? $md_user_levels : null));
		// If user is not allowed to access this post, just return the protectedPage template
		if (!$user_post_access) {
			include_once 'templates/_protectedPage.php';
			$content = ob_get_contents();
		}
	}
	else {
		//echo 'no post id';
	}
	ob_end_clean();
	return $content;
}

/**
 * Filter called from move_to_protect() in idc-functions
 */
function idmember_protect_category($content) {
	if (current_user_can('manage_options')) {
		//return $content;
	}
	ob_start();
	global $wp_query, $post;
	if (is_multisite()) {
		require (ABSPATH . WPINC . '/pluggable.php');
		$current_user = wp_get_current_user();
		$md_user_levels = null;
		if (!empty($current_user)) {
			$user_id = $current_user->ID;
			$md_user = ID_Member::user_levels($user_id);
			if (!empty($md_user)) {
				$md_user_levels = unserialize($md_user->access_level);
			}
		}
	}
	else {
		$md_user_levels = ID_Member::get_user_levels();
	}
	//print_r($wp_query);
	$term_array = apply_filters('idc_protect_terms', array('category', 'post_tag'));
	$tag_terms = get_terms(array('category', 'post_tag'));
	//print_r($tag_terms);
	$term_array = array();
	$i = 0;
	if (is_array($tag_terms)) {
		//print_r($tag_terms);
		foreach ($tag_terms as $object) {
			//echo $k." = ".$v."<br/>";
			//print_r($object);
			//if ($object == 'term_id') {
				$term_id = $object->term_id;
				//echo $term_id;
				$term_protected = get_option('protect_term_'.$term_id);
				//echo $term_protected;
				$post_tags = wp_get_post_tags(isset($post) ? $post->ID : null);
				//print_r($post_tags);
				$post_cats = wp_get_post_categories(isset($post) ? $post->ID : null);
				//print_r($post_cats);
				$tag_match = in_array($term_id, $post_cats);
				$cat_match = in_array($term_id, $post_tags);
				if ($term_protected && ($cat_match || $tag_match)) {
					if (is_user_logged_in()) {
						//echo 'protected';
						$term_array[$i]['term_id'] = $term_id;
						$allowed = get_option('term_'.$term_id.'_allowed_levels');
						if (isset($allowed)) {
							$array = unserialize($allowed);
							if (!empty($array)) {
								$term_array[$i]['terms'] = $array;
								//print_r($md_user_levels);
								foreach ($term_array as $array) {
									//print_r($array);
									foreach ($md_user_levels as $level) {
										if (in_array($level, $array['terms'])) {
											$pass = true;
											break;
										}
										else {
											$fail = true;
										}
									}
									if (!isset($pass)) {
										continue;
									}
								}
								if (!isset($pass)) {
									// user doesn't own any required level
									include_once 'templates/_protectedPage.php';
									$content = ob_get_contents();
								}
							}
							else {
								// no levels will grant access
								include_once 'templates/_protectedPage.php';
								$content = ob_get_contents();
							}
						}
						else {
							// user doesn't own any levels
							include_once 'templates/_protectedPage.php';
							$content = ob_get_contents();
						}
					}
					else {
						// user not logged in
						include_once 'templates/_protectedPage.php';
						$content = ob_get_contents();
					}
				}
			//}
			$i++;
		}
	}
	//print_r($term_array);
	/*if (!empty($term_array)) {
		foreach ($term_array as $term_levels) {
			if (!empty($md_user_levels)) {
				foreach ($md_user_levels as $md_level) {
					if (in_array($md_level, $term_levels['terms'])) {
						$fail = true;
					}
					else {
						$pass = true;
					}
				}
			}
		}
	}*/
	ob_end_clean();
	return $content;
}

function idmember_protect_bbp($content) {
	global $post;
	if (isset($post)) {
		$post_id = $post->ID;
		$post_parent = $post->post_parent;
		$protected = get_post_meta($post_id, 'memberdeck_protected_posts', true);
		$parent_protected = get_post_meta($post_parent, 'memberdeck_protected_posts', true);
		if (!empty($protected) || !empty($parent_protected)) {
			$access = array();
			$parent_access = array();
			ob_start();
			if (!empty($protected)) {
				$access = unserialize($protected);
				//print_r($access);
			}
			if (!empty($parent_protected)) {
				$parent_access = unserialize($parent_protected);
				//print_r($parent_access);
			}
			$login_url = site_url('/wp-login.php');
			if (is_user_logged_in()) {
				$current_user = wp_get_current_user();
				$member = new ID_Member();
				$member_levels = $member->user_levels($current_user->ID);
				$unserialized = unserialize($member_levels->access_level);

				if (empty($unserialized) && !current_user_can('manage_options')) {
					//echo 'no levels';
					$unserialized = array();
					// wp_redirect(md_get_durl());
					// $content = plugins_url( 'templates/_protectedPage.php', __FILE__ );
					include_once 'templates/_protectedPage.php';
					$content = ob_get_contents();
				}
				foreach ($unserialized as $check) {
					if ( !in_array($check, $access) && !in_array($check, $parent_access) && !current_user_can('manage_options')) {
						$fail = true;
					}
					else {
						$pass = true;
					}

				}
				if (!isset($pass)) {
					//echo 'does not match';
					// wp_redirect(md_get_durl());
					// $content = plugins_url( 'templates/_protectedPage.php', __FILE__ );
					include_once 'templates/_protectedPage.php';
					$content = ob_get_contents();
				}
			}
			else {
				// wp_redirect(md_get_durl());
				// $content = plugin_dir_path( __FILE__ ) . 'templates/_protectedPage.php';
				include_once 'templates/_protectedPage.php';
				$content = ob_get_contents();
			}
			ob_end_clean();
		}
	}
	return $content;
}

// add_filter('bbp_template_include_theme_compat', 'idmember_load_filters_bbp', 12);
add_filter('bbp_template_include', 'idmember_load_filters_bbp', 11);
// add_filter('bbp_template_include_theme_supports', 'idmember_protect_bbp', 11);
// add_filter('bbp_get_topic', 'idmember_protect_bbp', 10, 3);
// add_filter('the_content', 'idmember_load_filters_bbp', 2);

function idmember_load_filters_bbp($content) {
	global $post;
	// We don't need to restore any filters if it's a Forum post_type
	if (!empty($post) && $post->post_type == "forum") {
		//bbp_restore_all_filters( 'the_content' );
		return $content;
	}
	// If it's Topic post, then restore the_content filter
	//bbp_restore_all_filters( 'the_content' );
	add_filter('the_content', 'idmember_protect_bbp');
	return $content;
}

add_filter('login_redirect', 'memberdeck_login_redirect', 3, 3);

function memberdeck_login_redirect($redirect_to, $request, $user) {
	// If user is an error object, redirect to dashboard with error code
	if (is_wp_error($user)) {
		// Getting the permalink structure
		if (function_exists('idf_get_querystring_prefix')) {
			$error_code = $user->get_error_code();
			// Check if code is empty, if yes, then it's a logout call, we don't need to touch it
			if (!empty($error_code)) {
				$error_code = $user->get_error_code();
				$prefix = idf_get_querystring_prefix();
				if (stripos($redirect_to, '?') !== -1) {
					$prefix = '&';
				}
				if (stripos($redirect_to, 'login_failure')) {
					parse_str($redirect_to, $string);
					$string['error_code'] = $error_code;
					$redirect_to = urldecode(http_build_query($string));
				}
				else {
					$redirect_to = $redirect_to.$prefix.'login_failure=1&error_code='.$error_code;
				}
				wp_redirect($redirect_to);
			}
		}
	}
    return $redirect_to;
}

add_filter('idc_dashboard_notification', 'idc_register_success');

function idc_register_success($notification) {
	if (isset($_GET['account_created']) && $_GET['account_created']) {
		$notification .= '<p class="success">'.__('Your account has been successfully created.', 'memberdeck').'</p>';
	}
	else if (isset($_GET['account_updated']) && $_GET['account_updated']) {
		$notification .= '<p class="success">'.__('Your account has been successfully updated.', 'memberdeck').'</p>';
	}
	return $notification;
}

add_filter( 'idc_dashboard_notification', 'idc_order_lightbox' );

function idc_order_lightbox($notification) {
	global $global_currency;
	if (isset($_GET['idc_product']) && isset($_GET['paykey'])) {
		if (class_exists('ID_Project')) {
			$settings = ID_Project::get_id_settings();
		}
		$current_user = wp_get_current_user();
		$order = new ID_Member_Order(null, $current_user->ID, $_GET['idc_product']);
		$level = ID_Member_Level::get_level($_GET['idc_product']);
		$levels = array($level);
		// Project and order details to be shown on template
		$last_order = apply_filters('idc_last_order_lightbox', $order->get_last_order());

		// First checking if this lightbox is loaded for the 1st time using transients, if not, don't load lighbox and return $notification
		if ( isset($last_order) && false === ( $is_set = get_transient( 'idc_order_lightbox_'.$last_order->id ) ) ) {
			set_transient( 'idc_order_lightbox_'.$last_order->id, "value_stored", 0 );
			$status = "completed";
			// Getting the order price using filter
			$price = apply_filters('idc_order_price', $last_order->price, $last_order->id);
		}
		else if (! isset($last_order) ) {
			// If level is recurring, the check there is a subscription for it 
			if ($level->level_type == "recurring") {
				// We need to check if recurring order is made and not returned by payment gateway yet
				$subscription = new ID_Member_Subscription(null, $current_user->ID, $_GET['idc_product']);
				$sub_details = $subscription->find_subscription();
				if (!empty($sub_details)) {
					// The subscription exists then it means that order hasn't yet completed, so go on
					$status = "pending";
					$last_order = array();
					if (isset($_GET['mdid_checkout'])) {
						$price = apply_filters('id_price_format', $level->level_price, $sub_details->source);
					} else {
						$price = apply_filters('idc_price_format', $level->level_price, $sub_details->source);
					}
				} else {
					return $notification;
				}
			}
		}
		else {
			return $notification;
		}

		ob_start();
		include_once 'templates/_orderLightbox.php';
		$notification .= ob_get_contents();
		ob_end_clean();
	}

	return $notification;
}

/**
 * Filter to get the upgrade pathways and get price difference if any
 * Return: updated product price
 */
function idc_upgrade_pathways_price($product_price, $product_id, $user_id, $ignore_upgrade) {
	// If $pwyw_applied is false, then it means we need to calculate price difference in pathways (if exists)
	// otherwise just return the price, as pwyw will already have the difference price
	if ($ignore_upgrade === false) {
		$level = ID_Member_Level::get_level($product_id);
		// If level is recurring, don't perform upgrade level functionality
		if ($level->level_type != 'recurring') {
			$idc_pathways = new ID_Member_Pathways(null, $product_id);
			$product_pathway = $idc_pathways->get_product_pathway();
			if (!empty($product_pathway)) {
				$idc_pathways->upgrade_pathway = $product_pathway->upgrade_pathway;
				$level_difference = $idc_pathways->get_lower_product_difference($product_price, $user_id);
				if ($level_difference > 0) {
					// Setting new level price
					$product_price = $level_difference;
				}
			}
		}
	}
	return $product_price;
}
add_filter('idc_checkout_level_price', 'idc_upgrade_pathways_price', 10, 4);

/**
 * Filter to show checkout form when used Button shortcode
 */
function idc_shortcode_button_checkout($content) {
	$product_id = absint($_POST['product_id']);
	if ($product_id > 0) {
		$shortcode = do_shortcode('[idc_checkout product="'.$product_id.'"]');
		return $shortcode;
	}
	return $content;
}


add_filter('idc_price_format', 'idc_price_format', 10, 2);

function idc_price_format($amount, $gateway = null) {
	if ($gateway == 'BTC' || $gateway == 'coinbase') {
		$amount = sprintf('%f', (float) $amount);
	}
	else if ($gateway !== 'credit' && $gateway !== 'credits') {
		if ($amount > 0) {
			$amount = number_format(preg_replace('/[^0-9.]+/', "", $amount), 2, '.', ',');
		}
		else {
			$amount = '0.00';
		}
	}
	else {
		if ($amount > 0) {
			$amount = number_format($amount);
		}
		else {
			$amount = '0';
		}
	}
	return $amount;
}

/**
 * Filter to append currency symbol based on order meta
 */
add_filter('idc_order_price', 'idc_order_price', 10, 2);

function idc_order_price($amount, $order_id) {
	global $global_currency;
	$meta = ID_Member_Order::get_order_meta($order_id, 'gateway_info', true);
	$amount = apply_filters('idc_price_format', $amount, (!empty($meta['gateway']) ? $meta['gateway'] : $global_currency));
	if (!empty($meta)) {
		if ($meta['gateway'] == 'credit') {
			$amount = 0;
			$order = new ID_Member_Order($order_id);
			$the_order = $order->get_order();
			if (!empty($the_order)) {
				$level = ID_Member_Level::get_level($the_order->level_id);
				if (!empty($level)) {
					$pwyw_price = ID_Member_Order::get_order_meta($the_order->id, 'pwyw_price', true);
					if ($pwyw_price > 0 && $meta['currency_code'] == "credits") {
						$amount = apply_filters('idc_price_format', $pwyw_price, 'credit');
					}
					else {
						$amount = apply_filters('idc_price_format', $level->credit_value, 'credit');
					}
				}
			}
			$amount = $amount.' '. apply_filters('idc_credits_label', __('Credits', 'memberdeck'), true, $amount);
		} else {
			$currency_sym = ID_Member_Order::get_order_currency_sym($order_id, $meta);
			$amount = $currency_sym.$amount;
		}
	}
	else {
		if ($global_currency == 'credits') {
			$amount = $amount.' '. apply_filters('idc_credits_label', __('Credits', 'memberdeck'), true, $amount);
		} else {
			$currency_sym = ID_Member_Order::get_order_currency_sym($order_id, $meta);
			$amount = $currency_sym.$amount;
		}
	}
	return $amount;
}

/**
 * Filter to display label for Credits (Virtual currency in IDC)
 */
add_filter('idc_credits_label_replace', 'idc_credits_label_filter', 10, 3);
add_filter('idc_credits_label', 'idc_credits_label_filter', 10, 3);

function idc_credits_label_filter($credits_label, $plural, $amount = null) {
	// Getting the saved option from admin side
	$virtual_currency_labels = get_option('virtual_currency_labels');

	// Determine the plural is true or false depending on the given amount (if given)
	if ($amount !== null) {
		// Removing formatting if any from the amount
		$amount = preg_replace('/[^0-9.]+/', "", $amount);
		if ($amount > 1) {
			$plural = true;
		} else {
			$plural = false;
		}
	}

	if (!empty($virtual_currency_labels)) {
		if ($plural) {
			$credits_label = $virtual_currency_labels['label_plural'];
		} else {
			$credits_label = $virtual_currency_labels['label_singular'];
		}
	}
	else {
		if ($plural) {
			$credits_label = 'Credits';
		} else {
			$credits_label = 'Credit';
		}
	}
	return $credits_label;	
}

/**
 * Filter to display checkout form for renewing product
 */
function idc_product_renewal_checkout($content) {
	$product_id = absint($_GET['idc_renewal_checkout']);
	if ($product_id > 0) {
		$shortcode = do_shortcode('[idc_checkout product="'.$product_id.'"]');
		return $shortcode;
	}
	return $content;
}

/**
 * Filter for showing currency symbol of active credit card gateway
 */
function idc_credit_card_currency($code) {
	$settings = get_option('memberdeck_gateways');
	if ($settings['es'] == 1) {
		$currency = $settings['stripe_currency'];
		$code = md_currency_symbol($currency);
	}
	else if ($settings['efd'] == 1) {
		$code = '$';
	}
	else if ($settings['eauthnet'] == 1) {
		$code = '$';
	}
	return $code;
}
add_filter('idc_credit_card_currency', 'idc_credit_card_currency', 10, 1);

/**
 * Filter for calculating the fee and returning
 */
function idc_fee_calculation($fee, $price, $fee_type, $gateway) {
	if ($gateway == "pp-adaptive") {
		if ($fee_type == 'percentage') {
			$fee = floatval($price) * (floatval($fee) / 100);
		}
		else {
			// As fee is stored in Cents in Enterprise settings
			$fee = $fee / 100;
		}
	}
	else if ($gateway == "stripe") {
		if ($fee_type == 'percentage') {
			$fee = round($price * ($fee / 100));
		}
	}
	else {
		if ($fee_type == 'percentage') {
			$fee = number_format($price * ($fee / 100), 2, '.', '');
		}
	}
	return $fee;
}
add_filter('idc_fee_amount', 'idc_fee_calculation', 10, 4);

//==============================================================================================================================
// Memberdeck and IgnitionDeck shared filters
//==============================================================================================================================
if ($crowdfunding) {
	remove_filter('id_display_currency', 'id_display_currency_filter', 2);
//	remove_filter('id_price_format', 'id_price_format', 10);
//	remove_filter('id_funds_raised', 'id_funds_raised', 10);
//	remove_filter('id_project_goal', 'id_project_goal', 10);
	add_filter('id_display_currency', 'mdid_display_currency', 11, 2);
	// add_filter('idc_display_currency', 'mdid_display_currency', 11, 2);
	add_filter('id_price_format', 'filter_project_price', 11, 3);
    add_filter('id_funds_raised', 'filter_project_price', 11, 3);
	add_filter('id_project_goal', 'filter_project_price', 11, 3);
	add_filter('id_lightbox_currency_symbol', 'idc_global_currency_symbol', 10, 2);
//	add_filter('id_funds_raised', 'id_idc_funds_format', 11, 3);
//	add_filter('id_project_goal', 'id_idc_funds_format', 11, 3);
	// add_filter('id_lightbox_currency_symbol', 'idc_global_currency_symbol', 10, 2);
}

/**
 * The filter to display either currency or number of credits to purchase a project
 */
function filter_project_price($amount, $post_id, $noformat = false) {
	global $global_currency;
	// Getting the "currency/credit" from options stored in IDC > Crowdfunding / from Project options
	if ($global_currency == "credits") {
		if ($noformat) {
			$amount = $amount;//.' '. apply_filters('idc_credits_label', __('Credits', 'memberdeck'), true);
		}
		else {
			$amount = number_format((float) preg_replace('/[^0-9.]+/', "", $amount));// .' '. apply_filters('idc_credits_label', __('Credits', 'memberdeck'), true);
		}
	}
	// return apply_filters('id_display_currency', $amount, $post_id);
	return $amount;
}

/**
 * Filter Function to filter out raised and goals amount
 * @param double $amount 
 * @param int    $post_id 
 * @param bool   $noformat 
 * @return string Formatted Price
 */
function id_idc_funds_format($amount, $post_id, $noformat = false) {
	if ($noformat) {
		return $amount;
	}
	else {
		return apply_filters('id_display_currency', apply_filters('id_price_format', $amount, $post_id), $post_id);
	}
}

/**
 * The filter to format the currency in proper format with its symbol
 */
function mdid_display_currency($amount, $post_id) {
	global $global_currency;
	if (!empty($global_currency)) {
		if ($global_currency == "credits") {
			$currency_code = apply_filters('idc_credits_label', __('Credits', 'memberdeck'), true, $amount);
		}
		else {
			$currency_code = md_currency_symbol($global_currency);
		}
	}
	// // Removing all currencies except the formatting
	// $amount = preg_replace('/[^0-9.,]+/', "", $amount);
	if ("right" == apply_filters('idc_currency_symbol_position', 'left', $post_id)) {
		$amount = $amount . " " . $currency_code;
	} else {
		$amount = $currency_code.$amount;
	}
	return $amount;
}

/**
 * Filter called from mdid_replace_purchaseform() in idc-functions.php
 */
function mdid_set_form($content) {
	$member_level = absint($_GET['mdid_checkout']);
	if (isset($_GET['level'])) {
		$id_level = absint($_GET['level']);
		$owner = mdid_get_owner($member_level, $id_level);
		if (!empty($owner)) {
			// prevent WP from adding line breaks automatically
			remove_filter('the_content', 'wpautop');
			return do_shortcode('[idc_checkout product="'.$owner.'"]');
		}
	}
	return $content;
}

function id_idc_pwyw_price($price, $credit_value, $pwyw_price) {
	global $global_currency;
	// See if the global currency is credits, then use pwyw price instead of level
	if ($global_currency == "credits") {
		if ($pwyw_price > 0) {
			$price = $pwyw_price;
		}
		else {
			$price = $credit_value;
		}
	}
	return $price;
}
add_filter('id_idc_pwyw_price', 'id_idc_pwyw_price', 10, 3);

/**
 * Function to make the global currency position to right if it's virtual currency
 */
function idc_global_currency_position($position, $post_id) {
	global $global_currency;
	if ($global_currency == "credits") {
		$position = "right";
	}
	return $position;
}
add_filter('idc_currency_symbol_position', 'idc_global_currency_position', 10, 2);

function idc_level_name($level_name) {
	return stripslashes($level_name);
}

add_filter('idc_level_name', 'idc_level_name');

/**
 * Function for adding some arguments in levels dropdown in Support popup IDCF
 */
function id_idc_add_level_dropdown_arguments($level_data, $project_id) {
	if (function_exists('idf_platform')) {
		$platform = idf_platform();
	} else {
		$platform = '';
	}
	// Checking if IDC is enabled and selected as commerce platform
	if ($platform == "idc") {
		// First check that we have any levels coming in $level_data
		if (is_array($level_data)) {
			// Get the attached IDC product
			$project_assignments = get_assignments_by_project($project_id);
			// Now adding into $level_data array, the product id
			foreach ($project_assignments as $assignment) {
				$assignment_data = get_project_levels($assignment->assignment_id);
				$assignment_detail = maybe_unserialize($assignment_data->levels);
				$project_level = array_shift($assignment_detail);
				// Getting product data for level_type
				$product_data = ID_Member_Level::get_level($assignment->level_id);
				if (isset($level_data[$project_level - 1])) {
					$level_data[$project_level - 1]->idc_product_id = $assignment->level_id;
					$level_data[$project_level - 1]->idc_product_level_type = $product_data->level_type;
				}
			}
		}
	}
	return $level_data;
}
add_filter('idcf_dropdown_level', 'id_idc_add_level_dropdown_arguments', 10, 2);

/**
 * Function for adding attributes in <option> tags in dropdown for Project levels in _lbLevelSelect
 */
function id_idc_level_attributes($attributes, $level) {
	if (function_exists('idf_platform')) {
		$platform = idf_platform();
	} else {
		$platform = '';
	}
	// Adding few attributes from IDC if IDF commerce platform is IDC
	if ($platform == "idc" && isset($level->idc_product_id)) {
		$attributes .= 'data-idc-product="'.$level->idc_product_id.'" data-idc-level-type="'.$level->idc_product_level_type.'" ';
	}
	return $attributes;
}
add_filter('idcf_dropdown_option_attributes', 'id_idc_level_attributes', 10, 2);

/**
 * Filter to hide protected Projects on listing pages
 */
// function id_idc_hide_grid_protected_projects($content) {
function id_idc_hide_grid_protected_projects($query) {
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;

	// Check if the theme is IgnitionDeck
	$theme_name = wp_get_theme();
	$textdomain = $theme_name->get('Template');
	$is_ignitiondeck_theme = ($textdomain == 'fivehundred' || ($theme_name->get('Author') == 'Virtuous Giant' || $theme_name->get('Author') == 'IgnitionDeck')) && (isset($query->query['post_type']) && $query->query['post_type'] == "ignition_product" && isset($query->query['posts_per_page']) && isset($query->query['paged']));

	if ((is_archive() && $query->is_main_query()) || $is_ignitiondeck_theme) {
		$args = array(
			'post_type' => 'ignition_product',
			'post_status' => 'publish',
			'meta_key' => 'memberdeck_protected_posts',
			'meta_value' => '',
			'meta_compare' => '!='
		);
		// $protected_projects = ID_Member::get_protected_post_metas( 'memberdeck_protected_posts' );
		$protected_projects = get_posts( $args );

		// Looping these posts, and see which one is not to be shown to the user
		$post_not_allowed = array();
		for ($i=0 ; $i < count($protected_projects) ; $i++) { 
			$protected_project_id = $protected_projects[$i]->ID;
			$access_allowed = idc_user_allowed_post_access($protected_project_id, $user_id);
			// $access_allowed = false;
			ob_start();
			if ($access_allowed[0] == false) {
				echo $protected_project_id;
				array_push($post_not_allowed, $protected_project_id);
			}
			ob_end_clean();
		}

		if (count($post_not_allowed) > 0) {
			$query->query_vars['post__not_in'] = $post_not_allowed;
		}
	}
}
// add_filter('the_content', 'id_idc_hide_grid_protected_projects', 100);
add_action('pre_get_posts', 'id_idc_hide_grid_protected_projects', 30);

function idc_display_checkout_descriptions($content, $level, $level_price, $user_data, $settings, $general, $credit_value) {
	global $global_currency;
	// Getting the required variables for the Description template
	$customer_id = customer_id();

	$type = $level->level_type;
	$recurring = $level->recurring_type;
	$limit_term = $level->limit_term;
	$term_length = $level->term_length;
	$combined_product = $level->combined_product;
	// $credit_value = $level->credit_value;

	// If there is a combined product, check which active gateways allows recurring transactions
	if ($combined_product) {
		$combined_level = ID_Member_Level::get_level($combined_product);
		// Now see if any CreditCard gateway is active which supports recurring products, we just need to see if we have
		// to show that text or not in General text of different payment methods
		$combined_purchase_gateways = idc_combined_purchase_allowed($settings);
	} else {
		$combined_purchase_gateways = array();
	}

	$coname = $general['coname'];
	// Paypal currency
	$pp_currency = 'USD';
	if (!empty($settings)) {
		if (is_array($settings)) {
			$pp_currency = (isset($settings['pp_currency']) ? $settings['pp_currency'] : 'USD');
		}
	}
	$cc_currency_symbol = apply_filters('idc_cc_desc_currency_sym', '$', $settings);
	$cc_currency = apply_filters('idc_cc_desc_currency', 'USD', $settings);
	// Stripe currency
	$es = (isset($settings['es']) ? $settings['es'] : 0);
	$stripe_currency = 'USD';
	$stripe_symbol = '$';
	if (!empty($settings)) {
		if (is_array($settings)) {
			$stripe_currency = (isset($settings['stripe_currency']) ? $settings['stripe_currency'] : 'USD');
			$stripe_symbol = md_currency_symbol($stripe_currency);
		}
	}

	// Coinbase currency
	$ecb = (isset($settings['ecb']) ? $settings['ecb'] : '0');
	if ($ecb) {
		$cb_currency = (isset($settings['cb_currency']) ? $settings['cb_currency'] : 'BTC');
		$cb_symbol = md_currency_symbol($cb_currency);
	} else {
		$cb_currency = '';
		$cb_symbol = '';
	}

	// Global currency symbol
	if ($global_currency == "credits") {
		$global_currency_sym = '$';		//ucwords(apply_filters('idc_credits_label', __('credits', 'memberdeck'), true));
	} else {
		$global_currency_sym = md_currency_symbol($global_currency);
	}

	ob_start();
	include_once 'templates/_checkoutFreeDescription.php';
	$free_description = ob_get_contents();
	ob_clean();
	$content .= apply_filters('idc_free_checkout_description', $free_description, $level, $level_price, (isset($user_data) ? $user_data : ''), $settings, $general);
	
	ob_start();
	include_once 'templates/_checkoutPayPalDescription.php';
	$paypal_description = ob_get_contents();
	ob_clean();
	$content .= apply_filters('idc_paypal_checkout_description', $paypal_description, $level, $level_price, (isset($user_data) ? $user_data : ''), $settings, $general);
	
	ob_start();
	include_once 'templates/_checkoutCreditCardDescription.php';
	$credit_card_description = ob_get_contents();
	ob_clean();
	$content .= apply_filters('idc_credit_card_checkout_description', $credit_card_description, $level, $level_price, (isset($user_data) ? $user_data : ''), $settings, $general);
	
	ob_start();
	include_once 'templates/_checkoutCreditsDescription.php';
	$credits_description = ob_get_contents();
	ob_clean();
	$content .= apply_filters('idc_credits_checkout_description', $credits_description, $level, $level_price, (isset($user_data) ? $user_data : ''), $settings, $general);
	
	ob_start();
	include_once 'templates/_checkoutCoinbaseDescription.php';
	$coinbase_description = ob_get_contents();
	ob_clean();
	$content .= apply_filters('idc_coinbase_checkout_description', $coinbase_description, $level, $level_price, (isset($user_data) ? $user_data : ''), $settings, $general);
	
	ob_start();
	include_once 'templates/_checkoutOfflineDescription.php';
	$offline_description = ob_get_contents();
	ob_clean();
	$content .= apply_filters('idc_offline_checkout_description', $offline_description, $level, $level_price, (isset($user_data) ? $user_data : ''), $settings, $general);

	return $content;
}
add_filter('idc_checkout_descriptions', 'idc_display_checkout_descriptions', 10, 7);

/**
 * Filter for adding a selection menu to add IDC product using popup or select a product
 */
function idc_id_add_product_dropdown($meta_fields) {
	if (function_exists('idf_platform')) {
		$platform = idf_platform();
	} else {
		$platform = '';
	}
	// If Commerce platform is IDC, then add fields for adding IDC product directly from IDCF > Add Project page
	if ($platform == "idc") {
		// Getting IDC levels
		$levels = ID_Member_Level::get_levels();

		$fields = $meta_fields[0]['fields'];
		$new_fields = array();
		for ($i=0, $j=0; $i < count($fields) ; $i++, $j++) {
			// Copying the fields into the new_fields array
			$new_fields[$j] = $fields[$i];
			// If the field is ign_product_title, add after it, a select to add IDC product
			if ($fields[$i]['type'] == 'text' && $fields[$i]['id'] == 'ign_product_title') {
				$select_field = array(
					'name' => __('IDC Product', 'gbtech'),
					'id' => 'level_idc_product_1',
					'value' => 'level_idc_product_1',
					'type' => 'select',
					'class' => 'ign_idc_level_select',
					'options' => array(
						array(
							'name' => '--'.__('Select IDC Product', 'memberdeck').'--',
							'id' => 'open',
							'value' => ''
						),
						array(
							'name' => __('Add New Product', 'memberdeck'),
							'id' => 'closed',
							'value' => 'add_new_product'
						)
					)
				);
				// Adding the levels into options of select field
				foreach ($levels as $level) {
					array_push($select_field['options'], array(
						'name' => $level->level_name,
						'id' => $level->id,
						'value' => $level->id
					));
				}
				// Add the select field after project_title
				$j++;
				$new_fields[$j] = $select_field;

				// Adding a hidden field to store if level is selected from older ones or a new level is created
				$hidden_field = array(
					// 'name' => __('IDC Product', 'gbtech'),
					'id' => 'idc_product_selected_1',
					'value' => 'no',
					'type' => 'text',
					'class' => 'hidden ign_level_selected_option'
				);
				$j++;
				$new_fields[$j] = $hidden_field;
			}
		}
		$meta_fields[0]['fields'] = $new_fields;
	}
	return $meta_fields;
}
// add_filter('id_postmeta_boxes', 'idc_id_add_product_dropdown', 4, 1);

/**
 * Filter will return the currency symbol that could be used globally anywhere. It will check if only one gateway is active, then
 * show its currency, otherwise the global currency
 * @param string $symbol  The symbol already coming from IDCF
 * @param int 	 $post_id The post_id of the Project of IDCF
 * @return string $symbol The symbol to return
 */
function idc_global_currency_symbol($symbol, $post_id) {
	global $global_currency;

	// Getting active gateways from settings
	$settings = get_option('memberdeck_gateways');

	// If we have not settings stored in DB, return $ as currency symbol
	if (empty($settings)) {
		return '$';
	}
	// Commenting code below as we don't need it, will remove in the future releases
	//=================================================================================
	// $count_active = 0;
	// $active_gateways = array();

	// // Stripe
	// if (isset($settings['es']) && $settings['es'] == '1') {
	// 	array_push($active_gateways, 'stripe');
	// 	$count_active++;
	// }
	// // First data
	// if (isset($settings['efd']) && $settings['efd'] == '1') {
	// 	array_push($active_gateways, 'firstdata');
	// 	$count_active++;
	// }
	// // Authorize.Net
	// if (isset($settings['eauthnet']) && $settings['eauthnet'] == '1') {
	// 	array_push($active_gateways, 'authorize.net');
	// 	$count_active++;
	// }
	// // Coinbase
	// if (isset($settings['ecb']) && $settings['ecb'] == '1') {
	// 	array_push($active_gateways, 'coinbase');
	// 	$count_active++;
	// }
	// // Standard PayPal
	// if (isset($settings['epp']) && $settings['epp'] == '1') {
	// 	array_push($active_gateways, 'paypal');
	// 	$count_active++;
	// }
	// // PayPal Adaptive
	// if (isset($settings['eppadap']) && $settings['eppadap'] == '1') {
	// 	array_push($active_gateways, 'paypaladaptive');
	// 	$count_active++;
	// }
	// // Offline Checkout
	// if (isset($settings['manual_checkout']) && $settings['manual_checkout'] == '1') {
	// 	array_push($active_gateways, 'offline');
	// 	$count_active++;
	// }
	// $count_active = apply_filters('idc_active_gateways_count', $count_active, $settings);
	//=================================================================================

	// If count_active is greater than 1, then we will have to return global currency, else we get the currency of the gateway
	if (true) {		//$count_active > 1
		if ($global_currency == "credits") {
			$symbol = ucwords(apply_filters('idc_credits_label', __('Credits', 'memberdeck'), true));
		} else {
			$symbol = md_currency_symbol($global_currency);
		}
	} else {
		// Commenting code below as we don't need it, will remove in the future releases
		//=================================================================================
		// $active_gateways = apply_filters('idc_active_gateways_list', $active_gateways, $settings);
		// // Only one gateway is active, get which one is that
		// if (count($active_gateways) > 0) {
		// 	$gateway = array_keys($active_gateways);
		// 	// Depending on which gateway is active, returning the currency symbol
		// 	switch ($gateway[0]) {
		// 		case 'stripe':
		// 			$stripe_currency = (isset($settings['stripe_currency']) ? $settings['stripe_currency'] : '$');
		// 			$symbol = md_currency_symbol($stripe_currency);
		// 			break;
		// 		case 'firstdata':
		// 			$symbol = apply_filters('idc_cc_desc_currency_sym', '$', $settings);
		// 			break;
		// 		case 'authorize.net':
		// 			$symbol = apply_filters('idc_cc_desc_currency_sym', '$', $settings);
		// 			break;
		// 		case 'authorize.net':
		// 			$symbol = apply_filters('idc_cc_desc_currency_sym', '$', $settings);
		// 			break;
		// 		case 'coinbase':
		// 			$cb_currency = (isset($gateways['cb_currency']) ? $gateways['cb_currency'] : 'BTC');
		// 			$symbol = md_currency_symbol($cb_currency);
		// 			break;
		// 		case 'paypal':
		// 			$symbol = (isset($settings['pp_symbol']) ? $settings['pp_symbol'] : '$');
		// 			break;
		// 		case 'paypaladaptive':
		// 			$symbol = (isset($settings['pp_symbol']) ? $settings['pp_symbol'] : '$');
		// 			break;
				
		// 		default:
		// 			$symbol = '$';
		// 			break;
		// 	}
		// }
		//=================================================================================
	}

	return $symbol;
}

add_filter('idc_send_group_message_title', 'idc_custom_group_message_title'); 

function idc_custom_group_message_title($title) {
	if (isset($_GET['level'])) {
		$level_id = absint($_GET['level']);
		if ($level_id > 0) {
			$level = ID_Member_Level::get_level($level_id);
			if (!empty($level)) {
				$level_name = $level->level_name;
				$title = str_replace(' Group', ' '.$level_name.' '.__('Group', 'memberdeck'), $title);
			}
		}
	}
	return $title;
}

add_filter('idc_email_template_array', 'custom_product_to_template_array');

function custom_product_to_template_array($array) {
	$custom_products = ID_Member_Level::get_levels_by_custom('custom_message', 1);
	foreach ($custom_products as $custom_product) {
		$array['custom_product_message_'.$custom_product->id] = '';
	}
	return $array;
}

add_action('idc_email_template_option', 'idc_custom_product_message');

function idc_custom_product_message() {
	echo '<option name="custom_product_message">'.__('Custom Product Message', 'memberdeck').'</option>';
}

add_action('idc_after_email_template_option', 'idc_custom_product_message_dropdown');

function idc_custom_product_message_dropdown() {
	$dropdown = '';
	$dropdown .= '<div class="form-row third left">';
	$dropdown .= '<label for="custom_product_select">'. __('Select Product', 'memberdeck').'</label>';
	$dropdown .= '<select name="custom_product_select" id="custom_product_select">';
	$custom_products = ID_Member_Level::get_levels_by_custom('custom_message', 1);

	foreach ($custom_products as $custom_product) {
		$status = get_option('custom_product_message_status_'.$custom_product->id);
		$dropdown .= '<option name="registration_email" value="'.$custom_product->id.'" data-status="'.$status.'">'.$custom_product->level_name.'</option>';
	}
	$dropdown .= '</select>';
	$dropdown .= '</div>';
	$dropdown .= '<div class="form-row third custom_product_status">';
	$dropdown .= '<label for="custom_product_status">'. __('Message Status', 'memberdeck').'</label>';
	$dropdown .= '<select name="custom_product_status" id="custom_product_status">';
	$dropdown .= '<option value="draft">'.__('Draft', 'memberdeck').'</option>';
	$dropdown .= '<option value="publish">'.__('Published (Live)', 'memberdeck').'</option>';
	$dropdown .= '</select>';
	$dropdown .= '</div>';
	if (!empty($custom_products)) {
		echo $dropdown;
	}
}

add_action('admin_init', 'idc_save_custom_message_status');

function idc_save_custom_message_status() {
	if (isset($_POST['custom_product_status'])) {
		$status = sanitize_text_field($_POST['custom_product_status']);
		$product_id = absint($_POST['custom_product_select']);
		update_option('custom_product_message_status_'.$product_id, $status);
	}
}

add_action('idc_email_template', 'idc_custom_product_message_text');

function idc_custom_product_message_text() {
	$custom_products = ID_Member_Level::get_levels_by_custom('custom_message', 1);
	foreach ($custom_products as $custom_product) {
		$custom_message = null;
		$custom_message = stripslashes(get_option('custom_product_message_'.$custom_product->id));
		echo '<div class="form-row custom_product_message_'.$custom_product->id.'_text email_text custom_product_text" style="display: none">';
		wp_editor((isset($custom_message) ? $custom_message : ''), "custom_product_message_".$custom_product->id."_text", array('textarea_rows' => 20));
		echo '</div>';
	}
	return;
}

add_action('memberdeck_payment_success', 'idc_send_custom_product_message', 10, 5);
add_action('memberdeck_free_success', 'idc_send_custom_product_message', 10, 2);

function idc_send_custom_product_message($user_id, $order_id, $paykey = '', $fields = null, $source = '') {
	if ($order_id > 0) {
		$order = new ID_Member_Order($order_id);
		$the_order = $order->get_order();
		if (!empty($the_order)) {
			$level_id = $the_order->level_id;
			if ($level_id > 0) {
				$level = ID_Member_Level::get_level($level_id);
				if (!empty($level)) {
					$custom_message = $level->custom_message;
					if ($custom_message) {
						$message = get_option('custom_product_message_'.$level_id);
						$custom_message_status = get_option('custom_product_message_status_'.$level_id, 'draft');
						if (!empty($message) && $custom_message_status == 'publish') {
							// now we can get user data
							$user_id = $the_order->user_id;
							if ($user_id > 0) {
								$user = get_user_by('id', $user_id);
								if (!empty($user)) {
									$to = $user->user_email;
									$subject = __('Your', 'memberdeck').' '.$level->level_name.' '.__('Purchase', 'memberdeck');
									$mail = new ID_Member_Email($to, $subject, $message, $user_id);
									$mail->send_mail();
								}
							}
						}
					}
				}
			}
		}
	}
}

add_filter('idc_email_test_subject', 'idc_custom_product_message_subject');

function idc_custom_product_message_subject($subject) {
	$no_test = str_replace(__('Test', 'memberdeck'), '', $subject);
	$lowercase = strtolower($no_test);
	$trim = trim($lowercase);
	$underscores = str_replace(' ', '_', $trim);
	if (strpos($underscores, 'custom_product_message_') !== false) {
		$product_id = str_replace('custom_product_message_', '', $underscores);
	}
	if (isset($product_id)) {
		$level = new ID_Member_Level();
		$the_level = $level->get_level($product_id);
		if (!empty($the_level)) {
			$product_name = $the_level->level_name;
			$subject = str_replace($product_id, '['.$product_name.']', $subject);
		}
	}
	return $subject;
}
?>