<?php
/**
 * Class for Renewals 
 */
class ID_Member_Renewal
{
	var $user_id;

	function __construct($user_id) {
		$this->user_id = $user_id;
	}

	public function send_notification_for_renewal($days_left, $product_id, $level = null) {
		$https = md_https();
		$durl = md_get_durl($https);
		if (function_exists('idf_get_querystring_prefix')) {
			$prefix = idf_get_querystring_prefix();
		} else {
			$prefix = '?';
		}
		$settings = get_option('md_receipt_settings');
		$user_id = $this->user_id;
		if (!empty($settings)) {
			if (!is_array($settings)) {
				$settings = unserialize($settings);
			}
			$coname = $settings['coname'];
			$coemail = $settings['coemail'];
		}
		else {
			$coname = '';
			$coemail = get_option('admin_email', null);
		}

		if (!empty($user_id)) {
			$user = get_user_by('id', $user_id);
			if (isset($user)) {
				$fname = $user->user_firstname;
				$lname = $user->user_lastname;
				$email = $user->user_email;
			}
		}
		// If id is not set, then no one is the recipient, return from the function
		else {
			return false;
		}
		if (empty($email)) {
			return false;
		}
		$site_name = get_bloginfo('name');
		$subject = $site_name.' '.__('Product Renewal Notification', 'memberdeck');
		// Getting weeks and months left if they are needed
		$weeks_left = number_format($days_left / 7, 0);
		$months_left = number_format($days_left / 30, 0);

		// Getting level for product information
		if (empty($level)) {
			$level = ID_Member_Level::get_level($product_id);
		}

		$message = '<html><body>';
		$text = get_option('product_renewal_email');
		if (empty($text)) {
			$text = get_option('product_renewal_email_default');
		}
		if (empty($text)) {
			$message .= '<div style="padding:10px;background-color:#f2f2f2;">
							<div style="padding:10px;border:1px solid #eee;background-color:#fff;">
							<h2>'.$subject.'</h2>

								<div style="margin:10px;">

		 							'.__('Hello', 'memberdeck').' '. (isset($fname) ? $fname : '') .' '. (isset($lname) ? $lname : '') .', <br /><br />
		  
		  							'.__('Your product ', 'memberdeck').'<strong>'.$level->level_name.'</strong>'.__(' is about to expire', 'memberdeck').'.<br /><br />

		  							'.__('Please renew your product to avoid any inconvenience', 'memberdeck').'<br/><br/>
								
		  							<div style="border: 1px solid #333333; width: 500px;">
		    							<table width="500" border="0" cellspacing="0" cellpadding="5">
		          							<tr bgcolor="#333333" style="color: white">
						                        <td width="200">'.__('Days remaining', 'memberdeck').'</td>
						                    </tr>
					                         <tr>
					                           <td width="200">'.$days_left.'</td>
					                      	</tr>
		    							</table>
		    						</div>
									<br>
									<a href="'.$durl.$prefix.'idc_renewal_checkout='.$level->id.'&price='.$level->level_price.'">'.__('Follow this link', 'memberdeck').'</a> '.__('to renew your product or use the address below', 'memberdeck').'.<br/>
									<a href="'.$durl.$prefix.'idc_renewal_checkout='.$level->id.'&price='.$level->level_price.'">'.$durl().$prefix.'idc_renewal_checkout='.$level->id.'&amp;price='.$level->level_price.'</a>
									
								</div>

								<table rules="all" style="border-color:#666;width:80%;margin:20px auto;" cellpadding="10">

		    					<!--table rows-->

								</table>

				               ---------------------------------<br />
				               '.$coname.'<br />
				               <a href="mailto:'.$coemail.'">'.$coemail.'</a>
				           

				            </div>
				        </div>';
		}
		else {
			$merge_swap = array(
				array(
					'tag' => '{{NAME}}',
					'swap' => $fname.' '.$lname
					),
				array(
					'tag' => '{{SITE_NAME}}',
					'swap' => $site_name
					),
				array(
					'tag' => '{{EMAIL}}',
					'swap' => $email
					),
				array(
					'tag' => '{{DURL}}',
					'swap' => $durl
					),
				array(
					'tag' => '{{COMPANY_NAME}}',
					'swap' => $coname
					),
				array(
					'tag' => '{{COMPANY_EMAIL}}',
					'swap' => $coemail
					),
				array(
					'tag' => '{{DAYS_LEFT}}',
					'swap' => $days_left
				),
				array(
					'tag' => '{{WEEKS_LEFT}}',
					'swap' => $weeks_left
				),
				array(
					'tag' => '{{MONTHS_LEFT}}',
					'swap' => $months_left
				),
				array(
					'tag' => '{{PRODUCT_NAME}}',
					'swap' => $level->level_name
				),
				array(
					'tag' => '{{RENEWAL_CHECKOUT_URL}}',
					'swap' => $durl.$prefix.'idc_renewal_checkout='.$level->id.'&price='.$level->level_price
				)
			);
			foreach ($merge_swap as $swap) {
				$text = str_replace($swap['tag'], $swap['swap'], $text);
			}
			$message .= wpautop($text);
		}
		$message .= '</body></html>';
		// Sending email using Member Email class
		$mail = new ID_Member_Email($email, $subject, $message, $this->user_id);
		// $mail = new ID_Member_Email($email, $subject, $message, $user_id);
		$send_mail = $mail->send_mail();
	}
}
?>