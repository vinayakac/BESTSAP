<?php if (is_user_logged_in()) { ?>
	<li><a href="<?php echo $durl; ?>"><?php _e('Dashboard', 'idcommerce'); ?></a></li>
	<li><a href="<?php echo $durl.$prefix; ?>edit-profile=1"><?php _e('Account', 'idcommerce'); ?></a></li>
	<li><a href="<?php echo $durl.$prefix; ?>idc_orders=1"><?php _e('Order History', 'idcommerce'); ?></a></li>
	<?php if ($crowdfunding) { ?>
		<li><a href="<?php echo $durl.$prefix.'backer_profile='.$current_user->ID; ?>"><?php _e('Backer Profile', 'idcommerce'); ?></a></li>
		<?php if (current_user_can('create_edit_projects')) { ?>
			<li><a href="<?php echo $durl.$prefix.'creator_profile='.$current_user->ID; ?>"><?php _e('Creator Profile', 'idcommerce'); ?></a></li>
			<li><a href="<?php echo $durl.$prefix; ?>payment_settings=1"><?php _e('Creator Settings', 'idcommerce'); ?></a></li>
			<li><a href="<?php echo $durl.$prefix; ?>creator_projects=1"><?php _e(($project_count > 0 ? 'My Projects' : 'Create Project'), 'idcommerce'); ?></a></li>
		<?php } ?>
	<?php } ?>
<?php } ?>