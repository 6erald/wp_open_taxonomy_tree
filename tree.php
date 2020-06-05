<?php
/**
 * Plugin Name:       Open Taxonomy Tree
 * Plugin URI:        TODO: https://mypluginurl.com/
 * Description:       TODO: Beschreibung einfügen
 * Version:           1.1
 * Requires at least: TODO: 5.2 Welche Version?
 * Requires PHP:      TODO: 7.2 Welche Version?
 * Author:            Gerald Wagner
 * Author URI:        https://gerald-wagner.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

add_shortcode( 'taxonomytree', 'taxonomytree_shortcode' );

function taxonomytree_shortcode( $atts ) {

	// Define shorcode atts and default atts
	$atts = shortcode_atts( array(
		'post_type'  => 'posts',
		'taxonomy'   => 'category'
	), $atts );

 	// Save post type and taxonomy slug in wp_options table to access later
	update_option( 'tree_post_type', $atts['post_type'] );
	update_option( 'tree_taxonomy',  $atts['taxonomy'] );

	// Replace the shortcode with a div to build the tree with d3.js  later
	return '<div id ="taxonomytree"></div>';
}

add_action( 'wp_ajax_taxonomytree', 'taxonomytree_callback' );
add_action( 'wp_ajax_nopriv_taxonomytree', 'taxonomytree_callback' );

function taxonomytree_callback() {

	// Extract taxonomy name tree parent
	$tree_taxonomy      = get_option( 'tree_taxonomy' );
	$tree_taxonomy_arr  = get_taxonomy( $tree_taxonomy );
	$tree_taxonomy_name = $tree_taxonomy_arr->labels->singular_name;

	// Create main parent element of the tree
	$tree = array(
		'parent'  => -1,
		'name'    => $tree_taxonomy_name,
	);

	// Start recursion and build the tree as child element
	$tree['children'] = taxonomytree_build_tree( 0 );
	$response = json_encode( $tree );

	// Response output
	header( "Content-Type: application/json" );
	echo $response;
	die();
}

function taxonomytree_build_tree( $root ) {

	// Define args for query
	$tree_taxonomy  = get_option( 'tree_taxonomy' );
	$args = array(
		'parent'      => $root,
		'meta_key'    => 'tree_order',
		'orderby'     => 'meta_value_num',
		'order'       => 'ASC',
		'hide_empty'  => 0,
		'taxonomy'    => $tree_taxonomy,
	);

	// Query the terms of the actual parent
	$tree_terms = get_categories( $args );

	foreach ( $tree_terms as $tree_term ) {

		// Call recusion for every child term element
		$tree_term->children = taxonomytree_build_tree( $tree_term->term_id );

		// Add term color in term elements of first level
		if ( 0 === $tree_term->parent ) {
			$tree_term->taxonomy_color = tree_color_get_term_meta( $tree_term->term_id, true );
		}

		// Get blogposts for term elements of last level
		if ( empty ( $tree_term->children ) ) {

			$tree_post_type = get_option( 'tree_post_type' );
			$tree_posts     = get_posts( array(
				'post_type'  => $tree_post_type,
				'meta_key'   => 'tree_order',
				'orderby'    => 'meta_value_num',
				'order'      => 'ASC',
				'tax_query'  => array(
					array(
						'taxonomy'  => $tree_taxonomy,
						'terms'     => $tree_term->term_id
					)
				)
			));

			// Add post title in term elements
			foreach ( $tree_posts as $tree_post ) {

				$tree_post->name = $tree_post->post_title;
				$tree_term->children[] = $tree_post;
			}
		}

		// Add term element in tree
		$tree[] = $tree_term;
	}

	return $tree;
}

add_action( 'wp_footer', 'taxonomytree_scripts' );

function taxonomytree_scripts() {

	wp_register_script( 'taxonomytree_d3_js', plugins_url( 'tree.js', __FILE__ ), array( 'd3_js' ) );
	wp_enqueue_script( 'taxonomytree_d3_js' );

	wp_register_script( 'd3_js', plugins_url( 'd3.v3.min.js', __FILE__ ), array( 'jquery' ) );
    wp_enqueue_script( 'd3_js' );

	wp_register_style( 'style_css', plugins_url( 'style.css', __FILE__ ) );
    wp_enqueue_style( 'style_css' );

	wp_localize_script( 'taxonomytree_d3_js', 'TaxononmyTreeAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}

require_once 'tree-metabox-color.php';
require_once 'tree-metabox-order.php';

?>
