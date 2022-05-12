/*jshint esversion: 6 */
$(document).ready(function() {
    initConnectionGraph();
    // drawMap();
    setTimeout( "drawMap()",500 );
    //console.log($.browser)

	$('#rightsContent').hide();
    $('#licenceContent').hide();
	$('#dataformats').hide();
	$(document).on('click', '#toggleRightsContent', function(e){
		e.preventDefault();
        if($('#toggleRightsContent').html()=='view details')
        {
            $('#toggleRightsContent').html('hide details')
        }else{
            $('#toggleRightsContent').html('view details')
        };
		$('#rightsContent').slideToggle();
	}).on('click', '#gotodata', function(e){
		e.preventDefault();
		$('#dataformats').slideToggle();
	}).on('click', '#toggleLicenceContent', function(e){
        e.preventDefault();
        if($('#toggleLicenceContent').html()=='view details')
        {
            $('#toggleLicenceContent').html('hide details')
        }else{
            $('#toggleLicenceContent').html('view details')
        };
        $('#licenceContent').slideToggle();

    });



    $('a[qtip_popup]').qtip({
        content: {
            text: $('a[qtip_popup]').attr('qtip_popup')
        },
        show: {
            delay: 1000,
            solo: false,
            ready: true
        },
        hide: {
            delay: 1000,
            fixed: true,
        },
        position: {viewport: $(window),my: 'bottom center',at: 'top center'},
        style: {
            classes: 'qtip-bootstrap',
            def: 'false',
            width:135
        }

    });

    $('a[qtip]').mouseover(function(){
        $(this).qtip('hide');
        $('a[qtip]').qtip({
            content:{
                text:function(e,api){
                    var tip = $(this).attr('qtip');
                    var content = tip;
                    if(tip.indexOf('#')==0 || tip.indexOf('.')==0) {
                        if($(tip.toString()).length) {
                            content = $(tip.toString()).html();
                        }
                    }
                    return content;
                }
            },
            show: {
                event: 'mouseover, click',
                ready: true
            },
            hide: {
                delay: 1000,
                fixed: true,
            },
            position: {target:'mouse', adjust: { mouse: false }, viewport: $(window) },
            style: {classes: 'qtip-light qtip-shadow qtip-normal qtip-bootstrap'}
        });
    });

});

$(document).on('click', '.ro_preview', function(event){
	event.preventDefault();
	$(this).qtip({
		show:{event:'click'},
        hide: {
            delay: 1000,
            fixed: true,
        },
		content: "Loading...",
		events: {
			show: function( event, api ) {
				api.elements.content.html('Loading...');
				var element = api.elements.target;
				if ($(element).attr('ro_id')) {
					var url = base_url+'registry_object/preview/?ro_id='+$(element).attr('ro_id')+'&omit='+$('#ro_id').val();
				} else if($(element).attr('identifier_relation_id')) {
					var url = base_url+'registry_object/preview/?identifier_relation_id='+$(element).attr('identifier_relation_id');
				} else if($(element).attr('identifier_doi')) {
					var url = base_url+'registry_object/preview/?identifier_doi='+$(element).attr('identifier_doi');
				}

                if (url && $(element).attr('href').indexOf('source=')) {
                    var source = $(element).attr('href').split('source=')[1];
                    url += '&source='+source;
                }

                console.log(url);

				if (url) {
					$.ajax({
						url: url,
						success: function(content) {
							api.elements.content.html(content);
							api.reposition(null, false);
						}
					});
				} else {
					return 'Error displaying preview';
				}
			}
		},
		position: {
			target:'mouse',
			adjust: { mouse: false },
			viewport: $(window),
			my: 'left center'
		},
		style: {classes: 'qtip-light qtip-shadow qtip-normal qtip-bootstrap'},
		show: {
			event:event.type,
			ready:'true'
		}
	},event);
});


