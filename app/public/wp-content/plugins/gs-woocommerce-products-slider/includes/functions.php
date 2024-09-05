<?php

namespace GSWPS;

/**
 * Protect direct access
 */
if ( !defined('ABSPATH') ) die();

function is_divi_active() {
    if (!defined('ET_BUILDER_PLUGIN_ACTIVE') || !ET_BUILDER_PLUGIN_ACTIVE) return false;
    return et_core_is_builder_used_on_current_request();
}

function is_divi_editor() {
    if ( !empty($_POST['action']) && $_POST['action'] == 'et_pb_process_computed_property' && !empty($_POST['module_type']) && $_POST['module_type'] == 'gs_team_members' ) return true;
}

function minimize_css_simple($css) {
    // https://datayze.com/howto/minify-css-with-php
    $css = preg_replace('/\/\*((?!\*\/).)*\*\//', '', $css); // negative look ahead
    $css = preg_replace('/\s{2,}/', ' ', $css);
    $css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
    $css = preg_replace('/;}/', '}', $css);
    return $css;
}

function get_gs_woo_terms($term_name, $idsOnly = false) {

    $_terms = get_terms($term_name, [
        'hide_empty' => false,
    ]);

    if (empty($_terms)) return [];

    if ($idsOnly) return wp_list_pluck($_terms, 'term_id');

    $terms = [];

    foreach ($_terms as $term) {
        $terms[] = [
            'label' => wp_specialchars_decode( $term->name ),
            'value' => $term->term_id
        ];
    }

    return $terms;
}

function gs_wp_kses($content) {

    $allowed_tags = wp_kses_allowed_html('post');

    $input_common_atts = ['class' => true, 'id' => true, 'style' => true, 'novalidate' => true, 'name' => true, 'width' => true, 'height' => true, 'data' => true, 'title' => true, 'placeholder' => true, 'value' => true];

    $allowed_tags = array_merge_recursive($allowed_tags, [
        'select' => $input_common_atts,
        'input' => array_merge($input_common_atts, ['type' => true, 'checked' => true]),
        'option' => ['class' => true, 'id' => true, 'selected' => true, 'data' => true, 'value' => true]
    ]);

    return wp_kses(stripslashes_deep($content), $allowed_tags);
}

function echo_return($content, $echo = false) {
    if ($echo) {
        echo gs_wp_kses($content);
    } else {
        return $content;
    }
}

function gs_woo_string_to_array($terms = '') {
    if (empty($terms)) return [];
    return (array) array_filter(explode(',', $terms));
}

function gs_woo_array_to_string($terms = []) {
    if (empty($terms)) return [];
    return implode(',', $terms);
}

function gs_product_select($select_by) {

    $products = wc_get_products(array(
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => -1
    ));

    $_products = [];

    foreach ( $products as $product ) {

        if ( $select_by === 'name' ) {
            $_products[] = [
                'label' => $product->get_name(),
                'value' => $product->get_id()
            ];
        }

        if ( $select_by === 'sku' ) {
            $_products[] = [
                'label' => $product->get_sku(),
                'value' => $product->get_id()
            ];
        }
    }

    return $_products;
}

function gs_product_tags() {
    $product_tags = get_terms('product_tag');

    $tags = [];
    foreach ($product_tags as $product_tag) {
        $tags[] = [
            'label' => $product_tag->name,
            'value' => $product_tag->term_id
        ];
    }
    return $tags;
}

