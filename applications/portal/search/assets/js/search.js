var searchData = {};
var searchUrl = base_url+'search/filter';
var searchBox = null;
var map = null;
var pushPin = null;
var resultPolygons = [];//new Array();
var markersArray = [];//new Array();
var polygonsDict = {};//new Array();
var markerClusterer = null;
var rectangleOptions = null;
var infowindow = null;
var subjectType = 'anzsrc-for';
$(document).ready(function() {

	/*GET HASH TAG*/
	$(window).hashchange(function(){
		var hash = window.location.hash;
		var hash = location.href.substr(location.href.indexOf("#"));
		var query = hash.substring(3, hash.length);
		var words = query.split('/');
		// $('.tabs').hide();
		$('#search_box, #selected_group, #selected_subject').empty();
		$('div.qtip:visible').qtip('hide');
		searchData = {};
		fieldString = '';

		// Flag whether to fire a new search after this logic
		var refreshSearch = false;

		$.each(words, function(){
			var string = this.split('=');
			var term = string[0];
			var value = string[1];
			if(term && value) {
				value = decodeURIComponent(value);
				searchData[term] = value;
				switch(term){
					case 'q':
						$('#search_box').val(value);
						break;
					case 'rq': // raw queries (such as those redirected from browse/topic see-more)
						$('#search_box').val("<custom advanced search>");
						break;
					case 'group':
						$('#selected_group').html(decodeURIComponent(value));
						break;
					case 'class':
						$('.tabs a').removeClass('current');
						$('.tabs a[filter_value='+value+']').addClass('current');
						break;
					case 'tab':
						searchData['class'] = value;
						delete searchData['tab'];
						refreshSearch = true;
						break;
				}
				// If there is a search specified (being on the map page doesn't count!), show the cancel search button
				if (term != "map"){
					$('.clearAll').show();
				}
			}
			if (refreshSearch)
			{
				changeHashTo(formatSearch());
				return;
			}
			/**
			 * term could be: q, p, tab, group, type, subject, vocabUriFilter, licence, temporal, n, e, s, w, spatial
			 * resultSort, limitRows, researchGroupSort, subjectSort, typeSort, licenseSort
			 */
		});

		executeSearch(searchData, searchUrl);
		initMap();
	});
	$(window).hashchange(); //do the hashchange on page load

	 initExplanations('collection');
	 initExplanations('party');
	 initExplanations('service');
	 initExplanations('activity');

	
});

function isMapView(searchData) {
        return (typeof(searchData['map']) !== 'undefined' && searchData['map'] === 'show');
}

function executeSearch(searchData, searchUrl){
        if(infowindow)
        {
	  infowindow.close();
        }

        resultPolygons.length = 0;
	clearOverlays();
        //if we're in the map view, don't fire a search unless we have
        //search terms, or spatial coverage data.
        if (isMapView(searchData) &&
	    typeof(searchData['q']) === 'undefined' &&
	    typeof(searchData['spatial']) === 'undefined' &&
	    typeof(searchData['p']) === 'undefined') {
	        var template = $('#search-noterms-template').html();
	        var output = Mustache.render(template);
	        if (output.trim().length > 0) {
	                $('#search_notice').html(output).removeClass('hide').addClass('info');
		}
	        else {
		        $('#search_notice:not(.hide)').empty().addClass('hide');
		}
	        initSearchPage();
	}
        else {
	        $('.container').css({opacity:0.5});
	        if (isMapView(searchData)) {
	              $('#search_loading.hide').removeClass('hide');
		}
	        //add loading placeholder
		$.ajax({
			url:searchUrl,
			type: 'POST',
			data: {filters:searchData},
			dataType:'json',
			success: function(data){
				$.each(data.result.docs, function(){
					// log(this.display_title, this.score, this.id);
				});

				var numFound = data.result.numFound;
			    var numReturned = data.result.docs.length;
				$('#search-result, .pagination, #facet-result, #search_notice').empty();

				//search result
				var template = $('#search-result-template').html();
				var output = Mustache.render(template, data);
				$('#search-result').html(output);

				//pagination
				var template = $('#pagination-template').html();
				var output = Mustache.render(template, data);
				$('.pagination').html(output);

				//facet
				var template = $('#facet-template').html();
				var output = Mustache.render(template, data);
				$('#facet-result').html(output);

				//populate spatial result polygons
				var docs = data.result.docs;
				//this iteration is what causes the 'slow script' detection warning message
				//in MSIE v8. On my local machine, this message started to appear around the 500
				//record mark.
				//more information: http://support.microsoft.com/kb/175500

			        //if we're showing the map, and using an old crappy browser,
			        //limit the number of results
			        if (isMapView(searchData) &&
				    $.browser.msie === true &&
				    parseInt($.browser.version) < 9)
			        {
					    docs = docs.slice(0,500);
					}

				        //truncated results notice; only display on map view
			        if (isMapView(searchData)) {
			            var template = $('#search-trunc-template').html();
			            var truncdata = {trunc: (numFound !== docs.length),
						     found: numFound,
						     returned: docs.length};
			            var output = Mustache.render(template, truncdata);
			            if (output.trim().length > 0) {
							$('#search_notice').html(output).removeClass('hide').addClass('info');
					    }else {
							$('#search_notice:not(.hide)').empty().addClass('hide');
					    }
					}else {
					    $('#search_notice:not(.hide)').empty().addClass('hide');
					}

				$(docs).each(function(){
				 	if(typeof(this.spatial_coverage_polygons) !== 'undefined'){
					    resultPolygons.push([this.id,
								 this.spatial_coverage_polygons[0],
								 this.spatial_coverage_centres[0]]);
				 	}
				});
				initSearchPage();
		        $('.sidebar.mapmode_sidebar').show();
		        $(".contributor").each(function(){
		        	if($(this).html() == $(this).attr('slug'))
		        	{
 						// $(this).parent().addClass('contrib');
 					}
				});

			},
			error: function(data){
				$('#search-result').html('There was a problem connecting to the server. Please try again in a little while...');
			}
		}).always(function() {
		            //remove loading placeholder
			    $('.container').css({opacity:1});
			    $('#search_loading:not(".hide")').addClass('hide');
		});
	}
}

