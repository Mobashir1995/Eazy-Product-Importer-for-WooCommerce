<?php
/**
 * WCPI Settings
 *
 * @package PluginDevs
 * @since   0.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * WCPI_Settings Class to store all settings.
 *
 * @class WCPI_Settings
 */
class WCPI_Settings {

	public $store_url       = 'wb_wcpi_store_url';
	public $consumer_key    = 'wb_wcpi_consumer_key';
	public $consumer_secret = 'wb_wcpi_consumer_secret';
	public $product_status  = 'wb_wcpi_product_status';
	public $download_images = 'wb_wcpi_download_images';
	public $import_start    = 'wb_wcpi_import_started_time';

	public $default_attached_categories = 'wb_wcpi_default_product_categories';
	public $default_attached_tags       = 'wb_wcpi_default_product_tags';

	public $request_timeout       = 'wb_wcpi_request_timeout';
	public $per_page              = 'wb_wcpi_result_per_request';
	public $import_product_status = 'wb_wcpi_filter_product_status';
	public $additional_products   = 'wb_wcpi_additional_products';

	public $order   = 'wb_wcpi_filter_product_order';
	public $orderby = 'wb_wcpi_filter_product_orderby';

	public $importing_page                       = 'wb_wcpi_currently_importing_page';
	public $total_imported_products              = 'wb_wcpi_total_imported_products';
	public $total_products_from_api              = 'wb_wcpi_total_products_from_api';
	public $total_product_page_from_api          = 'wb_wcpi_total_product_page_from_api';
	public $maybe_import_run                     = 'wb_wcpi_maybe_import_run';
	public $maybe_additional_products_import_run = 'wb_wcpi_maybe_additional_products_import_run ';

	public $filter_product_type         = 'wb_wcpi_filter_product_type';
	public $filter_min_price            = 'wb_wcpi_filter_min_price';
	public $filter_max_price            = 'wb_wcpi_filter_max_price';
	public $filter_product_stock_status = 'wb_wcpi_filter_product_stock_status';
	public $filter_product_date_before  = 'wb_wcpi_filter_product_date_before';
	public $filter_product_date_after   = 'wb_wcpi_filter_product_date_after';
	public $filter_product_cat_id       = 'wb_wcpi_product_filter_cat_id';
	public $filter_product_tag_id       = 'wb_wcpi_product_filter_tag_id';
	public $filter_sku                  = 'wb_wcpi_filter_sku';
	public $filter_include_id           = 'wb_wcpi_filter_include_id';
	public $filter_exclude_id           = 'wb_wcpi_filter_exclude_id';

	/**
	 * Register Settings
	 */
	public function register_setting() {
		register_setting( 'wcpi_settings_options', $this->store_url );
		register_setting( 'wcpi_settings_options', $this->consumer_key );
		register_setting( 'wcpi_settings_options', $this->consumer_secret );
		register_setting( 'wcpi_settings_options', $this->product_status );
		register_setting( 'wcpi_settings_options', $this->download_images );
		register_setting( 'wcpi_settings_options', 'wb_wcpi_background' );
		register_setting( 'wcpi_settings_options', $this->default_attached_categories );
		register_setting( 'wcpi_settings_options', $this->default_attached_tags );
		register_setting( 'wcpi_settings_options', $this->request_timeout );
		register_setting( 'wcpi_settings_options', $this->per_page );
		register_setting( 'wcpi_settings_options', $this->import_product_status );
		register_setting( 'wcpi_settings_options', $this->filter_product_type );
		register_setting( 'wcpi_settings_options', $this->filter_product_cat_id );
		register_setting( 'wcpi_settings_options', $this->filter_product_tag_id );
		register_setting( 'wcpi_settings_options', $this->filter_sku );
		register_setting( 'wcpi_settings_options', $this->filter_include_id );
		register_setting( 'wcpi_settings_options', $this->filter_exclude_id );
		register_setting( 'wcpi_settings_options', $this->filter_min_price );
		register_setting( 'wcpi_settings_options', $this->filter_max_price );
		register_setting( 'wcpi_settings_options', $this->filter_product_stock_status );
		register_setting( 'wcpi_settings_options', $this->order );
		register_setting( 'wcpi_settings_options', $this->orderby );
		register_setting( 'wcpi_settings_options', $this->filter_product_date_before );
		register_setting( 'wcpi_settings_options', $this->filter_product_date_after );
		register_setting( 'wcpi_settings_options', $this->maybe_import_run );
		register_setting( 'wcpi_settings_options', $this->maybe_additional_products_import_run );
	}