function gs_product_by_type($product_types = []) {

    if( empty( $product_types ) ) return;

    $featured_products = [];
    if (in_array("gs-featured", $product_types)) {

        $featured_products = wc_get_featured_product_ids();
    }

    $query_args = [
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'post_type'      => 'product'
    ];

    $meta_query = [];
    
    if (in_array("gs-older-products", $product_types)) {
        $query_args['order']          = 'ASC';
    } else {
        $query_args['order']          = 'DESC';
    }

    if (in_array("in-stock", $product_types)) {
        $in_stock = array(
            'key'     => '_stock_status',
            'value'   => 'instock',
            'compare' => 'IN',
        );

        array_push($meta_query, $in_stock);
    }

    if (in_array("gs-free", $product_types)) {

        $free_prod = array(
            'key'     => '_price',
            'value'   => '0',
            'compare' => '=',
        );

        array_push($meta_query, $free_prod);
    }

    if (in_array("popularity", $product_types)) {

        $best_seller = array(
            'key'   => 'total_sales'
        );

        array_push($meta_query, $best_seller);
    }

    if (in_array("rating", $product_types)) {
        $top_rated = [
            'key' => '_wc_average_rating'
        ];
        array_push($meta_query, $top_rated);
    }

    if (in_array("popularity", $product_types) || in_array("rating", $product_types)) {

        $query_args['orderby'] = [
            'meta_value_num' => 'DESC',
            'date'           => 'DESC'
        ];
    }
    else {
        $query_args['orderby']   = 'date';
    }

    if ( !empty($meta_query) ) {
        $meta_query['relation'] = 'AND';
    }

    $query_args['meta_query'] = $meta_query;

    $queried = new \WP_Query( $query_args );

    $prod_ids = wp_list_pluck( $queried->posts, 'ID' );

    $all_prod_ids = array_merge( $prod_ids, $featured_products );

    return $all_prod_ids;
}

function is_preview() {
    return isset($_REQUEST['gswoo_shortcode_preview']) && !empty($_REQUEST['gswoo_shortcode_preview']);
}

function get_shortcode_settings($id, $is_preview = false) {

    $default_settings = array_merge( ['id' => $id, 'is_preview' => $is_preview], plugin()->builder->get_shortcode_default_settings() );

    if ( $is_preview ) {
        $preview_settings = plugin()->builder->validate_shortcode_settings( get_transient($id) );
        return shortcode_atts( $default_settings, $preview_settings );
    }

    $shortcode = plugin()->builder->_get_shortcode($id);
    
    if ( empty($shortcode) ) return false;

    return shortcode_atts( $default_settings, (array) $shortcode['shortcode_settings'] );
}

function carousel_settings( $settings = [], $is_ticker = false ) {

    if ( is_gswps_pro() && gswps_pro_is_valid() ) {

        if ( $is_ticker ) {
            return [
                'mode' 				=> 'horizontal',
                'speed'             => intval( $settings['gs_ticker_speed'] ),
                'pauseOnHover' 		=> wp_validate_boolean( $settings['gs_stop_on_hover'] ),
                'slideSpace' 		=> intval( $settings['gs_gutter'] ),
                'desktopLogos'      => gs_cols_to_number( $settings['columns'] ),
                'tabletLogos'      	=> gs_cols_to_number( $settings['columns_tablet'] ),
                'mobileLogos'      	=> gs_cols_to_number( $settings['columns_mobile_portrait'] ),
                'reverseDirection'  => wp_validate_boolean( $settings['reverse_direction'] ),
            ];
        }

    }
    
    $carousel_settings = [
        'speed'                     => intval( $settings['gs_carousel_speed'] ),
        'loop'                      => wp_validate_boolean( $settings['gs_is_loop'] ),
        'columns'                   => gs_cols_to_number( $settings['columns'] ),
        'columns_tablet'            => gs_cols_to_number( $settings['columns_tablet'] ),
        'columns_mobile_portrait'   => gs_cols_to_number( $settings['columns_mobile_portrait'] ),
        'columns_mobile'            => gs_cols_to_number( $settings['columns_mobile'] ),
        'slide_moves'               => $settings['move_slides'],
        'spaceBetween'              => intval( $settings['gs_gutter'] ),
        'isAutoplay'                => wp_validate_boolean( $settings['gs_is_autoplay'] ),
        'autoplay_delay'            => intval( $settings['gs_autoplay_delay'] ),
        'pause_on_hover'            => wp_validate_boolean( $settings['gs_stop_on_hover'] ),
        'navs'                      => wp_validate_boolean( $settings['carousel_navs_enabled'] ),
        'dots'                      => wp_validate_boolean( $settings['carousel_dots_enabled'] ),
        'reverseDirection'          => false,
        'dynamicBullets'            => wp_validate_boolean( $settings['dynamic_dots_enabled'] ),
        'carousel_navs_style'       => sanitize_key($settings['carousel_navs_style']),
        'carousel_dots_style'       => sanitize_key($settings['carousel_dots_style']),
        'carousel_navs_position'    => sanitize_key($settings['carousel_navs_position'])
    ];

    if ( is_gswps_pro() && gswps_pro_is_valid() ) {
        $carousel_settings['reverseDirection'] = wp_validate_boolean( $settings['reverse_direction'] );
    }

    return $carousel_settings;
}

