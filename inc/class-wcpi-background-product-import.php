<?php
/**
 * Class for Import Product in Background
 *
 * @package PluginDevs
 * @since   0.9.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WCPI_Background_Product_Import' ) ) {
	/**
	 * Background Product Import Class
	 */
	class WCPI_Background_Product_Import extends WC_Background_Process {

		/**
		 * Initiate new background process.
		 */
		public function __construct() {
			$this->action = 'wcpi_import_product';

			wc_set_time_limit( 0 );

			parent::__construct();
		}

		/**
		 * Task
		 *
		 * Override this method to perform any actions required on each
		 * queue item. Return the modified item for further processing
		 * in the next pass through. Or, return false to remove the
		 * item from the queue.
		 *
		 * @param mixed $item Queue item to iterate over.
		 *
		 * @return mixed
		 */
		protected function task( $item ) {
			// Actions to perform.
			if ( ! empty( $item ) ) {
				$this->push_to_queue_log( $item->id );
				$logger  = wc_get_logger();
				$context = array( 'source' => 'wcpi' );
				$logger->debug( 'processing ' . wc_print_r( $item->id, true ) . ' ' . wc_print_r( $item->name, true ), $context );
				$settings                      = wcpi_settings();
				$total_imported_product_number = $settings->get_total_imported_products();
				$args                          = array(
					'wcpi_product_id' => $item->id,
				);
				$existing_product              = wcpi_get_product( $args );
				$existing_product_id           = 0;
				if ( ! empty( $existing_product ) ) {
					$existing_product_id = $existing_product->get_id();
				}
				$item->wcpi_existing_product_id = $existing_product_id;
				$product_id                     = wcpi_create_product( $item );
				if ( $product_id > 0 ) {
					$settings->set_total_imported_products( $total_imported_product_number + 1 );
				}
			}

			return false;
		}

		/**
		 * Push Product ID to Log Array which contain processed product ids
		 * 
		 * @param integer $id ID of the element
		 * 
		 * @return array $array Array of Elements 
		 */
		protected function push_to_queue_log( $id ) {
			$array   = array();
			$array[] = $id;
			return $array;
		}

		/**
		 * Get Batch Items
		 */
		public function get_batch_items() {
			$batches = $this->get_batch();
			if ( isset( $this->get_batch()->data ) && ! empty( $this->get_batch()->data ) && is_array( $this->get_batch()->data ) ) {
				return $this->get_batch()->data;
			} else {
				return array();
			}
		}

		/**
		 * Cancel the Background Process
		 */
		public function cancel() {

		}

		/**
		 * Complete
		 *
		 * Override if applicable, but ensure that the below actions are
		 * performed, or, call parent::complete().
		 */
		protected function complete() {
			global $WCPI;
			parent::complete();
			$settings               = wcpi_settings();
			$current_importing_page = $settings->get_current_importing_page();
			$total_imported_page    = $settings->set_total_imported_page( $current_importing_page + 1 );
			// Show notice to user or perform some other arbitrary task...
			$next_products = $WCPI->Client->list_product(
				array(
					'page' => $total_imported_page,
				)
			);
			if ( ! empty( $next_products ) ) {
				$WCPI->Import->import_products();
			} else {
				$settings->delete_maybe_import_run();
			}
		}
	}
}
