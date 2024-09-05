<?php
namespace GSWPS;

class Hooks {

    public function __construct() {

        add_action( 'plugins_loaded', [$this, 'gs_woo_plugin_loaded'] );
        add_action( 'init', [ $this, 'i18n'] );
        add_action( 'admin_init', [$this, 'review_notice'] );
        add_action( 'admin_init', [$this, 'gswps_nag_ignore'] );
        add_filter( 'manage_edit-gs_wps_cpt_columns', [$this, 'gs_add_new_shortcode_columns'] );
        add_action( 'manage_gs_wps_cpt_posts_custom_column', [$this, 'gs_manage_shortcode_columns'], 20, 3 );
        add_filter( 'plugin_row_meta', [$this, 'gswps_row_meta'], 10, 2 );
        add_action( 'admin_init', [$this, 'gsadmin_signup_notice'] );
        add_action( 'in_admin_header', [ $this, 'disable_admin_notices' ], PHP_INT_MAX );
        // add_action( 'admin_notices',  [ $this, 'gsvariation_wc_requirement_notice' ] );

        add_action( 'woocommerce_product_options_general_product_data', [ $this, 'gswps_add_badge_field' ] );
        add_action( 'woocommerce_product_options_inventory_product_data', [ $this, 'gswps_add_initial_stock_field' ] );

        add_action( 'woocommerce_process_product_meta', [ $this, 'gswps_save_custom_data_field' ] );

    }

    /**
     * Display the badge field
     */
    function gswps_add_badge_field() {
        $args = array(
            'id' => 'product_badge',
            'label' => __( 'Product Badge', 'gswps' ),
            'class' => 'gswps-product-badge',
            'desc_tip' => true,
            'description' => __( 'Enter the badge text to display in the product.', 'gswps' ),
        );
        woocommerce_wp_text_input( $args );
    }

    /**
     * Display the initial stock field
     */
    function gswps_add_initial_stock_field() {
        $args = array(
            'id' => 'initial_stock',
            'label' => __( 'Initial Quantity', 'gswps' ),
            'class' => 'gswps-product-initial-stock',
            'desc_tip' => true,
            'description' => __( 'Enter the initial stock quantity to display in the sold bar of product.', 'gswps' ),
        );
        woocommerce_wp_text_input( $args );
    }

    /**
     * Save the badge field
     */
    function gswps_save_custom_data_field( $post_id ) {
        $product = wc_get_product( $post_id );

        // Product Badge
        $badge = isset( $_POST['product_badge'] ) ? $_POST['product_badge'] : '';
        $product->update_meta_data( 'product_badge', sanitize_text_field( $badge ) );
        
        // Product Badge
        $badge = isset( $_POST['initial_stock'] ) ? $_POST['initial_stock'] : 0;
        $product->update_meta_data( 'initial_stock', intval( $badge ) );

        $product->save();
    }

    // Plugin On Loaded
    public function gs_woo_plugin_loaded() {
        gs_woo_plugin_update_version();
        Builder::maybe_create_shortcodes_table();
    }

    public function review_notice() {

        $this->review_dismiss();
        $this->review_pending();

        $activation_time    = get_site_option('GSWPS_active_time');
        $review_dismissal   = get_site_option('review_dismiss');
        $maybe_later        = get_site_option('GSWPS_maybe_later');

        if ('yes' == $review_dismissal) {
            return;
        }

        if (!$activation_time) {
            add_site_option('GSWPS_active_time', time());
        }

        $daysinseconds = 259200; // 3 Days in seconds.

        if ('yes' == $maybe_later) {
            $daysinseconds = 604800; // 7 Days in seconds.
        }

        if (time() - $activation_time > $daysinseconds) {
            add_action('admin_notices', [ $this, 'review_notice_message' ]);
        }
    }

    /**
     * For the notice preview.
     */
    function review_notice_message() {
        $scheme      = (parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY)) ? '&' : '?';
        $url         = $_SERVER['REQUEST_URI'] . $scheme . 'review_dismiss=yes';
        $dismiss_url = wp_nonce_url($url, 'GSWPS-review-nonce');
        $_later_link = $_SERVER['REQUEST_URI'] . $scheme . 'GSWPS_review_later=yes';
        $later_url   = wp_nonce_url($_later_link, 'GSWPS-review-nonce');
?>

