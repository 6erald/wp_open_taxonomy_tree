# Open Taxonomy Tree Wordpress Plugin


## Description

The Open Taxonomy Tree Wordpress Plugin displays a taxonomy tree with its terms and posts structure using [d3.js](https://d3js.org/). It is originated in the context of the open source website to display the [Sustainable Open Mobility Taxonomy](https://opensourcelab.dfki.de/taxonomy/).


## Installation

1. Download the files and place the folder into your wordpress plugin directory

2. Activate plugin in Wordpress > Plugins > Installed Plugins

3. Place a shortcode where the taxonomy tree shall display this way:

`[taxonomytree]`
* the default taxonomy "category" and the default post type "post" will display

`[taxonomytree taxonomy="my_taxonomy_slug"]`
* the taxonomy of a specific post type will display but not the assigned posts

`[taxonomytree taxonomy="my_taxonomy_slug" post_type="my_post_type_slug"]`
* the taxonomy of a specific post type and its assigned posts will display

4. Add tree colors to the terms of level 1 in hex `#fff` or `#ffffff`

5. Add tree orders to terms and post to display them

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

* A Project of [open source lab](https://opensourcelab.dfki.de/)
* Author: [Gerald Wagner](https://github.com/6erald/)
