<?php

namespace GSWPS;

?>

<div class="gs-containeer">

    <div class="<?php echo esc_attr( implode( ' ', $gs_row_classes ) ); ?>">

    <?php foreach ( $products as $product ) :

        $product = wc_get_product( $product );
        $classes = [ 'gswps-product-single--box', get_col_classes( $settings['columns'], $settings['columns_tablet'], $settings['columns_mobile_portrait'], $settings['columns_mobile'] ) ];

        ?>

        <div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">

            <div class="gswps-product-box">

                <!-- Product Image -->
                <div class="gswps-product-img-wrapper">

                    <!-- Floating Action Buttons -->
                    <?php include Template_Loader::locate_template( 'partials/gs-wps-floating-actions.php' ); ?>
                    
                    <!-- Product Thumbnail -->
                    <?php echo $product->get_image( 'gswps_product_thumb', array('class' => "gswps_img") ); ?>

                </div>

                <!-- gswps-product details -->
                <div class="gswps-product-details">

                    <div class="gswps-pd-left">

                        <?php
                        
                        if ( $settings['gs_show_title'] ) {
                            printf( '<h3 class="gs_wps_title"><a href="%s" class="gswps_img_url">%s</a></h3>', $product->get_permalink(), $product->get_title() );
                        }

                        if ( $settings['show_hide_price'] && $price_html = $product->get_price_html() ) : ?>
                            <span class="price"><?php echo $price_html; ?></span>
                        <?php endif;

                        if ( $settings['show_hide_desc'] ) {
                            printf( '<div class="gswps-short-desc">%s</div>', gs_product_short_description( $product, $settings['gs_excerpt_length'] ) );
                        } ?>

                    </div> 

                    <div class="gswps-pd-right">

                        <div class="gswps-action-btn">

                            <?php if ( $settings['is_add_to_wishlist_enabled'] && shortcode_exists( 'yith_wcwl_add_to_wishlist' ) ) : ?>

                                <div class="gswps-action-btn-wrapper gswps-theme2-ab-wrapper">

                                <?php echo do_shortcode( sprintf('[yith_wcwl_add_to_wishlist product_id="%d"]', $product->get_id() ) ); ?>

                                    <!-- Love Sign -->
                                    <svg width="18" height="16" viewBox="0 0 18 16" xmlns="http://www.w3.org/2000/svg"><path d="M9 15.2423C8.87379 15.2423 8.74758 15.2086 8.63578 15.1407C2.66344 11.5225 0 8.46 0 5.21016C0 2.36848 2.02289 0 4.85156 0C6.34887 0 7.6616 0.667617 8.64738 1.93078C8.77887 2.09918 8.89594 2.26758 9 2.43105C9.10371 2.26793 9.22113 2.09918 9.35262 1.93078C10.3384 0.667617 11.6511 0 13.1484 0C15.9785 0 18 2.36988 18 5.21016C18 8.45965 15.3366 11.5225 9.36422 15.1404C9.25242 15.2082 9.12621 15.2423 9 15.2423ZM4.85156 1.40625C2.78895 1.40625 1.40625 3.16406 1.40625 5.21016C1.40625 6.53801 2.01375 7.86164 3.26391 9.25664C4.50738 10.6439 6.38613 12.1046 9 13.7152C11.6139 12.1046 13.4926 10.6439 14.7361 9.25664C15.9859 7.86164 16.5938 6.53836 16.5938 5.21016C16.5938 3.16582 15.2132 1.40625 13.1484 1.40625C12.085 1.40625 11.1811 1.87383 10.4611 2.79598C9.90738 3.50543 9.67359 4.22578 9.67113 4.23316C9.57938 4.52707 9.30762 4.72711 9 4.72711C8.69238 4.72711 8.42027 4.52707 8.32887 4.23316C8.32711 4.22789 8.08594 3.48398 7.51359 2.76363C6.79746 1.86293 5.90203 1.40625 4.85156 1.40625Z"/></svg>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div> <!-- End of gswps-product details -->
                
                <!-- Product Badges -->
                <?php include Template_Loader::locate_template( 'partials/gs-wps-badges.php' ); ?>

            </div>

        </div>

    <?php endforeach; ?>

    </div>

</div>