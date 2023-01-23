<?php
/*
* Plugin Name: Library Book Search
* Description: A plugin for searching library books by name, author, publisher, price, and rating.
* Version: 1.0
* Author: Jayvin busa
* Text Domain: library-book-search
*/

//Include file for search form and search result
require_once( plugin_dir_path( __FILE__ ) . 'shortcodes/search-form.php');
require_once( plugin_dir_path( __FILE__ ) . 'shortcodes/search-results.php');

// Register custom post type and taxonomies on plugin activation
add_action( 'init', 'librarysearch_activate' );
function librarysearch_activate() {
    librarysearch_register_book_post_type();
    librarysearch_register_taxonomies();
    librarysearch_register_custom_fields();
    flush_rewrite_rules();
}

// Register custom post type for books
function librarysearch_register_book_post_type() {
    $args = array(
        'labels' => array(
            'name' => __( 'Books', 'library-book-search' ),
            'singular_name' => __( 'Book', 'library-book-search' )
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array( 'title', 'editor', 'thumbnail' )
    );
    register_post_type( 'book', $args );
}

// Register custom taxonomies for author and publisher
function librarysearch_register_taxonomies() {
    register_taxonomy( 'author', 'book', array(
        'labels' => array(
            'name' => __( 'Authors', 'library-book-search' ),
            'singular_name' => __( 'Author', 'library-book-search' )
        ),
        'hierarchical' => true
    ) );
    register_taxonomy( 'publisher', 'book', array(
        'labels' => array(
            'name' => __( 'Publishers', 'library-book-search' ),
            'singular_name' => __( 'Publisher', 'library-book-search' )
        ),
        'hierarchical' => true
    ) );
}

// Register the custom fields
function librarysearch_register_custom_fields() {
    add_action( 'add_meta_boxes', 'librarysearch_add_custom_fields' );
    add_action( 'save_post', 'librarysearch_save_custom_fields' );
}

// Add the custom fields to the "book" post type
function librarysearch_add_custom_fields() {
    add_meta_box( 'librarysearch_book_price_range', __( 'Price Range', 'library-book-search' ), 'librarysearch_book_price_range_callback', 'book', 'normal', 'default' );
    add_meta_box( 'librarysearch_book_rating', __( 'Rating', 'library-book-search' ), 'librarysearch_book_rating_callback', 'book', 'normal', 'default' );
}

// Callback function to display the "price range" custom field
function librarysearch_book_price_range_callback( $post ) {
    $price_range_min = get_post_meta( $post->ID, 'price_range_min', true );
    $price_range_max = get_post_meta( $post->ID, 'price_range_max', true );

    echo '<label for="price_range_min">' . __( 'Minimum Price', 'library-book-search' ) . '</label>';
    echo '<input type="number" id="price_range_min" name="price_range_min" value="' . esc_attr( $price_range_min ) . '" />';
    echo '<br>';
    echo '<label for="price_range_max">' . __( 'Maximum Price', 'library-book-search' ) . '</label>';
    echo '<input type="number" id="price_range_max" name="price_range_max" value="' . esc_attr( $price_range_max ) . '" />';
}

// Callback function to display the "price range" custom field
function librarysearch_book_rating_callback( $post ) {
    $rating = get_post_meta( $post->ID, 'rating', true );
    echo '<label for="rating">' . __( 'Rating', 'library-book-search' ) . '</label>';
    echo '<select id="rating" name="rating">';
    for ( $i = 1; $i <= 5; $i++ ) {
        echo '<option value="' . $i . '" ' . selected( $rating, $i, false ) . '>' . $i . '</option>';
    }
    echo '</select>';
}

//validate and sanitize the user input before saving the custom field values
function librarysearch_save_custom_fields( $post_id ) {
    // Check if this is an autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check if the user has permission to save the post
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Validate and sanitize the "price range" custom field
    if ( isset( $_POST['price_range_min'] ) && isset( $_POST['price_range_max'] ) ) {
        $price_range_min = sanitize_text_field( $_POST['price_range_min'] );
        $price_range_max = sanitize_text_field( $_POST['price_range_max'] );

        if ( is_numeric( $price_range_min ) && is_numeric( $price_range_max ) ) {
            update_post_meta( $post_id, 'price_range_min', $price_range_min );
            update_post_meta( $post_id, 'price_range_max', $price_range_max );
        }
    }

    // Validate and sanitize the "rating" custom field
    if ( isset( $_POST['rating'] ) ) {
        $rating = sanitize_text_field( $_POST['rating'] );

        if ( is_numeric( $rating ) && $rating >= 1 && $rating <= 5 ) {
            update_post_meta( $post_id, 'rating', $rating );
        }
    }
}