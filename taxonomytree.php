<?php
/**
 * Plugin Name:       Open Taxonomy Tree
 * Plugin URI:        https://github.com/open-source-lab-DFKI
 * Description:       The Open Taxonomy Tree Wordpress Plugin displays a taxonomy tree with its categries and posts structure using d3.js
 * Version:           1.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Gerald Wagner
 * Author URI:        https://gerald-wagner.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */


/**
 * Shortcode
 */

add_shortcode( 'taxonomytree', 'taxonomytree_shortcode' );

function taxonomytree_shortcode( $atts ) {

	// Define shorcode atts and default atts
	if ( post_type_exists ( 'structure' ) ) {
		$tree_taxonomy  = 'structure';
		$tree_post_type = 'tax_structure';
	} else {
		$tree_taxonomy  = 'posts';
		$tree_post_type = 'category';
	}

	$atts = shortcode_atts( array(
		'post_type'  => $tree_taxonomy,
		'taxonomy'   => $tree_post_type
	), $atts );

 	// Save post type and taxonomy slug in wp_options table to access later
	update_option( 'tree_post_type', $atts['post_type'] );
	update_option( 'tree_taxonomy',  $atts['taxonomy'] );

	// Replace the shortcode with a div to build the tree inside later
	return '<div id ="taxonomytree"></div>';
}

/**
 * Scripts & styles
 */

add_action( 'wp_footer', 'taxonomytree_scripts' );

function taxonomytree_scripts() {

	wp_register_script( 'taxonomytree_js', plugins_url( 'taxonomytree.js', __FILE__ ), array( 'd3_js' ) );
	wp_enqueue_script( 'taxonomytree_js' );

	wp_register_script( 'd3_js', plugins_url( 'd3.v3.min.js', __FILE__ ), array( 'jquery' ) );
    wp_enqueue_script( 'd3_js' );

	wp_register_style( 'style_css', plugins_url( 'styles/style.css', __FILE__ ) );
    wp_enqueue_style( 'style_css');

	wp_localize_script( 'taxonomytree_js', 'TaxonomyTreeAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}

/**
 * Callback & tree recursion
 */

add_action( 'wp_ajax_taxonomytree', 'taxonomytree_callback' );
add_action( 'wp_ajax_nopriv_taxonomytree', 'taxonomytree_callback' );

function taxonomytree_callback() {

	// Extract taxonomy name for tree main parent
	$tree_taxonomy      = get_option( 'tree_taxonomy' );
	$tree_taxonomy_arr  = get_taxonomy( $tree_taxonomy );
	$tree_taxonomy_name = $tree_taxonomy_arr->labels->singular_name;

	// If you use the xml for 'Sustainable Open Mobility Taxonomy'
	// extract and overwrite taxonomy name tree for main parent from post meta
	$the_slug = 'sustainable-open-mobility-taxonomy';
	$args = array(
		'name' 		     => $the_slug,
		'post_type'      => 'structure'
	);
	$my_post = get_posts($args);

	if ( post_exists($my_post[0]->post_title) != 0 ) {
		$post_meta_key      = 'taxonomy_rootname';
		$tree_taxonomy_name = get_post_meta($my_post[0]->ID, $post_meta_key);
	}

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
					))
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
 * Metaboxes
 */

define('__METABOX__', dirname(__FILE__).'/metaboxes');

require_once ( __METABOX__ . '/metabox-color.php' );
require_once ( __METABOX__ . '/metabox-order.php' );
require_once ( __METABOX__ . '/metabox-excerpt.php' );

/**
 * Register new taxonomy for custom post type "structure"
 */

if ( file_exists ( dirname(__FILE__).'/register-structure.php' ) ) {

	require_once ( dirname(__FILE__).'/register-structure.php' );

}

?>