function is_gswps_pro() {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    return defined('GSWPS_VERSION_PRO') && is_plugin_active( GSWPS_PRO_PLUGIN );
}

function gs_is_wc_active() {
	return class_exists( 'WooCommerce' );
}

function gs_woo_plugin_update_version() {
    if ( GSWPS_VERSION !==  get_option('gs_woops_slider_version') ) {
        update_option( 'gs_woops_slider_version', GSWPS_VERSION );
        return true;
    }
    return false;
}

function gs_get_option( $option, $default = '' ) {

    $defaults = plugin()->builder->get_shortcode_default_prefs();
    $options = shortcode_atts( $defaults, get_option('gs_woo_slider_shortcode_prefs') );

    if (isset($options[$option])) {
        return $options[$option];
    }

    return $default;
}

function gswps_star_rating( $args = array() ) {
	$defaults    = array(
		'rating' => 0,
		'style'   => 'regular',
		'echo'   => true
	);
	$parsed_args = wp_parse_args( $args, $defaults );

	// Non-English decimal places when the $rating is coming from a string.
	$rating = (float) str_replace( ',', '.', $parsed_args['rating'] );

	// Calculate the number of each type of star needed.
	$full_stars  = floor( $rating );
	$half_stars  = ceil( $rating - $full_stars );
	$empty_stars = 5 - $full_stars - $half_stars;

    /* translators: Hidden accessibility text. %s: The rating. */
    $title = sprintf( __( '%s rating' ), number_format_i18n( $rating, 1 ) );

    if ( $parsed_args['style'] === 'compact' ) {
        $output  = '<div class="gswps-star-rating">';
        $output .= '<span class="screen-reader-text">' . $title . '</span>';
        $output .= '<div class="gswps-star-full" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="17" viewBox="0 0 18 17" fill="none">
        <path d="M9 0L11.0206 6.21885H17.5595L12.2694 10.0623L14.2901 16.2812L9 12.4377L3.70993 16.2812L5.73056 10.0623L0.440492 6.21885H6.97937L9 0Z" fill="#FAB421"/>
        </svg></div><div class="gswps-rating-value">('. number_format_i18n( $rating, 1 ) .')</div>';
        $output .= '</div>';

        if ( $parsed_args['echo'] ) echo $output;

        return $output;
    }

	$output  = '<div class="gswps-star-rating">';
	$output .= '<span class="screen-reader-text">' . $title . '</span>';
	$output .= str_repeat( '<div class="gswps-star-full" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="17" viewBox="0 0 18 17" fill="none">
    <path d="M9 0L11.0206 6.21885H17.5595L12.2694 10.0623L14.2901 16.2812L9 12.4377L3.70993 16.2812L5.73056 10.0623L0.440492 6.21885H6.97937L9 0Z" fill="#FAB421"/>
    </svg></div>', $full_stars );

	$output .= str_repeat( '<div class="gswps-star-half" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="24" height="24" viewBox="0 0 24 24"><path d="M12 16.613l4.507 2.704-1.183-5.127 4-3.493-5.296-0.451-2.028-4.845v11.211zM24 9.176l-6.535 5.69 1.972 8.451-7.437-4.507-7.437 4.507 1.972-8.451-6.535-5.69 8.62-0.732 3.38-7.944 3.38 7.944z"/></svg></div>', $half_stars );

	$output .= str_repeat( '<div class="gswps-star-empty" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="14" viewBox="0 0 15 14" fill="none">
    <path d="M14.3222 4.80703L9.59165 4.8143L8.13724 0.450886C8.05477 0.181809 7.79238 0 7.5 0C7.20762 0 6.94522 0.181809 6.85526 0.450886L5.40085 4.8143L0.677775 4.80703C0.385394 4.80703 0.123001 4.98884 0.0330372 5.25791C-0.0569262 5.52699 0.0405341 5.82516 0.280437 5.99242L4.11138 8.68319L2.64198 13.0393C2.55201 13.3084 2.64947 13.6066 2.88938 13.7738C3.12928 13.9411 3.44415 13.9411 3.68405 13.7738L7.5 11.0685L11.3159 13.7738C11.4359 13.8611 11.5708 13.8975 11.7133 13.8975C11.8557 13.8975 11.9907 13.8538 12.1106 13.7738C12.3505 13.6066 12.448 13.3084 12.358 13.0393L10.8886 8.68319L14.7196 5.99242C14.9595 5.82516 15.0569 5.52699 14.967 5.25791C14.877 4.98884 14.6146 4.80703 14.3222 4.80703ZM9.69661 7.89778C9.4567 8.06504 9.35924 8.36321 9.44921 8.63229L10.4238 11.5194L7.89734 9.73041C7.65744 9.56315 7.34256 9.56315 7.10266 9.73041L4.57619 11.5194L5.55079 8.63229C5.64076 8.36321 5.5433 8.06504 5.30339 7.89778L2.76942 6.12332L5.89565 6.1306C6.18803 6.1306 6.45043 5.94879 6.54039 5.67971L7.5 2.78531L8.45961 5.67244C8.54957 5.94151 8.81197 6.12332 9.10435 6.12332L12.2306 6.11605L9.69661 7.89778Z" fill="#FF5C00"/>
    </svg></div>', $empty_stars );
	$output .= '</div>';

	if ( $parsed_args['echo'] ) echo $output;

	return $output;
}

/**
 * Compatibility check with Pro plugin
 */
function is_woocommerce_compatible() {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        add_action( 'admin_notices', 'GSWPS\woocommerce_fail_notice' );
        return false;
    }
    return true;
}

/**
 * Compatibility check with Pro plugin
 */
function is_pro_compatible() {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    if ( is_plugin_active( GSWPS_PRO_PLUGIN ) ) {
        if ( version_compare( GSWPS_VERSION_PRO, GSWPS_MIN_PRO_VERSION, '<' ) ) {
            add_action( 'admin_notices', 'GSWPS\pro_compatibility_notice' );
            return false;
        }
    }
    return true;
}

function woocommerce_fail_notice() {

    $screen = get_current_screen();

    $woocommerce_plugin = 'woocommerce/woocommerce.php';
    
    if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) return;

    if ( array_key_exists( $woocommerce_plugin, get_plugins() ) ) {

        if ( ! current_user_can( 'activate_plugins' ) ) return;

        $activation_url = wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . $woocommerce_plugin . '&plugin_status=all&paged=1&s' ), 'activate-plugin_' . $woocommerce_plugin );
        $message = '<p>' . __( 'GS WooCommerce Products Slider is not working because you need to activate the WooCommerce plugin.', 'gswps' ) . '</p>';
        $message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $activation_url, __( 'Activate WooCommerce Plugin Now', 'gswps' ) ) . '</p>';
        
    } else {

        if ( ! current_user_can( 'install_plugins' ) ) return;

        $install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' );
        $message = '<p>' . __( 'GS WooCommerce Products Slider is not working because you need to install the WooCommerce plugin.', 'gswps' ) . '</p>';
        $message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, __( 'Install WooCommerce Now', 'gswps' ) ) . '</p>';
        
    }

    echo '<div class="error"><p>' . $message . '</p></div>';
    
}

