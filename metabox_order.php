<?php


/**
 * Order Post Metabox
 */

/**
 * Source: https://www.smashingmagazine.com/2011/10/create-custom-post-meta-boxes-wordpress/
 */


/* Fire our meta box setup function on the post editor screen. */
add_action( 'load-post.php',     'tree_order_setup_post_meta_box' );
add_action( 'load-post-new.php', 'tree_order_setup_post_meta_box' );

/* Meta box setup function. */
function tree_order_setup_post_meta_box() {

    /* Add meta boxes on the 'add_meta_boxes' hook. */
    add_action( 'add_meta_boxes', 'tree_order_add_post_meta_box' );

    /* Save post meta on the 'save_post' hook. */
    add_action( 'save_post', 'tree_order_save_post_meta', 10, 2 );
}

/* Create one or more meta boxes to be displayed on the post editor screen. */
function tree_order_add_post_meta_box() {

    $tree_post_type = get_option( 'tree_post_type' );

    add_meta_box(
        'tree-order-post',                         // Unique ID
         esc_html__( 'Taxonomy-Tree Post Order', '' ), // Title
        'tree_order_post_meta_box',                    // Callback function
         $tree_post_type,                              // Admin page (or post type)
        'side',                                        // Context
        'default'                                      // Priority
    );
}

/* Display the post meta box. */
function tree_order_post_meta_box( $post ) {

    $post_meta = $post && !empty( $post->ID) ? get_post_meta( $post->ID, 'tree_order', true) : false;

    wp_nonce_field( basename( __FILE__ ), 'tree_order_nonce' ); ?>

    <!-- TODO: Form-Felder vereinheitlichen -->
    <p><label for="tree-order-post">
            <?php _e( "Change the post order to structure them in the tree.", '' ); ?>
        </label>
        <br />
        <input class="widefat" type="number" size="30" name="tree-order-post" id="tree-order-post" value="<?php echo $post_meta ? $post_meta : 1; ?>" />
    </p>

<?php }

/* Save the meta box’s post metadata. */
function tree_order_save_post_meta( $post_id, $post ) {

  /* Verify the nonce before proceeding. */
  if ( !isset( $_POST['tree_order_nonce'] ) ||
       !wp_verify_nonce( $_POST['tree_order_nonce'], basename( __FILE__ ) ) )
     return $post_id;

  /* Get the post type object. */
  $post_type = get_post_type_object( $post->post_type );

  /* Check if the current user has permission to edit the post. */
  if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
    return $post_id;

  /* Get the posted data and sanitize it for use as an HTML class. */
  $new_meta_value = ( isset( $_POST['tree-order-post'] ) ? sanitize_html_class( $_POST['tree-order-post'] ) : ’ );

  /* Get the meta key. */
  $meta_key = 'tree_order';

  /* Get the meta value of the custom field key. */
  $meta_value = get_post_meta( $post_id, $meta_key, true );

  /* If a new meta value was added and there was no previous value, add it. */
  if ( $new_meta_value && ’ == $meta_value )
    add_post_meta( $post_id, $meta_key, $new_meta_value, true );

  /* If the new meta value does not match the old value, update it. */
  elseif ( $new_meta_value && $new_meta_value != $meta_value )
    update_post_meta( $post_id, $meta_key, $new_meta_value );

  /* If there is no new meta value but an old value exists, delete it. */
  elseif ( ’ == $new_meta_value && $meta_value )
    delete_post_meta( $post_id, $meta_key, $meta_value );
}


/**
 * Order Term Metabox
 */

 /**
  * Source: https://gist.github.com/dtbaker/7563c8bdba24b9fdbbb975175f461035
  * TODO: Form fields anpassen
  * TODO: bezeichnungen anpassen
  */

$tree_taxonomy  = get_option( 'tree_taxonomy' );

add_action( "{$tree_taxonomy}_add_form_fields", 'tree_order_add_term_field' );

function tree_order_add_term_field(){ ?>

    <div class="form-field tree-term-order-wrap">
        <label for="tree-term-order"> <?php _e( 'Tree Order', 'tree' ); ?> </label>
        <?php wp_nonce_field( basename( __FILE__ ), 'tree_term_order_nonce' ); ?>
        <input type="number" name="tree_term_order" id="tree-term-order" value="1" class="tree-order-field" data-default-color="#ffffff" />
        <p class="description">
            <!-- TODO: Beschreibung einfügen -->
            <?php _e( 'Beschreibung einfügen', 'tree' ); ?>
        </p>
    </div>
<?php }

add_action( "{$tree_taxonomy}_edit_form_fields",'tree_order_edit_term_field' );

function tree_order_edit_term_field( $term ) {

	// Retrieve the existing value(s) for this meta field.
    $default   = 1;
	$term_order = $term && !empty( $term->term_id ) ? get_term_meta( $term->term_id, 'tree_order', true ) : false; ?>

	<tr class="form-field tree-term-order-wrap">
        <th scope="row"><label for="tree-term-order"><?php _e( 'Tree Order', 'tree' ); ?></label></th>
        <td>
            <?php wp_nonce_field( basename( __FILE__ ), 'tree_term_order_nonce' ); ?>
            <input type="number" name="tree_term_order" id="tree-term-order" value="<?php echo esc_attr( $term_order ); ?>" class="tree-color-field" data-default-color="<?php echo esc_attr( $default ); ?>" />
            <p class="description">
                <!-- TODO: Beschreibung einfügen -->
                <?php _e( 'Beschreibung einfügen', 'tree' ); ?>
            </p>
		</td>
	</tr>

<?php }

add_action( "edited_{$tree_taxonomy}", 'tree_order_save_term_meta' );
add_action( "create_{$tree_taxonomy}", 'tree_order_save_term_meta' );

function tree_order_save_term_meta( $term_id ) {
    if ( ! isset( $_POST['tree_term_order_nonce'] ) || ! wp_verify_nonce( $_POST['tree_term_order_nonce'], basename( __FILE__ ) ) )
        return;

    $old_term_order = get_term_meta( $term_id );
    $new_term_order = isset( $_POST['tree_term_order'] ) ? $_POST['tree_term_order'] : '';

    if ( $old_term_order && '' === $new_term_order )
        delete_term_meta( $term_id, 'tree_order' );

    else if ( $old_term_order !== $new_term_order )
        update_term_meta( $term_id, 'tree_order', $new_term_order );
}

?>
