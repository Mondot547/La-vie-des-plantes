<?php

namespace GSWPS;

/**
 * Protect direct access
 */
if (!defined('ABSPATH')) exit;

final class Builder {

    private $option_name = 'gs_woo_slider_shortcode_prefs';
    private $layout_config_option_name = 'gs_woo_slider_layout_config';

    public function __construct() {

        add_action('admin_menu', array($this, 'register_sub_menu'));
        add_action('admin_enqueue_scripts', array($this, 'scripts'));
        add_action('wp_enqueue_scripts', array($this, 'preview_scripts'));

        add_action('wp_ajax_gswoo_create_shortcode', array($this, 'create_shortcode'));
        add_action('wp_ajax_gswoo_clone_shortcode', array($this, 'clone_shortcode'));
        add_action('wp_ajax_gswoo_get_shortcode', array($this, 'get_shortcode'));
        add_action('wp_ajax_gswoo_update_shortcode', array($this, 'update_shortcode'));
        add_action('wp_ajax_gswoo_delete_shortcodes', array($this, 'delete_shortcodes'));
        add_action('wp_ajax_gswoo_temp_save_shortcode_settings', array($this, 'temp_save_shortcode_settings'));
        add_action('wp_ajax_gswoo_get_shortcodes', array($this, 'get_shortcodes'));

        add_action('wp_ajax_gswoo_get_shortcode_pref', array($this, 'get_shortcode_pref'));
        add_action('wp_ajax_gswoo_save_shortcode_pref', array($this, 'save_shortcode_pref'));

        add_action('wp_ajax_gswoo_get_layout_config', array($this, 'get_layout_config'));
        add_action('wp_ajax_gswoo_save_layout_config', array($this, 'save_layout_config'));

        add_action('template_include', array($this, 'populate_shortcode_preview'));
        add_action('show_admin_bar', array($this, 'hide_admin_bar_from_preview'));

        return $this;
    }

    public function hide_admin_bar_from_preview($visibility) {

        if ( is_preview() ) return false;

        return $visibility;
    }

    public function add_shortcode_body_class($classes) {

        if ( is_preview()) return array_merge($classes, array('gswoo-shortcode-preview--page'));

        return $classes;
    }

    public function populate_shortcode_preview($template) {

        global $wp, $wp_query;

        if ( is_preview()) {

            // Create our fake post
            $post_id = rand(1, 99999) - 9999999;
            $post = new \stdClass();
            $post->ID = $post_id;
            $post->post_author = 1;
            $post->post_date = current_time('mysql');
            $post->post_date_gmt = current_time('mysql', 1);
            $post->post_title = __('Shortcode Preview', 'gswps');
            $post->post_content = '[gswoo preview="yes" id="' . $_REQUEST['gswoo_shortcode_preview'] . '"]';
            $post->post_status = 'publish';
            $post->comment_status = 'closed';
            $post->ping_status = 'closed';
            $post->post_name = 'fake-page-' . rand(1, 99999); // append random number to avoid clash
            $post->post_type = 'page';
            $post->filter = 'raw'; // important!

            // Convert to WP_Post object
            $wp_post = new \WP_Post($post);

            // Add the fake post to the cache
            wp_cache_add($post_id, $wp_post, 'posts');

            // Update the main query
            $wp_query->post = $wp_post;
            $wp_query->posts = array($wp_post);
            $wp_query->queried_object = $wp_post;
            $wp_query->queried_object_id = $post_id;
            $wp_query->found_posts = 1;
            $wp_query->post_count = 1;
            $wp_query->max_num_pages = 1;
            $wp_query->is_page = true;
            $wp_query->is_singular = true;
            $wp_query->is_single = false;
            $wp_query->is_attachment = false;
            $wp_query->is_archive = false;
            $wp_query->is_category = false;
            $wp_query->is_tag = false;
            $wp_query->is_tax = false;
            $wp_query->is_author = false;
            $wp_query->is_date = false;
            $wp_query->is_year = false;
            $wp_query->is_month = false;
            $wp_query->is_day = false;
            $wp_query->is_time = false;
            $wp_query->is_search = false;
            $wp_query->is_feed = false;
            $wp_query->is_comment_feed = false;
            $wp_query->is_trackback = false;
            $wp_query->is_home = false;
            $wp_query->is_embed = false;
            $wp_query->is_404 = false;
            $wp_query->is_paged = false;
            $wp_query->is_admin = false;
            $wp_query->is_preview = false;
            $wp_query->is_robots = false;
            $wp_query->is_posts_page = false;
            $wp_query->is_post_type_archive = false;

            // Update globals
            $GLOBALS['wp_query'] = $wp_query;
            $wp->register_globals();

            include GSWPS_FILES_DIR . '/includes/shortcode-builder/preview.php';

            return;
        }

        return $template;
    }

    public function register_sub_menu() {

        add_menu_page(
            __('Product Views ', 'gswps'),
            __('Product Views', 'gswps'),
            'manage_options',
            'gs-woo-slider',
            array($this, 'view'),
            GSWPS_FILES_URI . '/assets/img/icon.svg',
            GSWPS_MENU_POSITION
        );

        add_submenu_page(
            'gs-woo-slider',
            __('Shortcodes ', 'gswps'),
            __('Shortcodes', 'gswps'),
            'manage_options',
            'gs-woo-slider',
            array($this, 'view'),
        );

    }

    public function view() {

        include_once GSWPS_FILES_DIR . '/includes/shortcode-builder/page.php';
    }

    public function scripts($hook) {

        if ('toplevel_page_gs-woo-slider' != $hook) return;

        wp_register_style('gs-zmdi-fonts', GSWPS_FILES_URI . '/assets/libs/material-design-iconic-font/css/material-design-iconic-font.min.css', '', GSWPS_VERSION, 'all');

        if ( ! is_pro_active() || ! gswps_pro_is_valid() ) {
            wp_register_style('gs-woo-shortcode', GSWPS_FILES_URI . '/assets/admin/css/gs-woops-shortcode.min.css', array('gs-zmdi-fonts'), GSWPS_VERSION, 'all');
            wp_register_script('gs-woo-shortcode', GSWPS_FILES_URI . '/assets/admin/js/gs-woops-shortcode.min.js', array('jquery'), GSWPS_VERSION, true);
        }

        do_action('gs_woo_register_scripts');

        wp_localize_script('gs-woo-shortcode', '_gswps_data', $this->get_localized_data());

        wp_enqueue_style('gs-woo-shortcode');
        wp_enqueue_script('gs-woo-shortcode');
    }

    public function get_localized_data() {

        $data = array(
            "nonce" => array(
                "create_shortcode"                 => wp_create_nonce("_gswps_create_shortcode_gs_"),
                "clone_shortcode"                  => wp_create_nonce("_gswps_clone_shortcode_gs_"),
                "update_shortcode"                 => wp_create_nonce("_gswps_update_shortcode_gs_"),
                "delete_shortcodes"                => wp_create_nonce("_gswps_delete_shortcodes_gs_"),
                "temp_save_shortcode_settings"     => wp_create_nonce("_gswps_temp_save_shortcode_settings_gs_"),
                "save_shortcode_pref"              => wp_create_nonce("_gswps_save_shortcode_pref_gs_"),
                "import_woowps_demo"               => wp_create_nonce("_gswps_import_demo_gs_"),
            ),
            "ajaxurl" => admin_url("admin-ajax.php"),
            "adminurl" => admin_url(),
            "siteurl" => home_url()
        );

        $data['shortcode_settings'] = $this->get_shortcode_default_settings();
        $data['shortcode_options']  = $this->get_shortcode_default_options();
        $data['translations']       = $this->get_translation_srtings();
        $data['preference']         = $this->get_shortcode_default_prefs();
        $data['preference_options'] = $this->get_shortcode_prefs_options();
        $data['layoutConfig']         = $this->get_default_layout_config();
        $data['layoutConfig_options'] = $this->get_layout_config_options();

        $data['demo_data'] = [
            'woops_data'      => wp_validate_boolean(get_option('gswps_dummy_woops_data_created')),
            'shortcode_data'  => wp_validate_boolean(get_option('gswps_dummy_shortcode_data_created'))
        ];

        return $data;
    }

    public function preview_scripts() {

        if (! is_preview()) return;

        wp_enqueue_style('gs-woo-shortcode-preview', GSWPS_FILES_URI . '/assets/css/gs-woops-shortcode-preview.min.css', '', GSWPS_VERSION, 'all');
    }

    public function gswoo_get_wpdb() {

        global $wpdb;

        if (wp_doing_ajax()) $wpdb->show_errors = false;

        return $wpdb;
    }

    public function gswoo_check_db_error() {

        $wpdb = $this->gswoo_get_wpdb();

        if ($wpdb->last_error === '') return false;

        return true;
    }

