# Open Taxonomy Tree Wordpress Plugin
-------------------------------------


## Description

The Open Taxonomy Tree Wordpress Plugin displays a taxonomy tree with its terms and posts structure using [d3.js](https://d3js.org/).


## Installation

1. Download the files into a folder of your wordpress plugin directory

2. Activate plugin in Wordpress > Plugins > Installed Plugins

3. Place a shortcode where the taxonomy tree shall display this way:

```
[taxonomytree post_type="my_post_type_slug" taxonomy="my_taxonomy_slug"]
```
* taxonomytree (required): Identifies the shortcode
* post_type (optional): Identifies the target post type by his slug
* taxonomy (optional): Identifies the target taxonomy by his slug

4. The following layout is necessary to display the post elements.

```
Levels and Objecs:
==================             3 Post
                              /
                      2 Term • 3 Post
                     /        \
             1 Term •          3 Post
            /        \
0 Taxonomy •          2 Term
            \
             1 Term
```
* Level 1 term has no parent than the taxonomy itself
* Level 2 term has level 1 term as parent
* Level 3 post has level 2 term as parent
* Layout changes with d3.js can be made in tree.js


## Files used

* README.md
* style.css
* tree.js
* tree.php
* tree-metabox-color.php

    <!--
    * displays a metabox to add a color during creation of a new term
    * displays a metabox to add a color during editing of a  level 1 term
    * saves and updates term meta in the wp_termmeta table
    -->

* tree-metabox-order.php

    <!--
    * displays a metabox to add a order-number during creation of a new term and post
    * displays a metabox to add a order-number during editing of a term and post
    * saves and updates term meta / post meta in the wp_termmeta / wp_postme table
    -->

* d3.v3.min.js


## License

[GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0.html)

    <!--
    * Git Styleguide
    * https://udacity.github.io/git-styleguide/
     -->

## Credits

* A Project by the [open source lab](https://opensourcelab.dfki.de/)
* Author: [Gerald Wagner](https://github.com/6erald/)
