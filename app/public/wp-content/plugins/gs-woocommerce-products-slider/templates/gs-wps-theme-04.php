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

                        <!-- Add to cart -->
                        <?php if ( $settings['show_hide_add_to_cart'] ) : ?>
                            <form class="cart gswps-product-cart" action="<?php echo esc_url( $product->add_to_cart_url() ); ?>" method="post" enctype='multipart/form-data'>
                                <button type="submit" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->add_to_cart_text() ); ?></button>
                            </form>
                        <?php endif; ?>
                        
                        <!-- Product Thumbnail -->
                        <?php echo $product->get_image( 'gswps_product_thumb', array('class' => "gswps_img") ); ?>

                    </div>

                    <!-- gswps-product details -->
                    <div class="gswps-product-details">

                        <?php
                        
                        if ( $settings['gs_show_title'] ) {
                            printf( '<h3 class="gs_wps_title"><a href="%s" class="gswps_img_url">%s</a></h3>', $product->get_permalink(), $product->get_title() );
                        }
                        
                        if ( $settings['show_hide_rating'] ) {
                            gswps_star_rating([ 'rating' => $product->get_average_rating() ]);
                        }

                        if ( $settings['show_hide_price'] && $price_html = $product->get_price_html() ) : ?>
                            <span class="price"><?php echo $price_html; ?></span>
                        <?php endif;

                        if ( $settings['show_hide_desc'] ) {
                            printf( '<div class="gswps-short-desc">%s</div>', gs_product_short_description( $product, $settings['gs_excerpt_length'] ) );
                        }
                        
                        ?>

                    </div> <!-- End of gswps-product details -->
                    
                    <!-- Product Badges -->
                    <?php include Template_Loader::locate_template( 'partials/gs-wps-badges.php' ); ?>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>