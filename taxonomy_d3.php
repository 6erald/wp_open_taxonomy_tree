<?php
/*

Plugin Name: open taxonomy tree
Plugin URI:
Author: Gerald Wagner

*/


function taxonomytree_shortcode($atts) {

	$atts = shortcode_atts( array(
		'post_type'  => 'posts',
		'taxonomy'   => 'category'
	), $atts);

 	// save 'post_type' and 'taxonomy' in wp_options to access later
	update_option("tree_post_type", $atts['post_type']);
	update_option("tree_taxonomy", $atts['taxonomy']);

	return '<div id ="categorytree"></div>' ;

}

add_shortcode( 'taxonomy_d3', 'taxonomytree_shortcode' );

function categoryd3tree_scripts() {

	wp_register_script( 'categoryd3tree_js', plugins_url( 'tree.js', __FILE__ ), array( 'd3_js' ) );
	wp_enqueue_script(  'categoryd3tree_js' );

	wp_register_script( 'd3_js', plugins_url( 'd3.v3.min.js', __FILE__ ), array( 'jquery' ) );
    wp_enqueue_script(  'd3_js' );

	wp_register_style( 'style_css', plugins_url( 'style.css', __FILE__ ) );
    wp_enqueue_style(  'style_css' );

	// declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
	wp_localize_script( 'categoryd3tree_js', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}

add_action( 'wp_footer', 'categoryd3tree_scripts' );

function categorytree_callback() {

	$tree_taxonomy = get_option('tree_taxonomy');
	//process plugin
	$tree = array(
		'parent'      => -1,
		'name'        => $tree_taxonomy, // TODO: Namen andern
	);

	// generate the response
	//$tree['children']=buildtree();
	$tree['children'] = buildrectree(0);
	$response = json_encode($tree);

	// response output
	header("Content-Type: application/json");
	echo $response;
	die();
}

function buildrectree($root) {

	$tree_post_type = get_option("tree_post_type");
	$tree_taxonomy = get_option("tree_taxonomy");
	/*
	$tree_taxonomy_order = get_option("tree_category_$l1cat->term_id");
	*/

	$args = array(
		'parent'      => $root,
		'meta_key'    => 'tree_taxonomy_order', //acf-order
		'orderby'     => 'meta_value_num',
		'order'       => 'ASC',
		'hide_empty'  => 0,
		'taxonomy'    => $tree_taxonomy,
	);

	$l1cats = get_categories($args);

	foreach($l1cats as $l1cat) {
		$l1cat->children=buildrectree($l1cat->term_id);

		// push blogposts into $tree in category of last level
		if(empty ($l1cat->children)) {
			$l1posts = get_posts( array(
				'post_type'    => $tree_post_type,
				'meta_key'     => 'tree_post_order',
				'orderby'      => 'meta_value_num',
				'order'        => 'ASC',
				'tax_query'    => array(
					array(
						'taxonomy'     => $tree_taxonomy,
						'terms'        => $l1cat->term_id
					)
				)
			));
			foreach($l1posts as $l1post) {
				$l1post->name=$l1post->post_title;
				$l1cat->children[]=$l1post; //TODO: Wie ist hier das spacing sinnvoll?
			}
		}

		// push $color into $l1cat of first level
		$cat_data = get_option("tree_category_$l1cat->term_id");
		if (0 == $l1cat->parent) {
			if (isset($cat_data['cat_color']) && $cat_data['cat_color'] != '') {
				$l1cat->taxonomy_color = $cat_data['cat_color'];
			} else { //default color
				$l1cat->taxonomy_color = '#000000';
			}
		}

		// push $l1cat innto $tree
		$tree[]=$l1cat;
	}
	return $tree;
}

add_action( 'wp_ajax_categorytree', 'categorytree_callback' );
add_action( 'wp_ajax_nopriv_categorytree', 'categorytree_callback' );

// metaboxes category_order and category_color;
// require_once 'metabox_category.php';
require_once 'metabox_order.php';
require_once 'metabox_color.php';

?>
