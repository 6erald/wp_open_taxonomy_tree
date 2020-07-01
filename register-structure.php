<?php

function taxonomytree_register_structure () {


     /**
      * Register new taxonomy "tax_structure" for custom post type "structure"
      */

     register_taxonomy('tax_structure', 'structure', array(
          'hierarchical'           => true,
          'labels'                 => array(
               'name'                   => esc_html__('Taxonomy Structure','tree'),
               'add_new_item'           => esc_html__('Add Taxonomy Structure','tree'),
               'new_item_name'          => esc_html__('New Taxonomy Structure','tree')
          ),
          'show_ui'                => true,
          'show_admin_column'      => true,
          'show_in_rest'           => true,
          'query_var'              => true,
     ));


     /**
      * Register new custom post type "structure"
      */

     register_post_type ('structure', array(
         'labels'   =>  array(
               'name'               => __( 'Taxonomy', 'tree' ),
               'singular_name'      => __( 'Taxonomy', 'tree' ),
               'menu_name'          => __( 'Taxonomy', 'tree' ),
               'name_admin_bar'     => __( 'Taxonomy', 'tree' ),
               'add_new'            => __( 'Add New', 'tree' ),
               'add_new_item'       => __( 'Add New Taxonomy', 'tree' ),
               'new_item'           => __( 'New Taxonomy Element', 'tree' ),
               'edit_item'          => __( 'Edit Taxonomy Element', 'tree' ),
               'view_item'          => __( 'View Taxonomy Element', 'tree' ),
               'all_items'          => __( 'All Taxonomy Elements', 'tree' ),
               'search_items'       => __( 'Search Taxonomy Element', 'tree' ),
               'parent_item_colon'  => __( 'Parent Taxonomy Element:', 'tree' ),
               'not_found'          => __( 'No Taxonomy Elements found.', 'tree' ),
               'not_found_in_trash' => __( 'No Taxonomy Elements found in Trash.', 'tree' )
         ),
         'public'             => true,
         'publicly_queryable' => true,
         'show_ui'            => true,
         'show_in_menu'       => true,
         'query_var'          => false,
         'capability_type'    => 'post',
         'has_archive'        => true,
         'hierarchical'       => false,
         'menu_position'      => 5,
         'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt'),
         'show_in_rest'       => true,
         'rewrite'            => array(
              'slug'               => 'taxonomy',
              'with_front'         => true,
              'pages'              => true,
          ),
     ));

}

add_action ('init', 'taxonomytree_register_structure');

?>
