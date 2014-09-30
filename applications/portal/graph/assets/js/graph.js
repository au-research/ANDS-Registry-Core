var viewerWidth = $(document).width();

var margin = {top: 0, right: 500, bottom: 0, left: 300},
    width = viewerWidth - margin.right - margin.left,
    height = 650 - margin.top - margin.bottom;
    
var i = 0,
    duration = 750,
    root;

var tree = d3.layout.tree()
    // .size([height, width])
    .nodeSize([50,240])
    ;

var diagonal = d3.svg.diagonal()
    .source(function(d){
      return {'x':d.source.x, 'y':d.source.y+170}
    })
    .projection(function(d) { 
      return [d.y, d.x]; 
    });

var x = d3.scale.linear()
    .domain([-width / 2, width / 2])
    .range([0, width]);

var y = d3.scale.linear()
    .domain([-height / 2, height / 2])
    .range([height, 0]);

var xAxis = d3.svg.axis()
    .scale(x)
    .orient("bottom")
    .tickSize(-height);

var yAxis = d3.svg.axis()
    .scale(y)
    .orient("left")
    .ticks(5)
    .tickSize(-width);

var svg = d3.select("#graph").append("svg")
    .attr("width", width + margin.right + margin.left)
    .attr("height", height + margin.top + margin.bottom)
    .call(d3.behavior.zoom().scaleExtent([1, 5]).on("zoom", zoom))
  .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")")
    ;
var zoomListener = d3.behavior.zoom().scaleExtent([0.1, 3]).on("zoom", zoom);

var registry_object_id = $('#registry_object_id').val();
var offset = 0;
var next_offset = 10;
var url = base_url+'graph/data/'+registry_object_id;
d3.json(url, function(error, flare) {

  root = flare;
  root.x0 = height / 2;
  root.y0 = 0;

  function collapse(d) {
    if (d.children) {
      d._children = d.children;
      d._children.forEach(collapse);
      d.children = null;
    }
  }

  root.children.forEach(collapse);
  update(root);
  centerNode(root);
});

d3.select(self.frameElement).style("height", "800px");

function zoom() {
  svg.attr("transform", "translate(" + d3.event.translate + ")scale(" + d3.event.scale + ")");
}

function update(source) {

  offset = source.offset;
  next_offset = source.next_offset;

  // Compute the new tree layout.
  var nodes = tree.nodes(root).reverse(),
      links = tree.links(nodes);

  // Normalize for fixed-depth.
  nodes.forEach(function(d) { 
    d.y = d.depth * 280;
  });

  // Update the nodes…
  var node = svg.selectAll("g.node")
      .data(nodes, function(d) { return d.id || (d.id = ++i); });

  // Enter any new nodes at the parent's previous position.
  var nodeEnter = node.enter().append("g")
      .attr("class", "node")
      .attr("transform", function(d) { return "translate(" + source.y0 + "," + source.x0 + ")"; })
      .on("click", click);
  
  

 // var rect = nodeEnter.append('rect')
 //      .attr('x', -10)
 //      .attr('y', -20)
 //      .attr('width', 200)
 //      .attr('height', 40)
 //      .attr('fill', 'white')
 //      .attr('stroke', 'lightsteelblue');


nodeEnter.append('foreignObject')
  .attr('x', -10)
  .attr('y', -20)
  .attr('width', 200)
  .attr('height', 40)
  .append('xhtml:div')
  .html(function(d){
    return '<div class="scontainer"><div class="rect"><img class="icon" src="http://devl.ands.org.au/minh/assets/img/collection.png" alt="" /><h1>'+d.name+'</h1></div></div>';
  });

// nodeEnter.append('svg:image')
//       .attr('x', 0)
//       .attr('y', -12)
//       .attr('width', 20)
//       .attr('height', 24)
//       .attr('xlink:href', 'http://devl.ands.org.au/minh/assets/img/collection.png');

  // nodeEnter.append("circle")
  //     .attr("r", 1e-6)
  //     .style("fill", function(d) { return d._children ? "lightsteelblue" : "#fff"; });

  // nodeEnter.append("text")
  //     .attr("x", function(d) { return 25; })
  //     .attr("dy", ".35em")
  //     // .attr("text-anchor", function(d) { return d.children || d._children ? "end" : "start"; })
  //     .text(function(d) { return d.name; })
  //     .attr('class', 'text')
  //     .style("fill-opacity", 1e-6);

  // Transition nodes to their new position.
  var nodeUpdate = node.transition()
      .duration(duration)
      .attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; });

  // nodeUpdate.select("circle")
  //     .attr("r", 4.5)
  //     .style("fill", function(d) { return d._children ? "lightsteelblue" : "#fff"; });

  // nodeUpdate.select("text")
  //     .style("fill-opacity", 1);

  // Transition exiting nodes to the parent's new position.
  var nodeExit = node.exit().transition()
      .duration(duration)
      .attr("transform", function(d) { return "translate(" + source.y + "," + source.x + ")"; })
      .remove();

  // nodeExit.select("circle")
  //     .attr("r", 1e-6);

  // nodeExit.select("text")
  //     .style("fill-opacity", 1e-6);

  // Update the links…
  var link = svg.selectAll("path.link")
      .data(links, function(d) { 
        return d.target.id; 
      });

  // Enter any new links at the parent's previous position.
  link.enter().insert("path", "g")
      .attr("class", "link")
      .attr("d", function(d) {
        var o = {x: source.x0, y: source.y0};
        return diagonal({source: o, target: o});
      });

  // Transition links to their new position.
  link.transition()
      .duration(duration)
      .attr("d", diagonal);

  // Transition exiting nodes to the parent's new position.
  link.exit().transition()
      .duration(duration)
      .attr("d", function(d) {
        var o = {x: source.x, y: source.y};
        return diagonal({source: o, target: o});
      })
      .remove();

  // Stash the old positions for transition.
  nodes.forEach(function(d) {
    d.x0 = d.x;
    d.y0 = d.y;
  });
}

// Toggle children on click.
function click(d) {
  centerNode(d);
  if (d.children) {
    d._children = d.children;
    d.children = null;
  } else {
    d.children = d._children;
    d._children = null;
    url = base_url+'graph/data/'+registry_object_id+'?offset='+next_offset;
    $.getJSON(url, function(addTheseJSON) {
        var newnodes = tree.nodes(addTheseJSON.children).reverse();
        d.parent.children = d.parent.children.concat(newnodes[0]);
        update(d);
    });
  }
  update(d);
}

function centerNode(source) {
    scale = zoomListener.scale();
    x = -source.y0;
    y = -source.x0;
    x = x * scale + width / 2;
    y = y * scale + height / 2;
    d3.select('g').transition()
        .duration(duration)
        .attr("transform", "translate(" + x + "," + y + ")scale(" + scale + ")");
    zoomListener.scale(scale);
    zoomListener.translate([x, y]);
}