    public function validate_shortcode_settings($shortcode_settings) {

        $shortcode_settings = shortcode_atts( $this->get_shortcode_default_settings(), $shortcode_settings );

        $shortcode_settings['gs_show_title']                = wp_validate_boolean( $shortcode_settings['gs_show_title'] );
        $shortcode_settings['show_hide_rating']             = wp_validate_boolean( $shortcode_settings['show_hide_rating'] );
        $shortcode_settings['show_count_down_timer']        = wp_validate_boolean( $shortcode_settings['show_count_down_timer'] );
        $shortcode_settings['show_hide_price']              = wp_validate_boolean( $shortcode_settings['show_hide_price'] );
        $shortcode_settings['show_hide_desc']               = wp_validate_boolean( $shortcode_settings['show_hide_desc'] );
        $shortcode_settings['show_hide_add_to_cart']        = wp_validate_boolean( $shortcode_settings['show_hide_add_to_cart'] );
        $shortcode_settings['gs_is_autoplay']               = wp_validate_boolean( $shortcode_settings['gs_is_autoplay'] );
        $shortcode_settings['gs_is_loop']                   = wp_validate_boolean( $shortcode_settings['gs_is_loop'] );
        $shortcode_settings['gs_stop_on_hover']             = wp_validate_boolean( $shortcode_settings['gs_stop_on_hover'] );
        $shortcode_settings['reverse_direction']            = wp_validate_boolean( $shortcode_settings['reverse_direction'] );
        $shortcode_settings['carousel_navs_enabled']        = wp_validate_boolean( $shortcode_settings['carousel_navs_enabled'] );
        $shortcode_settings['carousel_dots_enabled']        = wp_validate_boolean( $shortcode_settings['carousel_dots_enabled'] );
        $shortcode_settings['dynamic_dots_enabled']         = wp_validate_boolean( $shortcode_settings['dynamic_dots_enabled'] );

        $shortcode_settings['is_add_to_wishlist_enabled']   = wp_validate_boolean( $shortcode_settings['is_add_to_wishlist_enabled'] );
        $shortcode_settings['is_compare_button_enabled']    = wp_validate_boolean( $shortcode_settings['is_compare_button_enabled'] );
        $shortcode_settings['is_quick_view_enabled']        = wp_validate_boolean( $shortcode_settings['is_quick_view_enabled'] );
        $shortcode_settings['display_hot_badge']            = wp_validate_boolean( $shortcode_settings['display_hot_badge'] );
        $shortcode_settings['display_out_of_stock_badge']   = wp_validate_boolean( $shortcode_settings['display_out_of_stock_badge'] );
        $shortcode_settings['display_sale_badge']           = wp_validate_boolean( $shortcode_settings['display_sale_badge'] );
        $shortcode_settings['sale_badge_type']              = sanitize_text_field( $shortcode_settings['sale_badge_type'] );

        $shortcode_settings['image_type']                   = sanitize_text_field( $shortcode_settings['image_type'] );
        $shortcode_settings['order']                        = sanitize_text_field( $shortcode_settings['order'] );
        $shortcode_settings['orderby']                      = sanitize_text_field( $shortcode_settings['orderby'] );
        $shortcode_settings['gs_woo_template']              = sanitize_text_field( $shortcode_settings['gs_woo_template'] );
        $shortcode_settings['view_type']                    = sanitize_text_field( $shortcode_settings['view_type'] );
        $shortcode_settings['columns']                      = sanitize_text_field( $shortcode_settings['columns'] );
        $shortcode_settings['columns_tablet']               = sanitize_text_field( $shortcode_settings['columns_tablet'] );
        $shortcode_settings['columns_mobile_portrait']      = sanitize_text_field( $shortcode_settings['columns_mobile_portrait'] );
        $shortcode_settings['columns_mobile']               = sanitize_text_field( $shortcode_settings['columns_mobile'] );
        $shortcode_settings['image_size']                   = sanitize_text_field( $shortcode_settings['image_size'] );
        $shortcode_settings['btn_background']               = sanitize_text_field( $shortcode_settings['btn_background'] );
        $shortcode_settings['btn_bg_hover']                 = sanitize_text_field( $shortcode_settings['btn_bg_hover'] );
        $shortcode_settings['btn_text_color']               = sanitize_text_field( $shortcode_settings['btn_text_color'] );
        $shortcode_settings['btn_text_hover_color']         = sanitize_text_field( $shortcode_settings['btn_text_hover_color'] );
        $shortcode_settings['star_rating_color']            = sanitize_text_field( $shortcode_settings['star_rating_color'] );

        $shortcode_settings['gs_l_products']            = (int) $shortcode_settings['gs_l_products'];
        $shortcode_settings['gs_gutter']                = (int) $shortcode_settings['gs_gutter'];
        $shortcode_settings['gs_carousel_speed']        = (int) $shortcode_settings['gs_carousel_speed'];
        $shortcode_settings['gs_ticker_speed']          = (int) $shortcode_settings['gs_ticker_speed'];
        $shortcode_settings['gs_autoplay_delay']        = (int) $shortcode_settings['gs_autoplay_delay'];
        $shortcode_settings['gs_excerpt_length']        = (int) $shortcode_settings['gs_excerpt_length'];

        $shortcode_settings['select_cats']              = array_map( 'intval', $shortcode_settings['select_cats'] );
        $shortcode_settings['exclude_cats']             = array_map( 'intval', $shortcode_settings['exclude_cats'] );
        $shortcode_settings['select_by_tag']            = array_map( 'intval', $shortcode_settings['select_by_tag'] );
        $shortcode_settings['deselect_by_tag']          = array_map( 'intval', $shortcode_settings['deselect_by_tag'] );
        $shortcode_settings['select_by_name']           = array_map( 'intval', $shortcode_settings['select_by_name'] );
        $shortcode_settings['deselect_by_name']         = array_map( 'intval', $shortcode_settings['deselect_by_name'] );
        $shortcode_settings['gs_woo_product_type']      = array_map( 'sanitize_key', $shortcode_settings['gs_woo_product_type'] );
        $shortcode_settings['gs_woo_ex_product_type']   = array_map( 'sanitize_key', $shortcode_settings['gs_woo_ex_product_type'] );
        
        return (array) $shortcode_settings;
    }

    protected function get_gswoo_shortcode_db_columns() {

        return array(
            'shortcode_name'     => '%s',
            'shortcode_settings' => '%s',
            'created_at'         => '%s',
            'updated_at'         => '%s'
        );
    }

    public function _get_shortcode($shortcode_id, $is_ajax = false) {

        if (empty($shortcode_id)) {
            if ($is_ajax) wp_send_json_error(__('Shortcode ID missing', 'gswps'), 400);
            return false;
        }

        $wpdb = $this->gswoo_get_wpdb();

        $shortcode = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}gs_woo_slider WHERE id = %d LIMIT 1", absint($shortcode_id)), ARRAY_A);

        if ($shortcode) {

            $shortcode["shortcode_settings"] = json_decode($shortcode["shortcode_settings"], true);
            $shortcode["shortcode_settings"] = $this->validate_shortcode_settings( $shortcode["shortcode_settings"] );

            wp_cache_add( 'gs_woo_shortcodes'. $shortcode_id, $shortcode, 'gs_wcps' );

            if ($is_ajax) wp_send_json_success($shortcode);

            return $shortcode;
        }

        if ($is_ajax) wp_send_json_error(__('No shortcode found', 'gswps'), 404);