$(document).on('click', '.filter',function(e){
	if(!$(this).hasClass('remove_facet')){
		searchData['p']=1;
		searchData[$(this).attr('filter_type')] = $(this).attr('filter_value');
		if($(this).attr('filter_type')=='subject_vocab_uri'){
			searchData['subject_vocab_uri_display'] = $(this).text();
		}
		//searchData.push({label:$(this).attr('facet_type'),value:encodeURIComponent($(this).attr('facet_value'))});
		changeHashTo(formatSearch());
	}
}).on('click', '.remove_facet', function(e){
	e.preventDefault();
	e.stopPropagation();
	var filter_type = $(this).attr('filter_type');
	if($(this).attr('filter_type')=='subject_vocab_uri'){
		delete(searchData['subject_vocab_uri_display']);
	}
	if($(this).attr('filter_type')=='class'){
		$('.tabs a').removeClass('current');
		$('.tabs a[filter_value=all]').addClass('current');
	}
	delete(searchData[filter_type]);
	searchData['p']=1;
	changeHashTo(formatSearch());
}).on('click', '.toggle_sidebar', function(e){
	e.preventDefault();
	$('.sidebar').toggle();
	$('.main').toggleClass('full-width');
}).on('click', '#browse-more-subject', function(e){
	$(this).qtip({
		content: {
			text: 'Loading...',
			ajax: {
				url: base_url+'search/getsubjectfacet',
				type: 'POST', // POST or GET
				data: {filters:searchData, subjectType:subjectType}, // Data to pass along with your request
				success: function(data, status) {
					this.set('content.text', data);
					loadSubjectBrowse(subjectType);
				}
			},
			title: {
				text: 'Subjects',
				button: 'Close'
			}
		},

		show:{solo:true,ready:true,event:'click'},
	    hide:false,
	    position:{my:'top right', at:'bottom left'},
	    style: {
	        classes: 'ui-tooltip-light ui-tooltip-shadow seealso-tooltip',
	        width: 400,
	    }
	});
}).on('change', '#subjectfacet-select', function(){
	subjectType = $(this).val();
	loadSubjectBrowse($(this).val());
}).on('click', '.vocab_tree_standard ins', function(){
	$(this).siblings('ul').toggle();
	$(this).parent().toggleClass('tree_closed').toggleClass('tree_open');
}).on('click', '.vocab_tree_standard .tree_leaf span', function(e){
	if(!$(this).hasClass('tree_empty')){
		searchData['s_subject_value_resolved']=$(this).parent().attr('vocab_value');
		searchData['p'] = 1;
		changeHashTo(formatSearch());
	}
}).on('click','.clearAll',function(e){
	/* Reset the search page (set tab back to 'all') */
	e.preventDefault();

	if (searchData['map'] == 'show')
	{
		// Stay on the map search if that's where we came from
		searchData = {map:'show'};
	}
	else
	{
		searchData = {}
	}
	$('.adv_input').val('');
	$('.tabs a').removeClass('current');
	$('.tabs a[filter_value=all]').addClass('current');
	changeHashTo(formatSearch());
	$(this).hide();
}).on('click', '.post', function(e){
	e.preventDefault();
	var url = $('a.title', this).attr('href');
	window.location = url;
}).on('click', '#togglefacetsort', function(){
	if($(this).hasClass('facetsortcount')){
		$(this).removeClass('facetsortcount').addClass('facetsortaz');
		searchData['facetsort']='alpha';
	}else{
		$(this).removeClass('facetsortaz').addClass('facetsortcount');
		delete searchData['facetsort'];
	}
	changeHashTo(formatSearch());
});