        <div class="gswps-review-notice">
            <div class="gswps-review-thumbnail">
                <img src="<?php echo plugins_url('gs-woocommerce-products-slider/assets/img/icon-128x128.png') ?>" alt="">
            </div>
            <div class="gswps-review-text">
                <h3><?php _e('Leave A Review?', 'gswps') ?></h3>
                <p><?php _e('We hope you\'ve enjoyed using <b>GS Products Slider for WooCommerce Lite</b>! Would you consider leaving us a review on WordPress.org?', 'gswps') ?></p>
                <ul class="gswps-review-ul">
                    <li>
                        <a href="https://wordpress.org/support/plugin/gs-woocommerce-products-slider/reviews" target="_blank">
                            <span class="dashicons dashicons-external"></span>
                            <?php _e('Sure! I\'d love to!', 'gswps') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url($dismiss_url); ?>">
                            <span class="dashicons dashicons-smiley"></span>
                            <?php _e('I\'ve already left a review', 'gswps') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url($later_url); ?>">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php _e('Maybe Later', 'gswps') ?>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.gsplugins.com/contact/" target="_blank">
                            <span class="dashicons dashicons-sos"></span>
                            <?php _e('I need help!', 'gswps') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url($dismiss_url); ?>">
                            <span class="dashicons dashicons-dismiss"></span>
                            <?php _e('Never show again', 'gswps') ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <style>
            .gswps-review-notice {
                padding: 15px 15px 15px 0;
                background-color: #fff;
                border-radius: 3px;
                margin: 20px 20px 0 0;
                border-left: 4px solid transparent;
            }

            .gswps-review-notice:after {
                content: '';
                display: table;
                clear: both;
            }

            .gswps-review-thumbnail {
                width: 114px;
                float: left;
                line-height: 80px;
                text-align: center;
                border-right: 4px solid transparent;
            }

            .gswps-review-thumbnail img {
                width: 72px;
                vertical-align: middle;
                opacity: .85;
                -webkit-transition: all .3s;
                -o-transition: all .3s;
                transition: all .3s;
            }

            .gswps-review-thumbnail img:hover {
                opacity: 1;
            }

            .gswps-review-text {
                overflow: hidden;
            }

            .gswps-review-text h3 {
                font-size: 24px;
                margin: 0 0 5px;
                font-weight: 400;
                line-height: 1.3;
            }

            .gswps-review-text p {
                font-size: 13px;
                margin: 0 0 5px;
            }

            .gswps-review-ul {
                margin: 0;
                padding: 0;
            }

            .gswps-review-ul li {
                display: inline-block;
                margin-right: 15px;
            }

            .gswps-review-ul li a {
                display: inline-block;
                color: #10738B;
                text-decoration: none;
                padding-left: 26px;
                position: relative;
            }

            .gswps-review-ul li a span {
                position: absolute;
                left: 0;
                top: -2px;
            }
        </style>

