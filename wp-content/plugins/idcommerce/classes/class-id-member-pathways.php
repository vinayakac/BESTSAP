<?php

class ID_Member_Pathways {
	var $id;
	var $product_id;
	var $pathway_id;
	var $pathway_name;
	var $upgrade_pathway;
	private $debug_mode = false;

	function __construct(
		$id = null,
		$product_id = null,
		$pathway_id = null,
		$pathway_name = null,
		$upgrade_pathway = null)
	{
		$this->id = $id;
		$this->product_id = $product_id;
		$this->pathway_id = $pathway_id;
		$this->pathway_name = $pathway_name;
		$this->upgrade_pathway = $upgrade_pathway;
	}

	function LOGShow($variable_name, $variable_value, $is_array, $add_pre_tag, $newline_char="<br>") {
		if ($this->debug_mode) {
			$add_pre_tag = false;
			$add_pre_tag_array = true;
			if ($add_pre_tag || ($add_pre_tag_array && $is_array)) {
				echo "<pre>";
			}
			if ($is_array) {
				echo $variable_name.": "; print_r($variable_value); echo $newline_char;
			}
			else {
				echo $variable_name.": "; echo $variable_value; echo $newline_char;
			}
			if ($add_pre_tag || ($add_pre_tag_array && $is_array)) {
				echo "</pre>";
			}
		}
	}

	function get_pathway() {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_upgrade_pathways WHERE id = %d', $this->id);
		$res = $wpdb->get_row($sql);
		if (!empty($res)) {
			$res->upgrade_pathway = maybe_unserialize($res->upgrade_pathway);
		}
		return $res;
	}

	function get_pathways() {
		global $wpdb;
		$sql = 'SELECT * FROM '.$wpdb->prefix.'memberdeck_upgrade_pathways';
		$res = $wpdb->get_results($sql);
		return $res;
	}

	function get_product_pathway() {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_product_pathway WHERE product_id = %d', $this->product_id);
		$this->LOGShow('get_product_pathway sql', $sql, false, false);
		$res = $wpdb->get_row($sql);
		if (!empty($res)) {
			$this->id = $res->pathway_id;
			$product_pathway = $this->get_pathway();
			return $product_pathway;
		}
		return false;
	}

	/**
	 * This will check if any of the products coming in array exists in any pathway
	 * $product_ids: array of product/level ids
	 * return records if exists, or false
	 */
	function check_product_pathway_exists($product_ids) {
		global $wpdb;
		$product_ids_str = implode(",", $product_ids);
		$sql = 'SELECT * FROM '.$wpdb->prefix.'memberdeck_product_pathway WHERE product_id IN ('.$product_ids_str.') LIMIT 1';
		$res = $wpdb->get_row($sql);
		if (count($res) > 0) {
			return $res;
		} else {
			return false;
		}
	}

	function add_pathway() {
		global $wpdb;
		$result = $wpdb->insert( $wpdb->prefix."memberdeck_upgrade_pathways", array(
			'pathway_name' => $this->pathway_name,
			'upgrade_pathway' => maybe_serialize($this->upgrade_pathway)
		) );
		if (!$result) {
			return false;
		} else {
			return $wpdb->insert_id;
		}
	}

	function add_product_pathway_relation() {
		global $wpdb;
		$result = $wpdb->insert( $wpdb->prefix."memberdeck_product_pathway", array(
			'product_id' => $this->product_id,
			'pathway_id' => $this->pathway_id
		) );
		if (!$result) {
			return false;
		} else {
			return $wpdb->insert_id;
		}
	}

	function update_product_pathway() {
		global $wpdb;
		// Check first, if pathway for current product already exists, if not, insert a new entry
		$product_pathways = $this->get_product_pathway();
		if (!empty($product_pathways)) {
			// Pathway exists, so upgrade
			$wpdb->update( 
				$wpdb->prefix."memberdeck_upgrade_pathways", 
				array(
					'pathway_name' => $this->pathway_name,
					'upgrade_pathway' => maybe_serialize($this->upgrade_pathway)
				), 
				array( 'id' => $product_pathways->id ), 
				array('%s', '%s'), 
				array('%d') 
			);
		}
		else {
			// Pathway doesn't exist, insert a new one
			$new_path = $this->add_product_pathway();
		}
	}