function loadSubjectBrowse(val){
	if(val!='anzsrc-for'){
		$('#subjectfacet').html('Loading...');
		$.ajax({
			url:base_url+'search/getAllSubjects/'+val,
			type: 'POST',
			data: {filters:searchData},
			success: function(data){
				$('#subjectfacet').html(data);
				$('#subjectfacet ul.vocab_tree_standard ul').each(function(){
					$('li:gt(10)', this).hide();
				});
				$('#subjectfacet ul.vocab_tree_standard ul').append('<li class="show_next_10" current="10">Show More..</li>');
				$('.show_next_10').click(function(){
					var current = $(this).attr('current');
					var next = parseInt(current) + 10;
					var theList = $(this).parent();
					$('li:lt('+next+')', theList).show();
					$(this).attr('current', next);
				});
			}
		});
	}else{
		$('#subjectfacet div').remove();
		$('#subjectfacet').append($('<div/>'));
		var sqc = '';
		if(searchData['q'] && $('.fuzzy-suggest').length==0){
			sqc += "+fulltext:(" + searchData['q'] + ")";
		}else if(searchData['q'] && $('.fuzzy-suggest').length >0){
			sqc +='+fulltext:('+searchData['q']+'~0.7)';
		}
		if(searchData['class'] && searchData['class']!='all') sqc += ' +class:("'+searchData['class']+'")';
		if(searchData['group']) sqc += '+group:("'+searchData['group']+'")';
		if(searchData['type']) sqc += '+type:("'+searchData['type']+'")';
		if (sqc == ''){
			sqc = "*:*";
		}

		//sqc += '&defType=edismax';
		$('#subjectfacet div').vocab_widget({mode:'tree', repository:'anzsrc-for', sqc:encodeURIComponent(sqc), endpoint: window.default_base_url + 'apps/vocab_widget/proxy/'})
		.on('treeselect.vocab.ands', function(event) {
			var target = $(event.target);
			var data = target.data('vocab');
			searchData['subject_vocab_uri'] = encodeURIComponent(data.about);
			searchData['subject_vocab_uri_display'] = data.label;
			searchData['p'] = 1;
			changeHashTo(formatSearch());
	    });
	}
}


