$(document).ready(function() {


// Pointer to DOM element containing metadata about this registryObject
var metadataContainer = $('#registryObjectMetadata');
var loading_icon = '<div style="width:100%; padding:40px; text-align:center;"><img src="'+base_url+'assets/core/images/ajax-loader.gif" alt="Loading..." /><br/><br/><center><b>Loading...</b></center></div>';
var ACCORDION_MODE_SUGGESTORS = ['datacite'];
var draftText = '<small class="lightgrey">[DRAFT]</small> ';

setRegistryLink();
initDescriptionDisplay();
drawRegistryIcon();

// Check if we have a hierarchal connections graph
initViewPage();

// duplicate displays will be postponed 'till next release
//checkForDuplicateRecords();
getRelatedObjectsTitleByKey();
drawMap();
initConnections(); 
initAddTagForm();
initLinkedRecords();
initThemePageLinks();

var fw_cookie = $.cookie('falling_water_dontshow');
if(!fw_cookie && false){
	$('.open-popup-link').magnificPopup({
	  type:'inline',
	  midClick: true, // allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source.
	  removalDelay: 300,
	  mainClass: 'mfp-zoom-in'
	});
	$('.open-popup-link').magnificPopup('open');
	$('#nothanks').click(function(e){
		e.preventDefault();
		$.cookie('falling_water_dontshow', 'set', { path: '/' });
		$.magnificPopup.close();
	});
	$('#fwform').submit(function(e){
		e.preventDefault();
		var name = $('#fwform input[name=name]').val();
		var email = $('#fwform input[name=email]').val();
		if (!name) alert('Please input a name');
		if (!email) alert('Please input an email address');
		if (name && email) {
			$.ajax({
		  		type:"POST",
		  		url: base_url+"/home/falling_water_register",
		  		data: {name:name, email:email},
	  			success:function(msg){
	  				$.cookie('falling_water_dontshow', 'set', { path: '/' });
	  				$.magnificPopup.close();
	  			}
	  		}); 
		}
	});
}

// If we're a collection, then hit DataCite for SeeAlso
if ( $('#class', metadataContainer).html() == "Collection" )
{
	initDataciteSeeAlso();
	initConnectionGraph();
}

// Internal Suggested Links
initInternalSuggestedLinks();

if($('#groupsRightBox p').length > 5) {
	var groupsSize = $('#groupsRightBox p').length;
	$('#groupsRightBox p:gt(4)').hide();
	$('#groupsRightBox').append('<p><a href="javascript:;" class="show_all_groups">Show All '+groupsSize+' Organisations & Groups</a></p>');
	$('.show_all_groups').click(function(){
		$('#groupsRightBox p').show();
		$(this).remove();
	});
}

if (!isPublished()) { $('#draft_status').removeClass("hide"); }

$('.subject_vocab_filter').click(function(e){
	e.preventDefault();
	window.location = base_url+'search'+suffix+'subject_vocab_uri='+encodeURIComponent($(this).attr('vocab_uri'))+'/subject_vocab_uri_display='+encodeURIComponent($(this).text());
});

$('.subjectFilter').click(function(e){
	e.preventDefault();
	window.location = base_url+'search'+suffix+'s_subject_value_resolved='+encodeURIComponent($(this).attr('id'));
});


function initViewPage()
{
	$('img.logo').error(function() { log("error loading image: " + $(this).attr('src')); $(this).addClass('hide'); });

	$('ul.limit5').each(function(){
		if($('li', this).length>5){
			$('li:gt(4)', this).hide();
			$(this).append('<li><a href="javascript:;" class="show-all-subjects">Show More...</a></li>');
			$('.show-all-subjects').click(function(){
				$(this).parent().siblings().show();
				$(this).parent().remove();
			});
		}
	})

 
	$('.publication').each(function(){
		if($(this).attr('title')!='' && typeof $(this).attr('object_class') != 'undefined') {
			var theRel = $(this).attr('title');
			var theClass = $(this).attr('object_class').toLowerCase();
			$(this).qtip({
				content: {
				text: loading_icon,
				
				ajax: {
					url: base_url+'view/getRelationship',
					type: 'POST',
					data: {relationship: theRel, object_class: theClass},
					loading:true,
					success: function(data, status) {                                                              
						this.set('content.text', data)                     
					}
				}

				},
				style: {classes: 'ui-tooltip-light ui-tooltip-shadow seealso-tooltip',width: '250px'},
				show: 'mouseover',
				hide: 'mouseout' 
			}); 
		}else{
			$(this).qtip("disable");
		}
	});
 //   $('#displaytitle').each(function(){console.log($(this));});
}

function drawRegistryIcon(){
	if($('#class_type').length > 0){      
		var icon = $('<img />').attr('src', base_url+'assets/core/images/icons/'+$('#class_type').text()+'.png').attr('class', 'left_icon icontip').css('margin-top','-2px').height(16).width(16);

			//if there is a title, put the icon beside it
			$('.breadcrumb').append(' ').append(icon);
	   
	}
	var theType  = $('#class_type').text()
	$('.icontip').qtip({    
		content:$('#'+theType+'_explanation').html(),
		show: 'mouseover',
		hide: 'mouseout',
		style: {
		classes: 'ui-tooltip-light ui-tooltip-shadow'
		}
	})
}

function initConnections(){
	$('.preview_connection').each(function(){
		if(typeof $('a', this).attr('slug')!=='undefined'){
			generatePreviewTip($(this), $('a',this).attr('slug'), $('a', this).attr('registry_object_id'), $('a', this).attr('relation_type'), $('a', this).attr('relation_description'), $('a', this).attr('relation_url'), null);
		}else if($('a', this).attr('identifier_relation_id')!=''){
			generatePreviewTip($(this), null, null, $('a', this).attr('relation_type'), $('a', this).attr('relation_description'), $('a', this).attr('relation_url'), $('a', this).attr('identifier_relation_id'));
		}else if($('a', this).attr('draft_id')!=''){
			generatePreviewTip($(this), null, $('a',this).attr('draft_id'), $('a', this).attr('relation_type'), $('a', this).attr('relation_description'), $('a', this).attr('relation_url'), null);
			$('a', this).prepend(draftText);
		}
	});
}

$(document).on('click', 'a.suggestor_paging',function(e){
	e.preventDefault();
	updateLinksDisplay($('#links_dialog'), 'Suggested Links', $(this).attr('suggestor'), $(this).attr('offset'), 10);
}).on('click', '.user_tags a', function(e){
	e.preventDefault();
	window.location = base_url+'search'+suffix+'tag='+encodeURIComponent($(this).text());
});

function formatConnectionTip(tt){
	var tooltip = $('#ui-tooltip-'+tt.id+'-content');
	bindPaginationConnection(tooltip);
}

function bindPaginationConnection(tt){
	$('.goto', tt).on('click',function(e){
		var slug = $(this).attr('ro_slug');
		var id = $(this).attr('ro_id');
		var page = $(this).attr('page');
		var relation_type = $(this).attr('relation_type');

		if(slug != '')
			var url = base_url+'view/getConnections/?slug='+slug+'&relation_type='+relation_type+'&page='+page;
		if(id != '')
			var url = base_url+'view/getConnections/?id='+id+'&relation_type='+relation_type+'&page='+page;

		$.ajax({
			url: url, 
			type: 'GET',
			success: function(data){
				$(tt).html(data);
				bindPaginationConnection(tt);
			}
		});
	});
}

/*if (isPublished()) { $('#draft_status').removeClass("hide"); }*/
function initInternalSuggestedLinks()
{

}

function checkForDuplicateRecords(){
	var suggestor = 'ands_duplicates';
	var url_suffix = "view/getSuggestedLinks/"+suggestor+"/0/0/?id=" + getRegistryObjectID();
	$.ajax({
		url:base_url+url_suffix,
		dataType:'json',
		success:function(data){
			var count = parseInt(data.count);
			if(count > 0)
			{
				var dupLinks = '';
				$.each(data.links, function(){
					dupLinks += "<a href='"+this.url+"'>"+this.group+"</a>";               
				});
				$('#displaytitle').append("<div>duplicate(s):<br/>"+dupLinks+"</div>");
			}
		}
	});
}

function initDataciteSeeAlso(){
	var suggestor = 'datacite';
	var url_suffix = "view/getSuggestedLinks/"+suggestor+"/0/0"+ (isPublished() ? "/?slug=" + getSLUG() : "/?id=" + getRegistryObjectID());

	$.ajax({
		url:base_url+url_suffix,
		dataType:'json',
		success:function(data){
			var count = parseInt(data.count);
			if(count>0){
				var datacite_explanation = $('#datacite_explanation').html();
				var datacite_qmark = "<img class='datacite_help' src='"+base_url+"assets/core/images/question_mark.png' width='14px' />";
				$('#DataCiteSuggestedLinksBox').html('<h4>External Records</h4>' +'<h5><a href="#" class="show_accordion" data-title="Records suggested by DataCite" data-suggestor="'+suggestor+'" data-start="0" data-rows="10"> ' + data.count + " records</a> from DataCite " + datacite_qmark + "</h5>").fadeIn();
				$('.datacite_help').qtip({
					content:{text:datacite_explanation},
					title: {
						text: 'See Also DataCite',
						button: 'Close'
					},
					style: {
						classes: 'ui-tooltip-light ui-tooltip-shadow datacite-about',
						width: 400,
					},
					show:{event:'click',solo:true},
					hide:{delay:1000, fixed:true},
					position:{my:'bottom right', at:'top center'}
				});
			}
		}
	});
}


/* Hook to capture class="show_accordion" */
/* Note: will grab the current cursor and link target from
		 bound data- attributes */
/* Note: the datacite callback is pretty late to the party; attach the handler
   on the document instead
*/
	$(document).on('click', 'a.show_accordion', function(e){
	e.preventDefault();
	e.stopPropagation();
	var handler = $(this);
	var qTitle = handler.attr('data-title');
	var dialog = $('#links_dialog');
	dialog.html(loading_icon);
	updateLinksDisplay(dialog,
			   handler.attr('data-title'),
			   handler.attr('data-suggestor'),
			   handler.attr('data-start'),
			   handler.attr('data-rows'));
	handler.qtip({
		content:{
			text:dialog,
			title: {
				text: handler.attr('data-title'),
				button: 'Close'
			}
		},
		style: {
			classes: 'ui-tooltip-light ui-tooltip-shadow seealso-tooltip'
		},
		show:{solo:true},
		hide:{fixed:true, event:'unfocus'},
		position:{my:'center right', at:'left center',viewport:$(window)}
	}).qtip('show');
});

function setRegistryLink()
{
	var registryLink = $('#registryViewLink').find('a').attr('href');
	var regObjId = getRegistryObjectID();
	var newRef = registryLink + 'registry_object/view/' + regObjId;
	$('#registryViewLink').find('a').attr('href', newRef);
	$('#registryViewLink').show();
}

/* Updates the contents of an accordion window */
function updateLinksDisplay(container, title, suggestor, start, rows)
{
	// Loading icon as display loads...
	container.html(loading_icon);
	// Specify the web service endpoint
	var url_suffix = "view/getSuggestedLinks/"+suggestor+"/"+start+"/"+rows+ (isPublished() ? "/?slug=" + getSLUG() : "/?id=" + getRegistryObjectID());

	// Fire off the request
	$.get(base_url + url_suffix, function(data){   

		// Cleanup data
	data.links = $.map(data.links, function(link, idx) {
			// Clean HTML tags
		link['description'] = htmlDecode(link['description']);
		link['class'] = (link['class'] === "external" ? null : link['class']);
			link['display_footer'] = (suggestor === 'datacite' ? null : 'true');
		return link;
	});

		var template = $('#link_list_template').html();
		var output = Mustache.render(template, data);
		container.html("<div>" + output + "</div>");
		if(!$('.suggestor_paging').html())
		{
			$('#separater').html('')
		}
	},'json');
}

$('a.next_accordion_query').on('click', function(e){
	e.preventDefault();
	e = $(this);
	updateAccordion($('#links_dialog'),
			e.attr("data-title"),
			e.attr("data-suggestor"),
			e.attr("data-start"),
			e.attr("data-rows"));
});



/*************/
/* view page */
/*************/

$('a.collectionNote').on('click', function(e) {
  e.preventDefault();
});

function isPublished()
{
	return ($('#status', metadataContainer).html() == "PUBLISHED");
}

function getRegistryObjectID()
{
	return $('#registry_object_id', metadataContainer).html();
}

function getSLUG()
{
	return $('#slug', metadataContainer).html();
}

function getClassIconSrc(ro_class)
{
	switch(ro_class)
	{
		case "collection":
			return base_url+'assets/core/images/icons/collections.png';
		break;
		case "party":
			return base_url+'assets/core/images/icons/parties.png'
		break;
		case "service":
			return base_url+'assets/core/images/icons/services.png'
		break;
		case "activity":
			return base_url+'assets/core/images/icons/activities.png'
		break;
		default:
			return false;
	}
}

function traverseAndSelectChildren(tree, select_id)
{
	for (var i = tree.length - 1; i >= 0; i--) {
		if (tree[i].registry_object_id == select_id)
		{
			tree[i].select = true;
			tree[i].expand = true;
			tree[i].focus = true;
		   // tree[i].activate = true;
		}
		else
		{
			if (tree[i].children)
			{            
				tree[i].children = traverseAndSelectChildren(tree[i].children, select_id);
			}
		}
	}
	return tree;
}


function initConnectionGraph()
	{
	// Attach the dynatree widget to an existing <div id="tree"> element
	// and pass the tree options as an argument to the dynatree() function:
	var connection_params = {}
	if (isPublished())
	{
		connection_params.slug = getSLUG();
	}
	else
	{
		connection_params.id = getRegistryObjectID();
	}
	$.get( base_url + "view/connectionGraph",
			connection_params,
			function(data)
			{
				if (data && data.length>=1 && data[0].children.length>0)
				{
				  
					 //console.log(data);
					traverseAndSelectChildren(data, getRegistryObjectID()); 

					/* Generate the tree */
					$("#connectionTree").dynatree({
						children: data,
						onActivate: function(node) {
							// If this has more parts, open them...
							if (node.data.children)
							{
								node.expand();
							}
						},
						onClick: function(node) {
							if (node.data.registry_object_id != getRegistryObjectID())
							{
								$('#' + node.li.id).qtip('show'); 
							}

							// XXX: show the tooltip
							// A DynaTreeNode object is passed to the activation handler
							// Note: we also get this event, if persistence is on, and the page is reloaded.
						   //window.location = base_url + node.data.slug;
						},

						onDblClick: function(node) {
							// Change to view this record
							if (node.data.status=='PUBLISHED')
							{
								window.location = base_url + node.data.slug;
							}
							else
							{
								window.location = base_url + "view/?id=" + node.data.registry_object_id;
							}
						},

						onPostInit: function (isReloading, isError)
						{
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
							$('a',$(nodeSpan)).attr('href', base_url + node.data.slug);

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
							else
							{

								/* Prepare the tooltip preview */
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
										fixed: true,
									},
									style: {
										classes: 'ui-tooltip-light ui-tooltip-shadow previewPopup',
										width: 550,
									}
								});
							}
						},

						persist: false,
						generateIds: true,
						autoCollapse: false,
						activeVisible: true,
						autoFocus: false,
						clickFolderMode: 3, // 1:activate, 2:expand, 3:activate and expand
						imagePath: "/",
						debugLevel: 0
					});


					$('#collectionStructureWrapper a.hide.collectionNote')
						.qtip({
						  content:{
						title: {
						  text:'Browse nested collections'
						},
						text: $('#collectionStructureQtip')
						  },
						  style: {
						classes: 'ui-tooltip-light ui-tooltip-shadow previewPopup',
						width: 550
						  }
						}).removeClass('hide');
							$('#collectionStructureWrapper').show();
							}
						}, 
						'json'
				);  

}



