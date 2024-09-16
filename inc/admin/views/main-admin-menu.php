<?php
/**
 * WCPI Admin Menu Page View
 *
 * @package PluginDevs
 * @since   0.9.0
 */

	defined( 'ABSPATH' ) || exit;

	$settings = wcpi_settings();

	// General Options.
	$wb_wcpi_store_url       = get_option( 'wb_wcpi_store_url' ) ? get_option( 'wb_wcpi_store_url' ) : '';
	$wb_wcpi_consumer_key    = get_option( 'wb_wcpi_consumer_key' ) ? get_option( 'wb_wcpi_consumer_key' ) : '';
	$wb_wcpi_consumer_secret = get_option( 'wb_wcpi_consumer_secret' ) ? get_option( 'wb_wcpi_consumer_secret' ) : '';

	// Product Options.
	$wb_wcpi_background                 = get_option( 'wb_wcpi_background' ) ? get_option( 'wb_wcpi_background' ) : WCPI_DEFAULT_BACKGROUND_IMPORT;
	$wb_wcpi_default_product_categories = get_option( 'wb_wcpi_default_product_categories' ) ? get_option( 'wb_wcpi_default_product_categories' ) : WCPI_DEFAULT_PRODUCT_CATEGORIES;
	$wb_wcpi_default_product_tags       = get_option( 'wb_wcpi_default_product_tags' ) ? get_option( 'wb_wcpi_default_product_tags' ) : WCPI_DEFAULT_PRODUCT_TAGS;
	$wb_wcpi_request_timeout            = get_option( 'wb_wcpi_request_timeout' ) ? get_option( 'wb_wcpi_request_timeout' ) : WCPI_DEFAULT_REQUEST_TIMEOUT;
	$wb_wcpi_result_per_request         = get_option( 'wb_wcpi_result_per_request' ) ? get_option( 'wb_wcpi_result_per_request' ) : WCPI_DEFAULT_RESULT_PER_REQUEST;
	$wb_wcpi_product_filter_cat_id      = get_option( 'wb_wcpi_product_filter_cat_id' ) ? get_option( 'wb_wcpi_product_filter_cat_id' ) : '';
	$wb_wcpi_product_filter_tag_id      = get_option( 'wb_wcpi_product_filter_tag_id' ) ? get_option( 'wb_wcpi_product_filter_tag_id' ) : '';

	$wb_wcpi_filter_product_status = get_option( 'wb_wcpi_filter_product_status' ) ? get_option( 'wb_wcpi_filter_product_status' ) : WCPI_DEFAULT_FILTER_PRODUCT_STATUS;
	$wb_wcpi_filter_product_type   = get_option( 'wb_wcpi_filter_product_type' ) ? get_option( 'wb_wcpi_filter_product_type' ) : WCPI_DEFAULT_FILTER_PRODUCT_TYPE;

	$wb_wcpi_filter_product_order        = get_option( 'wb_wcpi_filter_product_order' ) ? get_option( 'wb_wcpi_filter_product_order' ) : WCPI_DEFAULT_FILTER_PRODUCT_ORDER;
	$wb_wcpi_filter_product_orderby      = get_option( 'wb_wcpi_filter_product_orderby' ) ? get_option( 'wb_wcpi_filter_product_order' ) : WCPI_DEFAULT_FILTER_PRODUCT_ORDERBY;
	$wb_wcpi_filter_product_date_after   = get_option( 'wb_wcpi_filter_product_date_after' ) ? get_option( 'wb_wcpi_filter_product_date_after' ) : '';
	$wb_wcpi_filter_product_date_before  = get_option( 'wb_wcpi_filter_product_date_before' ) ? get_option( 'wb_wcpi_filter_product_date_before' ) : '';
	$wb_wcpi_filter_sku                  = get_option( 'wb_wcpi_filter_sku' ) ? get_option( 'wb_wcpi_filter_sku' ) : '';
	$wb_wcpi_filter_include_id           = get_option( 'wb_wcpi_filter_include_id' ) ? get_option( 'wb_wcpi_filter_include_id' ) : '';
	$wb_wcpi_filter_exclude_id           = get_option( 'wb_wcpi_filter_exclude_id' ) ? get_option( 'wb_wcpi_filter_exclude_id' ) : '';
	$wb_wcpi_filter_min_price            = get_option( 'wb_wcpi_filter_min_price' ) ? get_option( 'wb_wcpi_filter_min_price' ) : '';
	$wb_wcpi_filter_max_price            = get_option( 'wb_wcpi_filter_max_price' ) ? get_option( 'wb_wcpi_filter_max_price' ) : '';
	$wb_wcpi_filter_product_stock_status = get_option( 'wb_wcpi_filter_product_stock_status' ) ? get_option( 'wb_wcpi_filter_product_stock_status' ) : WCPI_DEFAULT_FILTER_PRODUCT_STOCK_STATUS;

