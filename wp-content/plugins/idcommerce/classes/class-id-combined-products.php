<?php
/**
 * ID_Combined_Products class
 * This class handles all the combined product functions
 */
class ID_Combined_Products {
	private $product_id;
	private $combined_product_id;

	function __construct() {

	}

	/**
	 * function for initializing all hooks and attaching functions to it
	 */
	function init_hooks() {
		add_action('idc_make_combined_purchase', array($this, 'make_combined_purchase'), 2, 6);
	}

	/**
	 * This function/Action will be used to make a purchase of the combined recurring product
	 */
	public function make_combined_purchase($product_id, $source, $customer_id, $user_id, $plan, $level_data = "") {
		$this->product_id = $product_id;
		if (empty($level_data)) {
			$level_data = ID_Member_Level::get_level($product_id);
		}
		$this->combined_product_id = $level_data->combined_product;

		// If there is a combined product with this product, then make a purchase
		if ($this->combined_product_id) {
			$combined_level_data = ID_Member_Level::get_level($this->combined_product_id);
			// Selection of payment gateways and calling the appropriate function
			switch ($source) {
				case 'stripe':
					$this->stripe_recurring_purchase($combined_level_data, $customer_id, $user_id, $combined_level_data->plan);

					break;
				
				default:
					# code...
					break;
			}
		}
	}

	/**
	 * Function for making stripe purchase of recurring level
	 */
	public function stripe_recurring_purchase($level_data, $custid, $user_id, $plan) {
		try {
			$c = Stripe_Customer::retrieve($custid);
		}
		catch (Exception $e) {
			$message = $e->json_body['error']['message'].' '.__LINE__;
			print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
			exit;
		}
		//echo $custid;
		//print_r($c);
		// varchange
		try {
			$subscriptions = $c->subscriptions->retrieve($plan);
			$new_sub = false;
		}
		catch (Exception $e) {
			// new subscriber
			$new_sub = true;
		}
		try {
			$sub = $c->updateSubscription(array('plan' => $plan));
		}
		catch (Stripe_CardError $e) {
			//print_r($e);
			$message = $e->json_body['error']['message'].' '.__LINE__;
			print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
			exit;
		}
		catch (Stripe_InvalidRequestError $e) {
			$message = $e->jsonBody['error']['message'].' '.__LINE__;
			print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
			exit;
		}
		//print_r($sub);
		if ($sub->status == 'active' || $sub->status == 'trialing') {
			$txn_id = $sub->plan->id;
			//echo $txn_id;
			$success = true;
			if (isset($user_id) && $new_sub) {
				$new_sub = new ID_Member_Subscription(null, $user_id, $level_data->id, $sub->id, 'stripe');
				$filed_sub = $new_sub->add_subscription();

			}
		}
		// $start = $sub->start;
	}
}

// Initializing a class for attaching hooks
$id_combined_products = new ID_Combined_Products();
$id_combined_products->init_hooks();
?>