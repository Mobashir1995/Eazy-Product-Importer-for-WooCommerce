<?php
/**
 * Category Helper Functions
 *
 * @package PluginDevs
 * @since   0.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Attach Category to Product. Create Category if not Exists. If Exists then Update it
 *
 * @param integer $product_id   Product ID
 * @param object  $args         Category Argument.
 * @param string  $taxonomy     Taxonomy Name. Default product_cat.
 *
 * @return void
 */
function wcpi_attached_product_categories( $product_id, $args, $taxonomy = 'product_cat' ) {
	if ( is_array( $args->categories ) && ! empty( $args->categories ) ) {
		$settings   = wcpi_settings();
		$categories = array();
		$term_args  = array();

		foreach ( $args->categories as $category ) {
			$cat_id             = 0;
			$field              = is_scalar( $category ) ? $category : $category->slug;
			$term_exists        = term_exists( $field, $taxonomy );
			$is_term_exists     = false;
			$term_maybe_updated = false;

			// $term_exists = get_term_by( 'slug', $field, $taxonomy );
			// if ( ! is_wp_error( $term_exists ) && ! empty( $term_exists ) ) {

			if ( $term_exists && is_array( $term_exists ) ) {
				$cat_id          = (int) $term_exists['term_id'];
				$term_process_id = get_term_meta( $cat_id, 'wcpi_term_updated_import_id', true );
				$is_term_exists  = true;

				// Ignore Updating if the term is already processed in current Import
				if ( $term_process_id === $settings->get_import_started_time() ) {
					$term_maybe_updated = true;
				}
			}

			if ( ! $is_term_exists && is_object( $category ) ) {
				$term_by_meta = get_terms(
					array(
						'hide_empty' => false, // also retrieve terms which are not used yet
						'order'      => 'DESC',
						'orderby'    => 'id',
						'number'     => 1,
						'meta_query' => array(
							array(
								'key'   => 'wcpi_term_id',
								'value' => $category->id,
							),
						),
						'taxonomy'   => $taxonomy,
					)
				);

				if ( ! empty( $term_by_meta ) && ! is_wp_error( $term_by_meta ) ) {
					foreach ( $term_by_meta as $meta_term ) {
						$cat_id          = (int) $meta_term->term_id;
						$term_process_id = get_term_meta( $cat_id, 'wcpi_term_updated_import_id', true );
						$is_term_exists  = true;
						// Ignore Updating if the term is already processed in current Import
						if ( $term_process_id === $settings->get_import_started_time() ) {
							$term_maybe_updated = true;
						}
					}
				}
			}

			if ( ! $is_term_exists ) {
				$insert_term = wp_insert_term( $category->name, $taxonomy, array( 'slug' => $category->slug ) );
				if ( ! is_wp_error( $insert_term ) && is_array( $insert_term ) && ! empty( $insert_term ) && absint( $insert_term['term_id'] ) > 0 ) {
					$cat_id = (int) $insert_term['term_id'];
				}
			}

			if ( $cat_id > 0 && false === $term_maybe_updated ) {
				update_term_meta( $cat_id, 'wcpi_term_updated_time', current_time( 'timestamp' ) );
				update_term_meta( $cat_id, 'wcpi_term_updated_import_id', $settings->get_import_started_time() );

				$api_category = (object) array();

				if ( is_object( $category ) && isset( $category->name ) && ! empty( $category->name ) ) {
					$term_args['name'] = $category->name;
				}

				if ( is_object( $category ) && isset( $category->slug ) && ! empty( $category->slug ) ) {
					$term_args['slug'] = $category->slug;
				}

				if ( is_object( $category ) && isset( $category->id ) && intval( $category->id ) > 0 ) {
					update_term_meta( $cat_id, 'wcpi_term_id', $category->id );
				}

				/**
				 * Get Category Info from API
				 */
				if ( is_object( $category ) && isset( $category->id ) && intval( $category->id ) > 0 ) {
					global $WCPI;
					$api_category = $WCPI->Client->get_category( $category->id );
				}

				/**
				 * Set Category Info from API
				 */
				if (
					is_object( $api_category ) && ! empty( $api_category ) && isset( $api_category->id ) && intval( $api_category->id ) > 0
				) {

					if ( is_object( $category ) && isset( $category->description ) && ! empty( $category->description ) ) {
						$term_args['description'] = $category->description;
					}

					if ( is_object( $category ) && isset( $category->display ) ) {
						update_term_meta( $cat_id, 'display_type', $category->display );
					}

					/**
					 * Assign Parent Term.
					 */
					if ( is_taxonomy_hierarchical( $taxonomy ) && isset( $api_category->parent ) && intval( $api_category->parent ) > 0 ) {

						$parent_term = get_terms(
							array(
								'hide_empty' => false, // also retrieve terms which are not used yet
								'order'      => 'DESC',
								'orderby'    => 'id',
								'number'     => 1,
								'meta_query' => array(
									array(
										'key'   => 'wcpi_term_id',
										'value' => $api_category->parent,
									),
								),
								'taxonomy'   => $taxonomy,
							)
						);

						if ( ! empty( $parent_term ) && ! is_wp_error( $parent_term ) ) {
							foreach ( $parent_term as $parent ) {
								$term_args['parent'] = (int) $parent->term_id;
							}
						} else {
							// Recursively Create Parent Category if already not Exist.
							wcpi_recursively_create_parent_categories( $api_category, $taxonomy );
							$parent_term = get_terms(
								array(
									'hide_empty' => false, // also retrieve terms which are not used yet
									'order'      => 'DESC',
									'orderby'    => 'id',
									'number'     => 1,
									'meta_query' => array(
										array(
											'key'   => 'wcpi_term_id',
											'value' => $api_category->parent,
										),
									),
									'taxonomy'   => $taxonomy,
								)
							);

							if ( ! empty( $parent_term ) && ! is_wp_error( $parent_term ) ) {
								foreach ( $parent_term as $parent ) {
									$term_args['parent'] = (int) $parent->term_id;
								}
							}
						}
					}
				}

				$cat_update = wp_update_term(
					$cat_id,
					$taxonomy,
					$term_args
				);

				if ( ! is_wp_error( $cat_update ) && is_array( $cat_update ) && ! empty( $cat_update ) && absint( $cat_update['term_id'] ) > 0 ) {
					$cat_id = (int) $cat_update['term_id'];
				}
			}

			if ( $cat_id > 0 ) {
				// Add all categories to array to add to the product.
				$cat_id = (int) $cat_id;
				array_push( $categories, $cat_id );
			}
		}

		if ( ! empty( $categories ) ) {
			if ( 'product_cat' === $taxonomy ) {
				$default_categories    = $settings->get_default_attached_categories();
				$uncategorized_term_id = get_option( 'default_product_cat' );
				$uncategorized_term    = get_term_by( 'term_id', $uncategorized_term_id, $taxonomy );

				if ( ! in_array( $uncategorized_term->slug, $default_categories, true ) ) {
					wp_remove_object_terms( $product_id, $uncategorized_term->term_id, $taxonomy );
				}
			}
			wp_set_object_terms( $product_id, $categories, $taxonomy, true );
		}
	}
}

