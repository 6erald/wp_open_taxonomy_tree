<?php


/**
 * Order Post Metabox
 * Source: https://www.smashingmagazine.com/2011/10/create-custom-post-meta-boxes-wordpress/
 */

add_action( 'load-post.php',     'tree_order_setup_post_meta_box' );
add_action( 'load-post-new.php', 'tree_order_setup_post_meta_box' );

function tree_order_setup_post_meta_box() {

    add_action( 'add_meta_boxes', 'tree_order_add_post_meta_box' );
    add_action( 'save_post', 'tree_order_save_post_meta', 10, 2 );
}

function tree_order_add_post_meta_box() {

    $tree_post_type = get_option( 'tree_post_type' );

    add_meta_box(
        'tree_order_post',
         esc_html__( 'Tree Order', 'tree' ),
        'tree_order_post_meta_box',
         $tree_post_type,
        'side',
        'default'
    );
}

function tree_order_post_meta_box( $post ) {

    $default   = 1;
    $post_meta = get_post_meta( $post->ID, 'tree_order', true);

    if ( ! $post_meta )
        $post_meta = $default; ?>
        
    <!-- TODO: ggf. Beschreibung einfügen -->
    <p><label for="tree-order-post"><?php _e( "ggf. Beschreibung einfügen", 'tree' ); ?></label>
        <?php wp_nonce_field( basename( __FILE__ ), 'tree_order_nonce' ); ?>
        <input type="number" name="tree_order_post" id="tree-order-post" value="<?php echo esc_attr( $post_meta ); ?>" class="tree-order-field" />
        <p class="description">
            <!-- TODO: Beschreibung einfügen -->
            <?php _e( 'Beschreibung einfügen', 'tree' ); ?>
        </p>
    </p>
<?php }

function tree_order_save_post_meta( $post_id, $post ) {

    if ( ! isset( $_POST['tree_order_nonce'] ) || !wp_verify_nonce( $_POST['tree_order_nonce'], basename( __FILE__ ) ) )
        return;

    $meta_key       = 'tree_order';
    $old_meta_value = get_post_meta( $post_id, $meta_key );
    $new_meta_value = isset( $_POST['tree_order_post'] ) ? $_POST['tree_order_post'] : '';

    if ( $old_meta_value && '' === $new_meta_value )
        delete_post_meta( $post_id, $meta_key );

    else if ( $new_meta_value !== $old_meta_value )
        update_post_meta( $post_id, $meta_key, $new_meta_value );
}


/**
 * Order Term Metabox
 * Source: https://gist.github.com/dtbaker/7563c8bdba24b9fdbbb975175f461035
 */

$tree_taxonomy  = get_option( 'tree_taxonomy' );

add_action( "{$tree_taxonomy}_add_form_fields", 'tree_order_add_term_field' );

function tree_order_add_term_field(){ ?>

    <div class="form-field tree-order-term-wrap">
        <label for="tree-order-term"> <?php _e( 'Tree Order', 'tree' ); ?> </label>
        <?php wp_nonce_field( basename( __FILE__ ), 'tree_order_term_nonce' ); ?>
        <input type="number" name="tree_order_term" id="tree-order-term" value="1" class="tree-order-field" />
        <p class="description">
            <!-- TODO: Beschreibung einfügen -->
            <?php _e( 'Beschreibung einfügen', 'tree' ); ?>
        </p>
    </div>
<?php }

add_action( "{$tree_taxonomy}_edit_form_fields",'tree_order_edit_term_field' );

function tree_order_edit_term_field( $term ) {

    $default   = 1;
	$term_meta = get_term_meta( $term->term_id, 'tree_order', true );

    if ( ! $term_meta )
        $term_meta = $default; ?>

	<tr class="form-field tree-order-term-wrap">
        <th scope="row"><label for="tree-order-term"><?php _e( 'Tree Order', 'tree' ); ?></label></th>
        <td>
            <?php wp_nonce_field( basename( __FILE__ ), 'tree_order_term_nonce' ); ?>
            <input type="number" name="tree_order_term" id="tree-order-term" value="<?php echo esc_attr( $term_meta ); ?>" class="tree-order-field" />
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
    if ( ! isset( $_POST['tree_order_term_nonce'] ) || ! wp_verify_nonce( $_POST['tree_order_term_nonce'], basename( __FILE__ ) ) )
        return;

    $meta_key       = 'tree_order';
    $old_meta_value = get_term_meta( $term_id );
    $new_meta_value = isset( $_POST['tree_order_term'] ) ? $_POST['tree_order_term'] : '';

    if ( $old_meta_value && '' === $new_meta_value )
        delete_term_meta( $term_id, $meta_key );

    else if ( $old_meta_value !== $new_meta_value )
        update_term_meta( $term_id, $meta_key, $new_meta_value );
} ?>
