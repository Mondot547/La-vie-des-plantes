<?php

namespace GSWPS;

$badge = $product->get_meta( 'product_badge' );

?>

<div class="gswps-featured-level">

    <div class="gswps-featured-level-wrapp">
        
        <!-- Product Out Of Stock Badge -->
        <?php if ( $product->get_type() !== 'variable' && ! $product->is_in_stock() && $settings['display_out_of_stock_badge'] ) : ?>

            <div class="outofstock gswps-featured-content"><?php echo esc_html( gs_get_option( 'out_of_stock_badge_text' ) ); ?></div>

        <?php else: ?>

            <!-- Product Discount Sale Badge -->
            <?php if ( $product->is_on_sale() && $settings['display_sale_badge'] ) : ?>
                <div class="gswps-featured-disc gswps-featured-content">
                    <?php echo esc_html( woo_get_product_sale_badge_amount( $product, $settings['sale_badge_type'] ) ); ?>
                </div>
            <?php endif; ?>
                
            <!-- Product Best Seller Badge -->
            <?php if ( $settings['display_hot_badge'] && !empty( $badge ) ) : ?>
                <div class="onsele gswps-featured-content"><?php echo esc_html( $badge ); ?></div>
            <?php endif; ?>

        <?php endif; ?>

    </div>

</div>