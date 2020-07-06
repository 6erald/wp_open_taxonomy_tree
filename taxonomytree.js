/**
 * Open Taxonomy Tree Wordpress Plugin
 */

jQuery(document).ready(function($) {

    var root;

    // Create measurements
    var margin = { top: 20, right: 20, bottom: 20, left: 20 },
        width  = 900 - margin.right - margin.left,
        height = 500 - margin.top - margin.bottom;

    // Set size of tree layout
    var tree = d3.layout.tree()
        .size([height, width]);

    // Rotate the coordinates for horizontal layout.
    var diagonal = d3.svg.diagonal()
        .projection(function(d) { return [d.y, d.x]; });

    // Create the svg
    var svg = d3.select('#taxonomytree')
        .append("svg")
        .attr("viewBox", "0 0 " + width + " " + height)
        .attr("preserveAspectRatio", "xMinYMin")
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top / 2 + ")");


    /**
     * Ajax
     */

    jQuery.ajax({
        url: TaxonomyTreeAjax.ajaxurl,
        data: {
    	    'action': 'taxonomytree',
        },
        dataType: 'JSON',
        success: function(data) {
    	    root = data;
    	    root.x0 = height / 2;
    	    root.y0 = 0;

    	    process_d3_tree(root);
        },
        error: function(errorThrown) {
    	   console.log(errorThrown);
        }
    });


    /**
     * Process the tree layout
     */

    function process_d3_tree(source) {

        /**
         * Create data and measurements
         */

        // Data
        var nodes = tree.nodes(source);
        var links = tree.links(nodes);

        // Length Units
        var maxDepth = determMaxDepth(nodes);

        function determMaxDepth(nodes) {

            var result = nodes.reduce((acc, val) => {

                acc = (acc === undefined || val.depth > acc) ? val.depth : acc;
                return acc;

            },[]);

            return result;
        }

        var unitLength = width / (maxDepth + 3);

        nodes.forEach(function(d) {

            d.y = d.depth * unitLength + unitLength;

            if (d.depth == 0) {
                d.y = d.y - unitLength / 3;
                return d.y;
            }

            if (d.post_title) {
                d.y = d.y - unitLength / 6;
                return d.y;
            }

            d.y = d.y + unitLength / 2;
            return d.y;
        });


        /**
         * Create main elements
         */

        // Path
        var link = svg.selectAll(".link")
            .data(links)
            .enter()
            .append("g")
            .attr("class", "diagonal")
            .append("path")
            .attr("class", "link")
            .attr("d", diagonal)
            .style("stroke", selectParentLinkColor);

        // Label
        var node = svg.selectAll(".node")
        	.data(nodes)
        	.enter()
        	.append("g")
        	.attr("class", "node")
        	.attr("transform", function(d) {
                // Rotate coordinates d.x and d.y for horizontal layout
        		return "translate(" + d.y + "," + d.x  + ")";
            });


        /**
         * Create taxonomy elements
         */

        // Label
        var nodeTaxonomy = node.filter(function(d) { return d.depth==0; })
            .append("text")
            .text(function(d){ return d.name; })
            .attr("class", "label taxonomy")
            .style("text-anchor", "end")
            .call(wrap, 90, 15);


        /**
         * Create post elements
         */

        // Label
        var nodePost = node.filter(function(d) { return d.post_title; })
            .append("text")
            .attr("class", "label post")
            .text(function(d){ return d.name; })
            .style("fill", selectParentNodeColor);

        // Line
        var nodePostLineLenght = 1.7 * unitLength;

        var nodePostLine = node.filter(function(d) { return d.post_title; })
            .insert("line")
            .attr("class", "label-line")
            .attr("x1", function(d) { return 0 })
            .attr("y1", function(d) { return 0 })
            .attr("x2", function(d) { return 0  + nodePostLineLenght })
            .attr("y2", function(d) { return 0 })
            .style("stroke", selectParentNodeColor);

        // Button
        var nodePostButton = node.filter(function(d) { return d.post_title; })
            .append("g")
            .attr("class", "label-button");

        nodePostButton.insert("circle")
            .attr("class", "label-button-circle")
            .attr('r', 5)
            .attr("cx", nodePostLineLenght -5 -1.5)
            .attr("cy", -5 -3*1.5)
            .style("stroke", selectParentNodeColor);

        nodePostButton.insert("line")
            .attr("class", "label-button-line")
            .attr("x1", nodePostLineLenght -9.5 )
            .attr("y1", -5 -3*1.5)
            .attr("x2", nodePostLineLenght -3.5)
            .attr("y2", -5 -3*1.5)
            .style("stroke", selectParentNodeColor);

        nodePostButton.insert("line")
            .attr("class", "label-button-line")
            .attr("x1", nodePostLineLenght -6.5 )
            .attr("y1", -5 -5*1.5)
            .attr("x2", nodePostLineLenght -6.5)
            .attr("y2", -5 -1*1.5)
            .style("stroke", selectParentNodeColor)
            .style("z-index", 100);


        /**
         * Create (curvy) term elements
         */

        // Defs with ID
        var linkTerm = svg.selectAll('.label-curvyInfo')
        	.data(links)
        	.enter()
        	.append("defs").append("path")
        	.attr("class", "label-curvyInfo")
        	.attr("id", function(d) { return d.target.term_id })
        	.attr("d", diagonal);

        // Label related to defs id
        var nodeTerm = svg.selectAll('.label-curvy')
        	.data(nodes)
        	.enter()
        	.append('g')
        	.append("text")
        	.attr("dy", "-0.35em")
        	.attr("class", "label-curvy")
        	.append("textPath")
        	.attr("xlink:href",function(d) { return "#"+d.term_id; })
        	.text(function(d) { return d.name })
        	.attr("startOffset", "100%")
        	.style("text-anchor", "end")
        	.style("fill", selectParentNodeColor);


        /**
         * Color selection
         */

        function selectParentNodeColor(el) {

            while (el != null && typeof(el) != 'undefined') {

                if (el.taxonomy_color) {
                    return el.taxonomy_color;
                }

                el = el.parent;
            }
        }

        function selectParentLinkColor(el) {

            if (el == null) {
                return;
            }

            var target = el.target;

            while (typeof(target) != 'undefined') {

                if (target.taxonomy_color) {
                    return target.taxonomy_color;
                }

                target = target.parent;
            }
        }


        /**
         * Mouse interaction
         */

        node.on("mouseover", nodeMouseOver);
        node.on("mouseout", nodeMouseOut);
        node.on("click", nodeMouseClick);

        nodeTerm.on("mouseover", nodeMouseOver);
        nodeTerm.on("mouseout", nodeMouseOut);

        function nodeMouseOver (d) {

        	// Highlight path
        	svg.selectAll(".link")
            	.filter(function(e) {

                    el = d;

                    while (el != null && typeof(el) != 'undefined') {

                        if ((e.source == el.parent) && (e.target == el)) {
                            return true;
                        }

                        el = el.parent;
                    }
                })
                .classed("highlight", true);

        	// Highlight label
        	svg.selectAll(".label, .label-curvy, .label-line, .label-button, .label-button-circle, .label-button-line")
                .filter(function(e) { return (d.name == e.name); })
                .classed("highlight", true);
        }

        function nodeMouseOut (d) {

            d3.selectAll(".highlight").classed("highlight", false);
        }

        function nodeMouseClick (d) {

        	if (d.post_title) {

        		var infoBoxText = document.getElementById("infobox-text");

        		var textContent = '<h3><a id="infobox-link">' + d.post_title + "</a></h3>"
    		                    + "<p>" + d.post_excerpt + "</p>";

    		    infoBoxText.innerHTML = textContent;

        		var infoBoxLink = document.getElementById("infobox-link");
    		    infoBoxLink.href = d.guid;

                showInfoBox();
        	}
        }


        /**
         * Info box
         */

        makeInfoBox();

        function makeInfoBox() {

            var taxonomyTree = document.getElementById("taxonomytree");
            taxonomyTree.insertAdjacentHTML('afterend', '<div id="infobox"></div>');

            var infoBox = document.getElementById("infobox");
            infoBox.classList.add("hide-me");
            infoBox.innerHTML = '<span id="infobox-skip">'
                              + 'close'
                              + '</span>'
                              + '<div id="infobox-text"></div>';

            var infoBoxSkip = document.getElementById("infobox-skip");
            infoBoxSkip.addEventListener("click", hideInfoBox);
        }

        function showInfoBox() {

            var infoBox = document.getElementById("infobox");
            infoBox.classList.remove("hide-me");
        }

        function hideInfoBox() {

            var infoBox = document.getElementById("infobox");
            infoBox.classList.add("hide-me");
        }


        /**
         * Text wrap of root term
         */

        function wrap(text, width, yOffset) {

            text.each(function() {

                var text = d3.select(this),
                    words = text.text().split(/\s+/).reverse(),
                    word,
                    line = [],
                    lineNumber = 0,
                    lineHeight = 1.1, // ems
                    y = text.attr("y"),
                    dy = parseFloat(text.attr("dy")),
                    tspan = text.text(null)
                        .append("tspan")
                        .attr("x", 0)
                        .attr("y", y)
                        .attr("dy", "" + yOffset /-2 + "px");

                while (word = words.pop()) {

                    line.push(word);
                    tspan.text(line.join(" "));

                    if (tspan.node().getComputedTextLength() > width) {

                        line.pop();
                        tspan.text(line.join(" "));
                        line = [word];
                        tspan = text.append("tspan")
                            .attr("x", 0)
                            .attr("y", y)
                            .attr("dy", "" + yOffset + "px")
                            .text(word);
                    }
                }
            });
        }

    } // process_d3_tree()

}); // jQuery()
