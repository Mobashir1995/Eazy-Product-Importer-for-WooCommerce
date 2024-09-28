<?php
/**
 * Class for Check Compatibility
 *
 * @package PluginDevs
 * @since   0.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Compatibility Checker Class to check if dependency plugins and server status are met
 */
class WCPI_Check_Compat {

	/**
	 * Constructor Method
	 *
	 * @return void
	 */
	public function __construct() {
		if ( 
			! in_array( 
			  'woocommerce/woocommerce.php', 
			  apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) 
			) 
		  ){
			add_action( 'admin_notices', array( $this, 'admin_notice_missing_wc_plugin' ) );
			return;
		}

		// Check for required PHP version.
		if ( version_compare( PHP_VERSION, WCPI_NOTICE_MIN_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_minimum_php_version' ) );
			return;
		}
	}

	/**
	 * Admin Notice
	 *
	 * Warning when the site doesn't have WooCommerce installed or activated.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_missing_wc_plugin() {

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
			/* translators: 1: Current Plugin name 2: WooCommerce Plugin Name */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'wcpi' ),
			'<strong>' . esc_html__( 'Easy Product Importer for WooCommerce', 'wcpi' ) . '</strong>',
			'<strong>' . esc_html__( 'WooCommerce', 'wcpi' ) . '</strong>'
		);

		echo wp_kses_post( '<div class="notice notice-error"><p>' . $message . '</p></div>' );
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'wcpi' ),
			'<strong>' . esc_html__( 'Easy Product Importer for WooCommerce', 'wcpi' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'wcpi' ) . '</strong>',
			WCPI_NOTICE_MIN_PHP_VERSION
		);

		echo wp_kses_post( '<div class="notice notice-error"><p>' . $message . '</p></div>' );

	}

}

new WCPI_Check_Compat();
