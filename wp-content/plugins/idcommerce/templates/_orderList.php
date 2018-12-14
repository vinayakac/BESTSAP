<ul class="md-box-wrapper full-width cf">
	<li class="md-box">
		<div class="md-profile">
			<table>
				<tr>
					<th><?php _e('Order ID', 'memberdeck'); ?></th>
					<th><?php _e('Order Date', 'memberdeck'); ?></th>
					<th><?php _e('Product Name', 'memberdeck'); ?></th>
					<th><?php _e('Amount', 'memberdeck'); ?></th>
					<th><?php _e('Transaction ID', 'memberdeck'); ?></th>
					<th><?php _e('Actions', 'memberdeck'); ?></th>
				</tr>
				<?php $i = 1; ?>
				<?php 
					foreach ($orders as $order) {
						$j = 0;
						foreach ($levels as $level) {
							$level_id = $level->id;
							if ($order->level_id == $level_id) {
								$order_level_key = $j;
								break;
							}
							$j++;
						}
						$price = $order->price;
						if ($crowdfunding) {
							$mdid_order = mdid_by_orderid($order->id);
							if (!empty($mdid_order)) {
								$meta = ID_Member_Order::get_order_meta($order->id, 'gateway_info', true);
								if (!empty($meta)) {
									if ($meta['gateway'] == 'credit') {
										$price = $levels[$order_level_key]->credit_value;
										$pay_id = $mdid_order->pay_info_id;
										$id_order = new ID_Order($pay_id);
										$the_order = $id_order->get_order();
										if (!empty($the_order)) {
											$project_id = $the_order->product_id;
											if ($project_id > 0) {
												$project = new ID_Project($project_id);
												$post_id = $project->get_project_postid();
												$closed = get_post_meta($post_id, 'ign_project_closed', true);
											}
										}
									}
								}
							}
						}

					$time_zone = new DateTimeZone($default_timezone);
					$datetime = new DateTime($order->order_date);
					$datetime->setTimezone($time_zone);
				?>
				<tr>
					<td><?php echo '100'.$order->id; ?></td>
					<td><?php echo date('F d, Y', strtotime($datetime->format('Y-m-d H:i:s'))); ?></td>
					<td><?php echo (isset($levels[$order_level_key]) ? $levels[$order_level_key]->level_name : ''); ?></td>
					<td><?php echo apply_filters('idc_order_price', $price, $order->id); ?></td>
					<!--<td><?php echo apply_filters('idc_currency_order_meta', apply_filters('idc_currency_format', $price, ''), $order->id); ?></td>-->
					<td><?php echo $order->transaction_id; ?></td>
					<td>
						<table>
							<tr>
							<?php if (isset($closed) && !$closed) { ?>
							<a href="<?php echo md_get_durl().$prefix; ?>idc_orders=1&amp;cancel_pledge=<?php echo $order->id; ?>&amp;pay_id=<?php echo $pay_id; ?>&amp;level=<?php echo $order->level_id; ?>" title="Cancel Pledge" class="cancel-pledge"><i class="fa fa-times"></i></a>
							<?php } else { ?>
							<!-- &nbsp;  <span style="width: 14px; display: inline-block;"></span> -- > <!-- commenting it for review -->
							<?php } ?>
							<a href="?idc_orders=1&amp;view_receipt=<?php echo $order->id; ?>" title="View Receipt"><i class="fa fa-file-text-o"></i></a>
							</tr>
						</table>
					</td>
				</tr>
				<?php $i++; } ?>
			</table>
		</div>
	</li>
</ul>