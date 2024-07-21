<?php
/**
 * 
 */
swm_override_php_max_values();
class SWM_WC_Product
{
	private static $instance;

	protected $process;

	public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new SWM_WC_Product();
        }
        return self::$instance;
    }
	
	function __construct() {
		
	}

	public function import_wc_product($shopify_product_array){
		$import_result = '';
		$product_image_download=0;
        $current_user = wp_get_current_user();
        $new_meta_value = $current_user->user_login;
        global $wpdb;
        $table_name = $wpdb->prefix.'shopify_custom_table_ids';
		for( $i = 0; $i < count($shopify_product_array); $i++ ){
			$shopify_product = $shopify_product_array[$i];
            $current_user_id = get_current_user_id();
			//$shopify_product = $shopify_product_array;
            $shopify_id = isset( $shopify_product['id'] ) ? sanitize_text_field($shopify_product['id']) : '';
            $post_title = isset( $shopify_product['title'] ) ? sanitize_text_field($shopify_product['title']) : '';
			$post_content = isset( $shopify_product['body_html'] ) ? wp_slash($shopify_product['body_html']) : '';
			$post_name = isset( $shopify_product['handle'] ) ? sanitize_text_field($shopify_product['handle']) : '';
			$post_default_status = swm_get_option( 'wb_swm_product_status_'.$current_user_id ) ? swm_get_option( 'wb_swm_product_status_'.$current_user_id ) : 'draft';
			$product_categories = swm_get_option( 'wb_swm_product_categories_'.$current_user_id ) ? swm_get_option( 'wb_swm_product_categories_'.$current_user_id) : '';
			$default_product_tags = swm_get_option( 'wb_swm_product_tags_'.$current_user_id ) ? swm_get_option( 'wb_swm_product_tags_'.$current_user_id ) : array();
			$post_params = array(
			    'post_type' => "product",
			    'post_title' => $post_title,
			    'post_name' => $post_name,
			    'post_content' => $post_content,
			    'post_status' => $post_default_status,
			    'post_parent' => '',
                'post_author'   => $current_user_id,
			);
            $result = $wpdb->get_row( 'SELECT * FROM '.$table_name.'  where shopify_ids="'.$shopify_id.'"');
            if (!empty($result)) {
                $product_ids= $result->tgc_wp_ids;
                if (wc_get_product($product_ids)) {
                    $post_params['ID'] = $product_ids;
                    $post_id = wp_update_post( $post_params );
                    $product_image_download=1;
                } else {
                    $wpdb->query( 'DELETE FROM ' . $table_name . ' WHERE shopify_ids = ' . $shopify_id );
                    $post_id = wp_insert_post( $post_params );
                    $shopify_ids_converted = intval($shopify_id);
                    $data_to_insert = array(
                        'shopify_ids' => $shopify_ids_converted,
                        'tgc_wp_ids' => $post_id,
                    );
                    $wpdb->insert($table_name, $data_to_insert , array( '%d', '%d' ));

                    update_post_meta( $post_id, 'woo_dropshipper', $new_meta_value);

                    update_post_meta( $post_id, 'woo_drop_author', get_current_user_id());


                }

            } else {
                $post_id = wp_insert_post( $post_params );
                $shopify_ids_converted = intval($shopify_id);
                $data_to_insert = array(
                    'shopify_ids' => $shopify_ids_converted,
                    'tgc_wp_ids' => $post_id,
                );
                $wpdb->insert($table_name, $data_to_insert , array( '%d', '%d' ));
               // echo "ID $shopify_id does not exist in the table $table_name.";
                update_post_meta( $post_id, 'woo_dropshipper', $new_meta_value);
                update_post_meta( $post_id, 'woo_drop_author', get_current_user_id());
                
            }
			if( !is_wp_error($post_id) && $post_id ){
				update_post_meta($post_id, 'swm_import_type', 'swm_shopify_product');
				if( count($shopify_product['variants']) == 1 ){
					//$import_result .= '<strong>simple</strong> ';
					$this->create_simple_product($post_id, $shopify_product,$product_image_download);
				}elseif ( count($shopify_product['variants']) >1 ) {
					//$import_result .= '<strong>variable </strong> ';
					$this->create_variable_product($post_id, $shopify_product,$product_image_download);
				}

				if( isset($shopify_product['product_type']) && !empty($shopify_product['product_type']) ){
					wp_set_object_terms($post_id, $shopify_product['product_type'], 'product_cat', true);
				}

				if ( is_array( $product_categories ) && !empty( $product_categories ) ) {
					wp_set_post_terms( $post_id, $product_categories, 'product_cat', true );
				}


				$shopify_tags = isset( $shopify_product['tags'] ) ? explode(',', $shopify_product['tags']) : array();

				$tags = array_merge($shopify_tags, $default_product_tags);
				if ( !empty($tags) ) {
					wp_set_object_terms( $post_id, $tags, 'product_tag' );
				}

				$import_result .= '<p>Product <strong>#'.$post_title.'('.$post_id.')</strong> Created Successfully <strong><a href="'.get_edit_post_link($post_id).'">View & Edit</a></strong></p>';
			}else{
				$import_result .= '<p><strong>#'.$post_title.'('.$post_id.')</strong> Not Created due to '.$post_id->get_error_message().' error</p>';
			}


		}
		return $import_result;
	}

	public function create_simple_product($post_id, $shopify_product,$product_image_download){
        $current_user_id = get_current_user_id();
		$wb_swm_download_images = get_option('wb_swm_download_images_'.$current_user_id) ? get_option('wb_swm_download_images_'.$current_user_id) : SWM_DEFAULT_IMAGE_DOWNLOAD_STATUS;
		// set product is simple/variable/grouped
		$variant = $shopify_product['variants'][0];
		if( $variant['inventory_policy'] === 'continue' ){
			$_backorders = 'yes';
		}else{
			$_backorders = 'no';
		}

		if( $variant['inventory_management'] === 'shopify' ){
			$inventory_management = 'yes';
			if( $variant['inventory_quantity'] > 0 ){
				$stock_status = 'instock';
			}else{
				if( $_backorders === 'yes' ){
					$stock_status = 'onbackorder';	
				}else{
					$stock_status = 'outofstock';
				}
			}
		}else{
			$inventory_management = 'no';
			$stock_status = 'instock';
		}

		if( $variant['requires_shipping'] ){
			$_virtual = 'no';
		}else{
			$_virtual = 'yes';
		}

		$regular_price = $variant['compare_at_price'];
		$sale_price    = $variant['price'];
		if ( ! floatval($regular_price) || floatval( $regular_price ) == floatval( $sale_price ) ) {
			$regular_price = $sale_price;
			$sale_price    = '';
		}
	
		wp_set_object_terms( $post_id, 'simple', 'product_type' );
		update_post_meta( $post_id, '_visibility', 'visible' );
		update_post_meta( $post_id, '_stock_status', $stock_status);
		update_post_meta( $post_id, 'total_sales', '0' );
		update_post_meta( $post_id, '_downloadable', 'no' );
		update_post_meta( $post_id, '_virtual', $_virtual );
		update_post_meta( $post_id, '_regular_price', $regular_price );
		update_post_meta( $post_id, '_price', $regular_price );
		update_post_meta( $post_id, '_purchase_note', '' );
		update_post_meta( $post_id, '_featured', 'no' );
		update_post_meta( $post_id, '_weight', floatval($variant['weight']) );
		update_post_meta( $post_id, '_length', '' );
		update_post_meta( $post_id, '_width', '' );
		update_post_meta( $post_id, '_height', '' );
		update_post_meta( $post_id, '_sku', sanitize_text_field($variant['sku']) );
		update_post_meta( $post_id, '_product_attributes', array() );
		update_post_meta( $post_id, '_sale_price_dates_from', '' );
		update_post_meta( $post_id, '_sale_price_dates_to', '' );
		update_post_meta( $post_id, '_sold_individually', '' );
		update_post_meta( $post_id, '_manage_stock', $inventory_management );
		wc_update_product_stock($post_id, intval($variant['inventory_quantity']) );
		update_post_meta( $post_id, '_backorders', $_backorders );
		
		update_post_meta( $post_id, '_swm_shopify_product_id', $shopify_product['id'] );
		update_post_meta( $post_id, '_swm_shopify_variation_id', $variant['id'] );

		if ( $sale_price ) {
			update_post_meta( $post_id, '_sale_price', $sale_price );
			update_post_meta( $post_id, '_price', $sale_price );
		}


		if( $wb_swm_download_images == 'on' && $product_image_download==0 ){

			$images_d      = array();
			$images        = isset( $shopify_product['images'] ) ? $shopify_product['images'] : array();
			if ( count( $images ) ) {
				foreach ( $images as $image ) {
					$images_d[] = array(
						'src'         => $image['src'],
						'alt'         => $image['alt'],
						'product_ids' => array(),
					);
				}
				$images_d[0]['product_ids'][] = $post_id;
		
			}

			$this->process = new SWM_Background_Process();
			$queue_image=false;

			if ( count( $images_d ) ) {
				$this->process->push_to_queue( $images_d );
				$queue_image = true;
			}

			if ( $queue_image ) {
				$this->process->save()->dispatch();
			}
		}
	}

	public function create_variable_product($post_id, $shopify_product,$product_image_download){
        $current_user_id = get_current_user_id();
		$wb_swm_download_images = get_option('wb_swm_download_images_'.$current_user_id) ? get_option('wb_swm_download_images_'.$current_user_id) : SWM_DEFAULT_IMAGE_DOWNLOAD_STATUS;
		$variations = $shopify_product['variants'];
		$options = $shopify_product['options'];

		if( $wb_swm_download_images == 'on' && $product_image_download==0 ){
			$images_d      = array();
			$images        = isset( $shopify_product['images'] ) ? $shopify_product['images'] : array();
			if ( !empty($images) && count( $images ) ) {
				foreach ( $images as $image ) {
					$images_d[] = array(
						'src'         => $image['src'],
						'alt'         => $image['alt'],
						'product_ids' => array()
					);
				}
				$images_d[0]['product_ids'][] = $post_id;
			}

			$this->process = new SWM_Background_Process();
			$queue_image=false;
		}

		$attr_data = array();
		foreach ( $options as $option_key => $option_val ) {
			$attr_data[ $option_val['name'] ] = array(
				'name'         => $option_val['name'],
				'value'        => implode( ' | ', $option_val['values'] ),
				'position'     => $option_val['position'],
				'is_visible'   => '',
				'is_variation' => 1,
				'is_taxonomy'  => '',
			);
		}
		
		update_post_meta( $post_id, '_visibility', 'visible' );
		//update_post_meta( $post_id, '_sku', sanitize_text_field($shopify_product['sku']) );
		update_post_meta( $post_id, '_product_attributes', $attr_data );

		update_post_meta( $post_id, '_swm_shopify_product_id', $shopify_product['id'] );

		wp_set_object_terms( $post_id, 'variable', 'product_type' );
		
		foreach ( $variations as $variation ) {
			$regular_price = $variation['compare_at_price'];
			$sale_price    = $variation['price'];
			
			if ( ! floatval($regular_price) || floatval( $regular_price ) == floatval( $sale_price ) ) {
				$regular_price = $sale_price;
				$sale_price    = '';
			}

			if( $variation['inventory_policy'] === 'continue' ){
				$_backorders = 'yes';
			}else{
				$_backorders = 'no';
			}

			if( $variation['inventory_management'] === 'shopify' ){
				$inventory_management = 'yes';
				if( $variation['inventory_quantity'] > 0 ){
					$stock_status = 'instock';
				}else{
					if( $_backorders === 'yes' ){
						$stock_status = 'onbackorder';	
					}else{
						$stock_status = 'outofstock';
					}
				}
			}else{
				$inventory_management = 'no';
				$stock_status = 'instock';
			}

			if( $variation['requires_shipping'] ){
				$_virtual = 'no';
			}else{
				$_virtual = 'yes';
			}

			
			$variation_obj = new WC_Product_Variation();
			$variation_obj->set_parent_id( $post_id );
			$attributes = array();
			foreach ( $options as $option_key => $option_val ) {
				$option_index = $option_key + 1;
				if ( isset( $variation[ 'option' . $option_index ] ) && $variation[ 'option' . $option_index ] ) {
					$attributes[ strtolower( $option_val['name'] ) ] = $variation[ 'option' . $option_index ];
				}
			}
			$variation_obj->set_attributes( $attributes );

			$fields = array(
				'sku'            => $this->sku_exists( $variation['sku'] ) ? '' : $variation['sku'],
				'regular_price'  => $regular_price,
				'manage_stock'   => $inventory_management,
				'stock_status'   => $stock_status,
				'stock_quantity' => $variation['inventory_quantity'],
				'virtual'		 => $_virtual,
				'weight'         => $variation['weight'],
				'backorders'	 => $_backorders
			);
			if ( $sale_price ) {
				$fields['sale_price'] = $sale_price;
			}
			foreach ( $fields as $field_key => $field_value ) {
				$variation_obj->{"set_$field_key"}( wc_clean( $field_value ) );
			}
			do_action( 'product_variation_linked', $variation_obj->save() );
			$variation_obj_id = $variation_obj->get_id();

			update_post_meta( $variation_obj_id, '_swm_shopify_variation_id', $variation['id'] );

			
			if( $wb_swm_download_images == 'on'){
				if ( !empty($images) && count( $images ) ) {
					foreach ( $images as $image_k => $image_v ) {
						if ( (isset($image_v['variant_ids'])) && (in_array( $variation['id'], $image_v['variant_ids'] )) ) {
							$images_d[ $image_k ]['product_ids'][] = $variation_obj_id;
						}
					}
				}
			}

		}

		if( $wb_swm_download_images == 'on' && $product_image_download==0 ){
			if ( count( $images_d ) ) {
				$this->process->push_to_queue( $images_d );
				$queue_image = true;
			}

			if ( $queue_image ) {
				$this->process->save()->dispatch();
			}
		}

	}

	public function sku_exists( $sku = '' ) {
		$sku_exists = false;
		if ( $sku ) {
			$id_from_sku = wc_get_product_id_by_sku( $sku );
			$product     = $id_from_sku ? wc_get_product( $id_from_sku ) : false;
			$sku_exists  = $product && 'importing' !== $product->get_status();
		}

		return $sku_exists;
	}

}