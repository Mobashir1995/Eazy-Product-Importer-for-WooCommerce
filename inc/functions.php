<?php
/**
 * Utility Helper Functions
 *
 * @package PluginDevs
 * @since   0.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Check if API Key is valid
 *
 * @return boolean
 */
function wcpi_check_api_key() {
	$consumer_key    = get_option( 'wb_wcpi_consumer_key' );
	$consumer_secret = get_option( 'wb_wcpi_consumer_secret' );
	$store_url       = get_option( 'wb_wcpi_store_url' );
	if ( ! empty( $store_url ) && ! empty( $consumer_key ) && ! empty( $consumer_secret ) ) {
		return true;
	}
	return false;
}

/**
 * Get Raw Store Domain Name from http URL
 *
 * @param mixed $url Website URL.
 * @return string
 */
function wcpi_get_store_name_from_url( $url ) {
	$parse     = wp_parse_url( $url );
	$url       = preg_replace( '#^www.(.+.)#i', '$1', $parse['host'] );
	$wp_domain = $url . '/' . $parse['path'];
	$wp_domain = str_replace( '//', '/', $wp_domain );
	return $wp_domain;
}

/**
 * Hold the Settings Object
 *
 * @return object WCPI_Settings
 */
function wcpi_settings() {
	$settings = new WCPI_Settings();
	return $settings;
}

/**
 * Download Downloadble file from a remote URL
 *
 * @param mixed $name File Name.
 * @param mixed $file File Object.
 *
 * @return (string|string[]|null)[]|false
 */
function wcpi_download_downloadble_file_from_url( $name, $file ) {
	$store_url  = get_option( 'wb_wcpi_store_url' );
	$store_path = wcpi_get_store_name_from_url( $store_url );
	// add product image.
	if ( ! function_exists( 'media_handle_upload' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
	}

	// Download file to temp location.
	$tmp = download_url( $file );

	// If error storing temporarily, unlink.
	if ( is_wp_error( $tmp ) ) {
		@unlink( $tmp['tmp_name'] );
	} else {
		// Array based on $_FILE as seen in PHP file uploads.
		$file_array = array(
			'name'     => basename( $file ), // ex: wp-header-logo.png
			'tmp_name' => $tmp,
			'error'    => 0,
			'size'     => filesize( $tmp ),
		);

		$upload_dir        = wp_get_upload_dir();
		$wc_downloads_path = $upload_dir['basedir'] . '/woocommerce_uploads/';
		$download_path     = $wc_downloads_path . $store_path;
		//phpcs:disable
		// $new_file = wp_unique_filename($download_path, basename($file_array['name']));
		//phpcs:enable
		$new_file      = basename( $file_array['name'] );
		$new_file_path = $download_path . $new_file;

		if ( ! is_dir( $download_path ) ) {
			wp_mkdir_p( $download_path );
		}
		$move_new_file = @copy( $file_array['tmp_name'], $new_file_path );
		unlink( $file_array['tmp_name'] );

		if ( $move_new_file ) {
			$wc_downloads_url = $upload_dir['url'] . '/woocommerce_uploads/';
			$url              = $upload_dir['baseurl'] . '//woocommerce_uploads/' . $store_path . '/' . $file_array['name'];
			$url              = preg_replace( '/([^:])(\/{2,})/', '$1/', $url );

			$results = array(
				'file_path' => $new_file_path,
				'file_url'  => $url,
			);
			return $results;
		}
	}

	return false;
}

/**
 * Download Image from a remote URL
 *
 * @param mixed $url File URL.
 * @param int   $post_id Media ID.
 *
 * @return int|\WP_Error
 */
function wcpi_upload_image( $url, $post_id = 0 ) {
	// add product image.
	if ( ! function_exists( 'media_handle_upload' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
	}
	$thumb_url = $url;

	// Download file to temp location.
	$tmp = download_url( $thumb_url );

	// Set variables for storage.
	// fix file name for query strings.
	preg_match( '/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $thumb_url, $matches );
	$file_array['name']     = basename( $matches[0] );
	$file_array['tmp_name'] = $tmp;

	// If error storing temporarily, unlink.
	if ( is_wp_error( $tmp ) ) {
		@unlink( $file_array['tmp_name'] );
	}

	// use media_handle_sideload to upload img.
	$thumbid = media_handle_sideload( $file_array, $post_id );

	// If error storing permanently, unlink.
	if ( is_wp_error( $thumbid ) ) {
		@unlink( $file_array['tmp_name'] );
	}

	return $thumbid;
}

/**
 * Get attachment ID.
 *
 * @param  string $url        Attachment URL.
 * @param  int    $product_id Product ID.
 * @return int
 * @throws Exception If attachment cannot be loaded.
 */
function wcpi_get_attachment_id_from_url( $url, $product_id ) {
	if ( empty( $url ) ) {
		return 0;
	}

	$id         = 0;
	$upload_dir = wp_upload_dir( null, false );
	$base_url   = $upload_dir['baseurl'] . '/';

	// Check first if attachment is inside the WordPress uploads directory, or we're given a filename only.
	if ( false !== strpos( $url, $base_url ) || false === strpos( $url, '://' ) ) {
		// Search for yyyy/mm/slug.extension or slug.extension - remove the base URL.
		$file = str_replace( $base_url, '', $url );
		$args = array(
			'post_type'   => 'attachment',
			'post_status' => 'any',
			'fields'      => 'ids',
            'meta_query'  => array( // @codingStandardsIgnoreLine.
				'relation' => 'OR',
				array(
					'key'     => '_wp_attached_file',
					'value'   => '^' . $file,
					'compare' => 'REGEXP',
				),
				array(
					'key'     => '_wp_attached_file',
					'value'   => '/' . $file,
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_wcpi_attachment_source',
					'value'   => '/' . $file,
					'compare' => 'LIKE',
				),
			),
		);
	} else {
		// This is an external URL, so compare to source.
		$args = array(
			'post_type'   => 'attachment',
			'post_status' => 'any',
			'fields'      => 'ids',
            'meta_query'  => array( // @codingStandardsIgnoreLine.
				array(
					'value' => $url,
					'key'   => '_wcpi_attachment_source',
				),
			),
		);
	}

    $ids = get_posts( $args ); // @codingStandardsIgnoreLine.

	if ( $ids ) {
		$id = current( $ids );
	}

	// Upload if attachment does not exists.
	if ( ! $id && stristr( $url, '://' ) ) {
		$id = wcpi_upload_image( $url, $product_id );

		if ( ! is_wp_error( $id ) && $id > 0 ) {
			// Save attachment source for future reference.
			update_post_meta( $id, '_wcpi_attachment_source', $url );
		}
	}

	return $id;
}

/**
 * Convert date to ISO Complient date
 *
 * @param mixed $date Date.
 *
 * @return mixed
 */
function wcpi_convert_ISO_date( $date ) {
	if ( strtotime( $date ) ) {
		$datetime = new WC_DateTime( $date );
		$value    = $datetime->date( 'Y-m-d\TH:i:s' );
		return $value;
	}
	return false;
}
