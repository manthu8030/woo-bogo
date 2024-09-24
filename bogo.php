<?php
/**
 * Plugin Name: WooCommerce BOGO Free
 * Description: Automatically adds a free product to the cart based on Buy 2 Get 1 Free or Buy 3 Get 1 Free logic.
 * Version: 1.0
 * Author: Manthan
 * Author URI: https://github.com/manthu8030
 */

function auto_add_free_product_with_message() {
    // Set the Buy X Get 1 Free rules
    $b2g1_tag = 'buy-2-get-1'; // Replace with your specific tag slug for "Buy 2 Get 1 Free"
    
    // Initialize tracking arrays for products
    $free_products_to_add = [];
    $free_products_to_remove = [];

    // Loop through cart items and apply logic
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        $product = $cart_item['data'];
        $product_id = $product->get_id();
        $quantity = $cart_item['quantity'];
        
        // Check if product has the specific tag for "Buy 2 Get 1 Free"
        if ( has_term( $b2g1_tag, 'product_tag', $product_id ) ) {
            // Check if the customer qualifies for a free product (Buy 2 Get 1 Free)
            if ( $quantity >= 2 ) {
                $free_qty = floor( $quantity / 2 );
                $free_products_to_add[$product_id] = $free_qty;
            }
        } else {
            // Check for "Buy 3 Get 1 Free" logic for other products
            if ( $quantity >= 3 ) {
                $free_qty = floor( $quantity / 3 );
                $free_products_to_add[$product_id] = $free_qty;
            }
        }
    }

    // Loop through free products to add
    foreach ( $free_products_to_add as $product_id => $free_qty ) {
        $is_in_cart = false;

        // Check if free product is already in cart and update its quantity if needed
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            if ( $cart_item['product_id'] == $product_id && isset( $cart_item['free_product'] ) && $cart_item['free_product'] === true ) {
                $is_in_cart = true;

                // Update quantity of the free product to match the offer
                if ( $cart_item['quantity'] != $free_qty ) {
                    WC()->cart->set_quantity( $cart_item_key, $free_qty );
                }
                break;
            }
        }

        // If not in the cart, add the free product
        if ( ! $is_in_cart ) {
            WC()->cart->add_to_cart( $product_id, $free_qty, 0, [], [ 'free_product' => true ] );
        }
    }

    // Remove free product if conditions are no longer met
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        if ( isset( $cart_item['free_product'] ) && $cart_item['free_product'] === true ) {
            $product_id = $cart_item['product_id'];
            if ( ! isset( $free_products_to_add[$product_id] ) ) {
                WC()->cart->remove_cart_item( $cart_item_key );
            }
        }
    }
}
add_action( 'woocommerce_before_calculate_totals', 'auto_add_free_product_with_message' );


// Set the price of free product to zero
function set_free_product_price( $cart_object ) {
    foreach ( $cart_object->get_cart() as $cart_item ) {
        if ( isset( $cart_item['free_product'] ) && $cart_item['free_product'] === true ) {
            $cart_item['data']->set_price( 0 ); // Set free product price to 0
        }
    }
}
add_action( 'woocommerce_before_calculate_totals', 'set_free_product_price' );


// Add a message to indicate that the product is free
function add_free_product_label_in_cart( $title, $cart_item, $cart_item_key ) {
    if ( isset( $cart_item['free_product'] ) && $cart_item['free_product'] === true ) {
        $title .= ' <small>(This is a free product)</small>'; // Message for free product
    }
    return $title;
}
add_filter( 'woocommerce_cart_item_name', 'add_free_product_label_in_cart', 10, 3 );

// Enqueue custom JavaScript for block-based cart page
function custom_bogo_cart_script() {
    if ( is_cart() || is_checkout() ) { // Enqueue only on the cart or checkout page
        wp_enqueue_script(
            'custom-bogo-cart',
            plugin_dir_url( __FILE__ ) . 'assets/js/custom-bogo-cart.js',
            array( 'wp-blocks', 'wp-element', 'wp-components', 'wc-blocks-checkout' ),
            time(), // Change this to version if needed
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'custom_bogo_cart_script' );
 
 