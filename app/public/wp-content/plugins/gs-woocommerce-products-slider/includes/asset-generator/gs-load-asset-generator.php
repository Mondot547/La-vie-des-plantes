<?php

namespace GSWPS;

if ( ! defined( 'ABSPATH' ) ) die();

require_once __DIR__ . '/gs-asset-generator-base.php';
require_once __DIR__ . '/gs-woo-asset-generator.php';

// Needed for pro compatibility
do_action( 'gs_woo_slider_assets_generator_loaded' );