<?php
namespace GSWPS;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

final class Dummy_Data {

    public function __construct() {

        if ( ! is_admin() ) return;

        add_action( 'wp_ajax_gswps_import_shortcode_data', array($this, 'import_shortcode_data') );

        add_action( 'wp_ajax_gswps_remove_shortcode_data', array($this, 'remove_shortcode_data') );
        
        // Shortcodes
        add_action( 'gswps_dummy_shortcodes_process_start', function() {

            // Force delete option if have any
            delete_option( 'gswps_dummy_shortcode_data_created' );

            // Force update the process
            set_transient( 'gswps_dummy_shortcode_data_creating', 1, 3 * MINUTE_IN_SECONDS );

        });

        add_action( 'gswps_dummy_shortcodes_process_finished', function() {

            // clean the record that we have started a process
            delete_transient( 'gswps_dummy_shortcode_data_creating' );

            // Add a track so we never duplicate the process
            update_option( 'gswps_dummy_shortcode_data_created', 1 );

        });

        add_action( 'plugins_loaded', [$this, 'maybe_import_shortcodes_initially'], 100 );
        
    }

    public function import_shortcode_data() {

        // Validate nonce && check permission
        if ( !check_admin_referer('_gswps_import_demo_gs_') || !current_user_can('manage_options') ) wp_send_json_error( __('Unauthorised Request', 'gswps'), 401 );

        // Hide the notice
        update_option( 'gswps_dismiss_demo_data_notice', 1 );

        // Start importing
        $this->_import_shortcode_data();

    }

    public function remove_shortcode_data() {

        // Validate nonce && check permission
        if ( !check_admin_referer('_gswps_import_demo_gs_') || !current_user_can('manage_options') ) wp_send_json_error( __('Unauthorised Request', 'gswps'), 401 );

        // Hide the notice
        update_option( 'gswps_dismiss_demo_data_notice', 1 );

        // Remove data
        $this->_remove_shortcode_data();
    }

    public function _import_shortcode_data( $is_ajax = null ) {

        if ( $is_ajax === null ) $is_ajax = wp_doing_ajax();

        // Data already imported
        if ( get_option('gswps_dummy_shortcode_data_created') !== false || get_transient('gswps_dummy_shortcode_data_creating') !== false ) {

            $message_202 = __( 'Dummy Shortcodes already imported', 'gswps' );

            if ( $is_ajax ) wp_send_json_success( $message_202, 202 );
            
            return [
                'status' => 202,
                'message' => $message_202
            ];

        }
        
        // Importing demo shortcodes
        $this->create_dummy_shortcodes();

        $message = __( 'Dummy Shortcodes imported', 'gswps' );

        if ( $is_ajax ) wp_send_json_success( $message, 200 );

        return [
            'status' => 200,
            'message' => $message
        ];

    }

    public function _remove_shortcode_data( $is_ajax = null ) {

        if ( $is_ajax === null ) $is_ajax = wp_doing_ajax();

        $this->delete_dummy_shortcodes();

        delete_option( 'gswps_dummy_shortcode_data_created' );
        delete_transient( 'gswps_dummy_shortcode_data_creating' );

        $message = __( 'Dummy Shortcodes deleted', 'gswps' );

        if ( $is_ajax ) wp_send_json_success( $message, 200 );

        return [
            'status' => 200,
            'message' => $message
        ];

    }

    // Shortcode
    public function create_dummy_shortcodes() {

        do_action( 'gswps_dummy_shortcodes_process_start' );

        plugin()->builder->create_dummy_shortcodes();

        do_action( 'gswps_dummy_shortcodes_process_finished' );

    }

    // Shortcode
    public function maybe_import_shortcodes_initially() {

        if ( get_option('gswps_install_demo_shortcodes_initially') == true ) {

            $shortcodes = plugin()->builder->_get_shortcodes();

            if ( empty( $shortcodes ) ) {

                do_action( 'gswps_dummy_shortcodes_process_start' );

                plugin()->builder->create_dummy_shortcodes();

                do_action( 'gswps_dummy_shortcodes_process_finished' );
            }

            delete_option( 'gswps_install_demo_shortcodes_initially' );

        }

    }

    public function delete_dummy_shortcodes() {
        
        plugin()->builder->delete_dummy_shortcodes();

    }

}