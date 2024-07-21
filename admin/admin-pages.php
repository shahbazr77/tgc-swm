<?php
if( !class_exists('Swm_Admin_Pages') ){
	/**
	 * Register All Admin Pages from Here
	 */
	class SWM_Admin_Pages
	{

		private static $instance;

		public static function getInstance() {
	        if (!isset(self::$instance)) {
	            self::$instance = new Swm_Admin_Pages();
	        }
	        return self::$instance;
	    }
		
		function __construct() {
			add_action( 'admin_init', array($this, 'swm_register_setting') ); 
			add_action('admin_menu', array($this, 'swm_menu_page'),999);
           // add_action( 'admin_menu', array($this,'dropshipper_settings_pag2e'),999999);
		}





		public function swm_menu_page(){

        $custom_capabillity="manage_options";
        if (in_array( 'woocommerce-dropshippers-vendors/woocommerce-dropshippers-vendors.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            if (current_user_can('administrator')) {
            $custom_capabillity="manage_options";
            } else {
            $custom_capabillity="show_dropshipper_widget";
            }
        }


			//obal $submenu;
			add_menu_page(
				__('Shopify to WC', 'tgc-swm'),
				__('Shopify to WC', 'tgc-swm'),
                $custom_capabillity,
				'swm_shopify_to_wc',
				array($this, 'swm_menu_page_callback'),
				'dashicons-image-rotate-left',
				25
			);

		}




//        function dropshipper_settings_pag2e() {
//            if( ! current_user_can('manage_network') ){
//
//                add_menu_page(
//                    __('Shopify to WC2', 'tgc-swm'),
//                    __('Shopify to WC2', 'tgc-swm'),
//                    'show_dropshipper_widget',
//                    'swm_shopify_to_wc',
//                    array($this, 'swm_menu_page_callback'),
//                    'dashicons-image-rotate-left',
//
//                );
//
//
//            }
//        }


		public function swm_menu_page_callback(){
			require_once('templates/main-admin-menu.php');
		}
		

		public function swm_register_setting() {

            $current_user_id = get_current_user_id();
		    register_setting( 'swm_settings_options_'.$current_user_id, 'wb_swm_store_url_'.$current_user_id);
		    register_setting( 'swm_settings_options_'.$current_user_id, 'wb_swm_access_token_'.$current_user_id);
			register_setting( 'swm_settings_options_'.$current_user_id, 'wb_swm_api_key_'.$current_user_id);
		    register_setting( 'swm_settings_options_'.$current_user_id, 'wb_swm_api_pwd_'.$current_user_id);
		    register_setting( 'swm_settings_options_'.$current_user_id, 'wb_swm_product_status_'.$current_user_id);
		    register_setting( 'swm_settings_options_'.$current_user_id, 'wb_swm_download_images_'.$current_user_id);
		    register_setting( 'swm_settings_options_'.$current_user_id, 'wb_swm_product_categories_'.$current_user_id);
		    register_setting( 'swm_settings_options_'.$current_user_id, 'wb_swm_product_tags_'.$current_user_id);
		    register_setting( 'swm_settings_options_'.$current_user_id, 'wb_swm_request_timeout_'.$current_user_id);
		    register_setting( 'swm_settings_options_'.$current_user_id, 'wb_swm_result_per_request_'.$current_user_id);
		    register_setting( 'swm_settings_options_'.$current_user_id, 'wb_swm_download_cat_images_'.$current_user_id);
		    register_setting( 'swm_settings_options_'.$current_user_id, 'wb_swm_cats_per_request_'.$current_user_id);
		    register_setting( 'swm_settings_options_'.$current_user_id, 'wb_swm_customer_per_request_'.$current_user_id);
		    register_setting( 'swm_settings_options_'.$current_user_id, 'wb_swm_order_per_request_'.$current_user_id);
		}


	}

}
