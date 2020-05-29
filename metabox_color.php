<?php

/**
 * Color Metabox
 */

/**
 * Source: https://themehybrid.com/weblog/introduction-to-wordpress-term-meta
 * IDEA: colorpicker eifügen https://themehybrid.com/weblog/introduction-to-wordpress-term-meta
 * IDEA: auch mit hilfe von https://wordpress.org/plugins/wp-term-colors/
 * NOTE: check 'wp-admin/edit-tag-form.php' and 'admin/edit-tags.php' to find the most appropriate hook for your use case
 * TODO: check "register_meta" & "delete_term_meta" wirklich nötig?
 */

$tree_taxonomy = get_option("tree_taxonomy");

add_action( 'init', 'tree_color_register_meta' );

function tree_color_register_meta() {

    register_meta( 'term', 'tree_color', 'tree_color_sanitize_hex' );
}

function tree_color_sanitize_hex( $color ) {

    $color = ltrim( $color, '#' );

    return preg_match( '/([A-Fa-f0-9]{3}){1,2}$/', $color ) ? $color : '';
}

function tree_color_get_term_meta( $term_id, $hash = false ) {

    $color = get_term_meta( $term_id, 'tree_color', true );
    $color = tree_color_sanitize_hex( $color );

    return $hash && $color ? "#{$color}" : $color;
}

add_action( "{$tree_taxonomy}_add_form_fields",  'tree_color_add_term_field' );

function tree_color_add_term_field() { ?>

    <div class="form-field tree-term-color-wrap">
        <label for="tree-term-color"><?php _e( 'Tree Color', 'tree' ); ?></label>
        <?php wp_nonce_field( basename( __FILE__ ), 'tree_term_color_nonce' ); ?>
        <input type="text" name="tree_term_color" id="tree-term-color" value="#ffffff" class="tree-color-field" data-default-color="#ffffff" />
        <p class="description">
            <!-- TODO: Beschreibung einfügen -->
            <?php _e( 'Beschreibung einfügen', 'tree' ); ?>
        </p>
    </div>
<?php }

add_action( "{$tree_taxonomy}_edit_form_fields", 'tree_color_edit_term_field' );

function tree_color_edit_term_field( $term ) {

    // only terms of first level can display colors
    if( 0 != $term->parent )
        return;

    $default = '#ffffff';
    $color   = tree_color_get_term_meta( $term->term_id, true );

    if ( ! $color )
        $color = $default; ?>

    <tr class="form-field tree-term-color-wrap">
        <th scope="row"><label for="tree-term-color"><?php _e( 'Tree Color', 'tree' ); ?></label></th>
        <td>
            <?php wp_nonce_field( basename( __FILE__ ), 'tree_term_color_nonce' ); ?>
            <input type="text" name="tree_term_color" id="tree-term-color" value="<?php echo esc_attr( $color ); ?>" class="tree-color-field" data-default-color="<?php echo esc_attr( $default ); ?>" />
            <p class="description">
                <!-- TODO: Beschreibung einfügen -->
                <?php _e( 'Beschreibung einfügen', 'tree' ); ?>
            </p>
        </td>
    </tr>
<?php }

add_action( "edit_{$tree_taxonomy}",   'tree_color_save_term_meta' );
add_action( "create_{$tree_taxonomy}", 'tree_color_save_term_meta' );

function tree_color_save_term_meta( $term_id ) {

    if ( ! isset( $_POST['tree_term_color_nonce'] ) || ! wp_verify_nonce( $_POST['tree_term_color_nonce'], basename( __FILE__ ) ) )
        return;

    $old_color = tree_color_get_term_meta( $term_id );
    $new_color = isset( $_POST['tree_term_color'] ) ? tree_color_sanitize_hex( $_POST['tree_term_color'] ) : '';

    if ( $old_color && '' === $new_color )
        delete_term_meta( $term_id, 'tree_color' );

    else if ( $old_color !== $new_color )
        update_term_meta( $term_id, 'tree_color', $new_color );
}


?>