function getRelatedObjectsTitleByKey()
{
    if($('.resolvable_key').length > 0)
    {
        $('.resolvable_key').each(function(){
            link = $(this);
            if($(this).attr('key_value')){
                title_url = base_url + 'registry/services/api/registry_objects/?fq=key:("' + encodeURIComponent($(this).attr('key_value')) + '")&fl=title';
                $.ajax({
                    type:"GET",
                    url: title_url,
                    success:function(msg){
                        title = msg.message.response.docs[0].title;
                        link.html(title);
                    }
                });
            }
            link.removeClass('hide');
        });
    }
}


function generatePreviewTip(element, slug, registry_object_id, relation_type, relation_description, relation_url, identifier_relation_id)
{
	var preview_url;
	if (slug != null)
	{
		preview_url = base_url + "preview/" + slug + '/' + registry_object_id;
		//alert(preview_url)
	}
	else if(registry_object_id != null)
	{
		preview_url = base_url + "preview/?id=" + registry_object_id;
	}
	else if(identifier_relation_id != null)
	{
		preview_url = base_url + "preview/?identifier_relation_id=" + identifier_relation_id;
	}
	/* Prepare the tooltip preview */
	$('a', element).qtip({
		content: {
			text: 'Loading preview...',
			title: {
				text: 'Preview',
				button: 'Close'
			},
			ajax: {
				url: preview_url, 
				type: 'GET',
			   // data: { "slug": slug, "registry_object_id": registry_object_id },
				success: function(data, status) {
					data = $.parseJSON(data);        
					// Clean up any HTML rubbish...                   
					var temp = $('<span/>');
					temp.html(data.html);
					$("div.descriptions", temp).html($("div.descriptions", temp).text());
					$("div.descriptions", temp).html($("div.descriptions", temp).directText());

					if (data.slug){
						$('.viewRecord',temp).attr("href", base_url + data.slug + '/' + data.registry_object_id);
					}
					else
					{
						$('.viewRecord').attr("href",base_url+"view/?id=" + data.registry_object_id);
					}
					this.set('content.text', temp.html());   
					var relDesc = '';
					var relUrl = '';
					if (data.slug){
						$('.viewRecordLink'+data.slug).attr("href",base_url + data.slug+'/'+data.registry_object_id);
						$('.viewRecord').attr("href", base_url + data.slug+'/'+data.registry_object_id);
						if(relation_type){
							
							if(relation_description)
							{
								relDesc = ' <br /><span style="color:#666666"><em>' + relation_description +'</em></span>'
							}
						   
							if(relation_url)
							{
								relUrl = ' <a href="' + relation_url +'" target="_blank"><em>(URL)</em></a></span>'
							}
						 $('.previewItemHeader'+data.slug).html(relation_type + relDesc + relUrl);
						}                       

					}else{
						$('.viewRecordLink'+data.registry_object_id).attr("href",base_url+"view/?id=" + data.registry_object_id);
						if(relation_type){
							if(relation_description)
							{
								relDesc = ' <br /><span style="color:#666666"><em>' + relation_description +'</em></span>'
							}
							if(relation_url)
							{
								relUrl = ' <a href="' + relation_url +'" target="_blank"><em>(URL)</em></a></span>'
							}                            
							$('.previewItemHeader'+data.registry_object_id).html(relation_type + relDesc + relUrl);
						}
					}                   
				} 
			}
		},
		position: {
			my: 'left center',
			at: 'right center',
			viewport: $(window)
		},
		show: {
			event: 'click',
		},
		hide: {
			delay: 1000,
			fixed: true,
		},
		style: {
			classes: 'ui-tooltip-light ui-tooltip-shadow previewPopup',
			width: 550
		},
	})
	.on('click', function(e){e.preventDefault();return false;})
	.on('dblclick', function(e){ e.preventDefault(); window.location = $(this).attr('href'); });
}