/**
 * Create Parent Categories Recursively from the API
 *
 * This will create Categories from all level of parent child relationship
 *
 * @param object  $category
 * @param string  $taxonomy
 * @param integer $child
 *
 * @return integer $cat_id The First Inner Category ID
 *
 * $api_cat = {
 *      id: 9,
 *      parent: 10 | 0,
 *      slug: 'slug',
 *      name: 'name',
 *      description: 'description',
 *      display_type: 'display_type'
 * }
 */
function wcpi_recursively_create_parent_categories( $category, $taxonomy = 'product_cat', $child = 0 ) {

	if ( ! is_taxonomy_hierarchical( $taxonomy ) ||
		! is_object( $category ) ||
		empty( $category ) ||
		! isset( $category->id ) ||
		intval( $category->id ) <= 0
	) {
		return 0;
	}

	$cat_id             = 0;
	$term_args          = array();
	$parent_category    = (object) array();
	$term_process_id    = get_term_meta( $category->id, 'wcpi_term_updated_import_id', true );
	$term_maybe_updated = false;
	$settings           = wcpi_settings();

	if ( $term_process_id === $settings->get_import_started_time() ) {
		$term_maybe_updated = true;
	}

	if ( $term_maybe_updated ) {
		return 0;
	}

	if ( is_object( $category ) && isset( $category->name ) && ! empty( $category->name ) ) {
		$term_args['name'] = $category->name;
	}

	if ( is_object( $category ) && isset( $category->slug ) && ! empty( $category->slug ) ) {
		$term_args['slug'] = $category->slug;
	}

	if ( is_object( $category ) && isset( $category->description ) && ! empty( $category->description ) ) {
		$term_args['description'] = $category->description;
	}

	/**
	 * Create Category here.
	 */
	$new_term = wp_insert_term( $category->name, $taxonomy, $term_args );
	if ( ! is_wp_error( $new_term ) &&
		is_array( $new_term ) &&
		! empty( $new_term ) &&
		absint( $new_term['term_id'] ) > 0
	) {
		$cat_id = (int) $new_term['term_id'];

		update_term_meta( $cat_id, 'wcpi_term_id', $category->id );
		update_term_meta( $cat_id, 'wcpi_term_updated_time', current_time( 'timestamp' ) );
		update_term_meta( $cat_id, 'wcpi_term_updated_import_id', $settings->get_import_started_time() );
		if ( is_object( $category ) && isset( $category->description ) && ! empty( $category->description ) ) {
			update_term_meta( $cat_id, 'display_type', $category->display );
		}

		// Assign Parent Child Relationship on WPDB.
		if ( is_integer( $child ) && intval( $child ) > 0 ) {
			$parent_child_cat_relation = get_option( 'wcpi_parent_child_relations', array() );
			array_push(
				$parent_child_cat_relation,
				array(
					'parent' => $cat_id,
					'child'  => $child,
				)
			);
			update_option( 'wcpi_parent_child_relations', $parent_child_cat_relation );
		}
	}

	/**
	 * Check for parent category and call recursively if parent is existed.
	 */
	if ( isset( $category->parent ) && intval( $category->parent ) > 0 ) {
		global $WCPI;
		$parent_category = $WCPI->Client->get_category( $category->parent );

		if (
			is_object( $parent_category ) &&
			! empty( $parent_category ) &&
			isset( $parent_category->parent ) &&
			intval( $parent_category->parent ) > 0
		) {
			/**
			 * @param object  $parent_category Parent Category Details from API.
			 * @param string  $taxonomy        Taxonomy Name
			 * @param integer $cat_id          Recently Created Child Category
			 */
			wcpi_recursively_create_parent_categories( $parent_category, $taxonomy, $cat_id );
		}
	}

	$parent_child_cat_relation = get_option( 'wcpi_parent_child_relations', array() );
	if ( ! empty( $parent_child_cat_relation ) ) {
		foreach ( $parent_child_cat_relation as $relation ) {
			wp_update_term(
				$relation['child'],
				$taxonomy,
				array(
					'parent' => $relation['parent'],
				)
			);
		}
		delete_option( 'wcpi_parent_child_relations' );
	}

	return $cat_id;
}
