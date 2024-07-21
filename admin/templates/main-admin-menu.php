<?php

      $current_user_id = get_current_user_id();
      $current_user = wp_get_current_user();

    //General Options

    $wb_swm_store_url_name='wb_swm_store_url_'.$current_user_id;
    $wb_swm_store_url= get_option('wb_swm_store_url_'.$current_user_id) ? get_option('wb_swm_store_url_'.$current_user_id) : '';
    $wb_swm_api_key_name='wb_swm_api_key_'.$current_user_id;
    $wb_swm_api_key = get_option('wb_swm_api_key_'.$current_user_id) ? get_option('wb_swm_api_key_'.$current_user_id) : '';
    $wb_swm_api_pwd__name='wb_swm_api_pwd_'.$current_user_id;
    $wb_swm_api_pwd = get_option('wb_swm_api_pwd_'.$current_user_id) ? get_option('wb_swm_api_pwd_'.$current_user_id) : '';
    $wb_swm_access_token_name='wb_swm_access_token_'.$current_user_id;
    $wb_swm_access_token = get_option('wb_swm_access_token_'.$current_user_id) ? get_option('wb_swm_access_token_'.$current_user_id) : '';




    //Product Options
    $wb_swm_product_status_name='wb_swm_product_status_'.$current_user_id;
    $wb_swm_product_status = get_option('wb_swm_product_status_'.$current_user_id) ? get_option('wb_swm_product_status_'.$current_user_id) : 'publish';
    $wb_swm_download_images_name='wb_swm_download_images_'.$current_user_id;
    $wb_swm_download_images = get_option('wb_swm_download_images_'.$current_user_id) ? get_option('wb_swm_download_images_'.$current_user_id) : 'off';
    $wb_swm_product_categories_name='wb_swm_product_categories_'.$current_user_id;
    $wb_swm_product_categories = get_option('wb_swm_product_categories_'.$current_user_id) ? get_option('wb_swm_product_categories_'.$current_user_id) : array();
    $wb_swm_product_tags_name='wb_swm_product_tags_'.$current_user_id;
    $wb_swm_product_tags = get_option('wb_swm_product_tags_'.$current_user_id) ? get_option('wb_swm_product_tags_'.$current_user_id) : array();
    $wb_swm_request_timeout_name='wb_swm_request_timeout_'.$current_user_id;
    $wb_swm_request_timeout = get_option('wb_swm_request_timeout_'.$current_user_id) ? get_option('wb_swm_request_timeout_'.$current_user_id) : SWM_DEFAULT_REQUEST_TIMEOUT;
    $wb_swm_result_per_request_name='wb_swm_result_per_request_'.$current_user_id;
    $wb_swm_result_per_request = get_option('wb_swm_result_per_request_'.$current_user_id) ? get_option('wb_swm_result_per_request_'.$current_user_id) : SWM_DEFAULT_RESULT_PER_REQUEST;

    //Category Options
    $wb_swm_download_cat_images_name='wb_swm_download_cat_images_'.$current_user_id;
    $wb_swm_download_cat_images = get_option('wb_swm_download_cat_images_'.$current_user_id) ? get_option('wb_swm_download_cat_images_'.$current_user_id) : 'off';
    $wb_swm_cats_per_request_name='wb_swm_cats_per_request_'.$current_user_id;
    $wb_swm_cats_per_request = get_option('wb_swm_cats_per_request_'.$current_user_id) ? get_option('wb_swm_cats_per_request_'.$current_user_id) : SWM_DEFAULT_CAT_PER_REQUEST;

    //Customer Options
    $wb_swm_customer_per_request = '';

    //Orders Options
    $wb_swm_order_per_request = '';
    ?>
