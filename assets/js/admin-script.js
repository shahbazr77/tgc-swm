'use strict'
function swm_reset_progress_bar(){
	jQuery('#swm-resourse-selection .ui.indicating.progress').each(function(){
		if( jQuery(this).attr('data-percent') ){
			jQuery(this).removeAttr('data-percent').removeClass('active success').addClass('swm-hidden');
			jQuery(this).find('.bar').removeAttr('style').removeClass('success');
			jQuery(this).find('.label').text('Waiting');
		}
	});
}
var selected_resources = [];
jQuery(document).ready(function(){


	function call_importor_automatically() {
		// Your code here
		console.log("Function called at " + new Date());
		if( !jQuery(this).hasClass('swm-import-running') ){
			jQuery(this).addClass('swm-import-running');
			jQuery(this).addClass('disabled');
			jQuery('.swm-running-import-loader').removeClass('hidden').addClass('active');
			selected_resources = [];
			swm_reset_progress_bar();
			jQuery('#swm-resourse-selection .swm-switch-input:checked').each(function(){
				var value = jQuery(this).val();
				if( value == 'enable' ){
					var type = jQuery(this).parents('.swm-container').data('resource-type');
					selected_resources.push(type);
					jQuery(this).parents('tr').find('.ui.indicating.progress').removeClass('swm-hidden');
				}
			});
			//jQuery('.ajax_loaded_content').empty();
			jQuery('.ajax_loaded_content .swm-import-product-logs').empty();
			jQuery('.ajax_loaded_content .swm-import-cats-logs').empty();
			jQuery('.ajax_loaded_content .swm-import-customer-logs').empty();
			jQuery('.ajax_loaded_content .swm-import-order-logs').empty();
			swm_start_import();
		}

	}

	//const intervalInMilliseconds = 10 * 60 * 1000; // 5 minutes * 60 seconds/minute * 1000 milliseconds/second
	//setInterval(call_importor_automatically, intervalInMilliseconds);






	jQuery('.swm-start-importer-btn').on('click', function(){
		if( !jQuery(this).hasClass('swm-import-running') ){
			jQuery(this).addClass('swm-import-running');
			jQuery(this).addClass('disabled');
			jQuery('.swm-running-import-loader').removeClass('hidden').addClass('active');
			selected_resources = [];
			swm_reset_progress_bar();
			jQuery('#swm-resourse-selection .swm-switch-input:checked').each(function(){
				var value = jQuery(this).val();
				if( value == 'enable' ){
					var type = jQuery(this).parents('.swm-container').data('resource-type');
					selected_resources.push(type);
					jQuery(this).parents('tr').find('.ui.indicating.progress').removeClass('swm-hidden');
				}
			});
			//jQuery('.ajax_loaded_content').empty();
			jQuery('.ajax_loaded_content .swm-import-product-logs').empty();
			jQuery('.ajax_loaded_content .swm-import-cats-logs').empty();
			jQuery('.ajax_loaded_content .swm-import-customer-logs').empty();
			jQuery('.ajax_loaded_content .swm-import-order-logs').empty();
			swm_start_import();
		}
	});
	swm_set_nav_bar_pos();
	jQuery('#swm-mainform').find('.ui.segments').addClass('hidden');
	jQuery('#swm-mainform').find('#swm-resourse-selection').addClass('hidden');
	jQuery('#swm-mainform').find('.ui.segments').first().addClass('active').removeClass('hidden');
	jQuery('#swm-mainform').find('.ui.segments').last().addClass('active').removeClass('hidden');
	jQuery('.swm-navigation-bar .item:not(.swm-import-btn)').on('click', function(e){
		e.preventDefault();
		var section_id = jQuery(this).attr('href');
		// var section_top_pos = jQuery(section_id).offset().top - jQuery('.swm-navigation-bar').outerHeight() - 50;
		var section_top_pos = jQuery('#swm-mainform').offset().top - jQuery('.swm-navigation-bar').outerHeight() - 50;
		jQuery('html, body').stop().animate({
			scrollTop: section_top_pos
		}, 750, 'swing')
		jQuery(this).siblings('.item').removeClass('active');
		jQuery(this).addClass('active');
		jQuery('#swm-mainform').find('.ui.segments').addClass('hidden');
		jQuery('#swm-mainform').find('#swm-resourse-selection').addClass('hidden');
		jQuery(section_id).parent().addClass('active').removeClass('hidden');
		jQuery('#swm-mainform').find('.ui.segments').last().addClass('active').removeClass('hidden');

	});

	jQuery('.swm-import-btn').on('click', function(e){
		e.preventDefault();
		var section_id = jQuery(this).attr('href');
		jQuery(this).siblings('.item').removeClass('active');
		jQuery('#swm-mainform').find('.ui.segments').addClass('hidden');
		jQuery(section_id).addClass('active').removeClass('hidden');
		// jQuery('#swm-mainform').find('.ui.segments').last().addClass('active').removeClass('hidden');
	});

	var invalid_field_instance = 0;
	jQuery('#swm-mainform')
	  .form({
	    fields: {
	      store_url: {
	        identifier: 'wb_swm_store_url',
	        rules: [
	          {
	            type   : 'empty',
	            prompt : '<strong>Store URL</strong> Should not be Empty'
	          }
	        ]
	      },
		  access_token: {
	        identifier: 'wb_swm_access_token',
	        rules: [
	          {
	            type   : 'empty',
	            prompt : '<strong>Access Token</strong> Should not be Empty'
	          }
	        ]
	      },
	      request_timeout: {
	        identifier: 'wb_swm_request_timeout',
	        rules: [
	          {
	            type   : 'integer',
	            prompt : '<strong>Request Timeout</strong> Field Must be an Integer'
	          }
	        ]
	      },
	      product_per_request: {
	        identifier: 'wb_swm_result_per_request',
	        rules: [
	          {
	            type   : 'integer',
	            prompt : '<strong>Products Per Request</strong> Field Must be an Integer'
	          }
	        ]
	      },
	      cats_per_request: {
	        identifier: 'wb_swm_cats_per_request',
	        rules: [
	          {
	            type   : 'integer',
	            prompt : '<strong>Categories Per Request</strong> Field Must be an Integer'
	          }
	        ]
	      },
	    },
	    onInvalid : function(){
	    	// var _this = jQuery(this);
	    	var _this = jQuery('#swm-mainform').find('.field.error').eq(0);
	    	var section_id = '#'+_this.parents('table').parents('.ui.segments').children('.title').attr('id');
	    	// console.log('section_id '+section_id);
	    	if( section_id ){
	    		var section_top_pos = jQuery('#swm-mainform').offset().top - jQuery('.swm-navigation-bar').outerHeight() - 50;
				jQuery('html, body').stop().animate({
					scrollTop: section_top_pos
				}, 750, 'swing')
				jQuery('.swm-navigation-bar').find("a.item:not(.swm-import-btn)").removeClass('active');
				jQuery('.swm-navigation-bar').find("a.item:not(.swm-import-btn)[href='"+section_id+"']").addClass("active");
				jQuery('#swm-mainform').find('.ui.segments').addClass('hidden');
				jQuery('#swm-mainform').find('#swm-resourse-selection').addClass('hidden');
				jQuery(section_id).parent().addClass('active').removeClass('hidden');
				jQuery('#swm-mainform').find('.ui.segments').last().addClass('active').removeClass('hidden');
				return false;
	    	}
	    },

	});
		  
	jQuery('#wb_swm_request_timeout').on('focusout', function(){
		var default_value = swm_ajax_object.wb_swm_request_timeout ? swm_ajax_object.wb_swm_request_timeout : 600;
		set_default_int_value_on_form_validate(default_value, jQuery(this) );
	});
	jQuery('#wb_swm_result_per_request').on('focusout', function(){
		var default_value = swm_ajax_object.wb_swm_result_per_request ? swm_ajax_object.wb_swm_result_per_request : 5;
		set_default_int_value_on_form_validate(default_value, jQuery(this) );
	});
	jQuery('#wb_swm_cats_per_request').on('focusout', function(){
		var default_value = swm_ajax_object.wb_swm_cats_per_request ? swm_ajax_object.wb_swm_cats_per_request : 10;
		set_default_int_value_on_form_validate(default_value, jQuery(this) );
	});

});

