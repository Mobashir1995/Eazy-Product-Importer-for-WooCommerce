<?php
/**
 * Start the Image Import Background Process
 *
 * @package PluginDevs
 * @since   0.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class for Image Import
 */
class WCPI_Image_Import {
	/**
	 * The Background Process
	 *
	 * @var WCPI_Background_Image_Import
	 * @since 0.9.0
	 */
	protected $process;

	/**
	 * The single instance of the class.
	 *
	 * @var WCPI_Import
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
		require_once WCPI_PATH . 'inc/class-wcpi-background-image-import.php';
		$this->process = new WCPI_Background_Image_Import();
	}

	/**
	 * Push Image to start Background Import
	 *
	 * @param string $image Product Image.
	 * @return void
	 */
	public function import_image( $image = '' ) {
		if ( is_array( $image ) && ! empty( $image ) ) {
			$this->process->push_to_queue( $image );
			$this->process->save()->dispatch();
		}
	}
}
