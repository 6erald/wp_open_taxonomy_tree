/*
Open Taxonomy Tree (Wordpress Plugin)
Copyright (C) 2020 Gerald Wagner
*/
var taxonomy,
    i = 0,
    root;

jQuery(document).ready(function ($) {
var margin = {top: 20, right: 20, bottom: 20, left: 20},
    width = 900 - margin.right - margin.left,
    height = 500 - margin.top - margin.bottom;

// set size of tree layout
var tree = d3.layout.tree()
    .size([height, width]);

// rotate the coordinates for horizontal layout
var diagonal = d3.svg.diagonal()
    .projection(function(d) { return [d.y, d.x]; });

// create the svg
var svg = d3.select('#categorytree').append("svg")
    .attr("viewBox", "0 0 " + width + " " + height)
    .attr("preserveAspectRatio", "xMinYMin")
    .append("g")
    .attr("transform", "translate(" + margin.left + ","  + margin.top/2 + ")");

// ajax
jQuery.ajax({
    url: MyAjax.ajaxurl,
    data:{
        'action':'categorytree',
        'taxonomy': taxonomy
    },
    dataType: 'JSON',
    success:function(data){
        // our handler function will go here
        // this part is very important!
        // its what happens with the JSON data
        // after it is fetched via AJAX!
        // alert(JSON.stringify(data));
        root = data;
	    root.x0 = height / 2;
	    root.y0 = 0;

	    update(root);
    },
    error: function(errorThrown){
          //alert(JSON.stringify(errorThrown));
          console.log(errorThrown);
    }
});


function update(source) {

     /*
      *
      * CREATE ARRAYS AND MEASUREMENTS
      *
      */

     // create nodes
     // .nodes-method runs the data in the layout
     // .nodes returns an array with those nodes contains the name, children, depth, x-/y-coordinate
     var nodes = tree.nodes(root);

     // create links
     // .links-method is the path between the nodes
     // .links returns an array which contains soruce and target elements
     var links = tree.links(nodes);

     // create length units and
     var unitLenght = 150;
         nodes.forEach(function(d) { return d.y = d.depth * unitLenght + unitLenght; });
         nodes.forEach(function(d) {
               if (d.depth==0) {return d.y = d.y - unitLenght/3;}
               if (d.depth==2) {return d.y = d.y + unitLenght/2;}
               if (d.depth==3) {return d.y = d.y - unitLenght/6;}
         });


    /*
     *
     * CREATE ELEMENTS
     *
     */

     // create and style digaonals of all links
     svg.selectAll(".link")
         .data(links)
         .enter()
         .append("g")
         .attr("class", "diagonal")
              .append("path")
              .attr("class", "link")
              .style("stroke-width", "1")
              .style("fill", "none")
              .style("stroke",
                    function (d) {
                    if (d.target.taxonomy_color) {
                        return d.target.taxonomy_color
                    }
                    if ( d.target.parent.taxonomy_color ) {
                        return d.target.parent.taxonomy_color
                    }
                    if ( d.target.parent.parent.taxonomy_color ) {
                        return d.target.parent.parent.taxonomy_color
                    }
                    else {return "black"}
                    }
               )
              .attr("d", diagonal)
              .attr("z-index", "-100");

     // create g.node
     var node =
     svg.selectAll(".node")
          .data(nodes)
          .enter()
          .append("g")
               .attr("class", "node")
               .attr("transform", function (d) {
                    // rotate the coordinates d.x and d.y for horizontal layout
                    return "translate(" + d.y + "," + d.x  + ")"; })
               .attr("z-index", "0");

     // append names on g.node
     node.filter(function(d){
               // filter only 0 and 3rd level labels
               return d.depth==0 || d.depth==3} )
          .append("text")
          .attr("class", "labels")
          .text(function (d) {return d.name;})                                 // appends the name of the node as text
          .style("text-anchor", function(d) {                                   // switches the textanchor of depth 1 and 2
               if (d.depth==0) {return "end";}
          })
          .attr("transform", "translate(0, -5)")
          .style("fill", "black")
          .filter(function(d){return d.depth==0})
          .call(wrap, 90, 15)


     // append g.circle to level 3
     node.filter(function(d){ return d.depth==3; })
          .append("g")
          .attr("class", "circle");

     // append g.line to level 3
     node.filter(function(d){ return d.depth==3; })
          .append("g")
          .attr("class", "line");




     // create Links for blogposts
     var lineLength = 1.7*unitLenght;
     nodes.forEach(function(d){
          // request and save post_name
          if (d.post_content) {
               // create Link
               svg.selectAll(".labels")
                    .filter(function(e) { return ( d.post_title==e.name ); })
                    .text(d.post_title)                                        // insert text content in a-element
                    .style("fill", d.parent.parent.taxonomy_color )
               // create line
               svg.selectAll(".line")
                    .filter(function(e) { return ( d.post_title==e.name ); })
                         .insert("line")
                         .attr("class", "inline")
                         .attr("stroke-width", 1)
                         .attr("x1", function(d) {return 0 })
                         .attr("y1", function(d) {return 0})
                         .attr("x2", function(d) {return 0  + lineLength})
                         .attr("y2", function(d) {return 0})
                         .style("stroke", d.parent.parent.taxonomy_color )
               // create circle
               svg.selectAll(".circle")
                    .filter(function(e) { return ( d.post_title==e.name ); })
                    .insert("a")                                                // append a-element
                    .attr("xlink:href", location.href + d.post_name)            // create link element
                    .attr("class", "blog-link")
                         .insert("circle")
                         .attr("class", "mycircle")
                         .attr('r', 5)
                         .attr("cx", lineLength -5 -1.5)
                         .attr("cy", -5 -3*1.5)
                         .style("stroke", d.parent.parent.taxonomy_color )
               // insert line in circle
               svg.selectAll(".circle")
                    .filter(function(e) { return ( d.post_title==e.name ); })
                              .insert("line")
                              .attr("class", "horizontal-line")
                              .attr("stroke-width", 1)
                              .attr("x1", lineLength -9.5 )
                              .attr("y1", -5 -3*1.5)
                              .attr("x2", lineLength -3.5)
                              .attr("y2", -5 -3*1.5)
                              .style("stroke", d.parent.parent.taxonomy_color)
                              .style("z-index", 100)
               // insert line in circle
               svg.selectAll(".circle")
                    .filter(function(e) { return ( d.post_title==e.name ); })
                              .insert("line")
                              .attr("class", "vertiacal-line")
                              .attr("stroke-width", 1)
                              .attr("x1", lineLength -6.5 )
                              .attr("y1", -5 -5*1.5)
                              .attr("x2", lineLength -6.5)
                              .attr("y2", -5 -1*1.5)
                              .style("stroke", d.parent.parent.taxonomy_color)
                              .style("z-index", 100)
          }
     });

     // path names
     // create defs with ID
     svg.selectAll('.infoCurvy')
          .data(links)
          .enter()
          .append("defs").append("path")
          .attr("class", "infoCurvy")
          .attr("id", function (d){return d.target.term_id})
          .attr("d", diagonal);

     // create textCurvy related to defs
     var curvyText =
     svg.selectAll('.textCurvy')
          .data(nodes)
          .enter()
          .append('g')
          .append("text")
          .attr("dy", "-0.35em")
          .attr("class", "textCurvy")
               .append("textPath")
               .attr("xlink:href",function(d){return "#"+d.term_id;})
               .text(function(d){ return d.name })
               .attr("startOffset", "100%")
               .style("text-anchor", "end")
               .style("fill",
                    function (d) {
                    if (d.taxonomy_color) {
                        return d.taxonomy_color
                    }
                    if ( d.parent.taxonomy_color ) {
                        return d.parent.taxonomy_color
                    } else { return "black"}
                    }
               )

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

          var dname = d.name;

          // highlight taxonomy path of element
          svg.selectAll(".link")
          .filter(function(e) {
               return ( (d.name==e.name)               && (e.source === d.parent) || (e.target === d) ) ||
                      ( (d.parent.name==e.name)        && (e.source === e.parent) || (e.target === d.parent) ) ||
                      ( (d.parent.parent.name==e.name) && (e.source === e.parent) || (e.target === d.parent.parent) );
          })
          .style("stroke-width", "2.25");

          // highlight text of element
          svg.selectAll(".textCurvy, .labels")
               .style("font-weight", function(d){
                    if (d.name == dname){ return "600";}
               });

          // highlight line of element
          svg.selectAll(".inline")
               .filter(function(j){ return (d.name==j.name); })
               .style("stroke-width", "2.25");
          // highlight circle of element
          svg.selectAll(".mycircle")
               .filter(function(j){ return (d.name==j.name); })
               .style("fill", d.parent.parent.taxonomy_color);
          svg.selectAll(".vertiacal-line, .horizontal-line")
               .filter(function(j){ return (d.name==j.name); })
               .classed("highlight", true)
               .style("stroke", "white");
     }

     function handleMouseOut (d) {

               // unhighlight text and path of mouseover element
               d3.selectAll(".link, .mycircle, .inline").style("stroke-width", "1");
               d3.selectAll(".mycircle").style("fill", "white");
               d3.selectAll(".highlight")
                    .classed("highlight", false)
                    .style("stroke", d.parent.parent.taxonomy_color);
               d3.selectAll(".textCurvy, .labels").style("font-weight", "400");
     }

     function handleMouseClick (d) {

          var dname = d.name;

          if (d.post_content) {
               // console.log(dname);

               var alertBox = document.getElementById("alertbox");
               alertBox.style.display = "block";

               $("#alert-box-inner").animate({opacity: "1"}, 100);
               $("#alert-box-inner-inner").animate({opacity: "1"}, 500);
               // var alertBoxInner = document.getElementById("alert-box-inner");
               // alertBoxInner.classList.remove('hide-me');
               //
               // var alertBoxInnerInner = document.getElementById("alert-box-inner-inner");
               // alertBoxInnerInner.classList.remove('hide-me');
               // alertBox.style.borderColor = d.parent.parent.taxonomy_color ;

               var alertBoxText = document.getElementById("alert-text");
               var ourHTMLString = "";
                   ourHTMLString += "<h3>" + d.post_title + "</h3>";
                   ourHTMLString += "<p>" + d.post_excerpt + "</p>";
                   // ourHTMLString += d.post_content.split("<!--more-->", 1);
               alertBoxText.innerHTML = ourHTMLString;

               var taxonomyBoxLink = document.getElementById("alert-link");
               taxonomyBoxLink.href = location.href + d.post_name;

          }
     }

} // update()

}); // jQuery()

