<?php

function custom_cron_intervals($schedules) {
    $schedules['dailyrun'] = array(
        'interval' => 86400, // 24 hours in seconds
        'display'  => __('Once Daily')
    );
    return $schedules;
}
add_filter('cron_schedules', 'custom_cron_intervals');

function shopify_cron_function() {
    global $swm_shopify_product;

    $response = array();

    $create_cache_folder = swm_create_cache_directory();
    if ($create_cache_folder === 'write_error') {
        $response['write_error'] = true;
    }
    $response = array_merge($response, $swm_shopify_product->get_all_shopify_products());

    if (isset($response['swm_import_records']) && $response['swm_import_records']['imported_page'] >= $response['swm_import_records']['total_pages']) {
        delete_option('swm_import_records');
        $response['status'] = 'completed';
    }

    // Reschedule the cron job to run again in 30 minutes
    wp_schedule_event(current_time('timestamp') + 86400, 'dailyrun', 'shopify_importer_product');
}
// Schedule the cron job to run every 30 minutes
if (!wp_next_scheduled('shopify_importer_product')) {
    wp_schedule_event(current_time('timestamp') + 86400, 'dailyrun', 'shopify_importer_product');
}
add_action('shopify_importer_product', 'shopify_cron_function');
