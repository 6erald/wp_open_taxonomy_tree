<?php
/**
 * Plugin Name:       Open Taxonomy Tree
 * Plugin URI:        TODO: https://mypluginurl.com/
 * Description:       The Open Taxonomy Tree Wordpress Plugin displays a taxonomy tree with its categries and posts structure using d3.js
 * Version:           1.1
 * Requires at least: TODO: 5.2 Welche Version? -> 5.4.1 ist die aktuelle version // benötgigt rest API
 * Requires PHP:      TODO: 7.2 Welche Version?
 * Author:            Gerald Wagner
 * Author URI:        https://gerald-wagner.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

add_shortcode( 'taxonomytree', 'taxonomytree_shortcode' );
/**
 * Taxonomy Tree Shortcode
 *
 * Request the post type and taxonomy which shall be displayed and stores them
 * in wp_options table. Return the div where d3.js builds and displays the final tree later.
 *
 */
function taxonomytree_shortcode( $atts ) {

	// Define shorcode atts and default atts
	$atts = shortcode_atts( array(
		'post_type'  => 'posts',
		'taxonomy'   => 'category'
	), $atts );

 	// Save post type and taxonomy slug in wp_options table to access later
	update_option( 'tree_post_type', $atts['post_type'] );
	update_option( 'tree_taxonomy',  $atts['taxonomy'] );

	// Replace the shortcode with a div to build the tree with d3.js later
	return '<div id ="taxonomytree"></div>';
}

add_action( 'wp_footer', 'taxonomytree_scripts' );
/**
 * Register and Enqueue Scripts/Styles
 *
 */
function taxonomytree_scripts() {

	wp_register_script( 'taxonomytree_js', plugins_url( 'taxonomytree.js', __FILE__ ), array( 'd3_js' ) );
	wp_enqueue_script( 'taxonomytree_js' );

	wp_register_script( 'd3_js', plugins_url( 'd3.v3.min.js', __FILE__ ), array( 'jquery' ) );
    wp_enqueue_script( 'd3_js' );

	wp_register_style( 'style_css', plugins_url( 'style.css', __FILE__ ) );
    wp_enqueue_style( 'style_css' );

	wp_localize_script( 'taxonomytree_js', 'TaxonomyTreeAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}

add_action( 'wp_ajax_taxonomytree', 'taxonomytree_callback' );
add_action( 'wp_ajax_nopriv_taxonomytree', 'taxonomytree_callback' );
/**
 * Taxonomy Tree Ajax callback
 *
 * Get call by taxonomytree.js. Create the main element of the tree and call
 * the function taxonomytree_build_tree to build the child tree elements.
 * Responses with an json object.
 *
 */
function taxonomytree_callback() {

	// Extract taxonomy name for tree main parent
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

/**
* Taxonomy Tree Recursion
*
* Query the term for the actual root/parent element. Call the Taxonomy Tree
* Recusion again for every term element as child. Add colors for level 1 terms.
* Add posts at the end of the tree if exists. Structure terms and posts by
* meta key 'tree_order'. Return the final tree array.
*
*/
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
			$tree_term->taxonomy_color = taxonomytree_color_get_term_meta( $tree_term->term_id, true );
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
					) )
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

/**
 * Taxonomy Tree Metaboxes
 *
 * Add metabox color terms. Add metabox order for terms and posts.
 *
 */
require_once 'taxonomytree-metabox-color.php';
require_once 'taxonomytree-metabox-order.php';



add_action( 'init', 'taxonomytree_add_excerpt_support_for_post' );
/**
 * Enable the Excerpt meta box in post type edit screen.
 */
function taxonomytree_add_excerpt_support_for_post() {

	$tree_post_type = get_option( 'tree_post_type' );
    add_post_type_support( $tree_post_type, 'excerpt' );
}


?>
