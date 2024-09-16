'use strict'
var step=0, type, get_product_import_status_interval;
var wcpi_admin_bar_top_pos = jQuery('.wrap.wcpi').offset().top;
var selected_resources = [];
jQuery(document).ready(function(){
	wcpi_set_nav_bar_pos();
	jQuery('.wb_wcpi_calendar').calendar({
		type: 'datetime'
	});

	jQuery( '.wcpi-change-url-btn' ).on( 'click', function() {
		jQuery('#wb_wcpi_auth_button').removeClass( 'disable-auth-btn' );
		jQuery( this ).hide();
	} );

	jQuery( '#wb_wcpi_store_url' ).on( 'keyup', function( e ) {
		var store_url = jQuery( this ).val();
		store_url = store_url.replace(/\s/g, '');
		if( store_url ) {
			jQuery('#wb_wcpi_auth_button').removeClass( 'disable-auth-btn' );
		}else {
			jQuery('#wb_wcpi_auth_button').addClass( 'disable-auth-btn' );
		}
	});

	jQuery( '#wb_wcpi_auth_button' ).on( 'click', function( e ) {
		e.preventDefault();
		var auth_params = {
			"app_name": wcpi_ajax_object.app_name,
			"scope": wcpi_ajax_object.scope,
			"user_id": wcpi_ajax_object.user_id,
			"return_url": wcpi_ajax_object.return_url,
			"callback_url": wcpi_ajax_object.callback_url
		};
		var urlParams = new URLSearchParams(auth_params).toString();
		var store_url = jQuery( '#wb_wcpi_store_url' ).val();
		store_url = store_url.replace(/\s/g, '');
		var endpoint = '/wc-auth/v1/authorize';
		
		if( store_url ) {
			jQuery.ajax({
				type: 'post',
				url:  wcpi_ajax_object.ajax_url,
				data: {
					action: 'wcpi_set_imported_store_url',
					nonce : wcpi_ajax_object.nonce,
					store : store_url
				}, 
				success: function(result){
					console.log('wpci_get_import_percent success start');
					window.location.href = store_url + endpoint + '?' + urlParams;
				}
			});
		}else{
			alert( 'please enter a valid url' );
		}
		
		
	} );
	
	jQuery('.wcpi-start-importer-btn').on('click', function(e){
		e.preventDefault();
		jQuery( '.wcpi-ajax-import-start-error-msg' ).remove();
		if( !jQuery(this).hasClass('wcpi-import-running') ){
			jQuery(this).addClass('wcpi-import-running');
			jQuery(this).addClass('disabled');
			jQuery('#submit').parent().addClass('wcpi-import-running');
			jQuery('#submit').parent().addClass('disabled');
			jQuery('.wcpi-running-import-loader').removeClass('hidden').addClass('active');
			selected_resources = [];
			wcpi_reset_progress_bar();
			jQuery('#wcpi-resourse-selection .wcpi-switch-input:checked').each(function(){
				var value = jQuery(this).val();
				if( value == 'enable' ){
					var type = jQuery(this).parents('.wcpi-container').data('resource-type');
					selected_resources.push(type);
					jQuery(this).parents('tr').find('.ui.indicating.progress').removeClass('wcpi-hidden');
				}
			});
			//jQuery('.ajax_loaded_content').empty();
			jQuery('.ajax_loaded_content .wcpi-import-product-logs').empty();
			jQuery('.ajax_loaded_content .wcpi-import-cats-logs').empty();
			jQuery('.ajax_loaded_content .wcpi-import-customer-logs').empty();
			jQuery('.ajax_loaded_content .wcpi-import-order-logs').empty();
			
			jQuery('#wb-wcpi-product-download-status').find('.ui.indicating.progress').addClass('active');
			jQuery('#wb-wcpi-product-download-status').find('.ui.indicating.progress').progress();
			jQuery('.ajax_loaded_content .wcpi-import-product-logs').empty().html('<h4 class="wcpi-import-completed-header">Importing Products</h4>');
			wcpi_import_product();
		}
	});

	
	if( wcpi_ajax_object.import_running === '0' ){
		jQuery('#wcpi-mainform').find('.ui.segments').addClass('hidden');
		jQuery('#wcpi-mainform').find('#wcpi-resourse-selection').addClass('hidden');
		jQuery('#wcpi-mainform').find('.ui.segments').first().addClass('active').removeClass('hidden');
		jQuery('#wcpi-mainform').find('.ui.segments').last().addClass('active').removeClass('hidden');
	}

	if( wcpi_ajax_object.import_running === '1' ){
		jQuery('#wcpi-mainform').find('.ui.segments').addClass('hidden');
		jQuery('#wcpi-mainform').find('#wcpi-resourse-selection').addClass('active');
		//jQuery(section_id).parent().addClass('active').removeClass('hidden');
		//jQuery('#wcpi-mainform').find('.ui.segments').last().addClass('active').removeClass('hidden');

		jQuery('#wb-wcpi-product-download-status').find('.ui.indicating.progress').removeClass('wcpi-hidden').addClass('active');
		jQuery('#wb-wcpi-product-download-status').find('.ui.indicating.progress').progress();
	}

	jQuery('.wcpi-navigation-bar .item:not(.wcpi-import-btn)').on('click', function(e){
		e.preventDefault();
		var section_id = jQuery(this).attr('href');
		// var section_top_pos = jQuery(section_id).offset().top - jQuery('.wcpi-navigation-bar').outerHeight() - 50;
		var section_top_pos = jQuery('#wcpi-mainform').offset().top - jQuery('.wcpi-navigation-bar').outerHeight() - 50;
		jQuery('html, body').stop().animate({
			scrollTop: section_top_pos
		}, 750, 'swing')
		jQuery(this).siblings('.item').removeClass('active');
		jQuery(this).addClass('active');
		jQuery('#wcpi-mainform').find('.ui.segments').addClass('hidden');
		jQuery('#wcpi-mainform').find('#wcpi-resourse-selection').addClass('hidden');
		jQuery(section_id).parent().addClass('active').removeClass('hidden');
		jQuery('#wcpi-mainform').find('.ui.segments').last().addClass('active').removeClass('hidden');

	});

	jQuery('.wcpi-import-btn').on('click', function(e){
		e.preventDefault();
		var section_id = jQuery(this).attr('href');
		jQuery(this).siblings('.item').removeClass('active');
		jQuery('#wcpi-mainform').find('.ui.segments').addClass('hidden');
		jQuery(section_id).addClass('active').removeClass('hidden');
		// jQuery('#wcpi-mainform').find('.ui.segments').last().addClass('active').removeClass('hidden');
	});

	var invalid_field_instance = 0;
	jQuery('#wcpi-mainform')
	  .form({
	    fields: {
	      store_url: {
	        identifier: 'wb_wcpi_store_url',
	        rules: [
	          {
	            type   : 'empty',
	            prompt : '<strong>Store URL</strong> Should not be Empty'
	          }
	        ]
	      },
	      api_key: {
	        identifier: 'wb_wcpi_consumer_key',
	        rules: [
	          {
	            type   : 'empty',
	            prompt : '<strong>Consumer Key</strong> Should not be Empty'
	          }
	        ]
	      },
	      api_pwd: {
	        identifier: 'wb_wcpi_consumer_secret',
	        rules: [
	          {
	            type   : 'empty',
	            prompt : '<strong>Consumer Secret</strong> Should not be Empty'
	          }
	        ]
	      },
	      request_timeout: {
	        identifier: 'wb_wcpi_request_timeout',
	        rules: [
	          {
	            type   : 'integer',
	            prompt : '<strong>Request Timeout</strong> Field Must be an Integer'
	          }
	        ]
	      },
	      product_per_request: {
	        identifier: 'wb_wcpi_result_per_request',
	        rules: [
	          {
	            type   : 'integer',
	            prompt : '<strong>Products Per Request</strong> Field Must be an Integer'
	          }
	        ]
	      },
	      cats_per_request: {
	        identifier: 'wb_wcpi_cats_per_request',
	        rules: [
	          {
	            type   : 'integer',
	            prompt : '<strong>Categories Per Request</strong> Field Must be an Integer'
	          }
	        ]
	      },
	      customer_per_request: {
	        identifier: 'wb_wcpi_customer_per_request',
	        rules: [
	          {
	            type   : 'integer',
	            prompt : '<strong>Customer Per Request</strong> Field Must be an Integer'
	          }
	        ]
	      },
	      order_per_request: {
	        identifier: 'wb_wcpi_order_per_request',
	        rules: [
	          {
	            type   : 'integer',
	            prompt : '<strong>Order Per Request</strong> Field Must be an Integer'
	          }
	        ]
	      },
	    },
	    onInvalid : function(){
	    	// var _this = jQuery(this);
	    	var _this = jQuery('#wcpi-mainform').find('.field.error').eq(0);
	    	var section_id = '#'+_this.parents('table').parents('.ui.segments').children('.title').attr('id');
	    	console.log('section_id '+section_id);
	    	if( section_id ){
	    		var section_top_pos = jQuery('#wcpi-mainform').offset().top - jQuery('.wcpi-navigation-bar').outerHeight() - 50;
				jQuery('html, body').stop().animate({
					scrollTop: section_top_pos
				}, 750, 'swing')
				jQuery('.wcpi-navigation-bar').find("a.item:not(.wcpi-import-btn)").removeClass('active');
				jQuery('.wcpi-navigation-bar').find("a.item:not(.wcpi-import-btn)[href="+section_id+"]").addClass("active");
				jQuery('#wcpi-mainform').find('.ui.segments').addClass('hidden');
				jQuery('#wcpi-mainform').find('#wcpi-resourse-selection').addClass('hidden');
				jQuery(section_id).parent().addClass('active').removeClass('hidden');
				jQuery('#wcpi-mainform').find('.ui.segments').last().addClass('active').removeClass('hidden');
				return false;
	    	}
	    },

	});
	
	wpci_get_import_percent();
	get_product_import_status_interval = setInterval(function(){
		wpci_get_import_percent();
	},10000);

	jQuery('#wb_wcpi_request_timeout').on('focusout', function(){
		var default_value = wcpi_ajax_object.wb_wcpi_request_timeout ? wcpi_ajax_object.wb_wcpi_request_timeout : 600;
		set_default_int_value_on_form_validate(default_value, jQuery(this) );
	});
	jQuery('#wb_wcpi_result_per_request').on('focusout', function(){
		var default_value = wcpi_ajax_object.wb_wcpi_result_per_request ? wcpi_ajax_object.wb_wcpi_result_per_request : 5;
		set_default_int_value_on_form_validate(default_value, jQuery(this) );
	});

});

