<?php
/**
 *
 */
class SWM_Ajax_Handler
{
    public function __construct()
    {
        swm_override_php_max_values();
        add_action('wp_ajax_swm_import_product', array($this, 'swm_import_product'));
        add_action('wp_ajax_swm_import_custom_collection', array($this, 'swm_import_custom_collection'));
    }

    public function swm_import_product()
    {
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
        wp_send_json($response);
        wp_die();
    }


    public function swm_import_custom_collection()
    {
        global $swm_shopify_product_cat;
        $response = array();

        $response = array_merge($response, $swm_shopify_product_cat->get_all_shopify_custom_collection());

        if (isset($response['swm_import_custom_collection_records']) && $response['swm_import_custom_collection_records']['imported_page'] >= $response['swm_import_custom_collection_records']['total_pages']) {
            delete_option('swm_import_custom_collection_records');
            $response['status'] = 'completed';
        }
        wp_send_json($response);
        wp_die();
    }
}
