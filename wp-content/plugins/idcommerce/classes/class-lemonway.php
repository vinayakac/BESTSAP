<?php
/**
 * LemonWay class for integration into IDC
 */
class ID_Lemon_Way {
	public static $lemonway_active = true;

	private $wlLogin;
	private $wlPass;
	private $myUrls;
	public static $wallet_id = '';
	public static $card_id = '';
	public static $preauth_txn_id = '';
	public static $project_creator = '';
	public static $insert_member = false;
	public static $payment_success = false;

	function __construct() {

	}

	function init() {
		// If Gateway is active, then add these function against action hooks/filters
		if (self::$lemonway_active) {
			$gateway_settings = get_option('memberdeck_gateways');

			// Admin Side hooks/filters attached functions
			add_action('wp_enqueue_scripts', array($this, 'lemonway_enqueue'), 10);
			add_action('admin_enqueue_scripts', array($this, 'lemonway_admin_enqueue'), 10);
			add_action('idc_admin_menu_after', array($this, 'lemonway_admin_menu'), 10);
			// add_action('idc_gateway_columns_after', array($this, 'lemonway_admin_settings'), 10);
			// add_filter('idc_gateway_settings', array($this, 'lemonway_store_settings'), 10, 2);
			add_filter('idc_admin_menu_after', array($this, 'lemonway_connect_menu'), 10);
			// Only call these hooks if LemonWay is active gateway for credit card
			if (isset($gateway_settings['elw']) && $gateway_settings['elw'] == '1') {
				add_action('admin_notices', array($this, 'lemonway_notice_creator_not_approved'), 10);

				// Front-end side hooks/filters attached functions
				add_action('idc_after_credit_card_selectors', array($this, 'lemonway_selector_checkout'), 10, 1);
				add_filter('idc_creator_settings_enabled', array($this, 'lemonway_creator_settings'), 10, 1);
				add_action('md_payment_settings_extrafields', array($this, 'lemonway_creator_connect_box'), 10);
				add_action('init', array($this, 'lemonway_store_creator_settings'), 10);
				add_action('md_purchase_extrafields', array($this, 'lemonway_card_type_selection'), 10);
				add_filter('idc_cc_desc_currency', array($this, 'lemonway_currency'), 10, 2);
				add_filter('idc_cc_desc_currency_sym', array($this, 'lemonway_currency_symbol'), 10, 2);
				add_filter('idc_order_currency', array($this, 'lemonway_order_currency'), 10, 3);
				add_action('idc_daily_event', array($this, 'lemonway_check_creators_kyc'), 10);
				add_filter('idc_checkout_form_customer_id', array($this, 'lemonway_customer_card_id'), 10, 3);
				// add_action('init', array($this, 'lemonway_test_function'), 10);

				// 3D secure functions
				add_action('wp_ajax_idc_lemonway_checkout_method_details', array($this, 'lemonway_get_required_method'), 10);
				add_action('wp_ajax_nopriv_idc_lemonway_checkout_method_details', array($this, 'lemonway_get_required_method'), 10);
				add_action('init', array($this, 'lemonway_payment_sucessful'), 10);
				add_filter('idc_last_order_lightbox', array($this, 'lemonway_get_order_creating'), 10, 1);

				// For Non Secure 3D (filters/action hooks) called in idmember_create_customer()
				add_filter('idc_customer_id_checkout', array($this, 'lemonway_get_customer_id'), 10, 4);
				add_filter('idc_create_customer_checkout', array($this, 'lemonway_create_wallet'), 10, 3);
				add_filter('idc_new_customer_card_check_checkout', array($this, 'lemonway_check_card_exists'), 10, 3);
				add_filter('idc_charge_using_token_checkout', array($this, 'lemonway_charge_stored_card'), 10, 3);
				add_filter('idc_charge_without_token_checkout', array($this, 'lemonway_charge_stored_card'), 10, 3);
				add_filter('idc_user_update_checkout', array($this, 'lemonway_update_user_and_meta'), 10, 2);
				add_filter('idc_preorder_charge_token', array($this, 'lemonway_preorder_charge_token'), 10, 3);
				add_filter('idc_instant_checkout', array($this, 'lemonway_instant_checkout'), 10, 3);
				add_filter('idc_member_insert_checkout', array($this, 'lemonway_get_insert_member'), 10, 3);
				add_filter('idc_checkout_success', array($this, 'lemonway_non3ds_success'), 10, 3);
	
				// For Preauth Processing
				add_filter('idc_preauth_customer_id', array($this, 'lemonway_get_customer_id'), 10, 3);
				add_filter('idc_preauth_paid', array($this, 'lemonway_validate_preauth'), 10, 6);
				add_filter('idc_preauth_paid_transaction', array($this, 'lemonway_preauth_txn_id'), 10, 5);
			}

			// Setting $myUrls
			$this->myUrls = array(
				'returnUrl' => home_url('/').'?idc_lemonway=1&status=success',
				'cancelUrl' => home_url('/').'?idc_lemonway=1&status=cancel',
				'errorUrl' => home_url('/').'?idc_lemonway=1&status=error',
				// 'cssUrl' => 'https://www.lemonway.fr/mercanet_lw.css'
			);
		}
	}

	/**
	 * Function to get the fee in case user is creator and project is mdid
	 */
	function lemonway_get_creator_account($extra_fields, $amount, $settings, $source) {
		$fee = 0.00;
		$wallet_id_receiver = '';

		// If creator settings are enabled from Admin, then go further, else just return
		if (isset($settings['lemonway_creators']) && $settings['lemonway_creators'] == '1') {
			foreach ($extra_fields as $field) {
				// If it's mdid_checkout
				if ($field['name'] == "mdid_checkout" && $field['value'] == "1") {
					$mdid_checkout = true;
				}
				if ($field['name'] == "project_id") {
					$project_id = $field['value'];
				}
			}
			// If $mdid_checkout is true, then get Creator id of Project
			if (isset($mdid_checkout) && $mdid_checkout && isset($project_id)) {
				$idcf_project = new ID_Project($project_id);
				$post_id = $idcf_project->get_project_postid();
	
				// Getting post for Author
				$post = get_post($post_id);
				$author_id = $post->post_author;
	
				// Now check if creator has connected Wallet, if yes, then get his wallet id, otherwise use default wallet_id
				$connect_settings = get_user_meta($author_id, 'idc_lemonway_creator_wallet', true);
				if (!empty($connect_settings) && isset($connect_settings['lemonway_connect']) && $connect_settings['lemonway_connect'] == '1') {
					// Check that KYC2 status is active as well
					$kyc_approved = get_user_meta($author_id, 'idc_lemonway_kyc_approved', true);
					if ($kyc_approved == "1") {
						$wallet_id_receiver = $connect_settings['wallet_id'];

						// Calculating fee if there is any
						$fee_settings = get_option('idc_lemonway_connect_settings');
						if (!empty($fee_settings)) {
							$app_fee = $fee_settings['app_fee'];
							$fee_type = $fee_settings['fee_type'];
							$fee = apply_filters('idc_fee_amount', $app_fee, $amount, $fee_type, $source);
						}
					}
				}
			}
		}
		// Formatting fee
		$fee = number_format($fee, 2, '.', '');
		return array("fee" => $fee, "wallet_id" => $wallet_id_receiver, "creator_id" => (isset($author_id) ? $author_id : ''));
	}

	/****************************************************************************************************************
	 * Admin filters and actions
	 ****************************************************************************************************************/
	/**
	 * Enqueue scripts
	 */
	public function lemonway_enqueue() {
		wp_register_script('lemonway-js', plugins_url('/../js/lemonway.js', __FILE__), array('idcommerce-js'));
		wp_enqueue_script('lemonway-js');
	}

	public function lemonway_admin_enqueue() {
		wp_register_script('lemonway-js-admin', plugins_url('/../js/lemonway-admin.js', __FILE__), array('idcommerce-admin-js'));
		wp_enqueue_script('lemonway-js-admin');
	}

	/**
	 * Creates Random number for Wallet ID
	 */
	private function getRandomId(){
		return str_replace('.', '', microtime(true).rand());
	}

	/**
	 * Action function for adding LemonWay menu if Lemonway is enabled
	 */
	public function lemonway_admin_menu() {
		$lw_menu = add_submenu_page('idc', __('Lemon Way', 'memberdeck'), __('Lemon Way', 'memberdeck'), 'idc_manage_gateways', 'idc-lemonway-settings', array($this, 'lemonway_admin_settings'));
	}

