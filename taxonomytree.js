/**
 * Open Taxonomy Tree Wordpress Plugin
 *
 */

jQuery(document).ready(function($) {

var root;

// Create measurements
var margin = {top: 20, right: 20, bottom: 20, left: 20},
    width  = 900 - margin.right - margin.left,
    height = 500 - margin.top - margin.bottom;

// Set size of tree layout
var tree = d3.layout.tree()
    .size([height, width]);

// Rotate the coordinates for horizontal layout
var diagonal = d3.svg.diagonal()
    .projection(function(d) {return [d.y, d.x];});

// Create the svg
var svg = d3.select('#taxonomytree').append("svg")
    .attr("viewBox", "0 0 " + width + " " + height)
    .attr("preserveAspectRatio", "xMinYMin")
    .append("g")
    .attr("transform", "translate(" + margin.left + ","  + margin.top/2 + ")");

// Ajax
jQuery.ajax({
    url: TaxonomyTreeAjax.ajaxurl,
    data: {
	    'action': 'taxonomytree',
    },
    dataType: 'JSON',
    success: function(data) {
	    // alert(JSON.stringify(data));
	    root = data;
	    root.x0 = height / 2;
	    root.y0 = 0;

	    update(root);
    },
    error: function(errorThrown) {
	   //alert(JSON.stringify(errorThrown));
	   console.log(errorThrown);
    }
});


function update(source) {

/**
 * Create arrays and measurements
 *
 */

// Create nodes
var nodes = tree.nodes(root);

// Create links
var links = tree.links(nodes);

console.log(root);
console.log(nodes);
console.log(links);

// Create length units
var unitLength = 150;
    nodes.forEach(function(d) {return d.y = d.depth * unitLength + unitLength;});
    nodes.forEach(function(d) {
		if (d.depth==0) {return d.y = d.y - unitLength/3;}
		if (d.depth==2) {return d.y = d.y + unitLength/2;}
		if (d.depth==3) {return d.y = d.y - unitLength/6;}
    });


/**
 * Create Elements
 *
 */

function selectParentNodeColor(el) {

    var colorCategory = el;
    while (! colorCategory.taxonomy_color) {
        colorCategory = colorCategory.parent;
    }
    return colorCategory.taxonomy_color;
}

function selectParentLinkColor(el) {

    var colorCategory = el.target;
    while (! colorCategory.taxonomy_color) {
        colorCategory = colorCategory.parent;
    }
    return colorCategory.taxonomy_color;
}

// Create link
var link = svg.selectAll(".link")              // Level 1 - Import & Setup
    .data(links)
    .enter()
    .append("g")                               // Level 2 - Elements & Attributes
    .attr("class", "diagonal")
    .append("path")
    .attr("class", "link")
    .attr("d", diagonal)
    .style("stroke", selectParentLinkColor);

// Create node
var node = svg.selectAll(".node")
	.data(nodes)
	.enter()
	.append("g")
	.attr("class", "node")
	.attr("transform", function(d) {
        // Rotate coordinates d.x and d.y for horizontal layout
		return "translate(" + d.y + "," + d.x  + ")";
    });

// Create Taxonomy Label
var labelTaxonomy = node.filter(function(d) {return d.depth==0;})
    .append("text")
    .text(function(d){return d.name;})
    .attr("class", "label taxonomy")
    .style("text-anchor", "end")
    .call(wrap, 90, 15);


/* *
 * Create Post elements
 */

// Create Post Label
var labelPost = node.filter(function(d) {return d.post_title;})
    .append("text")
    .attr("class", "label post")
    .text(function(d){return d.name;})
    .style("fill", selectParentNodeColor);

// Length of Post underline
var underlineLength = 1.7 * unitLength;

// Creat Post Underline
var linePost = node.filter(function(d) {return d.post_title;})
    .insert("line")
    .attr("class", "label-line")
    .attr("x1", function(d) {return 0})
    .attr("y1", function(d) {return 0})
    .attr("x2", function(d) {return 0  + underlineLength})
    .attr("y2", function(d) {return 0})
    .style("stroke", selectParentNodeColor);

// Create Button
var circle = node.filter(function(d) {return d.post_title;})
    .append("g")
    .attr("class", "label-button");

    circle.insert("circle")
    .attr("class", "label-button-circle")
    .attr('r', 5)
    .attr("cx", underlineLength -5 -1.5)
    .attr("cy", -5 -3*1.5)
    .style("stroke", selectParentNodeColor);

    circle.insert("line")
    .attr("class", "label-button-line")
    .attr("x1", underlineLength -9.5 )
    .attr("y1", -5 -3*1.5)
    .attr("x2", underlineLength -3.5)
    .attr("y2", -5 -3*1.5)
    .style("stroke", selectParentNodeColor);

    circle.insert("line")
    .attr("class", "label-button-line")
    .attr("x1", underlineLength -6.5 )
    .attr("y1", -5 -5*1.5)
    .attr("x2", underlineLength -6.5)
    .attr("y2", -5 -1*1.5)
    .style("stroke", selectParentNodeColor)
    .style("z-index", 100);

// Create Path lables
// Create defs with ID
var infoCurvy = svg.selectAll('.label-curvyInfo')
	.data(links)
	.enter()
	.append("defs").append("path")
	.attr("class", "label-curvyInfo")
	.attr("id", function(d) {return d.target.term_id})
	.attr("d", diagonal);

// Create label-curvyText related to defs
var curvyText = svg.selectAll('.label-curvyText')
	.data(nodes)
	.enter()
	.append('g')
	.append("text")
	.attr("dy", "-0.35em")
	.attr("class", "label-curvyText")
	.append("textPath")
	.attr("xlink:href",function(d) {return "#"+d.term_id;})
	.text(function(d) {return d.name})
	.attr("startOffset", "100%")
	.style("text-anchor", "end")
	.style("fill", function(d) {
		if (d.taxonomy_color) {
            return d.taxonomy_color
        }
		if (d.parent.taxonomy_color) {
            return d.parent.taxonomy_color
        }
    });

/*
 *
 * MOUSE INTERAKTION
 *
 */

node.on("mouseover", handleMouseOver);
node.on("mouseout", handleMouseOut);
node.on("click", handleMouseClick);

curvyText.on("mouseover", handleMouseOver);
curvyText.on("mouseout", handleMouseOut);

function handleMouseOver (d) {

	// Highlight path of d
	svg.selectAll(".link")
    	.filter(function(e) {
    		return ((d.name==e.name)			   && (e.source === d.parent) || (e.target === d)) ||
    			   ((d.parent.name==e.name)		   && (e.source === e.parent) || (e.target === d.parent)) ||
    			   ((d.parent.parent.name==e.name) && (e.source === e.parent) || (e.target === d.parent.parent));
        })
        .classed("highlight", true);

	// Highlight text of d
	svg.selectAll(".label-curvyText, .label, .label-line, .label-button, .label-button-circle, .label-button-line")
        .filter(function(j) {return (d.name==j.name);})
        .classed("highlight", true);
}

function handleMouseOut (d) {

    d3.selectAll(".highlight").classed("highlight", false);
}

function handleMouseClick (d) {

	if (d.post_content) {

		var alertBoxText  = document.getElementById("infobox-text");

		var ourHTMLString = '<h3><a id="infobox-link">' + d.post_title + "</a></h3>";
		    ourHTMLString += "<p>" + d.post_excerpt + "</p>";

		alertBoxText.innerHTML = ourHTMLString;

		var taxonomyBoxLink = document.getElementById("infobox-link");
		    taxonomyBoxLink.href = d.guid;

        showAlertBox();
	}
}

buildAlertBox();

function buildAlertBox() {

    var taxonomyTree = document.getElementById("taxonomytree");
        taxonomyTree.insertAdjacentHTML('afterend', '<div id="infobox"></div>');

    var alertBox = document.getElementById("infobox");
        alertBox.classList.add("hide-me");
        alertBox.innerHTML += '<span id="infobox-skip">'
                            + 'close'
                            + '</span>'
                            + '<div id="infobox-text"></div>'
                            + '<div id="alert-thumbnail"></div>';

    var alertSkip = document.getElementById("infobox-skip");
        alertSkip.addEventListener("click", hideAlertBox);
}

function showAlertBox() {

    var alertBox = document.getElementById("infobox");
        alertBox.classList.remove("hide-me");
}

function hideAlertBox() {

    var alertBox = document.getElementById("infobox");
        alertBox.classList.add("hide-me");
}


// Wraps texts
//QUESTION: warum fehlen hier so viele SEMIKOLONS???
function wrap(text, width, mydy) {

    text.each(function() {

        var text = d3.select(this),
        words = text.text().split(/\s+/).reverse(),
        word,
        line = [],
        lineNumber = 0,
        lineHeight = 1.1, // ems
        y = text.attr("y"),
        dy = parseFloat(text.attr("dy")),
        tspan = text.text(null).append("tspan").attr("x", 0).attr("y", y).attr("dy", "" + mydy /-2 + "px")

        while (word = words.pop()) {
            line.push(word)
            tspan.text(line.join(" "))

            if (tspan.node().getComputedTextLength() > width) {
                line.pop()
                tspan.text(line.join(" "))
                line = [word]
                tspan = text.append("tspan").attr("x", 0).attr("y", y).attr("dy", "" + mydy + "px").text(word)
            }
        }
    })
}

} // update()

}); // jQuery()
