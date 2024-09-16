<?php
/**
 * Class for Import Images in Background
 *
 * @package PluginDevs
 * @since   0.9.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WCPI_Background_Image_Import' ) ) {
	/**
	 * Background Image Import Class
	 */
	class WCPI_Background_Image_Import extends WC_Background_Process {
		/**
		 * Hold the Background Import action name
		 *
		 * @var string
		 */
		protected $action = 'wcpi_import_product';

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
				$logger  = wc_get_logger();
				$context = array( 'source' => 'wcpi-image' );
				$logger->debug( wc_print_r( $item, true ), $context );
			}

			return false;
		}

		/**
		 * Get batch Items
		 *
		 * @return mixed
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
		 * Complete
		 *
		 * Override if applicable, but ensure that the below actions are
		 * performed, or, call parent::complete().
		 */
		protected function complete() {

			parent::complete();

			// Show notice to user or perform some other arbitrary task...
		}
	}
}
