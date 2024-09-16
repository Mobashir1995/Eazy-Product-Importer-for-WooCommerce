<?php
/**
 * Database Class
 *
 * @package PluginDevs
 * @since   0.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Create DataBase Operation with this Class
 */
class WCPI_DB {
	/**
	 * The single instance of the class.
	 *
	 * @var WCPI_Create_Product
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
	 * @return WCPI_DB - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 *
	 * Create Essential Tables for the plugin on plugin installation
	 *
	 * @return void
	 */
	public function create_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE WCPI_LINKED_PRODUCT_DB_TABLE (
            id          INT(9)                  NOT NULL AUTO_INCREMENT,
            product_id  INT(9)                  NOT NULL,
            parent_id   INT(9)                  NOT NULL,
            relation    VARCHAR(16) DEFAULT ''  NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Set Upsel/Crossel and Linked Products from here
	 *
	 * @param mixed  $products_ids Product ID.
	 * @param mixed  $parent_id Parent Product ID.
	 * @param string $type Link Type.
	 *
	 * @return void
	 */
	public function set_linked_products( $products_ids, $parent_id, $type = '' ) {
		global $wpdb;
	}
}