function pro_compatibility_notice() {

    $screen = get_current_screen();
    
    if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) return;
    if ( 'update' === $screen->base && 'update' === $screen->id ) return;

    if ( ! current_user_can( 'update_plugins' ) ) return;

    $upgrade_url = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . GSWPS_PRO_PLUGIN ), 'upgrade-plugin_' . GSWPS_PRO_PLUGIN );
    $message = '<p>' . __( 'GS WooCommerce Products is not working because you need to upgrade the GS WooCommerce Products Pro plugin to latest version.', 'gswps' ) . '</p>';
    $message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $upgrade_url, __( 'Upgrade GS WooCommerce Products Pro Now', 'gswps' ) ) . '</p>';

    echo '<div class="error"><p>' . $message . '</p></div>';
    
}

function gs_product_short_description( $product, $max_length = 100 ) {
    $description = sanitize_text_field( $product->get_short_description() );

    if ( $max_length > 0 && strlen( $description ) > $max_length ) {
        $description = substr( $description, 0, $max_length );
    }

    return $description;
}

function is_pro_active() {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    return is_plugin_active( GSWPS_PRO_PLUGIN );
}

/**
 * Plugins action links
 */
function add_pro_link( $links ) {
    if ( ! is_pro_active() ) {
        $links[] = '<a style="color: red; font-weight: bold;" class="gs-pro-link" href="https://www.gsplugins.com/product/gs-woocommerce-product-slider" target="_blank">Go Pro!</a>';
    }
    $links[] = '<a href="https://www.gsplugins.com/wordpress-plugins" target="_blank">GS Plugins</a>';
    return $links;
}