function wpci_get_import_percent(){
	console.log('wpci_get_import_percent start');
	jQuery.ajax({
		type: 'get',
		url:  wcpi_ajax_object.ajax_url,
		data: {
			action: 'wcpi_get_import_status',
			nonce : wcpi_ajax_object.nonce
		}, 
		success: function(result){
			console.log('wpci_get_import_percent success start');
			var response = (result.data);
			if( false !== jQuery.inArray( response.status, [ 'running', 'completed', 'unknown' ] ) ) {
				switch(response.status){
					case 'running':
					case 'completed':
						var import_percentage = (response.total_imported_products / response.total_products_from_api) * 100;
						import_percentage = import_percentage.toFixed();
						if( import_percentage > 0 ){
							jQuery('#wb-wcpi-product-download-status').find('.ui.indicating.progress').progress('set percent', import_percentage);
							jQuery('#wb-wcpi-product-download-status').find('.label').text(import_percentage+'%');

							if( response.type === 'additional_products' ) {
								console.log( 'test additional products' );
								jQuery('#wb-wcpi-product-download-status').find('.label').html('<span class="wcpi-light-weight">(Importing Additional Products)</span> '+import_percentage+'%');
							}

						}

						if( import_percentage >= 100 ){
							jQuery('.mini.modal.wcpi-import-complete').modal('show');
							jQuery('.wcpi-start-importer-btn').removeClass('wcpi-import-running');
							jQuery('.wcpi-start-importer-btn').removeClass('disabled');
							jQuery('#submit').parent().removeClass('wcpi-import-running');
							jQuery('#submit').parent().removeClass('disabled');
							jQuery('.wcpi-running-import-loader').addClass('hidden').removeClass('active');
						}
						break;
					default:
						clearInterval( get_product_import_status_interval );
						break;
				}
			}else {
				clearInterval( get_product_import_status_interval );
			}
		},
		error: function(result){
			clearInterval( get_product_import_status_interval );
			console.log(result);
		}
	});
}
function wcpi_reset_progress_bar(){
	jQuery('#wcpi-resourse-selection .ui.indicating.progress').each(function(){
		if( jQuery(this).attr('data-percent') ){
			jQuery(this).removeAttr('data-percent').removeClass('active success').addClass('wcpi-hidden');
			jQuery(this).find('.bar').removeAttr('style').removeClass('success');
			jQuery(this).find('.label').text('Waiting');
		}
	});
	
}

