<?php
/**
* Plugin Name: PW Book List Custom Post Type 
* Version:  1.0
* Author: Peter Ward
*/

add_action( 'init', 'create_custombooks' );
function create_custombooks() {    
    register_post_type( 'books_list',
        array(
            'labels' => array(
                'name'               => 'Books',
                'singular_name'      => 'Book',
                'menu_name'          => 'Book',
                'name_admin_bar'     => 'Book',
                'add_new'            => 'Add New',
                'add_new_item'       => 'Add New A Book',
                'new_item'           => 'New Book',
                'edit_item'          => 'Edit Book',
                'view_item'          => 'View Book',
                'all_items'          => 'All Books',
                'search_items'       => 'Search Books',
                'parent_item_colon'  => 'Parent Books:',
                'not_found'          => 'No books found.',
                'not_found_in_trash' => 'No books found in Bin.'
            ),
 
            'description' => 'My Books',
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'        => true,
            'rewrite'         => array( 'slug' => 'books' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'      => false,
            'menu_position'     => 20,
            'supports' => array( 'title', 'editor', 'comments', 'thumbnail', 'custom-fields' ),
            'taxonomies' => array( '' ),
            'menu_icon' => plugins_url( 'images/image.png', __FILE__ ),
            'has_archive' => true
        )
    );
}

add_action( 'admin_init', 'book_admin' );
function book_admin() {
    add_meta_box( 
        'book_meta_box',
        'Book Details',
        'display_book_list_meta_box',
        'books_list', 
        'normal', 
        'high'
    );
}

function display_book_list_meta_box( $book_submit ) {
    //"ISBN", "Number of Pages", "Publisher" and "First Publication Date"
    $book_isbn = esc_html( get_post_meta( $book_submit->ID, 'book_isbn_meta', true ) );
    $book_pageqty = intval( get_post_meta( $book_submit->ID, 'book_pageqty_meta', true ) );
    $book_publisher = esc_html( get_post_meta( $book_submit->ID, 'book_publisher_meta', true ) );
    $book_pubdate = esc_html( get_post_meta( $book_submit->ID, 'book_pubdate_meta', true ) );
    echo '<input type="hidden" name="pw_book_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';
    ?>

    <table>
        <tr>
            <td style="width: 100%">ISBN</td>
            <td><input type="text" size="80" name="book_isbn" value="<?php echo $book_isbn; ?>" /></td>
        </tr>
        <tr>
            <td style="width: 100%">Number of Pages</td>
            <td><input type="text" size="80" name="book_pageqty" value="<?php echo $book_pageqty; ?>" /></td>
        </tr>
        <tr>
            <td style="width: 100%">Publisher</td>
            <td><input type="text" size="80" name="book_publisher" value="<?php echo $book_publisher; ?>" /></td>
        </tr>
        <tr>
            <td style="width: 100%">First Publication Date</td>
            <td><input type="text" size="80" name="book_pubdate" value="<?php echo $book_pubdate; ?>" /></td>
        </tr>        

    </table>
    <?php
}

add_action( 'save_post', 'add_book_fields', 10, 2 );
function add_book_fields( $book_id, $book ) {
    if (!wp_verify_nonce($_POST['pw_book_nonce'], basename(__FILE__))) {
        return $book_id;
    }

    if ( $book->post_type == 'books_list' ) {
        if ( isset( $_POST['book_isbn'] ) && $_POST['book_isbn'] != '' ) {
            update_post_meta( $book_id, 'book_isbn_meta', $_POST['book_isbn'] );
        }
        if ( isset( $_POST['book_pageqty'] ) && $_POST['book_pageqty'] != '' ) {
            update_post_meta( $book_id, 'book_pageqty_meta', $_POST['book_pageqty'] );
        }
        if ( isset( $_POST['book_publisher'] ) && $_POST['book_publisher'] != '' ) {
            update_post_meta( $book_id, 'book_publisher_meta', $_POST['book_publisher'] );
        }
        if ( isset( $_POST['book_pubdate'] ) && $_POST['book_pubdate'] != '' ) {
            update_post_meta( $book_id, 'book_pubdate_meta', $_POST['book_pubdate'] );
        }        
    }
}

/* Add the ISBN to the list of posts  */
add_filter( 'manage_books_list_posts_columns', 'set_books_columns' );
function set_books_columns($columns) {
    $columns['book_isbn'] = __( 'ISBN' );
    // add more columns here from Types e.g First Published Date
    return $columns;
}

add_action( 'manage_books_list_posts_custom_column' , 'set_book_column', 10, 2 );
function set_book_column( $column, $post_id ) {
    switch ( $column ) {
        case 'book_isbn' :
            $term = get_post_meta( $post_id, 'book_isbn_meta', true );
            if ( is_string( $term ) )
                echo $term;
            else
                _e( 'Unable to return ISBN '.$post_id );
            break;
    }
}
