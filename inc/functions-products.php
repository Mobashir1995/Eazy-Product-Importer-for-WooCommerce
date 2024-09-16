<?php
/**
 * Product Helper Functions
 *
 * @package PluginDevs
 * @since   0.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 *
 * Get Product
 *
 * @param mixed $prod_args Arguments.
 * @return mixed
 */
function wcpi_get_product( $prod_args ) {
	$default_prod_args = array(
		'wcpi_product_id' => 0,
		'post_type'       => 'product',
	);
	$prod_args         = wp_parse_args( $prod_args, $default_prod_args );
	$product           = array();

	if ( absint( $prod_args['wcpi_product_id'] ) > 0 ) {
		$statuses = array_keys( get_post_stati() );
		$args     = array(
			'post_type'      => $prod_args['post_type'],
			'posts_per_page' => '1',
			'post_status'    => $statuses,
			'meta_query'     => array( //phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => 'wcpi_product_id',
					'value'   => $prod_args['wcpi_product_id'],
					'compare' => '=',
				),
			),
		);

		$query = new WP_Query( $args );
		if ( $query->found_posts > 0 ) {
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$product = wc_get_product( get_the_ID() );
				}
				wp_reset_postdata();
			}
		}
	}
	return $product;
}

/**
 * Create Product
 *
 * @param mixed $args Arguments.
 * @return mixed
 */
