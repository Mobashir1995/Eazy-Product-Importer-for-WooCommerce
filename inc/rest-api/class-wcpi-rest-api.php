<?php
/**
 * Class for accessing REST API
 *
 * @package PluginDevs
 * @since   0.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Rest API Class
 */
class WCPI_REST_API {


	/**
	 * API instance.
	 *
	 * @var WCPI_Client
	 */
	public $API = null;

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
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_rest_api_get_rest_namespaces', array( $this, 'woo_wcpi_endpoints' ) );
	}

	/**
	 * Register REST API Endpoints
	 *
	 * @param mixed $controllers REST API Controllers.
	 * @return mixed
	 */
	public function woo_wcpi_endpoints( $controllers ) {
		$controllers['wc/v3']['custom'] = 'WC_REST_Custom_Controller';

		return $controllers;
	}
}
