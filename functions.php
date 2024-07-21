<?php
if (!swm_is_woocommerce_activated()) {
    add_action('admin_notices', 'swm_missing_wc_notice');
}

function swm_missing_wc_notice()
{
    $class = 'notice error notice-error';
    $message = __('You have need to activate the WooCommerce plugin to use the <strong>SWM - Migrate Shopify to WooCommerce</strong> Plugin.', 'swm');

    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
}

function swm_get_file_contents($file_path)
{
    ob_start();
    require_once($file_path);
    $contents = ob_get_clean();
    return $contents;
}

if (! function_exists('swm_get_option')) {
    function swm_get_option($option = '', $default = null)
    {
        $options = get_option($option);
        return (isset($options)) ? $options : $default;
    }
}

function swm_set_store_url_query()
{
    $current_user_id = get_current_user_id();
    $store_url = swm_get_option('wb_swm_store_url_'.$current_user_id);
    $api_key = swm_get_option('wb_swm_api_key_'.$current_user_id);
    $api_password = swm_get_option('wb_swm_api_pwd_'.$current_user_id);
    $access_token = get_option('wb_swm_access_token_'.$current_user_id);

    if (!empty($access_token)) {
       $url = 'https://'.$store_url.'/admin/api/'.SWM_API_VERSION;
    } elseif (!empty($api_key) && !empty($api_password)) {
        $url = 'https://'.$api_key.':'.$api_password.'@'.$store_url.'/admin/api/'.SWM_API_VERSION;
    }
    return $url;

}

function swm_remote_request($url, $args = array(), $method = 'GET')
{
    $current_user_id = get_current_user_id();
    $url = esc_url_raw($url);
    $request_timeout = swm_get_option('wb_swm_request_timeout_'.$current_user_id) ? swm_get_option('wb_swm_request_timeout_'.$current_user_id) : SWM_DEFAULT_REQUEST_TIMEOUT;
    $api_key = swm_get_option('wb_swm_api_key_'.$current_user_id);
    $api_password = swm_get_option('wb_swm_api_pwd_'.$current_user_id);
    $access_token = get_option('wb_swm_access_token_'.$current_user_id);
    $args['method'] = $method;
    $args['user-agent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.120 Safari/537.36';
    $args['timeout'] = $request_timeout;
    if (!empty($access_token)) {
        $args['headers']['X-Shopify-Access-Token'] = $access_token;
    } elseif (!empty($api_key) && !empty($api_password)) {
        $args['headers']['Authorization'] = 'Basic ' . base64_encode($api_key . ':' . $api_password);
    }
    $wp_args = apply_filters('swm_remote_request_args', $args);
    $request = wp_remote_request($url, $wp_args);

    //Fix API Rate limiting if already exceed
    if (!is_wp_error($request) && ($request['response']['code'] == 429)) {
        $api_retry_after = wp_remote_retrieve_header($request, 'retry-after');
        sleep(intval($api_retry_after));
        swm_remote_request($url, $args, $method);
    }
    if (!is_wp_error($request) && ($request['response']['code'] == 200)) {
        $api_call_limit = wp_remote_retrieve_header($request, 'X-Shopify-Shop-Api-Call-Limit');
        $total_api_call = substr($api_call_limit.'/', 0, strpos($api_call_limit, '/'));
        
        //reduce API call rate limit if it is about to exceed
        if ($total_api_call >= 38) {
            sleep(10);
        }
        $api_response = array();
        $api_response = json_decode(wp_remote_retrieve_body($request), true);
        $pagination = array();
        $pagination = swm_get_pagination_link($request);
        $api_response = array_merge($api_response, $pagination);
    } else {
        return false;
    }
    return $api_response;
}

function swm_check_and_reset_api_limit()
{
}

function swm_upload_image($url, $desc='gallery desc')
{
    //add product image:
    if (! function_exists('media_handle_upload')) {
        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
        require_once(ABSPATH . "wp-admin" . '/includes/file.php');
        require_once(ABSPATH . "wp-admin" . '/includes/media.php');
    }
    $thumb_url = $url;

    // Download file to temp location
    $tmp = download_url($thumb_url);

    // Set variables for storage
    // fix file name for query strings
    preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG|webp)/', $thumb_url, $matches);
    $file_array['name']     = basename($matches[0]);
    $file_array['tmp_name'] = $tmp;

    // If error storing temporarily, unlink
    if (is_wp_error($tmp)) {
        @unlink($file_array['tmp_name']);
    }

    //use media_handle_sideload to upload img:
    $thumbid = media_handle_sideload($file_array, '', $desc);

    // If error storing permanently, unlink
    if (is_wp_error($thumbid)) {
        @unlink($file_array['tmp_name']);
    }

    return $thumbid;
}


