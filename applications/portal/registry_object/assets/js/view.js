$(document).ready(function() {
    initConnectionGraph()
    drawMap();
    //console.log($.browser)
    
	$('#rightsContent').hide();
	$('#dataformats').hide();
	$(document).on('click', '#toggleRightsContent', function(e){
		e.preventDefault();
		$('#rightsContent').slideToggle();
	}).on('click', '#gotodata', function(e){
		e.preventDefault();
		$('#dataformats').slideToggle();
	});


    window.ATL_JQ_PAGE_PROPS =  {
        "triggerFunction": function(showCollectorDialog) {
            //Requries that jQuery is available!
            jQuery(".myCustomTrigger").click(function(e) {
                e.preventDefault();
                showCollectorDialog();
            });
        }};
	// $('.panel-body').readmore();

});

$(document).on('click', '.ro_preview', function(event){
	event.preventDefault();
	$(this).qtip({
		show:{event:'click'},
		hide:'unfocus',
		content: {
			text: function(event, api) {
				api.elements.content.html('Loading...');
				return $.ajax({
					url:base_url+'registry_object/preview/'+$(this).attr('ro_id')
				}).then(function(content){
					return content;
				},function(xhr,status,error){
					api.set('content.text', status + ': ' + error);
				});
			}
		},
		position: {target:'mouse', adjust: { mouse: false }, viewport: $(window) },
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
            zoom: 2,disableDefaultUI: true,center:latlng,panControl: true,zoomControl: true,mapTypeControl: true,scaleControl: true,
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

        //draw centers
        var centers = $('p.spatial_coverage_center');
        $.each(centers, function(){
        	if($(this).html() !=''){
        		drawable = true;
        		var marker = new google.maps.Marker({
        		    map: map2,
        		    position: stringToLatLng($(this).html()),
        		    draggable: false,
        		    raiseOnDrag:false,
        		    visible:true
        		});
        	}
            
        });

        console.log(bounds);
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
    }
}


function initConnectionGraph() {

	// Attach the dynatree widget to an existing <div id="tree"> element
	// and pass the tree options as an argument to the dynatree() function:
	if ($('#connectionTree').attr('mydata')) {
		var data = JSON.parse(decodeURIComponent($('#connectionTree').attr('mydata')));
		$('#connectionTree').removeAttr('mydata');
		var ro_id = $('#connectionTree').attr('ro_id');
		$('#connectionTree').removeAttr('ro_id');
		data = traverseAndSelectChildren(data, ro_id);

		/* Generate the tree */
		$("#connectionTree").dynatree({
			debugLevel: 0,
			children: data,
			onActivate: function(node) {
			// If this has more parts, open them...
			    if (node.data.children)	{
				    node.expand();
			    }
			},
			onClick: function(node) {
				//if (node.data.registry_object_id != ro_id())
				//{
					// $('#' + node.li.id).qtip('show');
				//}

				// XXX: show the tooltip
				// A DynaTreeNode object is passed to the activation handler
				// Note: we also get this event, if persistence is on, and the page is reloaded.
			   //window.location = base_url + node.data.slug;
			},
			onDblClick: function(node) {
			// Change to view this record
					if (node.data.status=='PUBLISHED')	{
						window.location = base_url + node.data.slug + "/" + node.data.registry_object_id;
					}
					else{
						window.location = base_url + "view/?id=" + node.data.registry_object_id;
					}
			},

			onPostInit: function (isReloading, isError) {
				// Hackery to make the nodes representing THIS registry object
				// visible, but not highlighted
				nodes = this.getSelectedNodes();
				for (var i = nodes.length - 1; i >= 0; i--) {
					nodes[i].activate();
					nodes[i].deactivate();
				};
			},

			onRender: function(node, nodeSpan) {
				var preview_url;
				if (node.data.status=='PUBLISHED')
				{
					preview_url = base_url + "preview/" + node.data.slug;
				}
				else
				{
					$('a', nodeSpan).prepend(draftText);
					preview_url = base_url + "preview/?registry_object_id=" + node.data.registry_object_id;
				}

				/* Change the icon in the tree */
				if (node.data['class']=="collection")
				{
					$(nodeSpan).find("span.dynatree-icon").css("background-position", "-38px -155px");
				}
				else if (node.data['class']=="party")
				{
					$(nodeSpan).find("span.dynatree-icon").css("background-position", "-19px -155px");
				}
				else if (node.data['class']=="service")
				{
					$(nodeSpan).find("span.dynatree-icon").css("background-position", "0px -156px");
				}
				else if (node.data['class']=="activity")
				{
					$(nodeSpan).find("span.dynatree-icon").css("background-position", "-57px -155px");
				}
				
				$(nodeSpan).attr('title', $(nodeSpan).text());
				$('a',$(nodeSpan)).attr('href', base_url + node.data.slug +"/"+node.data.registry_object_id);

				if (node.data['class']=="more")
				{
					$(nodeSpan).find("span.dynatree-icon").remove();
					var a = $('a',$(nodeSpan));

					// Bind the accordion classes and attributes
					a.addClass('view_all_connection');
					if (node.data.status=='PUBLISHED')
					{
						a.attr('ro_slug', node.data.slug);
					}
					else
					{
						a.attr('ro_id', node.data.registry_object_id);
					}

					a.attr('relation_type','nested_collection');
					a.attr('page', 2);
					//console.log($(nodeSpan).html());
				}
	            /*	else
				{

					 Prepare the tooltip preview
					$('#' + node.li.id).qtip({
						content: {
							text: 'Loading preview...',
							title: {
								text: 'Preview',
								button: 'Close'
							},
							ajax: {
								url: preview_url, 
								type: 'GET',
								//data: { "slug": node.data.slug, "registry_object_id": node.data.registry_object_id },
								success: function(data, status) {
									data = $.parseJSON(data);                                       
									var decoded_content = $(data.html);
									var content_description = htmlDecode(decoded_content.find('.post .descriptions').html());
									decoded_content.find('.post .descriptions').html('<small>' + content_description + '</small>');
									this.set('content.text', decoded_content);

									if (data.slug)
									{
										$('.viewRecord').attr("href", base_url + data.slug);
									}
									else
									{
										$('.viewRecord').attr("href",base_url+"view/?id=" + data.registry_object_id);
									}
								} 
							}
						},
						position: {
							my: 'left center',
							at: 'right center',
							target: $('#' + node.li.id + " > span")
						},
						show: {
							event: 'none',
							solo: true
						},
						hide: {
							delay: 1000,
							fixed: true
						},
						style: {
							classes: 'ui-tooltip-light ui-tooltip-shadow previewPopup',
							width: 550
						}
					});
				}*/
			}

	    });
	}

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