function traverseAndSelectChildren(tree, select_id) {
	//console.log(tree);
    for (var i = tree.length - 1; i >= 0; i--) {
		if (tree[i].registry_object_id == select_id) {
			tree[i].select = true;
			tree[i].expand = true;
			tree[i].focus = true;
           		   // tree[i].activate = true;
		} else {
			if (tree[i].children) {
				tree[i].children = traverseAndSelectChildren(tree[i].children, select_id);
			}
		}
	}
	return tree;
}

function drawMap(){//drawing the map on the left side

    if($('#spatial_coverage_map').length > 0){//if there is a coverage
        var latlng = new google.maps.LatLng(-25.397, 133.644);
        var myOptions = {
            zoom: 2,disableDefaultUI: true,center:latlng,panControl: true,zoomControl: true,mapTypeControl: true,scaleControl: true, scrollwheel:false,
            streetViewControl: false,overviewMapControl: true,mapTypeId: google.maps.MapTypeId.TERRAIN
        };
        var map2 = new google.maps.Map(document.getElementById("spatial_coverage_map"),myOptions);
        var bounds = new google.maps.LatLngBounds();

        //draw coverages
        var coverages = $('p.coverage');
        //console.log(coverages.html());
        // console.log(coverages.text());

        var mapContainsOnlyMarkers = true; // if there is only marker, then zoom out to a default depth (markers get "bounded" at max zoom level)
        var locationText = [];

        $.each(coverages, function(){
            // setTimeout('500');

            coverage = $(this).text();
            if (coverage!='') {
	            split = coverage.split(' ');
	            if(split.length>1) {
	                mapContainsOnlyMarkers = false;
	                coords = [];
	                $.each(split, function(){
	                    coord = stringToLatLng(this);
	                    coords.push(coord);
	                    bounds.extend(coord);
	                });
	                poly = new google.maps.Polygon({
	                    paths: coords,
	                    strokeColor: "#FF0000",
	                    strokeOpacity: 0.8,
	                    strokeWeight: 2,
	                    fillColor: "#FF0000",
	                    fillOpacity: 0.35
	                });
	                poly.setMap(map2);
	            }else{
	                var marker = new google.maps.Marker({
	                    map: map2,
	                    position: stringToLatLng($(this).html()),
	                    draggable: false,
	                    raiseOnDrag:false,
	                    visible:true
	                });
	                bounds.extend(stringToLatLng($(this).html()));
	            }
	        }
        });

        //DEPRECATED the drawing of centres
        // var centers = $('p.spatial_coverage_center');
        // $.each(centers, function(){
        // 	if($(this).html() !=''){
        // 		drawable = true;
        // 		var marker = new google.maps.Marker({
        // 		    map: map2,
        // 		    position: stringToLatLng($(this).html()),
        // 		    draggable: false,
        // 		    raiseOnDrag:false,
        // 		    visible:true
        // 		});
        // 	}
        // });

        // console.log(bounds);
        map2.fitBounds(bounds);

        if (mapContainsOnlyMarkers)
        {
            // CC-197/CC-304 - Center map on markers
            // fitBounds tends to wrap to max zoom level on markers
            // we still want a "good" fit if there are multiple markers, but
            // if we're zoomed to close, lets zoom out once the map loads!
            var listener = google.maps.event.addListenerOnce(map2, "idle", function() {
                if (map2.getZoom() > 3) map2.setZoom(3);
            });
        }

        google.maps.event.trigger(map2, 'resize');
        map2.setZoom( map2.getZoom() );
    }
}


