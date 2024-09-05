<?php

namespace GSWPS;

require_once plugin_dir_path( __FILE__ ) . 'gs-plugins-common-pages.php';

new GS_Plugins_Common_Pages([
	
	'parent_slug' 	=> 'gs-woo-slider',
	
	'lite_page_title' 	=> __('Lite Plugins by GS Plugins'),
	'pro_page_title' 	=> __('Premium Plugins by GS Plugins'),
	'help_page_title' 	=> __('Support & Documentation by GS Plugins'),

	'lite_page_slug' 	=> 'gs-wps-plugins-lite',
	'pro_page_slug' 	=> 'gs-wps-plugins-premium',
	'help_page_slug' 	=> 'gs-wps-plugins-help',

	'links' => [
		'docs_link' 	=> 'https://docs.gsplugins.com/woocommerce-product-slider/',
		'rating_link' 	=> 'https://wordpress.org/support/plugin/gs-woocommerce-products-slider/reviews/#new-post'
	]

]);