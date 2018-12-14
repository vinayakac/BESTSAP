<?php if (is_user_logged_in()) { ?>
	<li><a href="<?php echo $durl; ?>"><i class="fa fa-home"></i></a></li>
	<li><a href="<?php echo $durl.$prefix; ?>edit-profile=1"><i class="fa fa-cog"></i></a></li>
	<li><a href="<?php echo $durl.$prefix; ?>idc_orders=1"><i class="fa fa-file-text"></i></a></li>
	<?php if ($crowdfunding) { ?>
		<li><a href="<?php echo $durl.$prefix.'backer_profile='.$current_user->ID; ?>"><i class="fa fa-user"></i></a></li>
		<?php if (current_user_can('create_edit_projects')) { ?>
			<li><a href="<?php echo $durl.$prefix.'creator_profile='.$current_user->ID; ?>"><i class="fa fa-users"></i></a></li>
			<li><a href="<?php echo $durl.$prefix; ?>payment_settings=1"><i class="fa fa-university"></i></a></li>
			<li><a href="<?php echo $durl.$prefix; ?>creator_projects=1"><i class="fa fa-rocket"></i></a></li>
		<?php } ?>
	<?php } ?>
<?php }