<?php

/**
 * Color Metabox
 */

/**
 * -------------------------- NOTES --------------------------
 * TUTORIAL: https://themehybrid.com/weblog/introduction-to-wordpress-term-meta
 * IDEA: colorpicker eifügen https://themehybrid.com/weblog/introduction-to-wordpress-term-meta
 * IDEA: auch mit hilfe von https://wordpress.org/plugins/wp-term-colors/
 * NOTE: check 'wp-admin/edit-tag-form.php' and 'admin/edit-tags.php' to find the most appropriate hook for your use case
 */

function tree_color_setup_term_meta_box() {

    $tree_taxonomy = get_option("tree_taxonomy");

    // TODO: wofür ist das hier nötig?
    // add_action( 'init', 'tree_color_register_meta' );

    add_action( $tree_taxonomy.'_add_form_fields',  'tree_color_add_term_field' );
    add_action( $tree_taxonomy.'_edit_form_fields', 'tree_color_edit_term_field' );

    add_action( 'edit_'.$tree_taxonomy,  'tree_color_save_term_meta' );
    add_action( 'create'.$tree_taxonomy, 'tree_color_save_term_meta' );

}

// function tree_color_register_meta() {
//
//     register_meta( 'term', 'tree_color', 'tree_color_sanitize_hex' );
// }

function tree_color_sanitize_hex( $color ) {

    $color = ltrim( $color, '#' );

    return preg_match( '/([A-Fa-f0-9]{3}){1,2}$/', $color ) ? $color : '';
}

function tree_color_get_term_meta( $term_id, $hash = false ) {

    $color = get_term_meta( $term_id, 'tree_color', true );
    $color = tree_color_sanitize_hex( $color );

    return $hash && $color ? "#{$color}" : $color;
}

function tree_color_add_term_field() {

    wp_nonce_field( basename( __FILE__ ), 'jt_term_color_nonce' ); ?>

    <div class="form-field jt-term-color-wrap">
        <label for="jt-term-color"><?php _e( 'Color', 'jt' ); ?></label>
        <input type="text" name="jt_term_color" id="jt-term-color" value="" class="jt-color-field" data-default-color="#ffffff" />
    </div>
<?php }

function tree_color_edit_term_field( $term ) {

    $default = '#ffffff';
    $color   = tree_color_get_term_meta( $term->term_id, true );

    if ( ! $color )
        $color = $default; ?>

    <tr class="form-field jt-term-color-wrap">
        <th scope="row"><label for="jt-term-color"><?php _e( 'Color', 'jt' ); ?></label></th>
        <td>
            <?php wp_nonce_field( basename( __FILE__ ), 'jt_term_color_nonce' ); ?>
            <input type="text" name="jt_term_color" id="jt-term-color" value="<?php echo esc_attr( $color ); ?>" class="jt-color-field" data-default-color="<?php echo esc_attr( $default ); ?>" />
        </td>
    </tr>
<?php }

function tree_color_save_term_meta( $term_id ) {

    if ( ! isset( $_POST['jt_term_color_nonce'] ) || ! wp_verify_nonce( $_POST['jt_term_color_nonce'], basename( __FILE__ ) ) )
        return;

    $old_color = tree_color_get_term_meta( $term_id );
    $new_color = isset( $_POST['jt_term_color'] ) ? tree_color_sanitize_hex( $_POST['jt_term_color'] ) : '';

    if ( $old_color && '' === $new_color )
        delete_term_meta( $term_id, 'tree_color' );


    else if ( $old_color !== $new_color )
        update_term_meta( $term_id, 'tree_color', $new_color );
}

add_action( 'edit-tags.php',      'tree_color_setup_term_meta_box' );
add_action( 'load-edit-tags.php', 'tree_color_setup_term_meta_box' );

?>