/**
 * Plugins Load Text Domain
 */
function gs_load_textdomain() {
    load_plugin_textdomain( 'gswps', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

/**
 * Remove Reviews Metadata on plugin Deactivation.
 */
function on_deactivation() {
    delete_option('GSWPS_active_time');
    delete_option('GSWPS_maybe_later');
    delete_option('gsadmin_maybe_later');
}

/**
 * Activation redirects
 */
function on_activation() {
    add_option('gswps_activation_redirect', true);
}

/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function gs_appsero_init() {

    // if ( !class_exists('GSWPSAppSero\Insights') ) {
    //     require_once GSL_PLUGIN_DIR . 'includes/appsero/Client.php';
    // }

    // $client = new \GSWPSAppSero\Client('2f95117b-b1c6-4486-88c0-6b6d815856bf', 'GS Logo Slider', __FILE__);
    // // Active insights
    // $client->insights()->init();
}

function get_col_classes( $desktop = '3', $tablet = '4', $mobile_portrait = '6', $mobile = '12' ) {
    return sprintf('gs-col-lg-%s gs-col-md-%s gs-col-sm-%s gs-col-xs-%s', $desktop, $tablet, $mobile_portrait, $mobile);
}

function gs_cols_to_number($cols) {
    return (12 / (float) str_replace('_', '.', $cols));
}

function woo_get_percentage_sale( $product ) {

    if ( $product->is_type('variable') ) {
        $percentages = array();

        // Get all variation prices
        $prices = $product->get_variation_prices();

        // Loop through variation prices
        foreach( $prices['price'] as $key => $price ){
            // Only on sale variations
            if( $prices['regular_price'][$key] !== $price ){
                // Calculate and set in the array the percentage for each variation on sale
                $percentages[] = round(100 - ($prices['sale_price'][$key] / $prices['regular_price'][$key] * 100));
            }
        }

        return ( 0 - max($percentages) ) . '%';

    } else {

        $regular_price = (float) $product->get_regular_price();
        $sale_price    = (float) $product->get_sale_price();

        return ( 0 - round( 100 - ( $sale_price / $regular_price * 100) ) ) . '%';
    }
}

function woo_get_product_sale_badge_amount( $product, $badge_type ) {
    if ( $badge_type == 'numeric' ) {
        return woo_get_percentage_sale( $product );
    } else {
        return gs_get_option( 'sale_badge_text' );
    }
}

function disable_pro_items( $free_items, $pro_items ) {

    if (!is_gswps_pro() || ! gswps_pro_is_valid()) {
        $pro_items = array_map(function ($item) {
            $item['label'] = $item['label'] . ' (Pro)';
            $item['pro'] = true;
            return $item;
        }, $pro_items);
    }

    return array_merge($free_items, $pro_items);
}