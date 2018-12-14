<li class="md-box half">
	<div class="md-profile stripe-settings">
		<h3><?php _e('Stripe', 'memberdeck') ?></h3>
		<p><?php _e('Sign up with the Stripe payment gateway to get paid directly to your own account. The process is simple, and there are no setup or monthly fees. Click Connect with Stripe to get started.', 'memberdeck') ?></p>
		<a <?php echo (empty($check_creds) ? 'href="https://connect.stripe.com/oauth/authorize?response_type=code&amp;client_id='.$client_id.'&amp;scope=read_write&amp;state='.$user_id.'"' : ''); ?> class="<?php echo $button_style; ?>">
			<span><?php echo (!empty($check_creds) ? '<i class="fa fa-check"></i> '.__('Connected!', 'memberdeck') : __('Connect with Stripe', 'memberdeck')); ?></span>
		</a>
	</div>
</li>