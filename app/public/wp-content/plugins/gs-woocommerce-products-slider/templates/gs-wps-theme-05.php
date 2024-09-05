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

                    <?php
                    
                    if ( $settings['gs_show_title'] ) {
                        printf( '<h3 class="gs_wps_title"><a href="%s" class="gswps_img_url">%s</a></h3>', $product->get_permalink(), $product->get_title() );
                    }

                    if ( $settings['show_hide_price'] && $price_html = $product->get_price_html() ) : ?>
                        <span class="price"><?php echo $price_html; ?></span>
                    <?php endif;

                    if ( $settings['show_hide_add_to_cart'] ) : ?>
                        <form class="cart gswps-product-cart" action="<?php echo esc_url( $product->add_to_cart_url() ); ?>" method="post" enctype='multipart/form-data'>
                            <button type="submit" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->add_to_cart_text() ); ?>
                                <svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.5303 6.53033C15.8232 6.23744 15.8232 5.76256 15.5303 5.46967L10.7574 0.696699C10.4645 0.403806 9.98959 0.403806 9.6967 0.696699C9.40381 0.989593 9.40381 1.46447 9.6967 1.75736L13.9393 6L9.6967 10.2426C9.40381 10.5355 9.40381 11.0104 9.6967 11.3033C9.98959 11.5962 10.4645 11.5962 10.7574 11.3033L15.5303 6.53033ZM0 6.75H15V5.25H0V6.75Z" fill="#FF5C00"/></svg>
                            </button>
                        </form>
                    <?php endif; ?>

                </div> <!-- End of gswps-product details -->
                
                <!-- Product Badges -->
                <?php include Template_Loader::locate_template( 'partials/gs-wps-badges.php' ); ?>

            </div>

        </div>

    <?php endforeach; ?>

    </div>

</div>