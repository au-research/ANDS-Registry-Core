app.directive('resolve', function($http, $log, vocab_factory){
	return {
		template: '<ul class="listy"><li ng-repeat="item in result"><a href="" ng-click="toggleFilter(\'anzsrc-for\', item.notation, true)">{{item.label | toTitleCase}} <small><i class="fa fa-remove"></i></small></a></li></ul>',
		scope: {
			subjects: '=subjects',
			vocab: '='
		},
		transclude: true,
		link: function(scope) {
			scope.result = [];
			scope.$watch('subjects', function(newv){
				if(newv) {
					scope.result = [];
					vocab_factory.resolveSubjects(scope.subjects).then(function(data){
						// $log.debug(data);
						angular.forEach(data, function(label, notation){
							scope.result.push({notation:notation,label:label});
						});
						// $log.debug(scope.result);
					});
				}
			});

			scope.toggleFilter = function(type, value, execute) {
				scope.$emit('toggleFilter', {type:type,value:value,execute:execute});
			}
		}
	}
});

app.directive('mappreview', function($log, uiGmapGoogleMapApi){
	return {
		template: '<a href="" ng-click="advanced(\'spatial\')"><img src="{{static_img_src}}"/></a><div></div>',
		scope: {
			sbox: '=',
			centres: '=',
			polygons: '=',
			draw:'='
		},
		transclude:true,
		link: function(scope, element) {
			uiGmapGoogleMapApi.then(function(){
				if(element && scope.draw=='map'){
					//the map
					var myOptions = {
					  zoom: 4,
					  center: new google.maps.LatLng(-25.397, 133.644),
					  disableDefaultUI: true,
					  panControl: true,
					  zoomControl: true,
					  mapTypeControl: true,
					  scaleControl: true,
					  streetViewControl: false,
					  overviewMapControl: false,
					  scrollwheel:false,
					  mapTypeId: google.maps.MapTypeId.ROADMAP
					};
					$(element).height('200px');
					map = new google.maps.Map(element[0],myOptions);

					var bounds = new google.maps.LatLngBounds();

					//centres
					angular.forEach(scope.centres, function(centre){
						$log.debug('centre', stringToLatLng(centre).toString());
						var marker = new google.maps.Marker({
							map:map,
							position: stringToLatLng(centre),
		        		    draggable: false,
		        		    raiseOnDrag:false,
		        		    visible:true
						});
					});

					//polygons
					angular.forEach(scope.polygons, function(polygon){
						$log.debug('polygon', polygon);
						split = polygon.split(' ');
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
						    poly.setMap(map);
						}else{
						    var marker = new google.maps.Marker({
						        map: map,
						        position: stringToLatLng(polygon),
						        draggable: false,
						        raiseOnDrag:false,
						        visible:true
						    });
						    bounds.extend(stringToLatLng(polygon));
						}
					});
					// $log.debug(bounds);
					map.fitBounds(bounds);
					// $log.debug(map.getZoom());

					google.maps.event.addListenerOnce(map, 'bounds_changed', function() {
					    $log.debug('bound change zoom', map.getZoom());
					});

				} else if(element && scope.draw=='static') {
					scope.static_img_src = 'https://maps.googleapis.com/maps/api/staticmap?center=-32.75,144.75&zoom=8&size=328x200&maptype=roadmap&markers=color:red%7C|-32.75,144.75&path=color:0xFFFF0033|fillcolor:0xFFFF0033|weight:5|-32.5,145|-33,145|-33,144.5|-32.5,144.5|-32.5,145';

					var src = 'https://maps.googleapis.com/maps/api/staticmap?maptype=roadmap&size=328x200';

					//center
					if(scope.centres && scope.centres.length > 0){
						var center = scope.centres[0];
						var lat,lon;
						angular.forEach(scope.centres, function(centre){
							var coord = stringToLatLng(centre);
							lat = coord.lat();
							lon = coord.lng();
						});
						src +='&center='+lat+','+lon;
					}
					

					//markers
					var markers = [];
					// if(scope.centres && scope.centres.length > 0) {
					// 	markers.push(lat+','+lon);
					// }

					//bounds
					var bounds = new google.maps.LatLngBounds();

					//polygon
					var polys = [];
					angular.forEach(scope.polygons, function(polygon){
						split = polygon.split(' ');
						if(split.length>1) {
						    mapContainsOnlyMarkers = false;
						    coords = [];
						    $.each(split, function(){
						        coord = stringToLatLng(this);
						        coords.push(coord);
						        bounds.extend(coord);
						    });
						    poly = new google.maps.Polygon({
						        paths: coords
						    });
						    // $log.debug(poly.getPath());
						    // $log.debug('encoded', google.maps.geometry.encoding.encodePath(poly.getPath()));
						    polys.push(google.maps.geometry.encoding.encodePath(poly.getPath()));
						}else{
							var coord = stringToLatLng(polygon);
							markers.push(coord.lat()+','+coord.lng());
						    bounds.extend(coord);
						}
					});

					if (markers.length > 0){
						// $log.debug(markers);
						angular.forEach(markers, function(marker){
							src+='&markers=color:red%7C|'+marker;
						});
					}

					if (polys.length > 0) {
						// $log.debug(polys);
						angular.forEach(polys, function(poly){
							src+='&path=color:0xFF0000|fillcolor:0xFF000045|weight:2|enc:'+poly;
						});
					}

					var mapDim = {height:200,width:328};
					src +='&zoom='+getBoundsZoomLevel(bounds, mapDim);

					scope.static_img_src = src;
					// $log.debug(src);
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

				function extendBounds(bounds, coordinates) {
				    for (b in coordinates) {
				        bounds.extend(coordinates[b]);            
				    };
				    console.log(bounds.toString());
				};

				function getBoundsZoomLevel(bounds, mapDim) {
				    var WORLD_DIM = { height: 256, width: 256 };
				    var ZOOM_MAX = 21;

				    function latRad(lat) {
				        var sin = Math.sin(lat * Math.PI / 180);
				        var radX2 = Math.log((1 + sin) / (1 - sin)) / 2;
				        return Math.max(Math.min(radX2, Math.PI), -Math.PI) / 2;
				    }

				    function zoom(mapPx, worldPx, fraction) {
				        return Math.floor(Math.log(mapPx / worldPx / fraction) / Math.LN2);
				    }

				    var ne = bounds.getNorthEast();
				    var sw = bounds.getSouthWest();

				    var latFraction = (latRad(ne.lat()) - latRad(sw.lat())) / Math.PI;

				    var lngDiff = ne.lng() - sw.lng();
				    var lngFraction = ((lngDiff < 0) ? (lngDiff + 360) : lngDiff) / 360;

				    var latZoom = zoom(mapDim.height, WORLD_DIM.height, latFraction);
				    var lngZoom = zoom(mapDim.width, WORLD_DIM.width, lngFraction);

				    return Math.min(latZoom, lngZoom, ZOOM_MAX);
				}
			});
			// $log.debug(scope.centres);
			
			scope.advanced = function(active) {
				scope.$emit('advanced', active);
			}
		}
	}
})

app.directive('focusMe', function($timeout, $parse) {
  return {
    //scope: true,   // optionally create a child scope
    link: function(scope, element, attrs) {
      var model = $parse(attrs.focusMe);
      scope.$watch(model, function(value) {
        if(value === true) { 
          $timeout(function() {
            element[0].focus(); 
          });
        }
      });
    }
  };
})

;