function set_default_int_value_on_form_validate( value, _this ){
	var field_value = _this.val();
	if( field_value.trim() == '' ){
		_this.val(value);
	}
}

jQuery(window).load(function(){
	swm_set_nav_bar_pos();
});

jQuery(window).scroll(function(){
	swm_set_nav_bar_pos();
});

jQuery(window).resize(function(){
	swm_set_nav_bar_pos();
})

var swm_admin_bar_top_pos = jQuery('.wrap.swm').offset().top;
function swm_set_nav_bar_pos(){
	var admin_bar_height = jQuery('#wpadminbar').outerHeight();
	var admin_bar_bottom_pos = swm_admin_bar_top_pos - admin_bar_height;
	var scrollTop = jQuery(window).scrollTop();
	var container_width = jQuery('.wrap.swm').innerWidth();
	var sticky_header_height = jQuery('.swm-navigation-bar').outerHeight() + 14;
	if(scrollTop >= admin_bar_bottom_pos){
		jQuery('#swm-mainform').css({
			'margin-top': sticky_header_height
		});
		jQuery('.swm-navigation-bar').addClass('swm-fixed-nav-bar');
		jQuery('.swm-navigation-bar.swm-fixed-nav-bar').width(container_width);
	}else{
		jQuery('#swm-mainform').css({
			'margin-top': 0
		});
		jQuery('.swm-navigation-bar').removeClass('swm-fixed-nav-bar');
		jQuery('.swm-navigation-bar').css('width', 'inherit');
	}
}

