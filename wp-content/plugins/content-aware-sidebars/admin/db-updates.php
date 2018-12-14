<?php
/**
 * @package Content Aware Sidebars
 * @author Joachim Jensen <jv@intox.dk>
 * @license GPLv3
 * @copyright 2018 by Joachim Jensen
 */

if (!defined('ABSPATH')) {
	exit;
}

$cas_db_updater = new WP_DB_Updater('cas_db_version',CAS_App::PLUGIN_VERSION, true);
$cas_db_updater->register_version_update('1.1','cas_update_to_11');
$cas_db_updater->register_version_update('2.0','cas_update_to_20');
$cas_db_updater->register_version_update('3.0','cas_update_to_30');
$cas_db_updater->register_version_update('3.1','cas_update_to_31');
$cas_db_updater->register_version_update('3.4','cas_update_to_34');
$cas_db_updater->register_version_update('3.5.1','cas_update_to_351');
$cas_db_updater->register_version_update('3.7.2','cas_update_to_372');

/**
 * Update to version 3.7.2
 *
 * @since  3.7.2
 * @return boolean
 */
function cas_update_to_372() {
	global $wpdb;

	$time = time();

	$wpdb->query("
		UPDATE $wpdb->usermeta AS t 
		INNER JOIN $wpdb->usermeta AS r ON t.user_id = r.user_id 
		SET t.meta_value = '{$time}' 
		WHERE t.meta_key = '{$wpdb->prefix}_ca_cas_tour' 
		AND r.meta_key = '{$wpdb->prefix}_ca_cas_review' 
		AND r.meta_value != '1' 
		AND CAST(r.meta_value AS DECIMAL) <= 1491004800
	");

	$wpdb->query("
		DELETE FROM $wpdb->usermeta 
		WHERE meta_key = '{$wpdb->prefix}_ca_cas_review' 
		AND meta_value != '1' 
		AND CAST(meta_value AS DECIMAL) <= 1491004800
	");

	return true;
}

/**
 * Update to version 3.5.1
 * Simplify auto select option
 *
 * @since  3.5.1
 * @return boolean
 */
function cas_update_to_351() {
	global $wpdb;

	$group_ids = $wpdb->get_col("SELECT post_id FROM $wpdb->postmeta WHERE meta_value LIKE '_ca_sub_%'");
	foreach ($group_ids as $group_id) {
		add_post_meta($group_id,'_ca_autoselect',1,true);
	}

	$wpdb->query("
		DELETE FROM $wpdb->postmeta 
		WHERE meta_value LIKE '_ca_sub_%'
	");

	return true;
}


/**
 * Version 3.3.3 -> 3.4
 * Inherit condition exposure from sidebar
 * Remove sidebar exposure
 *
 * @since  3.4
 * @return boolean
 */
function cas_update_to_34() {
	global $wpdb;

	$wpdb->query("
		UPDATE $wpdb->posts AS c
		INNER JOIN $wpdb->posts AS s ON s.ID = c.post_parent
		INNER JOIN $wpdb->postmeta AS e ON e.post_id = s.ID
		SET c.menu_order = e.meta_value
		WHERE c.post_type = 'condition_group'
		AND e.meta_key = '_ca_exposure'
	");

	$wpdb->query("
		DELETE FROM $wpdb->postmeta 
		WHERE meta_key = '_ca_exposure'
	");

	wp_cache_flush();

	return true;
}

/**
 * Version 3.0 -> 3.1
 * Remove flag about plugin tour for all users
 *
 * @since  3.1
 * @return boolean
 */
function cas_update_to_31() {
	global $wpdb;
	$wpdb->query("
		DELETE FROM $wpdb->usermeta
		WHERE meta_key = '{$wpdb->prefix}_ca_cas_tour'
	");
	return true;
}

/**
 * Version 2.0 -> 3.0
 * Data key prefices will use that from WP Content Aware Engine
 * Condition group post type made generic
 * Module id convention made consistent
 *
 * @since  3.0
 * @return boolean
 */
function cas_update_to_30() {
	global $wpdb;

	// Get all sidebars
	$posts = get_posts(array(
		'numberposts'     => -1,
		'post_type'       => 'sidebar',
		'post_status'     => 'publish,pending,draft,future,private,trash'
	));

	if(!empty($posts)) {

		$wpdb->query("
			UPDATE $wpdb->posts
			SET post_type = 'condition_group', post_status = 'publish'
			WHERE post_type = 'sidebar_group'
		");

		$metadata = array(
			'post_types'     => 'post_type',
			'taxonomies'     => 'taxonomy',
			'authors'        => 'author',
			'page_templates' => 'page_template',
			'static'         => 'static',
			'bb_profile'     => 'bb_profile',
			'bp_member'      => 'bp_member',
			'date'           => 'date',
			'language'       => 'language',
			'exposure'       => 'exposure',
			'handle'         => 'handle',
			'host'           => 'host',
			'merge-pos'      => 'merge_pos'
		);
		
		foreach($metadata as $old_key => $new_key) {
			$wpdb->query("
				UPDATE $wpdb->postmeta 
				SET meta_key = '_ca_".$new_key."' 
				WHERE meta_key = '_cas_".$old_key."'
			");
			switch($new_key) {
				case 'author':
				case 'page_template':
					$wpdb->query("
						UPDATE $wpdb->postmeta 
						SET meta_value = '".$new_key."' 
						WHERE meta_key = '_ca_".$new_key."' 
						AND meta_value = '".$old_key."'
					");
					break;
				case 'post_type':
				case 'taxonomy':
					$wpdb->query("
						UPDATE $wpdb->postmeta 
						SET meta_value = REPLACE(meta_value, '_cas_sub_', '_ca_sub_') 
						WHERE meta_key = '_ca_".$new_key."' 
						AND meta_value LIKE '_cas_sub_%'
					");
					break;
			}
		}

		// Clear cache for new meta keys
		wp_cache_flush();
	}

	return true;
}

/**
 * Version 1.1 -> 2.0
 * Moves module data for each sidebar to a condition group
 * 
 * @author Joachim Jensen <jv@intox.dk>
 * @since  2.0
 * @return boolean
 */
function cas_update_to_20() {
	global $wpdb;

	$module_keys = array(
		'static',
		'post_types',
		'authors',
		'page_templates',
		'taxonomies',
		'language',
		'bb_profile',
		'bp_member'
	);

	// Get all sidebars
	$posts = get_posts(array(
		'numberposts'     => -1,
		'post_type'       => 'sidebar',
		'post_status'     => 'publish,pending,draft,future,private,trash'
	));
	if(!empty($posts)) {
		foreach($posts as $post) {

			//Create new condition group
			$group_id = wp_insert_post(array(
				'post_status'           => $post->post_status, 
				'post_type'             => 'sidebar_group',
				'post_author'           => $post->post_author,
				'post_parent'           => $post->ID,
			));

			if($group_id) {

				//Move module data to condition group
				$wpdb->query("
					UPDATE $wpdb->postmeta 
					SET post_id = '".$group_id."' 
					WHERE meta_key IN ('_cas_".implode("','_cas_",$module_keys)."')
					AND post_id = '".$post->ID."'
				");

				//Move term data to condition group
				$wpdb->query("
					UPDATE $wpdb->term_relationships 
					SET object_id = '".$group_id."' 
					WHERE object_id = '".$post->ID."'
				");

			}

		}		
	}

	return true;

}

/**
 * Version 0.8 -> 1.1
 * Serialized metadata gets their own rows
 * 
 * @return boolean 
 */
function cas_update_to_11() {
	
	$moduledata = array(
		'static',
		'post_types',
		'authors',
		'page_templates',
		'taxonomies',
		'language'
	);
	
	// Get all sidebars
	$posts = get_posts(array(
		'numberposts'     => -1,
		'post_type'       => 'sidebar',
		'post_status'     => 'publish,pending,draft,future,private,trash'
	));
	
	if(!empty($posts)) {
		foreach($posts as $post) {
			foreach($moduledata as $field) {
				// Remove old serialized data and insert it again properly
				$old = get_post_meta($post->ID, '_cas_'.$field, true);
				if($old != '') {
					delete_post_meta($post->ID, '_cas_'.$field, $old);
					foreach((array)$old as $new_single) {
						add_post_meta($post->ID, '_cas_'.$field, $new_single);
					}
				}
			}
		}
	}
	
	return true;
}

//eol