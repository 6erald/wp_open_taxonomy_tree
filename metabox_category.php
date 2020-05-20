<?php

/*
 * From: https://en.bainternet.info/wordpress-category-extra-fields/
 *
 */

$tree_taxonomy = get_option("tree_taxonomy");

//add extra fields to category edit form hook
add_action ( $tree_taxonomy . '_edit_form_fields', 'extra_category_fields');
add_action ( $tree_taxonomy . '_add_form_fields', 'extra_category_fields');

//add extra fields to category edit form callback function
function extra_category_fields( $tag ) {    //check for existing featured ID
    $t_id = $tag->term_id;
    $cat_meta = get_option( "category_$t_id");
    ?>
    <!-- Form Field Order -->
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="extra1"><?php _e('extra field'); ?></label>
        </th>
        <td>
            <input type="text" name="Cat_meta[extra1]" id="Cat_meta[extra1]" size="25" style="width:60%;" value="<?php echo $cat_meta['extra1'] ? $cat_meta['extra1'] : ''; ?>"><br />
            <span class="description"><?php _e('extra field'); ?></span>
        </td>
    </tr>

    <!-- Form Field Color -->
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="cat_color"><?php _e('Taxonomy Color'); ?></label>
        </th>
        <td>
            <input type="text" name="Cat_meta[cat_color]" id="Cat_meta[cat_color]" size="25" style="width:60%;" value="<?php echo $cat_meta['cat_color'] ? $cat_meta['cat_color'] : ''; ?>"><br />
            <span class="description"><?php _e('Please fill in Hex Code "#000000". Colors are only displayed in first hierarchical level categories. '); ?></span>
        </td>
    </tr>
    <?php
}

add_action ( 'edited_' . $tree_taxonomy, 'save_extra_category_fileds');
add_action ( 'create_' . $tree_taxonomy, 'save_extra_category_fileds');

// save extra category extra fields callback function
function save_extra_category_fileds( $term_id ) {
    if ( isset( $_POST['Cat_meta'] ) ) {
        $t_id = $term_id;
        $cat_meta = get_option( "category_$t_id");
        $cat_keys = array_keys($_POST['Cat_meta']);
            foreach ($cat_keys as $key){
            if (isset($_POST['Cat_meta'][$key])){
                $cat_meta[$key] = $_POST['Cat_meta'][$key];
            }
        }
        //save the option array
        update_option( "category_$t_id", $cat_meta );
    }
}




/*


https://codex.wordpress.org/Database_Description#Table:_wp_options

wp_terms:
---------
name    = Category 1
term_id = 2

wp_options:
-----------
option_id    = 397
noption_name = category_2 // category_ + term_id
option_value = a:2:{
                    s:6:"extra1";
                    s:4:"test";
                    s:6:"cat_color";
                    s:6:"test 2";
                }

                description:
                -----------
                array : size 2 : {
                    string : size 10 : "key-string";
                    string : size 12 : "value-string";
                }
*/

?>
