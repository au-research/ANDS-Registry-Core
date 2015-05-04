app.directive('facetSearch', function($http, $log){
	return {
		templateUrl: base_url+'assets/registry_object/templates/facetSearch.html',
		scope : {
			facets: '=',
			type :'@'
		},
		link: function(scope) {
			scope.$watch('facets', function(newv){
				if(newv) {
					// $log.debug(scope.type, newv);
					scope.facet = false;
					angular.forEach(newv, function(content, index) {
						scope.facet = (content.name == scope.type ? content : scope.facet);
					});
					// $log.debug(scope.facet);
				}
			});

			scope.tipfor = function(text) {
				if(text.length >= 30) {
					return text;
				} else return 'not long enough';
			}

            scope.tip = function() {
                var text = '';
                if(scope.facet.name=='access_rights'){
                    text = "<strong>Open</strong>: Data that is readily accessible and reusable.<br />"
                    text = text + "<strong>Conditional</strong>: Data that is accessible and reusable, providing certain conditions are met (e.g. free registration is required).<br />";
                    text = text + "<strong>Restricted</strong>: Data access is limited in some way (e.g. only available to a particular group of users or at a specific physical location).<br />";
                    text = text + "<strong>Other</strong>: no value or user defined custom value.";

                }

                if(scope.facet.name=='license_class'){
                    text = "<strong>Open Licence</strong>: A licence bearing broad permissions that may include a requirement to attribute the source, or share-alike (or both), requiring a derivative work to be licensed on the same or similar terms as the reused material.<br />";
                    text = text + "<strong>Non-commercial Licence</strong>: As for the Open Licence but also restricting reuse only for non-commercial purposes.<br />";
                    text = text + "<strong>Non-derivative Licence</strong>: As for the Open Licence but also prohibits adaptation of the material, and in the second case also restricts reuse only for non-commercial purposes.<br />";
                    text = text + "<strong>Restrictive Licence</strong>: A licence preventing reuse of material unless certain restrictive conditions are satisfied. Note licence restrictions, and contact.<br />";
                    text = text + "<strong>No Licence</strong>: All rights to reuse, communicate, publish or reproduce the material are reserved, with the exception of specific rights contained within the Copyright Act 1968 or similar laws.  Contact the copyright holder for permission to reuse this material.<br />";
                    text = text + "<strong>Other</strong>: No value or user defined custom value."
                }
                if(scope.facet.name=='administering_institution'){
                    text = "Please note that adding a Managing Institution filter to your search will restrict your search to only those grants and projects in Research Data Australia which have the managing institution recorded."
                }
                if(scope.facet.name=='funders'){
                    text = "Please note that adding a funder filter to your search will restrict your search to only those grants and projects in Research Data Australia which have the funder recorded."
                }
                if(scope.facet.name=='funding_scheme'){
                    text = "Please note that adding a funding scheme filter to your search will restrict your search to only those grants and projects in Research Data Australia which contain funding scheme information."
                }
                return text;
            }

			scope.filterExists = scope.$parent.filterExists;
			scope.isFacet = scope.$parent.isFacet;
			scope.toggleFilter = scope.$parent.toggleFilter;

			scope.advanced = function(active) {
				scope.$emit('advanced', active);
			}
			
			scope.hashChange = scope.$parent.hashChange;
		}
	}
});

app.directive('facetinfo', function($log) {
	return {
		template: '<i class="fa fa-info" tip="{{info}}" ng-if="info"></i>',
		scope: {
			infotype: '=',
			infovalue: '='
		},
		transclude: true,
		link: function(scope) {
			// $log.debug(scope.infotype, scope.infovalue);

			var values = {
				'access_rights' : {
					'open' : 'Data that is readily accessible and reusable.',
					'conditional' : 'Data that is accessible and reusable, providing certain conditions are met (e.g. free registration is required).',
					'restricted': 'Data access is limited in some way (e.g. only available to a particular group of users or at a specific physical location).',
					'other': 'no value or user defined custom value'
				},
				'license_class': {
					'open licence': 'A licence bearing broad permissions that may include a requirement to attribute the source, or share-alike (or both), requiring a derivative work to be licensed on the same or similar terms as the reused material.',
					'non-commercial licence' : 'As for the Open Licence but also restricting reuse only for non-commercial purposes.',
					'non-derivative licence' : 'As for the Open Licence but also prohibits adaptation of the material, and in the second case also restricts reuse only for non-commercial purposes.',
					'restrictive licence': 'A licence preventing reuse of material unless certain restrictive conditions are satisfied. Note licence restrictions, and contact',
					'no licence': 'All rights to reuse, communicate, publish or reproduce the material are reserved, with the exception of specific rights contained within the Copyright Act 1968 or similar laws.  Contact the copyright holder for permission to reuse this material.',
					'other': 'no value or user defined custom value'
				}
			}
			// $log.debug(values);

			if (values[scope.infotype] && values[scope.infotype][scope.infovalue]) {
				scope.info = values[scope.infotype][scope.infovalue];
			}
		}
	}
});

app.directive('resolve', function($http, $log, vocab_factory){
	return {
		template: '<ul class="listy no-bottom"><li ng-repeat="item in result"><a href="" ng-click="toggleFilter(vocab, item.notation, true)">{{item.label | toTitleCase | truncate:30}} <small><i class="fa fa-remove" tip="Remove Item"></i></small></a></li></ul>',
		scope: {
			subjects: '=subjects',
			vocab: '=',
			prefilter:'@'
		},
		transclude: true,
		link: function(scope) {
			scope.result = [];
			scope.$watch('subjects', function(newv){
				if(newv) {
					scope.result = [];
					vocab_factory.resolveSubjects(scope.vocab, scope.subjects).then(function(data){
						// $log.debug(data);
						angular.forEach(data, function(label, notation){
							scope.result.push({notation:notation,label:label});
						});
						// $log.debug(scope.result);
					});
				}
			});

			scope.toggleFilter = function(type, value, execute) {
				if(!scope.prefilter) {
					scope.$emit('toggleFilter', {type:type,value:value,execute:execute});
				} else {
					scope.$emit('togglePreFilter', {type:type,value:value,execute:execute});
				}
			}
		}
	}
});

app.directive('resolveRo', function($log, $http, record_factory) {
	return {
		template: '{{title}}',
		scope: {
			roid: '='
		},
		transclude: true,
		link: function(scope) {
			scope.title = scope.roid;
			record_factory.get_record(scope.roid).then(function(data){
				if(data.core && data.core.title) {
					scope.title = data.core.title;
				}
			});
		}
	}
});



app.directive('classicon', function($log) {
	return {
		template: '<i class="{{class}}"></i>',
		scope: {
			fclass: '='
		},
		transclude: true,
		link: function(scope, element) {
			scope.$watch('fclass', function() {
				if (scope.fclass=='collection') {
					scope.class = 'fa fa-folder-open';
				} else if(scope.fclass=='service') {
					scope.class = 'fa fa-wrench';
				} else if(scope.fclass=='party') {
					scope.class = 'fa fa-user';
				} else if(scope.fclass=='activity') {
					scope.class = 'fa fa-flask';
				}
				// scope.class += ' icon-white';
			});
			
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