function initConnectionGraph() {

	// hide the container until the tree is fully loaded and have data
	$("#nested-collection-tree-container").hide();

	/**
	 * Visit the node and set data
	 *
	 * @param {FancytreeNode} n
	 */
	function visitNode(n) {

		// don't do anything to paging node or status node
		if (n.isPagingNode() || n.isStatusNode()) {
			return;
		}

		// set and make visible the current node
		if (n.data.identifier === $("#ro_id").val()) {
			n.setSelected(true);
			n.makeVisible({scrollIntoView: false});
		}

		// if this node is supposed to be a parent node
		if (n.data.childrenCount > 0) {

			n.data.folder = true;

			// if there's no children, then it's a lazy node
			if (n.children === null && !n.isLazy()) {
				n.resetLazy();
			}

			// there are children, expand this node
			if (n.children && n.children.length > 0) {
				n.setExpanded(true);
			}

			// if there are children and there are supposed to be more children, attempt to add paging node
			if (typeof n.children == "undefined") {
				n.children = [];
			}

			if (n.data.childrenCount > n.children.length) {
				let hasPagingNode = n.children.filter(function (nc) {
					return nc.isPagingNode();
				}).length > 0;

				// only add paging node if it doesn't already have one
				if (!hasPagingNode) {

					let offset = n.children.length - 1;
					let excludeIDs = [];

					// special case, if there's exactly 1 children (initial load)
					if (n.children.length === 1) {
						excludeIDs.push(n.children[0].data.identifier);
						offset = offset > 0 ? offset - 1 : offset;
					}

					// exclude the current record (because it should already loaded)
					excludeIDs.push($("#ro_id").val());

					// comma separated
					excludeIDs = excludeIDs.join();

					n.addPagingNode({
						title: "Load More...",
						statusNodeType: "paging",
						icon: false,
						url: api_url + 'registry/records/' + n.data.identifier + '/nested-collection-children?offset=' + offset + '&excludeIDs=' + excludeIDs
					});
				}
			}

		}
	}

	// initialise the fancytree for nested collection
	$("#nested-collection-tree").fancytree({
		activeVisible: true,

		source: {
			url: api_url + 'registry/records/'+$('#ro_id').val()+'/nested-collection',
			cache: false
		},

		icon: function(event, data) {
			if (data.node.icon !== false) {
				return "fa fa-folder-open icon-portal";
			}
			return "";
		},

		// upon expansion of a lazy node
		lazyLoad: function(event, data) {
			let n = data.node;
			data.result = {
				url: api_url + 'registry/records/'+n.data.identifier+'/nested-collection-children',
				cache: false
			};
		},

		// when new children are loaded, visit them
		loadChildren: function(event, data) {
			let node = data.node;
			if (node.children) {
				$.each(node.children, function(index, n) {
					visitNode(n);
				});
			}

			// revisit the parent due to the loaded data might not contain the full set
			visitNode(node);
		},

		// visit all nodes initially
		init: function(event, data) {
			data.tree.visit(function(node) {
				visitNode(node);
			});

			// only display the tree if there are more than 1 nodes
			if (data.tree.count() > 1) {
				$("#nested-collection-tree-container").show();
			}
		},

		// add ro_preview and update the href to match
		// ro_preview would be automatically bind with qtip for preview
		renderNode: function(event, data) {
			let node = data.node;
			let $span = $(node.span);
			if (data.node.data.identifier && data.node.data.url) {
				$span.find('> span.fancytree-title')
					.addClass('ro_preview')
					.attr("ro_id", data.node.data.identifier)
					.attr("href", data.node.data.url);
			}
		},

		// upon clicking the PaginationNode
		clickPaging: function(event, data) {
			data.node.replaceWith({
				url: data.node.data.url
			}).done(function(data){
				if (data && data.length > 0) {
					$("#nested-collection-tree").fancytree("getTree").visit(function(n) {
						visitNode(n);
					});
				}
			});
		},

	});
}


function stringToLatLng(str){
    var word = str.split(',');
    if(word[0] && word[1]) {
    	var lat = word[1];
    	var lon = word[0];
    } else {
    	var word = str.split(' ');
    	var lat = word[1];
    	var lon = word[0];
    }
    var coord = new google.maps.LatLng(parseFloat(lat), parseFloat(lon));
    return coord;
}