        return false;
    }

    public function _update_shortcode($shortcode_id, $nonce, $fields, $is_ajax) {

        if ( ! wp_verify_nonce($nonce, '_gswps_update_shortcode_gs_') || !current_user_can('manage_options') ) {
            if ($is_ajax) wp_send_json_error(__('Unauthorised Request', 'gswps'), 401);
            return false;
        }

        if (empty($shortcode_id)) {
            if ($is_ajax) wp_send_json_error(__('Shortcode ID missing', 'gswps'), 400);
            return false;
        }

        $_shortcode = $this->_get_shortcode($shortcode_id, false);

        if (empty($_shortcode)) {
            if ($is_ajax) wp_send_json_error(__('No shortcode found to update', 'gswps'), 404);
            return false;
        }

        $shortcode_name = !empty($fields['shortcode_name']) ? $fields['shortcode_name'] : $_shortcode['shortcode_name'];
        $shortcode_settings  = !empty($fields['shortcode_settings']) ? $fields['shortcode_settings'] : $_shortcode['shortcode_settings'];

        // Remove dummy indicator on update
        if (isset($shortcode_settings['gswoo-demo_data'])) unset($shortcode_settings['gswoo-demo_data']);

        $shortcode_settings = $this->validate_shortcode_settings($shortcode_settings);

        $wpdb = $this->gswoo_get_wpdb();

        $data = array(
            "shortcode_name"         => $shortcode_name,
            "shortcode_settings"     => json_encode($shortcode_settings),
            "updated_at"             => current_time('mysql')
        );

        $num_row_updated = $wpdb->update("{$wpdb->prefix}gs_woo_slider", $data, array('id' => absint($shortcode_id)),  $this->get_gswoo_shortcode_db_columns());

        wp_cache_delete('gs_woo_shortcodes');

        if ( $this->gswoo_check_db_error() ) {
            if ($is_ajax) wp_send_json_error(sprintf(__('Database Error: %1$s', 'gswps'), $wpdb->last_error), 500);
            return false;
        }

        do_action('gs_woo_shortcode_updated', $num_row_updated);
        do_action('gsp_shortcode_updated', $num_row_updated);

        if ($is_ajax) wp_send_json_success(array(
            'message' => __('Shortcode updated', 'gswps'),
            'shortcode_id' => $num_row_updated
        ));

        return $num_row_updated;
    }

    public function _get_shortcodes($shortcode_ids = [], $is_ajax = false, $minimal = false) {

        $wpdb = $this->gswoo_get_wpdb();
        $fields = $minimal ? 'id, shortcode_name' : '*';

        if (!empty($shortcode_ids)) {

            $how_many = count($shortcode_ids);
            $placeholders = array_fill(0, $how_many, '%d');
            $format = implode(', ', $placeholders);
            $query = "SELECT {$fields} FROM {$wpdb->prefix}gs_woo_slider WHERE id IN($format)";

            $shortcodes = $wpdb->get_results($wpdb->prepare($query, $shortcode_ids), ARRAY_A);
        } else {

            $shortcodes = wp_cache_get('gs_woo_shortcodes');

            if (!empty($shortcodes)) {
                if ($is_ajax) wp_send_json_success($shortcodes);
                return $shortcodes;
            }

            $shortcodes = $wpdb->get_results("SELECT {$fields} FROM {$wpdb->prefix}gs_woo_slider ORDER BY id DESC", ARRAY_A);
        }

        // check for database error
        if ($this->gswoo_check_db_error()) wp_send_json_error(sprintf(__('Database Error: %s'), $wpdb->last_error));

        if (empty($shortcode_ids)) wp_cache_set('gs_woo_shortcodes', $shortcodes, '', DAY_IN_SECONDS);

        if ($is_ajax) wp_send_json_success($shortcodes);

        return $shortcodes;
    }

    public function create_shortcode() {

        // validate nonce && check permission
        if (!check_admin_referer('_gswps_create_shortcode_gs_') || !current_user_can('manage_options')) wp_send_json_error(__('Unauthorised Request', 'gswps'), 401);

        $shortcode_settings  = !empty($_POST['shortcode_settings']) ? $_POST['shortcode_settings'] : '';
        $shortcode_name  = !empty($_POST['shortcode_name']) ? $_POST['shortcode_name'] : __('Undefined', 'gswps');

        if (empty($shortcode_settings) || !is_array($shortcode_settings)) {
            wp_send_json_error(__('Please configure the settings properly', 'gswps'), 206);
        }

        $shortcode_settings = $this->validate_shortcode_settings($shortcode_settings);

        $wpdb = $this->gswoo_get_wpdb();

        $data = array(
            "shortcode_name" => $shortcode_name,
            "shortcode_settings" => json_encode($shortcode_settings),
            "created_at" => current_time('mysql'),
            "updated_at" => current_time('mysql'),
        );

        $wpdb->insert("{$wpdb->prefix}gs_woo_slider", $data, $this->get_gswoo_shortcode_db_columns());

        // check for database error
        if ($this->gswoo_check_db_error()) wp_send_json_error(sprintf(__('Database Error: %s'), $wpdb->last_error), 500);

        wp_cache_delete('gs_woo_shortcodes');

        do_action('gs_woo_shortcode_created', $wpdb->insert_id);
        do_action('gsp_shortcode_created', $wpdb->insert_id);

        do_action('gs-woo-shortcode-fired');

        // send success response with inserted id
        wp_send_json_success(array(
            'message' => __('Shortcode created successfully', 'gswps'),
            'shortcode_id' => $wpdb->insert_id
        ));
    }

    public function clone_shortcode() {

        // validate nonce && check permission
        if (!check_admin_referer('_gswps_clone_shortcode_gs_') || !current_user_can('manage_options')) wp_send_json_error(__('Unauthorised Request', 'gswps'), 401);

        $clone_id  = !empty($_POST['clone_id']) ? $_POST['clone_id'] : '';

        if (empty($clone_id)) wp_send_json_error(__('Clone Id not provided', 'gswps'), 400);

        $clone_shortcode = $this->_get_shortcode($clone_id, false);

        if (empty($clone_shortcode)) wp_send_json_error(__('Clone shortcode not found', 'gswps'), 404);

        $shortcode_settings  = $clone_shortcode['shortcode_settings'];
        $shortcode_name  = $clone_shortcode['shortcode_name'] . ' ' . __('- Cloned', 'gswps');

        $shortcode_settings = $this->validate_shortcode_settings($shortcode_settings);

        $wpdb = $this->gswoo_get_wpdb();

        $data = array(
            "shortcode_name" => $shortcode_name,
            "shortcode_settings" => json_encode($shortcode_settings),
            "created_at" => current_time('mysql'),
            "updated_at" => current_time('mysql'),
        );

        $wpdb->insert("{$wpdb->prefix}gs_woo_slider", $data, $this->get_gswoo_shortcode_db_columns());

        // check for database error
        if ($this->gswoo_check_db_error()) wp_send_json_error(sprintf(__('Database Error: %s'), $wpdb->last_error), 500);

        wp_cache_delete('gs_woo_shortcodes');

        // Get the cloned shortcode
        $shotcode = $this->_get_shortcode($wpdb->insert_id, false);

        // send success response with inserted id
        wp_send_json_success(array(
            'message' => __('Shortcode cloned successfully', 'gswps'),
            'shortcode' => $shotcode,
        ));
    }

    public function get_shortcode() {

        $shortcode_id = !empty($_GET['id']) ? absint($_GET['id']) : null;

        $this->_get_shortcode($shortcode_id, wp_doing_ajax());
    }

    public function update_shortcode($shortcode_id = null, $nonce = null) {

        $shortcode_id = !empty($_POST['id']) ? absint($_POST['id']) : null;
            
        if ( ! $nonce ) {
            $nonce = $_POST['_wpnonce'] ?: null;
        }

        if (empty($shortcode_id)) {
            wp_send_json_error(__('Shortcode ID missing', 'gswps'), 400);
        }

        $this->_update_shortcode($shortcode_id, $nonce, $_POST, true);
    }

    public function delete_shortcodes() {

        if (!check_admin_referer('_gswps_delete_shortcodes_gs_') || !current_user_can('manage_options'))
            wp_send_json_error(__('Unauthorised Request', 'gswps'), 401);

        $ids = isset($_POST['ids']) ? $_POST['ids'] : null;

        if (empty($ids)) {
            wp_send_json_error(__('No shortcode ids provided', 'gswps'), 400);
        }

        $wpdb = $this->gswoo_get_wpdb();

        $count = count($ids);

        $ids = implode(',', array_map('absint', $ids));
        $wpdb->query("DELETE FROM {$wpdb->prefix}gs_woo_slider WHERE ID IN($ids)");

        wp_cache_delete('gs_woo_shortcodes');

        if ($this->gswoo_check_db_error()) wp_send_json_error(sprintf(__('Database Error: %s'), $wpdb->last_error), 500);

        $m = _n("Shortcode has been deleted", "Shortcodes have been deleted", $count, 'gswps');

        wp_send_json_success(['message' => $m]);
    }

    public function get_shortcodes() {

        $this->_get_shortcodes(null, wp_doing_ajax());
    }

    public function temp_save_shortcode_settings() {

        if ( ! check_admin_referer('_gswps_temp_save_shortcode_settings_gs_') || !current_user_can('manage_options') )
            wp_send_json_error( __( 'Unauthorised Request', 'gswps' ), 401 );

        $temp_key = isset( $_POST['temp_key'] ) ? $_POST['temp_key'] : null;
        $shortcode_settings = isset( $_POST['shortcode_settings'] ) ? $_POST['shortcode_settings'] : [];

        if ( empty($temp_key) ) wp_send_json_error(__('No temp key provided', 'gswps'), 400);
        if ( empty($shortcode_settings) ) wp_send_json_error(__('No temp settings provided', 'gswps'), 400);

        delete_transient( $temp_key );
        set_transient( $temp_key, $this->validate_shortcode_settings( $shortcode_settings ), 86400 ); // save the transient for 1 day

        wp_send_json_success([
            'message' => __('Temp data saved', 'gswps'),
        ]);
    }

    public function get_translation_srtings() {
        return [
            'columns'                             => __('Columns', 'gswps'),
            'columns_tablet'                      => __('Columns Tablet', 'gswps'),
            'columns_mobile_portrait'             => __('Columns Portpait Mobile', 'gswps'),
            'columns_mobile'                      => __('Columns Mobile', 'gswps'),
            'exclude_cats'                        => __('Exclude By Cats', 'gswps'),
            'deselect_by_name'                    => __('Exclude Specific Products', 'gswps'),
            'deselect_by_tag'                     => __('Exclude By Tags', 'gswps'),
            'select_by_tag'                       => __('Include By Tags', 'gswps'),
            'select_by_name'                      => __('Display Specific Products', 'gswps'),
            'select_products'                     => __('Select Products', 'gswps'),
            'select_cats'                         => __('Include By Cats', 'gswps'),
            'gs-woo-template-type'                => __('Templates', 'gswps'),
            'gs-woo-cat-type'                     => __('Category Type', 'gswps'),
            'gs-woo-product-type'                 => __('Include By Type', 'gswps'),
            'gs-woo-exclude-product-type'         => __('Exclude By Type', 'gswps'),
            'gs-woo-cat-placeholder'              => __('Select Category', 'gswps'),
            'include_by_type'                     => __('Include Product Types', 'gswps'),
            'exclude_by_type'                     => __('Exclude Product Types', 'gswps'),
            'gs_woo_template'                     => __('Select Theme', 'gswps'),
            'gs_woo_template--help'               => __('Select preferred Style & Theme', 'gswps'),
            'view_type'                           => __('View Type', 'gswps'),
            'view_type--help'                     => __('Select Theme Style', 'gswps'),
            'reverse_direction'                   => __('Reverse Direction', 'gswps'),
            'reverse_direction__details'          => __('Reverse Direction for Carousel sliding', 'gswps'),    

            'gs_excerpt_length'                   => __('Description Length', 'gswps'),
            'gs_excerpt_length__details'          => __('Control the Description Character Length, 0 means all', 'gswps'),

            'carousel_navs_enabled'               => __('Enable Carousel Navs', 'gswps'),
            'carousel_navs_enabled__details'      => __('Enable carousel navs for this theme, it may not available for certain theme', 'gswps'),
            
            'carousel_navs_style'                 => __('Navs Style', 'gswps'),
            'carousel_navs_style__details'        => __('Select carousel navs style, this is available for certain theme', 'gswps'),
            
            'gs_slider_nav_color'                 => __('Nav Color', 'gswps'),
            'gs_slider_nav_bg_color'              => __('Nav BG Color', 'gswps'),
            'gs_slider_nav_hover_color'           => __('Nav Hover Color', 'gswps'),
            'gs_slider_nav_hover_bg_color'        => __('Nav Hover BG Color', 'gswps'),
            
            'carousel_dots_enabled'               => __('Enable Carousel Dots', 'gswps'),
            'carousel_dots_enabled__details'      => __('Enable carousel dots for this theme, it may not available for certain theme', 'gswps'),
            
            'dynamic_dots_enabled'                => __('Enable Dynamic Dots', 'gswps'),
            'dynamic_dots_enabled__details'       => __('Enable carousel dynamic dots for this theme.', 'gswps'),
            
            'carousel_dots_style'                 => __('Dots Style', 'gswps'),
            'carousel_dots_style__details'        => __('Select carousel dots style', 'gswps'),
            
            'carousel_navs_position'              => __('Navs Position', 'gswps'),
            'carousel_navs_position__details'     => __('Select carousel navs position', 'gswps'),
            
            'carousel_navs_position'              => __('Navs Position', 'gswps'),
            'carousel_navs_position__details'     => __('Select carousel navs position', 'gswps'),
            
            'gs_slider_dot_color'                 => __('Dots Color', 'gswps'),
            'gs_slider_dot_hover_color'           => __('Dots Active Color', 'gswps'),
            
            'star_rating_color'                   => __('Rating Color', 'gswps'),
            'price_color'                         => __('Price Color', 'gswps'),
            'dl_price_color'                      => __('Old Price Color', 'gswps'),
            'product_title_color'                 => __('Title Color', 'gswps'),
            'product_title_color_hover'           => __('Title Hover Color', 'gswps'),
            'product_des_color'                   => __('Description Color', 'gswps'),
            'product_des_color_hover'             => __('Description Hover Color', 'gswps'),
            'product_tags_color'                  => __('Badges Color', 'gswps'),
            'product_tags_bg_color'               => __('Badges BG Color', 'gswps'),
            'product_dis_tag_bg_color'            => __('Discount Badge BG Color', 'gswps'),
            'product_stockout_tag_bg_color'       => __('Out of Stock Badge BG Color', 'gswps'),

            'btn_background'                      => __('Button Background', 'gswps'),
            'btn_background_hover'                => __('Button Background Hover', 'gswps'),

            'btn_text_color'                      => __('Button Text', 'gswps'),
            'btn_text_hover_color'                => __('Button Text Hover', 'gswps'),

            'wishlist_popup_text'                 => __('Wishlist Popup Text', 'gsteam'),
            'wishlist_popup_text-details'         => __('Change the Wishlist Popup Text', 'gsteam'),

            'sale_badge_text'                     => __('Sale Badge Text', 'gsteam'),
            'sale_badge_text-details'             => __('Change the Sale Badge Text', 'gsteam'),

            'out_of_stock_badge_text'             => __('Out Of Stock Badge Text', 'gsteam'),
            'out_of_stock_badge_text-details'     => __('Change the Out Of Stock Badge Text', 'gsteam'),

            'compare_popup_text'                  => __('Compare Popup Text', 'gsteam'),
            'compare_popup_text-details'          => __('Change the Compare Popup Text', 'gsteam'),

            'quick_view_popup_text'               => __('Quick View Popup Text', 'gsteam'),
            'quick_view_popup_text-details'       => __('Change the Quick View Popup Text', 'gsteam'),

            'disable_lazy_load'                   => __('Disable Lazy Load', 'gsteam'),
            'disable_lazy_load-details'           => __('Disable Lazy Load for the Product images', 'gsteam'),
            'lazy_load_class'                     => __('Lazy Load Class', 'gsteam'),
            'lazy_load_class-details'             => __('Add class to disable lazy loading, multiple classes should be separated by space', 'gsteam'),
            'total-products'                      => __('Display Limit', 'gswps'),
            'total-products--help'                => __('Set number of total products to show. -1 means all products', 'gswps'),
            'image-size--placeholder'             => __('Select Size', 'gswps'),
            'image-size--help'                    => __('Select the attachment size from the registered sources', 'gswps'),
            'include_items_by_cats'               => __('Include Items By Category', 'gswps'),
            'exclude_items_by_cats'               => __('Exclude Items By Category', 'gswps'),
            'include_by_tag'                      => __('Include Items By Tag Name', 'gswps'),
            'exclude_by_tag'                      => __('Exclude Items By Tag Name', 'gswps'),
            'gs_show_title'                       => __('Display Title', 'gswps'),
            'gs_show_title--help'                 => __( 'Show or Hide Product Title', 'gswps' ),
            'show_hide_rating'                    => __( 'Display Ratings', 'gswps' ),
            'show_hide_rating--help'              => __( 'Show or Hide Product Ratings', 'gswps' ),
            'show_count_down_timer'               => __( 'Display Count Down', 'gswps' ),
            'show_count_down_timer--help'         => __( 'Show or Hide Product Count Down Timer. Make sure WPC Countdown Timer plugin is active', 'gswps' ),
            'show_hide_price'                     => __( 'Display Price', 'gswps' ),
            'show_hide_price--help'               => __( 'Show or Hide Product Price', 'gswps' ),
            'show_hide_desc'                      => __( 'Display Description', 'gswps' ),
            'show_hide_desc--help'                => __( 'Show or Hide Product Description', 'gswps' ),

            'is_add_to_wishlist_enabled'          => __( 'Display Wishlist', 'gswps' ),
            'is_add_to_wishlist_enabled--help'    => __( 'Show or Hide the Wishlist button. Make sure YITH WooCommerce Wishlist plugin is activated', 'gswps' ),

            'is_compare_button_enabled'           => __( 'Display Compare', 'gswps' ),
            'is_compare_button_enabled--help'     => __( 'Show or Hide the Compare button. Make sure YITH WooCommerce Compare plugin is activated', 'gswps' ),

            'is_quick_view_enabled'               => __( 'Display Quick View', 'gswps' ),
            'is_quick_view_enabled--help'         => __( 'Show or Hide the Quick View button. Make sure YITH WooCommerce Quick View plugin is activated', 'gswps' ),

            'display_hot_badge'                   => __( 'Display Hot Badge', 'gswps' ),
            'display_hot_badge--help'             => __( 'Show or Hide the Hot badge', 'gswps' ),

            'display_out_of_stock_badge'          => __( 'Display Out Of Stock Badge', 'gswps' ),
            'display_out_of_stock_badge--help'    => __( 'Show or Hide the Out Of Stock badge', 'gswps' ),

            'display_sale_badge'                  => __( 'Display Sale Badge', 'gswps' ),
            'display_sale_badge--help'            => __( 'Show or Hide the Sale badge', 'gswps' ),
            'sale_badge_type'                     => __( 'Sale Badge Type', 'gswps' ),

            'show_hide_add_to_cart'               => __( 'Display Add to Cart', 'gswps' ),
            'show_hide_add_to_cart--help'         => __( 'Show or Hide Product Add to Cart', 'gswps' ),
            'move_slides'                         => __('Move Items', 'gswps'),
            'gs_is_autoplay'                      => __('Autoplay', 'gswps'),
            'gs_is_autoplay--help'                => __('Enable/Disable Auto play to change the slides automatically after certain time. Default On', 'gswps'),
            'gs_autoplay_delay'                   => __('Delay', 'gswps'),
            'gs_autoplay_delay--help'             => __('Set the Autoplay delay in millisecond. Default 2000 ms', 'gswps'),
            'gs_is_loop'                          => __('Infinite Loop', 'gswps'),
            'gs_is_loop--help'                    => __('If ON, clicking on "Next" while on the last slide will start over from first slide and vice-versa', 'gswps'),
            'gs-l-slider-stop'                    => __('Pause on hover', 'gswps'),
            'gs-l-slider-stop--help'              => __('Autoplay will pause when mouse hovers over woo. Default On', 'gswps'),
            'gs-reverse-direction'                => __('Reverse Direction', 'gswps'),
            'gs-reverse-direction--help'          => __('Reverse the direction of movement. Default Off', 'gswps'),
            'gs-l-stp-tkr'                        => __('Pause on Hover', 'gswps'),
            'gs-l-stp-tkr--help'                  => __('Ticker will pause when mouse hovers over slider. Note: this functionality does NOT work if using CSS transitions!. Default Off', 'gswps'),
            'gs-l-ctrl'                           => __('Slider Navs', 'gswps'),
            'gs-l-ctrl--help'                     => __('Next / Previous control for carousel. Default On Controls are not available when Ticker Mode is enabled', 'gswps'),
            'gs-l-ctrl-pos'                       => __('Navs Position', 'gswps'),
            'gs-l-ctrl-pos--placeholder'          => __('Navs Position', 'gswps'),
            'gs-l-ctrl-pos--help'                 => __('Position of Next / Previous control for carousel. Default Bottom', 'gswps'),
            'gs_carousel_speed'                   => __('Slides Speed', 'gswps'),
            'gs_carousel_speed--help'             => __('Duration of transition between slides (in ms)', 'gswps'),
            'gs_ticker_speed'                     => __('Ticker Speed', 'gswps'),
            'gs-l-dot-for-each'                   => __('Dot for Each', 'gswps'),
            'gs-l-dot-for-each-nav--help'         => __('Dot Navigation Help', 'gswps'),
            'gs-l-pagi'                           => __('Slider Dots', 'gswps'),
            'gs-l-pagi--help'                     => __('Dots control for carousel below the widget. Default Off', 'gswps'),
            'gs-l-pagi-dynamic'                   => __('Dynamic Dots', 'gswps'),
            'gs-l-pagi-dynamic--help'             => __('Good to enable if you use for many slides. So it will keep only few dots visible at the same time. Default On', 'gswps'),
            'gs_stop_on_hover'                    => __('Play Pause', 'gswps'),
            'gs_stop_on_hover'                    => __('Stop On Hover', 'gswps'),
            'gs_stop_on_hover--help'              => __('Play Pause control bellow the carousel. Default Off', 'gswps'),
            'gs-l-title'                          => __('woo Title', 'gswps'),
            'gs-l-title--help'                    => __('Display woo including / excluding Title. Default Off', 'gswps'),
            'show-cat'                            => __('woo Category', 'gswps'),
            'show-cat--help'                      => __('Display woo including / excluding Category. Default Off', 'gswps'),
            'gs-l-tooltip'                        => __('Tooltip', 'gswps'),
            'gs-l-tooltip--help'                  => __('Enable / disable Tooltip option.', 'gswps'),
            'gs-l-gray'                           => __('woos style', 'gswps'),
            'gs-l-gray--help'                     => __('woo grayscale feature works only in modern browsers.. like Chrome, Firefox and Safari', 'gswps'),
            'gs_gutter'                           => __('Columns Gap', 'gswps'),
            'gs_gutter--help'                     => __('Increase / decrease the gaps between each item. Default 30.', 'gswps'),
            'gs-l-min-woo'                        => __('Desktop woos', 'gswps'),
            'gs-l-min-woo--help'                  => __('The minimum number of woos to be shown. Default 5, max 10. (Theme : Slider1,Fullwith slider,Center Mode, Ticker)', 'gswps'),
            'gs-l-tab-woo'                        => __('Tablet woos', 'gswps'),
            'gs-l-tab-woo--help'                  => __('The minimum number of woos to be shown. Default 3, max 10. (Theme : Slider1,Fullwith slider,Center Mode,2 Rows Slider, Ticker)', 'gswps'),
            'gs-l-mob-woo'                        => __('Mobile woos', 'gswps'),
            'gs-l-mob-woo--help'                  => __('The minimum number of woos to be shown. Default 2, max 10. (Theme : Slider1,Fullwith slider,Center Mode,2 Rows Slider, Ticker)', 'gswps'),
            'gs-l-move-woo'                       => __('Move woos', 'gswps'),
            'gs-l-move-woo--help'                 => __('The number of woos to move on transition. Default 1, max 10.', 'gswps'),
            'gs-woo-filter-name'                  => __('All Filter Name', 'gswps'),
            'gs-woo-filter-name--placeholder'     => __('All', 'gswps'),
            'gs-woo-filter-name--help'            => __('Replace preferred text instead of "All" for Filter Theme.', 'gswps'),
            'gs-woo-filter-align'                 => __('Filter Name Align', 'gswps'),
            'gs-woo-filter-align--placeholder'    => __('Filters Align', 'gswps'),
            'gs-woo-filter-align--help'           => __('Filter Categories alignment for Filter Theme.', 'gswps'),
            'gs-l-clkable'                        => __('Clickable woos', 'gswps'),
            'gs-l-clkable--help'                  => __('Specify target to open the Links, Default New Tab', 'gswps'),
            'order'                               => __('Order', 'gswps'),
            'image_type'                          => __('Image Type', 'gswps'),
            'image_type--help'                    => __('Image Type', 'gswps'),
            'order--placeholder'                  => __('Order', 'gswps'),
            'order-by'                            => __('Order By', 'gswps'),
            'order-by--placeholder'               => __('Order By', 'gswps'),
            'woo-cat'                             => __('Categories', 'gswps'),
            'woo-cat--placeholder'                => __('Categories', 'gswps'),
            'woo-cat--help'                       => __('Select specific woo category to show that specific category woos', 'gswps'),
            'install-demo-data'                   => __('Install Pre-Build Shortcodes', 'gswps'),
            'install-demo-data-description'       => __('Quick start with Woo Product Views by installing the pre-build shortcodes', 'gswps'),
            'preference'                          => __('Preference', 'gswps'),
            'save-preference'                     => __('Save Preference', 'gswps'),
            
            'config-layouts'                      => __('Config Layouts'),
            'config-layouts--sub-heading'         => __('Replace Product page layouts with shortcodes'),
            'save-layout-config'                  => __('Save Settings', 'gswps'),

            'post_type_product'                   => __('Shop/Products Page', 'gswps'),
            'tax_product_cat'                     => __('Product Category Page', 'gswps'),
            'tax_product_tag'                     => __('Product Tag Page', 'gswps'),
            'product_search'                      => __('Product Search Page', 'gswps'),

            'product_select_shortcode'            => __('Select Shortcode', 'gswps'),
            'products_replace_type'               => __('Way To Retrieve Product', 'gswps'),

            'custom-css'                          => __('Custom CSS', 'gswps'),
            'shortcodes'                          => __('Shortcodes', 'gswps'),
            'global-settings-for-gs-woo-slider'   => __('Global Settings for Woo Product Views', 'gswps'),
            'all-shortcodes-for-gs-woo-slider'    => __('All shortcodes for Woo Product Views', 'gswps'),
            'create-shortcode'                    => __('Create Shortcode', 'gswps'),
            'create-new-shortcode'                => __('Create New Shortcode', 'gswps'),
            'shortcode'                           => __('Shortcode', 'gswps'),
            'name'                                => __('Name', 'gswps'),
            'action'                              => __('Action', 'gswps'),
            'actions'                             => __('Actions', 'gswps'),
            'edit'                                => __('Edit', 'gswps'),
            'clone'                               => __('Clone', 'gswps'),
            'delete'                              => __('Delete', 'gswps'),
            'delete-all'                          => __('Delete All', 'gswps'),
            'create-a-new-shortcode-and'          => __('Create a new shortcode & save it to use globally in anywhere', 'gswps'),
            'edit-shortcode'                      => __('Edit Shortcode', 'gswps'),
            'general-settings'                    => __('General', 'gswps'),
            'style-settings'                      => __('Style', 'gswps'),
            'query-settings'                      => __('Query', 'gswps'),
            'shortcode-name'                      => __('Shortcode Name', 'gswps'),
            'name-of-the-shortcode'               => __('Shortcode Name', 'gswps'),
            'save-shortcode'                      => __('Save Shortcode', 'gswps'),
            'preview-shortcode'                   => __('Preview Shortcode', 'gswps')
        ];
    }

    public function get_columns() {

        $columns = [
            [
                'label' => __( '1 Column', 'gswps' ),
                'value' => '12'
            ],
            [
                'label' => __( '2 Columns', 'gswps' ),
                'value' => '6'
            ],
            [
                'label' => __( '3 Columns', 'gswps' ),
                'value' => '4'
            ],
            [
                'label' => __( '4 Columns', 'gswps' ),
                'value' => '3'
            ],
            [
                'label' => __( '5 Columns', 'gswps' ),
                'value' => '2_4'
            ],
            [
                'label' => __( '6 Columns', 'gswps' ),
                'value' => '2'
            ],
        ];

        return $columns;
    }

    public function move_slides() {

        $items = [
            [
                'label' => __( 'Default', 'gswps' ),
                'value' => 'default'
            ],
            [
                'label' => __( '1 Item', 'gswps' ),
                'value' => '1'
            ],
            [
                'label' => __( '2 Items', 'gswps' ),
                'value' => '2'
            ],
            [
                'label' => __( '3 Items', 'gswps' ),
                'value' => '3'
            ],
            [
                'label' => __( '4 Items', 'gswps' ),
                'value' => '4'
            ],
            [
                'label' => __( '5 Items', 'gswps' ),
                'value' => '5'
            ],
            [
                'label' => __( '6 Items', 'gswps' ),
                'value' => '6'
            ],
        ];

        return $items;
    }

    public function get_themes() {
        $themes = [
            [
                'label' => __('Effect( Lite 1 )', 'gswoo'),
                'value' => 'theme1'
            ],
            [
                'label' => __('Effect( Lite 2 )', 'gswoo'),
                'value' => 'theme2'
            ],
            [
                'label' => __('Effect( Lite 3 )', 'gswoo'),
                'value' => 'theme3'
            ],
            [
                'label' => __('Effect( Lite 4 )', 'gswoo'),
                'value' => 'theme4'
            ]
        ];

        return $themes;
    }

    public function get_shortcode_options_image_sizes() {

        $sizes = get_intermediate_image_sizes();

        if (empty($sizes)) return [];

        return array_map(function ($size) {
            $label = preg_replace('/_|-/', ' ', $size);
            return [
                'label' => ucwords($label),
                'value' => $size
            ];
        }, $sizes);
    }

    public static function get_free_templates() {

        return [
            [
                'label' => __('Style 01', 'gswps'),
                'value' => 'gs-wc-style-1'
            ],
            [
                'label' => __('Style 02', 'gswps'),
                'value' => 'gs-wc-style-2'
            ],
            [
                'label' => __('Style 03', 'gswps'),
                'value' => 'gs-wc-style-3'
            ],
            [
                'label' => __('Style 04', 'gswps'),
                'value' => 'gs-wc-style-4'
            ],
            [
                'label' => __('Style 05', 'gswps'),
                'value' => 'gs-wc-style-5'
            ]
        ];
    }

    public static function get_pro_templates() {

        return [
            [
                'label' => __('Style 06', 'gswps'),
                'value' => 'gs-wc-style-6'
            ],
            [
                'label' => __('Style 07', 'gswps'),
                'value' => 'gs-wc-style-7'
            ],
            [
                'label' => __('Style 08', 'gswps'),
                'value' => 'gs-wc-style-8'
            ],
            [
                'label' => __('Style 09', 'gswps'),
                'value' => 'gs-wc-style-9'
            ],
            [
                'label' => __('Style 10', 'gswps'),
                'value' => 'gs-wc-style-10'
            ],
            [
                'label' => __('Style 11', 'gswps'),
                'value' => 'gs-wc-style-11'
            ],
            [
                'label' => __('Style 12', 'gswps'),
                'value' => 'gs-wc-style-12'
            ],
            [
                'label' => __('Style 13', 'gswps'),
                'value' => 'gs-wc-style-13'
            ],
            [
                'label' => __('Style 14', 'gswps'),
                'value' => 'gs-wc-style-14'
            ],
            [
                'label' => __('Style 15', 'gswps'),
                'value' => 'gs-wc-style-15'
            ],
            [
                'label' => __('Style 16', 'gswps'),
                'value' => 'gs-wc-style-16'
            ],
            [
                'label' => __('Style 17', 'gswps'),
                'value' => 'gs-wc-style-17'
            ],
            [
                'label' => __('Style 18', 'gswps'),
                'value' => 'gs-wc-style-18'
            ],
            [
                'label' => __('Style 19', 'gswps'),
                'value' => 'gs-wc-style-19'
            ],
            [
                'label' => __('Style 20', 'gswps'),
                'value' => 'gs-wc-style-20'
            ],
            [
                'label' => __('Style 21', 'gswps'),
                'value' => 'gs-wc-style-21'
            ],
            [
                'label' => __('Style 22', 'gswps'),
                'value' => 'gs-wc-style-22'
            ],
            [
                'label' => __('Style 23', 'gswps'),
                'value' => 'gs-wc-style-23'
            ],
            [
                'label' => __('Style 24', 'gswps'),
                'value' => 'gs-wc-style-24'
            ],
            [
                'label' => __('Style 25', 'gswps'),
                'value' => 'gs-wc-style-25'
            ],
            [
                'label' => __('Style 26', 'gswps'),
                'value' => 'gs-wc-style-26'
            ],
            [
                'label' => __('Style 27', 'gswps'),
                'value' => 'gs-wc-style-27'
            ],
            [
                'label' => __('Style 28', 'gswps'),
                'value' => 'gs-wc-style-28'
            ],
            [
                'label' => __('Style 29', 'gswps'),
                'value' => 'gs-wc-style-29'
            ],
            [
                'label' => __('Style 30', 'gswps'),
                'value' => 'gs-wc-style-30'
            ],
            [
                'label' => __('Style 31', 'gswps'),
                'value' => 'gs-wc-style-31'
            ],
            [
                'label' => __('Style 32', 'gswps'),
                'value' => 'gs-wc-style-32'
            ],
            [
                'label' => __('Style List 01', 'gswps'),
                'value' => 'gs-wc-style-list-01'
            ],
            [
                'label' => __('Style List 02', 'gswps'),
                'value' => 'gs-wc-style-list-02'
            ],
            [
                'label' => __('Style List 03', 'gswps'),
                'value' => 'gs-wc-style-list-03'
            ],
            [
                'label' => __('Style List 04', 'gswps'),
                'value' => 'gs-wc-style-list-04'
            ],
            [
                'label' => __('Style List 05', 'gswps'),
                'value' => 'gs-wc-style-list-05'
            ]
        ];
    }

    public function get_shortcode_templates() {
        $free_themes = self::get_free_templates();
        $pro_themes  = self::get_pro_templates();

        if ( ! is_gswps_pro() || ! gswps_pro_is_valid() ) {
            $pro_themes = array_map(function ($item) {
                $item['label'] = $item['label'] . ' (Pro)';
                $item['pro'] = true;
                return $item;
            }, $pro_themes);
        } else {
            $themes = array_merge( $free_themes, $pro_themes );
            array_multisort( array_column($themes, 'label'), SORT_STRING, $themes );
            return $themes;
        }

        return array_merge( $free_themes, $pro_themes );
    }

    public function get_theme_styles() {        

        $free_themes = [
            [
                'label' => __('Carousel', 'gswps'),
                'value' => 'carousel'
            ],
            [
                'label' => __('Grid', 'gswps'),
                'value' => 'grid'
            ]
        ];

        $pro_themes = [
            [
                'label' => __('Masonry', 'gswps'),
                'value' => 'masonry'
            ],
            [
                'label' => __('Ticker', 'gswps'),
                'value' => 'ticker'
            ]
        ];

        return disable_pro_items( $free_themes, $pro_themes );
    }

    public static function get_free_prod() {
        return [
            [
                'label' => __('Featured', 'gswps'),
                'value' => 'gs-featured'
            ]
        ];
    }

    public static function get_pro_prod() {
        return [
            [
                'label' => __('In stock', 'gswps'),
                'value' => 'in-stock'
            ],
            [
                'label' => __('Free', 'gswps'),
                'value' => 'gs-free'
            ]
        ];
    }

    public static function get_sale_badge_types() {
        return [
            [
                'label' => __('Text', 'gswps'),
                'value' => 'text'
            ],
            [
                'label' => __('Numeric', 'gswps'),
                'value' => 'numeric'
            ]
        ];
    }

    public function get_carousel_nav_styles( $free_only = false ) {

        $styles = [
            [
                'label' => __('Default', 'gswps'),
                'value' => 'default'
            ],
            [
                'label' => __('Style One', 'gswps'),
                'value' => 'style-one'
            ],
            [
                'label' => __('Style Two', 'gswps'),
                'value' => 'style-two'
            ],
            [
                'label' => __('Style Three', 'gswps'),
                'value' => 'style-three'
            ]
        ];

        if ( $free_only ) return $styles;

        $styles_pro = [
            [
                'label' => __('Style Four', 'gswps'),
                'value' => 'style-four'
            ],
            [
                'label' => __('Style Five', 'gswps'),
                'value' => 'style-five'
            ],
            [
                'label' => __('Style Six', 'gswps'),
                'value' => 'style-six'
            ],
            [
                'label' => __('Style Seven', 'gswps'),
                'value' => 'style-seven'
            ],
            [
                'label' => __('Style Eight', 'gswps'),
                'value' => 'style-eight'
            ],
            [
                'label' => __('Style Nine', 'gswps'),
                'value' => 'style-nine'
            ],
            [
                'label' => __('Style Ten', 'gswps'),
                'value' => 'style-ten'
            ],
            [
                'label' => __('Style Eleven', 'gswps'),
                'value' => 'style-eleven'
            ]
        ];

        return disable_pro_items( $styles, $styles_pro );
    }

    public function get_carousel_dot_styles( $free_only = false ) {

        $styles = [
            [
                'label' => __('Default', 'gswps'),
                'value' => 'default'
            ],
            [
                'label' => __('Style One', 'gswps'),
                'value' => 'style-one'
            ],
            [
                'label' => __('Style Two', 'gswps'),
                'value' => 'style-two'
            ]

        ];

        if ( $free_only ) return $styles;

        $styles_pro = [
            [
                'label' => __('Style Three', 'gswps'),
                'value' => 'style-three'
            ],
            [
                'label' => __('Style Four', 'gswps'),
                'value' => 'style-four'
            ]

        ];

        return disable_pro_items( $styles, $styles_pro );
    }

    public function get_carousel_nav_positions() {

        $styles = [
            [
                'label' => __('Bottom', 'gswps'),
                'value' => 'bottom'
            ],
            [
                'label' => __('Center', 'gswps'),
                'value' => 'center'
            ],
            [
                'label' => __('Center Outside', 'gswps'),
                'value' => 'center-outside'
            ],
            [
                'label' => __('Center Inside', 'gswps'),
                'value' => 'center-inside'
            ],
            [
                'label' => __('Top Right', 'gswps'),
                'value' => 'top-right'
            ],
            [
                'label' => __('Top Left', 'gswps'),
                'value' => 'top-left'
            ]

        ];

        return $styles;
    }

    public function get_product_type() {

        $free_items = self::get_free_prod();
        $pro_items  = self::get_pro_prod();

        return disable_pro_items( $free_items, $pro_items );
    }

    public function get_order_by_items() {

        $free_items = [
            [
                'label' => __('Default', 'gswps'),
                'value' => 'menu_order'
            ],
            [
                'label' => __('Product ID', 'gswps'),
                'value' => 'ID'
            ],
            [
                'label' => __('Product Name', 'gswps'),
                'value' => 'title'
            ],
            [
                'label' => __('Latest', 'gswps'),
                'value' => 'date'
            ],
            [
                'label' => __('Random', 'gswps'),
                'value' => 'rand'
            ],
        ];

        $pro_items = [
            [
                'label' => __('Best Sellers / Popularity', 'gswps'),
                'value' => 'popularity'
            ],
            [
                'label' => __('Average Rating', 'gswps'),
                'value' => 'rating'
            ],
            [
                'label' => __('Price', 'gswps'),
                'value' => 'price'
            ]
        ];

        return disable_pro_items( $free_items, $pro_items );
    }

    public function get_shortcode_default_options() {

        return [

            'columns'                 => $this->get_columns(),
            'columns_tablet'          => $this->get_columns(),
            'columns_mobile_portrait' => $this->get_columns(),
            'columns_mobile'          => $this->get_columns(),
            'move_slides'         => $this->move_slides(),
            'gs_woo_cat_type'         =>  get_gs_woo_terms('product_cat'),
            'exclude_cats'            =>  get_gs_woo_terms('product_cat'),
            'select_cats'             =>  get_gs_woo_terms('product_cat'),
            'gs_woo_product_type'     => $this->get_product_type(),
            'gs_woo_ex_product_type'  => $this->get_product_type(),
            'gs_woo_templates'        => $this->get_shortcode_templates(),
            'view_types'              => $this->get_theme_styles(),
            'image_size'              => $this->get_shortcode_options_image_sizes(),
            'sale_badge_types'        => $this->get_sale_badge_types(),
            'products'                => gs_product_select('name'),
            'select_by_tag'           => gs_product_tags(),
            'deselect_by_tag'         => gs_product_tags(),

            'carousel_navs_style'       => $this->get_carousel_nav_styles(),
            'carousel_dots_style'       => $this->get_carousel_dot_styles(),
            'carousel_navs_position'    => $this->get_carousel_nav_positions(),
            
            'image_types' => [
                [
                    'label' => __('Solid', 'gswps'),
                    'value' => 'solid',
                ],
                [
                    'label' => __('Transparent', 'gswps'),
                    'value' => 'transparent',
                ],
            ],

            'orderby' => $this->get_order_by_items(),

            'order' => [
                [
                    'label' => __('DESC', 'gswps'),
                    'value' => 'DESC'
                ],
                [
                    'label' => __('ASC', 'gswps'),
                    'value' => 'ASC'
                ],
            ]

        ];
    }

    public function get_shortcode_default_settings() {
        return [
            'gs_show_title'                 => true,
            'show_hide_rating'              => true,
            'show_count_down_timer'         => true,
            'show_hide_price'               => true,
            'show_hide_add_to_cart'         => true,
            'show_hide_desc'                => true,
            'gs_is_autoplay'                => false,
            'gs_is_loop'                    => true,
            'gs_stop_on_hover'              => true,
            'reverse_direction'             => false,

            // Floating Buttons
            'is_add_to_wishlist_enabled'    => true,
            'is_compare_button_enabled'     => true,
            'is_quick_view_enabled'         => true,
            'display_hot_badge'             => true,
            'display_out_of_stock_badge'    => true,
            'display_sale_badge'            => true,
            'sale_badge_type'               => 'text',

            // Dropdown
            'columns'                       => '3',
            'columns_tablet'                => '4',
            'columns_mobile_portrait'       => '6',
            'columns_mobile'                => '12',
            'move_slides'                   => 'default',
            'image_size'                    => 'medium',
            'order'                         => 'ASC',
            'orderby'                       => get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ),
            'gs_woo_template'               => 'gs-wc-style-1',
            'view_type'                     => 'carousel',
            'image_type'                    => 'solid',

            // Number
            'gs_l_products'                 => 16,
            'gs_gutter'                     => 30,
            'gs_carousel_speed'             => 2500,
            'gs_ticker_speed'               => 2500,
            'gs_excerpt_length'             => 100,
            'reverse_direction'             => false,

            // Range
            'gs_autoplay_delay'             => 2000,

            'carousel_navs_enabled'         => true,
            'carousel_dots_enabled'         => true,
            'dynamic_dots_enabled'          => true,
            'carousel_navs_style'           => 'default',
            'carousel_dots_style'           => 'default',
            'carousel_navs_position'        => 'bottom',
            'gs_slider_nav_color'           => '',
            'gs_slider_nav_bg_color'        => '',
            'gs_slider_nav_hover_color'     => '',
            'gs_slider_nav_hover_bg_color'  => '',
            'gs_slider_dot_color'           => '',
            'gs_slider_dot_hover_color'     => '',

            // Colors
            'star_rating_color'             => '',

            'price_color'                   => '',
            'dl_price_color'                => '',
            
            'product_title_color'           => '',
            'product_title_color_hover'     => '',

            'product_des_color'             => '',
            'product_des_color_hover'       => '',

            'product_tags_color'            => '',
            'product_tags_bg_color'         => '',
            'product_dis_tag_bg_color'      => '',
            'product_stockout_tag_bg_color' => '',

            'btn_bg_hover'                  => '',
            'btn_background'                => '',
            'btn_text_color'                => '',
            'btn_text_hover_color'          => '',

            'select_cats'                   => [],
            'exclude_cats'                  => [],
            'select_by_tag'                 => [],
            'deselect_by_tag'               => [],
            'select_by_name'                => [],
            'deselect_by_name'              => [],
            'gs_woo_product_type'           => [],
            'gs_woo_ex_product_type'        => []
        ];
    }

    public function get_shortcode_default_prefs() {
        return [
            'gs_woops_slider_custom_css'  => '',
            'disable_lazy_load'           => false,
            'lazy_load_class'             => 'skip-lazy',
            'sale_badge_text'             => 'Sale',
            'out_of_stock_badge_text'     => 'Out Of Stock',
            'wishlist_popup_text'         => 'Wishlist',
            'compare_popup_text'          => 'Compare',
            'quick_view_popup_text'       => 'Quick View'
        ];
    }

    public function get_shortcode_prefs_options() {
        return [];
    }

    public function get_default_layout_config() {
        return [
            'post_type_product'  => true,
            'post_type_product_shortcode'  => '',
            'post_type_product_replace_type'  => 'no_change',

            'tax_product_cat'  => false,
            'tax_product_cat_shortcode'  => '',
            'tax_product_cat_replace_type'  => 'no_change',

            'tax_product_tag'  => false,
            'tax_product_tag_shortcode'  => '',
            'tax_product_tag_replace_type'  => 'no_change',

            'product_search'  => false,
            'product_search_shortcode'  => '',
            'product_search_replace_type'  => 'no_change',
        ];
    }

    public function get_shortcode_options() {

        $shortcodes = $this->_get_shortcodes( [], false, true );

        $_shortcodes = [];

        foreach ( $shortcodes as $shortcode ) {
            $_shortcodes[] = [
                'label' => $shortcode['shortcode_name'],
                'value' => $shortcode['id']
            ];
        }

        return $_shortcodes;
    }

    public function get_layout_config_options() {
        return [
            'shortcodes' => $this->get_shortcode_options(),
            'replace_types' => [
                [
                    'label' => __( 'No Change', 'gswps' ),
                    'value' => 'no_change'
                ],
                [
                    'label' => __( 'Change completely (use all options of the shortcode)', 'gswps' ),
                    'value' => 'change_all'
                ]
            ]
        ];
    }

    public function validate_shortcode_pref( $settings ) {

        foreach ( $settings as $key => $value ) {
            switch ( $key ) {

                // Validate Boolean
                case 'disable_lazy_load' : {
                    $settings[ $key ] = wp_validate_boolean( $value );
                    break;
                }

                // Validate Key
                case 'gs_woops_slider_custom_css' : {
                    $settings[ $key ] = wp_strip_all_tags( $value );
                    break;
                }

                // Validate Text Field
                default : {
                    $settings[ $key ] = sanitize_text_field( $value );
                }

            }
        }

        return $settings;
    }

    public function _save_shortcode_pref($nonce, $settings, $is_ajax) {

        if (!wp_verify_nonce($nonce, '_gswps_save_shortcode_pref_gs_')) {
            if ($is_ajax) wp_send_json_error(__('Unauthorised Request', 'gswps'), 401);
            return false;
        }

        $settings = $this->validate_shortcode_pref( $settings );

        update_option($this->option_name, $settings, 'yes');

        // Clean permalink flush
        delete_option('GS_woo_Slider_plugin_permalinks_flushed');

        do_action('gs_woo_preference_update');

        do_action('gsp_preference_update');

        if ( $is_ajax ) wp_send_json_success( __('Preference saved', 'gswps') );
    }

    public function save_shortcode_pref() {

        if ( empty($_POST['_wpnonce']) ) {
            wp_send_json_error( __('No nonce provided', 'gswps'), 400 );
        }

        if ( empty($_POST['prefs']) ) {
            wp_send_json_error( __('No preference provided', 'gswps'), 400 );
        }

        $this->_save_shortcode_pref( $_POST['_wpnonce'], $_POST['prefs'], true );
    }

    public function validate_layout_config( $settings ) {

        foreach ( $settings as $key => $value ) {
            switch ( $key ) {

                // Validate Boolean
                case 'post_type_product' :
                case 'tax_product_cat' :
                case 'tax_product_tag' :
                case 'product_search' : {
                    $settings[ $key ] = wp_validate_boolean( $value );
                    break;
                }

                // Validate Key
                case 'post_type_product_replace_type' :
                case 'tax_product_cat_replace_type' :
                case 'tax_product_tag_replace_type' :
                case 'product_search_replace_type' : {
                    $settings[ $key ] = sanitize_key( $value );
                    break;
                }

                // Validate Text Field
                default : {
                    $settings[ $key ] = sanitize_text_field( $value );
                }

            }
        }

        return $settings;
    }
    
    public function _save_layout_config($nonce, $settings, $is_ajax) {

        if (!wp_verify_nonce($nonce, '_gswps_save_shortcode_pref_gs_')) {
            if ($is_ajax) wp_send_json_error(__('Unauthorised Request', 'gswps'), 401);
            return false;
        }

        $settings = $this->validate_layout_config( $settings );

        update_option( $this->layout_config_option_name, $settings, 'yes' );
        
        do_action('gs_woo_preference_update');

        do_action('gsp_preference_update');

        if ( $is_ajax ) wp_send_json_success( __('Config saved', 'gswps') );
    }

    public function save_layout_config() {

        if ( empty($_POST['_wpnonce']) ) {
            wp_send_json_error( __('No nonce provided', 'gswps'), 400 );
        }

        if ( empty($_POST['config']) ) {
            wp_send_json_error( __('No config provided', 'gswps'), 400 );
        }

        $this->_save_layout_config( $_POST['_wpnonce'], $_POST['config'], true );
    }

    public function _get_shortcode_pref( $is_ajax ) {

        $default    = $this->get_shortcode_default_prefs();
        $pref       = get_option( $this->option_name );
        $pref       = shortcode_atts( $default, $pref );

        if ( $is_ajax ) {
            wp_send_json_success($pref);
        }

        return $pref;
    }

    public function get_shortcode_pref() {
        return $this->_get_shortcode_pref( wp_doing_ajax() );
    }

    public function _get_layout_config( $is_ajax ) {

        $default    = $this->get_default_layout_config();
        $config       = get_option( $this->layout_config_option_name );
        $config       = shortcode_atts( $default, $config );

        if ( $is_ajax ) {
            wp_send_json_success($config);
        }

        return $config;
    }

    public function get_layout_config() {
        return $this->_get_layout_config( wp_doing_ajax() );
    }

    static function maybe_create_shortcodes_table() {

        global $wpdb;

        $gs_woo_slider_db_version = '1.0';

        $saved_db_version = get_option("{$wpdb->prefix}gs_woo_slider_db_version");

        if ( $saved_db_version == $gs_woo_slider_db_version ) return; // vail early

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gs_woo_slider (
            id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
            shortcode_name TEXT NOT NULL,
            shortcode_settings LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (id)
        )" . $wpdb->get_charset_collate() . ";";

        if ( $saved_db_version < $gs_woo_slider_db_version ) {
            dbDelta($sql);
        }

        update_option("{$wpdb->prefix}gs_woo_slider_db_version", $gs_woo_slider_db_version);

        if ( $saved_db_version === false ) {
            update_option( 'gswps_install_demo_shortcodes_initially', true );
        }
    }

    public function create_dummy_shortcodes() {

        $request = wp_remote_get( GSWPS_FILES_URI . '/includes/demo-data/shortcodes.json', array('sslverify' => false) );

        if (is_wp_error($request)) return false;

        $shortcodes = wp_remote_retrieve_body($request);
        $shortcodes = json_decode($shortcodes, true);

        $wpdb = $this->gswoo_get_wpdb();

        if ( !$shortcodes || !count($shortcodes) ) return;

        foreach ($shortcodes as $shortcode) {

            $shortcode['shortcode_settings'] = json_decode($shortcode['shortcode_settings'], true);
            $shortcode['shortcode_settings']['gswoo-demo_data'] = true;

            $data = array(
                "shortcode_name"     => $shortcode['shortcode_name'],
                "shortcode_settings" => json_encode($shortcode['shortcode_settings']),
                "created_at"         => current_time('mysql'),
                "updated_at"         => current_time('mysql'),
            );

            $wpdb->insert("{$wpdb->prefix}gs_woo_slider", $data, $this->get_gswoo_shortcode_db_columns());
        }

        wp_cache_delete('gs_woo_shortcodes');
    }

    public function delete_dummy_shortcodes() {

        $wpdb = $this->gswoo_get_wpdb();

        $needle = 'gswoo-demo_data';

        $wpdb->query("DELETE FROM {$wpdb->prefix}gs_woo_slider WHERE shortcode_settings like '%$needle%'");

        wp_cache_delete('gs_woo_shortcodes');
    }
}