<div class="wrap woocommerce swm ">
	<div class="ui inverted menu swm-navigation-bar">
	  <a href="#general-settings" class="green item active">General Settings</a>
	  <a href="#product-settings" class="green item">Product Settings</a>
	  <a href="#category-settings" class="green item">Category Settings</a>
	  <a href="#swm-resourse-selection" class="blue item swm-import-btn">Import</a>
	</div>
	<form action="options.php" class="ui form" method="post" id="swm-mainform">
		<?php settings_fields('swm_settings_options_'.$current_user_id); ?>

		<!-- <div class="ui fluid container segment"> -->
			<!-- <div class="ui styled fluid accordion"> -->
			<div class="ui error message" <?php if (!swm_check_api_key()) {
			    echo 'style="display: block;"';
			} ?>>
				<p>You have need to enter Valid Domain, and Access Token to Migrate from Shopify</p>
			</div>
			<div class="ui error message"></div>
			<div class="ui raised segments">
			  <div id="general-settings" class="title swm-border-bottom active bg-grey">
			  	<h3 class="ui block header">
			    	General Settings
			  	</h3>
			  </div>
			  <div class="content active">
			    <table class="ui padded table swm-no-border">
					<tbody>
						<tr>							
							<td class="three wide">
								<label for="wb_swm_store_url"><?php esc_html_e('Store URL', 'tgc-swm'); ?> </label>
							</td>
							<td class="forminp forminp-text field">
								<input name="<?php echo $wb_swm_store_url_name; ?>" id="wb_swm_store_url_name" type="text" style="" value="<?php echo esc_attr($wb_swm_store_url); ?>" class="" placeholder="">
							</td>
						</tr>
						<tr valign="top">
							<td scope="row" class="three wide titledesc">
								<label for="wb_swm_access_token"><?php esc_html_e('Access Token', 'tgc-swm'); ?></label>
							</td>
							<td class="forminp forminp-text field">
								<input name="<?php echo $wb_swm_access_token_name; ?>" id="<?php echo $wb_swm_access_token_name; ?>" type="text" style="" value="<?php echo esc_attr($wb_swm_access_token); ?>" placeholder="">
							</td>
						</tr>
						<tr valign="top">
							<td scope="row" class="three wide titledesc">
								<label for="wb_swm_request_timeout"><?php esc_html_e('Request Timeout(s)', 'tgc-swm'); ?> </label>
							</td>
							<td class="forminp forminp-text field">
								<input name="<?php echo $wb_swm_request_timeout_name; ?>" id="wb_swm_request_timeout" type="text" style="" value="<?php echo esc_attr($wb_swm_request_timeout); ?>" class="" placeholder="">
							</td>
						</tr>
					</tbody>
			    </table>
			  </div>
			</div>
			
			<div class="ui raised segments hidden">
			  <div id='product-settings' class="title swm-border-bottom active bg-grey"><!-- Start Product Settings -->
			    <h3 class="ui block header">
			    	Product Settings
				</h3>
			  </div>
			  <div class="content">
			    <table class="ui padded table swm-no-border">
					<tbody>
						<tr>							
							<td class="three wide ">
								<label for="wb_swm_product_status"><?php esc_html_e('Product Status', 'tgc-swm'); ?> </label>
							</td>
							<td class="forminp forminp-text field">
                                <?php if (in_array('administrator', $current_user->roles)) { ?>
								<select class="ui search dropdown" name="<?php echo $wb_swm_product_status_name; ?>" id="wb_swm_product_status">
									<option value="publish" <?php echo selected($wb_swm_product_status, 'publish'); ?> ><?php esc_html_e('Publish', 'tgc-swm'); ?></option>
									<option value="pending" <?php echo selected($wb_swm_product_status, 'pending'); ?>><?php esc_html_e('Pending', 'tgc-swm'); ?></option>
									<option value="draft" <?php echo selected($wb_swm_product_status, 'draft'); ?>><?php esc_html_e('Draft', 'tgc-swm'); ?></option>
								</select>
                                <?php }else{ ?>
                                <select class="ui search dropdown" name="<?php echo $wb_swm_product_status_name; ?>" id="wb_swm_product_status">
                                    <option value="pending" <?php echo selected($wb_swm_product_status, 'pending'); ?>><?php esc_html_e('Pending', 'tgc-swm'); ?></option>
                                </select>
                                <?php } ?>

							</td>
						</tr>
						<tr valign="top">
							<td scope="row" class="three wide titledesc">
								<label for="wb-swm-enable-images"><?php esc_html_e('Download Images', 'tgc-swm') ?> </label>
							</td>
							<td class="forminp forminp-text field">
								<div class="ui toggle checkbox">
							      <input type="checkbox"  name="<?php echo $wb_swm_download_images_name; ?>" value="on" id="wb-swm-enable-images" <?php echo checked($wb_swm_download_images, 'on'); ?>>
							    </div>
							</td>
						</tr>
						<tr>							
							<td class="three wide">
								<label for="wb_swm_product_categories"><?php esc_html_e('Default Product Categories', 'tgc-swm'); ?> </label>
							</td>
							<td class="forminp forminp-text field">
								<select multiple="" class="ui search dropdown" name="wb_swm_product_categories[]" id="wb_swm_product_categories">
									<?php
			                                        $product_cat_args = array(
			                                                  'taxonomy'  => 'product_cat',
			                                                  'hide_empty'=> false
			                                                );
    $product_cat_terms = get_terms($product_cat_args);
    if (!empty($product_cat_terms)) {
        foreach ($product_cat_terms as $key => $value) {
            ?>
												<option <?php if (in_array($value->term_id, $wb_swm_product_categories)) {
												    echo 'selected="selected"';
												}; ?> value="<?php echo $value->term_id; ?>"><?php echo $value->name; ?></option>
									<?php
        }
    } else { ?>
										<option value=""><?php esc_html_e('No Product Categories Found', 'tgc-swm'); ?></option>
									<?php } ?>
								</select>
							</td>
						</tr>
						<tr>							
							<td class="three wide">
								<label for="wb_swm_product_tags"><?php esc_html_e('Default Product Tags', 'tgc-swm'); ?> </label>
							</td>
							<td class="forminp forminp-text field">
								<select multiple="" class="ui search dropdown" name="<?php echo $wb_swm_product_tags_name=[]; ?>" id="wb_swm_product_tags">
									<?php
    $product_tag_args = array(
              'taxonomy'  => 'product_tag',
              'hide_empty'=> false
            );
    $product_tag_terms = get_terms($product_tag_args);
    if (!empty($product_tag_terms)) {
        foreach ($product_tag_terms as $key => $value) {
            ?>
												<option <?php if (in_array($value->name, $wb_swm_product_tags)) {
												    echo 'selected="selected"';
												}; ?> value="<?php echo esc_attr($value->name); ?>"><?php echo $value->name; ?></option>
									<?php
        }
    } else { ?>
											<option value=""><?php esc_html_e('No Product Tags Found', 'tgc-swm'); ?></option>
										<?php } ?>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<td class="three wide titledesc">
								<label for="wb_swm_result_per_request"><?php esc_html_e('Products Per Request', 'tgc-swm'); ?> </label>
							</td>
							<td class="forminp forminp-text field">
								<input name="<?php echo $wb_swm_result_per_request_name ?>" id="wb_swm_result_per_request" type="text" style="" value="<?php echo esc_attr($wb_swm_result_per_request); ?>" class="" name="wb_swm_result_per_request" placeholder="">
							</td>
						</tr>

					</tbody>
			    </table>
			  </div><!-- End Product Settings -->
			</div>
			
			<div class="ui raised segments hidden">
			  <div id='category-settings' class="title swm-border-bottom active bg-grey"><!-- Start Category Settings -->
			    <h3 class="ui block header">
			    	Category Settings
			    </h3>
			  </div>
			  <div class="content">
			    <table class="ui padded table swm-no-border">
					<tbody>
						<tr valign="top">
							<td scope="row" class="three wide titledesc">
								<label for="wb-swm-enable-cat-images"><?php esc_html_e('Download Category Images', 'tgc-swm') ?> </label>
							</td>
							<td class="forminp forminp-text field">
								<div class="ui toggle checkbox">
							      <input type="checkbox"  name="<?php echo $wb_swm_download_cat_images_name ?>" value="on" id="wb-swm-enable-cat-images" <?php echo checked($wb_swm_download_cat_images, 'on'); ?>>
							    </div>
							</td>
						</tr>
						<tr valign="top">
							<td class="three wide titledesc">
								<label for="wb_swm_cats_per_request"><?php esc_html_e('Categories Per Request', 'tgc-swm'); ?> </label>
							</td>
							<td class="forminp forminp-text field">
								<input name="<?php echo $wb_swm_cats_per_request_name ?>" id="wb_swm_cats_per_request" type="text" style="" value="<?php echo esc_attr($wb_swm_cats_per_request); ?>" class="" name="wb_swm_cats_per_request" placeholder="">
							</td>
						</tr>

					</tbody>
			    </table>
			  </div><!-- End Category Settings -->
			</div>
			


			<!-- </div>  -->
			<div class="ui raised segments">
				<div class="ui segment">
					<p>
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
					</p>
				</div>
			</div>
		<!-- </div> -->

		<!-- <div id="swm-resourse-selection" class="ui fluid container segment"> -->
		<div id="swm-resourse-selection" class="ui fluid segment hidden">

		<?php if ($wb_swm_store_url && $wb_swm_access_token) { ?>
			<table class="ui padded table celled">
				<thead>
					<tr>
						<th><?php esc_html_e('Resource', 'tgc-swm') ?></th>
						<th><?php esc_html_e('Enable/Disable', 'tgc-swm') ?></th>
						<th><?php esc_html_e('Result', 'tgc-swm') ?></th>
					</tr>
				</thead>

				<tbody>
					<tr valign="top">
						<td scope="row" class="three wide titledesc">
							<label for="wb-swm-enable-product">Product </label>
						</td>
						<td class="forminp forminp-text three wide">
							<div class="swm-container" data-resource-type='product'>
						       <div class="ui toggle checkbox">
							      <input type="checkbox" class="swm-switch-input" name="wb-swm-import-product" value="enable" id="wb-swm-enable-images" checked>
							    </div>
							</div>
						</td>
						<td id="wb-swm-product-download-status">
							<div class="ui indicating progress swm-hidden">
							  <div class="bar"></div>
							  <div class="label"><?php esc_html_e('Waiting', 'wb-swm'); ?></div>
							</div>
						</td>
					</tr>
					<tr valign="top">
						<td scope="row" class="three wide titledesc">
							<label for="wb-swm-enable-product-cat"><?php esc_html_e('Product Categories', 'tgc-swm'); ?> </label>
						</td>
						<td class="forminp forminp-text three wide">
							<div class="swm-container" data-resource-type='product-category'>
								<div class="ui toggle checkbox">
							      <input type="checkbox" class="swm-switch-input" name="wb-swm-import-categories" value="enable" id="wb-swm-enable-categories" checked>
							    </div>
						    </div>
						</td>
						<td id="wb-swm-product-cat-download-status">
							<div class="ui indicating progress swm-hidden">
							  <div class="bar"></div>
							  <div class="label"><?php esc_html_e('Waiting', 'wb-swm'); ?></div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		<?php } ?>

			<?php if ($wb_swm_store_url && $wb_swm_access_token && swm_check_api_key()) { ?>
				<a class="swm-start-importer-btn button button-primary" href="javascript:void(0)">Start Import</a>
<!--				<a class="swm-delete-history-btn negative ui button" href="admin.php?page=swm_shopify_to_wc&swm_delete_import_history=true">Delete Previous Import History</a>-->
				
			<?php } else { ?>
				<div class="ui error message" style="display: block;" >
					<p>You have need to enter Valid Domain, and Access Token to Migrate from Shopify</p>
				</div>
			<?php } ?>
			<div class="swm-running-import-loader ui hidden inline loader"></div>
			<h3 class="ui header"><?php esc_html_e('Import Logs', 'swm'); ?></h3>
			<div class="ui secondary raised segment">
				<div class="ajax_loaded_content">
					<div class="swm-import-product-logs"></div>
					<div class="swm-import-cats-logs"></div>
					<div class="swm-import-customer-logs"></div>
					<div class="swm-import-order-logs"></div>
				</div>
			</div>
		</div>
	</form>
</div>

<div class="ui mini modal swm-import-complete">
  <div class="scrolling content">
    <p>Import Completed Successfully</p>
  </div>
</div>