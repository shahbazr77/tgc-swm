<?php
/**
 *
 */
swm_override_php_max_values();
class SWM_Shopify_Product
{
    private static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new SWM_Shopify_Product();
        }
        return self::$instance;
    }

    public function __construct()
    {
    }

    //get all shopify products
    public function get_all_shopify_products()
    {
        global $swm_woocommerce_product;
        $current_user_id = get_current_user_id();
        $result_per_request = get_option('wb_swm_result_per_request_'.$current_user_id) ? get_option('wb_swm_result_per_request_'.$current_user_id) : SWM_DEFAULT_RESULT_PER_REQUEST;
        swm_create_cache_directory('products');
        $url = swm_set_store_url_query().'products.json';
        $total_products = $this->count_total_shopify_product();
        $pages = ceil($total_products['count'] / $result_per_request);
        $products_list = array();
        $insert_as_wc = '';
        $product_directory_name = swm_get_cache_directory('products');
        $response = array();
        $response['total_products'] = $total_products['count'];
        $response['total_page_num'] = $pages;
        $import_records_options = array(
            'imported_page'	=> 0,
            'currently_imported_page'	=> 1,
            'total_pages' => $pages,
            'total_import_product' => 0,
            'total_shopify_products' => $total_products['count']
        );
        $import_records = get_option('swm_import_records_'.$current_user_id, $import_records_options);
        $url = add_query_arg(array(
                    'limit' => $result_per_request,
                ), $url);
        if (isset($import_records['next']) && !empty($import_records['next'])) {
            $url = add_query_arg(array(
                    'page_info' => $import_records['next'],
                ), $url);
        }
        $request = swm_remote_request($url);
        $result = isset($request['products']) ? $request['products'] : array() ;
        $add_product_cache = swm_create_cache_files($product_directory_name.'/products-cache-'.$import_records['currently_imported_page'].'.txt', json_encode($result));


        $insert_as_wc .= $swm_woocommerce_product->import_wc_product($result);

        $response['imported_products_title'] = $insert_as_wc;
        $swm_import_records = array(
            'imported_page'	=> $import_records['imported_page']+1,
            'currently_imported_page'	=> $import_records['currently_imported_page']+1,
            'total_pages' => $pages,
            'total_import_product' => $import_records['total_import_product'] + count($result),
            'total_shopify_products' => $total_products['count']
        );
        if (isset($request['next']) && !empty($request['next'])) {
            $swm_import_records['next'] = $request['next'];
        }
        update_option('swm_import_records_'.$current_user_id, $swm_import_records);
        $response['swm_import_records'] = get_option('swm_import_records_'.$current_user_id, $import_records_options);
        return $response;
    }

    public function get_product_by_collection_id($collection_id, $page_info='')
    {
        $product_list = array();
        $products_by_collection_id = $this->shopify_product_by_collection_query($collection_id, $page_info='');
        $product_list = isset($products_by_collection_id['products']) ? $products_by_collection_id['products'] : array();
        while (isset($products_by_collection_id['next']) && !empty($products_by_collection_id['next'])) {
            $page_info = $products_by_collection_id['next'];
            $products_by_collection_id = $this->shopify_product_by_collection_query($collection_id, $page_info);
            $products_list = isset($products_by_collection_id['products']) ? $products_by_collection_id['products'] : array();
            $product_list = array_merge($product_list, $products_list);
        }
        foreach ($product_list as $product) {
            $product_ids[] = $product['id'];
        }
        return $product_ids;
    }

    public function shopify_product_by_collection_query($collection_id, $page_info='')
    {
        $url = swm_set_store_url_query() . 'products.json';
        $url = add_query_arg(array(
                    'collection_id' => $collection_id,
                    'limit' => 250,
                    'fields' => 'id',
                ), $url);
        if (isset($page_info) && !empty($page_info)) {
            $url = remove_query_arg('collection_id', $url);
            $url = add_query_arg(array(
                'page_info' => $page_info,
            ), $url);
        }
        $products_by_collection_id = swm_remote_request($url);
        return $products_by_collection_id;
    }

    //get total count of shopify products
    public function count_total_shopify_product()
    {
        $url = swm_set_store_url_query().'products/count.json';
        $result = swm_remote_request($url);

        return $result;
    }

    public function count_total_product_by_collection_id($collection_id)
    {
        $url = swm_set_store_url_query() . 'products/count.json';
        $url = add_query_arg(array(
                        'collection_id' => $collection_id,
                    ), $url);
        $result = swm_remote_request($url);
        return $result;
    }

}
