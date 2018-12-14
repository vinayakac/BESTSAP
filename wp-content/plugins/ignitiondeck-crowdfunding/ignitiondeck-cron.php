<?php
if (!wp_next_scheduled('schedule_hourly_id_cron')) {
	wp_schedule_event(time(), 'hourly', 'schedule_hourly_id_cron');
}

function schedule_hourly_id_cron() {
	$raised = ID_Project::set_raised_meta();
	$percent = ID_Project::set_percent_meta();
	$days = ID_Project::set_days_meta();
	$closed = ID_Project::set_closed_meta();
}

add_action('schedule_hourly_id_cron', 'schedule_hourly_id_cron');
?>