function initSearchPage(){
	getTopLevelFacet();

	if(urchin_id!='' && searchData['q']!=''){
		var pageTracker = _gat._getTracker(urchin_id);
		pageTracker._initData(); 
		pageTracker._trackPageview('/search_results.php?q='+searchData['q']); 
	}

	$('.tabs').show();

	//see if we need to init the map
	if(searchData['map']){
		$('#searchmap').show();
		$('.container').css({margin:'0',width:'100%',padding:'0'});
		$('.main').css({width:'100%',padding:'0'});
		$('.sidebar').addClass('mapmode_sidebar');

		if ($.browser.msie && $.browser.version <= 9.0) {
			$('.sidebar').css({opacity:1,background:'#fff'});
		}

		$('.facet_class').show();
		$('#search-result, .pagination, .page_title, .tabs').hide();
		 processPolygons();
		 resetZoom();
		 $('.post').hover(function(){
		 	clearPolygons();
			polygonsDict[$(this).attr('ro_id')].setMap(map);
		 },function(){
		 	clearPolygons();
		 });
		 if(!searchData['keepsidebar']) $('.sidebar.mapmode_sidebar').hide();
	}else{
		$('#searchmap').hide();
		$('.container').css({margin:'0 auto',width:'960px',padding:'10px 0 0 0'});
		$('.main').css({width:'633px',padding:'20px 0 0 0'});
		$('.facet_class').hide();
		$('.sidebar').removeClass('mapmode_sidebar').show();
		$('#search-result, .pagination, .page_title, .tabs').show();
		$('html,body').animate({scrollTop: $('body').offset().top}, 750);//scroll to top

	        //we don't need this in map view, and it seems to block completion of the processing
	        //of the AJAX portal/search/filter POST... (~8s delay)
	        $('.class_icon').each(function(){
			    initExplanations($(this).attr('type'));
			})
	}
	
	// Put an orange glow around topic pages to highlight them in the search results!
	$('div[ro_id^="topic_"]').css('background-color','rgba(255, 122, 0, 0.18)')

	if(typeof(searchData['q'])=='undefined' && typeof(searchData['rq'])=='undefined') {
		$('#search_box').val('');
	}

	/* Question Mark For license facet */
	$('h3', '.facet_license_class').append(' <a target="_blank" href="http://www.ands.edu.au/guides/cpguide/cpgrights.html"> <img src="'+base_url+'assets/core/images/question_mark.png" width="14px"></a>');

	$('#search_map_toggle').unbind('click');
	$('#search_map_toggle').click(function(e){
	    $('#search_notice').addClass('hide');
		e.preventDefault();
		if(searchData['map']){
			//already map, hide map
			$('#searchmap').hide();
			delete searchData['map'];
			delete searchData['spatial'];
			if(searchBox!=null){
				searchBox.setMap(null);
				//searchBox = null;
			}
			initSearchPage();
		}else{
			//no map, show map
			searchData['map']='show';
			processPolygons();
			resetZoom();
		}
		changeHashTo(formatSearch());
	});

	//add another bind for advanced search specifically for this page
	$('#adv_start_search').click(function(e){
		e.preventDefault();
		e.stopPropagation();
		$('#ad_st').removeClass('exped');
    	$('.advanced_search').slideUp('fast');
	});

	//populate the advanced search field, BLACK MAGIC, not exactly, just some bad code
	if(searchData['q']){
		var q = searchData['q'];
		q = decodeURIComponent(q);

		if((q.split('"').length-1) % 2 === 0 && q.split('"').length!=1){//if the number of quote is even
			all = q.match(/"([^"]+)"/)[1];//anything inside quote
			rest = q.split(q.match(/"([^"]+)"/)[0]).join('');
		}else{
			all ='';
			rest = q;
		}
		$('.adv_all').val(all);
		rest_split = rest.split(" ");
		var nots = [];
		var inputs = '';
		$.each(rest_split, function(){
			if(this.indexOf('-')==0){//anything starts with - is nots
				nots.push(this.substring(1,this.length));
			}else{
				inputs += this+' ';//anything else is normal
			}
		});
		$('.adv_input').val($.trim(inputs));
		$('.adv_not').each(function(e,k){//populate the nots
			$(this).val(nots[e]);
		});
	}

	//update search term if there's no blocking ie no fuzzy search conducted or no result
	if(searchData['q']!='' && $('.block-record').length==0){
		$.ajax({
			url:base_url+'search/registerSearchTerm', 
			type: 'POST',
			data: {term:searchData['q'], num_found:$('#numFound').text()},
			success: function(data){
				
			}
		});
	}

	if(searchData['temporal']){
		$('#rst_range').attr('checked', 'checked');
		$('#slider').show();
		$('#slider').editRangeSlider('resize');
		var temporal = searchData['temporal'].split('-');
		$("#slider").editRangeSlider("min",parseInt(temporal[0]));
		$("#slider").editRangeSlider("max",parseInt(temporal[1]));
	}

	$('#search_box').unbind('keypress').keypress(function(e){
		if(e.which==13){//press enter
			searchData['q']=$(this).val();
			changeHashTo(formatSearch());
		}
	});

	$('#searchTrigger').unbind('click').live('click', function(){
		searchData['q']=$('#search_box').val();
		changeHashTo(formatSearch());
    });

	$('.excerpt').each(function(){

		// This will unencode the encoded entities, but also hide any random HTML'ed elements
		$(this).html(htmlDecode(htmlDecode(htmlDecode($(this).html()))));
		$(this).html($(this).directText());

		var thecontent = $(this).html();
		var newContent = ellipsis(thecontent, 200);
		if(thecontent!=newContent){
			newContent = '<div class="hide" fullExcerpt="true">'+thecontent+'</div>' + newContent + '';
		}

		$(this).html(newContent);
		}
	);

	// Clean up encoding issues in titles
	$('.post a.title').each(function(){$(this).html(htmlDecode($(this).html()));})

	// Hide logos which point to invalid resources.
	$('.post img').error(function() { log("error loading image: " + $(this).attr('src')); $(this).addClass('hide'); })

	$('.showmore_excerpt').click(function(){
		$(this).parent().html($(this).parent().children(0).html());
	});

	//init the facet sorting functionality
	if(searchData['facetsort']){
		$('#togglefacetsort').addClass('facetsortaz').removeClass('facetsortcount').attr('tip', 'Search option categories (below)  are currently being sorted alphabetically');
	}else{
		$('#togglefacetsort').removeClass('facetsortaz').addClass('facetsortcount').attr('tip', 'Search option categories (below) are being sorted by the number of matching records');
	}

	$('#sort-button').qtip({
		content:$('#sort-menu').html(),
		show: {
			event: 'click'
		},
		hide:{
			event: 'unfocus'
		},
		style:{
			classes:'ui-tooltip-light ui-tooltip-shadow'
		}
	})
}