function wcpi_create_product( $args ) {
	global $woocommerce, $WCPI;

	if ( ! function_exists( 'wcpi_get_product_object_type' ) && ! function_exists( 'wcpi_prepare_product_attributes' ) ) {
		return false;
	}

	$logger              = wc_get_logger();
	$context             = array( 'source' => 'wcpi-api-products' );
	$settings            = wcpi_settings();
	$existing_product_id = 0;
	// Get an empty instance of the product object (defining it's type).
	if ( $args->wcpi_existing_product_id > 0 ) {
		$existing_product    = wc_get_product( $args->wcpi_existing_product_id );
		$existing_product_id = $existing_product->get_id();
	}

	$product = wcpi_get_product_object_type( $args->type, $existing_product_id );
	if ( ! is_a( $product, 'WC_Product' ) ) {
		$logger->debug(
			'not a product ' . wc_print_r( $args, true ),
			$context
		);
		return false;
	}

	$product_id = $product->get_id();

	// Product name (Title) and slug.
	$product->set_name( $args->name ); // Name (title).
	if ( isset( $args->slug ) ) {
		$product->set_slug( $args->slug );
	}

	// Description and short description:.
	$product->set_description( $args->description );
	$product->set_short_description( $args->short_description );

	// Status publish, pending, draft or trash.
	if ( in_array( $settings->get_default_product_status(), array( 'publish', 'pending', 'draft' ), true ) ) {
		$status = $settings->get_default_product_status();
	} else {
		$status = isset( $args->status ) ? $args->status : $settings->get_default_product_status();
	}
	$product->set_status( $status );

	// Visibility hidden, visible, search or catalog.
	$product->set_catalog_visibility( isset( $args->catalog_visibility ) ? $args->catalog_visibility : 'visible' );

	// Featured boolean.
	$product->set_featured( isset( $args->featured ) ? $args->featured : false );

	// Virtual boolean.
	$product->set_virtual( isset( $args->virtual ) ? $args->virtual : false );

	// Prices.
	$product->set_regular_price( $args->regular_price );
	$product->set_sale_price( isset( $args->sale_price ) ? $args->sale_price : '' );
	$product->set_price( isset( $args->sale_price ) ? $args->sale_price : $args->regular_price );
	if ( isset( $args->sale_price ) ) {
		$product->set_date_on_sale_from( isset( $args->date_on_sale_from ) ? $args->date_on_sale_from : '' );
		$product->set_date_on_sale_to( isset( $args->date_on_sale_to ) ? $args->date_on_sale_to : '' );
	}
	$product->set_date_created( isset( $args->date_created ) ? $args->date_created : '' );
	$product->set_date_modified( isset( $args->date_modified ) ? $args->date_modified : '' );

	//phpcs:disable
	// $product->set_on_sale( isset( $args->on_sale ) ? $args->on_sale :  '' );
	// $product->set_purchasable( isset( $args->purchasable ) ? $args->purchasable :  '' );
	//phpcs:enable
	$product->set_total_sales( isset( $args->total_sales ) ? $args->total_sales : 0 );

	// Downloadable boolean.
	$product->set_downloadable( isset( $args->downloadable ) ? $args->downloadable : false );
	if ( isset( $args->downloadable ) && $args->downloadable ) {
		if ( is_array( $args->downloads ) && ! empty( $args->downloads ) ) {
			$downloadable_products = array();
			foreach ( $args->downloads as $downloads ) {
				$name = $downloads->name;
				$file = wcpi_download_downloadble_file_from_url( $name, $downloads->file );
				if ( is_array( $file ) && ! empty( $file ) ) {
					if ( isset( $file['file_url'] ) && ! empty( $file['file_url'] ) ) {
						$download        = array(
							'download_id' => wp_generate_uuid4(),
							'name'        => $name,
							'file'        => $file['file_url'],
						);
						$download_object = new WC_Product_Download();
						$download_object->set_id( $download['download_id'] );
						$download_object->set_name( $download['name'] );
						$download_object->set_file( $download['file'] );
						$downloadable_products[ $download['download_id'] ] = $download_object;
					}
				}
			}
			if ( ! empty( $downloadable_products ) ) {
				$product->set_downloads( $downloadable_products );
			}
		}
		$product->set_download_limit( isset( $args->download_limit ) ? $args->download_limit : '-1' );
		$product->set_download_expiry( isset( $args->download_expiry ) ? $args->download_expiry : '-1' );
	}

	// Taxes.
	if ( get_option( 'woocommerce_calc_taxes' ) === 'yes' ) {
		$product->set_tax_status( isset( $args->tax_status ) ? $args->tax_status : 'taxable' );
		$product->set_tax_class( isset( $args->tax_class ) ? $args->tax_class : '' );
	}

	if ( isset( $args->sku ) && ! empty( $args->sku ) ) {
		add_filter( 'wc_product_has_unique_sku', '__return_false' );
		$product->set_sku( $args->sku );

	}
		$product->set_manage_stock( isset( $args->manage_stock ) ? $args->manage_stock : false );
		$product->set_low_stock_amount( isset( $args->low_stock_amount ) ? $args->low_stock_amount : '' );
		$product->set_stock_status( isset( $args->stock_status ) ? $args->stock_status : 'instock' );
	if ( isset( $args->manage_stock ) && $args->manage_stock ) {
		$product->set_stock_status( 'yes' );
		$product->set_stock_quantity( $args->stock_quantity );
		$product->set_backorders( isset( $args->backorders ) ? $args->backorders : 'no' ); // yes, no or notify.
	}
	// } phpcs:disable

	// Sold Individually.
	$product->set_sold_individually( isset( $args->sold_individually ) ? $args->sold_individually : false );

	// Weight, dimensions and shipping class.
	$product->set_weight( isset( $args->weight ) ? $args->weight : '' );
	$product->set_length( isset( $args->dimensions->length ) ? $args->dimensions->length : '' );
	$product->set_width( isset( $args->dimensions->width ) ? $args->dimensions->width : '' );
	$product->set_height( isset( $args->dimensions->height ) ? $args->dimensions->height : '' );
	if ( isset( $args->shipping_class_id ) && isset( $args->shipping_class ) ) {

		$shipping_term = get_term_by( 'name', $args->shipping_class, 'product_shipping_class' );

		if ( ! $shipping_term || is_wp_error( $shipping_term ) ) {
			$shipping_term = (object) wp_insert_term( $args->shipping_class, 'product_shipping_class' );
		}
		if ( ! is_wp_error( $shipping_term ) ) {
			$product->set_shipping_class_id( $shipping_term->term_id );
		}
	}

	// Attributes et default attributes.
	if ( 'variation' !== $product->get_type() ) {
		if ( isset( $args->attributes ) ) {
			$product->set_attributes( wcpi_prepare_product_attributes( $args->attributes, $product ) );
		}
		if ( isset( $args->default_attributes ) ) {
			$product->set_default_attributes( $args->default_attributes ); // Needs a special formatting.
		}
	}

	if ( 'variation' === $product->get_type() ) {
		$product->set_parent_id( $args->parent_id );

		if ( isset( $args->attributes ) ) {
			$product->set_attributes( wcpi_prepare_product_variation_attributes( $args->attributes, $product ) );
		}
		if ( isset( $args->default_attributes ) ) {
			// $product->set_default_attributes( $args->default_attributes ); // Needs a special formatting.
		}
	}

	// Reviews, purchase note and menu order.
	$product->set_reviews_allowed( isset( $args->reviews_allowed ) ? $args->reviews_allowed : false );
	$product->set_average_rating( isset( $args->average_rating ) ? $args->average_rating : '0.00' );
	$product->set_rating_counts( isset( $args->rating_count ) ? $args->rating_count : 0 );
	$product->set_purchase_note( isset( $args->purchase_note ) ? $args->purchase_note : '' );
	if ( isset( $args->menu_order ) ) {
		$product->set_menu_order( $args->menu_order );
	}

		$upsell_context = array( 'source' => 'wcpi-api-upsell-products' );
		$logger->debug( 'product id start ' . $args->id, $upsell_context );
		$logger->debug( 'related product ids ' . wc_print_r( $args->upsell_ids, true ), $upsell_context );
		// Upsell IDs.
	if ( isset( $args->upsell_ids ) && ! empty( $args->upsell_ids ) ) {

		$upsell_ids = array();

		foreach ( $args->upsell_ids as $upsell ) {
			$upsell_args      = array(
				'wcpi_product_id' => $upsell,
			);
			$existing_product = wcpi_get_product( $upsell_args );
			$upsell_id        = 0;
			if ( ! empty( $existing_product ) ) {
				$upsell_id = $existing_product->get_id();
				$logger->debug( 'upsell product existed for ' . wc_print_r( $upsell_id, true ), $upsell_context );
			} else {
				$upsell_id = wp_insert_post(
					array(
						'post_type'    => 'product',
						'post_status'  => 'auto-draft',
						'post_title'   => $upsell,
						'post_content' => $upsell,
						'meta_input'   => array(
							'wcpi_product_id'   => $upsell,
							'wcpi_import_later' => 1,
						),
					)
				);
				$logger->debug( 'upsell product created for ' . wc_print_r( $upsell_id, true ), $upsell_context );
			}

			array_push( $upsell_ids, $upsell_id );
		}
		if ( is_array( $upsell_ids ) && ! empty( $upsell_ids ) ) {
			$logger->debug( 'upsell product array list' . wc_print_r( $upsell_ids, true ), $upsell_context );
			$product->set_upsell_ids( $upsell_ids );
		}
	}

	//phpcs:enable
	if ( isset( $args->cross_sell_ids ) && ! empty( $args->cross_sell_ids ) ) {
		$cross_sell_ids = array();

		foreach ( $args->cross_sell_ids as $cross_sell ) {
			$cross_sell_args  = array(
				'wcpi_product_id' => $cross_sell,
			);
			$existing_product = wcpi_get_product( $cross_sell_args );
			$cross_sell_id    = 0;
			if ( ! empty( $existing_product ) ) {
				$cross_sell_id = $existing_product->get_id();
				$logger->debug( 'upsell product existed for ' . wc_print_r( $cross_sell_id, true ), $upsell_context );
			} else {
				$cross_sell_id = wp_insert_post(
					array(
						'post_type'    => 'product',
						'post_status'  => 'auto-draft',
						'post_title'   => $cross_sell,
						'post_content' => $cross_sell,
						'meta_input'   => array(
							'wcpi_product_id'   => $cross_sell,
							'wcpi_import_later' => 1,
						),
					)
				);
				$logger->debug( 'cross_sell product created for ' . wc_print_r( $cross_sell_id, true ), $upsell_context );
			}

			array_push( $cross_sell_ids, $cross_sell_id );
		}
		if ( is_array( $cross_sell_ids ) && ! empty( $cross_sell_ids ) ) {
			$logger->debug( 'cross_sell product array list' . wc_print_r( $cross_sell_ids, true ), $upsell_context );
			$product->set_cross_sell_ids( $cross_sell_ids );
		}
	}

	// Set Group Products
	if ( isset( $args->grouped_products ) && ! empty( $args->grouped_products ) ) {

		$grouped_product_ids = array();

		foreach ( $args->grouped_products as $grouped_product ) {
			$grouped_args       = array(
				'wcpi_product_id' => $grouped_product,
			);
			$existing_product   = wcpi_get_product( $grouped_args );
			$grouped_product_id = 0;
			if ( ! empty( $existing_product ) ) {
				$grouped_product_id = $existing_product->get_id();
				$logger->debug( 'Grouped product existed for ' . wc_print_r( $grouped_product_id, true ), $upsell_context );
			} else {
				$grouped_product_id = wp_insert_post(
					array(
						'post_type'    => 'product',
						'post_status'  => 'auto-draft',
						'post_title'   => $grouped_product,
						'post_content' => $grouped_product,
						'meta_input'   => array(
							'wcpi_product_id'   => $grouped_product,
							'wcpi_import_later' => 1,
						),
					)
				);
				$logger->debug( 'Grouped product created for ' . wc_print_r( $grouped_product_id, true ), $upsell_context );
			}
			array_push( $grouped_product_ids, $grouped_product_id );
		}
		if ( is_array( $grouped_product_ids ) && ! empty( $grouped_product_ids ) ) {
			$logger->debug( 'Grouped product array list' . wc_print_r( $grouped_product_ids, true ), $upsell_context );
			$product->set_children( $grouped_product_ids );
		}
	}

	// SAVE PRODUCT.
	$product_id = $product->save();

	if ( $product_id > 0 && 'variation' !== $product->get_type() ) {

		// Set Variations
		if ( isset( $args->variations )
			&& ! empty( $args->variations )
			&& is_array( $args->variations )
		) {
			global $WCPI;
			$variants = $WCPI->Client->get_product_variants(
				$args->id,
				array(
					'per_page' => 100,
					'include'  => $args->variations,
					'parent'   => $args->id,
				)
			);
			foreach ( $variants as $variant ) {
				$variant->parent_id  = $product_id;
				$variant->type       = 'variation';
				$existing_variant    = wcpi_get_product(
					array(
						'wcpi_product_id' => $variant->id,
						'post_type'       => 'product_variation',
					)
				);
				$existing_variant_id = 0;

				if ( ! empty( $existing_variant ) ) {
					$existing_variant_id = $existing_variant->get_id();
				}
				$variant->wcpi_existing_product_id = $existing_variant_id;

				$logger  = wc_get_logger();
				$context = array( 'source' => 'wcpi-product-single-variant' );
				$logger->debug( 'response for ' . $product_id . ' is ' . wc_print_r( $variant, true ), $context );
				wcpi_create_product( $variant );
			}
		}

		if ( $product->is_type( 'external' ) ) {
			if ( isset( $args->external_url ) && ! empty( $args->external_url ) ) {
				update_post_meta( $product_id, '_product_url', $args->external_url );
			}
			if ( isset( $args->button_text ) && ! empty( $args->button_text ) ) {
				update_post_meta( $product_id, '_button_text', $args->button_text );
			}
		}

		// Set Default Categories.
		$default_categories = $settings->get_default_attached_categories();
		if ( isset( $default_categories ) ) {
			$default_categories = (object) array( 'categories' => $default_categories );
			wcpi_attached_product_categories( $product_id, $default_categories );
		}

		// Set Product Categories.
		if ( isset( $args->categories ) ) {
			$categories = (object) array( 'categories' => $args->categories );
			wcpi_attached_product_categories( $product_id, $categories );
		}

		// Set Default Tags.
		$default_tags = $settings->get_default_attached_tags();
		if ( isset( $default_tags ) ) {
			$default_tags = (object) array( 'categories' => $default_tags );
			wcpi_attached_product_categories( $product_id, $default_tags, 'product_tag' );
		}

		// Set Product Tags.
		if ( isset( $args->tags ) ) {
			$tags = (object) array( 'categories' => $args->tags );
			wcpi_attached_product_categories( $product_id, $tags, 'product_tag' );
		}
	}

	if ( $product_id > 0 ) {
		if ( $settings->get_download_images_status() ) {
			// Images and Gallery.
			if ( isset( $args->images ) && ! empty( $args->images ) ) {
				$count           = 0;
				$imageGalleryIds = array();
				foreach ( $args->images as $image ) {
					$count++;
					$imageId = wcpi_get_attachment_id_from_url( $image->src, $product_id );
					if ( ! is_wp_error( $imageId ) && $imageId > 0 ) {
						if ( 1 === $count ) {
							// And finally assign featured image to post.
							set_post_thumbnail( $product_id, $imageId );
						} elseif ( $count > 1 ) {
							array_push( $imageGalleryIds, $imageId );
						}
					}
				}

				if ( is_array( $imageGalleryIds ) && ! empty( $imageGalleryIds ) ) {
					update_post_meta( $product_id, '_product_image_gallery', implode( ',', $imageGalleryIds ) );
				}
			}
		}

		if ( get_post_meta( $product_id, 'wcpi_import_later', true ) ) {
			delete_post_meta( $product_id, 'wcpi_import_later' );
		}
		update_post_meta( $product_id, 'wcpi_product_id', $args->id );
	}

	if ( 'additional_product' === $args->import_type ) {
		$additional_products = $settings->get_additional_products_to_import();
		$removed_index       = array_search( $product_id, $additional_products );

		if ( false !== $removed_index ) {
			array_splice( $additional_products, $removed_index, 1 );
			$settings->set_additional_products_to_import( $additional_products );
		}
	}

	do_action( 'wcpi_product_created', $product_id, $product->get_type() );
	return $product_id;
}