function removeMe(){
     $("#alert-box-inner-inner").animate({opacity: "0"}, 500);
     setTimeout(function (){
          $("#alert-box-inner").animate({opacity: "0"}, 300);
     }, 500);
     setTimeout(function() {
          // TODO: variablen nur einmal deklarieren?
          var alertBox = document.getElementById("alertbox");
          alertBox.style.display = "none";
     }, 800);

     // var alertBoxInner = document.getElementById("alert-box-inner");
     // alertBoxInner.classList.add('hide-me');
     //
     // var alertBoxInnerInner = document.getElementById("alert-box-inner-inner");
     // alertBoxInnerInner.classList.add('hide-me');



}

// wraps texts
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


/*
     Navi
 */


scrolltopoint();

function scrolltopoint() {
     let links = document.querySelectorAll('.navi a');
     let i = 0;
     for (i=0; i<links.length; i++) {
          links[i].onclick = function (e) {
               e.preventDefault();

               elementToScroll = document.getElementById('scroll-container');
               elementToScroll.removeEventListener("scroll", removeScrollEvent);

               // Get Mittle of the Container
               scrollContainerWidth = document.getElementById("scroll-container").offsetWidth;
               scrollContainerCenter = scrollContainerWidth / 2;

               // Get Scrollpostion of Anchorpoint
               scrollTarget = e.target.getAttribute("href").substring(1);
               scrollTargetPosition = document.getElementById(scrollTarget).getBoundingClientRect().left;

               // Scrolloffset
               toScrollLeft = scrollTargetPosition - scrollContainerCenter;

               // Scroll
               elementToScroll.scrollLeft += toScrollLeft;

               removeScrollEvent();

               // add class "visible" to actual Linksbutton
               e.target.classList.add("visible");

               setTimeout(function(){
                    elementToScroll.addEventListener("scroll", removeScrollEvent);
               }, 1000);
          }
     }
}