	/**
	 * Action function for adding Lemon Way settings in Admin > IDC > Gateway Settings
	 */
	public function lemonway_admin_settings() {
		$gateway_settings = get_option('memberdeck_gateways');

		// If form is posted to save lemonway settings
		if (isset($_POST['lemonway_save_settings'])) {
			$gateway_settings = $this->lemonway_store_settings($gateway_settings, $_POST);
		}

		// Storing into variables
		$elw = (isset($gateway_settings['elw']) ? $gateway_settings['elw'] : '');
		$es = (isset($gateway_settings['es']) ? $gateway_settings['es'] : '');
		$eauthnet = (isset($gateway_settings['eauthnet']) ? $gateway_settings['eauthnet'] : '');
		$lemonway_test = (isset($gateway_settings['lemonway_test']) ? $gateway_settings['lemonway_test'] : '');

		// Live credentials
		$lemonway_login = (isset($gateway_settings['lemonway_login']) ? $gateway_settings['lemonway_login'] : '');
		$lemonway_pass = (isset($gateway_settings['lemonway_pass']) ? $gateway_settings['lemonway_pass'] : '');
		$lemonway_directkit_url = (isset($gateway_settings['lemonway_directkit_url']) ? $gateway_settings['lemonway_directkit_url'] : '');
		$lemonway_webkit_url = (isset($gateway_settings['lemonway_webkit_url']) ? $gateway_settings['lemonway_webkit_url'] : '');
		// Test credentials
		$lemonway_login_test = (isset($gateway_settings['lemonway_login_test']) ? $gateway_settings['lemonway_login_test'] : '');
		$lemonway_pass_test = (isset($gateway_settings['lemonway_pass_test']) ? $gateway_settings['lemonway_pass_test'] : '');
		$lemonway_directkit_url_test = (isset($gateway_settings['lemonway_directkit_url_test']) ? $gateway_settings['lemonway_directkit_url_test'] : '');
		$lemonway_webkit_url_test = (isset($gateway_settings['lemonway_webkit_url_test']) ? $gateway_settings['lemonway_webkit_url_test'] : '');
		// Others
		$lemonway_3ds_enabled = (isset($gateway_settings['lemonway_3ds_enabled']) ? $gateway_settings['lemonway_3ds_enabled'] : '');
		$lemonway_wallet_id = (isset($gateway_settings['lemonway_wallet_id']) ? $gateway_settings['lemonway_wallet_id'] : '');
		$lemonway_creators = (isset($gateway_settings['lemonway_creators']) ? $gateway_settings['lemonway_creators'] : '');

		$image_url = plugins_url('/images/lemonway.png', dirname(__file__));
		?>
		<div class="postbox-container memberdeck" style="width:50%; float:none;">
			<div class="metabox-holder">
				<div style="min-height:0;" class="meta-box-sortables">
					<form method="post" id="form_lemonway" name="form_lemonway" action="">
						<div class="postbox">
							<h3 class="hndle"><span><?php _e('Lemon Way Settings', 'memberdeck') ?></span>
								<div class="form-input" style="float: right; text-transform: none;">
									<input type="checkbox" name="test" id="test" value="1" <?php echo (($lemonway_test == '1') ? 'checked="checked"' : ''); ?> />
									<label for="test"><?php _e('Test Mode', 'memberdeck'); ?></label>
								</div>
							</h3>
							<div class="inside">
								<p>
									<img src="<?php echo $image_url; ?>" style="width: 100px;">
								</p>
								<div class="form-input lemonway-live">
									<label for="lemonway_login"><?php _e('Login', 'memberdeck'); ?></label>
									<input type="text" name="lemonway_login" id="lemonway_login" value="<?php echo (isset($lemonway_login) ? $lemonway_login : ''); ?>"/>
								</div>
								<div class="form-input lemonway-test">
									<label for="lemonway_login_test"><?php _e('Login (Sandbox)', 'memberdeck'); ?></label>
									<input type="text" name="lemonway_login_test" id="lemonway_login_test" value="<?php echo (isset($lemonway_login_test) ? $lemonway_login_test : ''); ?>"/>
								</div>
								<div class="form-input lemonway-live">
									<label for="lemonway_pass"><?php _e('Password', 'memberdeck'); ?></label>
									<input type="text" name="lemonway_pass" id="lemonway_pass" value="<?php echo (isset($lemonway_pass) ? $lemonway_pass : ''); ?>"/>
								</div>
								<div class="form-input lemonway-test">
									<label for="lemonway_pass_test"><?php _e('Password (Sandbox)', 'memberdeck'); ?></label>
									<input type="text" name="lemonway_pass_test" id="lemonway_pass_test" value="<?php echo (isset($lemonway_pass_test) ? $lemonway_pass_test : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="lemonway_wallet_id"><?php _e('Wallet ID', 'memberdeck'); ?></label>
									<input type="text" name="lemonway_wallet_id" id="lemonway_wallet_id" value="<?php echo (isset($lemonway_wallet_id) ? $lemonway_wallet_id : ''); ?>"/>
								</div>
								<div class="form-input lemonway-live">
									<label for="lemonway_directkit_url"><?php _e('DirectKit URL', 'memberdeck'); ?></label>
									<input type="text" name="lemonway_directkit_url" id="lemonway_directkit_url" value="<?php echo (isset($lemonway_directkit_url) ? $lemonway_directkit_url : ''); ?>"/>
								</div>
								<div class="form-input lemonway-test">
									<label for="lemonway_directkit_url_test"><?php _e('DirectKit URL (Sandbox)', 'memberdeck'); ?></label>
									<input type="text" name="lemonway_directkit_url_test" id="lemonway_directkit_url_test" value="<?php echo (isset($lemonway_directkit_url_test) ? $lemonway_directkit_url_test : ''); ?>"/>
								</div>
								<div class="form-input lemonway-live">
									<label for="lemonway_webkit_url"><?php _e('WebKit URL', 'memberdeck'); ?></label>
									<input type="text" name="lemonway_webkit_url" id="lemonway_webkit_url" value="<?php echo (isset($lemonway_webkit_url) ? $lemonway_webkit_url : ''); ?>"/>
								</div>
								<div class="form-input lemonway-test">
									<label for="lemonway_webkit_url_test"><?php _e('WebKit URL (Sandbox)', 'memberdeck'); ?></label>
									<input type="text" name="lemonway_webkit_url_test" id="lemonway_webkit_url_test" value="<?php echo (isset($lemonway_webkit_url_test) ? $lemonway_webkit_url_test : ''); ?>"/>
								</div>
								<br/>
								<div class="form-input inline">
									<input type="checkbox" name="elw" id="elw" value="1" class="cc-gateway-chkbox" siblings="lemonway" <?php echo (isset($elw) && $elw == 1 ? 'checked="checked"' : ''); ?> <?php echo ((isset($eauthnet) && $eauthnet == 1) || (isset($es) && $es == 1) ? 'disabled="disabled"' : ''); ?>/>
									<label for="elw"><?php _e('Enable Lemon Way', 'memberdeck'); ?></label>
								</div>
								<br/>
								<div class="form-input inline">
									<input type="checkbox" name="lemonway_3ds_enabled" id="lemonway_3ds_enabled" value="1" class="cc-gateway-chkbox" siblings="lemonway" <?php echo ($lemonway_3ds_enabled == "1" ? 'checked="checked"' : ''); ?> />
									<label for="lemonway_3ds_enabled"><?php _e('Enable 3D Secure', 'memberdeck'); ?></label>
								</div>
								<br/>
								<div class="form-input inline">
									<input type="checkbox" name="lemonway_creators" id="lemonway_creators" value="1" class="cc-gateway-chkbox" siblings="lemonway" <?php echo ($lemonway_creators == "1" ? 'checked="checked"' : ''); ?> />
									<label for="lemonway_creators"><?php _e('Enable for Creators', 'memberdeck'); ?></label>
								</div>
								<div class="submit">
									<button class="button button-primary" name="lemonway_save_settings" id="lemonway_save_settings"><?php _e('Save', 'memberdeck') ?></button>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Filter Function for Storing Lemon Way admin settings from Admin > IDC > Gateway settings
	 */
	public function lemonway_store_settings($settings, $posted_data) {
		// Live credentials/settings
		$settings['lemonway_login'] = sanitize_text_field($posted_data['lemonway_login']);
		$settings['lemonway_pass'] = sanitize_text_field($posted_data['lemonway_pass']);
		$settings['lemonway_directkit_url'] = sanitize_text_field($posted_data['lemonway_directkit_url']);
		$settings['lemonway_webkit_url'] = sanitize_text_field($posted_data['lemonway_webkit_url']);
		// Test credentials/settings
		$settings['lemonway_login_test'] = sanitize_text_field($posted_data['lemonway_login_test']);
		$settings['lemonway_pass_test'] = sanitize_text_field($posted_data['lemonway_pass_test']);
		$settings['lemonway_directkit_url_test'] = sanitize_text_field($posted_data['lemonway_directkit_url_test']);
		$settings['lemonway_webkit_url_test'] = sanitize_text_field($posted_data['lemonway_webkit_url_test']);
		// Storing others options not based on modes (Test or Live)
		$settings['lemonway_wallet_id'] = sanitize_text_field($posted_data['lemonway_wallet_id']);
		$settings['lemonway_3ds_enabled'] = (isset($posted_data['lemonway_3ds_enabled']) ? '1' : '0');
		$settings['lemonway_creators'] = (isset($posted_data['lemonway_creators']) ? '1' : '0');
		$settings['lemonway_test'] = (isset($posted_data['test']) ? '1' : '0');
		if (isset($posted_data['elw'])) {
			$settings['elw'] = absint($posted_data['elw']);
		}
		// Updating gateways option
		update_option('memberdeck_gateways', $settings);

		return $settings;
	}

	/**
	 * Action to add admin menu for LemonWay connect
	 */
	public function lemonway_connect_menu() {
		if (self::$lemonway_active) {
			$gateway_settings = get_option('memberdeck_gateways');

			// If Lemonway is enabled
			if (isset($gateway_settings['elw']) && $gateway_settings['elw'] == '1') {
				// If settings for creator enabled
				if (isset($gateway_settings['lemonway_creators']) && $gateway_settings['lemonway_creators'] == '1') {
					$sc_menu = add_submenu_page('idc', __('LemonWay Connect Settings', 'memberdeck'), __('LemonWay Connect Settings', 'memberdeck'), 'idc_manage_gateways', 'idc-lemonway-connect', array($this, 'idc_lemonway_connect_settings'));
				}
			}
		}
	}

	/**
	 * LemonWay connect settings
	 */
	public function idc_lemonway_connect_settings() {
		$fee_type = 'flat';
		$app_fee = 0;
		$lemonway_settings = get_option('idc_lemonway_connect_settings');

		// If fee settings are submitted
		if (isset($_POST['lemonway_connect_submit'])) {
			$fee_type = sanitize_text_field($_POST['fee_type']);
			$app_fee = sanitize_text_field($_POST['app_fee']);
			$lemonway_settings = array(
				'fee_type' => $fee_type,
				'app_fee' => $app_fee,
			);
			update_option('idc_lemonway_connect_settings', $lemonway_settings);
		}
		require dirname(__file__).'/../templates/admin/_lemonwayConnect.php';
	}

	/**
	 * Showing notification in Admin that User is not connected in LemonWay when admin edits the project
	 */
	function lemonway_notice_creator_not_approved() {
		global $post;
		global $pagenow;
		$page_array = array('post.php', 'post-new.php');
		if (is_admin()) {
			if (isset($post) && $post->post_type == 'ignition_product') {
				if (in_array($pagenow, $page_array)) {
					// Check that user (post author) is connected to LemonWay
					$author_id = $post->post_author;
					$connect_settings = get_user_meta($author_id, 'idc_lemonway_creator_wallet', true);
					if (!empty($connect_settings) && isset($connect_settings['lemonway_connect']) && $connect_settings['lemonway_connect'] == '1') {
						// User is connected, now check KYC status
						$creator_kyc_status = get_user_meta($author_id, 'idc_lemonway_kyc_approved', true);
						if (!empty($creator_kyc_status) && $creator_kyc_status == '1') {
							$notify_on_edit = false;
						} else {
							$notify_on_edit = true;
						}
					}
					if (isset($notify_on_edit) && $notify_on_edit) {
						?>
						<div class="error"> <p><font size="+1"><b><?php _e( 'Notice', 'memberdeck' ); ?>: </b><?php _e( 'Project creator has not connected to Lemon Way yet, or not approved by Lemon Way as Creator', 'memberdeck' ); ?></font>
						</p> </div>
						<?php
					}
				}
			}
		}
	}

	/****************************************************************************************************************
	 * Front-end filters and actions
	 ****************************************************************************************************************/
	/**
	 * Action function to show Lemonway selector
	 */
	public function lemonway_selector_checkout($gateway_settings) {
		?>
		<div><a id="pay-with-lemonway" class="pay_selector" href="#">
			<i class="fa fa-credit-card"></i>
			<span><?php _e('Credit Card', 'memberdeck'); ?></span>
		</a></div>
		<?php
	}

	/**
	 * Filter function to show payments/creator settings if lemonway is active and creator settings enabled
	 */
	public function lemonway_creator_settings($enabled) {
		$gateway_settings = get_option('memberdeck_gateways');
		if (isset($gateway_settings['lemonway_creators']) && $gateway_settings['lemonway_creators'] == '1') {
			array_push($enabled, 'lemonway');
		}

		return $enabled;
	}

	/**
	 * Action to add LemonWay connect button on Creator Settings tab
	 */
	public function lemonway_creator_connect_box() {
		if (is_user_logged_in()) {
			$gateway_settings = get_option('memberdeck_gateways');

			// If LemonWay is enabled for Creators
			if (isset($gateway_settings['lemonway_creators']) && $gateway_settings['lemonway_creators'] == '1') {
				$current_user = wp_get_current_user();
				$user_id = $current_user->ID;
	
				$lemonway_connect = '';
				// Getting creator wallet settings to show that user is creator and connected
				$connect_settings = get_user_meta($user_id, 'idc_lemonway_creator_wallet', true);
				if (!empty($connect_settings)) {
					if (isset($connect_settings['lemonway_connect']) && $connect_settings['lemonway_connect'] == '1') {
						$lemonway_connect = '1';
					}
				}
	
				include_once dirname(__file__).'/../templates/_lemonwayConnectBox.php';
			}
		}
	}

	/**
	 * Create a creator wallet on LemonWay end with all KYC documents to be uploaded
	 */
	public function lemonway_store_creator_settings() {
		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;

			// If Creator settings posted
			if (isset($_POST['creator_settings_submit'])) {
				// If LemonWay Connect checkbox is checked
				if (isset($_POST['lemonway_connect'])) {
					$connect_settings = get_user_meta($user_id, 'idc_lemonway_creator_wallet', true);
					global $user_email;
	
					// If user is already connected, then no need to perform functions below
					if (empty($connect_settings) || !isset($connect_settings['lemonway_connect'])) {
						// Getting gateway settings
						$gateway_settings = get_option('memberdeck_gateways');
						require dirname(__file__).'/../lib/LemonWay/LemonWayIncludes.php';
						$test = (($gateway_settings['lemonway_test'] == '1') ? true : false);
						$lemonwayKit = new LemonWayKit($gateway_settings, $test);
		
						// Create a Wallet for this user and store id in meta
						// Random number for Wallet ID
						$wallet = $this->getRandomId();
						$res = $lemonwayKit->RegisterWallet(array(
							'wallet' => $wallet, 
							'clientMail' => $user_email,
							'clientTitle' => 'Ms.',
							'clientFirstName' => $current_user->user_firstname, 
							'clientLastName' => $current_user->user_lastname
						));
						if (isset($res->lwError)) {
							// Error occurred while creating wallet, return error response, but check if it's user already exists code
							if ($res->lwError->CODE == "204") {
								// User already exists with this email
								$wallet_details = $lemonwayKit->GetWalletDetails(array(
									"email" => $user_email
								));
								if (isset($wallet_details->lwXml->WALLET->ID)) {
									$wallet_id = (string) $wallet_details->lwXml->WALLET->ID;
								} else {
									$wallet_id = null;
								}
							} else {
								$ret_response = json_encode( array("response" => "error", "message" => $res->lwError->CODE.": ".$res->lwError->MSG, "line" => __line__) );
								echo $ret_response;
								exit();
							}
						} else {
							$wallet_id = $res->wallet->ID;
						}
		
						// If we are getting wallet_id, save it in creator settings
						if (!empty($wallet_id)) {
							$connect_settings = array('lemonway_connect' => 1, 'wallet_id' => (string) $wallet_id);
							// Adding this wallet id to user meta
							update_user_meta($user_id, 'idc_lemonway_creator_wallet', $connect_settings);
							update_user_meta($user_id, 'idc_lemonway_kyc_approved', '0');
							update_user_meta($user_id, 'idc_lemonway_creator_connection_timestamp', time());
							// $payment_settings['lemonway_connect'] = 1;
						}
					}
				}
				// Remove connection to LemonWay, and creator settings
				else {
					delete_user_meta($user_id, 'idc_lemonway_creator_wallet');
				}
			}

			// KYC Document uploading to Wallet of creator
			if (isset($_POST['kyc_document_uploaded']) && $_POST['kyc_document_uploaded'] == "yes") {
				// If we have conect settings of Creator with lemonWay, go ahead with uploading the document
				$connect_settings = get_user_meta($user_id, 'idc_lemonway_creator_wallet', true);
				$durl = md_get_durl(is_ssl());
				if (function_exists('idf_get_querystring_prefix')) {
					$prefix = idf_get_querystring_prefix();
				} else {
					$prefix = '?';
				}

				if (!empty($connect_settings)) {
					// If file size is larger than zero
					$uploaded = false;
					if (isset($_FILES['kyc_document']) && $_FILES['kyc_document']['size'] > 0) {
						// if file size is less than or equal to 4MB
						if ($_FILES['kyc_document']['size'] <= 4000000) {
							if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
							
							$file_name = $_FILES['kyc_document']['name'];
							$wp_upload_dir = wp_upload_dir();
							$kyc_document_handle = wp_handle_upload($_FILES['kyc_document'], array('test_form' => false));
							$kyc_file_type = wp_check_filetype(basename($kyc_document_handle['file']), null);
	
							// Allowed file types are: PDF, JPG, JPEG, BMP, GIF, TIF, TIFF et PNG
							if ($kyc_file_type['ext'] == 'pdf' || $kyc_file_type['ext'] == 'jpg' || $kyc_file_type['ext'] == 'gif' || $kyc_file_type['ext'] == 'jpeg' || $kyc_file_type['ext'] == 'bmp' || $kyc_file_type['ext'] == 'tif' || $kyc_file_type['ext'] == 'tiff' || $kyc_file_type['ext'] == 'png') {
								$kyc_attachment = array(
							    	'guid' => $wp_upload_dir['url'] . '/' . basename( $kyc_document_handle['file'] ), 
							    	'post_mime_type' => $kyc_file_type['type'],
							    	'post_title' => preg_replace('/\.[^.]+$/', '', basename($kyc_document_handle['file'])),
							    	'post_content' => '',
							    	'post_status' => 'inherit'
							  	);
							  	$uploaded = true;
							}
							else {
								echo "<script>window.location = \"".$durl.$prefix."payment_settings=1&kyc_status=error&msg=".urlencode(__('The document is not in the correct format. Allowed formats are: PDF, JPG, JPEG, BMP, GIF, TIF, TIFF and PNG', 'memberdeck'))."\"; </script>";
							}
						}
						else {
							echo "<script>window.location = \"".$durl.$prefix."payment_settings=1&kyc_status=error&msg=".urlencode(__('File size must be less than 4MB', 'memberdeck'))."\"; </script>";
						}
					}
					// If file uploaded to our server, let's send it to LemonWay server
					if ($uploaded) {
						// If we have not initialized Lemonway variable before
						if (!isset($lemonwayKit)) {
							$gateway_settings = get_option('memberdeck_gateways');
	
							require dirname(__file__).'/../lib/LemonWay/LemonWayIncludes.php';

							$test = (($gateway_settings['lemonway_test'] == '1') ? true : false);
							$lemonwayKit = new LemonWayKit($gateway_settings, $test);
						}
						// UploadFile
						$file = file_get_contents($kyc_document_handle['file'], true);
						$buffer = base64_encode($file);
						$upload_res = $lemonwayKit->UploadFile(array(
							'wallet' => $connect_settings['wallet_id'],
							'fileName' => $file_name,
							'type' => sanitize_text_field($_POST['kyc_document_type']),
							'buffer'=> $buffer
						));

						if (isset($upload_res->lwError)) {
							// Document type already exists
							if ((string) $upload_res->lwError->CODE == '241') {
								echo "<script>window.location = \"".$durl.$prefix."payment_settings=1&kyc_status=success&msg=" . urlencode((string) $upload_res->lwError->MSG )."\"; </script>";
								// wp_redirect( $durl.$prefix."payment_settings=1&kyc_status=success&msg=" . urlencode((string) $upload_res->lwError->MSG ));
							} else {
								echo "<script>window.location = \"".$durl.$prefix."payment_settings=1&kyc_status=error&msg=" . urlencode((string) $upload_res->lwError->MSG)."\"; </script>";
								// wp_redirect( $durl.$prefix."payment_settings=1&kyc_status=error&msg=" . urlencode((string) $upload_res->lwError->MSG) );
							}
						} else {
							$file_id = (string) $upload_res->lwXml->UPLOAD->ID;
							$document_type = sanitize_text_field($_POST['kyc_document_type']);
							$creator_kyc_meta = get_user_meta($user_id, 'idc_lemonway_kyc_documents', true);
							if (empty($creator_kyc_meta)) {
								$creator_kyc_meta = array($document_type => $file_id, "status" => "1");
							} else {
								$creator_kyc_meta[$document_type] = $file_id;
								$creator_kyc_meta['status'] = '1';
							}
							update_user_meta($user_id, 'idc_lemonway_kyc_documents', $creator_kyc_meta);

							echo "<script>window.location = \"".$durl.$prefix."payment_settings=1&kyc_status=success\"; </script>";
							// wp_redirect( $durl.$prefix."payment_settings=1&kyc_status=success" );
						}
					}
				}
			}
		}
	}