	/**
	 * Get Consumer key
	 *
	 * @return string
	 */
	public function get_consumer_key() {
		$consumer_key = get_option( $this->consumer_key );
		return $consumer_key;
	}

	/**
	 * Get Consumer Secret
	 *
	 * @return string
	 */
	public function get_consumer_secret() {
		$consumer_secret = get_option( $this->consumer_secret );
		return $consumer_secret;
	}

	/**
	 * Get Domain
	 *
	 * @return string
	 */
	public function get_imported_store_url() {
		$store_url = get_option( $this->store_url );
		return $store_url;
	}

	/**
	 * Get Import Started Time
	 */
	public function get_import_started_time() {
		return get_option( $this->import_start );
	}

	/**
	 * Request Timeout
	 *
	 * @return integer
	 */
	public function get_request_timeout() {
		$request_timeout = get_option( $this->request_timeout, WCPI_DEFAULT_REQUEST_TIMEOUT );
		return $request_timeout;
	}

	/**
	 * Default attached Category
	 *
	 * @return integer
	 */
	public function get_default_attached_categories() {
		$categories = get_option( $this->default_attached_categories, WCPI_DEFAULT_PRODUCT_CATEGORIES );
		return $categories;
	}

	/**
	 * Default attached Tags
	 *
	 * @return integer
	 */
	public function get_default_attached_tags() {
		$tags = get_option( $this->default_attached_tags, WCPI_DEFAULT_PRODUCT_TAGS );
		return $tags;
	}


	/**
	 * WC API Version
	 *
	 * @return string
	 */
	public function get_api_version() {
		$api_version = 'wc/v3';
		return $api_version;
	}

	/**
	 * Default Product Status
	 *
	 * @return string
	 */
	public function get_default_product_status() {
		$product_status = get_option( $this->product_status, WCPI_DEFAULT_PRODUCT_STATUS );
		return $product_status;
	}

	/**
	 * Results Per Page
	 *
	 * @return integer
	 */
	public function get_results_per_page() {
		$per_page = get_option( $this->per_page, WCPI_DEFAULT_RESULT_PER_REQUEST );
		return $per_page;
	}

	/**
	 * Imported Product Status
	 *
	 * @return string
	 */
	public function get_import_product_status() {
		$product_status = get_option( $this->import_product_status, WCPI_DEFAULT_FILTER_PRODUCT_STATUS );
		return $product_status;
	}

	/**
	 * Download Image
	 *
	 * @return boolean
	 */
	public function get_download_images_status() {
		$download_image = get_option( $this->download_images, WCPI_DEFAULT_DOWNLOAD_IMAGES );
		if ( 'on' === $download_image ) {
			return true;
		}
		return false;
	}

	/**
	 * Imported Product Order
	 *
	 * @return string
	 */
	public function get_product_order() {
		$order = get_option( $this->order, WCPI_DEFAULT_FILTER_PRODUCT_ORDER );
		return $order;
	}

	/**
	 * Imported Product Order By
	 *
	 * @return string
	 */
	public function get_product_orderby() {
		$orderby = get_option( $this->orderby, WCPI_DEFAULT_FILTER_PRODUCT_ORDERBY );
		return $orderby;
	}

	/**
	 * Currently Importing Page
	 *
	 * @return integer
	 */
	public function get_current_importing_page() {
		$page = get_option( $this->importing_page, 1 );
		return $page;
	}

