<?php
/**
 * Product Import Class
 *
 * @package PluginDevs
 * @since   0.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class for start Product Import
 */
class WCPI_Import {
	/**
	 * The Background Process for all products
	 *
	 * @var WCPI_Background_Product_Import
	 */
	protected $process;

	/**
	 * The Included Product Import Background Process
	 *
	 * @var WCPI_Background_Included_Product_Imports
	 */
	protected $included_products;

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
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), -1 );
		add_action( 'admin_init', array( $this, 'start_import' ), -1 );
	}

	/**
	 * Start the Import Process
	 *
	 * @return void
	 */
	public function start_import() {
		$settings = wcpi_settings();
		if (
			is_admin() &&
			isset( $_GET['page'] ) && 'wcpi-product-import' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) &&
			1 === $settings->maybe_import_run()
		) {
			// $this->import_products();
		}
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
		require_once WCPI_PATH . 'inc/class-wcpi-background-product-import.php';
		require_once WCPI_PATH . 'inc/class-wcpi-background-included-product-import.php';
		$this->process           = new WCPI_Background_Product_Import();
		$this->included_products = new WCPI_Background_Included_Product_Import();
	}

	/**
	 * Make the Product Import Process object available
	 *
	 * @return object WCPI_Background_Product_Import
	 */
	public function get_product_import_process() {
		return $this->process;
	}

	/**
	 * Make the Inlucded Product Import Process object available
	 *
	 * @return object WCPI_Background_Included_Product_Import
	 */
	public function get_included_product_import_process() {
		return $this->included_products;
	}

	/**
	 * Push Products into the Background Import Process
	 *
	 * @return void
	 */
	public function import_products() {
		global $WCPI;
		$settings               = wcpi_settings();
		$current_importing_page = $settings->get_current_importing_page();
		$product_lists          = $WCPI->Client->list_product(
			array(
				'page' => $current_importing_page,
			)
		);
		$logger                 = wc_get_logger();
		$context                = array( 'source' => 'wcpi-api-products' );
		$logger->debug( wc_print_r( 'importing on ' . $current_importing_page, true ), $context );
		if ( is_array( $product_lists ) && ! empty( $product_lists ) ) {
			foreach ( $product_lists as $product ) {
				$logger->debug(
					wc_print_r( $product, true ),
					$context
				);
				$this->process->push_to_queue( $product );
			}
			$this->process->save()->dispatch();
		}
	}

	/**
	 * Push Single Products into the Background Import Process
	 *
	 * @param object $product Single Product.
	 *
	 * @return void
	 */
	public function create_single_product( $product ) {
		global $WCPI;
		$settings = wcpi_settings();

		$this->process->push_to_queue( $product );

		$this->process->save()->dispatch();

	}

	/**
	 * Push Products into the Background Import Process for additional products
	 *
	 * @return void
	 */
	public function import_included_products() {
		global $WCPI;
		$settings            = wcpi_settings();
		$additional_products = $settings->get_additional_products_to_import();

		if ( is_array( $additional_products ) && ! empty( $additional_products ) ) {
			$this->included_products->push_to_queue( $additional_products );
			$this->included_products->save()->dispatch();
		}
	}
}