function initExplanations(theType)
{
    $('.icontip_'+theType).qtip({
    	content: $('#'+theType+'_explanation').html(),
    	show: 'mouseover',
    	hide: 'mouseout',
    	style: {
    	classes: 'ui-tooltip-light ui-tooltip-shadow'
    	}
	})
}

function getTopLevelFacet(){
	var fuzzy = false;
	if($('.fuzzy-suggest').length>0){
		fuzzy = true;
	}
	$.ajax({
		url:base_url+'search/getTopLevel',
		type: 'POST',
		data: {filters:searchData, fuzzy:fuzzy},
		success: function(data){
			var template = $('#top-level-template').html();
			var output = Mustache.render(template, data);
			$('#top_concepts').html(output);
		},
		complete:function(){
			postSearch();
		}
	});
}

function postSearch(){

	// if(searchData['facetsort']=='alpha'){
	// 	var mylist = $('ul#top_concepts');
	// 	var listitems = mylist.children('li').get();
	// 	listitems.sort(function(a, b) {
	// 	   var compA = $(a).text().toUpperCase();
	// 	   var compB = $(b).text().toUpperCase();
	// 	   return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
	// 	})
	// 	$.each(listitems, function(idx, itm) { mylist.append(itm); });
	// }

    $('.sidebar ul').each(function(idx, facet){

		if($('li', facet).length>5){
		    var $facet = $(facet);
		    $('li:gt(4)', facet).hide();
		    $facet.append('<li><a href="javascript:;" class="show-all-facet">Show More...</a></li>');
		    $('.show-all-facet').click(function(){
				$(this).parent().siblings().show();
				$(this).parent().remove();
		    });
		}
	});



	var selecting_facets = ['class', 'group','type','license_class'];
	$.each(selecting_facets,function(){
		if(searchData[this]){
			var facet_value = decodeURIComponent(searchData[this]);
			var facet_div = $('div.facet_'+this);
			$('.filter[filter_value="'+facet_value+'"]',facet_div).attr('tip', "Click to deselect this search constraint").addClass('remove_facet').before('<img class="remove_facet" tip="Deselect this search constraint" filter_type="'+this+'" src="'+base_url+'assets/core/images/delete.png"/>');
		}
	});

	if(searchData['s_subject_value_resolved']){
		addDeleteFacet('s_subject_value_resolved', searchData['s_subject_value_resolved']);
	}

	if(searchData['subject_vocab_uri']){
		addDeleteFacet('subject_vocab_uri', searchData['subject_vocab_uri_display']);
		$('.filter[filter_value="'+decodeURIComponent(searchData['subject_vocab_uri'])+'"]').remove();
	}

	if(searchData['temporal']){
		var temporal = searchData['temporal'].split('-');
		addDeleteFacet('temporal', temporal[0]+'-'+temporal[1]);
	}

	initTips('a.remove_facet');
}

function addDeleteFacet(type, text){
	$('.remove_facet[filter_type='+type+']').parent().remove();
	var html = '<li><img src="'+base_url+'assets/core/images/delete.png" filter_type="'+type+'" class="remove_facet" /><a href="javascript:;" class="remove_facet" tip="Deselect this search constraint" filter_type="'+type+'">'+text+'</a></li>';
	$('.facet_subjects ul').prepend(html);
}

