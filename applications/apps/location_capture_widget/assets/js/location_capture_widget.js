/*
  Copyright 2009 The Australian National University
  Licensed under the Apache License, Version 2.0 (the "License");
  you may not use this file except in compliance with the License.
  You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

  Unless required by applicable law or agreed to in writing, software
  distributed under the License is distributed on an "AS IS" BASIS,
  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
  See the License for the specific language governing permissions and
  limitations under the License.
*******************************************************************************/
/* ANDS Location Capture Widget - v1.1 */
;(function($) {
    "use strict";
    var WIDGET_NAME = "ANDS Location Capture Widget";
    var WIDGET_NS = "location_widget";
    var DEFAULT_PROTOCOL = "https://";
    // var DEFAULT_PROTOCOL = window.location.protocol === 'https:' ?
    // 	"https://" :
    // 	"http://";
    var DEFAULT_SERVICE_POINT = DEFAULT_PROTOCOL +
	'services.ands.org.au/api/resolver';
    var ENABLE_GAZETTEER = false;
    //some display constants
    var POLY_COLOUR      = '#008dce';
    var EDIT_POLY_COLOUR = '#ff5b00';
    var OPEN_POLY_COLOUR = '#ff0000';

    // Globals and constants
    var TOOL_POINT_PREFIX  = 'alw_tool_point';
    var TOOL_REGION_PREFIX = 'alw_tool_region';
    var TOOL_BOX_PREFIX = 'alw_tool_box';
    var TOOL_TEXT_PREFIX   = 'alw_tool_text';
    var TOOL_SEARCH_PREFIX = 'alw_tool_search';
    var EMPTY_MAP_PREFIX = "alw_clear";
    var RESET_MAP_PREFIX = "alw_reset"

    var CONTROL_ID_PREFIX                  = 'alw_control_';
    var CANVAS_ID_PREFIX                   = 'alw_canvas_';
    var ADDRESS_SEARCH_DIALOG_ID_PREFIX    = 'alw_search_';
    var ADDRESS_SEARCH_TEXTFIELD_ID_PREFIX = 'alw_search_textfield';
    var ADDRESS_SEARCH_RESULTS_ID_PREFIX   = 'alw_search_results';
    var LONLAT_DIALOG_ID_PREFIX            = 'alw_lonlat_';
    var LONLAT_TEXTAREA_ID_PREFIX          = 'alw_lonlat_textarea';
    var INLINE_MESSAGE_ID_PREFIX           = 'alw_msg_';

    var PLUGIN_DEFAULTS = {
	target: "geoLocation",              //final resting place of the target data
	lonLat: false,                      //initial feature
	endpoint: DEFAULT_SERVICE_POINT,
	gasset_protocol: DEFAULT_PROTOCOL,
	zoom: 3,                           //starting zoom
	start: "133, -27",                 //starting point of the map
	jumpToPoint: true,                 //jump to existing point?
        mode: false                        //start in mode (search|coords)
    };
    return_callback: false			//return the data through a function

    /**
     * Is our widget instance initialised?
     * @param the widget container (jquery object)
     * @return boolean
     */
    function inited(welem) {
	var widget_data = welem.data(WIDGET_NS);
	return typeof(widget_data) !== 'undefined';
    }

    var methods = {
	//obtain the widget's underlying google map
	googlemap: function() {
	    var $this = $(this);
	    if (inited($this) && false) {
		var widget_data = $this.data(WIDGET_NS);
		return widget_data.map;
	    }
	    else {
		alert(WIDGET_NAME + " not initialised!");
	    }
	},
	//default operation: set up the map
	init: function(options) {
	    //if we've already been initialised, fail silently
	    if (inited($(this))) {
		return $(this);
	    }
	    var settings = $.extend({}, PLUGIN_DEFAULTS, options);

	    return this.each(function() {
		var $this = $(this);
		var $target = $("#" + settings.target);
		$('<div id="' + INLINE_MESSAGE_ID_PREFIX + $this.attr('id') + '"/>').insertBefore($this);
		var $msgBox = $("#" + INLINE_MESSAGE_ID_PREFIX + $this.attr('id'));
		$msgBox.hide();
		$this.addClass('alw_control');
		$this.removeClass("alw_loaderror");

		if (typeof($this.attr('id')) === 'undefined') {
		    //need to give it some kind of ID
		    var wcount = $(".ands_location_map_container").length;
		    if (wcount > 0) {
			wcount += 1;
			$this.attr('id','ands_location_map_container-' + wcount);
		    }
		    else {
			$this.attr('id','ands_location_map_container');
		    }
		    $this.addClass('ands_location_map_container');
		}

		/*
		 * ensure we have a target input that isn't already being used:
		 *  - if it doesn't exist, create it
		 *  - if it exists, and is being used by another plugin instance, throw an error
		 */
		
		if (!$target.length) {
		    $target = $('<input id="' + settings.target +
				'" name="' + settings.target +
				'" data-alwtarget="' + $this.attr('id') +
				'" type="hidden" />');
		    $target.insertAfter($this);
		}
		else if (typeof($target.data('alwtarget')) !== 'undefined') {
		    showError('The specified target field `' +
			      escape(settings.target) + '` is being used as a ' +
			      'target for another map (id: `' +
			      $target.data('alwtarget') + '`). ' +
			      'Review plugin configuration and try again.',
			     $this);
		    return;
		}


		//set up the initial coordinate data if provided
		if (settings.lonLat !== false) {
		    $target.val(settings.lonLat);
		}

		//set up widget data
		$this.data(WIDGET_NS, {
		    'input_field_orig': null,
		    'pushpin': null,
		    'pushpin_edit': null,
		    'shadow': null,
		    'polygon': null,
		    'geocoder': null,
		    'error_message': null,
		    'map': null,
		    'marker': null,
		    'drawing_manager': null,
		    'tools': {},
		    'feature_types': {},
		    'marker_listeners': []
		});

		//set the starting point
		try {
		    settings.start = getCoordsFromString(settings.start)[0];
		    if (!(settings.start instanceof google.maps.LatLng)) {
			showError("Invalid start point: specify 'start' option as '<i>longitude</i>, <i>latitude</i>'");
			return $this;
		    }
		}
		catch (e) {
		    showError("Invalid start point");
		    return $this;
		}

		//check non-default mode, if specified
		if (settings.mode !== false) {
		    if (settings.mode !== 'search' &&
			settings.mode !== 'coords') {
			showError('Invalid mode: currently, only <i>search</i> and <i>coords</i> are valid modes');
			return $this;
		    }
		}



		/**
		 * Pull down some feature data from the ANDS resolver, and build
		 * the map and associated controls.
		 */
		function makeMapWidget() {
            loadFeatureTypes();
		}

		/**
		 * Get the widget's error message
		 * @return (string) any error messages registered
		 */
		function getErrorMessage() {
		    var widget_data = $this.data(WIDGET_NS);
		    return widget_data.error_message;
		}

		/**
		 * Set the widget's error message
		 * @param the error message
		 */
		function setErrorMessage(message) {
		    var widget_data = $this.data(WIDGET_NS);
		    widget_data.error_message = message;
		    $this.data(WIDGET_NS, widget_data);
		}

		/**
		 * Pull down map features (states, other cruft) from
		 * the ANDS resolver
		 */
		function loadFeatureTypes() {
		    $.each(['feature'],
			   function(idx, type) {
			       var source = settings.endpoint + '?feature=' +
				   type + '&callback=?';
			       $.getJSON(source,
					 function(data) {
                         if(data.status == 'OK'){
                            ENABLE_GAZETTEER = true;
					        addFeatureTypes(data);
                            getMapControl();
                         }
					 });
			   });
		}

		/**
		 * Store feature data with the plugin instance
		 * @param feature data
		 */
		function addFeatureTypes(data) {
		    var widget_data = $this.data(WIDGET_NS);
		    for (var i=0; i < data.items.length; i++ ) {
			var title = data.items[i].title;
			var id = data.items[i].id;
			widget_data.feature_types[id] = title;
		    }
		    $this.data(WIDGET_NS, widget_data);
		}

		/**
		 * If invoked with initialisation coordinates,
		 * save these as the original details for a
		 * 'reset' command to restore
		 * @param the initial coordinate data
		 */
		function setOriginalInputFieldValue(value) {
		    var widget_data = $this.data(WIDGET_NS);
		    widget_data.input_field_orig = value;
		    $this.data(WIDGET_NS, widget_data);
		}

		/**
		 * Get the original initialisation data
		 * @return the original initialisation coordinates, or null, or empty string
		 */
		function getOriginalInputFieldValue() {
		  return $this.data(WIDGET_NS).input_field_orig;
		}

		/**
		 * Constructs the map container and controls, including various
		 * settings
		 */
		function getMapControl() {
		    var map = null;
		    var widget_data = $this.data(WIDGET_NS);
		    try {
			// Set up for reset map.
			setOriginalInputFieldValue($target.val());

			// Initialise this instance.
			var mapCanvasId = CANVAS_ID_PREFIX + $this.attr('id');

			getToolBarHTML($this);
			getAddressSearchDialogHTML($this);
			getLonLatDialogHTML($this);
			$this.append('<div class="alw_canvas" id="' + mapCanvasId + '"/>');

			// Build any icons we might need.
			if (!widget_data.pushpin_edit) {
			    widget_data.pushpin_edit = new google.maps.MarkerImage(settings.gasset_protocol + 'maps.google.com/intl/en_us/mapfiles/ms/micons/orange.png',
										   new google.maps.Size(32,32),
										   new google.maps.Point(0,0),
										   new google.maps.Point(16,32));
			}

			if (!widget_data.pushpin) {
			    widget_data.pushpin = new google.maps.MarkerImage(settings.gasset_protocol + 'maps.google.com/intl/en_us/mapfiles/ms/micons/blue.png',
									      new google.maps.Size(32,32),
									      new google.maps.Point(0,0),
									      new google.maps.Point(16,32));

			}

			widget_data.shadow = new google.maps.MarkerImage(settings.gasset_protocol + 'maps.google.com/intl/en_us/mapfiles/ms/micons/msmarker.shadow.png',
		    							 new google.maps.Size(36,32),
									 new google.maps.Point(0,0),
									 new google.maps.Point(16,32));



			var mapCanvas = $('#'+mapCanvasId);
			var myOptions = {
			    zoom: settings.zoom,
			    disableDefaultUI: true,
			    center: settings.start,
			    panControl: true,
			    zoomControl: true,
			    mapTypeControl: true,
			    scaleControl: true,
			    streetViewControl: false,
			    overviewMapControl: true,
			    mapTypeId: google.maps.MapTypeId.TERRAIN
			};
			map = new google.maps.Map(mapCanvas[0], myOptions);
		        var bounds = new google.maps.LatLngBounds();
			// Set the map from any existing values when it's completed loading.
		        var loadinglistener = google.maps.event.addListenerOnce(map,
									    "idle",
									    function()
									    {
										setMapFromInputField(true);
									    });
			// Set the default cursors.
			map.setOptions({draggableCursor:"default"});
			map.setOptions({draggingCursor:"move"});
			widget_data.map = map;
			widget_data.geocoder = new google.maps.Geocoder();
			$this.data(WIDGET_NS, widget_data);
			//invoke a user-defined mode (if it exists)
			if (settings.mode !== false) {
			    switch(settings.mode) {
			    case 'search':
				showAddressSearchDialog($("#" + TOOL_SEARCH_PREFIX + $this.attr('id')));
				break;
			    case 'coords':
				showLonLatDialog($("#" + TOOL_TEXT_PREFIX + $this.attr('id')));
				break;
			    }
			}
		    }
		    catch(e) {
		    	//console.error(e);
		    	// The Google Maps API probably didn't load. Not much we can do.
			showError('The mapping tool has failed to load. ' +
				  'Your browser must allow non-HTTPS content ' +
				  'to load on this page in order to use this ' +
				  'tool.' +
				  '<br/><br/>(JSException: ' + e + ')');
		    }
		}

		/**
		 * Display an error message.
		 * @param the message to display. if undefined, use the
		 * plugin's `error_message` data
		 * @param the container to hold the message. if undefined,
		 * use $this
		 */
		function showError(message, container) {
		    if (typeof(container) === "undefined") {
			container = $this;
		    }
		    if (typeof(message) === "undefined") {
			message = getErrorMessage();
		    }
		    container.addClass("alw_loaderror");
		    container.html(message);
		    if (container.is(":hidden"))
		    {
			$('<a style="float:right" id = "' + container.attr('id') + '-close" href="#">[X]</a><br/>').prependTo(container);
			$(document).on("click",
				       "#" + container.attr('id') + '-close',
				       function(event) {
					   event.preventDefault();
					   $(this).parent().hide();
					   return false;
				       });
			container.show();
		    }
		}

		/**
		 * Create the widget toolbar (point|region|search|coord|clear|reset)
		 * @param the element to insert the toolbar into
		 */
		function getToolBarHTML(welem) {
		    var id = null;
		    var html = '<div class="alw_toolbar">';
		    var widget_data = $this.data(WIDGET_NS);

		    // point
		    id = TOOL_POINT_PREFIX + $this.attr('id');
		    widget_data.tools[id] = false;
		    $this.on("click", "#" + id, null, function() { startPoint("#" + TOOL_POINT_PREFIX + $this.attr('id')); });
		    html += '<span class="alw_tool" id="' + id + '" title="Mark a point on the map">point</span>';

		    // region
		    id = TOOL_REGION_PREFIX + $this.attr('id');
		    $this.on("click", "#" + id, null, function() { startRegion($("#" + TOOL_REGION_PREFIX + $this.attr('id'))); });
		    widget_data.tools[id] = false;
		    html += '<span class="alw_tool" id="' + id + '" title="Draw a polygon on the map to mark a region">region</span>';

		    // Box
		    id = TOOL_BOX_PREFIX + $this.attr('id');
		    $this.on("click", "#" + id, null, function() { beginDrawingBox(); });
		    widget_data.tools[id] = false;
		    html += '<span class="alw_tool" id="' + id + '" title="Draw a polygon on the map to mark a region">box</span>';

		    // search...
		    id = TOOL_SEARCH_PREFIX + $this.attr('id');
		    widget_data.tools[id] = false;
		    $this.on("click", "#" + id, null, function() { showAddressSearchDialog($("#" + TOOL_SEARCH_PREFIX + $this.attr('id'))); });
		    html += '<span class="alw_tool" id="' + id + '" title="Search for a place or region to mark on the map">search...</span>';

		    // coordinates...
		    id = TOOL_TEXT_PREFIX + $this.attr('id');
		    $this.on("click", "#" + id, null, function() { showLonLatDialog($("#" + TOOL_TEXT_PREFIX + $this.attr('id'))); });
		    widget_data.tools[id] = false;
		    html += '<span class="alw_tool" id="' + id + '" title="Enter longitude,latitude pairs to mark a point or a region on the map">coordinates...</span>';

		    // clear
		    id = EMPTY_MAP_PREFIX + $this.attr('id');
		    $this.on("click", "#" + id, null, function() { emptyMap(); });
		    html += '<span class="alw_special_tool cleartool" id="'+ id + '" title="Clear the marker/region data">clear</span>';

		    // reset
		    if(widget_data.input_field_orig !== null && widget_data.input_field_orig !== "") {
			id = RESET_MAP_PREFIX + $this.attr('id');
			$this.on("click", "#" + id, null, function() { resetMap(); });
			html += '<span class="alw_special_tool" id="' + id + '" title="Reset the map to its initial state">reset</span>';
		    }

		    html += '</div>';
		    $this.data(WIDGET_NS, widget_data);
		    welem.append(html);
		}

		/**
		 * Toggle a tool's active status
		 * @param Jquery selector, or HTML element for the tool in question
		 * @param (boolean) what to set the tool's active status to
		 */
		function setToolActive(tool, active) {
		    tool = $(tool);
		    var widget_data = $this.data(WIDGET_NS);
		    widget_data.tools[tool.attr('id')] = active;
		    if (active) {
			tool.addClass('alw_tool_active').removeClass('alw_tool');
		    }
		    else {
			tool.removeClass('alw_tool_active').addClass('alw_tool');
		    }
		    $this.data(WIDGET_NS, widget_data);
		}

		/**
		 * Query a tool's active status
		 * @param Jquery selector, or HTML element for the tool in question
		 * @return (boolean) the tool's active status
		 */
		function getToolActive(tool) {
		    tool = $(tool);
		    var widget_data = $this.data(WIDGET_NS);
		    return widget_data.tools[tool.attr('id')];
		}

		/**
		 * Reset tools (and their child data) to default/initial values
		 * @param centre the map as well?
		 */
		function resetTools(centre) {
		    centre = typeof(centre) === 'undefined' ? true : false;

		    var widget_data = $this.data(WIDGET_NS);
		    var id = null;
		    var object = null;

		    // =======================================================================
		    // TIDY UP THE TOOLS
		    // =======================================================================
		    $.each([TOOL_POINT_PREFIX,
			    TOOL_REGION_PREFIX,
			    TOOL_BOX_PREFIX,
			    TOOL_TEXT_PREFIX,
			    LONLAT_DIALOG_ID_PREFIX,
			    LONLAT_TEXTAREA_ID_PREFIX,
			    TOOL_SEARCH_PREFIX,
			    ADDRESS_SEARCH_DIALOG_ID_PREFIX],
			   function(idx, prefix) {
			       var object = $("#" + prefix + $this.attr('id'));
			       setToolActive(object, false);

			       //tool-specific tidying
			       switch(prefix) {
			       case LONLAT_DIALOG_ID_PREFIX:
			       case ADDRESS_SEARCH_DIALOG_ID_PREFIX:
				   object.hide();
				   break;
			       case LONLAT_TEXTAREA_ID_PREFIX:
				   object.val('');
				   break;
			       }
			   });

		    // =======================================================================
		    // TIDY UP THE MAP
		    // =======================================================================
		    // Set the cursors back to the default settings.
		    widget_data.map.setOptions({draggableCursor:"default"});
		    widget_data.map.setOptions({draggingCursor:"move"});

		    // Remove any listeners from the map.
		    $.each(widget_data.marker_listeners, function(idx, listener) {
			google.maps.event.removeListener(listener);
		    });

		    if (widget_data.drawing_manager !== null) {
			widget_data.drawing_manager.setMap(null);
		    }

		    // Redraw the map.
		    setMapFromData($target.val(), {centre:centre, reset:false});
		    $this.data(WIDGET_NS, widget_data);
		}

		/**
		 * Reset the map to initial values, including initial coordinate data
		 */
		function resetMap() {
		    setMapFromData(getOriginalInputFieldValue(), {centre:true});
		}

		function resetZoomLevel(){
			var widget_data = $this.data(WIDGET_NS);
			google.maps.event.trigger(widget_data.map, 'resize');
		}

		/**
		 * Reset the map to initial values, ignoring (original) coordinate data
		 */
		function emptyMap() {
		    setMapFromData('', {centre:true});
		}

		/**
		 * Clean up coordinate data: remove whitespace
		 * @param the coordinate data to clean
		 * @return the cleaned coordinate data
		 */
		function tidyLonLatText(lonlatText) {
		    var cleanLonLatText = lonlatText;

		    if(cleanLonLatText !== "") {
			// Remove white space from between latitude and longitude.
			cleanLonLatText = cleanLonLatText.replace(new RegExp('\\s*,\\s*', "g"), ',');
			// Convert all white space between pairs to spaces.
			cleanLonLatText = cleanLonLatText.replace(new RegExp('\\s+',"g"),' ');
			// Remove any leading and/or trailing spaces.
			cleanLonLatText = $.trim(cleanLonLatText);
		    }
		    return cleanLonLatText;
		}

		/**
		 * return and array of google.map.LatLng objects, created
		 * from the coordinate string data provided
		 * @param coordinate string data, format "<lon>, <lat>"
		 * @return an array of google.maps.LatLng objects
		 */
		function getCoordsFromString(cstr) {
		    var coords = new Array();
		    var lonlatText = tidyLonLatText(cstr);
		    if(lonlatText !== "" && validateLonLatText(lonlatText)) {
			var coordsStr = lonlatText.split(' ');
   			for(var i=0; i < coordsStr.length; i++ ) {
			    // Fill the array with GLatLngs.
			    var coordsPair = coordsStr[i].split(",");
			    coords[i] = new google.maps.LatLng(coordsPair[1],coordsPair[0]);
			}
		    }
		    return coords;
		}

		/**
		 * Validate coordinate data, checking for:
		 *   - non-numeric data
		 *   - correct number of points (1 or >2)
		 *   - valid numeric data (lat > 90; lon > 180)
		 *   - if >2 points (ie: a polygon), a closed region
		 *
		 * @param coordinate pairs
		 * @return (boolean) whether or not the data is valid
		 */
		function validateLonLatText(lonlatText) {
		    var valid = true;

		    if(lonlatText !== "") {
			var coords = lonlatText.split(' ');
			var lat = null;
			var lon = null;
			var coordsPair = null;

			// Test for a two point line.
			if(coords.length === 2) {
			    setErrorMessage("The coordinates don't represent a point or a region.");
			    valid = false;
			}

			for(var i=0; i < coords.length && valid; i++ ) {
			    // Get the lat and lon.
			    coordsPair = coords[i].split(",");
			    lat = coordsPair[1];
			    lon = coordsPair[0];

			    // Test for numbers.
			    if(isNaN(lat) || isNaN(lon)) {
				setErrorMessage('Some coordinates are not numbers.');
				valid = false;
				break;
			    }
			    // Test the limits.
			    if( Math.abs(lat) > 90 || Math.abs(lon) > 180 ) {
				setErrorMessage('Some coordinates have invalid values.');
				valid = false;
				break;
			    }

			    // Test for an open region.
			    if( i === coords.length-1 ) {
				if( coords[0] !== coords[i] ) {
				    setErrorMessage("The coordinates don't represent a point or a region. To define a region the last point needs to be the same as the first.");
				    valid = false;
				}
			    }
			}
		    }
		    return valid;
		}

		/**
		 * Clear the map, removing: map markers, polygons, and the
		 * drawing manager
		 */
		function clearMap() {
		    // Remove polygons and markers from the map.
		    removeMarker();
		    removePolygon();

		    var widget_data = $this.data(WIDGET_NS);
		    
		    if (widget_data.drawing_manager !== null) {
				widget_data.drawing_manager.setMap(null);
				$this.data(WIDGET_NS, widget_data);
		    }

		    resetZoomLevel();
		}

		/**
		 * Centre the map on the current marker (or polygon)
		 */
		function centreMap() {
		    var widget_data = $this.data(WIDGET_NS);
		    if (widget_data.polygon !== null) {
			var bounds = new google.maps.LatLngBounds();
			var i;

			// The Bermuda Triangle
			var polygonCoords = widget_data.polygon.getPath().getArray();
			for (i = 0; i < polygonCoords.length; i++) {
			    bounds.extend(polygonCoords[i]);
			}
			//resetZoom();//google map api bug fix
			widget_data.map.fitBounds(bounds);
		    }

		    // Check for a marker to centre on.
		    if (widget_data.marker !== null && settings.jumpToPoint) {
			widget_data.map.setCenter(widget_data.marker.getPosition());
		    }
		    $this.data(WIDGET_NS, widget_data);
		}

		/**
		 * Create a (single) point marker on the map
		 * @param the 'point' tool: jQuery selector or HTML object
		 */
		function startPoint(tool) {
		    tool = $(tool);
		    var widget_data = $this.data(WIDGET_NS);
		    var active = getToolActive(tool);
		    resetTools(settings.jumpToPoint);

		    // Set the cursor for dropping a marker.
		    widget_data.map.setOptions({draggableCursor:"crosshair"});

		    if (!active) {
			setToolActive(tool, true);

			// Get coords from the the input field.
			var coords = getCoordsFromString($target.val());

			// Check to see if it represents a point.
			if( coords.length === 1 ) {
			    // Show an editable marker on the map.
			    createMarker(coords[0], true);
			}

			// Add a listener with an anonymous function for dropping a new marker on the map.
			widget_data.marker_listeners.push(google.maps.event.addListener(widget_data.map,
											"click",
											function(e) {
											    if( e.latLng) {
		    										// Set the input field and reset the control.
												$target.val(e.latLng.lng().toFixed(6) + "," + e.latLng.lat().toFixed(6));
												resetTools(settings.jumpToPoint);
											    }
   											}));
			if (settings.jumpToPoint) {
			    centreMap();
			}
		    }
		}

		/**
		 * Create a new (or edit an existing) marker
		 * @param coordinates for the marker
		 * @param whether or not the marker is editable
		 */
		function createMarker(latlng, editable) {
		    var widget_data = $this.data(WIDGET_NS);
		    // Remove any previous markers or regions.
		    clearMap();
		    var marker = null;
		    if (editable) {
			// Draw a new editable marker.
			marker = new google.maps.Marker({
			    position: latlng,
			    map: widget_data.map,
			    icon : widget_data.pushpin_edit,
			    shadow: widget_data.shadow,
			    draggable : true
			});

			// Add a listener with an anonymous function for updating after dragging an editable marker.
			google.maps.event.addListener(marker,
						      "dragend",
						      function() {
							  // Set the input field and reset the control.
							  var latlng = marker.getPosition();
							  $target.val(latlng.lng().toFixed(6) + "," + latlng.lat().toFixed(6));
						      });
		    }
		    else {
			// Draw a new marker.
    			marker = new google.maps.Marker({
			    position: latlng,
			    map: widget_data.map,
			    icon : widget_data.pushpin,
			    shadow: widget_data.shadow,
			    clickable: false
			});
		    }
		    widget_data.marker = marker;
		    $this.data(WIDGET_NS, widget_data);
		    // Set the input field value to the marker location.
		    $target.val(latlng.lng().toFixed(6) + "," + latlng.lat().toFixed(6));
		}

		/**
		 * Remove the marker from the map (if one exists)
		 */
		function removeMarker() {
		    var widget_data = $this.data(WIDGET_NS);
		    // Check for a marker.
		    if (widget_data.marker !== null) {
			// Remove the marker overlay.
			widget_data.marker.setMap(null);
			widget_data.marker = null;
		    }
		    $this.data(WIDGET_NS, widget_data);
		}

		/**
		 * Create a polygon region on the map
		 * @param the 'region' tool: jQuery selector or HTML object
		 */
		function startRegion(tool) {
		    tool = $(tool);
		    var widget_data = $this.data(WIDGET_NS);
		    var active = getToolActive(tool);
		    resetTools();

		    if (!active) {
			setToolActive(tool, true);
			// Get coords from the input field value.
			var coords = getCoordsFromString($target.val());

			// Check to see if it represents a region.
			if( coords.length > 2 ) {
			    createPolygon(coords, true);
			    centreMap();
			}
			else {
			    beginDrawing('');
			}
		    }
		    $this.data(WIDGET_NS, widget_data);
		}


		/**
		 * Begin drawing (a polygon) by clicking points in sequence
		 * @param the coordinate data for the starting point
		 */
		function beginDrawing(latLng) {
		    var widget_data = $this.data(WIDGET_NS);
		    // Remove any existing temporary polygon.
		    $.each(widget_data.marker_listeners, function(idx, listener) {
			google.maps.event.removeListener(listener);
		    });

		    widget_data.drawing_manager = new google.maps.drawing.DrawingManager({
			drawingMode: google.maps.drawing.OverlayType.POLYGON,
			drawingControl: false,
			polygonOptions: {
			    fillColor: OPEN_POLY_COLOUR,
			    paths : [latLng],
			    fillOpacity: 0.2,
			    strokeColor: OPEN_POLY_COLOUR,
			    strokeWeight: 2,
			    clickable: true,
			    zIndex: 1,
			    editable: true
			}
		    });
		    widget_data.drawing_manager.setMap(widget_data.map);
		    $this.data(WIDGET_NS, widget_data);

		    google.maps.event.addListener(widget_data.drawing_manager,
						  'polygoncomplete',
						  function(polygon) {
						      var widget_data = $this.data(WIDGET_NS);
						      savePolygonString(polygon.getPath());
						      polygon.setMap(null);
						      widget_data.drawing_manager.setMap(null);
						      resetTools();
						  });
		}

		function beginDrawingBox(){

		    var widget_data = $this.data(WIDGET_NS);
		    // Remove any existing temporary polygon.
		    $.each(widget_data.marker_listeners, function(idx, listener) {
				google.maps.event.removeListener(listener);
		    });
	        widget_data.drawing_manager = new google.maps.drawing.DrawingManager({
		    	drawingMode: google.maps.drawing.OverlayType.RECTANGLE,
		    	drawingControl: false,
		    	polygonOptions: {
		    	    fillColor: OPEN_POLY_COLOUR,
		    	    fillOpacity: 0.2,
		    	    strokeColor: OPEN_POLY_COLOUR,
		    	    strokeWeight: 2,
		    	    clickable: true,
		    	    zIndex: 1,
		    	    editable: true
		    	}
	        });
	        widget_data.drawing_manager.setMap(widget_data.map);
	        $this.data(WIDGET_NS, widget_data);
	        google.maps.event.addListener(widget_data.drawing_manager, 'overlaycomplete', function(e) {
	            var geoCodeRectangle = e.overlay;
	            widget_data.box = geoCodeRectangle;
	            var bnds = geoCodeRectangle.getBounds();
	            var n = bnds.getNorthEast().lat().toFixed(6);
	        	var e = bnds.getNorthEast().lng().toFixed(6);
	        	var s = bnds.getSouthWest().lat().toFixed(6);
	        	var w = bnds.getSouthWest().lng().toFixed(6);
                var polyString = w + ',' + n + ' ' + e + ',' + n + " " + e + ',' + s + ' ' + w + ',' + s + ' ' +  w + ',' + n;
                clearMap();
                $target.val(polyString);
                var coords = getCoordsFromString(polyString);
                var polygon = new google.maps.Polygon({
                    paths: coords,
                    map : widget_data.map,
                    strokeColor: POLY_COLOUR,
                    strokeOpacity: 0.7,
                    strokeWeight: 2,
                    fillColor: POLY_COLOUR,
                    fillOpacity: 0.2,
                    editable : false
                });

            widget_data.polygon = polygon;
            $this.data(WIDGET_NS, widget_data);
	        });
		}

		/**
		 * Create a polygon on the map, removing any existing
		 * markers / regions along the way
		 * @param coordinate data
		 * @param (boolean) whether or not the polygon should be
		 * editable
		 * (editable polygons have mouse, click event listeners aded,
		 * non-editable polygons do not)
		 */
		function createPolygon(coords, editable) {
		    var widget_data = $this.data(WIDGET_NS);
		    // Remove any previous markers or regions.
		    clearMap();
		    var polygon = null;
		    if(editable) {
			polygon = new google.maps.Polygon({
			    paths: coords,
			    map : widget_data.map,
			    strokeColor: OPEN_POLY_COLOUR,
			    strokeOpacity: 0.7,
			    strokeWeight: 2,
			    fillColor: OPEN_POLY_COLOUR,
			    fillOpacity: 0.2,
			    editable : true,
			    clickable : true
			});

			google.maps.event.addListener(polygon, 'click', function(e) {
			    savePolygonString(polygon.getPath());
			});

			google.maps.event.addListener(polygon, 'mouseup', function(e) {
			    savePolygonString(polygon.getPath());
			});

			google.maps.event.addListener(polygon, 'mouseout', function(e) {
			    savePolygonString(polygon.getPath());
			});
		    }
		    else {
			// Create a non-editable, non-clickable region.
			polygon = new google.maps.Polygon({
			    paths: coords,
			    map : widget_data.map,
			    strokeColor: POLY_COLOUR,
			    strokeOpacity: 0.7,
			    strokeWeight: 2,
			    fillColor: POLY_COLOUR,
			    fillOpacity: 0.2,
			    editable : false
			});
		    }
		    widget_data.polygon = polygon;
		    $this.data(WIDGET_NS, widget_data);
		}

		/**
		 * Derive coordinate data from a polygon, saving
		 * the data into our input field
		 * @param an array of coordinates corresponding to
		 * each vertex of the polygon
		 */
		function savePolygonString(path) {
		    // Get the coordinates of the polygon vertices.

		    var coords = path.getArray();
		    var polyString = "";
		    for( var i=0; i < coords.length; i++ ) {
 			var pLat = coords[i].lat();
 			var pLng = coords[i].lng();
 			if(i === 0) {
			    polyString =  pLng.toFixed(6) + "," + pLat.toFixed(6);
 			}
			else {
			    polyString = polyString + " " + pLng.toFixed(6) + "," + pLat.toFixed(6);
			}
		    }
		    polyString = polyString + " " + coords[0].lng().toFixed(6) + "," + coords[0].lat().toFixed(6);

		    // Set the input field value.
		    $target.val(polyString);

		    if(settings.return_callback && (typeof settings.return_callback === 'function')){
				settings.return_callback(polyString);
			}
		}

		/**
		 * Remove the polygon from the map
		 */
		function removePolygon() {
		    var widget_data = $this.data(WIDGET_NS);
		    // Check that we have a reference to the polygon.
		    if(widget_data.polygon !== null ) {
			// Disable editing (even though it is probably already disabled) to enable removal to work.
			widget_data.polygon.setMap(null);
			// Remove the reference.
			widget_data.polygon = null;
		    }

		    if(widget_data.box !== undefined && widget_data.box !== null){
		    	widget_data.box.setMap(null);
		    	widget_data.box = null;
		    }
		    $this.data(WIDGET_NS, widget_data);
		}

		/**
		 * Capture 'enter' keyboard events from the search modal's text
		 * box, using such events to perform the search
		 * @param any keypress event on the search modal input text field
		 */
		function checkSearchEvent(event) {
		    var result = true;
		    if( event.which === 13 ) {
			result = false;
			doSearch();
		    }
		    return result;
		}

		/**
		 * Activate (and display) the search modal
		 * @param the toolbar item to activate
		 */
		function showAddressSearchDialog(tool) {
		    tool = $(tool);
		    var widget_data = $this.data(WIDGET_NS);
		    var active = getToolActive(tool);
		    resetTools();

		    if( !active ) {
		        setToolActive(tool, true);
		        $("#" + ADDRESS_SEARCH_DIALOG_ID_PREFIX + $this.attr('id')).show();
			// Set the focus and select the text.
			var searchResultsTextfield = $("#" + ADDRESS_SEARCH_TEXTFIELD_ID_PREFIX + $this.attr('id'));
			searchResultsTextfield.focus();
			searchResultsTextfield.select();
		    }
		}

		/**
		 * Perform a search using either the Gazetteer, or Google
		 */
		function doSearch() {
		    var widget_data = $this.data(WIDGET_NS);
		    var searchResultsTextfield = $("#" + ADDRESS_SEARCH_TEXTFIELD_ID_PREFIX + $this.attr('id'));
		    var searchText = $.trim(searchResultsTextfield.val());

		    var searchResultsDiv = $("#" + ADDRESS_SEARCH_RESULTS_ID_PREFIX + $this.attr('id'));
		    if( searchText !== '' ) {
			searchResultsDiv.html('Searching...');
  			 if($("input[name=geocoderSelector]:checked").val() === 'geocoderSelector.gazetteer') {
  			     gazetteerGeocoder(searchText);
  			 }
  			 else{
			    widget_data.geocoder.geocode({ 'address': searchText},
							 function(results, status) {
  							     addAddressToMap(results, status);});
  			 }
		    }
		    else {
			searchResultsDiv.html('Nothing to search on! Try entering some terms in the text box above');
		    }
		}

		/**
		 * Inject google search results into the search modal's results list
		 * @param the Google geocoded search results
		 * @param the Google geocoded search status
		 */
		function addAddressToMap(results, status) {
		    var markerBullet = '';
		    var resultText = "";
		    var coordString = "";
		    if(status !== google.maps.GeocoderStatus.OK) {
			resultText = "No locations found";
		    }
		    else {
			// Loop through the results
			for( var i=0; i < results.length; i++ ) {
			    var accuracy = results[i].geometry.location_type;
			    if(results[i].geometry.bounds) {
				var nE = results[i].geometry.bounds.getNorthEast();
				var sW = results[i].geometry.bounds.getSouthWest();
				coordString = nE.lng().toFixed(6) +","+ nE.lat().toFixed(6)+" ";
				coordString += sW.lng().toFixed(6) +","+ nE.lat().toFixed(6)+" ";
				coordString += sW.lng().toFixed(6) +","+ sW.lat().toFixed(6)+" ";
				coordString += nE.lng().toFixed(6) +","+ sW.lat().toFixed(6)+" ";
				coordString += nE.lng().toFixed(6) +","+ nE.lat().toFixed(6);
				markerBullet = '&#9633;';
			    }
			    else {
				coordString = results[i].geometry.location.lng().toFixed(6) +","+ results[i].geometry.location.lat().toFixed(6) ;
				markerBullet = '&#8226;';
			    }
			    resultText  += '<div class="alw_search_result" data-coord="' + coordString + '" title="Set the map with this search result">' +
				markerBullet + '&nbsp;' + results[i].formatted_address+'</div>';
			}
		    }
		    var searchResultsDiv = $("#" + ADDRESS_SEARCH_RESULTS_ID_PREFIX + $this.attr('id'));
		    searchResultsDiv.html(resultText);
		}

		/**
		 * Perform a search against the Gazetteer
		 * @param the search terms to use
		 */
		function gazetteerGeocoder(searchText) {
		    var requestUrl = settings.endpoint + '?searchText=*' + encodeURIComponent(searchText) + '*&limit=14&callback=?';
		    $.getJSON(requestUrl, function(data) {
			displayGazetteerData(data);
		    });
		}

		/**
		 * Inject Gazetteer search results into the search modal's results list
		 * @param search results data from the Gazetteer endpoint
		 */
		function displayGazetteerData(data) {
		    var widget_data = $this.data(WIDGET_NS);
		    var markerBullet = '&#8226;';
		    var resultText = "";
		    var coordString = "";
		    if(data.items_count === '0') {
			resultText = "No locations found";
		    }
		    else {
			// Loop through the results
			for( var i=0; i < data.items.length; i++ ) {
			    var pointStr = data.items[i].coords;
			    coordString = data.items[i].lat +","+ data.items[i].lng ;
			    var	typetext = '';
			    for( var j=0; j < data.items[i].types.length; j++ ) {
				if(j !== 0)
				    typetext += ', '
				if(widget_data.feature_types[data.items[i].types[j]]){
				    typetext += widget_data.feature_types[data.items[i].types[j]];
				}
				else{
				    typetext += data.items[i].types[j];
				}
			    }
			    resultText  += '<div class="alw_search_result" data-coord="' + coordString + '" title="Set the map with this search result">' + markerBullet + '&nbsp;' + data.items[i].title + ' (' + typetext + ')</div>';
			}
		    }
		    var searchResultsDiv = $("#" + ADDRESS_SEARCH_RESULTS_ID_PREFIX + $this.attr('id'));
		    searchResultsDiv.html(resultText);
		}

		/**
		 * Create and insert a modal/dialog thingy
		 * @param the (jQuery object of an) element to append the dialog to
		 * @param the dialog id attribute
		 * @param html for the content div (callback)
		 * @param html for the toolbar div (callback)
		 */
		function makeDialog(append_to, dialog_id, content_for, toolbar_for) {
		    if (typeof(toolbar_for) !== 'function') {
			toolbar_for = function() { return ""; }
		    }
		    //why you'd want a dialog with no content is beyond me, but just in case...
		    if (typeof(content_for) !== 'function') {
			content_for = function() { return ""; }
		    }
		    var dialog_shell = $('<div class="alw_dialog_container">');
		    dialog_shell.attr('id', dialog_id);
		    dialog_shell.append('<img class="alw_dialog_back src="' + settings.gasset_protocol + 'maps.google.com/mapfiles/iws_s.png" alt="" />');
		    dialog_shell.append('<div class="alw_dialog_outer">' +
					'<div class="alw_dialog_inner">' +
					'<div class="alw_dialog_content">');
		    dialog_shell.find("div.alw_dialog_content").html(content_for.call());
		    dialog_shell.find("div.alw_dialog_inner").append('<div class="alw_buttonbar">' +
								     '<button type="button" class="alw_button cancel">cancel</button>' +
								     toolbar_for.call() + '</div>');
		    append_to.append(dialog_shell);
		}

		/**
		 * Create and insert the search modal
		 * @param the jQuery object to which the modal will be appended
		 */
		function getAddressSearchDialogHTML(welem) {
		    var mapDialogId = ADDRESS_SEARCH_DIALOG_ID_PREFIX + $this.attr('id');
		    var searchResultsDivId = ADDRESS_SEARCH_RESULTS_ID_PREFIX + $this.attr('id');
		    var searchResultsTextfieldId = ADDRESS_SEARCH_TEXTFIELD_ID_PREFIX + $this.attr('id');
		    makeDialog(welem,
			       mapDialogId,
			       function() {
				   $(this).css('overflow', 'hidden');
                   if(ENABLE_GAZETTEER){
                       return '<div class="alw_dialog_text"><i>Search for a region or place to mark on the map</i></div>' +
                           '<label style="cursor:hand">' +
                           '<input type="radio" id="geocoderSelector.gazetteer" name="geocoderSelector" checked="checked" value="geocoderSelector.gazetteer" /> ' +
                           'Australian Gazetteer</label>' +
                           '<label style="cursor:hand">' +
                           '<input type="radio" id="geocoderSelector.google" name="geocoderSelector" value="geocoderSelector.google" /> ' +
                           'Google</label>' +
                           '<div class="alw_dialog_text">' +
                           '<input type="text" id="' + searchResultsTextfieldId + '" style="margin: 0px; width: 210px;" />' +
                           '&nbsp;<button type="button" class="alw_button search">search</button></div>' +
                           '<div id="' + searchResultsDivId + '" style="padding: 0px 0px 0px 8px; margin: 0px 0px 0px 0px; height: 138px; overflow:auto;">' +
                           '</div></div>';
                   }
                   else{
                       return '<div class="alw_dialog_text"><i>Search for a region or place to mark on the map</i></div>' +
                           '<div class="alw_dialog_text">' +
                           '<input type="text" id="' + searchResultsTextfieldId + '" style="margin: 0px; width: 210px;" />' +
                           '&nbsp;<button type="button" class="alw_button search">search</button></div>' +
                           '<div id="' + searchResultsDivId + '" style="padding: 0px 0px 0px 8px; margin: 0px 0px 0px 0px; height: 138px; overflow:auto;">' +
                           '</div></div>';
                   }
			       });

		    $this.on("click",
			     "#" + mapDialogId + " div.alw_dialog_text button.alw_button.search",
			     null,
			     function() { doSearch(); });
		    $this.on("click",
			     "#" + mapDialogId + " div.alw_buttonbar button.alw_button.cancel",
			     null,
			     function() { resetTools(); });
		    $this.on("click",
			     "#" + ADDRESS_SEARCH_RESULTS_ID_PREFIX + $this.attr('id') + " div.alw_search_result",
			     null,
			     function() { setMapFromData($(this).data('coord'), {centre:true}); });

		    $this.on('keypress',
			     '#' + searchResultsTextfieldId,
			     null,
			     function(event) { return checkSearchEvent(event) });
		}


		/**
		 * Create and insert the coordinate input modal
		 * @param the jQuery object to which the modal will be appended
		 */
		function getLonLatDialogHTML(welem) {
		    var mapDialogId = LONLAT_DIALOG_ID_PREFIX + $this.attr('id');
		    makeDialog(welem,
			       mapDialogId,
			       function() {
				   var content = $('<div class="alw_dialog_text"><i>Enter space delimited longitude,latitude pairs</i></div>');
				   var textarea = $('<textarea style="display: block; margin: auto; width: 278px; height: 174px;"></textarea>');
				   textarea.attr('id', LONLAT_TEXTAREA_ID_PREFIX + $this.attr('id'));
				   content.append(textarea);
				   return content;
			       },
			       function() {
				   return '&nbsp;<button type="button" class="alw_button set" title="Set the map to show this point or region">set</button>';
			      });

		    $this.on("click",
			     "#" + mapDialogId + " button.alw_button.cancel",
			     null,
			     function() { resetTools();});
		    $this.on("click",
			     "#" + mapDialogId + " button.alw_button.set",
			     null,
			     function() { setMapFromText();});
		}

		/**
		 * Display the coordinate input dialog
		 * @param the toolbar item to activate
		 */
		function showLonLatDialog(tool) {
		    tool = $(tool);
		    var active = getToolActive(tool);
		    resetTools();

		    if (!active) {
			setToolActive(tool, true);
			var lonlatTextarea = $("#" + LONLAT_TEXTAREA_ID_PREFIX + $this.attr('id'));
			lonlatTextarea.val($target.val());

			var dialog = $("#" + LONLAT_DIALOG_ID_PREFIX + $this.attr('id'));
			dialog.show();

			// Set the focus.
			lonlatTextarea.focus();
		    }
		}

		/**
		 * Set up the map using the supplied coordinate data
		 * @param the coordinate data
		 * @param options:
		 *  - validate (default: false). validate coordinate data?
		 *  - centre (default: false). centre map on data?
		 *  - reset (default: true). reset tools (before setting up)?
		 */
		function setMapFromData(data, options) {
		    var defaults = {validate: false, centre: false, reset: true};
		    options = $.extend({}, defaults, options);
		    if ((options.validate && validateLonLatText(data)) ||
			!options.validate) {
			$target.val(data);
			if (options.reset) {
			    resetTools();
			}
			setMapFromInputField(options.centre);
		    }
		    else
		    {
			showError("Problem setting map with supplied data:<br/>" +
				  getErrorMessage() + "<br/>Cancel or correct the error and set.",
				  $msgBox);
		    }
		}

		/**
		 * Clear and rebuild map using coordinate data
		 * from target, centring as appropriate
		 * @param (boolean) whether or not to centre the map
		 */
		function setMapFromInputField(centred) {
		    // Clear the map.
		    clearMap();
		    // Redraw the map with values from the input field.
		    var coords = getCoordsFromString($target.val());
		    if (coords.length === 1) {
			createMarker(coords[0], false);
		    }
		    else if (coords.length > 2) {
			createPolygon(coords, false);
		    }

		    if (centred) {
			centreMap();
		    }
		}

		/**
		 * Redraw the map, using coordinate data set from the data modal
		 */
		function setMapFromText() {
		    setMapFromData(tidyLonLatText($("#" + LONLAT_TEXTAREA_ID_PREFIX + $this.attr('id')).val()),
				   {validate:true, centre:true});
		}

		/**
		 * Finally, we can call the constructor-like function
		 */
		(function(){
		    makeMapWidget();
		}) ()
	    });
	},
    };

    $.fn.ands_location_widget = function(method) {
	if (methods[method]) {
	    return methods[method].apply(
		this, Array.prototype.slice.call(arguments, 1));
	}
	else if (typeof method === 'object' || ! method) {
	    return methods.init.apply(this, arguments);
	}
	else {
	    $.error('Method ' +  method + ' does not exist for the ' + WIDGET_NAME);
	}
    };
})( jQuery );
