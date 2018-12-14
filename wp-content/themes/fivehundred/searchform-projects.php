<h2> posty </h2>
<form method="get" id="searchform" action="<?php echo home_url( '/' ); ?>">
<div id="search-inputs">
	<input type="text" value="" name="s" id="s" />
	<?php if (isset($post->post_type) && $post->post_type == 'ignition_product') { ?>
	<input type="hidden" name="post_type" value="ignition_product" />
	<?php } ?>
	<input type="submit" id="searchsubmit" value="<?php _e('Search', 'ignition_product'); ?>" />
</div>
</form>