	/**
	 * Total Imported Products
	 *
	 * @return integer
	 */
	public function get_total_imported_products() {
		$number = get_option( $this->total_imported_products, 0 );
		return $number;
	}

	/**
	 * Total Filtered Imported Products from the HTTP API
	 *
	 * @return integer
	 */
	public function get_total_filtered_products_from_api() {
		$number = get_option( $this->total_products_from_api, 0 );
		return $number;
	}

	/**
	 * Total Filtered Imported Product Pages from the HTTP API
	 *
	 * @return integer
	 */
	public function get_total_product_page_from_api() {
		$number = get_option( $this->total_product_page_from_api, 0 );
		return $number;
	}

	/**
	 * Filtered Product Type
	 *
	 * @return string
	 */
	public function get_filter_product_type() {
		$type = get_option( $this->filter_product_type, WCPI_DEFAULT_FILTER_PRODUCT_TYPE );
		return $type;
	}

	/**
	 * Filtered Minimum Price
	 *
	 * @return integer
	 */
	public function get_filter_min_price() {
		$price = get_option( $this->filter_min_price, '' );
		return $price;
	}

	/**
	 * Filtered Maximum Price
	 *
	 * @return integer
	 */
	public function get_filter_max_price() {
		$price = get_option( $this->filter_max_price, '' );
		return $price;
	}

	/**
	 * Filtered Stock Status
	 *
	 * @return string
	 */
	public function get_filter_product_stock_status() {
		$status = get_option( $this->filter_product_stock_status, WCPI_DEFAULT_FILTER_PRODUCT_STOCK_STATUS );
		return $status;
	}

	/**
	 * Filtered Date Before
	 *
	 * @return string
	 */
	public function get_filter_product_date_before() {
		$status = get_option( $this->filter_product_date_before, '' );
		return $status;
	}

	/**
	 * Filtered Date After
	 *
	 * @return string
	 */
	public function get_filter_product_date_after() {
		$status = get_option( $this->filter_product_date_after, '' );
		return $status;
	}

	/**
	 * Filtered Categories ID's
	 *
	 * @return string
	 */
	public function get_filter_product_cat_id() {
		$cats = get_option( $this->filter_product_cat_id, '' );
		return $cats;
	}

	/**
	 * Filtered Tag ID's
	 *
	 * @return string
	 */
	public function get_filter_product_tag_id() {
		$tags = get_option( $this->filter_product_tag_id, '' );
		return $tags;
	}

	/**
	 * Filtered SKU's
	 *
	 * @return string
	 */
	public function get_filter_sku() {
		$sku = get_option( $this->filter_sku, '' );
		return $sku;
	}

	/**
	 * Filtered Included product Id's
	 *
	 * @return string
	 */
	public function get_filter_include_id() {
		$include = get_option( $this->filter_include_id, '' );
		return $include;
	}

	/**
	 * Filtered Excluded product Id's
	 *
	 * @return string
	 */
	public function get_filter_exclude_id() {
		$exclude = get_option( $this->filter_exclude_id, '' );
		return $exclude;
	}

	/**
	 * Get Additional Products Lists which need to be imported after the import is finised.
	 *
	 * @return array $ids IDs of products
	 */
	public function get_additional_products_to_import() {
		$ids = get_option( $this->additional_products, array() );
		return $ids;
	}

	/**
	 * Whether import is running or not
	 *
	 * @return boolean
	 */
	public function maybe_import_run() {
		$return = get_option( $this->maybe_import_run, 0 );
		return $return;
	}

	/**
	 * Whether Additional Product import is running or not
	 *
	 * @return boolean
	 */
	public function maybe_additional_products_import_run() {
		$return = get_option( $this->maybe_additional_products_import_run, 0 );
		return $return;
	}

	/**
	 * Set Store URL
	 *
	 * @param string $url URL of the Store.
	 *
	 * @return void
	 */
	public function set_imported_store_url( $url ) {
		update_option( $this->store_url, $url );
	}

