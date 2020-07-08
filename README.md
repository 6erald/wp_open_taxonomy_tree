# Open Taxonomy Tree WordPress Plugin


## Description

The Open Taxonomy Tree Wordpress Plugin displays a taxonomy tree with its terms and posts structure using [d3.js](https://d3js.org/). It is originated in the context of the Open Source Lab website to display the [Sustainable Open Mobility Taxonomy](https://opensourcelab.dfki.de/taxonomy/). With this plugin you also will be able to import The Sustainable Open Mobility Taxonomy and display it on your own WordPress site.  


## Installation

1. Download the zip file and separate the sustainable_open_mobility_taxonomy.xml from it.

2. Log in to your site as an administrator.

3. Go to Plugins in the Admin panel.

4. Choose Plugins > Add new.

5. Then choose Add Plugins > Upload Plugin.

6. Add the zip file and upload it. Click on Install Now.

7. Activate the Plugin.

8. To import the content of the Sustainable Open Mobility Taxonomy, you have to import the sustainable_open_mobility_taxonomy.xml by following the next steps otherwise skip and go on with 9.:

    1. Go to Tools > Import in the WordPress admin panel.
    2. Install the "WordPress" importer from the list.
    3. Activate & Run Importer.
    4. Upload the sustainable_open_mobility_taxonomy.xml.
    5. You will first be asked to map the authors in this export file to users on the site. For each author, you may choose to map to an existing user on the site or to create a new user.
    6. You will secondly be asked to import the attachments. This will enable you to import the Illustrations of the taxonomy as thumbnails.
    7. WordPress will then import each of the posts, categories, Thumbnails of the Sustainable Open Mobility Taxonomy into your site.
    8. You'll find the imported content under Taxonomy in the WordPress admin panel. The introducon page you'll find under Sustainable Open Mobility Taxonomy. If you want to display the Sustainable Open Mobility Taxonomy somewhere else at your website place the shortcode `[taxonomytree]` on your target.

9. If you want to create a custom taxonomy go on with the following steps otherwise skip.

10. Place the shortcode where the taxonomy tree shall display and modify it this way:

    `[taxonomytree]`
    The default taxonomy "tax_structure" and the assigned post of the default post type "structure" will display.

    `[taxonomytree taxonomy="my_taxonomy_slug"]`
    You can display the taxonomy of a specific post type but not the assigned posts.

    `[taxonomytree taxonomy="my_taxonomy_slug" post_type="my_post_type_slug"]`
    You can display taxonomy of a specific post type and its assigned posts.

11. Add tree colors to the terms of level 1 in hex `#fff` or `#ffffff`.

12. Add tree orders to terms and post to display them.

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

* A Project by the [Open Source Lab](https://opensourcelab.dfki.de/)
* Concept: [Tina Gallico](https://www.tinagallico.com/)
* Illustration: [Olya Bazilevich](http://olyabazilevich.com/)
* Programming: [Gerald Wagner](https://github.com/6erald/)
