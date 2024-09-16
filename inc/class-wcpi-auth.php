<?php
/**
 * Authentication Class
 *
 * @package PluginDevs
 * @since   0.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Authenticate REST API
 */
class WCPI_AUTH {

	/**
	 * The single instance of the class.
	 *
	 * @var WCPI_AUTH
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
	 * @return WCPI_AUTH - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'woocommerce_api_wcpi_auth_return_url', array( $this, 'wcpi_auth_return_url' ) );
		add_action( 'woocommerce_api_wcpi_auth_callback_url', array( $this, 'wcpi_auth_callback_url' ) );
	}

	/**
	 * Return URL after Authentication
	 */
	public function wcpi_auth_return_url() {
		if ( isset( $_GET['success'] ) && 1 === intval( $_GET['success'] ) &&
			isset( $_GET['user_id'] ) && intval( $_GET['user_id'] ) === get_current_user_id()
		) {
			status_header( 200 );
			wp_safe_redirect( admin_url( 'admin.php?page=wcpi-product-import' ) );
		}
	}

	/**
	 * Return URL after Authentication Callback
	 */
	public function wcpi_auth_callback_url() {
		update_option( 'wcpi_auth_callback_url', 'yes' );
		status_header( 200 );
	}


}


new WCPI_AUTH();
