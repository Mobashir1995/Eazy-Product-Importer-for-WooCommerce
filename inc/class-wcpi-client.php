<?php
/**
 * REST API Client Class
 *
 * @package PluginDevs
 * @since   0.9.0
 */

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Client;

/**
 * Setup REST Api Client Class
 */
class WCPI_Client {
	/**
	 * Client instance.
	 *
	 * @var WCPI_Client
	 */
	public $Client = null;

	/**
	 * The single instance of the class.
	 *
	 * @var WCPI_Import
	 * @since 2.1
	 */
	protected static $instance = null;

	/**
	 * Main Instance.
	 *
	 * Ensures only one instance of the plugin is loaded or can be loaded.
	 *
	 * @since 2.1
	 * @static
	 * @return WCPI_Import - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor Method
	 *
	 * @return void
	 */
	public function __construct() {
		$this->Client = $this->set_Client();
	}

	/**
	 * Setup Client
	 *
	 * @return \Automattic\WooCommerce\Client
	 */
	public function set_Client() {
		$settings    = wcpi_settings();
		$woocommerce = new Client(
			$settings->get_imported_store_url(),
			$settings->get_consumer_key(),
			$settings->get_consumer_secret(),
			array(
				'version'    => $settings->get_api_version(),
				'timeout'    => $settings->get_request_timeout(),
				'verify_ssl' => false,
			)
		);

		return $woocommerce;
	}

	/**
	 * List Products from API
	 *
	 * @param array $params Arguments for REST API.
	 * @return mixed
	 */
	public function list_product(
		$params = array(
			'page' => 1,
		)
	) {
		$settings    = wcpi_settings();
		$defaults    = array(
			'page'     => 1,
			'per_page' => $settings->get_results_per_page(),
			'order'    => $settings->get_product_order(),
			'orderby'  => $settings->get_product_orderby(),
			'status'   => $settings->get_import_product_status(),
		);
		$filter_type = $settings->get_filter_product_type();
		if ( ! empty( $filter_type ) && 'any' !== $filter_type ) {
			$defaults['type'] = $filter_type;
		}
		$cats = $settings->get_filter_product_cat_id();
		if ( ! empty( $cats ) ) {
			$defaults['category'] = $cats;
		}
		$tags = $settings->get_filter_product_tag_id();
		if ( ! empty( $tags ) ) {
			$defaults['tag'] = $tags;
		}
		$sku = $settings->get_filter_sku();
		if ( ! empty( $sku ) ) {
			$defaults['sku'] = $sku;
		}
		$include = $settings->get_filter_include_id();
		if ( ! empty( $include ) ) {
			$defaults['include'] = $include;
		}
		$exclude = $settings->get_filter_exclude_id();
		if ( ! empty( $exclude ) ) {
			$defaults['exclude'] = $exclude;
		}
		$min_price = $settings->get_filter_min_price();
		if ( '' !== $min_price ) {
			$defaults['min_price'] = $min_price;
		}
		$max_price = $settings->get_filter_max_price();
		if ( '' !== $max_price ) {
			$defaults['max_price'] = $max_price;
		}
		$stock_status = $settings->get_filter_product_stock_status();
		if ( ! empty( $stock_status ) && 'any' !== $stock_status ) {
			$defaults['stock_status'] = $stock_status;
		}
		$before_date = wcpi_convert_ISO_date( $settings->get_filter_product_date_before() );
		if ( $before_date ) {
			$defaults['before'] = $before_date;
		}
		$after_date = wcpi_convert_ISO_date( $settings->get_filter_product_date_after() );
		if ( $after_date ) {
			$defaults['after'] = $after_date;
		}
		$params   = wp_parse_args( $params, $defaults );
		$endpoint = 'products';
		$response = $this->Client->get( $endpoint, $params );
		//phpcs:disable
		// print_r($response);die();
		// Set total resulted products and pages from API
		//phpcs:enable
		$headers        = $this->Client->http->getResponse()->getHeaders();
		$total_products = $headers['x-wp-total'];
		$total_pages    = $headers['x-wp-totalpages'];
		$logger         = wc_get_logger();
		$context        = array( 'source' => 'wcpi-api-products' );
		//phpcs:disable
		// $logger->debug( wc_print_r($headers, true), $context );
		// $logger->debug( wc_print_r($response, true), $context );
		//phpcs:enable
		$settings->set_total_products_from_api( $total_products );
		$settings->set_total_product_page_from_api( $total_pages );
		return $response;
	}

	/**
	 * Get Included Products from API
	 *
	 * @param array $params Arguments for REST API.
	 * @return mixed
	 */
	public function list_included_products( $params = array(
		'page'    => 1,
		'include' => array(),
	) ) {

		if ( ! is_array( $params['include'] ) || empty( $params['include'] ) ) {
			return false;
		}
		$settings = wcpi_settings();
		$defaults = array(
			'page'     => 1,
			'per_page' => $settings->get_results_per_page(),
		);
		$params   = wp_parse_args( $params, $defaults );
		$settings = wcpi_settings();
		$endpoint = 'products';
		$response = $this->Client->get( $endpoint, $params );

		$headers        = $this->Client->http->getResponse()->getHeaders();
		$total_products = $headers['x-wp-total'];
		$total_pages    = $headers['x-wp-totalpages'];
		$logger         = wc_get_logger();
		$context        = array( 'source' => 'wcpi-additional-products' );
		$logger->debug( wc_print_r( $response, true ), $context );
		$logger->debug( wc_print_r( $headers, true ), $context );
		return $response;
	}

	/**
	 * Get all Product Variants
	 *
	 * @param integer $product_id Product ID.
	 * @param array   $params     Query Parameters.
	 *
	 * @return mixed
	 */
	public function get_product_variants(
		$product_id,
		$params = array(
			'per_page' => 100,
		)
	) {

		$params = wp_parse_args( $params );

		$endpoint = 'products/' . $product_id . '/variations';
		$response = $this->Client->get( $endpoint, $params );

		return $response;
	}

	/**
	 *
	 * Get Category by ID
	 *
	 * @param  integer $ID Category ID.
	 *
	 * @return integer Category ID.
	 */
	public function get_category( int $ID ) {
		$endpoint = 'products/categories/' . $ID;
		$response = $this->Client->get( $endpoint );
		$headers  = $this->Client->http->getResponse()->getHeaders();

		return $response;
	}

	/**
	 * Setup Get Request
	 *
	 * @param mixed $endpoint REST API Endpoint.
	 * @param mixed $params Parameters.
	 *
	 * @return mixed
	 */
	public function get( $endpoint, $params ) {
		$response = $this->Client->get( $endpoint, $params );
		return $response;
	}
}