/**
 * Utility function that returns the correct product object instance
 *
 * @param mixed $type Product Type.
 * @param int   $product_id Product ID.
 *
 * @return \WC_Product_Variable|\WC_Product_Grouped|\WC_Product_External|\WC_Product_Simple|fal
 */
function wcpi_get_product_object_type( $type, $product_id = 0 ) {
	// Get an instance of the WC_Product object, depending on his type.
	if ( isset( $type ) && 'variable' === $type ) {
		$product = new WC_Product_Variable( $product_id );
	} elseif ( isset( $type ) && 'grouped' === $type ) {
		$product = new WC_Product_Grouped( $product_id );
	} elseif ( isset( $type ) && 'external' === $type ) {
		$product = new WC_Product_External( $product_id );
	} elseif ( isset( $type ) && 'variation' === $type ) {
		$product = new WC_Product_Variation( $product_id );
	} else {
		$product = new WC_Product_Simple( $product_id ); // "simple" By default
	}

	if ( ! is_a( $product, 'WC_Product' ) ) {
		return false;
	} else {
		return $product;
	}
}

/**
 * Utility function that prepare product attributes before saving
 *
 * @param mixed  $attributes Product Attributes.
 * @param object $product    WC_Product
 *
 * @return \WC_Product_Attribute[]
 */
