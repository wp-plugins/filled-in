<?php

function filled_in_cron_delete_failed_sumbmitions() {
  if( !get_option ('filled_in_cron_delete_failed') )
    return;
  
  global $wpdb;
  
  $sql = "DELETE e.*, d.* FROM {$wpdb->prefix}filled_in_errors as e
          LEFT JOIN {$wpdb->prefix}filled_in_data as d  ON e.data_id = d.id
          WHERE d.created < (NOW() - INTERVAL 1 MONTH )";
  $wpdb->query ($sql);

  //DELETE FROM wp_filled_in_useragents WHERE id NOT IN ( SELECT id FROM wp_filled_in_data WHERE 1)
  
  $sql = "OPTIMIZE TABLE {$wpdb->prefix}filled_in_errors, {$wpdb->prefix}filled_in_data;";
  $wpdb->query ($sql);
  
  update_option ('filled_in_cron_delete_failed_last_run', time() );
}
add_action('filled_in_cron_delete_failed_sumbmitions_event', 'filled_in_cron_delete_failed_sumbmitions');

function filled_in_deactivation() {
  wp_clear_scheduled_hook('filled_in_cron_delete_failed_sumbmitions_event');
}
register_deactivation_hook( dirname(dirname(__FILE__))."/filled_in.php", 'filled_in_deactivation');

if ( !wp_next_scheduled( 'filled_in_cron_delete_failed_sumbmitions_event' ) ) {
  wp_schedule_event( time() + 3600, 'daily', 'filled_in_cron_delete_failed_sumbmitions_event' );
}

?>