var step=0, type;
function swm_start_import(){
	console.log(selected_resources);
	console.log(selected_resources.length);
	//if( type == ''){
		// type = selected_resources[step];
		// step++;
	//}
	type = selected_resources[0];
	console.log(type);
	switch(type){
		case 'product':
			jQuery('#wb-swm-product-download-status').find('.ui.indicating.progress').addClass('active');
			jQuery('#wb-swm-product-download-status').find('.ui.indicating.progress').progress();
			jQuery('.ajax_loaded_content .swm-import-product-logs').empty().html('<h4 class="swm-import-completed-header">Importing Products</h4>');
			swm_import_product();
			break;
		case 'product-category':
			jQuery('#wb-swm-product-cat-download-status').find('.ui.indicating.progress').addClass('active');
			jQuery('#wb-swm-product-cat-download-status').find('.ui.indicating.progress').progress();
			jQuery('.ajax_loaded_content .swm-import-cats-logs').empty().html('<h4 class="swm-import-completed-header">Importing Product Categories</h4>');
			swm_import_product_cat();
			break;		
		default:
			break;
	}
	if( selected_resources.length <= 0 ){
		jQuery('.mini.modal.swm-import-complete')
		  .modal('show')
		;
		jQuery('.swm-start-importer-btn').removeClass('swm-import-running');
		jQuery('.swm-start-importer-btn').removeClass('disabled');
		jQuery('.swm-running-import-loader').addClass('hidden').removeClass('active');
	}

	if( step >= selected_resources.length ){
		step=0;
	}
}

function swm_import_product(){
	var start_time = new Date().getTime();
	jQuery.ajax({
		type: 'get',
		url:  swm_ajax_object.ajax_url,
		data: {
			'action': 'swm_import_product',
			'swm_ajax_nonce' : swm_ajax_object.nonce
		}, 
		success: function(result){
			
			if( result.write_error){
				//jQuery('.ajax_loaded_content').append('The Upload Directory is not set to proper permission. It needs to write permission enable');
			}

			if(
				(result.swm_import_records.currently_imported_page <= result.swm_import_records.total_pages) &&
				(result.swm_import_records.total_import_product <= result.swm_import_records.total_shopify_products)
			){
				jQuery('.ajax_loaded_content .swm-import-product-logs').append(result.imported_products_title);

				jQuery('#wb-swm-product-download-status').find('.ui.indicating.progress').addClass('active');
				var total_shopify_products = result.swm_import_records.total_shopify_products;
				var total_imported_products = result.swm_import_records.total_import_product;
				console.log( 'total_shopify_products '+total_shopify_products );
				console.log( 'total_imported_products '+total_imported_products );
				var import_percentage = ((total_imported_products/total_shopify_products)*100);
				import_percentage = import_percentage.toFixed();
				jQuery('#wb-swm-product-download-status').find('.ui.indicating.progress').progress('set percent', import_percentage);
				jQuery('#wb-swm-product-download-status').find('.label').text(import_percentage+'%');

				result.imported_products_title='';
				swm_go_to_bottom_of_element('.ajax_loaded_content');
				swm_import_product();
			}else{
				if( typeof result.status !== 'undefined' && result.status === 'completed' ){
					jQuery('.ajax_loaded_content .swm-import-product-logs').append(result.imported_products_title);
					result.imported_products_title='';
					jQuery('.ajax_loaded_content .swm-import-product-logs').append('<h4 class="swm-import-completed-header">Products Import Completed</h4>');
					console.log('finish');
					// var import_percentage = 100;
					var total_shopify_products = result.swm_import_records.total_shopify_products;
					var total_imported_products = result.swm_import_records.total_import_product;
					console.log( 'total_shopify_products '+total_shopify_products );
					console.log( 'total_imported_products '+total_imported_products );
					var import_percentage = ((total_imported_products/total_shopify_products)*100);
					import_percentage = import_percentage.toFixed();
					jQuery('#wb-swm-product-download-status').find('.ui.indicating.progress').progress('set percent', import_percentage);
					jQuery('#wb-swm-product-download-status').find('.label').text(import_percentage+'%');

					selected_resources.shift();
					swm_go_to_bottom_of_element('.ajax_loaded_content');
					swm_start_import();
				}
			}
			console.log(result);
		},
		error: function(result){
			console.log('error');
			console.log(result);
		}
	});
}

