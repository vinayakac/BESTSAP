<?php
add_action('idc_before_order_delete', 'mdid_delete_order_actions');

function mdid_delete_order_actions($order_id) {
	if ($order_id > 0) {
		$mdid_order = mdid_by_orderid($order_id);
		if (!empty($mdid_order->pay_info_id)) {
			ID_Order::delete_order($mdid_order->pay_info_id);
			
			// Removing mdid_order entry as well
			mdid_remove_order($order_id);
		}
	}
}
?>