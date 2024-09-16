<?php
/**
 * WCPI Menu Page
 *
 * @package PluginDevs
 * @since   0.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * WCPI_Menu_Page Class to create admin menu.
 *
 * @class WCPI_Menu_Page
 */
class WCPI_Admin_Menu_Page {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Create Admin Menu Method
	 */
	public function admin_menu() {
		add_menu_page(
			__( 'WooW Product Import', 'textdomain' ),
			__( 'WooW Product Import', 'textdomain' ),
			'manage_options',
			'wcpi-product-import',
			array( $this, 'admin_menu_callback' ),
			'dashicons-image-rotate-left',
			35
		);
	}

	/**
	 * Callback for Admin Menu
	 */
	public function admin_menu_callback() {
		$settings = wcpi_settings();
		$plugin   = get_plugin_data( WCPI_FILE );

		wp_enqueue_style( 'wcpi-product-import-semantic-css', WCPI_URL . 'assets/vendors/semantic/semantic.min.css', array(), WCPI_VERSION );
		wp_enqueue_style( 'wcpi-product-import', WCPI_URL . 'assets/css/style.css', array(), WCPI_VERSION );

		wp_enqueue_script( 'wcpi-product-import-semantic-js', WCPI_URL . 'assets/vendors/semantic/semantic.min.js', array(), WCPI_VERSION, true );
		wp_enqueue_script( 'wcpi-product-import', WCPI_URL . 'assets/js/scripts.js', array(), WCPI_VERSION, true );

		$swm_localized = array(
			'ajax_url'     => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'wcpi_ajax_nonce' ),
			'user_id'      => get_current_user_id(),
			'app_name'     => $plugin['Name'],
			'scope'        => 'read',
			'return_url'   => get_home_url() . '/wc-api/wcpi_auth_return_url/',
			'callback_url' => get_home_url() . '/wc-api/wcpi_auth_callback_url/',
		);
		if ( $settings->maybe_import_run() ) {
			$swm_localized['import_running'] = 1;
		} else {
			$swm_localized['import_running'] = 0;
		}
		wp_localize_script( 'wcpi-product-import', 'wcpi_ajax_object', $swm_localized );

		require_once 'views/main-admin-menu.php';
	}
}

new WCPI_Admin_Menu_Page();
