<?php

namespace GSWPS;

$wishlist_popup_text   = gs_get_option( 'wishlist_popup_text' );
$compare_popup_text    = gs_get_option( 'compare_popup_text' );
$quick_view_popup_text = gs_get_option( 'quick_view_popup_text' );

?>

<div class="gswps-action-btn">

    <?php if ( $settings['is_add_to_wishlist_enabled'] && shortcode_exists( 'yith_wcwl_add_to_wishlist' ) ) : ?>

        <div class="gswps-action-btn-wrapper">

            <?php echo do_shortcode( sprintf('[yith_wcwl_add_to_wishlist product_id="%d"]', $product->get_id() ) ); ?>

            <!-- Love Sign -->
            <svg width="18" height="16" viewBox="0 0 18 16" xmlns="http://www.w3.org/2000/svg"><path d="M9 15.2423C8.87379 15.2423 8.74758 15.2086 8.63578 15.1407C2.66344 11.5225 0 8.46 0 5.21016C0 2.36848 2.02289 0 4.85156 0C6.34887 0 7.6616 0.667617 8.64738 1.93078C8.77887 2.09918 8.89594 2.26758 9 2.43105C9.10371 2.26793 9.22113 2.09918 9.35262 1.93078C10.3384 0.667617 11.6511 0 13.1484 0C15.9785 0 18 2.36988 18 5.21016C18 8.45965 15.3366 11.5225 9.36422 15.1404C9.25242 15.2082 9.12621 15.2423 9 15.2423ZM4.85156 1.40625C2.78895 1.40625 1.40625 3.16406 1.40625 5.21016C1.40625 6.53801 2.01375 7.86164 3.26391 9.25664C4.50738 10.6439 6.38613 12.1046 9 13.7152C11.6139 12.1046 13.4926 10.6439 14.7361 9.25664C15.9859 7.86164 16.5938 6.53836 16.5938 5.21016C16.5938 3.16582 15.2132 1.40625 13.1484 1.40625C12.085 1.40625 11.1811 1.87383 10.4611 2.79598C9.90738 3.50543 9.67359 4.22578 9.67113 4.23316C9.57938 4.52707 9.30762 4.72711 9 4.72711C8.69238 4.72711 8.42027 4.52707 8.32887 4.23316C8.32711 4.22789 8.08594 3.48398 7.51359 2.76363C6.79746 1.86293 5.90203 1.40625 4.85156 1.40625Z"/></svg>

            <div class="gswps-action-btn-text"><?php echo esc_html( $wishlist_popup_text ); ?></div>

        </div>

    <?php endif; ?>

    <?php if ( $settings['show_hide_add_to_cart'] && in_array( $theme, ['gs-wc-style-2', 'gs-wc-style-7', 'gs-wc-style-14', 'gs-wc-style-27', ] ) ) : ?>

        <form class="cart gswps-product-cart" action="<?php echo esc_url( $product->add_to_cart_url() ); ?>" method="post" enctype='multipart/form-data'>
            <button type="submit" class="single_add_to_cart_button button alt">

                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.437 6.88776C17.044 6.39551 16.5077 6.12367 15.9272 6.12367H14.6011C14.4762 2.72204 12.0113 0 8.99908 0C5.98684 0 3.52194 2.72204 3.39704 6.12367H2.07092C1.49051 6.12367 0.954185 6.39551 0.561123 6.88776C0.065205 7.5049 -0.114795 8.36449 0.0725519 9.19102L1.66316 16.2C1.90194 17.258 2.7248 18 3.66153 18H14.333C15.2697 18 16.0926 17.2616 16.3313 16.2L17.9256 9.19102C18.113 8.36449 17.933 7.5049 17.437 6.88776ZM8.99908 1.49878C11.1885 1.49878 12.9811 3.54857 13.0987 6.12367H4.89949C5.01704 3.55224 6.80969 1.49878 8.99908 1.49878ZM16.4636 8.85674L14.873 15.8694C14.7921 16.2294 14.5607 16.5012 14.3366 16.5012H3.66153C3.43745 16.5012 3.20602 16.2294 3.1252 15.8694L1.53459 8.85674C1.4501 8.48571 1.39867 7.62245 2.07092 7.62245H15.9272C16.6509 7.62245 16.5481 8.48571 16.4636 8.85674Z" fill="white"/><path d="M6.65541 9.11389C6.24031 9.11389 5.90602 9.44818 5.90602 9.86328V14.547C5.90602 14.9621 6.24031 15.2963 6.65541 15.2963C7.07051 15.2963 7.4048 14.9621 7.4048 14.547V9.86328C7.40847 9.45185 7.07051 9.11389 6.65541 9.11389Z" fill="white"/><path d="M11.1627 9.11389C10.7476 9.11389 10.4134 9.44818 10.4134 9.86328V14.547C10.4134 14.9621 10.7476 15.2963 11.1627 15.2963C11.5779 15.2963 11.9121 14.9621 11.9121 14.547V9.86328C11.9121 9.45185 11.5779 9.11389 11.1627 9.11389Z" fill="white"/></svg>

                <span class="tooltipText">Add to cart</span>
                
            </button>
        </form>
    <?php endif; ?>


    <?php if ( $settings['is_compare_button_enabled'] && shortcode_exists( 'yith_compare_button' ) ) : ?>

        <div class="gswps-action-btn-wrapper">

            <div class="woocommerce product compare-button">

                <?php echo do_shortcode( sprintf('[yith_compare_button type="link" container="no" product="%d"]', $product->get_id() ) ); ?>

                <!-- Compair 1 -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 15"><path d="M10.675 2.027H6.944l.862-.874a.685.685 0 0 0 0-.956.663.663 0 0 0-.944 0l-2 2.026c-.015.016-.03.032-.043.05l-.02.026c-.005.01-.015.02-.02.029l-.02.028a.13.13 0 0 0-.02.029l-.017.03c-.003.01-.01.02-.012.03a.402.402 0 0 0-.01.03c-.003.01-.01.024-.012.033a.107.107 0 0 0-.006.032l-.01.035-.006.034c.001.01-.002.02-.002.029-.003.022-.003.044-.003.066 0 .023 0 .045.003.067l.002.028c.003.013.003.026.006.035l.01.035c.003.01.006.02.006.032.003.01.006.022.012.03l.01.037c.004.01.011.019.013.028.006.01.01.023.016.032l.015.029.02.028c.005.01.012.02.02.029l.02.026a.633.633 0 0 0 .043.05l2 2.027c.26.262.682.262.944 0a.685.685 0 0 0 0-.956l-.862-.874h3.725c2.212 0 4 1.81 4 4.052 0 .985-.388 1.91-1.066 2.596a.685.685 0 0 0 0 .957c.26.263.683.263.946 0A5.078 5.078 0 0 0 16 7.43c0-2.985-2.381-5.4-5.325-5.404Zm.65 10.035c-.003-.012-.003-.025-.006-.034l-.01-.035a.112.112 0 0 1-.01-.032c-.005-.01-.008-.022-.014-.032l-.013-.031c-.003-.01-.01-.02-.012-.029-.007-.01-.01-.022-.016-.031l-.016-.029-.018-.028a.174.174 0 0 0-.021-.023l-.02-.025a.591.591 0 0 0-.042-.048l-2-2.026a.663.663 0 0 0-.945 0 .685.685 0 0 0 0 .956l.863.875h-3.72c-2.214 0-4-1.81-4-4.052 0-.985.386-1.91 1.065-2.596a.685.685 0 0 0 0-.956.663.663 0 0 0-.944 0A5.076 5.076 0 0 0 0 7.43c0 2.985 2.381 5.397 5.325 5.404h3.731l-.862.874a.685.685 0 0 0 0 .955c.26.262.68.262.943 0l2-2.026a.337.337 0 0 0 .044-.051c.007-.006.013-.016.02-.025l.027-.029.019-.029a.132.132 0 0 0 .015-.028l.016-.032c.003-.01.01-.019.012-.028a.196.196 0 0 1 .013-.032c.003-.01.01-.022.012-.031.002-.01.006-.02.006-.032l.01-.035c.002-.013.002-.025.005-.035 0-.01.003-.02.003-.028.004-.022.004-.044.004-.067 0-.022 0-.044-.004-.066-.006-.01-.006-.02-.01-.03Z"/></svg>

                <div class="gswps-action-btn-text"><?php echo esc_html( $compare_popup_text ); ?></div>

            </div>

        </div>

    <?php endif; ?>

    <?php if ( $settings['is_quick_view_enabled'] && shortcode_exists( 'yith_quick_view' ) ) : ?>

        <div class="gswps-action-btn-wrapper">

            <?php echo do_shortcode( sprintf('[yith_quick_view product_id="%d"]', $product->get_id() ) ); ?>

            <!-- Quick View -->
            <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path d="M17.7864 16.7528L14.0245 12.9909C15.1671 11.6162 15.8562 9.85126 15.8562 7.92812C15.8527 3.55799 12.2983 0 7.92812 0C3.55799 0 0 3.55799 0 7.92812C0 12.2983 3.55799 15.8562 7.92812 15.8562C9.85126 15.8562 11.6162 15.1671 12.9909 14.0245L16.7528 17.7864C16.8969 17.9306 17.0833 18.0009 17.2696 18.0009C17.4559 18.0009 17.6423 17.9306 17.7864 17.7864C18.0712 17.5016 18.0712 17.0375 17.7864 16.7528ZM1.46257 7.92812C1.46257 4.3631 4.3631 1.46609 7.92461 1.46609C11.4861 1.46609 14.3866 4.36662 14.3866 7.92812C14.3866 11.4896 11.4861 14.3902 7.92461 14.3902C4.3631 14.3902 1.46257 11.4896 1.46257 7.92812Z"/></svg>

            <div class="gswps-action-btn-text"><?php echo esc_html( $quick_view_popup_text ); ?></div>

        </div>

    <?php endif; ?>

</div>