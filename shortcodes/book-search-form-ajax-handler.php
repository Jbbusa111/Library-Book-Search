<?php 
add_action( 'wp_ajax_book_search', 'book_search_callback' );
add_action( 'wp_ajax_nopriv_book_search', 'book_search_callback' );

function book_search_callback() {
// nonce validation
if ( !isset( $_POST['nonce'] ) || !wp_verify_nonce( $_POST['nonce'], 'book_search_nonce' ) ) {
   wp_send_json_error( 'Invalid nonce' );
}

// Data sanitation and validation
$book_name = filter_var($_POST['book_name'], FILTER_SANITIZE_STRING);
$author = filter_var($_POST['author'], FILTER_SANITIZE_STRING);
$publisher = filter_var($_POST['publisher'], FILTER_SANITIZE_STRING);
$min_price = filter_var($_POST['price_range_min'], FILTER_SANITIZE_NUMBER_INT);
$max_price = filter_var($_POST['price_range_max'], FILTER_SANITIZE_NUMBER_INT);
$rating = filter_var($_POST['rating'], FILTER_SANITIZE_NUMBER_INT);

// Search query
$tax_query = array();
        if(!empty($author)){
            $tax_query[] = array(
                'taxonomy' => 'author',
                'field'    => 'slug',
                'terms'    => $author,
            );
        }
        if(!empty($publisher)){
            $tax_query[] = array(
                'taxonomy' => 'publisher',
                'field'    => 'slug',
                'terms'    => $publisher,
            );
        }
        if(!empty($min_price)){
            $meta_query[] = array(
                'key' => 'price_range_min',
                'value' => array($min_price, $max_price),
                'type' => 'numeric',
                'compare' => 'BETWEEN'
            );
        }
        if(!empty($rating)){
            $meta_query[] = array(
                'key' => 'rating',
                'value' => $rating,
                'compare' => '='
            );
        }
        
        $args = array(
            'post_type' => 'book',
            'tax_query' => $tax_query,
            'meta_query' => $meta_query,
			'posts_per_page' => 5,
            'paged' => get_query_var('paged'),
           

        );
        
    
    

if (!empty($book_name)) {
   $args['s'] = $book_name;
}
$query = new WP_Query($args);

// Pagination
$big = 999999999;
$pagination = paginate_links(array(
    'base' => str_replace($big, '%#%', get_pagenum_link($big)),
    'format' => '?paged=%#%',
    'current' => max(1, get_query_var('paged')),
    'total' => $query->max_num_pages
));

// Ajax response
if ($query->have_posts()) {
    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Book Title</th>';
    echo '<th>Price</th>';
    echo '<th>Rating</th>';
    echo '<th>Author</th>';
    echo '<th>Publisher</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    while ($query->have_posts()) {
        $query->the_post();
        $price = get_post_meta(get_the_ID(), 'price_range_min', true);
        $rating = get_post_meta(get_the_ID(), 'rating', true);
        $link = '<a href="' . get_the_permalink() . '">' . get_the_title() . '</a>';
        echo '<tr>';
        echo '<td>' . $link . '</td>';
        echo '<td>' . $price . '</td>';
        echo '<td>' . $rating . '</td>';
        echo '<td>' . get_the_term_list(get_the_ID(), 'author', '', ', ') . '</td>';
        echo '<td>' . get_the_term_list(get_the_ID(), 'publisher', '', ', ') . '</td>';
        echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '<div class="pagination">' . $pagination . '</div>';
        } else {
        echo 'No books found.',$publisher;
        }
        
        // Basic styling
        echo '<style>
        table {
        width: 100%;
        border-collapse: collapse;
        }
        th, td {
        border: 1px solid #dddddd;
        padding: 8px;
        text-align: left;
        }
        th {
        background-color: #dddddd;
        }
        .pagination {
        text-align: center;
        }
        </style>';

}
?>