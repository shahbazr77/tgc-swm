<?php
/**
 * 
 */
swm_override_php_max_values();
class SWM_WC_Product_Cat
{
	private static $instance;

	protected $process;

	public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new SWM_WC_Product_Cat();
        }
        return self::$instance;
    }
	
	function __construct() {
		
	}

	public function import_wc_product_cats($shopify_collections){
		global $swm_shopify_product;
        $current_user_id = get_current_user_id();
		$wb_swm_download_images = get_option('wb_swm_download_cat_images_'.$current_user_id) ? get_option('wb_swm_download_cat_images_'.$current_user_id) : SWM_DEFAULT_IMAGE_DOWNLOAD_STATUS;
		$import_result = '';
		for ($i=0; $i <count($shopify_collections) ; $i++) {
			$collection = $shopify_collections[$i];
			$wc_cat = get_term_by( 'name', $collection['title'], 'product_cat' );
			$collection['woo_id'] = '';
			if( !$wc_cat ){
				$wc_insert_cat = wp_insert_term(
							        $collection['title'], // the term 
							        'product_cat', // the taxonomy
							        array(
							            'description'=> $collection['body_html'],
							            'slug' => $collection['handle'],
							        )
							    );
				if ( ! is_wp_error( $wc_cat ) ) {
					$collection['woo_id'] = isset( $wc_insert_cat['term_id'] ) ? $wc_insert_cat['term_id'] : '';
				}
				$wc_cat = get_term_by( 'id', $collection['woo_id'], 'product_cat' );
			}else {
				$collection['woo_id'] = $wc_cat->term_id;
			}
			$import_result .= '<p>Category <strong>#'.$wc_cat->name.'('.$wc_cat->term_id.')</strong> Created Successfully <strong><a href="'.get_edit_term_link($wc_cat->term_id, 'product_cat', 'product').'">View & Edit</a></strong></p>';
			if(isset($wc_cat->term_id)){
				update_term_meta($wc_cat->term_id, '_swm_shopify_collection_id', $collection['id'] );
			}
			$get_product_by_collection_id = $swm_shopify_product->get_product_by_collection_id( $collection['id'] );
			$shopify_product_ids = is_array($get_product_by_collection_id) ? $get_product_by_collection_id : array();

			if( !empty($shopify_product_ids) ){
				$args = array(
					'post_type'      => 'product',
					'post_status'    => array( 'publish', 'pending', 'draft' ),
					'posts_per_page' => - 1,
					'meta_query'     => array(
						array(
							'key'     => '_swm_shopify_product_id',
							'value'   => $shopify_product_ids,
							'compare' => 'IN'
						),
					),
				);

				$wc_product_query = new WP_Query( $args );
				if( $wc_product_query->have_posts() ){
					while( $wc_product_query->have_posts() ){
						$wc_product_query->the_post();
						$product_id = get_the_ID();
						wp_set_post_terms( $product_id, $collection['woo_id'], 'product_cat', true );
					}
					wp_reset_postdata();
				}
			}

			if( $wb_swm_download_images == 'on' ){

				$images_d      = array();
				$images        = isset( $collection['image'] ) ? $collection['image'] : array();
				if ( count( $images ) > 0 ) {
					if( isset($images['src']) && $images['src'] != '' ){
						$images_d[]['src']=$images['src'];
						$images_d[0]['alt']=$images['alt'];
					}
					$images_d[0]['collection_id'][] = $wc_cat->term_id;
			
				}

				$this->process = new SWM_Background_Process();
				$queue_image=false;

				if ( count( $images_d ) > 0 ) {
					$this->process->push_to_queue( $images_d );
					$queue_image = true;
				}


				if ( $queue_image ) {
					$this->process->save()->dispatch();
				}
			}
		}

		return $import_result;
	}


}