function SidebarToggle(controlDiv, map) {
	// Set CSS styles for the DIV containing the control
	// Setting padding to 5 px will offset the control
	// from the edge of the map.
	controlDiv.style.padding = '5px';

	// Set CSS for the control border.
	var controlUI = document.createElement('div');
	controlUI.style.backgroundColor = 'white';
	controlUI.style.borderStyle = 'solid';
	controlUI.style.borderWidth = '2px';
	controlUI.style.cursor = 'pointer';
	controlUI.style.textAlign = 'center';
	controlUI.title = 'Click to set the map to Home';
	controlDiv.appendChild(controlUI);

	// Set CSS for the control interior.
	var controlText = document.createElement('div');
	controlText.style.fontFamily = 'Arial,sans-serif';
	controlText.style.fontSize = '12px';
	controlText.style.paddingLeft = '4px';
	controlText.style.paddingRight = '4px';
	controlText.innerHTML = '<strong>Show/Hide Facet</strong>';
	controlUI.appendChild(controlText);

	// Setup the click event listeners: simply set the map to Chicago.
	google.maps.event.addDomListener(controlUI, 'click', function() {
		$('.sidebar').toggle();
		if($('.sidebar').is(':visible')){
			searchData['keepsidebar'] = 'open';
		}else delete searchData['keepsidebar'];
	});
}

function initMap(){

        if(infowindow) {
	      infowindow.close();
	}
	if(searchBox){
		searchBox.setMap(null);
		searchBox = null;
	}
	if(!map)
	{
		var latlng = new google.maps.LatLng(-25.397, 133.644);
	    var myOptions = {
	      zoom: 4,
	      center: latlng,
	      disableDefaultUI: true,
	      panControl: true,
	      zoomControl: true,
	      mapTypeControl: true,
	      scaleControl: true,
	      streetViewControl: false,
	      overviewMapControl: false,
	      mapTypeId: google.maps.MapTypeId.ROADMAP
	    };



	    map = new google.maps.Map(document.getElementById("searchmap"),myOptions);

	    var homeControlDiv = document.createElement('div');
	  	var homeControl = new SidebarToggle(homeControlDiv, map);

	  	homeControlDiv.index = 1;
	  	map.controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);
		var boxOptions = {
	        content: "boxText"
	        ,alignBottom :true
	        ,disableAutoPan: false
	        ,maxWidth: 0
	        ,pixelOffset: new google.maps.Size(-140, 0)
	        ,zIndex: null
	        ,boxStyle: {
	          background: "white"
	          ,opacity: 1
	         }
	        ,closeBoxMargin: "10px 2px 2px 2px"
	        ,closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif"
	        ,infoBoxClearance: new google.maps.Size(1, 1)
	        ,isHidden: false
	        ,pane: "floatPane"
	        ,enableEventPropagation: false
	    };

	    infowindow = new InfoBox(boxOptions);

	    pushPin = new google.maps.MarkerImage('http://maps.google.com/intl/en_us/mapfiles/ms/micons/blue.png',
						      new google.maps.Size(32,32),
						      new google.maps.Point(0,0),
						      new google.maps.Point(16,32)
						     );

	    var drawingManager = new google.maps.drawing.DrawingManager({
	        drawingMode: google.maps.drawing.OverlayType.RECTANGLE,
	        drawingControl: true,
	        drawingControlOptions: {
	          position: google.maps.ControlPosition.TOP_CENTER,
	          drawingModes: [
	           // google.maps.drawing.OverlayType.MARKER,
	           // google.maps.drawing.OverlayType.CIRCLE,
	            google.maps.drawing.OverlayType.RECTANGLE
	          ],
	        },
	        rectangleOptions:{
	        	fillColor: '#FF0000'
	        }
	      });
	      drawingManager.setMap(map);
	      drawingManager.setDrawingMode(null);
	      rectangleOptions = drawingManager.get('rectangleOptions');
	      rectangleOptions.fillColor= '#FF0000';
	      rectangleOptions.strokeColor= "#FF0000";
	      rectangleOptions.fillOpacity= 0.1;
	      rectangleOptions.strokeOpacity= 0.8;
	      rectangleOptions.strokeWeight= 2;
	      rectangleOptions.clickable= false;
	      // rectangleOptions.editable= true;
	      rectangleOptions.zIndex= 1;

	      drawingManager.set('rectangleOptions', rectangleOptions);
	     google.maps.event.addListener(map, 'click', function(e) {
	     	if(infowindow)
	     	{
	     		infowindow.close();
	 		}
	 	});
	     google.maps.event.addListener(map, 'zoom_changed', function(e) {
	     	if(infowindow)
	     	{
	     		infowindow.close();
	 		}
	 	});
	     google.maps.event.addListener(drawingManager, 'overlaycomplete', function(e) {
	         if (e.type == google.maps.drawing.OverlayType.RECTANGLE) {
	          // Switch back to non-drawing mode after drawing a shape.
	        drawingManager.setDrawingMode(null);
	        if(searchBox){
	        	searchBox.setMap(null);
	        }
	        var geoCodeRectangle = e.overlay;
	        searchBox = geoCodeRectangle;
	        var bnds = geoCodeRectangle.getBounds();
	        var n = bnds.getNorthEast().lat().toFixed(6);
	    	var e = bnds.getNorthEast().lng().toFixed(6);
	    	var s = bnds.getSouthWest().lat().toFixed(6);
	    	var w = bnds.getSouthWest().lng().toFixed(6);
	    	searchBox.setMap(map);
	        searchData['spatial'] = w + ' ' + s + ' ' + e + ' ' + n;
	        changeHashTo(formatSearch());
	        }

	       });
	     google.maps.event.addListenerOnce(map,
									    "idle",
									    function()
									    {
											map.setCenter(new google.maps.LatLng(-25.397, 133.644));
									    });

		var styles = [[{
		    url: base_url+'assets/search/img/pin.png',
		    width: 80, height: 54,
		    anchor: [23, 23],
		    textColor: 'black',
		    textSize: '10'
		  }]];

		markerClusterer = new MarkerClusterer(map, null, { maxZoom: 12, gridSize: 100, styles: styles[0]});
	}
	else
	{
		// Map already loaded...re-center it...
		map.setCenter(new google.maps.LatLng(-25.397, 133.644));
	}
}


