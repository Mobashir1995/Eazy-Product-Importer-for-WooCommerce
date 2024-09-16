<?php
/**
 * Class for Import Product in Background
 *
 * @package PluginDevs
 * @since   0.9.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WCPI_Background_Included_Product_Import' ) ) {
	/**
	 * Background Product Import Class
	 */
	class WCPI_Background_Included_Product_Import extends WC_Background_Process {

		/**
		 * Initiate new background process.
		 */
		public function __construct() {
			$this->action = 'wcpi_included_product_import';

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
		 * @param mixed $items Queue item to iterate over.
		 *
		 * @return mixed
		 */
		protected function task( $items ) {
			// Actions to perform.
			if ( is_array( $items ) && ! empty( $items ) ) {
				global $WCPI;
				$settings          = wcpi_settings();
				$included_products = array();
				foreach ( $items as $item ) {
					array_push( $included_products, get_post_meta( $item, 'wcpi_product_id', true ) );
				}
				$product_lists = $WCPI->Client->list_included_products( array( 'include' => $included_products ) );

				if ( is_array( $product_lists ) && ! empty( $product_lists ) ) {
					foreach ( $product_lists as $product ) {
						$product->import_type = 'additional_product';
						$WCPI->Import->create_single_product( $product );
					}
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
		 * Handle
		 *
		 * Pass each queue item to the task handler, while remaining
		 * within server memory and time limit constraints.
		 */
		protected function handle() {
			$this->lock_process();

			do {
				$batch = $this->get_batch();

				if ( empty( $batch->data ) ) {
					break;
				}

				foreach ( $batch->data as $key => $value ) {
					$task = $this->task( $value );

					if ( false !== $task ) {
						$batch->data[ $key ] = $task;
					} else {
						unset( $batch->data[ $key ] );
					}

					if ( $this->time_exceeded() || $this->memory_exceeded() ) {
						// Batch limits reached.
						break;
					}
				}

				// Update or delete current batch.
				if ( ! empty( $batch->data ) ) {
					$this->update( $batch->key, $batch->data );
				} else {
					$this->delete( $batch->key );
				}
			} while ( ! $this->time_exceeded() && ! $this->memory_exceeded() && ! $this->is_queue_empty() );

			$this->unlock_process();

			// Start next batch or complete process.
			if ( ! $this->is_queue_empty() ) {
				$this->dispatch();
			} else {
				$this->complete();
			}

			wp_die();
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
			$settings = wcpi_settings();
			// Show notice to user or perform some other arbitrary task...
			$additional_products = $settings->get_additional_products_to_import();
			if ( is_array( $additional_products ) && ! empty( $additional_products ) ) {
				$WCPI->Import->import_included_products();
			}
		}

	}
}