function swm_import_product_cat(){
	console.log('swm_import_product_cat');
	var start_time = new Date().getTime();
	jQuery.ajax({
		type: 'get',
		url:  swm_ajax_object.ajax_url,
		data: {
			'action': 'swm_import_custom_collection',
			'swm_ajax_nonce' : swm_ajax_object.nonce
		}, 

		success: function(result){
			// .swm-import-cats-logs
			if( result.write_error){
				//jQuery('.ajax_loaded_content').append('The Upload Directory is not set to proper permission. It needs to write permission enable');
			}
			if(
				(result.swm_import_custom_collection_records.currently_imported_page <= result.swm_import_custom_collection_records.total_pages) &&
				(result.swm_import_custom_collection_records.total_import_collections <= result.swm_import_custom_collection_records.total_shopify_collections)
			){
				var total_shopify_collections = result.swm_import_custom_collection_records.total_shopify_collections;
				var total_imported_collections = result.swm_import_custom_collection_records.total_import_collections;
				console.log( 'total_shopify_collections '+total_shopify_collections );
				console.log( 'total_imported_collections '+total_imported_collections );
				var import_percentage = ((total_imported_collections/total_shopify_collections)*100);
				import_percentage = import_percentage.toFixed();
				jQuery('#wb-swm-product-cat-download-status').find('.ui.indicating.progress').progress('set percent', import_percentage);
				jQuery('#wb-swm-product-cat-download-status').find('.label').text(import_percentage+'%');

				jQuery('.ajax_loaded_content .swm-import-cats-logs').append(result.imported_custom_collection_title);
				result.imported_custom_collection_title='';
				swm_go_to_bottom_of_element('.ajax_loaded_content');
				swm_import_product_cat();
			}else{
				if( typeof result.status !== 'undefined' && result.status === 'completed' ){
					jQuery('.ajax_loaded_content .swm-import-cats-logs').append(result.imported_custom_collection_title);
					// var import_percentage = 100;
					var total_shopify_collections = result.swm_import_custom_collection_records.total_shopify_collections;
					var total_imported_collections = result.swm_import_custom_collection_records.total_import_collections;
					console.log( 'total_shopify_collections '+total_shopify_collections );
					console.log( 'total_imported_collections '+total_imported_collections );
					var import_percentage = ((total_imported_collections/total_shopify_collections)*100);
					import_percentage = import_percentage.toFixed();
					jQuery('#wb-swm-product-cat-download-status').find('.ui.indicating.progress').progress('set percent', import_percentage);
					jQuery('#wb-swm-product-cat-download-status').find('.label').text(import_percentage+'%');

					result.imported_custom_collection_title='';
					jQuery('.ajax_loaded_content .swm-import-cats-logs').append('<h4 class="swm-import-completed-header">Product Categories Import Completed</h4>');
					console.log('finish collection');
					selected_resources.shift();
					swm_go_to_bottom_of_element('.ajax_loaded_content');
					swm_start_import();
				}
			}
			console.log(result);
		},
		error: function(result){
			console.log('error');
			console.log(result);
		}
	});
}

// Admin Page Script
jQuery(window).load(function(){
	jQuery('.swm .accordion').accordion({
		exclusive: false
	});
	jQuery('.swm .ui.dropdown').dropdown();
	jQuery('.swm .ui.checkbox').checkbox();
});

function swm_go_to_bottom_of_element( element ){
	var message_body_scrollheight = jQuery(element).prop('scrollHeight');
	console.log('message_body_scrollheight '+message_body_scrollheight);
    jQuery(element).scrollTop(message_body_scrollheight);
}