function processPolygons(){
    $.each(resultPolygons, function(idx, elem) {
	id = elem[0];
	polygons = elem[1];
	centers = elem[2];
	// log(id);
	createResultPolygonWithMarker(polygons, centers, id);
    });
}

function createResultPolygonWithMarker(polygons, centers, id)
{

	//var coords = getCoordsFromInputField(polygons);
	var centerCoords = getPointFromString(centers);
	var coords = getCoordsFromString(polygons);
        createMarker(centerCoords, id);
        createPolygon(coords, id);
}

function getCoordsFromString(lonLatStr)
{

	var coords = new Array();
	if(lonLatStr){
		var coordsStr = lonLatStr.split(' ');
		for( var i=0; i < coordsStr.length; i++ )
		{
			coordsPair = coordsStr[i].split(",");
			coords[i] = new google.maps.LatLng(coordsPair[1],coordsPair[0]);
		}
		//check if first point is the same as last... if not copy fist coords to the end.
		//if(coordsStr.length > 1 && coordsStr[0] !== coordsStr[i-1])
		//{
		//	coordsPair = coordsStr[0].split(",");
		//	coords[i] = new google.maps.LatLng(coordsPair[1],coordsPair[0]);
		//}
	}
//}
	return coords;
}

function getPointFromString(lonLatStr)
{
	lonLatStr = lonLatStr + " ";
	var coordsStr = lonLatStr.split(' ');
	coords = new google.maps.LatLng(coordsStr[1],coordsStr[0]);
	return coords;
}

function createPolygon(coords, id)
{
    if (typeof(polygonsDict[id]) === 'undefined') {
	polygon = new google.maps.Polygon({
			    paths: coords,
			    map : null,
			    strokeColor: '#008dce',
			    strokeOpacity: 0.7,
			    strokeWeight: 2,
			    fillColor: '#008dce',
			    fillOpacity: 0.2,
			    editable : false
			  });
	polygonsDict[id] = polygon;
    }
}

function createMarker(latlng, id)
{
	var marker = new google.maps.Marker({
	          position: latlng,
	          map: map,
	          icon : pushPin,
	        });
	marker.set("id", id);
	google.maps.event.addListener(marker,"mouseover",function(){
		clearPolygons();
	    polygonsDict[marker.id].setMap(map);
	});
	google.maps.event.addListener(marker,"click",function(){

		showPreviewWindowConent(marker);
	});
	google.maps.event.addListener(marker,"mouseout",function(){
		clearPolygons();
	});
	markerClusterer.addMarker(marker);
        markersArray.push(marker);
}


function clearOverlays()
{
	if(typeof markerClusterer!= 'undefined' && markerClusterer){
		markerClusterer.clearMarkers();
	}
	clearMarkers();
	clearPolygons();
}

function clearMarkers(){
    $.each(markersArray, function(idx, marker) {
	marker.setMap(null);
    });
}

