<?php

// Shortcode for search form
function librarysearch_form_shortcode() {
    ob_start();
    ?>
    <div style="border: 1px solid black; padding: 10px;">
    <h2 style="text-align: center;">Book Search</h2>
    <form action="<?php echo esc_url(home_url('/')); ?>" method="get" id="book-search-form">
    <input type="hidden" name="nonce" id="nonce" value="<?php echo wp_create_nonce('book_search_nonce'); ?>">
        <div style="display: flex;">
            <div style="flex: 1; padding: 5px;">
                <label for="book_name">Book Name:</label>
                <input type="text" name="book_name" id="book_name" placeholder="Enter book name">
            </div>
            <div style="flex: 1; padding: 5px;">
                <label for="book_author">Book Author:</label>
                <input type="text" name="book_author" id="book_author" placeholder="Enter book author">
            </div>
        </div>
        <div style="display: flex;">
            <div style="flex: 1; padding: 5px;">
                <label for="book_publisher">Book Publisher:</label>
                <select name="book_publisher" id="book_publisher">
                    <option value="">Select Publisher</option>
                    <?php
                    // Fetch publisher data from database
                    $publishers = fetch_publishers_from_db();
                    foreach ($publishers as $publisher) {
                        echo '<option value="' . $publisher->name . '">' . $publisher->name . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div style="flex: 1; padding: 5px;">
            <label for="price-range">Price Range:</label>
    <input type="text" id="price-range" readonly>
    <div id="price-slider"></div>
    
</div>
        </div>
        <div style="display: flex;">
            <div style="flex: 1; padding: 5px;">
                <label for="book_rating">Book Rating:</label>
                <select name="book_rating" id="book_rating">
                    <option value="">Select Rating</option>
                    <?php for ($i = 1; $i <= 5; $i++) {
                        echo '<option value="' . $i . '">' . $i . '</option>';
                    } ?>
                </select>
            </div>
        </div>
        <div style="text-align: center;">
            <input type="submit" value="Search">
        </div>
    </form>
</div>
<div id="book-search-result"></div>
<script>
    jQuery(document).ready(function($) {
        $("#book-search-form").submit(function(e) {
            e.preventDefault();
            var book_name = $("#book_name").val();
            var author = $("#book_author").val();
            var publisher = $("#book_publisher").val();
            var price_range_min = $("#price-slider").slider("values", 0);
            var price_range_max = $("#price-slider").slider("values", 1);
            var book_rating = $("#book_rating").val();
            var nonce = $("#nonce").val();
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                data: {
                    action: 'book_search',
                    book_name: book_name,
                    author: author,
                    publisher: publisher,
                    price_range_min: price_range_min,
                    price_range_max: price_range_max,
                    book_rating: book_rating,
                    nonce: nonce
                },
                success: function(response) {
                    $('#book-search-result').html(response);
                    console.log(response);
                },
                error: function(xhr, status, error) {
                    console.log(error);
                }
            });
        });
    $("#price-slider").slider({
        range: true,
        min: 0,
        max: 1000,
        values: [0, 1000],
        slide: function(event, ui) {
            $("#price-range").val("$" + ui.values[0] + " - $" + ui.values[1]);
        }
  });
  $("#price-range").val("$" + $("#price-slider").slider("values", 0) +" - $" + $("#price-slider").slider("values", 1));
});
</script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'library_book_search_form', 'librarysearch_form_shortcode' );
// Function to fetch publishers from the database
function fetch_publishers_from_db() {
    $publishers = get_terms( array(
        'taxonomy' => 'publisher',
        'hide_empty' => false,
    ) );
    return $publishers;
}
function librarysearch_enqueue_scripts() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-slider' );
    wp_enqueue_style( 'jquery-ui-slider-style', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css' );
    //wp_enqueue_script( 'library-book-search', plugin_dir_url( __FILE__ ) . 'library-book-search.js', array( 'jquery', 'jquery-ui-slider' ), '1.0', true );
  }
  add_action( 'wp_enqueue_scripts', 'librarysearch_enqueue_scripts' );