//Create Store Cache Directory Inside Uploads Folder
function swm_create_cache_directory($directory='')
{
    $current_user_id = get_current_user_id();
    $upload_dir =  wp_upload_dir();
    $upload_dir = trailingslashit($upload_dir['basedir']);
    $response = array();

    if (wp_is_writable($upload_dir)) {
        $store_name = swm_get_option('wb_swm_store_url_'.$current_user_id);
        $swm_cache_dir_name = $upload_dir.'/swm-cache/'.$store_name;
        if (!empty($directory)) {
            $swm_cache_dir_name = $swm_cache_dir_name.'/'.$directory;
        }
        wp_mkdir_p($swm_cache_dir_name);
    } else {
        return 'write_error';
    }
}

function swm_get_cache_directory($folder='')
{
    $current_user_id = get_current_user_id();
    $upload_dir =  wp_upload_dir();
    $upload_dir = trailingslashit($upload_dir['basedir']);
    $store_name = swm_get_option('wb_swm_store_url_'.$current_user_id);
    if (empty($folder)) {
        $swm_cache_dir_name = trailingslashit($upload_dir.'swm-cache/'.$store_name);
    } else {
        $swm_cache_dir_name = trailingslashit($upload_dir.'swm-cache/'.$store_name.'/'.$folder);
    }

    return $swm_cache_dir_name;
}

//get cache file
function swm_get_cache_files($file_name)
{
    return file_get_contents($file_name);
}

//create cache file
function swm_create_cache_files($file_name, $data = [])
{
    return file_put_contents($file_name, $data);
}

function swm_override_php_max_values()
{
    ini_set('memory_limit', '3000M');
    ini_set('max_execution_time', '3000');
    ini_set('max_input_time', '3000');
    ini_set('default_socket_timeout', '3000');
    ini_set('default_socket_timeout', '3000');
    set_time_limit(0);
}

function swm_get_woo_id_by_shopify_id($shopify_id, $is_variation = false)
{
    $product_id = '';
    if ($shopify_id) {
        $args = array(
            'post_status'    => array( 'publish', 'pending', 'draft' ),
            'posts_per_page' => '1',
            'cache_results'  => false,
            'no_found_rows'  => true,
            'fields'         => 'ids',
        );
        if (! $is_variation) {
            $args['post_type']  = 'product';
            $args['meta_query'] = array(
                    array(
                        'key'     => '_swm_shopify_product_id',
                        'value'   => $shopify_id,
                        'compare' => '=',
                    )
            );
        } else {
            $args['meta_key']   = '_swm_shopify_variation_id';
            $args['meta_value'] = $shopify_id;
            $args['post_type']  = 'product_variation';
        }
        $the_query = new WP_Query($args);

        if ($the_query->have_posts()) {
            $the_query->the_post();
            $product_id = get_the_ID();
        }
        wp_reset_postdata();
    }

    return $product_id;
}

function swm_get_admin_edit_user_link($user_id)
{
    if (get_current_user_id() == $user_id) {
        $edit_link = get_edit_profile_url($user_id);
    } else {
        $edit_link = add_query_arg('user_id', $user_id, self_admin_url('user-edit.php'));
    }

    return $edit_link;
}

function swm_is_woocommerce_activated()
{
    if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        return true;
    } else {
        return false;
    }
}

function swm_check_api_key()
{
    $url = swm_set_store_url_query().'products/count.json';

    $result = swm_remote_request($url);



    if ($result) {
        return true;
    } else {
        return false;
    }
}

function swm_get_pagination_link($request)
{
    $link      = wp_remote_retrieve_header($request, 'link');
    $page_link = array( 'previous' => '', 'next' => '' );
    if ($link) {
        $links = explode(',', $link);
        foreach ($links as $url) {
            $params = wp_parse_url($url);
            parse_str($params['query'], $query);
            if (! empty($query['page_info'])) {
                $query_params = explode('>;', $query['page_info']);
                if (trim($query_params[1]) === 'rel="next"') {
                    $page_link['next'] = $query_params[0];
                } else {
                    $page_link['previous'] = $query_params[0];
                }
            }
        }
    }

    return $page_link;
}