function initDescriptionDisplay()
{
	if ($('#viewRenderer').val() == 'contributor')
	{
		return;
	}
	
	// Hide multiple descriptions, with option to display all
	$('.descriptions div:gt(0)').hide();

	if ($('.descriptions div').length > 1)
	{
		$('.descriptions').after('<div class="show_all">Show All Descriptions</div>');
	}
	else
	{
		// If there is only one description, don't display a type
		$('.descriptions div h5').hide();
	}


	$('.show_all').click(function(){
		$(this).remove();
		$('.descriptions div').show();
		$('.descriptions').css({height:'auto'});
	});

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
		//console.log(coverages.text());
		
		var mapContainsOnlyMarkers = true; // if there is only marker, then zoom out to a default depth (markers get "bounded" at max zoom level)
		var locationText = [];
		
		$.each(coverages, function(){
			setTimeout('500');
			coverage = $(this).text();
			split = coverage.split(' ');
			if(split.length>1)
			{
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
		});

		//draw centers
		var centers = $('p.spatial_coverage_center');
		$.each(centers, function(){
			drawable = true;
			var marker = new google.maps.Marker({
				map: map2,
				position: stringToLatLng($(this).html()),
				draggable: false,
				raiseOnDrag:false,
				visible:true
			});
		});
		
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

function initAddTagForm(){
	var key = $('#key').text();
	$('.login').click(function(e){
		e.preventDefault();
		$('.login_banner').slideDown();
		$('html,body').animate({scrollTop:0},150);
	});

	$('.add_tag_form input#tag_value').keypress(function(e){
		if(e.which==13){
			addTag(key, $(this).val());
		}
	});

	$('.add_tag_form #tag_btn').click(function(){
		addTag(key, $('.add_tag_form input#tag_value').val());
	});

	$('.add_tag_form input#tag_value').typeahead([{
		name:'Suggestion',
		remote: base_url+'theme_page/suggestTag/false/?q=%QUERY',
		minLength:1,
		limit:5,
		cache:false,
		valueKey: 'name',
		highlight:true,
		hint:true,
		template: function(x){
			return '<p class="suggestion-source">'+x.source+'</p><p class="">'+x.name+'</p>';
		},
		'engine':Mustache
	}
	// ,{
	//     name:'Suggestion2',
	//     remote: base_url+'theme_page/suggestTag/true/?q=%QUERY',
	//     minLength:1,
	//     limit:5,
	//     cache:false,
	//     valueKey: 'name',
	//     highlight:true,
	//     hint:true,
	//     template: function(x){
	//         return '<p class="suggestion-source">'+x.source+'</p><p class="">'+x.name+'</p>';
	//     },
	//     'engine':Mustache
	// }
	]).on('typeahead:selected', function(){
		// window.location = base_url+'search/#!/q='+encodeURIComponent($('#search_box').val());
	});

	function addTag(key, tag){
		if(key && tag && tag != ''){
			$('.add_tag_form input, .add_tag_form button').attr('disabled', 'disabled');
			$.ajax({
				url:base_url+'theme_page/addTag',
				type:'POST',
				data:{key:key,tag:tag},
				success: function(data){
					$('.add_tag_form input, .add_tag_form button').removeAttr('disabled');
                    $('.add_tag_form input').val('');
                    $('.add_tag_form p').remove();
					if(data.status=='OK'){
                        if($('#tags_container').length == 0){
                            $("<p class=\"subject_type\">User Contributed Tags <a href=\"#\" class=\"tags_helper\"><i class=\"portal-icon portal-icon-info\"></i></a></p> <div class=\"tags user_tags\" id=\"tags_container\"></div>").insertBefore('.add_tag_form');
                        }
                        $('#tags_container').append('<a href="'+base_url+'search/#!/tag='+tag+'">'+tag+'</a>');
						sync();
					}else{
						if(data.message) $('.add_tag_form').append('<p>'+data.message+'</p>');
					}
				}
			});
		}
	}
}


function initLinkedRecords(){
	var num = $('#matching_identifier_count').text();
	var num = parseInt(num);
	if(num > 0){
		// Show the linked_records caret in group string on RDA
		$('.linked_records').show();

		// If URL param "fl" exists, then we came here from a linked records drop-down
		// in which case we don't show the tooltip again
		if (!checkURLParameterExists('fl'))
		{
			var text = '';
			if(num==1) {
				text = '1 Linked Record';
			}else {
				text = num + ' Linked Records';
			}
			$('.linked_records').qtip({
				content: text,
				show: {
					event: 'mouseover',
					ready: true
				},
				hide: 'mouseout',
				position: {viewport: $(window),my: 'bottom center',at: 'top center'},
				style: {
					classes: 'ui-tooltip-lightcream',
					def: 'false',
					width:135
				},
			});
		}

		// When clicked, the caret shows a dropdown of other linked records
		$('.linked_records').click(function(){
			$(this).qtip({
				content: {
					text: 'Loading...',
					ajax: {
						url: rda_service_url+'getMatchingRecordsOnIdentifiersByID/'+$('#registry_object_id').text(),
						contentType:'json',
						type: 'GET',
						success: function(data){
							data = JSON.parse(data);
							var ro_class = $('#class').text();
							//var msg ='<div class="linked_record_tooltip_title">This '+ro_class.toLowerCase()+' may also be described in:</div>';
							var text =''
							if (data.content.length == 1)
							{
								text = '1 Linked Record:';
							}
							else
							{
								text = data.content.length + ' Linked Records:';
							}
							var msg ='<div class="linked_record_tooltip_title">'+text+'</div>';
							msg += '<ul class="linkedrecords-list">';
							$.each(data.content, function(){
								msg +='<a href="'+base_url+this.slug+'/'+this.id+'?fl"><li>'+this.title+'<br/><span class="grey">Contributed by '+this.group+'</span></li></a>';
							});
							this.set('content.text', msg);

						}
					}
				},
				show: {
					event: 'click',
					ready:true
				},
				hide: 'unfocus',
				position: {viewport: $(window),my: 'top center',at: 'bottom center'},
				style: {
					classes: 'ui-tooltip-light ui-tooltip-shadow'
				}
			});
		});
	}
}

function initThemePageLinks(){

	$('.theme_page').each(function(){
		var slug = $(this).attr('slug');
		var this_div = $(this);
		$.ajax({
			url:base_url+ "theme_page/getThemePageBanner/"+slug,
			success: function(data){
				this_div.html(data);
			}
		});
	});


}

function sync(){
	var key = $('#key').text();
	$.ajax({
		url:base_url+'theme_page/syncRO',
		type:'POST',
		data:{key:key},
		success: function(data){
			
		}
	});
}

//record all resolvable identifier outbound link
$('#identifiers a[href]').addClass('recordOutBound');

$(document).on('click', '.connection_preview_link', function(e){
	e.preventDefault();
	var preview_url = base_url + "preview/?identifier_relation_id=" +  $(this).attr('identifier_relation_id');
	var thePreDiv = $(this).closest('.ro_preview').find('.ro_preview_description');
	if(thePreDiv.is(":visible"))
	{
		thePreDiv.slideToggle("slow");
	}
	else if(!(thePreDiv.is(':empty')))
	{
		thePreDiv.slideDown();
	}
	else{
		$.ajax({
			type: 'GET',
			url: preview_url,
			success:function(data){
				data = $.parseJSON(data);
				thePreDiv.html(data.html).slideDown();
			}
		});
	}
	return false;
}).on('click', '.recordOutBound', function(e){
	e.preventDefault();

	//record outbound link
	var url = $(this).attr('href');
	var from = document.URL;
	$.ajax({
		url:base_url+'view/recordoutbound',
		type:'POST',
		dataType:'json',
		data:{url:url,from:from},
		success:function(data){}
	});
	window.open(url);
});



function stringToLatLng(str){
	var word = str.split(',');
	var lat = word[1];
	var lon = word[0];
	var coord = new google.maps.LatLng(parseFloat(lat), parseFloat(lon));
	return coord;
}





jQuery('body')
.bind(
'click',
function(e){
if(
 jQuery('#dialog-modal').dialog('isOpen')
 && !jQuery(e.target).is('.ui-dialog, a')
 && !jQuery(e.target).closest('.ui-dialog').length
){
 jQuery('#dialog-modal').dialog('close');
}
}
);

});