<?php

/**
 *
 * @package   GS_WooCommerce_Products_Slider
 * @author    GS Plugins <hello@gsplugins.com>
 * @license   GPL-2.0+
 * @link      https://www.gsplugins.com
 * @copyright 2015 GS Plugins
 *
 * @wordpress-plugin
 * Plugin Name:     Woo Product Views Lite
 * Plugin URI:		https://www.gsplugins.com/wordpress-plugins
 * Description:     Transform Product Displays for Better Sales! Enhance Your Product Views: Carousel, Grid, Ticker, List, Masonry to Maximize Conversions! Check the <a href="https://wooprod.gsplugins.com">Woo Product Views Demo</a> & <a href="https://docs.gsplugins.com/woocommerce-product-slider">Documentation</a>. Display Products anywhere using shortcode like [gswoo id=#]
 * Version:         3.0.0
 * Author:       	GS Plugins
 * Author URI:      https://www.gsplugins.com
 * Text Domain:     gswps
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Defining constants
 */
if ( ! defined( 'GSWPS_VERSION' ) )
    define( 'GSWPS_VERSION', '3.0.0' );

if ( ! defined( 'GSWPS_MIN_PRO_VERSION' ) )
    define( 'GSWPS_MIN_PRO_VERSION', '3.0.0' );

if ( ! defined( 'GSWPS_MENU_POSITION' ) )
    define( 'GSWPS_MENU_POSITION', 31 );

if ( ! defined( 'GSWPS_PLUGIN_FILE' ) )
    define( 'GSWPS_PLUGIN_FILE', __FILE__ );

if ( ! defined( 'GSWPS_FILES_DIR' ) )
    define( 'GSWPS_FILES_DIR', untrailingslashit(plugin_dir_path( GSWPS_PLUGIN_FILE )) );

if ( ! defined( 'GSWPS_FILES_URI' ) )
    define( 'GSWPS_FILES_URI', untrailingslashit(plugins_url( '', GSWPS_PLUGIN_FILE )) );

if ( ! defined( 'GSWPS_PRO_PLUGIN' ) )
    define( 'GSWPS_PRO_PLUGIN', 'gs-woocommerce-products-slider-pro/gs-woocommerce-products-slider-pro.php' );

/**
 * Load essential files
 */
require_once GSWPS_FILES_DIR . '/includes/autoloader.php';
require_once GSWPS_FILES_DIR . '/includes/functions.php';
require_once GSWPS_FILES_DIR . '/includes/init.php';