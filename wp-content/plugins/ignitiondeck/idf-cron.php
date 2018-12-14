<?php
if (!wp_next_scheduled('schedule_twicedaily_idf_cron')) {
	wp_schedule_event(time(), 'twicedaily', 'schedule_twicedaily_idf_cron');
}
?>