function set_default_int_value_on_form_validate( value, _this ){
	var field_value = _this.val();
	if( field_value.trim() == '' ){
		_this.val(value);
	}
}

jQuery(window).load(function(){
	wcpi_set_nav_bar_pos();
});

jQuery(window).scroll(function(){
	wcpi_set_nav_bar_pos();
});

jQuery(window).resize(function(){
	wcpi_set_nav_bar_pos();
})

function wcpi_set_nav_bar_pos(){
	var admin_bar_height = jQuery('#wpadminbar').outerHeight();
	var admin_bar_bottom_pos = wcpi_admin_bar_top_pos - admin_bar_height;
	var scrollTop = jQuery(window).scrollTop();
	var container_width = jQuery('.wrap.wcpi').innerWidth();
	var sticky_header_height = jQuery('.wcpi-navigation-bar').outerHeight() + 14;
	if(scrollTop >= admin_bar_bottom_pos){
		jQuery('#wcpi-mainform').css({
			'margin-top': sticky_header_height
		});
		jQuery('.wcpi-navigation-bar').addClass('wcpi-fixed-nav-bar');
		jQuery('.wcpi-navigation-bar.wcpi-fixed-nav-bar').width(container_width);
	}else{
		jQuery('#wcpi-mainform').css({
			'margin-top': 0
		});
		jQuery('.wcpi-navigation-bar').removeClass('wcpi-fixed-nav-bar');
		jQuery('.wcpi-navigation-bar').css('width', 'inherit');
	}
}

