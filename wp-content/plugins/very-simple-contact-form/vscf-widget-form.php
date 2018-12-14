<?php

// disable direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Start session for captcha validation
if (!isset ($_SESSION)) session_start(); 
$_SESSION['vscf-widget-rand'] = isset($_SESSION['vscf-widget-rand']) ? $_SESSION['vscf-widget-rand'] : rand(100, 999);

// The shortcode
function vscf_widget_shortcode($vscf_atts) {
	$vscf_atts = shortcode_atts( array( 
		"email_to" => get_bloginfo('admin_email'),
		"label_name" => __('Name', 'very-simple-contact-form'),
		"label_email" => __('Email', 'very-simple-contact-form'),
		"label_subject" => __('Subject', 'very-simple-contact-form'),
		"label_message" => __('Message', 'very-simple-contact-form'),
		"label_captcha" => __('Enter number %s', 'very-simple-contact-form'),
		"label_submit" => __('Submit', 'very-simple-contact-form'),
		"error_name" => __('Please enter at least 2 characters', 'very-simple-contact-form'),
		"error_subject" => __('Please enter at least 2 characters', 'very-simple-contact-form'),
		"error_message" => __('Please enter at least 10 characters', 'very-simple-contact-form'),
		"error_captcha" => __('Please enter the correct number', 'very-simple-contact-form'),
		"error_email" => __('Please enter a valid email', 'very-simple-contact-form'),
		"message_success" => __('Thank you! You will receive a response as soon as possible.', 'very-simple-contact-form'),
		"hide_subject" => ''
	), $vscf_atts);

	// Set variables 
	$form_data = array(
		'form_name' => '',
		'form_email' => '',
		'form_subject' => '',
		'form_captcha' => '',
		'form_firstname' => '',
		'form_lastname' => '',
		'form_message' => ''
	);
	$error = false;
	$sent = false;
	$info = '';

	if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['vscf_widget_send']) ) {
	
		// Sanitize content
		$post_data = array(
			'form_name' => sanitize_text_field($_POST['vscf_name']),
			'form_email' => sanitize_email($_POST['vscf_email']),
			'form_subject' => sanitize_text_field($_POST['vscf_subject']),
			'form_message' => wp_kses_post($_POST['vscf_message']),
			'form_captcha' => sanitize_text_field($_POST['vscf_captcha']),
			'form_firstname' => sanitize_text_field($_POST['vscf_firstname']),
			'form_lastname' => sanitize_text_field($_POST['vscf_lastname'])
		);

		// Validate name
		$value = $post_data['form_name'];
		if ( strlen($value)<2 ) {
			$error_class['form_name'] = true;
			$error = true;
		}
		$form_data['form_name'] = $value;

		// Validate email
		$value = $post_data['form_email'];
		if ( empty($value) ) {
			$error_class['form_email'] = true;
			$error = true;
		}
		$form_data['form_email'] = $value;

		// Validate subject
		if ($vscf_atts['hide_subject'] != "true") {		
			$value = $post_data['form_subject'];
			if ( strlen($value)<2 ) {
				$error_class['form_subject'] = true;
				$error = true;
			}
			$form_data['form_subject'] = $value;
		}

		// Validate message
		$value = $post_data['form_message'];
		if ( strlen($value)<10 ) {
			$error_class['form_message'] = true;
			$error = true;
		}
		$form_data['form_message'] = $value;

		// Validate captcha
		$value = $post_data['form_captcha'];
		if ( $value != $_SESSION['vscf-widget-rand'] ) { 
			$error_class['form_captcha'] = true;
			$error = true;
		}
		$form_data['form_captcha'] = $value;

		// Validate first honeypot field
		$value = $post_data['form_firstname'];
		if ( strlen($value)>0 ) {
			$error = true;
		}
		$form_data['form_firstname'] = $value;

		// Validate second honeypot field
		$value = $post_data['form_lastname'];
		if ( strlen($value)>0 ) {
			$error = true;
		}
		$form_data['form_lastname'] = $value;

		// Sending message to admin
		if ($error == false) {
			// Hook to support plugin Contact Form DB
			do_action( 'vscf_before_send_mail', $form_data );
			$to = $vscf_atts['email_to'];
			if ($vscf_atts['hide_subject'] != "true") {
				$subject = "(".get_bloginfo('name').") " . $form_data['form_subject'];
			} else {
				$subject = get_bloginfo('name');
			}
			$message = $form_data['form_name'] . "\r\n\r\n" . $form_data['form_email'] . "\r\n\r\n" . $form_data['form_message'] . "\r\n\r\n" . sprintf( esc_attr__( 'IP: %s', 'very-simple-contact-form' ), vscf_get_the_ip() ); 
			$headers = "Content-Type: text/plain; charset=UTF-8" . "\r\n";
			$headers .= "Content-Transfer-Encoding: 8bit" . "\r\n";
			$headers .= "From: ".$form_data['form_name']." <".$form_data['form_email'].">" . "\r\n";
			$headers .= "Reply-To: <".$form_data['form_email'].">" . "\r\n";
			wp_mail($to, $subject, $message, $headers);
			$result = $vscf_atts['message_success'];
			$sent = true;
		}
	}

	// Display success message
	if(!empty($result)) {
		$info = '<p class="vscf-info">'.esc_attr($result).'</p>';
	}

	// Hide or display subject field 
	if ($vscf_atts['hide_subject'] == "true") {
		$hide = true;
	}

	// Contact form
	$email_form = '<form class="vscf" id="vscf" method="post">
		<p><label for="vscf_name">'.esc_attr($vscf_atts['label_name']).': <span class="'.(isset($error_class['form_name']) ? "error" : "hide").'" >'.esc_attr($vscf_atts['error_name']).'</span></label></p>
		<p><input type="text" name="vscf_name" id="vscf_name" '.(isset($error_class['form_name']) ? ' class="error"' : '').' maxlength="50" value="'.esc_attr($form_data['form_name']).'" /></p>
		
		<p><label for="vscf_email">'.esc_attr($vscf_atts['label_email']).': <span class="'.(isset($error_class['form_email']) ? "error" : "hide").'" >'.esc_attr($vscf_atts['error_email']).'</span></label></p>
		<p><input type="text" name="vscf_email" id="vscf_email" '.(isset($error_class['form_email']) ? ' class="error"' : '').' maxlength="50" value="'.esc_attr($form_data['form_email']).'" /></p>
		
		<p><label for="vscf_subject" '.(isset($hide) ? ' class="hide"' : '').'>'.esc_attr($vscf_atts['label_subject']).': <span class="'.(isset($error_class['form_subject']) ? "error" : "hide").'" >'.esc_attr($vscf_atts['error_subject']).'</span></label></p>
		<p><input type="text" name="vscf_subject" id="vscf_subject" '.(isset($hide) ? ' class="hide"' : ''). (isset($error_class['form_subject']) ? ' class="error"' : '').' maxlength="50" value="'.esc_attr($form_data['form_subject']).'" /></p>
		
		<p><label for="vscf_captcha">'.sprintf(esc_attr($vscf_atts['label_captcha']), $_SESSION['vscf-widget-rand']).': <span class="'.(isset($error_class['form_captcha']) ? "error" : "hide").'" >'.esc_attr($vscf_atts['error_captcha']).'</span></label></p>
		<p><input type="text" name="vscf_captcha" id="vscf_captcha" '.(isset($error_class['form_captcha']) ? ' class="error"' : '').' maxlength="50" value="'.esc_attr($form_data['form_captcha']).'" /></p>
		
		<p><input type="text" name="vscf_firstname" id="vscf_firstname" maxlength="50" value="'.esc_attr($form_data['form_firstname']).'" /></p>
		
		<p><input type="text" name="vscf_lastname" id="vscf_lastname" maxlength="50" value="'.esc_attr($form_data['form_lastname']).'" /></p>
		
		<p><label for="vscf_message">'.esc_attr($vscf_atts['label_message']).': <span class="'.(isset($error_class['form_message']) ? "error" : "hide").'" >'.esc_attr($vscf_atts['error_message']).'</span></label></p>
		<p><textarea name="vscf_message" id="vscf_message" rows="10" '.(isset($error_class['form_message']) ? ' class="error"' : '').'>'.wp_kses_post($form_data['form_message']).'</textarea></p>
		
		<p><input type="submit" value="'.esc_attr($vscf_atts['label_submit']).'" name="vscf_widget_send" id="vscf_widget_send" /></p>
	</form>';
	
	// Send message and unset captcha variabele or display form with error message
	if(isset($sent) && $sent == true) {
		unset($_SESSION['vscf-widget-rand']);
		return $info;
	} else {
		return $email_form;
	}
} 
add_shortcode('contact-widget', 'vscf_widget_shortcode');

?>