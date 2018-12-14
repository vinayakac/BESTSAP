<?php
class ID_Member_Email {
	var $to;
	var $subject;
	var $message;
	var $user_id;
	var $headers;

	function __construct(
		$to = null,
		$subject = '',
		$message = '',
		$user_id = null,
		$headers = null
		)
	{
		$this->to = $to;
		$this->subject = $subject;
		$this->message = $message;
		$this->user_id = $user_id;
		$this->headers = $headers;
	}

	function send_mail() {
		/*
		** Ensure we have company information
		*/

		$settings = get_option('md_receipt_settings');
		if (!empty($settings)) {
			$settings = maybe_unserialize($settings);
			$coname = (!empty($settings['coname']) ? $settings['coname'] : get_option('blogname', ''));
			$coemail = (!empty($settings['coemail']) ? $settings['coemail'] : get_option('admin_email', ''));
		}
		else {
			$coname = '';
			$coemail = get_option('admin_email', '');
		}
		/*
		** Check CRM Settings
		*/

		$crm_settings = get_option('crm_settings');
		if (!empty($crm_settings)) {
			$sendgrid_api_key = $crm_settings['sendgrid_api_key'];
			$enable_sendgrid = $crm_settings['enable_sendgrid'];
			$mandrill_key = $crm_settings['mandrill_key'];
			$enable_mandrill = $crm_settings['enable_mandrill'];
		}
		if (!empty($coemail)) {
			if (!empty($this->user_id)) {
				$user = get_user_by('id', $this->user_id);
				if (isset($user)) {
					$fname = $user->user_firstname;
					$lname = $user->user_lastname;
				}
			}

			if (isset($enable_sendgrid) && $enable_sendgrid == 1) {
				require_once IDC_PATH.'lib/sendgrid-php/sendgrid-php.php';
				$sendgrid = new SendGrid($sendgrid_api_key);
				$mail = new SendGrid\Email();
				$mail->
					addTo($this->to)->
					setReplyTo($coemail)->
					setFrom($coemail)->
					setFromName($coname)->
					setSubject($this->subject)->
					setText(null)->
					setReplyTo($coemail)->
					setHtml($this->message);
					try {
						$go = $sendgrid->send($mail);
					}
					catch(Exception $e) {
						exit;
					}
			}
			else if (isset($enable_mandrill) && $enable_mandrill == 1) {
				try {
					require_once IDC_PATH.'lib/mandrill-php-master/src/Mandrill.php';
					$mandrill = new Mandrill($mandrill_key);
					$msgarray = array(
						'html' => $this->message,
						'text' => '',
						'subject' => $this->subject,
						'from_email' => $coemail,
						'from_name' => $coname,
						'to' => array(
							array(
								'email' => $this->to,
								'name' => (isset($fname) && isset($lname) ? $fname.' '.$lname : ''),
								'type' => 'to'
								)
							),
						'headers' => array(
							'MIME-Version' => '1.0',
							'Content-Type' => 'text/html',
							'charset' =>  'UTF-8',
							'Reply-To' => $coemail
							)
						);
					$async = false;
					$ip_pool = null;
					$send_at = null;
					$go = $mandrill->messages->send($msgarray, $async, $ip_pool, $send_at);
				}
				catch(Mandrill_Error $e) {
				   // Mandrill errors are thrown as exceptions
				   //echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
				   // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
				}
			}
			else {
				//echo $email."<br>".$subject."<br>".$message;
				if (empty($headers)) {
					$this->headers = 'From: '.$coname.' <'.$coemail.'>' . "\n";
					$this->headers .= 'Reply-To: '.$coemail."\n";
					$this->headers .= "MIME-Version: 1.0\n";
					$this->headers .= "Content-Type: text/html; charset=UTF-8\n";
				}
				$go = mail($this->to, $this->subject, $this->message, $this->headers);
			}
		}
	}
}
?>