function wcpi_start_import(){
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
			
			break;
		case 'pages':
			break;
		case 'posts':
			break;			
		default:
			break;
	}
	if( selected_resources.length <= 0 ){
		
	}

	if( step >= selected_resources.length ){
		step=0;
	}
}

function wcpi_import_product(){
	var start_time = new Date().getTime();
	
	jQuery.ajax({
		type: 'get',
		url:  wcpi_ajax_object.ajax_url,
		data: {
			action: 'wcpi_import_product',
			nonce : wcpi_ajax_object.nonce
		}, 
		success: function(result){
			
			if( result.write_error){
				//jQuery('.ajax_loaded_content').append('The Upload Directory is not set to proper permission. It needs to write permission enable');
			}

			/*if(
				(result.wcpi_import_records.currently_imported_page <= result.wcpi_import_records.total_pages) &&
				(result.wcpi_import_records.total_import_product <= result.wcpi_import_records.total_shopify_products)
			){
				jQuery('.ajax_loaded_content .wcpi-import-product-logs').append(result.imported_products_title);

				jQuery('#wb-wcpi-product-download-status').find('.ui.indicating.progress').addClass('active');
				var total_shopify_products = result.wcpi_import_records.total_shopify_products;
				var total_imported_products = result.wcpi_import_records.total_import_product;
				console.log( 'total_shopify_products '+total_shopify_products );
				console.log( 'total_imported_products '+total_imported_products );
				var import_percentage = ((total_imported_products/total_shopify_products)*100);
				import_percentage = import_percentage.toFixed();
				jQuery('#wb-wcpi-product-download-status').find('.ui.indicating.progress').progress('set percent', import_percentage);
				jQuery('#wb-wcpi-product-download-status').find('.label').text(import_percentage+'%');

				result.imported_products_title='';
				wcpi_go_to_bottom_of_element('.ajax_loaded_content');
				wcpi_import_product();
			}else{
				if( typeof result.status !== 'undefined' && result.status === 'completed' ){
					jQuery('.ajax_loaded_content .wcpi-import-product-logs').append(result.imported_products_title);
					result.imported_products_title='';
					jQuery('.ajax_loaded_content .wcpi-import-product-logs').append('<h4 class="wcpi-import-completed-header">Products Import Completed</h4>');
					console.log('finish');
					// var import_percentage = 100;
					var total_shopify_products = result.wcpi_import_records.total_shopify_products;
					var total_imported_products = result.wcpi_import_records.total_import_product;
					console.log( 'total_shopify_products '+total_shopify_products );
					console.log( 'total_imported_products '+total_imported_products );
					var import_percentage = ((total_imported_products/total_shopify_products)*100);
					import_percentage = import_percentage.toFixed();
					jQuery('#wb-wcpi-product-download-status').find('.ui.indicating.progress').progress('set percent', import_percentage);
					jQuery('#wb-wcpi-product-download-status').find('.label').text(import_percentage+'%');

					selected_resources.shift();
					wcpi_go_to_bottom_of_element('.ajax_loaded_content');
					wcpi_start_import();
				}
			}*/
			console.log(result);
			get_product_import_status_interval = setInterval(function(){
				console.log('wpci_get_import_percent call init');
				wpci_get_import_percent();
			},10000);
		},
		error: function(result){
			console.log('error');
			console.log(result);
			clearInterval( get_product_import_status_interval );
			wcpi_reset_import_state();
			jQuery('.ajax_loaded_content .wcpi-import-product-logs').empty().before( '<div class="wcpi-ajax-import-start-error-msg" style="color: red; font-size: 16px">Something Went Wrong. Cannot start the Import.</div>' )
		}
	});
}




// Admin Page Script
jQuery(window).load(function(){
	jQuery('.wcpi .accordion').accordion({
		exclusive: false
	});
	jQuery('.wcpi .ui.dropdown').dropdown();
	jQuery('.wcpi .ui.checkbox').checkbox();
});

function wcpi_go_to_bottom_of_element( element ){
	var message_body_scrollheight = jQuery(element).prop('scrollHeight');
	console.log('message_body_scrollheight '+message_body_scrollheight);
    jQuery(element).scrollTop(message_body_scrollheight);
}

function wcpi_reset_import_state() {
	jQuery.ajax({
		type: 'get',
		url:  wcpi_ajax_object.ajax_url,
		data: {
			action: 'wcpi_reset_import_status',
			nonce : wcpi_ajax_object.nonce
		}, 
		complete: function(result){
			console.log(result);
			jQuery( '.wcpi-start-importer-btn' ).removeClass( 'wcpi-import-running disabled' );
			jQuery('.wcpi-running-import-loader').removeClass( 'active' ).addClass( 'hidden' );
		}
	});
}