	/**
	 * Action to add card type on the checkout page
	 */
	function lemonway_card_type_selection() {
		include_once dirname(__file__).'/../templates/_lemonwayCardTypes.php';
	}

	/**
	 * Filter function to return the currency lemonway uses
	 */
	function lemonway_currency($currency_code, $settings) {
		$currency_code = 'EUR';

		return $currency_code;
	}

	/**
	 * Function to return the currency symbol for Lemonway
	 */
	function lemonway_currency_symbol($currency_symbol, $settings) {
		$currency_symbol = '€';
		
		return $currency_symbol;
	}

	/**
	 * Filter function to return the LemonWay currenct currency
	 */
	public function lemonway_order_currency($currency_code, $global_currency, $source) {
		// If gateway is LemonWay
		if ($source == "lemonway") {
			$currency_code = 'EUR';
		}

		return $currency_code;
	}

	/**
	 * Daily event to check that creators who are not approved as creator yet are approved or not in LemonWay
	 * The creators will be those who are already connected and not approved
	 */
	public function lemonway_check_creators_kyc() {
		// Getting the users connected but not approved, check whether they are approved now.
		$query_args = array(
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'idc_lemonway_creator_wallet',
					'value' => '',
					'compare' => '!='
				),
				array(
					'key' => 'idc_lemonway_kyc_approved',
					'value' => '0'
				)
			)
		);
		$user_query = new WP_User_Query( $query_args );
		if (!empty( $user_query->results )) {
			// Getting Wallets registered after the user connection date
			$gateway_settings = get_option('memberdeck_gateways');
			require dirname(__file__).'/../lib/LemonWay/LemonWayIncludes.php';
			$test = (($gateway_settings['lemonway_test'] == '1') ? true : false);
			$lemonwayKit = new LemonWayKit($gateway_settings, $test);

			foreach($user_query->results as $user) {
				// echo '<p>ID: '.$user->ID.', User Name: '. $user->display_name . '</p>';
				// Getting user's Wallet
				$creator_wallet = get_user_meta($user->ID, 'idc_lemonway_creator_wallet', true);
				$wallet_id = $creator_wallet['wallet_id'];

				// Getting the timestamp in which document for KYC approval was uploaded so get wallets whose status was
				// changed after that timestamp
				$timestamp_creation = get_user_meta($user->ID, 'idc_lemonway_creator_connection_timestamp', true);
				$kyc_status = $lemonwayKit->GetKycStatus(array(
					'updateDate' => $timestamp_creation
				));

				// If there are no errors and we have some Wallets, then check which one is this user's (if any returned from
				// Lemon Way)
				if (!isset($kyc_status->lwError)) {
					$wallets = $kyc_status->lwXml->WALLETS->WALLET;
	
					// Check that user wallet is approved by traversing wallets array
					foreach ($wallets as $wallet) {
						// If wallet_id in loop is creator's Wallet_id, then check what's its status
						if ((string) $wallet->ID == $wallet_id) {
							// if status is equal to 6, then account is approved to KYC2 status
							if ((string) $wallet->S == "6") {
								update_user_meta($user->ID, 'idc_lemonway_kyc_approved', '1');
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Action test
	 */
	public function lemonway_test_function() {
		$gateway_settings = get_option('memberdeck_gateways');

		require dirname(__file__).'/../lib/LemonWay/LemonWayIncludes.php';

		$test = (($gateway_settings['lemonway_test'] == '1') ? true : false);
		$lemonwayKit = new LemonWayKit($gateway_settings, $test);

		$kyc_status = $lemonwayKit->GetKycStatus(array(
			'walletId' => '1443614296674113382',
			'updateDate' => strtotime("-1 year")
		));

		echo "<pre>kyc_status: ";
		print_r($kyc_status);
		echo "</pre>"; exit();

		// // From email, get his Wallet ID, and then check if card exists in his wallet
		// $wallet_details = $lemonwayKit->GetWalletDetails(array(
		// 	"email" => "karljl007@gmail.com"
		// ));
		// echo "<pre>wallet_details: ";
		// print_r($wallet_details);
		// echo "</pre>";

		// // Getting the users connected but not approved, check whether they are approved now.
		// $query_args = array(
		// 	'meta_query' => array(
		// 		'relation' => 'AND',
		// 		array(
		// 			'key' => 'idc_lemonway_creator_wallet',
		// 			'value' => '',
		// 			'compare' => '!='
		// 		),
		// 		array(
		// 			'key' => 'idc_lemonway_kyc_approved',
		// 			'value' => '0'
		// 		)
		// 	)
		// );
		// $user_query = new WP_User_Query( $query_args );
		// if (!empty( $user_query->results )) {
		// 	foreach($user_query->results as $user) {
		// 		// echo '<p>ID: '.$user->ID.', User Name: '. $user->user_email . '</p>';
		// 		echo "<pre>user: ";
		// 		print_r($user);
		// 		echo "</pre>";
		// 	}
		// }

		// $wallet_id = (string) $wallet_details->lwXml->WALLET->ID;
		// echo "wallet_id: $wallet_id<Br>";
		// // See if card exists
		// $cards = $wallet_details->lwXml->WALLET->CARDS->CARD;

		// $last4 = substr($cc_number, -4);
		// $first4 = substr($cc_number, 0, 4);
		// $card_exists = false;
		// foreach ($cards as $card) {
		// 	// Check if card in loop has same first 4 and last 4 numbers with card in demand for checking
		// 	// and also the Expiry dates are same, if yes, card exists, and we would re-use it, no need to create another in the
		// 	// Wallet
		// 	$card_last4 = substr($card->EXTRA->NUM, -4);
		// 	$card_first4 = substr($card->EXTRA->NUM, 0, 4);
		// 	$card_exp = $card->EXTRA->EXP;
		// 	if ($card_last4 == $last4 && $card_first4 == $first4 && $card_exp == $cc_expiry) {
		// 		$card_exists = true;
		// 		$custid = $card->ID;
		// 		break;
		// 	}
		// }

		// // Now if card exists, we don't need to do anything here, otherwise add this card
		// if (!$card_exists) {
		// 	// Getting card type
		// 	foreach ($extra_fields as $field) {
		// 		if ($field['name'] == "card_type") {
		// 			$card_type = $field['value'];
		// 			break;
		// 		}
		// 	}
		// 	// Add a card in that Wallet
		// 	$new_card = array(
		// 		'wallet' => (string) $wallet_id,
		// 		'cardType' => 1,
		// 		'cardNumber' => '5017670000006700',
		// 		'cardCode' => '392',
		// 		'cardDate' => '12/2019'
		// 	);
		// 	$response_card = $lemonwayKit->RegisterCard($new_card);
		// 	// If card added successfully as well, return Token then, else display an error
		// 	if (isset($response_card->lwError)) {
		// 		// Error occurred while adding card to Wallet
		// 		echo json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $response_card->lwError->CODE.': '.$response_card->lwError->MSG, 'line' => __LINE__));
		// 		// exit();
		// 	}
		// 	else {
		// 		// Card added, and we have a token for further use
		// 		$custid = (string) $response_card->lwXml->CARD->ID;
		// 		echo "card_id: $custid<br>";
		// 	}
		// }
	}

	/************************************************************************************************************************
	 * 3D Secure method functions
	 ************************************************************************************************************************/
	/**
	 * Ajax function to initialize 3D secure payment if it's enabled
	 */
	public function lemonway_get_required_method() {
		$gateway_settings = get_option('memberdeck_gateways');
		$secure_3d_enabled = $gateway_settings['lemonway_3ds_enabled'];

		// If 3D secure is enabled
		if ($secure_3d_enabled == 1) {
			require dirname(__file__).'/../lib/LemonWay/LemonWayIncludes.php';

			$test = (($gateway_settings['lemonway_test'] == '1') ? true : false);
			$lemonwayKit = new LemonWayKit($gateway_settings, $test);

			$extra_fields = $_POST['Fields'];
			$customer = $_POST['Customer'];

			if (is_user_logged_in()) {
				$current_user = wp_get_current_user();
				$first_name = $current_user->user_firstname;
				$last_name = $current_user->user_lastname;
				$email = $current_user->data->user_email;
				$user_data = json_encode( array("user_id" => $current_user->ID, "fname" => $first_name, "lname" => $last_name, "email" => $email) );
				$new_user = false;
			} else {
				$first_name = sanitize_text_field($customer['first_name']);
				$last_name = sanitize_text_field($customer['last_name']);
				$email = sanitize_text_field($customer['email']);
				$user_data = json_encode( array("fname" => $first_name, "lname" => $last_name, "email" => $email) );
				$new_user = true;
			}
			
			// Creating a Wallet for the User paying (NO NEED, SO COMMENTING THE CODE)
			// Random number for Wallet ID
			// $wallet = $this->getRandomId();
			$wallet_id = '';
			$continue = true;
			// $res = $lemonwayKit->RegisterWallet(array(
			// 	'wallet' => $wallet, 
			// 	'clientMail' => $email,
			// 	'clientTitle' => $first_name." ".$last_name, 
			// 	'clientFirstName' => $first_name, 
			// 	'clientLastName' => $last_name
			// ));
			// if (isset($res->lwError)) {
			// 	// Error occurred while creating wallet, return error response, but check if it's user already exists code
			// 	if ($res->lwError->CODE == "204") {
			// 		// User already exists with this email
			// 		$continue = true;
			// 		$wallet_details = $lemonwayKit->GetWalletDetails(array(
			// 			"email" => $email
			// 		));
			// 		$wallet_id = $wallet_details->lwXml->WALLET->ID;
			// 	} else {
			// 		$ret_response = json_encode( array("response" => "error", "message" => $res->lwError->CODE.": ".$res->lwError->MSG, "line" => __line__) );
			// 		echo $ret_response;
			// 		exit();
			// 	}
			// } else {
			// 	$continue = true;
			// 	$wallet_id = $res->wallet->ID;
			// }
			// If we need to continue
			if (isset($continue) && $continue) {
				$renewable = sanitize_text_field($_POST['Renewable']);
				$id = $_POST['ProductId'];
				$pwywPrice = sanitize_text_field($_POST['PWYW']);
				$txnType = sanitize_text_field($_POST['txnType']);
				$query_string = $_POST['queryString'];
				$level = ID_Member_Level::get_level($id);
				// Taking into account product renewable option
				if (isset($renewable) && $renewable) {
					$price = $level->renewal_price;
				}
				else {
					$price = $level->level_price;
					if (isset($pwywPrice) && $pwywPrice > $price) {
						$price = $pwywPrice;
					}
				}
				$price = number_format($price, 2, '.', '');
				// Return URL, but first checking that below function exists so that there is no Fatal error
				$durl = md_get_durl();
				if (function_exists('idf_get_querystring_prefix')) {
					$prefix = idf_get_querystring_prefix();
				} else {
					// Return error response that IDF is not active
					$ret_response = json_encode( array("response" => "error", "message" => __('IgnitionDeck Framework is not active. Please activate it before proceeding further.', 'memberdeck'), "line" => __line__) );
					echo $ret_response;
					exit();
				}
				$query_string = $query_string . '&price=' . $price;
				$return_url = $durl . $prefix . 'idc_lemonway=1&lemonway_payment=successful&payment_method=3dsecure&idc_product=' . $id . '&paykey=' . $query_string . '&user_data=' . urlencode($user_data);	// . '&user_data=' . $user_data

				$wallet_id_receiver = $gateway_settings['lemonway_wallet_id'];
				// Getting the fees in case of project creator account
				$creator_account = $this->lemonway_get_creator_account($extra_fields, $price, $gateway_settings, $source = 'lemonway');
				$fee = $creator_account['fee'];
				if (!empty($creator_account['wallet_id'])) {
					$wallet_id_receiver = $creator_account['wallet_id'];
				}

				// Creating token for redirecting user to the LemonWay URL
				$web_init_args = array(
					'wkToken' => $this->getRandomId(),
					'wallet' => $wallet_id_receiver,
					'amountTot'=> $price,
					'amountCom'=> $fee,
					'comment' => '',
					'returnUrl' => urlencode($return_url),
					'cancelUrl' => urlencode($this->myUrls['cancelUrl']),
					'errorUrl' => urlencode($this->myUrls['errorUrl']),
					// 'autoCommission' => '0'
				);
				// echo "web_init_args"; print_r($web_init_args); echo "\n";
				$res2 = $lemonwayKit->MoneyInWebInit($web_init_args);
				if (isset($res2->lwError)){
					$ret_response = json_encode( array("response" => "error", "message" => $res2->lwError->CODE.": ".$res2->lwError->MSG, "line" => __line__) );
					echo $ret_response;
					exit();
				}

				// Getting the form
				ob_start();
				$lemonwayKit->printCardForm($res2->lwXml->MONEYINWEB->TOKEN);
				$lemonway_form = ob_get_contents();
				ob_end_clean();

				// Getting the hidden field DATA's value and form post URL from the form
				$dom = new DOMDocument;
				$dom->loadHTML('<?xml version="1.0" encoding="utf-8"?>'.$lemonway_form);
				$xpath = new DOMXPath($dom);

				$form_element = $dom->getElementsByTagName('form')->item(0);
				$form_action = $form_element->getAttribute('action');

				$data_field_query = $xpath->query('//input[@name="DATA"]');
				$data_field = $data_field_query->item(0);
				$data_value = $data_field->getAttribute('value');

				$ret_response = json_encode( array("response" => "success", "secure_3d_enabled" => "1", "token" => (string) $res2->lwXml->MONEYINWEB->TOKEN, "wallet" => (string) $wallet_id, "form_action" => $form_action, "data_value" => $data_value, "form" => $lemonway_form) );
			}
		}
		echo $ret_response;
		exit();
	}

	/**
	 * Init action function called to add IDC order on successful payment
	 */
	public function lemonway_payment_sucessful() {
		// If we are getting in URL lemonway payment as successful
		if (isset($_GET['lemonway_payment']) && $_GET['lemonway_payment'] == "successful") {
			// $log = fopen('lemonwaylog.txt', 'a+');
			// fwrite($log, "Lemonway Success call:\n================================\n");
			// fwrite($log, "_REQUEST: ".print_r($_REQUEST, true)."\n");

			if (isset($_REQUEST['response_transactionId'])) {
				require dirname(__file__).'/../lib/LemonWay/LemonWayIncludes.php';
				$settings = get_option('memberdeck_gateways');

				$test = (($settings['test'] == '1') ? true : false);
				$lemonwayKit = new LemonWayKit($settings, $test);

				// Check transaction details first though, if payment is really successful
				$transaction_details = $lemonwayKit->GetMoneyInTransDetails(array(
					'transactionId'=> $_POST['response_transactionId'],
					'transactionComment'=>'',
					'transactionMerchantToken' => ''
				));
				if (isset($transaction_details->lwError)) {
					// fwrite($log, "transaction_details->lwError: ".print_r($transaction_details->lwError, true)."\n");
					// print '<br/>Error, code '.$transaction_details->lwError->CODE.' : '.$transaction_details->lwError->MSG;
					return;
				}
				if (count($transaction_details->operations) != 1) {
					// print '<br/>Error, too many results : '.count($transaction_details->operations);
					return;
				} else {
					if ((string)$transaction_details->operations[0]->STATUS == '3') {
						// Money-in successul, add orders into database
						$transaction_id = (string) $transaction_details->operations[0]->ID;
						$authorization_code = (string) $transaction_details->operations[0]->EXTRA->AUTH;
						$transaction_amount = (string) $transaction_details->operations[0]->CRED;
						$transaction_success = true;
					} else if ((string) $transaction_details->operations[0]->STATUS == '4') {
						// Transaction failed completely
						$transaction_amount = (string) $transaction_details->operations[0]->CRED;
						$transaction_success = false;
					} elseif ((string) $transaction_details->operations[0]->STATUS == '0') {
						// MoneyIn pending. Don't add the order yet
						$transaction_success = false;
					}
				}

				// If Transaction is successful, add IDC and IDCF order
				// fwrite($log, "transaction_success: ".$transaction_success."\n");
				if ($transaction_success) {
					$level_id = absint($_GET['idc_product']);
					$user_data = (array) json_decode(urldecode(stripslashes($_GET['user_data'])));
	
					$this->add_idc_idcf_order($level_id, $transaction_id, $transaction_amount, $authorization_code, $user_data);
				}
			}
			// fclose($log);
		}
		else {
			if (isset($_GET['idc_lemonway']) && isset($_GET['response_wkToken']) && isset($_GET['status']) && $_GET['status'] == "error") {
				require dirname(__file__).'/../lib/LemonWay/LemonWayIncludes.php';
				$settings = get_option('memberdeck_gateways');

				$test = (($settings['test'] == '1') ? true : false);
				$lemonwayKit = new LemonWayKit($settings, $test);

				// $log = fopen('lemonwaylog.txt', 'a+');
				// fwrite($log, "Error\n==================\n"."\n");
				$res = $lemonwayKit->GetMoneyInTransDetails(array('transactionId'=>'',
											  'transactionComment'=>'',
											  'transactionMerchantToken'=>$_GET['response_wkToken']));
				if (isset($res->lwError)){
					// fwrite($log, 'Error, code '.$res->lwError->CODE.' : '.$res->lwError->MSG."\n");
				}
				// fwrite($log, __line__.': RESponse '.print_r($res, true)."\n");
				// fclose($log);
				return;
			}
		}
	}

	/**
	 * Function to Add IDC and IDCF order if required
	 */
	public function add_idc_idcf_order($level_id, $txn_id, $price, $auth_code, $user_data) {
		global $crowdfunding;
		global $global_currency;
		global $old_db_version;

		$fname = $user_data['fname'];
		$lname = $user_data['lname'];
		$email = $user_data['email'];
		$sub_id = '';

		$log = fopen('lemonwaylog.txt', 'a+');
		fwrite($log, "txn_id: ".$txn_id."\n");
		$txn_check = ID_Member_Order::check_order_exists($txn_id);
		if (empty($txn_check)) {
			fwrite($log, "txn_check: empty\n");
			$level = ID_Member_Level::get_level($level_id);
			if ($level->limit_term == '1') {
				$term_length = $level->term_length;
			}
			$access_levels = array(absint($level_id));
			// now we need to see if this user exists in our db, if we are getting $user_id in arguments $user_data, then we
			// have a registered user
			$user_id = (isset($user_data['user_id']) ? $user_data['user_id'] : '');
			fwrite($log, "user_data: ".print_r($user_data, true)."\n");
			//fwrite($log, serialize($check_user)."\n");
			if (!empty($user_id)) {
				fwrite($log, 'user exists'."\n");
				// now we know this user exists we need to see if he is a current ID_Member
				$match_user = ID_Member::match_user($user_id);
				if (!isset($match_user)) {
					fwrite($log, 'first purchase'."\n");
					// not a member, this is their first purchase
					if ($level->e_date == 'lifetime') {
						$e_date = null;
					}
					else {
						$exp = strtotime('+1 years');
						$e_date = date('Y-m-d h:i:s', $exp);
					}
					$user = array(
						'user_id' => $user_id,
						'level' => $access_levels,
						'data' => array()
					);
					$new = ID_Member::add_user($user);
					$order = new ID_Member_Order(null, $user_id, $level_id, null, $txn_id, $sub_id, 'active', $e_date, $price);
					$new_order = $order->add_order();
				}
				else {
					fwrite($log, 'more than one purchase'."\n");
					// is a member, we need to push new data to their info table
					if (isset($match_user->access_level)) {
						$levels = unserialize($match_user->access_level);
						foreach ($levels as $key['val']) {
							$access_levels[] = absint($key['val']);
						}
					}
					$user = array(
						'user_id' => $user_id,
						'level' => $access_levels,
						'data' => array()
					);
					$new = ID_Member::update_user($user);
					fwrite($log, $user_id."\n");
					$order = new ID_Member_Order(null, $user_id, $level_id, null, $txn_id, $sub_id, 'active', $e_date, $price);
					$new_order = $order->add_order();
				}
			}
			else {
				fwrite($log, 'new user: '."\n");
				// user does not exist, we must create them
				// gen random pw they can change later
				$pw = idmember_pw_gen();
				// gen our user input
				$userdata = array(
					'user_pass' => $pw,
					'first_name' => $fname,
					'last_name' => $lname,
					'user_login' => $email,
					'user_email' => $email,
					'display_name' => $fname
				);
				fwrite($log, "userdata: ".json_encode($userdata)."\n");
				// insert user into WP db and return user id
				$user_id = wp_insert_user($userdata);
				fwrite($log, "new user id: ".$user_id."\n");
				// now add user to our member table
				if ($level->e_date == 'lifetime') {
					$e_date = null;
				}
				else {
					$exp = strtotime('+1 years');
					$e_date = date('Y-m-d h:i:s', $exp);
				}
				//fwrite($log, 'exp: '.$exp."\n");
				$reg_key = md5($email.time());
				$user = array(
					'user_id' => $user_id,
					'level' => $access_levels,
					'reg_key' => $reg_key,
					'data' => array()
				);
				$new = ID_Member::add_paypal_user($user);
				fwrite($log, "new member id: ". $new."\n");
				$order = new ID_Member_Order(null, $user_id, $level_id, null, $txn_id, $sub_id, 'active', $e_date, $price);
				$new_order = $order->add_order();
				fwrite($log, 'order added: '.$new_order."\n");
				do_action('idmember_registration_email', $user_id, $reg_key, $new_order);
			}
			// we need to pass any extra post fields set during checkout
			if (isset($_GET)) {
				$fields = $_GET;
			}
			else {
				$fields = array();
			}
			if (empty($reg_key)) {
				$reg_key = '';
			}
			if ($crowdfunding) {
				if (isset($fields['lemonway_payment']) && $fields['lemonway_payment'] == 'successful') {
					if (isset($fields['mdid_checkout'])) {
						$mdid_checkout = $fields['mdid_checkout'];
					}
					if (isset($fields['project_id'])) {
						$project_id = $fields['project_id'];
					}
					if (isset($fields['project_level'])) {
						$proj_level = $fields['project_level'];
					}
					$order = new ID_Member_Order($new_order);
					$order_info = $order->get_order();
					$created_at = $order_info->order_date;
					$pay_id = mdid_insert_payinfo($fname, $lname, $email, $project_id, $txn_id, $proj_level, $price, $status = null, $created_at);
					if (isset($pay_id)) {
						$mdid_id = mdid_insert_order('', $pay_id, $new_order, null);
						do_action('id_payment_success', $pay_id);
					}
				}
			}
			do_action('memberdeck_payment_success', $user_id, $new_order, $reg_key, $fields, 'lemonway');
		}
		fclose($log);
	}

	public function lemonway_get_order_creating($last_order) {
		// Check that this order is lemonway's and is successful
		if (isset($_GET['idc_lemonway']) && isset($_GET['lemonway_payment']) && $_GET['lemonway_payment'] == "successful") {
			// As order isn't created yet due to IPN, so we will just create a dummy order
			$last_order = (object) array();
			$last_order->id = 0;
			$last_order->user_id = 0;
			$last_order->level_id = absint($_GET['idc_product']);
			$last_order->order_date = date('Y-m-d H:i:s');
			$last_order->transaction_id = '';
			$last_order->subscription_id = '';
			$last_order->subscription_number = '';
			$last_order->e_date = date('Y-m-d H:i:s', strtotime("+1 year"));
			$last_order->status = 'active';
			$last_order->price = sanitize_text_field($_GET['price']);
			$last_order->source_gateway = 'lemonway';
		}

		return $last_order;
	}

	/************************************************************************************************************************
	 * Create customer hook/filter functions for non 3D secure
	 ************************************************************************************************************************/
	/**
	 * Getting customer id on checkout form if stored for this logged in User
	 */
	public function lemonway_customer_card_id($customer_id, $product_id, $settings) {
		// Check if lemonWay is enabled
		if (isset($settings['elw']) && $settings['elw'] == '1') {
			// If method is Non 3D secure, then get customer id, else no need
			if (isset($settings['lemonway_3ds_enabled']) && $settings['lemonway_3ds_enabled'] == '0') {
				if (isset($_GET['mdid_checkout'])) {
					$extra_fields = array(
						array(
							'name' => 'mdid_checkout',
							'value' => '1'
						),
						array(
							'name' => 'project_id',
							'value' => absint($_GET['mdid_checkout'])
						)
					);
				} else {
					$extra_fields = array();
				}
				$customer_id = $this->lemonway_get_customer_id($customer_id, "lemonway", null, $extra_fields);
			}
			// Just send null customer id
			else {
				$customer_id = '';
			}
		}

		return $customer_id;
	}

	/**
	 * Function To Get customer ID
	 */
	public function lemonway_get_customer_id($customer_id, $source, $user_id = null, $extra_fields = null) {
		if ($source == "lemonway") {
			if (is_user_logged_in()) {
				if (empty($user_id)) {
					if (is_multisite()) {
						require (ABSPATH . WPINC . '/pluggable.php');			
					}
					$current_user = wp_get_current_user();
					$user_id = $current_user->ID;
				}
				
				// If user is logged in, then we find his card id, based on Project's creator, otherwise just return null
				// Check if it's IDCF project
				foreach ($extra_fields as $field) {
					if ($field['name'] == "mdid_checkout" && $field['value'] == "1") {
						$mdid_checkout = true;
					}
				}
				if (isset($mdid_checkout) && $mdid_checkout) {
					// Check that if Project creator is connected to LemonWay and approved, then see if User's card_id exists for that user
					// Using lemonway_get_creator_account() to get creator wallet, if we don't get null, then user is connected and approved in LemonWay
					$settings = get_option('memberdeck_gateways');
					$creator_settings = $this->lemonway_get_creator_account($extra_fields, $price = 0, $settings, $source);
					if (isset($creator_settings['wallet_id']) && !empty($creator_settings['wallet_id'])) {
						$creator_id = $creator_settings['creator_id'];
						// Now getting the card id associated with this creator
						$customer_id = get_user_meta($user_id, 'idc_lemonway_card_token_'.$creator_id, true);
					}
					else {
						// Getting the card id associated with global wallet
						$customer_id = get_user_meta($user_id, 'idc_lemonway_card_token', true);
					}
				}
				// Not IDCF project, so get global card_id
				else {
					// Getting the card id associated with global wallet
					$customer_id = get_user_meta($user_id, 'idc_lemonway_card_token', true);
				}
			}
			// User not logged in, so customer_id is null
			else {
				$customer_id = '';
			}

			// Setting static variables
			self::$card_id = (string) $customer_id;
			// self::$wallet_id = (string) $wallet_id;
		}

		return $customer_id;
	}

	/**
	 * Create a customer and return customer id
	 * In args, receiving: $fname, $lname, $email, $cc_number, $cc_expiry, $cc_code, $settings, $source, $extra_fields, $insert (optional)
	 */
	public function lemonway_create_wallet($customer_id, $user_id, $args) {
		extract($args);

		// Getting card type
		foreach ($extra_fields as $field) {
			if ($field['name'] == "card_type") {
				$card_type = $field['value'];
				break;
			}
		}

		// If lemonway is enabled and current source is lemonway as well
		if ($settings['elw'] == "1" && $source == "lemonway") {
			$wallet = $this->getRandomId();
	
			require dirname(__file__).'/../lib/LemonWay/LemonWayIncludes.php';

			$test = (($settings['test'] == '1') ? true : false);
			$lemonwayKit = new LemonWayKit($settings, $test);
	
			$res = $lemonwayKit->RegisterWallet(array(
				'wallet' => $wallet, 
				'clientMail' => $email, 
				'clientTitle' => 'Ms.', 
				'clientFirstName' => $fname, 
				'clientLastName' => $lname
			));
			// If wallet created successfully, add a card in that wallet, else display the error
			if (isset($res->lwError)) {
				// Error occurred while creating wallet, return error response, but check if it's user already exists code
				if ($res->lwError->CODE == "204") {
					// User already exists with this email
					$wallet_details = $lemonwayKit->GetWalletDetails(array(
						"email" => $email
					));
					$wallet_id = (string) $wallet_details->lwXml->WALLET->ID;
				} else {
					echo json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $res->lwError->CODE.': '.$res->lwError->MSG, 'line' => __LINE__));
					exit();
				}
			} else {
				$wallet_id = $res->wallet->ID;
				// self::$wallet_id = (string) $wallet_id;
			}

			// Add a card in that Wallet, but check if it already exists
			$check_response = $this->lemonway_check_card_exists(self::$card_id, $user_id, $args);
			if (!empty($check_response)) {
				$customer_id = self::$card_id;
				if (isset($insert) && !$insert) {
					$insert = true;
					self::$insert_member = true;
				}
			}
			else {
				$customer_id = self::$card_id;
			}
		}

		return $customer_id;
	}

	/**
	 * Filter function to check if a card with number already exists in Customer's wallet and we are not readding
	 * $args contains: $fname, $lname, $email, $cc_number, $cc_expiry, $cc_code, $settings, $source, $extra_fields
	 */
	public function lemonway_check_card_exists($custid, $user_id, $args) {
		// Basically now, the $customer_id is $card_id, but we need to know if we have to add a new card, or old one will work
		extract($args);

		if (isset($settings['elw']) && $settings['elw'] == "1" && $source == "lemonway") {
			require dirname(__file__).'/../lib/LemonWay/LemonWayIncludes.php';

			$test = (($settings['test'] == '1') ? true : false);
			$lemonwayKit = new LemonWayKit($settings, $test);

			// Receiver Wallet id to register card with
			$wallet_id = $settings['lemonway_wallet_id'];
			$creator_account = $this->lemonway_get_creator_account($extra_fields, $price = 0, $settings, $source = 'lemonway');
			if (!empty($creator_account['wallet_id'])) {
				$wallet_id = $creator_account['wallet_id'];
				$creator_id = $creator_account['creator_id'];
			}

			// check if card exists in Creator wallet
			$wallet_details = $lemonwayKit->GetWalletDetails(array(
				"wallet" => $wallet_id
			));

			// // Now see if card exists if there is no error, and we have a Wallet
			// if (isset($wallet_details->lwError)){
			// 	// Error occurred while adding card to Wallet
			// 	// echo json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $response_charge->lwError->CODE.': '.$response_charge->lwError->MSG, 'line' => __LINE__));
			// 	// exit();
			// 	// If error code is 147, create new Wallet and add Card in it
			// 	if ($wallet_details->lwError->CODE == '147') {
			// 		// Create new Wallet
			// 		$args_create = array(
			// 			"fname" => $fname, 
			// 			"lname" => $lname, 
			// 			"email" => $email, 
			// 			"cc_number" => $cc_number, 
			// 			"cc_expiry" => $cc_expiry, 
			// 			"cc_code" => $cc_code, 
			// 			"settings" => $settings, 
			// 			"source" => $source, 
			// 			"extra_fields" => $extra_fields
			// 		);
			// 		list($custid,) = $this->lemonway_create_wallet('', $user_id, $args_create);
			// 		$wallet_id = self::$wallet_id;
			// 	}
			// }
			// else
			{
				// $wallet_id = (string) $wallet_details->lwXml->WALLET->ID;
				// See if card exists
				$cards = $wallet_details->lwXml->WALLET->CARDS->CARD;

				$last4 = substr($cc_number, -4);
				$first4 = substr($cc_number, 0, 4);
				$card_exists = false;
				foreach ($cards as $card) {
					// Check if card in loop has same first 4 and last 4 numbers with card in demand for checking
					// and also the Expiry dates are same, if yes, card exists, and we would re-use it, no need to create another in the
					// Wallet
					$card_last4 = substr($card->EXTRA->NUM, -4);
					$card_first4 = substr($card->EXTRA->NUM, 0, 4);
					$card_exp = $card->EXTRA->EXP;
					if ($card_last4 == $last4 && $card_first4 == $first4 && $card_exp == $cc_expiry) {
						$card_exists = true;
						$custid = $card->ID;
						break;
					}
				}

				// Now if card exists, we don't need to do anything here, otherwise add this card
				if (!$card_exists) {
					// Getting card type
					foreach ($extra_fields as $field) {
						if ($field['name'] == "card_type") {
							$card_type = $field['value'];
							break;
						}
					}

					// Add a card in that Wallet
					$new_card = array(
						'wallet' => (string) $wallet_id,
						'cardType' => $card_type,
						'cardNumber' => $cc_number,
						'cardCode' => $cc_code,
						'cardDate' => $cc_expiry
					);
					$response_card = $lemonwayKit->RegisterCard($new_card);
					// If card added successfully as well, return Token then, else display an error
					if (isset($response_card->lwError)) {
						// Error occurred while adding card to Wallet
						echo json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $response_card->lwError->CODE.': '.$response_card->lwError->MSG, 'line' => __LINE__));
						exit();
					}
					else {
						// Card added, and we have a token for further use
						$custid = (string) $response_card->lwXml->CARD->ID;
					}
				}
			}
		}

		// Adding to static variables
		self::$card_id = (string) $custid;
		self::$wallet_id = (string) $wallet_id;

		// If Creator is set, then we need to store card id in that creator
		if (isset($creator_id)) {
			if (!empty($user_id)) {
				update_user_meta($user_id, 'idc_lemonway_card_token_'.$creator_id, self::$card_id);
			} else {
				// This is a new user, so will store his Card id with this creator later
				self::$project_creator = $creator_id;
			}
		}

		return $custid;
	}

	/**
	 * Filter function to receive the $customer_id and $card_id, and make a charge on it
	 * @param  $custid   Wallet ID of the user
	 * @param  $card_id  Card token of User
	 * $args getting: $txn_type, $custid, $email, $card_id, $amount, $settings, $source, $extra_fields
	 */
	public function lemonway_charge_stored_card($txn_id, $success, $args) {
		// Getting the variables
		extract($args);

		$custid = self::$wallet_id;
		$card_id = self::$card_id;
		
		// If source is lemonway then make a charge
		if ($source == "lemonway") {
			require dirname(__file__).'/../lib/LemonWay/LemonWayIncludes.php';

			$test = (($settings['test'] == '1') ? true : false);
			$lemonwayKit = new LemonWayKit($settings, $test);
			$wallet_id_receiver = $settings['lemonway_wallet_id'];
			$amount = str_replace(",", "", $amount);

			// Getting the fees in case of project creator account
			$creator_account = $this->lemonway_get_creator_account($extra_fields, $amount, $settings, $source);
			$fee = $creator_account['fee'];
			if (!empty($creator_account['wallet_id'])) {
				$wallet_id_receiver = $creator_account['wallet_id'];
			}

			$wkToken = $this->getRandomId();
			// Formatting amounts
			$amount = number_format($amount, 2, '.', '');
			$response_charge = $lemonwayKit->MoneyInWithCardId(array(
				'wkToken' => $wkToken,
				'wallet' => $wallet_id_receiver,
				'amountTot' => $amount,
				'amountCom' => $fee,
				'comment' => __('Purchasing or Supporting Project on Crowdfunding website using IgnitionDeck Plugin. Website address is ', 'memberdeck'). home_url() . '. ' . __('Customer Wallet ID is ', 'memberdeck') . $custid . '. ' . __('Email address of the customer is ', 'memberdeck') . $email . '.',
				'cardId' => $card_id,
				// 'autoCommission'=>'0',
				'isPreAuth' => (($txn_type == 'preauth') ? '1' : '0')
			));

			if (isset($response_charge->lwError)){
				// Error occurred while adding card to Wallet
				echo json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $response_charge->lwError->CODE.': '.$response_charge->lwError->MSG, 'line' => __LINE__));
				exit();
			}
			
			// If it's not a preauth and STATUS = 2, then transaction is successful
			if ((string) $response_charge->operations[0]->STATUS == '3') {
				// Transaction is successful
				$txn_id = $response_charge->operations[0]->ID;
				self::$payment_success = true;
			}
			// If $txn_type is preauth, and status = 16
			else if ((string)$response_charge->operations[0]->STATUS == '16') {
				// Transaction is successful, pending validation now within 6 days otherwise transaction is cancelled
				$txn_id = $response_charge->operations[0]->ID;
				self::$payment_success = true;
			}
		}

		return $txn_id;
	}

	/**
	 * filter to add if required in $user, and update meta fields
	 */
	public function lemonway_update_user_and_meta($user, $source) {
		if ($source == "lemonway") {
			$user_id = $user['user_id'];
			// Update meta data
			// update_user_meta($user_id, 'idc_lemonway_wallet_id', self::$wallet_id);
			if (!empty(self::$project_creator)) {
				update_user_meta($user_id, 'idc_lemonway_card_token_'.self::$project_creator, self::$card_id);
			} else {
				update_user_meta($user_id, 'idc_lemonway_card_token', self::$card_id);
			}
		}
		return $user;
	}

	/**
	 * Function to set the charge token for lemonway, which is $txn_id
	 */
	public function lemonway_preorder_charge_token($charge_token, $txn_id, $args) {
		// echo __line__.": source: ".$args['source']."\n";
		// echo __line__.": charge_token: $charge_token\n";
		if ($args['source'] == "lemonway") {
			$success = false;
			// echo __line__.": self::wallet_id: ".self::$wallet_id."\n";
			// echo __line__.": self::card_id: ".self::$card_id."\n";
			$txn_id = $this->lemonway_charge_stored_card($txn_id, $success, $args);
			// $txn_id = $txn_id[0];

			$charge_token = $txn_id;
			// echo __line__.": txn_id: $txn_id\n";
			// echo __line__.": charge_token: ".$charge_token."\n";
		}

		return $charge_token;
	}

	/**
	 * Filter function to return whether instant checkout needs to be enabled or disabled
	 */
	public function lemonway_instant_checkout($instant_checkout, $user_id, $settings) {
		if (isset($settings['elw']) && $settings['elw'] == '1') {
			$instant_checkout = false;
			// check that Non secure 3D method is active
			if (isset($settings['lemonway_3ds_enabled']) && $settings['lemonway_3ds_enabled'] == '0') {
				$user_instant_checkout = get_user_meta($user_id, 'instant_checkout', true);
				if (!empty($user_instant_checkout) && $user_instant_checkout == 1) {
					$instant_checkout = true;
				}
			}
		}

		return $instant_checkout;
	}

	/**
	 * Function to return whether to insert a member or not
	 * In our class, it's a static variable taking value from the function lemonway_create_wallet(), so we need
	 * to return only
	 */
	public function lemonway_get_insert_member($insert, $customer_id, $source) {
		if ($source == "lemonway") {
			$insert = self::$insert_member;
		}

		return $insert;
	}

	/**
	 * Filter function to return whether last transaction being made was successful
	 */
	public function lemonway_non3ds_success($success, $txn_id, $source) {
		// If source is lemonway, return the static success variable
		if ($source == "lemonway") {
			$success = self::$payment_success;
		}

		return $success;
	}

	/**
	 * Filter function to validate PreAuth transaction and return 1 or 0 depending on the result
	 */
	public function lemonway_validate_preauth($paid, $price, $gateway, $customer_id, $charge_token, $settings) {
		// Check if gateway is lemonway, then process transaction
		if ($gateway == 'lemonway') {
			require dirname(__file__).'/../lib/LemonWay/LemonWayIncludes.php';

			$test = (($settings['test'] == '1') ? true : false);
			$lemonwayKit = new LemonWayKit($settings, $test);

			$validation_response = $lemonwayKit->MoneyInValidate(array(
				"transactionId" => $charge_token
			));
			// If there is an error in validation
			if (isset($validation_response->lwError)) {
				$paid = 0;
			}
			else {
				$paid = 1;
				self::$preauth_txn_id = (string) $validation_response->lwXml->MONEYIN->HPAY->ID;
			}
		}
		return $paid;
	}

	/**
	 * filter function to return the transaction id of PreAuth validation
	 */
	public function lemonway_preauth_txn_id($txn_id, $charge_token, $customer_id, $gateway, $settings) {
		// If gateway is lemonWay, $txn_id is stored in the static variable
		if ($gateway == "lemonway") {
			$txn_id = self::$preauth_txn_id;
		}
		return $txn_id;
	}
}

// If Lemonway is active
if (ID_Lemon_Way::$lemonway_active) {
	$idc_lemonway = new ID_Lemon_Way();
	$idc_lemonway->init();
}
?>