function showPreviewWindowConent(mOverlay)
{
	roIds = [];
	// either a marker is passed or a marker_cluster

    if(typeof mOverlay.id != 'undefined')
    {
    	//log(mOverlay);
    	roIds.push(mOverlay.id);
    	infowindow.setPosition(mOverlay.position);
    }
    else if(typeof mOverlay.markers_ != 'undefined')
    {
    	//log(mOverlay);
    	$(mOverlay.markers_).each(function(){
    	roIds.push(this.id);
    	});
    	infowindow.setPosition(mOverlay.center_)
    }
    if(roIds)
    {
		$.ajax({
			url:base_url+'view/preview',
			data : {roIds:roIds},
			type: 'POST',
			dataType:'json',
			success: function(data){
				infowindow.setContent(data.html);//
				infowindow.open(map);
			},
			error: function(data){
				//$('body').prepend(data.responseText);
				//console.error("ERROR" + data.responseText);
			        log("ERROR" + data.responseText);
				return null;
			}
		});
	}
}

function clearPolygons()
{
    $.each(polygonsDict, function(idx, polygon) {
	polygon.setMap(null);
    });
}

function resetZoom(){
        if (typeof(map) !== 'undefined' && map !== null) {
	  google.maps.event.trigger(map, 'resize');
	}
	if(searchBox)
	{
		//map.setCenter(searchBox.getBounds().getCenter());
		searchBox.setMap(map);
		//log(searchBox);
		//log("if searchBox lat:" + searchBox.getBounds().getCenter().lat() + " lng: " + searchBox.getBounds().getCenter().lng());
		//map.fitBounds(searchBox.getBounds());
	}
	else if(searchData['spatial']){
		//harvester https support test
		var spatialBounds = searchData['spatial'];
		spatialBounds = decodeURI(spatialBounds);
		var wsenArray = spatialBounds.split(' ');
		var sw = new google.maps.LatLng(wsenArray[1],wsenArray[0]);
		var ne = new google.maps.LatLng(wsenArray[3],wsenArray[2]);
		//148.359375 -32.546813 152.578125 -28.998532
		//LatLngBounds(sw?:LatLng, ne?:LatLng)
		var rBounds = new google.maps.LatLngBounds(sw,ne);
		//var rectangleOptions = new google.maps.RectangleOptions();
	  	rectangleOptions.fillColor= '#FF0000';
	  	rectangleOptions.strokeColor= "#FF0000";
	  	rectangleOptions.fillOpacity= 0.1;
	  	rectangleOptions.strokeOpacity= 0.8;
	  	rectangleOptions.strokeWeight= 2;
	  	rectangleOptions.clickable= false;
	  	rectangleOptions.bounds = rBounds;

	  	var geoCodeRectangle = new google.maps.Rectangle(rectangleOptions);
		geoCodeRectangle.setMap(map);
	  	searchBox = geoCodeRectangle;
	  	//searchBox.setMap(null);
	 	//map.setCenter(rBounds.getCenter);

	 	//log("no searchBox: lat:" + searchBox.getBounds().getCenter().lat() + " lng: " + searchBox.getBounds().getCenter().lng());
		//map.fitBounds(searchBox.getBounds());
	}

}
function findClusters()
{
	return $('div', $('#searchmap')).filter(function() {
	    var self = $(this);
	    return self.css('background-image').match(/pin\.png/);
	});
}

function clearClusters()
{
	$.each(findClusters(), function(i, val) { $(val).html(''); });
}

function formatSearch()
{
	var query_string = '#!/';
	$.each(searchData, function(i, v){
		query_string += i + '=' + encodeURIComponent(v) + '/';
	})
	return query_string;
}

function changeHashTo(location){
	window.location.hash = location;
}

/**
 * Triggers the clusterclick event and zoom's if the option is set.
 */
ClusterIcon.prototype.triggerClusterClick = function() {
  var markerClusterer = this.cluster_.getMarkerClusterer();

  // Trigger the clusterclick event.
  google.maps.event.trigger(markerClusterer, 'clusterclick', this.cluster_);

  var identical = checkIdenticalMarkers(this.cluster_);
  if (!identical){
    // Zoom into the cluster.
    this.map_.fitBounds(this.cluster_.getBounds());
  }
  else{
	showPreviewWindowConent(this.cluster_);
  }

};

function checkIdenticalMarkers(cluster){
	var identical = true;
	$.each(cluster.markers_, function(){
		if(!this.position.equals(cluster.markers_[0].position)) {
			identical = false;
			return identical;
		}
	});
	return identical;
}
