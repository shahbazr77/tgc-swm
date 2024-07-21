<?php
/**
 * 
 */
swm_override_php_max_values();
class SWM_Shopify_Product_Cat
{
	private static $instance;

	public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new SWM_Shopify_Product_Cat();
        }
        return self::$instance;
    }
	
	function __construct() { }

	//get all shopify products
	public function get_all_shopify_custom_collection(){
		global $swm_woocommerce_product_cat;
        $current_user_id = get_current_user_id();
		$result_per_request = get_option('wb_swm_cats_per_request_'.$current_user_id) ? get_option('wb_swm_cats_per_request_'.$current_user_id) : SWM_DEFAULT_CAT_PER_REQUEST;

		swm_create_cache_directory('custom-collections');

		$url = swm_set_store_url_query().'custom_collections.json';
		$total_collections = $this->count_total_shopify_custom_collections();
		$pages = ceil( $total_collections['count'] / $result_per_request );

		$collection_list = array();
		$insert_as_wc = '';
		$collection_directory_name = swm_get_cache_directory('custom-collections');

		$response = array();
		$response['total_collection'] = $total_collections['count'];
		$response['total_collections_num'] = $pages;
        $current_user_id = get_current_user_id();
		$import_records_options = array(
			'imported_page'	=> 0,
			'currently_imported_page'	=> 1,
			'total_pages' => $pages,
			'total_import_collections' => 0,
			'total_shopify_collections' => $total_collections['count']
		);
		$import_records = get_option('swm_import_custom_collection_records_'.$current_user_id, $import_records_options);

		$url = add_query_arg(array(
					'limit' => $result_per_request,
				), $url);

		if( isset($import_records['next']) && !empty($import_records['next']) ){
			$url = add_query_arg(array(
					'page_info' => $import_records['next'],
				), $url);
		}
		$request = swm_remote_request( $url );
		$result = isset( $request['custom_collections'] ) ? $request['custom_collections'] : array() ;
		$add_product_cache = swm_create_cache_files( $collection_directory_name.'/custom-collections-cache-'.$import_records['currently_imported_page'].'.txt', json_encode($result) );

		$insert_as_wc .= $swm_woocommerce_product_cat->import_wc_product_cats($result);
		$response['imported_custom_collection_title'] = $insert_as_wc;
		$swm_import_custom_collection_records = array(
			'imported_page'	=> $import_records['imported_page']+1,
			'currently_imported_page'	=> $import_records['currently_imported_page']+1,
			'total_pages' => $pages,
			'total_import_collections' => $import_records['total_import_collections'] + count($result),
			'total_shopify_collections' => $total_collections['count']
		);

		if( isset($request['next']) && !empty($request['next']) ){
			$swm_import_custom_collection_records['next'] = $request['next'];
		}
		
		update_option('swm_import_custom_collection_records_'.$current_user_id, $swm_import_custom_collection_records );

		$response['swm_import_custom_collection_records'] = get_option('swm_import_custom_collection_records_'.$current_user_id, $import_records_options);
		
		return $response;
		
	}

	//get total count of shopify products
	public function count_total_shopify_custom_collections(){
		$url = swm_set_store_url_query().'custom_collections/count.json';
		$result = swm_remote_request( $url );

		return $result;
	}

}
