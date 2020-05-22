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
    $cat_meta = get_option( "tree_category_$t_id");
    ?>
    <!-- Form Field Order -->
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="taxonomy_order"><?php _e('Taxonomy Order'); ?></label>
        </th>
        <td>
            <input type="number" name="Cat_meta[taxonomy_order]" id="Cat_meta[taxonomy_order]" size="25" style="width:60%;" value="<?php echo $cat_meta['taxonomy_order'] ? $cat_meta['taxonomy_order'] : ''; ?>"><br />
            <span class="description"><?php _e('Please fill in a number to order the categories.'); ?></span>
        </td>
    </tr>

    <!-- Form Field Color -->
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="cat_color"><?php _e('Taxonomy Color'); ?></label>
        </th>
        <td>
            <input type="color" name="Cat_meta[cat_color]" id="Cat_meta[cat_color]" size="25" style="width:60%;" value="<?php echo $cat_meta['cat_color'] ? $cat_meta['cat_color'] : ''; ?>"><br />
            <span class="description">
                <?php _e('Please fill in Hex Code "#000000".<br>Colors are only displayed in first hierarchical level categories.'); ?>
            </span>
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
        $cat_meta = get_option( "tree_category_$t_id");
        $cat_keys = array_keys($_POST['Cat_meta']);
            foreach ($cat_keys as $key){
            if (isset($_POST['Cat_meta'][$key])){
                $cat_meta[$key] = $_POST['Cat_meta'][$key];
            }
        }
        //save the option array
        update_option( "tree_category_$t_id", $cat_meta );
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
noption_name = tree_category_2 // tree_category_ + term_id
option_value = a:2:{
                    s:6:"taxonomy_order";
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


wp_options > option names:
--------------------------
tree_post_type
tree_taxonomy
tree_category_[id]
tree_post[id]

-------------------------

SELECT *
  FROM `wp_options`
 WHERE `option_name` LIKE 'tree%'

*/

?>
