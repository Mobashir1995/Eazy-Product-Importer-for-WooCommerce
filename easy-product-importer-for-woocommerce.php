<?php
/**
 * Plugin Name: Easy Product Importer for WooCommerce
 * Plugin URI: https://plugin-devs.com/
 * Description: Import WooCommerce Products from one Store to another Store
 * Version: 0.0.3
 * Author: Plugin Devs
 * Author URI: https://plugin-devs.com/
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: wcpi
 * Requires at least: 6.0
 * Requires PHP: 7.0
 *
 * @package PluginDevs
 */

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Client;

if ( ! defined( 'WCPI_FILE' ) ) {
	define( 'WCPI_FILE', __FILE__ );
}

if ( ! defined( 'WCPI_PATH' ) ) {
	define( 'WCPI_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WCPI_BASE' ) ) {
	define( 'WCPI_BASE', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'WCPI_URL' ) ) {
	define( 'WCPI_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Main Plugin Class
 */
final class WCPI {

	/**
	 * WCPI_Import version.
	 *
	 * @var string
	 */
	public $version = '0.0.3';

	/**
	 * The single instance of the class.
	 *
	 * @var WCPI_Import
	 * @since 2.1
	 */
	protected static $instance = null;

	/**
	 * WCPI_Import Plugin.
	 *
	 * @var object
	 */
	public $Plugin = '';

	/**
	 * Client instance.
	 *
	 * @var WCPI_Client
	 */
	public $Client = null;

	/**
	 * Product Importer instance.
	 *
	 * @var WCPI_Import
	 */
	public $Import = null;

	/**
	 * Product Image Importer instance.
	 *
	 * @var WCPI_Image_Import
	 */
	public $Image_Import = null;

	/**
	 * Product instance.
	 *
	 * @var WCPI_Product
	 */
	public $Product = null;

	/**
	 * Import History instance.
	 *
	 * @var WCPI_Import_History
	 */
	public $Import_History = null;

	/**
	 * Database instance.
	 *
	 * @var WCPI_DB
	 */
	public $DB = null;

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

	/**
	 * WCPI Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->check_compatibility();
		if ( class_exists( 'woocommerce' ) ) {
			$this->includes();
			$this->objects();
			$this->init_hooks();
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		/**
		 * Functions.
		 */
		require_once WCPI_PATH . '/vendor/autoload.php';
		require_once WCPI_PATH . 'inc/functions.php';
		require_once WCPI_PATH . 'inc/functions-categories.php';
		require_once WCPI_PATH . 'inc/functions-products.php';
		require_once WCPI_PATH . 'inc/class-wcpi-settings.php';
		require_once WCPI_PATH . 'inc/class-wcpi-client.php';
		require_once WCPI_PATH . 'inc/class-wcpi-product-import.php';
		require_once WCPI_PATH . 'inc/class-wcpi-image-import.php';
		require_once WCPI_PATH . 'inc/class-wcpi-product.php';
		require_once WCPI_PATH . 'inc/class-wcpi-import-history.php';
		require_once WCPI_PATH . 'inc/class-wcpi-ajax.php';
		require_once WCPI_PATH . 'inc/class-wcpi-auth.php';
		require_once WCPI_PATH . 'inc/class-wcpi-db.php';

		if ( is_admin() ) {
			require_once WCPI_PATH . 'inc/admin/class-wcpi-admin-menu-page.php';
		}
	}

	/**
	 * Define WC Constants.
	 */
	private function define_constants() {
		global $wpdb;

		$this->define( 'WCPI_VERSION', $this->version );
		$this->define( 'WCPI_NOTICE_MIN_PHP_VERSION', '7.2' );
		$this->define( 'WCPI_NOTICE_MIN_WP_VERSION', '5.2' );
		$this->define( 'WCPI_NOTICE_MIN_WC_VERSION', '5.0.0' );

		$this->define( 'WCPI_DEFAULT_REQUEST_TIMEOUT', '120' );
		$this->define( 'WCPI_DEFAULT_RESULT_PER_REQUEST', '10' );
		$this->define( 'WCPI_DEFAULT_PRODUCT_STATUS', 'inherit' );
		$this->define( 'WCPI_DEFAULT_DOWNLOAD_IMAGES', 'on' );
		$this->define( 'WCPI_DEFAULT_BACKGROUND_IMPORT', 'on' );
		$this->define( 'WCPI_DEFAULT_PRODUCT_CATEGORIES', array() );
		$this->define( 'WCPI_DEFAULT_PRODUCT_TAGS', array() );
		$this->define( 'WCPI_DEFAULT_FILTER_PRODUCT_STATUS', 'any' );
		$this->define( 'WCPI_DEFAULT_FILTER_PRODUCT_TYPE', 'any' );
		$this->define( 'WCPI_DEFAULT_FILTER_PRODUCT_ORDER', 'desc' );
		$this->define( 'WCPI_DEFAULT_FILTER_PRODUCT_ORDERBY', 'id' );
		$this->define( 'WCPI_DEFAULT_FILTER_PRODUCT_STOCK_STATUS', 'any' );

		// Database Table Names.
		$this->define( 'WCPI_LINKED_PRODUCT_DB_TABLE', $wpdb->prefix . 'wcpi_linked_product_lookup' );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 2.3
	 */
	private function init_hooks() {
		$settings = wcpi_settings();
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), -1 );
		add_action( 'admin_init', array( $settings, 'register_setting' ) );
		register_activation_hook( __FILE__, array( $this->DB, 'create_table' ) );
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
	public function on_plugins_loaded() {
		do_action( 'wcpi_loaded' );
	}

	/**
	 * Check Compatibility
	 */
	public function check_compatibility() {
		require_once WCPI_PATH . 'inc/class-wcpi-check-compat.php';
	}

	/**
	 *
	 * Declare all objects here
	 *
	 * @since 0.9.0
	 */
	public function objects() {
		$this->Client         = WCPI_Client::instance();
		$this->Import         = WCPI_Import::instance();
		$this->Image_Import   = WCPI_Image_Import::instance();
		$this->Product        = WCPI_Product::instance();
		$this->Import_History = WCPI_Import_History::instance();
		$this->DB             = WCPI_DB::instance();
	}
}


// Global for backwards compatibility.
$WCPI = WCPI::instance();
