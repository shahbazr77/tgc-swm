<?php
/**
 * Plugin Name: That Girl Club SWM
 * Plugin URI:
 * Description: Import all products from Shopify store to WooCommece
 * Version: 1.0.0
 * Author: Yodo Developers
 * Author URI:
 * Text Domain: tgc-swm
 * Domain Path: /languages
 * Copyright 2019 plugin-devs.com. All rights reserved.
 * Tested up to: 6.1.1
 * WC tested up to: 7.1.0
 **/

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Main Class
 */
class TGC_Shopify_to_Woo_Migration
{
    protected $process_single;
    protected $background_process;

    public function __construct()
    {
        //ini_set('max_execution_time', 0);
        //set_time_limit(0);
        do_action('swm_before_load');
        $this->define_constants();
        $this->hooks();
        $this->include_files();
        swm_override_php_max_values();
        do_action('swm_after_load');
    }

    //hooks
    public function hooks()
    {
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 10, 1);
        add_action('admin_notices', array( $this, 'background_image_download_admin_notices' ));
        register_activation_hook(__FILE__,array($this, 'activation_hook'));
        add_action('plugins_loaded', array( $this, 'plugins_loaded' ));
        register_activation_hook(__FILE__,array($this,'tgc_shopfify_create_custom_ids'));
        register_deactivation_hook(__FILE__, array($this,'tgc_shopfify_create_custom_ids_deactivate'));
        add_action('admin_menu',array($this,'tgc_hide_admin_menu_pages'),999);

    }
    public function tgc_hide_admin_menu_pages() {
            if (!current_user_can('administrator')) {
                remove_menu_page('jet-engine');
                remove_menu_page('elementor');
                remove_menu_page('jet-smart-filters');
            }
    }

    //Declare Constants
    public function define_constants()
    {
        $upload_dir =  wp_upload_dir();
        $upload_dir = trailingslashit($upload_dir['basedir']);
        define('WB_SWM_VERSION', '1.0.0');
        define('SWM_API_VERSION', '2023-10/');
        define('SWM_DEFAULT_REQUEST_TIMEOUT', '600');
        define('SWM_DEFAULT_RESULT_PER_REQUEST', '5');
        define('SWM_DEFAULT_CAT_PER_REQUEST', '10');
        define('SWM_DEFAULT_IMAGE_DOWNLOAD_STATUS', 'on');
        define('WB_SWM_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
        define('WB_SWM_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
        define('WB_SWM_ASSETS_FOLDER_URL', WB_SWM_PLUGIN_DIR_URL . 'assets');
        define('WB_SWM_INC_FOLDER_PATH', WB_SWM_PLUGIN_DIR_PATH . 'includes');
        define('SWM_CACHE', $upload_dir);//use the same cache folder to free version
        define('WB_SWM_UPGRADE_TO_PRO','');
    }

    //require files
    public function include_files()
    {
        global $pagenow;
        require_once(WB_SWM_PLUGIN_DIR_PATH . '/functions.php');
        require_once(WB_SWM_PLUGIN_DIR_PATH . '/admin/admin-pages.php');
        require_once(WB_SWM_PLUGIN_DIR_PATH . '/includes/libs/background-processing/wp-async-request.php');
        require_once(WB_SWM_PLUGIN_DIR_PATH . '/includes/libs/background-processing/wp-background-process.php');
        require_once(WB_SWM_PLUGIN_DIR_PATH . '/includes/class-background-process.php');
        $this->background_process = new SWM_Background_Process();
        //require_once(WB_SWM_INC_FOLDER_PATH . '/class-cronjob-call.php');
        require_once(WB_SWM_INC_FOLDER_PATH . '/class-ajax.php');
        require_once(WB_SWM_INC_FOLDER_PATH . '/wc/class-wc-product.php');
        require_once(WB_SWM_INC_FOLDER_PATH . '/wc/class-wc-product-cat.php');
        require_once(WB_SWM_INC_FOLDER_PATH . '/shopify/class-shopify-product.php');
        require_once(WB_SWM_INC_FOLDER_PATH . '/shopify/class-shopify-product-cat.php');

        if (isset($pagenow) && $pagenow == 'plugins.php') {
            require_once(WB_SWM_PLUGIN_DIR_PATH . '/admin/class-plugin-action-links.php');
            $settings_url = admin_url('admin.php');
            $settings_url = add_query_arg(array(
                'page' => 'swm_shopify_to_wc'
            ), $settings_url);
            $WB_Plugin_Action_Links = new WB_Plugin_Action_Links(
                'migrate-shopify-to-woocommerce/swm-shopify-to-wc-migration.php',
                'https://wordpress.org/support/plugin/migrate-shopify-to-woocommerce/reviews/?rate=5#new-post',
                WB_SWM_UPGRADE_TO_PRO,
                $settings_url
            );
        }




    }

    //enqueue admin style and scripts
    public function admin_enqueue_scripts($hook)
    {

        if ($hook === 'toplevel_page_swm_shopify_to_wc' || $hook === 'shopify-to-wc_page_swm-system-status') {
            $current_user_id = get_current_user_id();
            $wb_swm_request_timeout = get_option('wb_swm_request_timeout_'.$current_user_id) ? get_option('wb_swm_request_timeout_'.$current_user_id) : SWM_DEFAULT_REQUEST_TIMEOUT;
            $wb_swm_result_per_request = get_option('wb_swm_result_per_request_'.$current_user_id) ? get_option('wb_swm_result_per_request_'.$current_user_id) : SWM_DEFAULT_RESULT_PER_REQUEST;
            $wb_swm_cats_per_request = get_option('wb_swm_cats_per_request_'.$current_user_id) ? get_option('wb_swm_cats_per_request_'.$current_user_id) : SWM_DEFAULT_CAT_PER_REQUEST;

            wp_enqueue_style('swm-semantic-ui', WB_SWM_ASSETS_FOLDER_URL . '/css/semantic.min.css', array(), WB_SWM_VERSION, 'all');
            wp_enqueue_style('swm-admin-style', WB_SWM_ASSETS_FOLDER_URL . '/css/admin-style.css', array( 'swm-semantic-ui'), WB_SWM_VERSION, 'all');

            wp_enqueue_script('swm-semantic-ui', WB_SWM_ASSETS_FOLDER_URL . '/js/semantic.min.js', array('jquery'), WB_SWM_VERSION, true);
            wp_enqueue_script('swm-admin-script', WB_SWM_ASSETS_FOLDER_URL . '/js/admin-script.js', array('jquery', 'swm-semantic-ui'), WB_SWM_VERSION, true);
            wp_localize_script(
                'swm-admin-script',
                'swm_ajax_object',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('swm_ajax_nonce'),
                    'wb_swm_request_timeout' => $wb_swm_request_timeout,
                    'wb_swm_result_per_request' => $wb_swm_result_per_request,
                    'wb_swm_cats_per_request' => $wb_swm_cats_per_request,
                )
            );
        }
        wp_enqueue_script('swm-menu-script', WB_SWM_ASSETS_FOLDER_URL . '/js/menu-script.js', array('jquery'), WB_SWM_VERSION, true);
    }

    public function background_image_download_admin_notices()
    {
        if ($this->background_process->is_downloading()) {
            ?>
            <div class="updated">
                <p>
					<?php esc_html_e('SWM – Shopify to WooCommerce Migration: Product images are being downloaded in the background.', 'tgc-swm') ?>
                </p>
                <p>
					<?php printf(__('Please goto <a target="_blank" href="%s">Media</a> and view downloaded product images. If some images are downloaded repeatedly, please <strong>1. Stop importing product</strong>, <strong>2.  <a href="%s">Cancel downloading</a></strong> immediately and contact <strong>yododesing@gmail.com</strong> for help.', 'tgc-swm'), admin_url('upload.php'), add_query_arg(array( 'swm_cancel_download_image' => '1', ), wp_unslash(sanitize_text_field($_SERVER['REQUEST_URI'])))) ?>
                </p>
            </div>
			<?php
        } elseif (get_transient('swm_background_img_downloading_complete')) {
            delete_transient('swm_background_img_downloading_complete'); ?>
            <div class="updated">
                <p>
					<?php esc_html_e('SWM – Shopify to WooCommerce Migration: Product images are downloaded successfully.', 'tgc-swm') ?>
                </p>
            </div>
			<?php
        }
    }

    public function activation_hook()
    {
        $current_user_id = get_current_user_id();
        $wb_swm_download_images = get_option('wb_swm_download_images_'.$current_user_id);
        $wb_swm_download_cat_images = get_option('wb_swm_download_cat_images_'.$current_user_id);

        if (!$wb_swm_download_images) {
            update_option('wb_swm_download_images_'.$current_user_id, SWM_DEFAULT_IMAGE_DOWNLOAD_STATUS);
        }

        if (!$wb_swm_download_cat_images) {
            update_option('wb_swm_download_cat_images_'.$current_user_id, SWM_DEFAULT_IMAGE_DOWNLOAD_STATUS);
        }
    }

    public function plugins_loaded()
    {

        $this->img_download_bg_process = new SWM_Background_Process();

        if (isset($_REQUEST['swm_cancel_download_image']) && wp_unslash(sanitize_text_field($_REQUEST['swm_cancel_download_image']))) {
            delete_transient('swm_background_img_downloading_complete');
            $this->img_download_bg_process->kill_process();
            wp_safe_redirect(@remove_query_arg('swm_cancel_download_image'));
            exit;
        }
    }

    function tgc_shopfify_create_custom_ids_deactivate(){
        if (in_array( 'woocommerce-dropshippers-vendors/woocommerce-dropshippers-vendors.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            $role = get_role('dropshipper');
            $role->remove_cap('manage_options');
        }
    }
    function tgc_shopfify_create_custom_ids() {
        if (in_array( 'woocommerce-dropshippers-vendors/woocommerce-dropshippers-vendors.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            $role = get_role('dropshipper');
            $role->add_cap('manage_options');
        }
        $current_user_id = get_current_user_id();
        $wb_swm_download_images = get_option('wb_swm_download_images_'.$current_user_id);
        $wb_swm_download_cat_images = get_option('wb_swm_download_cat_images_'.$current_user_id);

        if (!$wb_swm_download_images) {
            update_option('wb_swm_download_images_'.$current_user_id, SWM_DEFAULT_IMAGE_DOWNLOAD_STATUS);
        }

        if (!$wb_swm_download_cat_images) {
            update_option('wb_swm_download_cat_images_'.$current_user_id, SWM_DEFAULT_IMAGE_DOWNLOAD_STATUS);
        }





        global $wpdb;
        $table_name = $wpdb->prefix.'shopify_custom_table_ids';
        // Create the table SQL statement
        $sql_plugin_status = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT(11) NOT NULL AUTO_INCREMENT,
            shopify_ids BIGINT,
            tgc_wp_ids  BIGINT,
            PRIMARY KEY (id)
        )";
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
        dbDelta( $sql_plugin_status );
        set_transient('synkraft_plugin_activation_redirect', true, 30);
    }





}


if (class_exists('TGC_Shopify_to_Woo_Migration')) {
    $swm_shopify_to_woo_migration = new TGC_Shopify_to_Woo_Migration();

    $swm_admin_pages = Swm_Admin_Pages::getInstance();

    $swm_ajax_handler = new SWM_Ajax_Handler();

    $swm_woocommerce_product = SWM_WC_Product::getInstance();
    $swm_woocommerce_product_cat = SWM_WC_Product_Cat::getInstance();
    $swm_shopify_product = SWM_Shopify_Product::getInstance();
    $swm_shopify_product_cat = SWM_Shopify_Product_Cat::getInstance();

}


add_action('restrict_manage_posts', 'swm_shopify_product_filter');
function swm_shopify_product_filter()
{
    global $typenow;
    global $pagenow;

    $swm_shopify_product = 0;
    if ($typenow == 'product') {
        if (
            $pagenow == 'edit.php' &&
            isset($_GET['swm_import_type']) &&
            $_GET['swm_import_type'] == 'swm_shopify_product'
        ) {
            $swm_shopify_product = 'swm_shopify_product';
        }
        echo '
			<select name="swm_import_type">
				<option value="">Filter by Import Type</option>
				<option '.selected($swm_shopify_product, 'swm_shopify_product', false).' value="swm_shopify_product">Shopify</option>
			</select>
		';
    }

    $swm_shopify_image = 0;
    if ($typenow == 'attachment') {
        if (
            $pagenow == 'upload.php' &&
            isset($_GET['swm_media_import_from']) &&
            $_GET['swm_media_import_from'] == 'swm_shopify_image'
        ) {
            $swm_shopify_image = 'swm_shopify_image';
        }
        echo '
			<select name="swm_media_import_from">
				<option value="">Filter by Import Type</option>
				<option '.selected($swm_shopify_image, 'swm_shopify_image', false).' value="swm_shopify_image">Shopify</option>
			</select>
		';
    }
}


add_action('parse_query', 'product_tags_sorting_query');
function product_tags_sorting_query($query)
{
    global $pagenow;

    $q_vars    = &$query->query_vars;

    if (
        $pagenow == 'edit.php' &&
        isset($q_vars['post_type']) &&
        $q_vars['post_type'] == 'product' &&
        isset($_GET['swm_import_type']) &&
        $_GET['swm_import_type'] == 'swm_shopify_product'
    ) {
        $q_vars['meta_query'][] = array(
            'field' => 'swm_import_type',
            'value' => 'swm_shopify_product',
            'compare' => '=',
        );
    }

    if (
        $pagenow == 'upload.php' &&
        isset($q_vars['post_type']) &&
        $q_vars['post_type'] == 'attachment' &&
        isset($_GET['swm_media_import_from']) &&
        $_GET['swm_media_import_from'] == 'swm_shopify_image'
    ) {
        $q_vars['meta_query'][] = array(
            'field' => 'swm_media_import_from',
            'value' => 'swm_shopify_image',
            'compare' => '=',
        );
    }
}

add_action('init', 'swm_delete_import_history');
function swm_delete_import_history()
{
    if (isset($_GET['page']) && $_GET['page'] =='swm_shopify_to_wc') {
        if (isset($_GET['swm_delete_import_history']) && $_GET['swm_delete_import_history'] =='true') {
            delete_option('swm_import_records');
            delete_option('swm_import_custom_collection_records');
            wp_redirect('admin.php?page=swm_shopify_to_wc');
        }
    }
}

