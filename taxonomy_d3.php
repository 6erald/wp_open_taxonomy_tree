<?php
/*

Plugin Name: open taxonomy tree
Plugin URI:
Author: Gerald Wagner

*/

add_action( 'wp_footer', 'categoryd3tree_scripts' );
add_action( 'wp_ajax_categorytree', 'categorytree_callback' );
add_action( 'wp_ajax_nopriv_categorytree', 'categorytree_callback' );

function categorytree_callback() {
	// get acf-field-title of the taxonomytree rootlement
	// from page with slug "taxonomy_root"
	$the_slug = 'taxonomy_root';
	$args = array(
		'name'        => $the_slug,
		'post_type'   => 'page',
		'post_status' => 'publish',
		'numberposts' => 1
	);
	$my_posts = get_posts($args);
	$taxonomyname = get_field('taxonomy_rootname', $my_posts[0]->ID);

	//process plugin
	$tree = array(
		'parent'      => -1,
		'name'        => $taxonomyname,
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
	$args = array(
		'parent'      => $root,
		'meta_key'    => 'taxonomy_order', //acf-order
		'orderby'     => 'meta_value',
		'order'       => 'ASC',
		'hide_empty'  => 0,
		'taxonomy'    => 'tax_structure',
	);
	$l1cats = get_categories($args);

	foreach($l1cats as $l1cat) {

		$l1cat->children=buildrectree($l1cat->term_id);
		// push $l1post into $l1cat
		if(empty ($l1cat->children)) {
			$l1posts = get_posts( array(
				'post_type'    => 'structure',
				'meta_key'     => 'taxonomy_order', //acf-order
				'orderby'      => 'meta_value',
				'order'        => 'ASC',
				'tax_query'    => array(
					array(
						'taxonomy'     => 'tax_structure',
						'field'        => 'tag_ID',
						'terms'        => $l1cat->term_id
					)
				)
			));
			foreach($l1posts as $l1post) {
				$l1post->name=$l1post->post_title;
				$l1cat->children[]=$l1post; //TODO: Wie ist hier das spacing sinnvoll?
			}
		}
		// push $color into $l1cat
		$key = $l1cat->parent;
		if(0 == $key) {
			$color = get_field('taxonomy_color', 'tax_structure_' . $l1cat->term_id);
			$l1cat->taxonomy_color = $color;
		}
		// push $l1cat innto $tree
		$tree[]=$l1cat;
	}
	return $tree;
}

function categoryd3tree_scripts() {
	if(!is_single()&&get_post_type()=='structure') {

		wp_register_script( 'categoryd3tree_js', get_template_directory_uri() . '/metabox/taxonomytree/tree.js', array( 'd3_js' ) );
		wp_enqueue_script ( 'categoryd3tree_js' );

		wp_register_script( 'd3_js', get_template_directory_uri() . '/metabox/taxonomytree/d3.v3.min.js', array( 'jquery' ) );
		wp_enqueue_script ( 'd3_js' );

		// declare the URL to the file that handles
		// the AJAX request (wp-admin/admin-ajax.php)
		wp_localize_script( 'categoryd3tree_js', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}
}


/**
 * Remove top categories of the Taxonomy in post.php / post-new.php
 */

add_action( 'admin_footer-post.php', 'wp_remove_top_categories_checkbox' );
add_action( 'admin_footer-post-new.php', 'wp_remove_top_categories_checkbox' );

function wp_remove_top_categories_checkbox() {
	global $post_type;
	if( 'structure' != $post_type )
		return;
		?>
		<script type="text/javascript">
			jQuery("#tax_structurechecklist>li>label input").each(function() {
				// remove only if is unchecked
				if(jQuery(this).is(':not(:checked)')) {
				   jQuery(this).remove();
				}
			});
		</script>
		<?php
}
?>
