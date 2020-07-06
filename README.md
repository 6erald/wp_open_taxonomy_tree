# Open Taxonomy Tree Wordpress Plugin


## Description

The Open Taxonomy Tree Wordpress Plugin displays a taxonomy tree with its terms and posts structure using [d3.js](https://d3js.org/). It is originated in the context of the Open Source Lab website to display the [Sustainable Open Mobility Taxonomy](https://opensourcelab.dfki.de/taxonomy/). With this plugin you also will be able to import The Sustainable Open Mobility Taxonomy and display it on your own WordPress site.  


## Installation

1. Download the files and separate the sustainable_open_mobility_taxonomy.xml from the other files.

2. Log in to your site as an administrator.

3. Upload the wp-taxonomy-tree folder into your wordpress plugin directory.

4. Got to Wordpress > Plugins > Installed Plugins in the WordPress admin panel and activate the Open Taxonomy Tree.

5. To import the sustainable_open_mobility_taxonomy.xml into your WordPress site follow next steps otherwise skip and go on with 6.:

    1. Go to Tools > Import in the WordPress admin panel.
    2. Install the "WordPress" importer from the list.
    4. Activate & Run Importer.
    5. Upload the sustainable_open_mobility_taxonomy.xml.
    6. You will first be asked to map the authors in this export file to users on the site. For each author, you may choose to map to an existing user on the site or to create a new user.
    7. You will secondly be asked to import the attachments. This will enable you to import the Illustrations of the taxonomy as thumbnails.
    8. WordPress will then import each of the posts, categories, Thumbnails of the Sustainable Open Mobility Taxonomy into your site.
    9. You'll find the imported content under Taxonomy in the WordPress admin panel. The introducon page you'll find under Sustainable Open Mobility Taxonomy. If you want to display the Sustainable Open Mobility Taxonomy somewhere else at your website place the shortcode `[taxonomytree]` on your target.

6. If you want to create a custom taxonomy go on with the following steps otherwise skip.

7. Place the shortcode where the taxonomy tree shall display and modify it this way:

    `[taxonomytree]`
    * The default taxonomy "tax_structure" and the assigned post of the default post type "structure" will display.

    `[taxonomytree taxonomy="my_taxonomy_slug"]`
    * You can display the taxonomy of a specific post type but not the assigned posts.

    `[taxonomytree taxonomy="my_taxonomy_slug" post_type="my_post_type_slug"]`
    * You can display taxonomy of a specific post type and its assigned posts.

8. Add tree colors to the terms of level 1 in hex `#fff` or `#ffffff`.

9. Add tree orders to terms and post to display them.

The following layout is necessary to display the post elements.

```
Levels and Objecs:
==================             3 Post
                              /
                      2 Term • 3 Post
                     /        \
             1 Term •          3 Post
            /        
0 Taxonomy •          2 Post
            \        /   
             1 Term • 2 Post
                     \
                      2 Post


```
* Level 1 term has no parent than the taxonomy itself
* Level 2 post has level 1 term as parent
* Level 2 term has level 1 term as parent
* Level 3 post has level 2 term as parent
* Layout changes with d3.js can be made in taxonomytree.js


## Credits

* A Project of the [Open Source Lab](https://opensourcelab.dfki.de/)
* Concept: [Tina Gallico](https://www.tinagallico.com/)
* Design: [Olya Bazilevich](http://olyabazilevich.com/)
* Programming: [Gerald Wagner](https://github.com/6erald/)
