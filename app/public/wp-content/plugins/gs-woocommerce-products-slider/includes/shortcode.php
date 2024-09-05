<?php
namespace GSWPS;

/**
 * Protect direct access
 */
if (!defined('ABSPATH')) exit;

class Shortcode {

    public function __construct() {
        add_shortcode('gswoo', [$this, 'register_gswoo_shortcode_builder']);
    }

    public function register_gswoo_shortcode_builder($atts) {
        
        if ( empty($atts['id']) ) {
            return __('Product Slider: No shortcode ID found', 'gswps');
        }
        
        return $this->render_shortcode( $atts['id'], !empty($atts['preview']) );
    }

    public function should_custom_script_render() {

        $render = false;

        // For VC
        if (!empty($_GET['vc_editable'])) return true;

        // For Elementor
        if ((!empty($_GET['action']) && $_GET['action'] == 'elementor') || (!empty($_POST['action']) && $_POST['action'] == 'elementor_ajax')) return true;

        // For gutenberg
        if (!empty($_GET['context']) && $_GET['context'] == 'edit') return true;

        // Beaver Builder
        if (isset($_GET['fl_builder_ui_iframe']) || !empty($_POST['fl_builder_data'])) return true;

        // Oxygen Builder
        if (!empty($_GET['action']) && $_GET['action'] == 'oxy_render_oxy-solid-testimonial') return true;

        return $render;
    }

    public function render_shortcode( $shortcode_id, $is_preview = false ) {

        $settings = get_shortcode_settings( $shortcode_id, $is_preview );

        if ( ! is_gswps_pro() || ! gswps_pro_is_valid() ) {

            // View Type
            if ( $settings['view_type'] === 'ticker' ) {
                $settings['view_type'] = 'carousel';
            } else if ( $settings['view_type'] === 'masonry' ) {
                $settings['view_type'] = 'grid';
            }
            
            // Theme
            $pro_themes = wp_list_pluck( plugin()->builder->get_pro_templates(), 'value' );
            if ( in_array( $settings['gs_woo_template'], $pro_themes ) ) {
                $settings['gs_woo_template'] = 'gs-wc-style-1';
            }

            // Order By
            if ( in_array( $settings['orderby'], [ 'popularity', 'rating', 'price' ] ) ) {
                $settings['orderby'] = 'menu_order';
            }
            
        } else {

            // View Type Fix 
            if ( strpos( $settings['gs_woo_template'], 'gs-wc-style-list' ) !== false ) {
                $settings['view_type'] = 'grid';
            }

        }

        if ( empty($settings) ) {
            return __('Product Slider: No shortcode found', 'gswps');
        }

        // By default force mode
        $force_asset_load = true;

        if ( ! $is_preview ) {

            // For Asset Generator
            $main_post_id = gsWooAssetGenerator()->get_current_page_id();
            $asset_data   = gsWooAssetGenerator()->get_assets_data( $main_post_id );

            if ( empty($asset_data) ) {

                // Saved assets not found
                // Force load the assets for first time load
                // Generate the assets for later use
                gsWooAssetGenerator()->generate($main_post_id, $settings);
                
            } else {

                // Saved assets found
                // Stop force loading the assets
                // Leave the job for Asset Loader
                $force_asset_load = false;
            }
        }

        $theme = $settings['gs_woo_template'];
        $image_type = $settings['image_type'];
        $view_type = $settings['view_type'];

        $products = apply_filters( 'gswoops_products', [], $settings );
        if ( empty($products) ) $products = ( new Query( $settings ) )->get_products();

        $carousel_settings      = carousel_settings( $settings, $view_type === 'ticker' );
        $carousel_navs_enabled  = $settings['carousel_navs_enabled'];
        $carousel_dots_enabled  = $settings['carousel_dots_enabled'];
        $carousel_navs_style    = $settings['carousel_navs_style'];
        $carousel_dots_style    = $settings['carousel_dots_style'];
        $carousel_navs_position = $settings['carousel_navs_position'];

        if ( ! is_gswps_pro() || ! gswps_pro_is_valid() ) {

            $free_nav_styles = plugin()->builder->get_carousel_nav_styles( true );
            $free_nav_styles = wp_list_pluck( $free_nav_styles, 'value' );
            if ( ! in_array( $carousel_navs_style, $free_nav_styles ) ) $carousel_navs_style = 'default';


            $free_dot_styles = plugin()->builder->get_carousel_dot_styles( true );
            $free_dot_styles = wp_list_pluck( $free_dot_styles, 'value' );
            if ( ! in_array( $carousel_dots_style, $free_dot_styles ) ) $carousel_dots_style = 'default';

        }

        $wrapper_classes = ['gs_wps_area', $theme, 'view_type-' . $view_type, 'image_type-' . $image_type ];
        $gs_row_classes = ['gs-roow'];

		if ( $view_type == 'carousel' ) {

			$wrapper_classes[] = 'gs-has-carousel-swiper';
			$gs_row_classes[] = 'gs_carousel_swiper';

			if ( $carousel_navs_enabled ) {
				$wrapper_classes[] = 'carousel-has-navs';
				$wrapper_classes[] = 'carousel-navs--' . $carousel_navs_style;
                $wrapper_classes[] = 'carousel-navs-pos--' . $carousel_navs_position;
			}

			if ( $carousel_dots_enabled ) {
				$wrapper_classes[] = 'carousel-has-dots';
				$wrapper_classes[] = 'carousel-dots--' . $carousel_dots_style;
			}

		}

        ob_start(); ?>

        <div id="gs_wps_area_<?php echo esc_attr( $shortcode_id ); ?>" class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" style="visibility:hidden; opacity:0;" data-carousel-settings='<?php echo json_encode($carousel_settings); ?>'>

            <?php

            $template = 'gs-wps-theme-01.php';

            if( is_gswps_pro() ) {
                $template = apply_filters( 'gswps_template_after__loaded', $theme );
            }
            else {
                switch ( $theme ) {
                    case 'gs-wc-style-1' : $template = 'gs-wps-theme-01.php'; break;
                    case 'gs-wc-style-2' : $template = 'gs-wps-theme-02.php'; break;
                    case 'gs-wc-style-3' : $template = 'gs-wps-theme-03.php'; break;
                    case 'gs-wc-style-4' : $template = 'gs-wps-theme-04.php'; break;
                    case 'gs-wc-style-5' : $template = 'gs-wps-theme-05.php'; break;    
                }
            }

            include Template_Loader::locate_template( $template );
            
            ?>

        </div>

        <?php

        wp_reset_postdata(); wp_reset_query();

        // Fire force asset load when needed
        if ( $force_asset_load ) {

            gsWooAssetGenerator()->force_enqueue_assets( $settings );
            wp_add_inline_script( 'gswps-public', "jQuery(document).trigger( 'gswps:scripts:reprocess' );jQuery(function() { jQuery(document).trigger( 'gswps:scripts:reprocess' ) })" );

			// Shortcode Custom CSS
			$css = gsWooAssetGenerator()->get_shortcode_custom_css( $settings );
			if ( !empty($css) ) printf( "<style>%s</style>" , minimize_css_simple($css) );
			
			// Prefs Custom CSS
			$css = gsWooAssetGenerator()->get_prefs_custom_css();
			if ( !empty($css) ) printf( "<style>%s</style>" , minimize_css_simple($css) );

        }

        return ob_get_clean();

    }

}