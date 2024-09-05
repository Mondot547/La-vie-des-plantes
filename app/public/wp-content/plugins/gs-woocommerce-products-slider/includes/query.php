<?php

namespace GSWPS;

class Query {

    public $settings = [];

    public function __construct( $settings ) {

        $this->settings = shortcode_atts([
            'order' => '',
            'orderby' => '',
            'gs_l_products' => -1,
            'select_cats' => [],
            'exclude_cats' => [],
            'select_by_tag' => [],
            'deselect_by_tag' => [],
            'select_by_name' => [],
            'deselect_by_name' => [],
            'gs_woo_product_type' => [],
            'gs_woo_ex_product_type' => []
        ], $settings );

        return $this;
    }

    public function get_products() {

        $number_of_posts = $this->settings['gs_l_products'];
        $product_types = (array) $this->settings['gs_woo_product_type'];
        $include = [];

        if ( in_array( 'gs-featured', $product_types ) ) {
            $include = wc_get_featured_product_ids();
        }

        $query_args = array(
            'post_type'         => 'product',
            'posts_per_page'    => $number_of_posts,
            'include'           => $include,
            'exclude'           => [],
            'tax_query'         => [],
            'order'             => $this->settings['order'],
            'orderby'           => $this->settings['orderby'],
        );

        $query_args = (array) apply_filters( 'gswps_products_query_args', $query_args, $this->settings );
        
        if ( $query_args['orderby'] === 'menu_order' ) {
            $query_args['orderby'] = 'menu_order title';
        }

        if ( count($query_args['tax_query']) > 1 ) {
            $query_args['tax_query']['relation'] = 'AND';
        }

        do_action( 'gswps_before_wc_get_products', $query_args, $this->settings );

        return wc_get_products( $query_args );

    }

}