<?php

/**
 * Metabox Post Excerpt
 */

add_action( 'init', 'taxonomytree_add_excerpt_support_for_post' );

function taxonomytree_add_excerpt_support_for_post() {

	$tree_post_type = get_option( 'tree_post_type' );
    add_post_type_support( $tree_post_type, 'excerpt' );
}


?>
