<?php
namespace GSWPS;

class Plugin {

    private static $_instance;
    public $scripts;
    public $shortcode;
    public $hooks;
    public $builder;

    public static function get_instance() {
        if ( ! self::$_instance ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {

        $this->scripts      = new Scripts;
        $this->shortcode    = new Shortcode;
        $this->hooks        = new Hooks;
        $this->builder      = new Builder;

        new Template_Loader;
        new Dummy_Data;

        require_once GSWPS_FILES_DIR . '/includes/asset-generator/gs-load-asset-generator.php';
        require_once GSWPS_FILES_DIR . '/includes/gs-common-pages/gs-wps-common-pages.php';
    }

}

function plugin() {
    return Plugin::get_instance();
}
plugin();
