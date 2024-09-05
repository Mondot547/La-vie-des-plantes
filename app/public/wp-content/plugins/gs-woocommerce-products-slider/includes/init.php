<?php
namespace GSWPS;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('plugins_loaded', function() {
    
    /**
     * Compatibility check with Pro plugin
     */
    if ( is_woocommerce_compatible() && is_pro_compatible() ) {
        /**
         * Activation redirects
         */
        register_activation_hook( GSWPS_PLUGIN_FILE, 'GSWPS\on_activation' );

        /**
         * Init Appsero
         */
        gs_appsero_init();

        /**
         * Load Main Plugin
         */
        require_once GSWPS_FILES_DIR . '/includes/plugin.php';
    }
    
    /**
     * Remove Reviews Metadata on plugin Deactivation.
     */
    register_deactivation_hook( GSWPS_PLUGIN_FILE, 'GSWPS\on_deactivation' );
    
    /**
     * Plugins action links
     */
    add_filter( 'plugin_action_links_' . plugin_basename( GSWPS_PLUGIN_FILE ), 'GSWPS\add_pro_link' );
    
    /**
     * Plugins Load Text Domain
     */
    add_action( 'init', 'GSWPS\gs_load_textdomain' );

}, -10 );