    <?php
    }


    /**
     * For Dismiss! 
     */
    public function review_dismiss() {

        if (
            !is_admin() ||
            !current_user_can('manage_options') ||
            !isset($_GET['_wpnonce']) ||
            !wp_verify_nonce(sanitize_key(wp_unslash($_GET['_wpnonce'])), 'GSWPS-review-nonce') ||
            !isset($_GET['review_dismiss'])
        ) {

            return;
        }

        add_site_option('review_dismiss', 'yes');
    }

    public function review_pending() {

        if (
            !is_admin() ||
            !current_user_can('manage_options') ||
            !isset($_GET['_wpnonce']) ||
            !wp_verify_nonce(sanitize_key(wp_unslash($_GET['_wpnonce'])), 'GSWPS-review-nonce') ||
            !isset($_GET['GSWPS_review_later'])
        ) {

            return;
        }
        // Reset Time to current time.
        update_site_option('GSWPS_active_time', time());
        update_site_option('GSWPS_maybe_later', 'yes');
    }

    /**
     * Nag Ignore
     */
    public function gswps_nag_ignore() {
        global $current_user;
        $user_id = $current_user->ID;
        /* If user clicks to ignore the notice, add that to their user meta */
        if (isset($_GET['gswps_nag_ignore']) && '0' == $_GET['gswps_nag_ignore']) {
            add_user_meta($user_id, 'gswps_ignore_notice279', 'true', true);
        }
    }

    public function gs_add_new_shortcode_columns($columns) {
        $new_columns['title']     = __('Shortcode Title Name', 'column name');
        $new_columns['shortcode'] = __('Shortcode', 'column name');
        $new_columns['date']      = __('Date', 'column name');
        return $new_columns;
    }

    public function gs_manage_shortcode_columns($column_name, $id) {
        $meta_template_class = get_post_meta($id, 'gs_template_type', true);
        switch ($column_name) {

            case 'shortcode':
                echo '
      <div class="shortcode">
        <code>[gs_wps id="' . $id . '" theme="' . $meta_template_class . '"]</code>
      </div>';
                break;
            default:
                break;
        } // end switch
    }

    public function gswps_row_meta($meta_fields, $file) {

        if ($file != 'gs-woocommerce-products-slider/gs-woocommerce-products-slider.php') {
            return $meta_fields;
        }

        echo "<style>.gswps-rate-stars { display: inline-block; color: #ffb900; position: relative; top: 3px; }.gswps-rate-stars svg{ fill:#ffb900; } .gswps-rate-stars svg:hover{ fill:#ffb900 } .gswps-rate-stars svg:hover ~ svg{ fill:none; } </style>";

        $plugin_rate   = "https://wordpress.org/support/plugin/gs-woocommerce-products-slider/reviews/?rate=5#new-post";
        $plugin_filter = "https://wordpress.org/support/plugin/gs-woocommerce-products-slider/reviews/?filter=5";
        $svg_xmlns     = "https://www.w3.org/2000/svg";
        $svg_icon      = '';

        for ($i = 0; $i < 5; $i++) {
            $svg_icon .= "<svg xmlns='" . esc_url($svg_xmlns) . "' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>";
        }

        // Set icon for thumbsup.
        $meta_fields[] = '<a href="' . esc_url($plugin_filter) . '" target="_blank"><span class="dashicons dashicons-thumbs-up"></span>' . __('Vote!', 'gscs') . '</a>';

        // Set icon for 5-star reviews. v1.1.22
        $meta_fields[] = "<a href='" . esc_url($plugin_rate) . "' target='_blank' title='" . esc_html__('Rate', 'gscs') . "'><i class='gswps-rate-stars'>" . $svg_icon . "</i></a>";

        return $meta_fields;
    }

    public function gsadmin_signup_notice() {

        $this->gsadmin_signup_pending();
        $activation_time    = get_site_option('gsadmin_active_time');
        $maybe_later        = get_site_option('gsadmin_maybe_later');

        if (!$activation_time) {
            add_site_option('gsadmin_active_time', time());
        }

        if ('yes' == $maybe_later) {
            $daysinseconds = 604800; // 7 Days in seconds.
            if (time() - $activation_time > $daysinseconds) {
                add_action('admin_notices', [$this, 'gsadmin_signup_notice_message']);
            }
        } else {
            // add_action( 'admin_notices' , 'gsadmin_signup_notice_message' );
        }
    }

    /**
     * For the notice signup.
     */
    public function gsadmin_signup_notice_message() {
        $scheme      = (parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY)) ? '&' : '?';
        $_later_link = $_SERVER['REQUEST_URI'] . $scheme . 'gsadmin_signup_later=yes';
        $later_url   = wp_nonce_url($_later_link, 'gsadmin-signup-nonce');
    ?>
        <div class=" gstesti-admin-notice updated gsteam-review-notice">
            <div class="gsteam-review-text">
                <h3><?php _e('GS Plugins Affiliate Program is now LIVE!', 'gst') ?></h3>
                <p>Join GS Plugins affiliate program. Share our 80% OFF lifetime bundle deals or any plugin with your friends/followers and earn up to 50% commission. <a href="https://www.gsplugins.com/affiliate-registration/?utm_source=wporg&utm_medium=admin_notice&utm_campaign=aff_regi" target="_blank">Click here to sign up.</a></p>
                <ul class="gsteam-review-ul">
                    <li style="display: inline-block;margin-right: 15px;">
                        <a href="<?php echo esc_url($later_url); ?>" style="display: inline-block;color: #10738B;text-decoration: none;position: relative;">
                            <span class="dashicons dashicons-dismiss"></span>
                            <?php _e('Hide Now', 'gst') ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

<?php
    }

    /**
     * For Maybe Later signup.
     */
    function gsadmin_signup_pending() {

        if (
            !is_admin() ||
            !current_user_can('manage_options') ||
            !isset($_GET['_wpnonce']) ||
            !wp_verify_nonce(sanitize_key(wp_unslash($_GET['_wpnonce'])), 'gsadmin-signup-nonce') ||
            !isset($_GET['gsadmin_signup_later'])
        ) {

            return;
        }
        // Reset Time to current time.
        update_site_option('gsadmin_maybe_later', 'yes');
    }

    public function disable_admin_notices() {

        global $parent_file;
    
        if ( $parent_file != 'gs-woo-slider' ) return;
        
        remove_all_actions( 'network_admin_notices' );
        remove_all_actions( 'user_admin_notices' );
        remove_all_actions( 'admin_notices' );
        remove_all_actions( 'all_admin_notices' );
    
    }
    
    // Load translations
    public function i18n() {
        load_plugin_textdomain('gswps', false, dirname(plugin_basename(GSWPS_FILES_URI)) . '/languages');
    }

    // function gsvariation_wc_requirement_notice() {
				
    //     if ( ! class_exists('WooCommerce') ) {
            
    //         $class = 'notice notice-error';
            
    //         $text    = esc_html__( 'WooCommerce', 'gswps' );
    //         $link    = esc_url( add_query_arg( array(
    //                'tab'       => 'plugin-information',
    //                'plugin'    => 'woocommerce',
    //                'TB_iframe' => 'true',
    //                'width'     => '640',
    //                'height'    => '500',
    //            ), admin_url( 'plugin-install.php' ) ) );
    //         $message = wp_kses( __( "<strong>GS Variation Swatches for WooCommerce Lite</strong> is an add-on of ", 'gswps' ), array( 'strong' => array() ) );
            
    //         printf( '<div class="%1$s"><p>%2$s <a class="thickbox open-plugin-details-modal" href="%3$s"><strong>%4$s</strong></a></p></div>', $class, $message, $link, $text );
            
    //     }
    // }
}
