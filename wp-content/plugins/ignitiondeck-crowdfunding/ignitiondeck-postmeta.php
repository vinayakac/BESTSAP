<?php
add_action('init', 'install_ign_metaboxes');

function install_ign_metaboxes() {
	if (is_admin()) {
		global $pagenow;
		if ($pagenow == 'post.php' || $pagenow =='post-new.php') {
			add_filter('ign_cmb_meta_boxes', 'ign_meta_boxes');
			require_once('ign_metabox/init.php');
			// Include & setup custom metabox and fields
		}
	}
}

function ign_meta_boxes(array $meta_boxes) {
	require 'languages_default/text_variables.php';
	$prefix = 'ign_';
	$id_meta_boxes = array(
	    'id' => 'product_meta',
	    'title' => $tr_Project,
	    'pages' => array('ignition_product'), // post type
		'context' => 'normal',
		'priority' => 'high',
		'class' => $prefix . 'projectmeta',
	    'fields' => array(
	    	(!(function_exists('is_id_pro') && is_id_pro())) ? array('type' => '') : array(
				'name' => __('Company Name', 'ignitiondeck'),
				'desc' => __('Project\'s Company Name', 'ignitiondeck'),
				'id' => $prefix.'company_name',
				'value' => $prefix.'company_name',
				'show_help' => true,
				'type' => 'text',
				'class' => $prefix.'projectmeta_left_third'
			),
			(!(function_exists('is_id_pro') && is_id_pro())) ? array('type' => '') : array(
				'name' => __('Company Location', 'ignitiondeck'),
				'desc' => __('Location of the Company. Example: New York', 'ignitiondeck'),
				'id' => $prefix.'company_location',
				'value' => $prefix.'company_location',
				'show_help' => true,
				'type' => 'text',
				'class' => $prefix.'projectmeta_third'
			),
			(!(function_exists('is_id_pro') && is_id_pro())) ? array('type' => '') : array(
				'name' => __('Company URL', 'ignitiondeck'),
				'desc' => __('URL of the website of the Company. Example: http://example.com', 'ignitiondeck'),
				'id' => $prefix.'company_url',
				'value' => $prefix.'company_url',
				'show_help' => true,
				'type' => 'text',
				'class' => $prefix.'projectmeta_third'
			),
			(!(function_exists('is_id_pro') && is_id_pro())) ? array('type' => '') : array(
				'name' => __('Company Facebook', 'ignitiondeck'),
				'desc' => __('Company Facebook page', 'ignitiondeck'),
				'id' => $prefix.'company_fb',
				'value' => $prefix.'company_fb',
				'show_help' => true,
				'type' => 'text',
				'class' => $prefix.'projectmeta_left_third'
			),
			(!(function_exists('is_id_pro') && is_id_pro())) ? array('type' => '') : array(
				'name' => __('Company Twitter', 'ignitiondeck'),
				'desc' => __('Company Twitter page', 'ignitiondeck'),
				'id' => $prefix.'company_twitter',
				'value' => $prefix.'company_twitter',
				'show_help' => true,
				'type' => 'text',
				'class' => $prefix.'projectmeta_third'
			),
			(!(function_exists('is_id_pro') && is_id_pro())) ? array('type' => '') : array(
				'name' => __('Company Logo', 'ignitiondeck'),
				'id' => $prefix.'company_logo',
				'value' => $prefix.'company_logo',
				'type' => 'file',
				'class' => $prefix.'projectmeta_full'
			),
			array(
				'name' => __('Project Type', 'ignitiondeck'),
				'desc' => __('Pledge what you want or level based campaign. If you choose pledge what you want, only the first level will be used. If you choose level based, you can create as many levels as you need.', 'ignitiondeck'),
				'id' => $prefix.'project_type',
				'class' => $prefix . 'projectmeta_left',
				'show_help' => true,
				'options' => array(
					array(
						'name' => __('Pledge what you want', 'ignitiondeck'),
						'id' => 'pwyw',
						'value' => 'pwyw'
					),
					array(
						'name' => __('Level based', 'ignitiondeck'),
						'id' => 'level-based',
						'value' => 'level-based'
					)
				),
				'type' => 'radio'
			),
			array(
				'name' => __('Campaign End Options', 'ignitiondeck'),
				'desc' => __('Choose how to handle campaign end. Leave open to keep collecting payments, closed to remove pledge button.', 'ignitiondeck'),
				'id' => $prefix.'end_type',
				'class' => $prefix . 'projectmeta_right',
				'show_help' => true,
				'options' => array(
					array(
						'name' => __('Open', 'ignitiondeck'),
						'id' => 'open',
						'value' => 'open'
					),
					array(
						'name' => __('Closed', 'ignitiondeck'),
						'id' => 'closed',
						'value' => 'closed'
					)
				),
				'type' => 'radio'
			),
			array(
		        'name' => __('Funding Goal', 'ignitiondeck'),
		        'desc' => __('Amount you are seeking to raise (required)', 'ignitiondeck'),
		        'id' => $prefix . 'fund_goal',
		        'class' => $prefix . 'projectmeta_left_third',
		        'show_help' => true,
		        'type' => 'text_money'
		    ),
		    (!(function_exists('is_id_pro') && is_id_pro())) ? array('type' => '') : array(
				'name' => __('Proposed Start Date', 'ignitiondeck'),
				'desc' => __('The date the project creator wishes to start funding', 'ignitiondeck'),
				'id' => $prefix . 'start_date',
				'class' => $prefix . 'projectmeta_third',
				'show_help' => true,
				'type' => 'text_date'
			),
		    array(
		        'name' => __('Fundraising End Date', 'ignitiondeck'),
		        'desc' => __('Date funding will end (recommended)', 'ignitiondeck'),
		        'id' => $prefix . 'fund_end',
		        'class' => $prefix . 'projectmeta_third',
		        'show_help' => true,
		        'type' => 'text_date'
		    ),
			array(
		        'name' => __('Project Short Description', 'ignitiondeck'),
		        'desc' => __('Used in the grid, widget areas, and on the purchase form', 'ignitiondeck'),
		        'id' => $prefix . 'project_description',
		        'class' => $prefix . 'projectmeta_full',
		        'show_help' => true,
		        'type' => 'textarea_small'
		    ),
			array(
		        'name' => __('Project Long Description', 'ignitiondeck'),
		        'desc' => __('Supports HTML. Used on project pages', 'ignitiondeck'),
		        'id' => $prefix . 'project_long_description',
		        'class' => $prefix . 'projectmeta_full tinymce',
		        'show_help' => true,
		        'type' => 'textarea_medium'
		    ),
		    array(
		        'name' => __('Video Embed Code', 'ignitiondeck'),
		        'desc' => __('Video embed code using iframe or embed format (YouTube, Vimeo, etc)', 'ignitiondeck'),
		        'id' => $prefix . 'product_video',
		        'class' => $prefix . 'projectmeta_full',
		        'show_help' => true,
		        'type' => 'textarea_small'
		    ),
		    array(
		        'type' => 'headline1',
		        'class' => $prefix . 'projectmeta_headline1'
		    ),
		    array(
		        'type' => 'level1wraptop',
		        'class' => 'projectmeta_none'
		    ),
		    array(
		        'name' => __('Level Title', 'ignitiondeck'),
		        'id' => $prefix . 'product_title',
		        'class' => $prefix . 'projectmeta_reward_title',
		        'show_help' => false,
		        'type' => 'text'
		    ),
			array(
		        'name' => __('Level Price', 'ignitiondeck'),
		        'id' => $prefix . 'product_price',
		        'class' => $prefix . 'projectmeta_reward_price',
		        'type' => 'text_money'
		    ),
		    array(
		        'name' => __('Level Short Description', 'ignitiondeck'),
		        'desc' => __('Used in widgets sidebars, and in some cases, on the purchase form', 'ignitiondeck'),
		        'id' => $prefix . 'product_short_description',
		        'class' => $prefix . 'projectmeta_reward_desc',
		        'show_help' => true,
		        'type' => 'textarea_small'
		    ),
		    array(
		        'name' => __('Level Long Description', 'ignitiondeck'),
		        'desc' => __('For use on the project page and in level shortcodes/widgets', 'ignitiondeck'),
		        'id' => $prefix . 'product_details',
		        'class' => $prefix . 'projectmeta_reward_desc tinymce',
		        'show_help' => true,
		        'type' => 'textarea_medium'
		    ),
		    array(
		        'name' => __('Level Limit', 'ignitiondeck'),
		        'desc' => __('Restrict the number of buyers that can back this level', 'ignitiondeck'),
		        'id' => $prefix . 'product_limit',
		        'class' => $prefix . 'projectmeta_reward_limit',
		        'show_help' => true,
		        'type' => 'text_small'
		    ),
		    array(
		    	'name' => __('Level Order', 'ignitiondeck'),
		    	'desc' => __('Enter a number of 0 (first) or higher if you wish to customize the placement of this level', 'ignitiondeck'),
		    	'id' => $prefix.'projectmeta_level_order',
		    	'class' => $prefix . 'projectmeta_reward_limit',
		    	'show_help' => true,
		    	'type' => 'text_small'
		    ),
			array(
			    'type' => 'level1wrapbottom',
			    'class' => 'projectmeta_none'
			),
		    
		   
			array(
	            'name' => '<h4 class="ign_projectmeta_title">'.__('Additional Levels', 'ignitiondeck').'</h4>',
				'std' => '',
	            'id' => $prefix . 'level',
	            'class' => $prefix . 'projectmeta_full new_levels',
	            'show_help' => false,
	            'type' => 'product_levels'
	        ),	
	        array(
	            'name' => __('Level Management', 'ignitiondeck'),
	            'id' => $prefix . 'addlevels',
	            'class' => $prefix . 'projectmeta_full new_level',
	            'type' => 'add_levels',
	        ),
	        array(
	            'type' => 'headline2',
	            'class' => $prefix . 'projectmeta_headline2'
	        ),
			array(
		        'name' => __('Image 2', 'ignitiondeck'),
		        'desc' => __('Image 2 - Shortcode: [project_image product="{product_number}" image="2"]', 'ignitiondeck'),
		        'id' => $prefix . 'product_image2',
		        'class' => $prefix . 'projectmeta_left',
		        'show_help' => true,
		        'type' => 'file'
		    ),
			array(
		        'name' => __('Image 3', 'ignitiondeck'),
		        'desc' => __('Image 3 - Shortcode: [project_image product="{product_number}" image="3"]', 'ignitiondeck'),
		        'id' => $prefix . 'product_image3',
		        'class' => $prefix . 'projectmeta_left',
		        'show_help' => true,
		        'type' => 'file'
		    ),
			array(
		        'name' => __('Image 4', 'ignitiondeck'),
		        'desc' => __('Image 4 - Shortcode: [project_image product="{product_number}" image="4"]', 'ignitiondeck'),
		        'id' => $prefix . 'product_image4',
		        'class' => $prefix . 'projectmeta_left',
		        'show_help' => true,
		        'type' => 'file'
		    ),
			array(
	            'name' => __('Project FAQs', 'ignitiondeck'),
	           'desc' => __('List Project FAQs here', 'ignitiondeck'),
	            'id' => $prefix . 'faqs',
	            'class' => $prefix . 'projectmeta_full tinymce',
	            'show_help' => true,
	            'type' => 'textarea_medium'
	        ),
			array(
	            'name' => __('Project Updates', 'ignitiondeck'),
	            'desc' => __('List Project Updates here', 'ignitiondeck'),
	            'id' => $prefix . 'updates',
	            'class' => $prefix . 'projectmeta_full tinymce',
	            'show_help' => true,
	            'type' => 'textarea_medium'
	        ),
	    )
	);
	$fields = apply_filters('id_postmeta_box_fields', $id_meta_boxes['fields']);
	$id_meta_boxes['fields'] = $fields;
	$meta_boxes[] = $id_meta_boxes;
	return apply_filters('id_postmeta_boxes', $meta_boxes);
}
?>