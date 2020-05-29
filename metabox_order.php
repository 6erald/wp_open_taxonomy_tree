<?php


/**
 * Order Post Metabox
 */

/**
 * -------------------------- NOTES --------------------------
 * Created with:
 * https://www.smashingmagazine.com/2011/10/create-custom-post-meta-boxes-wordpress/
 */

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
        'tree-taxonomy-order',                         // Unique ID
         esc_html__( 'Taxonomy-Tree Post Order', '' ), // Title
        'tree_order_post_meta_box',                    // Callback function
         $tree_post_type,                              // Admin page (or post type)
        'side',                                        // Context
        'default'                                      // Priority
    );
}

/* Display the post meta box. */
function tree_order_post_meta_box( $post ) {

    $post_meta = $post && !empty( $post->ID) ?
                 get_post_meta( $post->ID, 'tree_post_order', true) :
                 false;

    // TODO: nonce_field vereinheitlichen
    wp_nonce_field( basename( __FILE__ ), 'tree_post_order_nonce' ); ?>

    <!-- TODO: Form-Felder vereinheitlichen -->
    <p><label for="tree-taxonomy-order">
            <?php _e( "Change the post order to structure them in the tree.", '' ); ?>
        </label>
        <br />
        <input class="widefat" type="number" size="30"
               name="tree-taxonomy-order" id="tree-taxonomy-order"
               value="<?php echo $post_meta ? $post_meta : 1; ?>" />
    </p>

<?php }

/* Save the meta box’s post metadata. */
function tree_order_save_post_meta( $post_id, $post ) {

  /* Verify the nonce before proceeding. */
  if ( !isset( $_POST['tree_post_order_nonce'] ) ||
       !wp_verify_nonce( $_POST['tree_post_order_nonce'], basename( __FILE__ ) ) )
     return $post_id;

  /* Get the post type object. */
  $post_type = get_post_type_object( $post->post_type );

  /* Check if the current user has permission to edit the post. */
  if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
    return $post_id;

  /* Get the posted data and sanitize it for use as an HTML class. */
  $new_meta_value = ( isset( $_POST['tree-taxonomy-order'] ) ? sanitize_html_class( $_POST['tree-taxonomy-order'] ) : ’ );

  /* Get the meta key. */
  $meta_key = 'tree_post_order';

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

/* Fire our meta box setup function on the post editor screen. */
add_action( 'load-post.php',     'tree_order_setup_post_meta_box' );
add_action( 'load-post-new.php', 'tree_order_setup_post_meta_box' );

/**
 * Order Term Metabox
 */

/**
 * -------------------------- NOTES --------------------------
 * From: https://gist.github.com/dtbaker/7563c8bdba24b9fdbbb975175f461035
 */

function tree_order_setup_term_meta_box() {

    $tree_taxonomy  = get_option( 'tree_taxonomy' );

    add_action( $tree_taxonomy . '_add_form_fields', 'tree_order_edit_term_field', 10 );
    add_action( $tree_taxonomy . '_edit_form_fields','tree_order_edit_term_field', 10 );

    add_action( 'edited_' . $tree_taxonomy, 'tree_order_save_term_meta', 10, 2 );
    add_action( 'create_' . $tree_taxonomy, 'tree_order_save_term_meta', 10, 2 );

}

function tree_order_edit_term_field( $term ) {

	// Retrieve the existing value(s) for this meta field.
	$term_meta = $term && !empty( $term->term_id ) ?
                 get_term_meta( $term->term_id, 'tree_taxonomy_order', true ) :
                 false;

    wp_nonce_field( 'update_term_meta', 'term_meta_nonce' ); ?>

	<tr class="form-field">
        <th scope="row" valign="top">
            <label for="term_meta[tree_taxonomy_order]">
                <?php _e( "Taxonomy Order", '' ); ?>
            </label>
        </th>
        <td><input type="number"
                   name="term_meta[tree_taxonomy_order]" id="term_meta[tree_taxonomy_order]"
                   value="<?php echo $term_meta ? $term_meta : 1;?>" />
                   <!-- NOTE: term_meta[tree_taxonomy_order] really necessary? -->
            <p class="description">
                <?php _e( 'Change the post order to structure them in the tree.', '' ); ?>
            </p>
		</td>
	</tr>

<?php }

function tree_order_save_term_meta( $term_id ) {
	if (
		isset( $_POST['term_meta'] ) && is_array( $_POST['term_meta'] ) &&
		! empty( $_POST['term_meta_nonce'] ) && wp_verify_nonce( $_POST['term_meta_nonce'], 'update_term_meta' )
	) {
		foreach ( $_POST['term_meta'] as $key => $value ) {
			update_term_meta( $term_id, $key, sanitize_text_field( $value ) );
		}
	}
}

add_action( 'edit-tags.php',      'tree_order_setup_term_meta_box' );
add_action( 'load-edit-tags.php', 'tree_order_setup_term_meta_box' );


?>