function wcpi_prepare_product_attributes( $attributes, $product ) {
	$wc_attributes = array();

	foreach ( $attributes as $taxonomy => $values ) {
		if ( ! $values->slug || empty( $values->slug ) ) {
			continue;
		}

		$position       = 0;
		$attribute_slug = '';
		$visible        = false;
		$variation      = false;
		$attribute_name = isset( $values->name ) && ! empty( $values->name ) ? $values->name : $values->slug;
		$is_taxonomy    = isset( $values->id ) && intval( $values->id ) > 0 ? true : false;

		if ( $values->variation ) {
			$variation = true;
		}

		if ( $values->visible ) {
			$visible = true;
		}

		if ( $values->position ) {
			$position = $values->position;
		}

		if ( $values->slug ) {
			$attribute_slug = $values->slug;
		}

		// Get an instance of the WC_Product_Attribute Object.
		$attribute  = new WC_Product_Attribute();
		$term_ids   = array();
		$term_names = array();

		if ( $is_taxonomy ) {
			$wc_attr_id = wc_attribute_taxonomy_id_by_name( $attribute_slug );

			if ( ! ( is_int( $wc_attr_id ) && intval( $wc_attr_id ) > 0 )
			) {
				$wc_attr_id = wc_create_attribute(
					array(
						'name' => $attribute_name,
						'slug' => $attribute_slug,
						'type' => 'select',
					)
				);
			}

			if ( ! taxonomy_exists( $attribute_slug ) ) {
				$taxonomy_data = array(
					'hierarchical'          => false,
					'update_count_callback' => '_update_post_term_count',
					'labels'                => array(
						/* translators: %s: attribute name */
						'name'              => sprintf( _x( 'Product %s', 'Product Attribute', 'wcpi' ), $attribute_name ),
						'singular_name'     => $attribute_name,
						/* translators: %s: attribute name */
						'search_items'      => sprintf( __( 'Search %s', 'wcpi' ), $attribute_name ),
						/* translators: %s: attribute name */
						'all_items'         => sprintf( __( 'All %s', 'wcpi' ), $attribute_name ),
						/* translators: %s: attribute name */
						'parent_item'       => sprintf( __( 'Parent %s', 'wcpi' ), $attribute_name ),
						/* translators: %s: attribute name */
						'parent_item_colon' => sprintf( __( 'Parent %s:', 'wcpi' ), $attribute_name ),
						/* translators: %s: attribute name */
						'edit_item'         => sprintf( __( 'Edit %s', 'wcpi' ), $attribute_name ),
						/* translators: %s: attribute name */
						'update_item'       => sprintf( __( 'Update %s', 'wcpi' ), $attribute_name ),
						/* translators: %s: attribute name */
						'add_new_item'      => sprintf( __( 'Add new %s', 'wcpi' ), $attribute_name ),
						/* translators: %s: attribute name */
						'new_item_name'     => sprintf( __( 'New %s', 'wcpi' ), $attribute_name ),
						/* translators: %s: attribute name */
						'not_found'         => sprintf( __( 'No &quot;%s&quot; found', 'wcpi' ), $attribute_name ),
						/* translators: %s: attribute name */
						'back_to_items'     => sprintf( __( '&larr; Back to "%s" attributes', 'wcpi' ), $attribute_name ),
					),
					'show_ui'               => true,
					'show_in_quick_edit'    => false,
					'show_in_menu'          => false,
					'meta_box_cb'           => false,
					'query_var'             => false,
					'rewrite'               => false,
					'sort'                  => false,
					'public'                => false,
					'show_in_nav_menus'     => false,
					'capabilities'          => array(
						'manage_terms' => 'manage_product_terms',
						'edit_terms'   => 'edit_product_terms',
						'delete_terms' => 'delete_product_terms',
						'assign_terms' => 'assign_product_terms',
					),
				);

				$register_tax = register_taxonomy(
					$attribute_slug,
					apply_filters( "woocommerce_taxonomy_objects_{$attribute_slug}", array( 'product' ) ),
					apply_filters( "woocommerce_taxonomy_args_{$attribute_slug}", $taxonomy_data )
				);
			}

			// Loop through the term names.
			foreach ( $values->options as $terms => $term ) {
				$term_exists = term_exists( $term, $attribute_slug );
				if ( $term_exists && is_array( $term_exists ) && intval( $term_exists['term_id'] ) > 0 ) {
					// Get and set the term ID in the array from the term name.
					$term_ids[]   = $term_exists['term_id'];
					$term_names[] = $term;
				} else {
					$insert_term = wp_insert_term( $term, $attribute_slug );
					if ( ! is_wp_error( $insert_term ) &&
						is_array( $insert_term ) &&
						! empty( $insert_term ) &&
						absint( $insert_term['term_id'] ) > 0
					) {
						$term_ids[]   = $insert_term['term_id'];
						$term_names[] = $term;
					}
				}
			}

			$attribute->set_id( $wc_attr_id );
			$attribute->set_name( $attribute_slug );
			$attribute->set_options( $term_names );
			$attribute->set_position( $position );
			$attribute->set_visible( $visible );
			$attribute->set_variation( $variation );
		} else {
			foreach ( $values->options as $terms => $term ) {
				$term_names[] = $term;
			}
			$attribute->set_name( $attribute_slug );
			$attribute->set_options( $term_names );
			$attribute->set_position( $position );
			$attribute->set_visible( $visible );
			$attribute->set_variation( $variation );
		}

		// $position++; // Increase position.
		$wc_attributes[] = $attribute;
	}

	return $wc_attributes;
}

/**
 * Utility function that prepare product variation attributes before saving
 *
 * @param mixed  $attributes Variation Attributes.
 * @param object $variant    WC_Product_Variation
 *
 * @return \WC_Product_Attribute[]
 */
function wcpi_prepare_product_variation_attributes( $attributes, $variant ) {
	$wc_attributes = array();
	foreach ( $attributes as $key => $value ) {
		$attribute_value = '';
		if ( isset( $value->slug ) && ! empty( $value->slug ) ) {
			if ( isset( $value->id ) && ! empty( $value->id ) && intval( $value->id ) > 0 ) {
				$term = get_term_by( 'name', $value->option, $value->slug );
				if ( $term && ! is_wp_error( $term ) ) {
					$attribute_value = $term->slug;
				} else {
					$attribute_value = sanitize_title( $value->option );
				}
			} else {
				$attribute_value = $value->option;
			}

			if ( '' !== $attribute_value ) {
				$wc_attributes[ $value->slug ] = $attribute_value;
			}
		}
	}

	return $wc_attributes;
}
