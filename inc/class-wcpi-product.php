<?php
/**
 * Product Class
 *
 * @package PluginDevs
 * @since   0.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Operate with Product
 */
class WCPI_Product {
	/**
	 * The Background Process
	 *
	 * @var WP_Background_Process
	 * @since 0.9.0
	 */
	protected $process;

	/**
	 * The single instance of the class.
	 *
	 * @var WCPI_Create_Product
	 * @since 0.9.0
	 */
	protected static $instance = null;

	/**
	 * Main Instance.
	 *
	 * Ensures only one instance of the plugin is loaded or can be loaded.
	 *
	 * @since 2.1
	 * @static
	 * @return WCPI_Create_Product - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * When WP has loaded all plugins, trigger the `woocommerce_loaded` hook.
	 *
	 * This ensures `woocommerce_loaded` is called only after all other plugins
	 * are loaded, to avoid issues caused by plugin directory naming changing
	 * the load order. See #21524 for details.
	 *
	 * @since 3.6.0
	 */
	public function create_product() {

	}
}
