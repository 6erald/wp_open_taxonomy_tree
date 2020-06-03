<?php
/*

Plugin Name: open taxonomy tree
Plugin URI:
Author: Gerald Wagner

*/


function taxonomytree_shortcode( $atts ) {

	$atts = shortcode_atts( array(
		'post_type'  => 'posts',
		'taxonomy'   => 'category'
	), $atts );

 	// save 'post_type' and 'taxonomy' in wp_options to access later
	update_option( 'tree_post_type', $atts['post_type'] );
	update_option( 'tree_taxonomy',  $atts['taxonomy'] );

	return "<div id ='categorytree'></div>";
}

add_shortcode( 'taxonomy_d3', 'taxonomytree_shortcode' );

function taxonomytree_scripts( ) {

	wp_register_script( 'categoryd3tree_js', plugins_url( 'tree.js', __FILE__ ), array( 'd3_js' ) );
	wp_enqueue_script(  'categoryd3tree_js' );

	wp_register_script( 'd3_js', plugins_url( 'd3.v3.min.js', __FILE__ ), array( 'jquery' ) );
    wp_enqueue_script(  'd3_js' );

	wp_register_style( 'style_css', plugins_url( 'style.css', __FILE__ ) );
    wp_enqueue_style(  'style_css' );

	// declare the URL to the file that handles the AJAX request ( wp-admin/admin-ajax.php )
	wp_localize_script( 'categoryd3tree_js', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}

add_action( 'wp_footer', 'taxonomytree_scripts' );

function categorytree_callback( ) {

	$tree_taxonomy      = get_option( 'tree_taxonomy' );
	$tree_taxonomy_arr  = get_taxonomy( $tree_taxonomy );
	$tree_taxonomy_name = $tree_taxonomy_arr->labels->singular_name;

	//process plugin
	$tree = array(
		'parent'      => -1,
		'name'        => $tree_taxonomy_name,
	);

	// generate the response
	//$tree['children']=buildtree( );
	$tree['children'] = buildrectree( 0 );
	$response = json_encode( $tree );

	// response output
	header( "Content-Type: application/json" );
	echo $response;
	die( );
}

function buildrectree( $root ) {

	$tree_post_type = get_option( 'tree_post_type' );
	$tree_taxonomy  = get_option( 'tree_taxonomy' );

	$args = array(
		'parent'      => $root,
		'meta_key'    => 'tree_order',
		'orderby'     => 'meta_value_num',
		'order'       => 'ASC',
		'hide_empty'  => 0,
		'taxonomy'    => $tree_taxonomy,
	);

	$tree_terms = get_categories( $args );

	foreach ( $tree_terms as $tree_term ) {

		$tree_term->children = buildrectree( $tree_term->term_id );

		// push blogposts into $tree in category of last level
		if ( empty ( $tree_term->children ) ) {
			$tree_posts = get_posts( array(
				'post_type'    => $tree_post_type,
				'meta_key'     => 'tree_order',
				'orderby'      => 'meta_value_num',
				'order'        => 'ASC',
				'tax_query'    => array(
					array(
						'taxonomy'     => $tree_taxonomy,
						'terms'        => $tree_term->term_id
					)
				)
			));

			foreach ( $tree_posts as $tree_post ) {

				$tree_post->name = $tree_post->post_title;
				$tree_term->children[] = $tree_post;
			}
		}

		// push the term_color for terms of first level into $tree
		if ( 0 == $tree_term->parent )
			$tree_term->taxonomy_color = tree_color_get_term_meta( $tree_term->term_id, true );

		// push $tree_term innto $tree
		$tree[] = $tree_term;
	}

	return $tree;
}

add_action( 'wp_ajax_categorytree', 'categorytree_callback' );
add_action( 'wp_ajax_nopriv_categorytree', 'categorytree_callback' );

require_once 'metabox_color.php';
require_once 'metabox_order.php';

?>