	/**
	 * Set Consumer Key
	 *
	 * @param string $consumer_key Consumer Key.
	 *
	 * @return void
	 */
	public function set_consumer_key( $consumer_key ) {
		update_option( $this->consumer_key, $consumer_key );
	}

	/**
	 * Set Consumer Secret
	 *
	 * @param string $consumer_secret Consumer Secret.
	 *
	 * @return void
	 */
	public function set_consumer_secret( $consumer_secret ) {
		update_option( $this->consumer_secret, $consumer_secret );
	}

	/**
	 * Set Import Run Status
	 *
	 * @return void
	 */
	public function set_maybe_import_run() {
		update_option( $this->maybe_import_run, 1 );
	}

	/**
	 * Set Additional Import Run Status
	 *
	 * @return void
	 */
	public function set_maybe_additional_products_import_run() {
		update_option( $this->maybe_additional_products_import_run, 1 );
	}

	/**
	 * Set Total Imported Page
	 *
	 * @param integer $number Current Imported Page Number.
	 *
	 * @return void
	 */
	public function set_total_imported_page( $number ) {
		update_option( $this->importing_page, $number );
	}

	/**
	 * Set Total Imported Products
	 *
	 * @param integer $number Total Imported Product Number.
	 *
	 * @return void
	 */
	public function set_total_imported_products( $number ) {
		update_option( $this->total_imported_products, $number );
	}

	/**
	 * Set Total Filtered Imported Products from HTTP API
	 *
	 * @param integer $number Total Product Number from Imported Store.
	 *
	 * @return void
	 */
	public function set_total_products_from_api( $number ) {
		update_option( $this->total_products_from_api, $number );
	}

	/**
	 * Set Total Filtered Imported Pages from HTTP API
	 *
	 * @param integer $number Total Imported Page Number.
	 *
	 * @return void
	 */
	public function set_total_product_page_from_api( $number ) {
		update_option( $this->total_product_page_from_api, $number );
	}

	/**
	 * Set Import Started Time
	 */
	public function set_import_started_time() {
		update_option( $this->import_start, current_time( 'timestamp' ) );
	}

	/**
	 * Set Additional Products Lists which need to be imported after the import is finised.
	 *
	 * @param array $ids IDs of products.
	 */
	public function set_additional_products_to_import( $ids ) {
		if ( is_array( $ids ) && ! empty( $ids ) ) {
			update_option( $this->additional_products, $ids );
		}
	}

	/**
	 * Delete Total Imported Page Option
	 *
	 * @return void
	 */
	public function delete_total_imported_page() {
		delete_option( $this->importing_page );
	}

	/**
	 * Delete Total Imported Product Numbers
	 *
	 * @return void
	 */
	public function delete_total_imported_products() {
		delete_option( $this->total_imported_products );
	}

	/**
	 * Delete Total Filtered Imported Product from HTTP API
	 *
	 * @return void
	 */
	public function delete_total_products_from_api() {
		delete_option( $this->total_products_from_api );
	}

	/**
	 * Delete Total Filtered Imported Product Page from HTTP API
	 *
	 * @return void
	 */
	public function delete_total_product_page_from_api() {
		delete_option( $this->total_product_page_from_api );
	}

	/**
	 * Delete Import Running Status
	 *
	 * @return void
	 */
	public function delete_maybe_import_run() {
		delete_option( $this->maybe_import_run );
	}

	/**
	 * Delete Additional Import Running Status
	 *
	 * @return void
	 */
	public function delete_maybe_additional_products_import_run() {
		delete_option( $this->maybe_additional_products_import_run );
	}


	/**
	 * Delete Import Started Time
	 */
	public function delete_import_started_time() {
		delete_option( $this->import_start );
	}

	/**
	 * Delete Additional Products Lists
	 *
	 * @param array $ids IDs of products.
	 */
	public function delete_additional_products_to_import() {
		delete_option( $this->additional_products );
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 2.1
	 */
	public function __clone() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'wcpi' ), '2.1' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.1
	 */
	public function __wakeup() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'wcpi' ), '2.1' );
	}

}