	function update_pathway() {
		global $wpdb;
		$wpdb->update( 
			$wpdb->prefix."memberdeck_upgrade_pathways", 
			array(
				'pathway_name' => $this->pathway_name,
				'upgrade_pathway' => maybe_serialize($this->upgrade_pathway)
			), 
			array( 'id' => $this->id ), 
			array('%s', '%s'), 
			array('%d') 
		);
	}

	function delete_pathway() {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix."memberdeck_upgrade_pathways", array( 'id' => $this->id ) );
	}

	function delete_product_pathway() {
		global $wpdb;
		// Using class variable of product_id, we get it's pathway, and if found, delete it
		$product_pathway = $this->get_product_pathway();
		if (!empty($product_pathway)) {
			$wpdb->delete( $wpdb->prefix."memberdeck_upgrade_pathways", array( 'id' => $product_pathway->id ) );
		}
	}

	function delete_product_pathway_relations() {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix."memberdeck_product_pathway", array( 'pathway_id' => $this->pathway_id ) );
	}

	/**
	 * Function to get the product price difference with the lower level in pathway
	 * @get_lower_level_only	This will return lower level only on this->product_id
	 * @get_order_exists_only	This will return whether lower level order exists or not
	 * $this (class) variables used by this function
	 * 		product_id
	 * returns multiple values but default ones are
	 * 		(integer) difference if everything turns valid
	 * 		(boolean) false otherwise
	 */
	function get_lower_product_difference($level_price, $user_id, $get_lower_level_only = false, $get_order_exists_only = false) {
		global $wpdb;

		// Get pathway in which this level exists
		$product_pathway = $this->get_product_pathway();
		$this->LOGShow('get_lower_product_difference() product_pathway', $product_pathway, true, false);

		if (!empty($product_pathway)) {
			// Now in pathway, find which product is lower than this->product_id, so getting details of all products/level
			$pathway_levels = ID_Member_Level::get_multiple_levels($product_pathway->upgrade_pathway);
			$this->LOGShow('pathway_levels', $pathway_levels, true, false);
			// Looping those levels and finding which one's price is lower than the current level's
			$lower_level_price = $level_price;
			$found_one = false;
			for ($i=0 ; $i < count($pathway_levels) ; $i++) { 
				// this level_id in loop on not this->product_id
				$this->LOGShow('pathway_levels['.$i.']->id', $pathway_levels[$i]->id, false, false, ', ');
				$this->LOGShow('this->product_id', $this->product_id, false, false);

				if ($pathway_levels[$i]->id != $this->product_id) {
					// IF level_id in loop has price less than this->product_id price but greater than all other products, then that's a lower level product
					// so we will get this product
					$this->LOGShow('pathway_levels['.$i.']->level_price', $pathway_levels[$i]->level_price, false, false, ', ');
					$this->LOGShow('lower_level_price', $lower_level_price, false, false);
					$this->LOGShow('found_one', $found_one, false, false);
					if ( $pathway_levels[$i]->level_price < $lower_level_price && !$found_one ) {
						$this->LOGShow('inside first if', '', false, false);
						$lower_level_price = $pathway_levels[$i]->level_price;
						$lower_level_index = $i;
						$found_one = true;
					}
					else if ( $pathway_levels[$i]->level_price > $lower_level_price && $pathway_levels[$i]->level_price < $level_price && $found_one ) {
						$this->LOGShow('inside 2nd if', '', false, false);
						$lower_level_price = $pathway_levels[$i]->level_price;
						$lower_level_index = $i;
					}
				}
			}
			$this->LOGShow('lower_level_price', $lower_level_price, false, false);
			$this->LOGShow('lower_level_index', ((isset($lower_level_index)) ? $lower_level_index : ''), false, false);

			// If we found a level
			if (isset($lower_level_index)) {
				$lower_level = $pathway_levels[$lower_level_index];
				// If we need only lower level id, and stop this function here
				if ($get_lower_level_only) {
					return $lower_level;
				}
				$this->LOGShow('lower_level', $lower_level, true, false);

				// First check that order exists for the lower product ($product_id) for this user
				if (!empty($user_id)) {
					// If user_id is coming in arguments, user is logged in, otherwise the user is new
					$idc_order = new ID_Member_Order(null, $user_id, $lower_level->id);
					$last_order = $idc_order->get_last_order();
					$this->LOGShow('get_lower_product_difference(): last_order', $last_order, true, false);
					if (!empty($last_order)) {
						$order_exists = true;
					} else {
						$order_exists = false;
					}
				}
				// The user is new, so no previous order exists
				else {
					$order_exists = false;
				}
				// If we need to stop this function here and return if order exits of lower level or not
				if ($get_order_exists_only) {
					return $order_exists;
				}
				$this->LOGShow('order_exists', $order_exists, false, false);

				// Now if order exists, then we will use difference otherwise return false
				if (isset($order_exists) && $order_exists) {
					// get the price difference of the 2 levels
					$difference = $level_price - $lower_level->level_price;
					$this->LOGShow('difference', $difference, false, false);
					if ($difference > 0) {
						return $difference;
					} else {
						return $level_price;
					}
				}
				else {
					return 0;
				}
			}
			else {
				// No lower level product
				return 0;
			}
		}
		else {
			return false;
		}
	}

	function cancel_order_on_upgrade_pathways($order_id, $user_id) {
		global $wpdb;

		// Getting this->product_id
		$idc_order = new ID_Member_Order($order_id);
		$current_order = $idc_order->get_order();
		$this->product_id = $current_order->level_id;
		// Getting level to get its price
		$level = ID_Member_Level::get_level($this->product_id);

		$product_pathway = $this->get_product_pathway();
		// If pathway exists, check that lower level exists too
		if (!empty($product_pathway)) {
			// Getting the lower level
			$this->upgrade_pathway = $product_pathway->upgrade_pathway;
			$lower_level = $this->get_lower_product_difference($level->level_price, $user_id, $get_lower_level_only = true);
			// If lower_level exists, see if it is ordered, if yes, cancel that order
			if ($lower_level) {
				$idc_order = new ID_Member_Order(null, $user_id, $lower_level->id);
				$last_order = $idc_order->get_last_order();
				$this->LOGShow('cancel_order_on_upgrade_pathways(): last_order', $last_order, true, false);
				if (!empty($last_order)) {
					// Cancelling this last_order
					$idc_order->id = $last_order->id;
					$this->LOGShow('cancelling idc_order', $idc_order, true, false);
					$idc_order->cancel_status();

					// Updating new orders expiration date to older orders expiration date
					$expiry_date = $last_order->e_date;
					ID_Member_Order::update_order_date($order_id, $expiry_date);

					do_action('idc_upgrade_cancel_order_success', $order_id, $user_id, $last_order->id, $level->id, $lower_level->id);
					return true;
				}
			}
			return false;
		}
		return false;
	}
}

