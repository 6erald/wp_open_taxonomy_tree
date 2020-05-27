<?php

/*
 * TUTORIAL: https://themehybrid.com/weblog/introduction-to-wordpress-term-meta
 * IDEA: colorpicker eifügen https://themehybrid.com/weblog/introduction-to-wordpress-term-meta
 * IDEA: auch mit hilfe von https://wordpress.org/plugins/wp-term-colors/
 * NOTE: check 'wp-admin/edit-tag-form.php' and 'admin/edit-tags.php' to find the most appropriate hook for your use case
 */

$tree_taxonomy = get_option("tree_taxonomy");

add_action( 'init', 'jt_register_meta' );

function jt_register_meta() {

    register_meta( 'term', 'color', 'jt_sanitize_hex' );
}

function jt_sanitize_hex( $color ) {

    $color = ltrim( $color, '#' );

    return preg_match( '/([A-Fa-f0-9]{3}){1,2}$/', $color ) ? $color : '';
}

function jt_get_term_color( $term_id, $hash = false ) {

    $color = get_term_meta( $term_id, 'color', true );
    $color = jt_sanitize_hex( $color );

    return $hash && $color ? "#{$color}" : $color;
}

add_action( $tree_taxonomy.'_add_form_fields', 'ccp_new_term_color_field' );

function ccp_new_term_color_field() {

    wp_nonce_field( basename( __FILE__ ), 'jt_term_color_nonce' ); ?>

    <div class="form-field jt-term-color-wrap">
        <label for="jt-term-color"><?php _e( 'Color', 'jt' ); ?></label>
        <input type="text" name="jt_term_color" id="jt-term-color" value="" class="jt-color-field" data-default-color="#ffffff" />
    </div>
<?php }

add_action( $tree_taxonomy.'_edit_form_fields', 'ccp_edit_term_color_field' );

function ccp_edit_term_color_field( $term ) {

    $default = '#ffffff';
    $color   = jt_get_term_color( $term->term_id, true );

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

add_action( 'edit_'.$tree_taxonomy,  'jt_save_term_color' );
add_action( 'create'.$tree_taxonomy, 'jt_save_term_color' );

function jt_save_term_color( $term_id ) {

    if ( ! isset( $_POST['jt_term_color_nonce'] ) || ! wp_verify_nonce( $_POST['jt_term_color_nonce'], basename( __FILE__ ) ) )
        return;

    $old_color = jt_get_term_color( $term_id );
    $new_color = isset( $_POST['jt_term_color'] ) ? jt_sanitize_hex( $_POST['jt_term_color'] ) : '';

    if ( $old_color && '' === $new_color )
        delete_term_meta( $term_id, 'color' );

    else if ( $old_color !== $new_color )
        update_term_meta( $term_id, 'color', $new_color );
}


?>