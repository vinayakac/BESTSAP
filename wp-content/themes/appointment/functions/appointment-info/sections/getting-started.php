<?php
/**
 * Getting started template
 */

$customizer_url = admin_url() . 'customize.php' ;
?>

<div id="getting_started" class="appointment-tab-pane active">

	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<h1 class="appointment-info-title text-center"><?php echo __('About Appointment Theme','appointment'); ?><?php if( !empty($appointment['Version']) ): ?> <sup id="appointment-theme-version"><?php echo esc_attr( $appointment['Version'] ); ?> </sup><?php endif; ?></h1>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="appointment-tab-pane-half appointment-tab-pane-first-half">
				<p><?php esc_html_e( 'Business theme which is ideal for creating a corporate, business, fashion and sports websites.The premium version have tons of features. Hompage have number of sections for showing slider, your  portfolio, users reviews, latest news,  services, call to action and many more. Each section in the HomePage Template is well designed according to the business requirements.','appointment');?></p>
				<p>
				<?php esc_html_e( 'You can use this theme for any business type. Compatible with popular plugins like Contact Form 7, WPML, Polylang etc. Theme have their predefined version of contact page, service page, portfolio column layout pages, about us and blog layout pages helping you in creating an effective web presence.', 'appointment' ); ?>
				</p>
				</div>
			</div>
			<div class="col-md-6">
				<div class="appointment-tab-pane-half appointment-tab-pane-first-half">
				<img src="<?php echo esc_url( get_template_directory_uri() ) . '/functions/appointment-info/img/appointment.png'; ?>" alt="<?php esc_html_e( 'appointment Blue Child Theme', 'appointment' ); ?>" />
				</div>
			</div>	
		</div>
	
	
		 <div class="row">
		 
			<div class="appointment-tab-center">

				<h1><?php esc_html_e( "Useful Links", 'appointment' ); ?></h1>

			</div>
			
			<div class="col-md-6"> 
				<div class="appointment-tab-pane-half appointment-tab-pane-first-half">

					<a href="<?php echo esc_url(__('http://webriti.com/appointment-free/','appointment')); ?>" target="_blank"  class="info-block"><div class="dashicons dashicons-desktop info-icon"></div>
					<p class="info-text"><?php echo __('Lite Demo','appointment'); ?></p></a>
					
					<a href="<?php echo esc_url(__('http://webriti.com/demo/wp/appointment','appointment')); ?>" target="_blank"  class="info-block"><div class="dashicons dashicons-desktop info-icon"></div>
					<p class="info-text"><?php echo __('Pro Demo','appointment'); ?></p></a>
					
					<a href="<?php echo esc_url(__('http://webriti.com/help/category/themes/appointment/','appointment')); ?>" target="_blank"  class="info-block"><div class="dashicons dashicons-sos info-icon"></div>
					<p class="info-text"><?php echo __('Premium Theme Support','appointment'); ?></p></a>
					
				</div>
			</div>
			
			<div class="col-md-6">	
				<div class="appointment-tab-pane-half appointment-tab-pane-first-half">
					
					<a href="<?php echo esc_url(__('http://webriti.com/appointment','appointment')); ?>" target="_blank"  class="info-block"><div class="dashicons dashicons-book-alt info-icon"></div>
					<p class="info-text"><?php echo __('Premium Theme Details','appointment'); ?></p></a>
					
					<a href="<?php echo esc_url(__('https://wordpress.org/support/view/theme-reviews/appointment','appointment')); ?>" target="_blank"  class="info-block"><div class="dashicons dashicons-smiley info-icon"></div>
					<p class="info-text"><?php echo __('Your Feedback is valuable to us','appointment'); ?></p></a>
					
					
					
				</div>
			</div>
			
		</div>	
	</div>
</div>	