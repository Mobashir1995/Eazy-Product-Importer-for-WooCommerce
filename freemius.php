<?php

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'epifw_fs' ) ) {
    // Create a helper function for easy SDK access.
    function epifw_fs() {
        global $epifw_fs;

        if ( ! isset( $epifw_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $epifw_fs = fs_dynamic_init( array(
                'id'                  => '16625',
                'slug'                => 'easy-product-importer-for-woocommerce',
                'premium_slug'        => 'easy-product-importer-for-woocommerce-pro',
                'type'                => 'plugin',
                'public_key'          => 'pk_8d22be20bbf1468c770b8b6f44691',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_premium_version' => true,
                'has_paid_plans'      => true,
                'menu'                => array(
                    'slug'           => 'wcpi-product-import',
                ),
            ) );
        }

        return $epifw_fs;
    }

    // Init Freemius.
    epifw_fs();
    // Signal that SDK was initiated.
    do_action( 'epifw_fs_loaded' );
}