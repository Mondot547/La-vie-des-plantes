<?php
namespace GSWPS;

// if direct access than exit the file.
defined('ABSPATH') || exit;

$classmaps = [
	'Scripts'         => 'includes/scripts.php',
	'Shortcode'       => 'includes/shortcode.php',
	'Hooks'           => 'includes/hooks.php',
	'Builder'         => 'includes/shortcode-builder/builder.php',
	'Query'           => 'includes/query.php',
	'Dummy_Data'      => 'includes/demo-data/dummy-data.php',
	'Template_Loader' => 'includes/template-loader.php',
];

return $classmaps;