?>
<div class="wrap woocommerce wcpi ">
	<div class="ui inverted menu wcpi-navigation-bar">
		<a href="#general-settings" class="green item 
		<?php
		if ( ! $settings->maybe_import_run() ) {
			echo 'active'; }
		?>
		"><?php esc_html_e( 'General Settings', 'wcpi' ); ?> </a>
		<a href="#product-settings" class="green item"><?php esc_html_e( 'Product Settings', 'wcpi' ); ?></a>
		<a href="#import-settings" class="green item"><?php esc_html_e( 'Import Settings', 'wcpi' ); ?></a>
		<a href="#wcpi-resourse-selection" class="blue item wcpi-import-btn"><?php esc_html_e( 'Import', 'wcpi' ); ?></a>
	</div>
	<form action="options.php" class="ui form" method="post" id="wcpi-mainform">
		<?php settings_fields( 'wcpi_settings_options' ); ?>

		<!-- <div class="ui fluid container segment"> -->
			<!-- <div class="ui styled fluid accordion"> -->
			<div class="ui error message" 
			<?php
			if ( ! wcpi_check_api_key() ) {
				echo 'style="display: block;"'; }
			?>
			>
				<p><?php esc_html_e( 'You need to enter Valid Domain, API key and API secret to Migrate from another Store', 'wcpi' ); ?></p>
			</div>
			<div class="ui error message"></div>
			<div class="ui raised segments 
			<?php
			if ( $settings->maybe_import_run() ) {
				echo 'hidden'; }
			?>
			">
				<div id="general-settings" class="title wcpi-border-bottom active bg-grey">
					<h3 class="ui block header">
					<?php esc_html_e( 'General Settings', 'wcpi' ); ?>
					</h3>
				</div>
				<div class="content active">
					<table class="ui padded table wcpi-no-border">
						<tbody>
							<tr>							
								<td class="three wide">
									<label for="wb_wcpi_store_url"><?php esc_html_e( 'Website URL', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<input name="wb_wcpi_store_url" id="wb_wcpi_store_url" type="text" style="" value="<?php echo esc_attr( $wb_wcpi_store_url ); ?>" class="" name="wb_wcpi_store_url" placeholder="">
									<div class="wcpi-auth-btn">
										<input name="wb_wcpi_auth_button" id="wb_wcpi_auth_button" type="button" value="<?php echo esc_attr( 'Authenticate' ); ?>" class="button button-primary 
																																   <?php
																																	if ( wcpi_check_api_key() || ! wp_http_validate_url( $wb_wcpi_store_url ) ) {
																																		echo 'disable-auth-btn'; }
																																	?>
										" name="wb_wcpi_auth_button" placeholder="">
										<?php if ( wcpi_check_api_key() ) { ?>
											<a href="#" class="wcpi-change-url-btn"> <?php esc_html_e( 'Change Credentials', 'wcpi' ); ?> </a>
										<?php } ?>
									</div>
								</td>
							</tr>
							<tr valign="top">
								<td scope="row" class="three wide titledesc">
									<label for="wb_wcpi_consumer_key"><?php esc_html_e( 'Consumer Key', 'wcpi' ); ?></label>
								</td>
								<td class="forminp forminp-text field">
									<input name="wb_wcpi_consumer_key" id="wb_wcpi_consumer_key" type="text" style="" value="<?php echo esc_attr( $wb_wcpi_consumer_key ); ?>" class="" name="wb_wcpi_consumer_key" placeholder="">
								</td>
							</tr>
							<tr valign="top">
								<td scope="row" class="three wide titledesc">
									<label for="wb_wcpi_consumer_secret"><?php esc_html_e( 'Consumer Secret', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<input name="wb_wcpi_consumer_secret" id="wb_wcpi_consumer_secret" type="text" style="" value="<?php echo esc_attr( $wb_wcpi_consumer_secret ); ?>" class="" name="wb_wcpi_consumer_secret" placeholder="">
								</td>
							</tr>
							<tr valign="top">
								<td scope="row" class="three wide titledesc">
									<label for="wb_wcpi_request_timeout"><?php esc_html_e( 'Request Timeout(s)', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<input name="wb_wcpi_request_timeout" id="wb_wcpi_request_timeout" type="text" style="" value="<?php echo esc_attr( $wb_wcpi_request_timeout ); ?>" class="" name="wb_wcpi_request_timeout" placeholder="">
								</td>
							</tr>
							<tr valign="top" class="wcpi-d-none">
								<td scope="row" class="three wide titledesc">
									<label for="wb-wcpi-enable-background"><?php esc_html_e( 'Background Importing', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<div class="ui toggle checkbox">
									<input type="checkbox"  name="wb_wcpi_background" value="on" id="wb-wcpi-enable-background" <?php echo checked( $wb_wcpi_background, 'on' ); ?>>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<div class="ui raised segments hidden">
				<div id='product-settings' class="title wcpi-border-bottom active bg-grey"><!-- Start Product Settings -->
					<h3 class="ui block header">
						<?php esc_html_e( 'Product Settings', 'wcpi' ); ?>
					</h3>
				</div>
				<div class="content">		
					<table class="ui padded table wcpi-no-border">
						<tbody>
							<tr>							
								<td class="three wide ">
									<label for="wb_wcpi_product_status"><?php esc_html_e( 'Product Status', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<select class="ui search dropdown" name="wb_wcpi_product_status" id="wb_wcpi_product_status">
										<option value="inherit" <?php echo selected( $settings->get_default_product_status(), 'inherit' ); ?> ><?php esc_html_e( 'Inherit (From Import)', 'wcpi' ); ?></option>
										<option value="publish" <?php echo selected( $settings->get_default_product_status(), 'publish' ); ?> ><?php esc_html_e( 'Publish', 'wcpi' ); ?></option>
										<option value="pending" <?php echo selected( $settings->get_default_product_status(), 'pending' ); ?>><?php esc_html_e( 'Pending', 'wcpi' ); ?></option>
										<option value="draft" <?php echo selected( $settings->get_default_product_status(), 'draft' ); ?>><?php esc_html_e( 'Draft', 'wcpi' ); ?></option>
									</select>
									<span class="ui small error text"><?php esc_html_e( 'You Can Set Default Product Status from here', 'wcpi' ); ?></span>
								</td>
							</tr>
							<tr valign="top">
								<td scope="row" class="three wide titledesc">
									<label for="wb-wcpi-enable-images"><?php esc_html_e( 'Download Images', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<div class="ui toggle checkbox">
										<input type="checkbox" name="wb_wcpi_download_images" value="on" data-value="<?php echo esc_attr( $settings->get_download_images_status() ); ?>" id="wb-wcpi-enable-images" <?php echo checked( $settings->get_download_images_status(), true ); ?>>
									</div>
								</td>
							</tr>
							<tr>							
								<td class="three wide">
									<label for="wb_wcpi_default_product_categories"><?php esc_html_e( 'Default Product Categories', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<select multiple="" class="ui search dropdown" name="wb_wcpi_default_product_categories[]" id="wb_wcpi_default_product_categories">
										<?php
											$product_cat_args  = array(
												'taxonomy' => 'product_cat',
												'hide_empty' => false,
											);
											$product_cat_terms = get_terms( $product_cat_args );
											if ( ! empty( $product_cat_terms ) ) {
												foreach ( $product_cat_terms as $key => $value ) {
													?>
													<option 
													<?php
													if ( in_array( $value->slug, $wb_wcpi_default_product_categories ) ) { //phpcs:ignore
														echo 'selected="selected"';
													};
													?>
													value="<?php echo esc_attr( $value->slug ); ?>"><?php echo esc_html( $value->name ); ?></option>
													<?php
												}
											} else {
												?>
											<option value=""><?php esc_html_e( 'No Product Categories Found', 'wcpi' ); ?></option>
										<?php } ?>
									</select>
									<span class="ui small error text"><?php esc_html_e( 'You Can Assign Default Product Category from here', 'wcpi' ); ?></span>
								</td>
							</tr>
							<tr>							
								<td class="three wide">
									<label for="wb_wcpi_default_product_tags"><?php esc_html_e( 'Default Product Tags', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<select multiple="" class="ui search dropdown" name="wb_wcpi_default_product_tags[]" id="wb_wcpi_default_product_tags">
										<?php
											$product_tag_args  = array(
												'taxonomy' => 'product_tag',
												'hide_empty' => false,
											);
											$product_tag_terms = get_terms( $product_tag_args );
											if ( ! empty( $product_tag_terms ) ) {
												foreach ( $product_tag_terms as $key => $value ) {
													?>
													<option 
													<?php
													if ( in_array( $value->slug, $wb_wcpi_default_product_tags ) ) { //phpcs:ignore
														echo 'selected="selected"';
													};
													?>
													value="<?php echo esc_attr( $value->slug ); ?>"><?php echo esc_html( $value->name ); ?></option>
													<?php
												}
											} else {
												?>
												<option value=""></option>
											<?php } ?>
									</select>
									<span class="ui small error text"><?php esc_html_e( 'You Can Assign Default Product Tag from here', 'wcpi' ); ?></span>
								</td>
							</tr>
						</tbody>
					</table>
				</div><!-- End Product Settings -->
			</div>

			<div class="ui raised segments hidden">
				<div id='import-settings' class="title wcpi-border-bottom active bg-grey"><!-- Start Product Settings -->
					<h3 class="ui block header">
						<?php esc_html_e( 'Import Settings', 'wcpi' ); ?>
					</h3>
				</div>
				<div class="content">	
					<!-- <div class='title'>Choose filters which you want to apply</div> -->
					<table class="ui padded table">
						<tbody>
							<tr>							
								<td class="three wide ">
									<label for="wb_wcpi_filter_product_status"><?php esc_html_e( 'Product Status', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<select class="ui search dropdown" name="wb_wcpi_filter_product_status" id="wb_wcpi_filter_product_status">
										<option value="any" <?php echo selected( $wb_wcpi_filter_product_status, 'any' ); ?> ><?php esc_html_e( 'Any', 'wcpi' ); ?></option>
										<option value="publish" <?php echo selected( $wb_wcpi_filter_product_status, 'publish' ); ?> ><?php esc_html_e( 'Publish', 'wcpi' ); ?></option>
										<option value="pending" <?php echo selected( $wb_wcpi_filter_product_status, 'pending' ); ?>><?php esc_html_e( 'Pending', 'wcpi' ); ?></option>
										<option value="private" <?php echo selected( $wb_wcpi_filter_product_status, 'private' ); ?>><?php esc_html_e( 'Private', 'wcpi' ); ?></option>
										<option value="draft" <?php echo selected( $wb_wcpi_filter_product_status, 'draft' ); ?>><?php esc_html_e( 'Draft', 'wcpi' ); ?></option>
									</select>
									<span class="ui small error text"><?php esc_html_e( 'You Can Set Default Product Status from here', 'wcpi' ); ?></span>
								</td>
							</tr>
							<tr>							
								<td class="three wide ">
									<label for="wb_wcpi_filter_product_type"><?php esc_html_e( 'Product Type', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<select class="ui search dropdown" name="wb_wcpi_filter_product_type" id="wb_wcpi_filter_product_type">
										<option value="any" <?php echo selected( $wb_wcpi_filter_product_type, 'publish' ); ?> ><?php esc_html_e( 'Any', 'wcpi' ); ?></option>
										<option value="simple" <?php echo selected( $wb_wcpi_filter_product_type, 'simple' ); ?> ><?php esc_html_e( 'Simple', 'wcpi' ); ?></option>
										<option value="variable" <?php echo selected( $wb_wcpi_filter_product_type, 'variable' ); ?>><?php esc_html_e( 'Variable', 'wcpi' ); ?></option>
										<option value="grouped" <?php echo selected( $wb_wcpi_filter_product_type, 'grouped' ); ?>><?php esc_html_e( 'Grouped', 'wcpi' ); ?></option>
										<option value="external" <?php echo selected( $wb_wcpi_filter_product_type, 'external' ); ?>><?php esc_html_e( 'External', 'wcpi' ); ?></option>
									</select>
								</td>
							</tr>

							<tr valign="top">
								<td class="three wide titledesc">
									<label for="wb_wcpi_result_per_request"><?php esc_html_e( 'Products Per Request', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<input name="wb_wcpi_result_per_request" id="wb_wcpi_result_per_request" type="text" style="" value="<?php echo esc_attr( $wb_wcpi_result_per_request ); ?>" class="" name="wb_wcpi_result_per_request" placeholder="">
								</td>
							</tr>
							<tr valign="top">
								<td class="three wide titledesc">
									<label for="wb_wcpi_product_filter_cat_id"><?php esc_html_e( 'Products Category', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<input name="wb_wcpi_product_filter_cat_id" id="wb_wcpi_product_filter_cat_id" type="text" style="" value="<?php echo esc_attr( $wb_wcpi_product_filter_cat_id ); ?>" class="" name="wb_wcpi_product_filter_cat_id" placeholder="">
									<span class="ui small error text"><?php esc_html_e( 'Limit results to only these categories. Only accepts Catgeory ID. If you have multiple category ID, then separate them by Comma.', 'wcpi' ); ?> </span>
								</td>
							</tr>
							<tr valign="top">
								<td class="three wide titledesc">
									<label for="wb_wcpi_product_filter_tag_id"><?php esc_html_e( 'Products Tags', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<input name="wb_wcpi_product_filter_tag_id" id="wb_wcpi_product_filter_tag_id" type="text" style="" value="<?php echo esc_attr( $wb_wcpi_product_filter_tag_id ); ?>" class="" name="wb_wcpi_product_filter_tag_id" placeholder="">
									<span class="ui small error text"><?php esc_html_e( 'Limit results to only these tags. Only accepts Tag ID. If you have multiple tag ID, then separate them by Comma.', 'wcpi' ); ?> </span>
								</td>
							</tr>
							<tr valign="top">
								<td class="three wide titledesc">
									<label for="wb_wcpi_filter_sku"><?php esc_html_e( 'Products SKU', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<input name="wb_wcpi_filter_sku" id="wb_wcpi_filter_sku" type="text" style="" value="<?php echo esc_attr( $wb_wcpi_filter_sku ); ?>" class="" name="wb_wcpi_filter_sku" placeholder="">
									<span class="ui small error text"><?php esc_html_e( 'Limit results to only these SKU\'s. If you have multiple SKU\'s, then separate them by Comma.', 'wcpi' ); ?> </span>
								</td>
							</tr>
							<tr valign="top">
								<td class="three wide titledesc">
									<label for="wb_wcpi_filter_include_id"><?php esc_html_e( 'Include Products', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<input name="wb_wcpi_filter_include_id" id="wb_wcpi_filter_include_id" type="text" style="" value="<?php echo esc_attr( $wb_wcpi_filter_include_id ); ?>" class="" name="wb_wcpi_filter_include_id" placeholder="">
									<span class="ui small error text"><?php esc_html_e( 'Limit results to only these products. Only accepts Product ID. If you have multiple Product ID, then separate them by Comma.', 'wcpi' ); ?> </span>
								</td>
							</tr>
							<tr valign="top">
								<td class="three wide titledesc">
									<label for="wb_wcpi_filter_exclude_id"><?php esc_html_e( 'Exclude Products', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<input name="wb_wcpi_filter_exclude_id" id="wb_wcpi_filter_exclude_id" type="text" style="" value="<?php echo esc_attr( $wb_wcpi_filter_exclude_id ); ?>" class="" name="wb_wcpi_filter_exclude_id" placeholder="">
									<span class="ui small error text"><?php esc_html_e( 'Exclude these products from the result. Only accepts Product ID. If you have multiple Product ID, then separate them by Comma.', 'wcpi' ); ?> </span>
								</td>
							</tr>
							<tr valign="top">
								<td class="three wide titledesc">
									<label for="wb_wcpi_filter_min_price"><?php esc_html_e( 'Min Price', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<input name="wb_wcpi_filter_min_price" id="wb_wcpi_filter_min_price" type="text" style="" value="<?php echo esc_attr( $wb_wcpi_filter_min_price ); ?>" class="" name="wb_wcpi_filter_min_price" placeholder="">
									<span class="ui small error text"><?php esc_html_e( 'Filter Product By Minimum Price', 'wcpi' ); ?></span>
								</td>
							</tr>
							<tr valign="top">
								<td class="three wide titledesc">
									<label for="wb_wcpi_filter_max_price"><?php esc_html_e( 'Max Price', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<input name="wb_wcpi_filter_max_price" id="wb_wcpi_filter_max_price" type="text" style="" value="<?php echo esc_attr( $wb_wcpi_filter_max_price ); ?>" class="" name="wb_wcpi_filter_max_price" placeholder="">
									<span class="ui small error text"><?php esc_html_e( 'Filter Product By Minimum Price', 'wcpi' ); ?></span>
								</td>
							</tr>
							<tr>							
								<td class="three wide ">
									<label for="wb_wcpi_filter_product_stock_status"><?php esc_html_e( 'Stock Status', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<select class="ui search dropdown" name="wb_wcpi_filter_product_stock_status" id="wb_wcpi_filter_product_stock_status">
										<option value="any" <?php echo selected( $wb_wcpi_filter_product_stock_status, 'any' ); ?> ><?php esc_html_e( 'Any', 'wcpi' ); ?></option>
										<option value="instock" <?php echo selected( $wb_wcpi_filter_product_stock_status, 'instock' ); ?> ><?php esc_html_e( 'In Stock', 'wcpi' ); ?></option>
										<option value="outofstock" <?php echo selected( $wb_wcpi_filter_product_stock_status, 'outofstock' ); ?>><?php esc_html_e( 'Out of Stock ', 'wcpi' ); ?></option>
										<option value="onbackorder" <?php echo selected( $wb_wcpi_filter_product_stock_status, 'onbackorder' ); ?>><?php esc_html_e( 'On Back Order', 'wcpi' ); ?></option>
									</select>
									<span class="ui small error text"><?php esc_html_e( 'Filter Product By Stock Status', 'wcpi' ); ?></span>
								</td>
							</tr>
							<tr>							
								<td class="three wide ">
									<label for="wb_wcpi_filter_product_order"><?php esc_html_e( 'Order', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<select class="ui search dropdown" name="wb_wcpi_filter_product_order" id="wb_wcpi_filter_product_order">
										<option value="asc" <?php echo selected( $wb_wcpi_filter_product_order, 'asc' ); ?> ><?php esc_html_e( 'Ascending', 'wcpi' ); ?></option>
										<option value="desc" <?php echo selected( $wb_wcpi_filter_product_order, 'desc' ); ?>><?php esc_html_e( 'Descending', 'wcpi' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>							
								<td class="three wide ">
									<label for="wb_wcpi_filter_product_orderby"><?php esc_html_e( 'Order By', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<select class="ui search dropdown" name="wb_wcpi_filter_product_orderby" id="wb_wcpi_filter_product_orderby">
										<!-- <option value="date" <?php echo selected( $wb_wcpi_filter_product_orderby, 'date' ); ?> ><?php esc_html_e( 'Date', 'wcpi' ); ?></option> -->
										<option value="id" <?php echo selected( $wb_wcpi_filter_product_orderby, 'id' ); ?>><?php esc_html_e( 'ID', 'wcpi' ); ?></option>
										<option value="title" <?php echo selected( $wb_wcpi_filter_product_orderby, 'title' ); ?>><?php esc_html_e( 'Title', 'wcpi' ); ?></option>
										<option value="slug" <?php echo selected( $wb_wcpi_filter_product_orderby, 'slug' ); ?>><?php esc_html_e( 'Slug', 'wcpi' ); ?></option>
										<option value="price" <?php echo selected( $wb_wcpi_filter_product_orderby, 'price' ); ?>><?php esc_html_e( 'Price', 'wcpi' ); ?></option>
										<option value="popularity" <?php echo selected( $wb_wcpi_filter_product_orderby, 'popularity' ); ?>><?php esc_html_e( 'Popularity', 'wcpi' ); ?></option>
										<option value="rating" <?php echo selected( $wb_wcpi_filter_product_orderby, 'rating' ); ?>><?php esc_html_e( 'Rating', 'wcpi' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<td class="three wide titledesc">
									<label for="wb_wcpi_filter_product_date_before"><?php esc_html_e( 'Date Before', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<div class="ui calendar">
										<div class="ui input left icon">
										<i class="calendar icon"></i>
										<input name="wb_wcpi_filter_product_date_before" id="wb_wcpi_filter_product_date_before" class="wb_wcpi_calendar" type="text" style="" value="<?php echo esc_attr( $wb_wcpi_filter_product_date_before ); ?>" class="" name="wb_wcpi_filter_product_date_before" placeholder="" autocomplete="off">
										</div>
									</div>
									<span class="ui small error text"><?php esc_html_e( 'Filter Products which are created before the given date', 'wcpi' ); ?></span>
								</td>
							</tr>
							<tr valign="top">
								<td class="three wide titledesc">
									<label for="wb_wcpi_filter_product_date_after"><?php esc_html_e( 'Date After', 'wcpi' ); ?> </label>
								</td>
								<td class="forminp forminp-text field">
									<div class="ui calendar">
										<div class="ui input left icon">
										<i class="calendar icon"></i>
										<input name="wb_wcpi_filter_product_date_after" id="wb_wcpi_filter_product_date_after" class="wb_wcpi_calendar" type="text" style="" value="<?php echo esc_attr( $wb_wcpi_filter_product_date_after ); ?>" class="" name="wb_wcpi_filter_product_date_after" placeholder="" autocomplete="off">
										</div>
									</div>
									<span class="ui small error text"><?php esc_html_e( 'Filter Products which are created after the given date', 'wcpi' ); ?></span>
								</td>
							</tr>
						</tbody>
					</table>
				</div><!-- End Product Settings -->
			</div>

			<!-- </div>  -->
			<div class="ui raised segments 
			<?php
			if ( $settings->maybe_import_run() ) {
				echo 'hidden'; }
			?>
			">
				<div class="ui segment 
				<?php
				if ( $settings->maybe_import_run() ) {
					echo 'disabled'; }
				?>
				">
					<p>
						<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'wcpi' ); ?>">
					</p>
				</div>
			</div>
		<!-- </div> -->

		<!-- <div id="wcpi-resourse-selection" class="ui fluid container segment"> -->
		<div id="wcpi-resourse-selection" class="ui fluid segment 
		<?php
		if ( $settings->maybe_import_run() ) {
			echo 'active';
		} else {
			echo 'hidden'; }
		?>
		">
		<?php if ( ! $wb_wcpi_store_url || ! $wb_wcpi_consumer_key || ! $wb_wcpi_consumer_secret ) { ?>
			<div class="ui red message">
				<div class="header">
					<?php esc_html_e( 'You need to fill up the following fields before starting The Importing Process. Please fill up the following Informations.', 'wcpi' ); ?>
				</div>
				<ul class="list">
					<li><?php esc_html_e( 'You must include both a upper and lower case letters in your password.', 'wcpi' ); ?></li>
				</ul>
			</div>
		<?php } ?>

		<?php if ( $wb_wcpi_store_url && $wb_wcpi_consumer_key && $wb_wcpi_consumer_secret ) { ?>
			<table class="ui padded table celled">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Resource', 'wcpi' ); ?></th>
						<th><?php esc_html_e( 'Status', 'wcpi' ); ?></th>
						<th><?php esc_html_e( 'Result', 'wcpi' ); ?></th>
					</tr>
				</thead>

				<tbody>
					<tr valign="top">
						<td scope="row" class="three wide titledesc">
							<label for="wb-wcpi-enable-product"><?php esc_html_e( 'Product ', 'wcpi' ); ?></label>
						</td>
						<td class="forminp forminp-text three wide">
							<div class="wcpi-container" data-resource-type='product'>
								<div class="ui toggle read-only checkbox">
									<input type="checkbox" class="wcpi-switch-input" name="wb-wcpi-import-product" value="enable" id="wb-wcpi-enable-images" checked>
								</div>
							</div>
						</td>
						<td id="wb-wcpi-product-download-status">
							<div class="ui indicating progress wcpi-hidden ">
								<div class="bar"></div>
								<div class="label"><?php esc_html_e( 'Waiting', 'wb-wcpi' ); ?></div>							 
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		<?php } ?>

			<?php if ( $wb_wcpi_store_url && $wb_wcpi_consumer_key && $wb_wcpi_consumer_secret && wcpi_check_api_key() ) { ?>
					<a class="wcpi-start-importer-btn button button-primary 
					<?php
					if ( $settings->maybe_import_run() ) {
						echo 'wcpi-import-running disabled'; }
					?>
					" href="admin.php?page=wcpi-product-import&action=start-import"><?php esc_html_e( 'Start Import', 'wcpi' ); ?></a>

				<a class="wcpi-delete-history-btn negative ui button" href="admin.php?page=wcpi-product-import&action=delete-history"><?php esc_html_e( 'Delete Previous Import History', 'wcpi' ); ?></a>

			<?php } else { ?>
				<div class="ui error message" style="display: block;" >
					<p><?php esc_html_e( 'You need to enter Valid Domain, API key and API secret to start Migrate from another store', 'wcpi' ); ?></p>
				</div>
			<?php } ?>
			<div class="wcpi-running-import-loader ui inline loader 
			<?php
			if ( $settings->maybe_import_run() ) {
				echo 'active';
			} else {
				echo 'hidden'; }
			?>
			"></div>
			<h3 class="ui header"><?php esc_html_e( 'Import Logs', 'wcpi' ); ?></h3>
			<div class="ui secondary raised segment">
				<div class="ajax_loaded_content">
					<div class="wcpi-import-product-logs"></div>
					<div class="wcpi-import-cats-logs"></div>
					<div class="wcpi-import-customer-logs"></div>
					<div class="wcpi-import-order-logs"></div>
				</div>
			</div>
		</div>
	</form>
	<?php if ( 'on' === $wb_wcpi_background ) { ?>
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="wcpi-bg-import-form wcpi-hidden">
			<input type="hidden" name="action" value="wcpi_bg_import" />
			<input type="checkbox" name="selected-resource[]" class="wcpi-hidden" value="product" />
			<input type="submit" class="button button-primary wcpi-start-background-process" value="<?php esc_html_e( 'Start Background Import', 'wcpi' ); ?>" />
		</form>
	<?php } ?>
</div>

<div class="ui mini modal wcpi-import-complete">
	<div class="scrolling content">
		<p><?php esc_html_e( 'Import Completed Successfully', 'wcpi' ); ?></p>
	</div>
</div>