add_action('wp_ajax_idc_pathway_details', 'idc_pathway_details');
function idc_pathway_details() {
	$pathway_id = $_POST['pathway_id'];
	$idc_pathways = new ID_Member_Pathways($pathway_id);
	$pathway = $idc_pathways->get_pathway();
	$idc_pathways->LOGShow('pathway', $pathway, true, false);
	// making the response
	if (!empty($pathway)) {
		$response = array("upgrade_pathways" => $pathway->upgrade_pathway, "pathway_name" => $pathway->pathway_name, "response" => "success");
		echo json_encode($response);
	} else {
		echo json_encode(array("response" => "failure"));
	}

	exit();
}

add_action('idc_upgrade_cancel_order_success', 'idc_credits_check_on_upgrade', 10, 5);
function idc_credits_check_on_upgrade($order_id, $user_id, $lower_order_id, $level_id, $lower_level_id) {
	// We will subtract specific amount of credits added, due to newer level is an upgrade, first check how many credits associated with lower level
	$lower_level_credit = ID_Member_Credit::get_credit_by_level($lower_level_id);
	// // Now getting newer level credits
	$level_credit = ID_Member_Credit::get_credit_by_level($level_id);

	// Now getting the amount of credits that should have been added instead
	if (!empty($lower_level_credit) && !empty($level_credit)) {
		// 95 + 5 + 12 = 112
		// but added should be: 95 + 5 + (12 - 5) = 107
		// so 112 - 5 = 107
		$to_subtract = $lower_level_credit->credit_count;
		// Getting credits
		$member = new ID_Member($user_id);
		$user_credits = $member->get_user_credits();
		$new_credits = $user_credits - $to_subtract;
		// Updating new credits
		$member->set_credits($user_id, $new_credits);
	}
}
?>