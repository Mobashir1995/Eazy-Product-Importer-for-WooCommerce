<?php
/**
 * Class for Ajax
 *
 * @package PluginDevs
 * @since   0.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ajax Class
 */
class WCPI_AJAX {

	/**
	 * Constructor Method
	 */
	public function __construct() {
		add_action( 'wp_ajax_wcpi_import_product', array( $this, 'wcpi_import_product' ) );
		add_action( 'wp_ajax_wcpi_get_import_status', array( $this, 'wcpi_get_import_status' ) );
		add_action( 'wp_ajax_wcpi_reset_import_status', array( $this, 'reset_import_status' ) );
		add_action( 'wp_ajax_wcpi_set_imported_store_url', array( $this, 'set_imported_store_url' ) );
	}

	/**
	 * Start the import process with Ajax
	 *
	 * @return void
	 */
	public function wcpi_import_product() {
		if ( ! check_ajax_referer( 'wcpi_ajax_nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid Request' );
		}
		global $WCPI;
		delete_option( 'wcpi_parent_child_relations' );
		$settings = wcpi_settings();
		$settings->set_import_started_time();
		$settings->set_maybe_import_run();
		$WCPI->Import->import_products();
		wp_send_json_success( 'import_started' );
		wp_die();
	}

	/**
	 * Get the total Import status
	 *
	 * @return void
	 */
	public function wcpi_get_import_status() {
		if ( ! check_ajax_referer( 'wcpi_ajax_nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid Request' );
		}
		$settings = wcpi_settings();
		if ( $settings->maybe_import_run() ) {
			$total_imported_product_number    = $settings->get_total_imported_products();
			$total_filtered_products_from_api = $settings->get_total_filtered_products_from_api();
			$response                         = array(
				'status'                  => 'running',
				'total_imported_products' => $total_imported_product_number,
				'total_products_from_api' => $total_filtered_products_from_api,
			);
			
			if ( $total_imported_product_number >= $total_filtered_products_from_api ) {
				$draft_products = get_posts(
					array(
						'post_type'      => 'product',
						'post_status'    => 'auto-draft',
						'posts_per_page' => -1,
						'fields'         => 'ids',
						'meta_query'     => array(
							'relation' => 'AND',
							array(
								'key'     => 'wcpi_product_id',
								'type'    => 'NUMERIC',
								'compare' => '>=',
								'value'   => 0,
							),
							array(
								'key'     => 'wcpi_import_later',
								'type'    => 'NUMERIC',
								'compare' => '=',
								'value'   => 1,
							),
						),
					)
				);

				if ( is_array( $draft_products ) && ! empty( $draft_products ) ) {
					global $WCPI;
					$response = array(
						'status'                  => 'running',
						'type'                    => 'additional_products',
						'total_imported_products' => $total_imported_product_number,
						'total_products_from_api' => $total_filtered_products_from_api + count( $draft_products ),
					);
					$settings->set_additional_products_to_import( $draft_products );

					if ( ! $settings->maybe_additional_products_import_run() ) {
						$WCPI->Import->import_included_products();
						$settings->set_maybe_additional_products_import_run();
					}
				} else {
					$settings->delete_maybe_import_run();
					$settings->delete_import_started_time();
					$settings->delete_additional_products_to_import();
					$settings->delete_maybe_additional_products_import_run();
					delete_metadata( 'term', '', 'wcpi_term_updated_import_id', '', true );
					delete_option( 'wcpi_parent_child_relations' );
					$response['status'] = 'completed';
				}
			}
		} else {
			$response = array(
				'status' => 'unknown',
			);
		}
		wp_send_json_success( $response );
		wp_die();
	}

	/**
	 * Reset Import Status
	 *
	 * @return void
	 */
	public function reset_import_status() {
		$settings = wcpi_settings();
		$settings->delete_maybe_import_run();
	}

	/**
	 * Set Imported Store URL
	 *
	 * @return void
	 */
	public function set_imported_store_url() {
		if ( ! check_ajax_referer( 'wcpi_ajax_nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid Request' );
		}
		$settings = wcpi_settings();
		$store    = isset( $_POST['store'] ) && ! empty( $_POST['store'] ) ? sanitize_url( wp_unslash( $_POST['store'] ) ) : '';
		$settings->set_imported_store_url( $store );
		wp_die();
	}
}

new WCPI_AJAX();
