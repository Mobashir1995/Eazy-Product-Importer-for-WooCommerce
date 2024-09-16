<?php
/**
 * Product Import History class
 *
 * @package PluginDevs
 * @since   0.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class for Product Import History
 */
class WCPI_Import_History {


	/**
	 * The single instance of the class.
	 *
	 * @var WCPI_Import_History
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
	 * @return WCPI_Import_History - Main instance.
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
		add_action( 'admin_init', array( $this, 'delete_import_history' ) );
	}

	/**
	 * Delete Import History
	 *
	 * @return void
	 */
	public function delete_import_history() {
		if (
			is_admin() &&
			isset( $_GET['page'] ) && 'wcpi-product-import' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) &&
			isset( $_GET['action'] ) && 'delete-history' === sanitize_text_field( wp_unslash( $_GET['action'] ) )
		) {
			global $WCPI;
			$WCPI->Import->get_product_import_process()->kill_process();
			$WCPI->Import->get_included_product_import_process()->kill_process();

			$settings = wcpi_settings();
			$settings->delete_total_imported_page();
			$settings->delete_maybe_import_run();
			$settings->delete_total_imported_products();
			$settings->delete_import_started_time();
			$settings->delete_additional_products_to_import();
			$settings->delete_maybe_additional_products_import_run();
			delete_metadata( 'term', '', 'wcpi_term_updated_import_id', '', true );
			delete_option( 'wcpi_parent_child_relations' );

			wp_safe_redirect( admin_url( 'admin.php?page=wcpi-product-import' ) );
			exit();
		}
	}
}
