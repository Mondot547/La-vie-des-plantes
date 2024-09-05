<?php
namespace GSWPS;
use GSPLUGINS\GS_Asset_Generator_Base;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) die();

class GS_Woo_Asset_Generator extends GS_Asset_Generator_Base {

	private static $instance = null;

	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_assets_key() {
		return 'gs-woo-slider';
	}

	public function generateStyle( $selector, $selector_divi, $targets, $prop, $value ) {
		
		$selectors = [];

		if ( ! empty($targets) ) {
			if ( gettype($targets) !== 'array' ) $targets = [$targets];
		}

		if ( is_divi_active() && !empty($selector_divi) ) {
			if ( empty($targets) ) {
				$selectors[] = $selector_divi;
			} else {
				foreach ( $targets as $target ) $selectors[] = $selector_divi . $target;
			}
		}

		if ( empty($targets) ) {
			$selectors[] = $selector;
		} else {
			foreach ( $targets as $target ) $selectors[] = $selector . $target;
		}

		echo wp_strip_all_tags( sprintf( '%s{%s:%s}', join(',', $selectors), $prop, $value ) );
	}

	public function generateCustomCss( $settings, $shortCodeId ) {

		ob_start();

		$selector      = '#gs_wps_area_' . $shortCodeId;
		$selector_divi = '#et-boc .et-l div ' . $selector;

		if ( isset( $settings['gs_gutter'] ) ) {
			$settings['gs_gutter'] = intval( $settings['gs_gutter'] );
			$this->generateStyle( $selector, $selector_divi, '', '--single-item--gap', $settings['gs_gutter'] . 'px' );
		}

		if( !empty( $settings['star_rating_color'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--gswps-star-color', $settings['star_rating_color'] );
		}

		if( !empty( $settings['price_color'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--gswps-pa-color', $settings['price_color'] );
		}

		if( !empty( $settings['dl_price_color'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--gswps-pa-dl-color', $settings['dl_price_color'] );
		}

		if( !empty( $settings['product_title_color'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--gswps-title-color', $settings['product_title_color'] );
		}

		if( !empty( $settings['product_title_color_hover'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--gswps-title-hover-color', $settings['product_title_color_hover'] );
		}

		if( !empty( $settings['product_des_color'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--gswps-short-desc-color', $settings['product_des_color'] );
		}

		if( !empty( $settings['product_des_color_hover'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--gswps-short-desc-hover-color', $settings['product_des_color_hover'] );
		}

		if( !empty( $settings['product_tags_color'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--gswps-fl-content-color', $settings['product_tags_color'] );
		}

		if( !empty( $settings['product_tags_bg_color'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--gswps-fl-content-bg-color', $settings['product_tags_bg_color'] );
		}

		if( !empty( $settings['product_dis_tag_bg_color'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--gswps-fl-disc-bg-color', $settings['product_dis_tag_bg_color'] );
		}

		if( !empty( $settings['product_stockout_tag_bg_color'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--gswps-stockout-bg-color', $settings['product_stockout_tag_bg_color'] );
		}

		// Slider Navs Color
		if( !empty( $settings['gs_slider_nav_color'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--nav-icon-color', $settings['gs_slider_nav_color'] );
		}
		if( !empty( $settings['gs_slider_nav_hover_color'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--nav-icon-active-color', $settings['gs_slider_nav_hover_color'] );
			$this->generateStyle( $selector, $selector_divi, '', '--nav-border-active-color', $settings['gs_slider_nav_hover_color'] );
		}
		if( !empty( $settings['gs_slider_nav_bg_color'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--nav-bg-color', $settings['gs_slider_nav_bg_color'] );
		}
		if( !empty( $settings['gs_slider_nav_hover_bg_color'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--nav-bg-active-color', $settings['gs_slider_nav_hover_bg_color'] );
		}

		// Slider Dots Color
		if( !empty( $settings['gs_slider_dot_color'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--dot-bg-color', $settings['gs_slider_dot_color'] );
		}
		if( !empty( $settings['gs_slider_dot_hover_color'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--dot-bg-color-active', $settings['gs_slider_dot_hover_color'] );
		}

		if( !empty( $settings['btn_text_color'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--gswps-cart-btn-color', $settings['btn_text_color'] );
		}
		if( !empty( $settings['btn_text_hover_color'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--gswps-cart-btn-hover-color', $settings['btn_text_hover_color'] );
		}
		if( !empty( $settings['btn_background'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--gswps-cart-btn-bg-color', $settings['btn_background'] );
		}
		if( !empty( $settings['btn_bg_hover'] ) ) {
			$this->generateStyle( $selector, $selector_divi, '', '--gswps-cart-btn-bg-hover-color', $settings['btn_bg_hover'] );
		}
		
		return ob_get_clean();
	}

	public function generate_assets_data( Array $settings ) {

		if ( empty($settings) || !empty($settings['is_preview']) ) return;
		
		$this->add_item_in_asset_list( 'styles', 'gswps-public', [ 'gs-bootstrap-grid' ] );
		$this->add_item_in_asset_list( 'scripts', 'gswps-public' );

		$_swiper_enabled  = false;

		if ( $settings['view_type'] === 'carousel' ) {
			$_swiper_enabled = true;
		}

		if ( $_swiper_enabled ) {
			$this->add_item_in_asset_list( 'scripts', 'gswps-public', ['gs-swiper'] );
			$this->add_item_in_asset_list( 'styles', 'gswps-public', ['gs-swiper'] );
		}

		// Hooked for Pro if availabel
		do_action( 'gswps_assets_data_generated', $settings );

		// if ( is_divi_active() ) {
		// 	$this->add_item_in_asset_list( 'styles', 'gswps-public-divi', ['gswps-public'] );
		// }

		$css = $this->get_shortcode_custom_css( $settings );

		if ( !empty($css) ) {
			$this->add_item_in_asset_list( 'styles', 'inline', minimize_css_simple($css) );
		}
	}

	public function is_builder_preview() {
		// return plugin()->integrations->is_builder_preview();
		return false;
	}

	public function enqueue_builder_preview_assets() {
		plugin()->scripts->wp_enqueue_style_all( 'public' );
		plugin()->scripts->wp_enqueue_script_all( 'public' );
		$this->enqueue_prefs_custom_css();
	}

	public function maybe_force_enqueue_assets( Array $settings ) {
		
		plugin()->scripts->wp_enqueue_style_all( 'public' );
		plugin()->scripts->wp_enqueue_script_all( 'public' );

		// Shortcode Generated CSS
		$css = $this->get_shortcode_custom_css( $settings );
		$this->wp_add_inline_style( $css );
		
		// Prefs Custom CSS
		$this->enqueue_prefs_custom_css();
	}

	public function get_shortcode_custom_css( $settings ) {
		return $this->generateCustomCss( $settings, $settings['id'] );
	}

	public function get_prefs_custom_css() {
		$prefs = plugin()->builder->_get_shortcode_pref( false );
		if ( empty($prefs['gs_woops_slider_custom_css']) ) return '';
		return $prefs['gs_woops_slider_custom_css'];
	}

	public function enqueue_prefs_custom_css() {
		$this->wp_add_inline_style( $this->get_prefs_custom_css() );
	}

	public function wp_add_inline_style( $css ) {
		if ( !empty($css) ) $css = minimize_css_simple($css);
		if ( !empty($css) ) wp_add_inline_style( 'gswps-public', wp_strip_all_tags($css) );
	}

	/**
	 * Register Assets to wp_scripts global
	 *
	 * @since 1.0.0
	 */
	public function enqueue_plugin_assets( $main_post_id, $assets = [] ) {

		if ( empty($assets) || empty($assets['styles']) || empty($assets['scripts']) ) return;

		foreach ( $assets['styles'] as $asset => $data ) {
			if ( $asset == 'inline' ) {
				if ( !empty($data) ) wp_add_inline_style( 'gswps-public', $data );
			} else {
				plugin()->scripts->add_dependency_styles( $asset, $data );
			}
		}
		
		foreach ( $assets['scripts'] as $asset => $data ) {
			if ( $asset == 'inline' ) {
				if ( !empty($data) ) wp_add_inline_script( 'gswps-public', $data );
			} else {
				plugin()->scripts->add_dependency_scripts( $asset, $data );
			}
		}

		wp_enqueue_style( 'gswps-public' );
		wp_enqueue_script( 'gswps-public' );

		if ( is_divi_active() ) {
			// wp_enqueue_style( 'gswps-public-divi' );
		}

		$this->enqueue_prefs_custom_css();
	}
}

if ( ! function_exists( 'gsWooAssetGenerator' ) ) {
	function gsWooAssetGenerator() {
		return GS_Woo_Asset_Generator::getInstance(); 
	}
}

// Must inilialized for the hooks
gsWooAssetGenerator();