function removeScrollEvent() {
     let newLinks = document.querySelectorAll('.navi a');
     for (var j = 0; j < newLinks.length; j++) {
          newLinks[j].classList.remove("visible");
     }
}


document.getElementById("scroll-container").addEventListener("scroll", hideGradient);

function hideGradient() {

     //   get offsetWidth of #scrollcontainer
     let scrollContainer = document.getElementById("scroll-container");
     let scrollContainerWidth = scrollContainer.offsetWidth;

     // if #scrollcontainer.offsetWidth < 991px
     if (scrollContainerWidth < 991) {

          // TODO: margin über element holen?
          // get treeContainerWidth
          treeContainerWidth = document.getElementById('categorytree').offsetWidth;
          treeContainerWidth = treeContainerWidth * 1.10;

          // #scrollcontainer scrollLeft
          scrollConatinerScrollLeft = document.getElementById('scroll-container').scrollLeft;

          //   if #scrollcontainer scrollLeft + offsetWidth >1000
          myScrollOffset = scrollContainerWidth + scrollConatinerScrollLeft
          myGradient = document.getElementsByClassName('gradient right');

          if (myScrollOffset > 1150) {
               myGradient[0].style.opacity = "0";
          } else {
               myGradient[0].style.opacity = "1";
          }
     }
}
