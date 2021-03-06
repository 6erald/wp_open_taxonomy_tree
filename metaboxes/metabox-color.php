<?php

/**
 * Color Metabox
 */

$tree_taxonomy = get_option("tree_taxonomy");

add_action( 'init', 'taxonomytree_color_register_meta' );

function taxonomytree_color_register_meta() {

    register_meta( 'term', 'tree_color', 'taxonomytree_color_sanitize_hex' );
}

function taxonomytree_color_sanitize_hex( $tree_color ) {

    $tree_color = ltrim( $tree_color, '#' );

    return preg_match( '/([A-Fa-f0-9]{3}){1,2}$/', $tree_color ) ? $tree_color : '000000';
}

function taxonomytree_color_get_term_meta( $term_id, $hash = false ) {

    $tree_color = get_term_meta( $term_id, 'tree_color', true );
    $tree_color = taxonomytree_color_sanitize_hex( $tree_color );

    return $hash && $tree_color ? "#{$tree_color}" : $tree_color;
}

add_action( "{$tree_taxonomy}_add_form_fields",  'tree_color_add_term_field' );

function tree_color_add_term_field() {

    $default = '#000000'; ?>

    <div class="form-field tree-term-color-wrap">
        <label for="tree-term-color"><?php _e( 'Tree Color', 'tree' ); ?></label>
        <?php wp_nonce_field( basename( __FILE__ ), 'tree_term_color_nonce' ); ?>
        <input type="text" name="tree_term_color" id="tree-term-color" value="<?php echo esc_attr( $default ); ?>" class="tree-color-field" />
        <p class="description">
            <?php _e( 'Fill in a hex color with the format #000000 or #000 to style the main branches of your tree. Tree colors only affect categories of the first level (without parent categories).', 'tree' ); ?>
        </p>
    </div>
<?php }

add_action( "{$tree_taxonomy}_edit_form_fields", 'taxonomytree_color_edit_term_field' );

function taxonomytree_color_edit_term_field( $term ) {

    // only terms of first level can display colors in tree
    if ( 0 != $term->parent )
        return;

    $default = '#000000';
    $tree_color   = taxonomytree_color_get_term_meta( $term->term_id, true );

    if ( ! $tree_color ) {
        $tree_color = $default;
    } ?>

    <tr class="form-field tree-term-color-wrap">
        <th scope="row"><label for="tree-term-color"><?php _e( 'Tree Color', 'tree' ); ?></label></th>
        <td>
            <?php wp_nonce_field( basename( __FILE__ ), 'tree_term_color_nonce' ); ?>
            <input type="text" name="tree_term_color" id="tree-term-color" value="<?php echo esc_attr( $tree_color ); ?>" class="tree-color-field" />
            <p class="description">
                <?php _e( 'Fill in a hex color with the format #000000 or #000 to style the main branches of your tree.', 'tree' ); ?>
            </p>
        </td>
    </tr>
<?php }

add_action( "edited_{$tree_taxonomy}", 'taxonomytree_color_save_term_meta' );
add_action( "create_{$tree_taxonomy}", 'taxonomytree_color_save_term_meta' );

function taxonomytree_color_save_term_meta( $term_id ) {

    if ( ! isset( $_POST['tree_term_color_nonce'] ) || ! wp_verify_nonce( $_POST['tree_term_color_nonce'], basename( __FILE__ ) ) )
        return;

    $meta_key       = 'tree_color';
    $old_meta_value = taxonomytree_color_get_term_meta( $term_id );
    $new_meta_value = isset( $_POST['tree_term_color'] ) ? taxonomytree_color_sanitize_hex( $_POST['tree_term_color'] ) : '';

    if ( $old_meta_value && '' === $new_meta_value ){
        delete_term_meta( $term_id, $meta_key );
    } elseif ( $old_meta_value !== $new_meta_value ) {
        update_term_meta( $term_id, $meta_key, $new_meta_value );
    }
} ?>
