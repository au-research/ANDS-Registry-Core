jQuery(document).ready(function( $ ) {
	function isotopeInit() {
        $('.masonry').each( function( index, element ) {
            var $container = $(element);
            var $items = $container.find( '.masonry-item' );
            var padding = $container.attr( 'data-padding' );
            var isFullWidth = $container.parents( '.container-fullwidth' ).length > 0;
            // On fullscreen portfolio add negative margin on left and right and add 4pixel upon that for the loss after rounding
            var containerPadding = -padding / 2;

            $container.css({
                margin: '0 ' + containerPadding + 'px'
            });
            $container.imagesLoaded().always( function( loadedContainer ) {
                setTimeout( function() {
                    var columns = 3;
                    var screenWidth = $(window).width();
                    var wideColumns = 2;
                    if( screenWidth < 768 ) {
                        columns = $container.attr( 'data-col-xs' );
                        wideColumns = 1;
                    }
                    else if( screenWidth < 992 ) {
                        columns = $container.attr( 'data-col-sm' );
                        wideColumns =  1;
                    }
                    else if( screenWidth < 1200 ) {
                        columns = $container.attr( 'data-col-md' );
                        wideColumns =  2 ;
                    }
                    else if( screenWidth > 1200 ) {
                        columns = $container.attr( 'data-col-lg' );
                        wideColumns =  2 ;
                    }

                    // calculate item width and paddings
                    var itemWidth;
                    if ( $container.hasClass( 'use-masonry' ) ) {
                        $items.each(function() {
                            // Set the masonry column width
                            itemWidth = Math.floor( $container.width() / columns );

                            var item  = $(this);
                            if( item.hasClass( 'masonry-wide' ) ) {
                                item.css( 'width', itemWidth * wideColumns );
                            }
                            else {
                                item.css( 'width', itemWidth );
                            }
                        });
                    }
                    else {
                        itemWidth = Math.floor( $container.width() / columns );
                        $items.css( 'width', itemWidth );
                    }

                    $items.find('.figure,.post-masonry-inner').css( 'padding', padding / 2 + 'px' );

                    // wait for possible flexsliders to render before rendering isotope
                    $grid = $container.isotope( {
                        itemSelector: '.masonry-item',
                        getSortData : {
                            default: function ( $elem ) {
                                return parseInt( $elem.attr( 'data-menu-order' ) );
                            },
                            title: function ( $elem ) {
                                return $elem.attr( 'data-title' );
                            },
                            date: function ( $elem ) {
                                return Date.parse( $elem.attr( 'data-date' ) );
                            },
                            comments: function( $elem ) {
                                return parseInt( $elem.attr( 'data-comments') );
                            }
                        },
                        sortBy: 'default',
                        layoutMode: $container.attr( 'data-layout' ),
                        resizable: false,
                        masonry: {
                            columnWidth: itemWidth,
                            gutter: padding
                        }
                    }, function(){
                        // refresh waypoints after layout
                        $.waypoints('refresh');
                        $container.removeClass( 'no-transition' );
                    });

                },200);
            });
        });

        $('#help_modal').on('hidden.bs.modal', function () {
            if(readCookie('help_shown') != 'true')
            {
                $('.help_button').qtip({
                    content: {
                        text: "Access help anytime"
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
            }

            createCookie("help_shown",'true',100000);
        });
    }

    // Re initialise isotope on window resize
    $(window).smartresize(function(){
        isotopeInit();
    });

    // Init the isotope
    isotopeInit();


    //styling for the about page
    // CC-2040. Remove the counter $odometer plugin because it uses waypoint
    // $('.counter').each(function() {
    //
    //     var $counter = $(this);
    //     var $odometer = $counter.find('.odometer-counter');
    //     if($odometer.length > 0 ) {
    //         var od = new Odometer({
    //             el: $odometer[0],
    //             value: $odometer.text(),
    //             format: $counter.attr('data-format')
    //         });
    //         console.log(od);
    //         $counter.waypoint(function() {
    //             window.setTimeout(function() {
    //                 $odometer.html( $counter.attr( 'data-count' ) );
    //             }, 1500);
    //         },{
    //             triggerOnce: true,
    //             offset: 'bottom-in-view'
    //         });
    //     }
    // });

    // CC-2040. just set the value instead of using $counter plugin
    $('.counter').each(function() {
       $(this).find('.odometer-counter').html($(this).attr('data-count'));
    });



// Init On scroll animations
//     function onScrollInit( items, trigger ) {
//         items.each( function() {
//             var osElement = $(this),
//                 osAnimationClass = osElement.attr('data-os-animation'),
//                 osAnimationDelay = osElement.attr('data-os-animation-delay');
//
//             osElement.css({
//                 '-webkit-animation-delay':  osAnimationDelay,
//                 '-moz-animation-delay':     osAnimationDelay,
//                 'animation-delay':          osAnimationDelay
//             });
//
//             var osTrigger = ( trigger ) ? trigger : osElement;
//
//             osTrigger.waypoint(function() {
//                 osElement.addClass('animated').addClass(osAnimationClass);
//             },{
//                 triggerOnce: true,
//                 offset: '90%'
//             });
//         });
//     }

    // CC-2040 remove onScrollInit
    // onScrollInit( $('.os-animation') );
    // onScrollInit( $( '.staff-os-animation' ), $('.staff-list-container') );
    // onScrollInit( $( '.recent-simple-os-animation' ), $('.recent-simple-os-container') );

    // CC-2040. Instead of animating the opacity onScrollInit, just display them
    window.setTimeout(function() {
        $('.os-animation, .staff-os-animation, .staff-list-container, .recent-simple-os-animation, .recent-simple-os-container')
            .css("opacity", "1");
    }, 50);

    function createCookie(name,value,days) {
        if (days) {
            var date = new Date();
            date.setTime(date.getTime()+(days*24*60*60*1000));
            var expires = "; expires="+date.toGMTString();
        }
        else var expires = "";
        document.cookie = name+"="+value+expires+"; path=/";
    }

    function readCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }

    function eraseCookie(name) {
        createCookie(name,"",-1);
    }

    //
    ///////////////////

    $(document).on('click', '.togglediv', function(e){
        e.preventDefault();
        var div = $(this).attr('data-toggle');
        console.log(div, $(div), $(div).length);
        $(div).toggle();
    }).on('click', '#show_all_anchor', function(e){
        $('#show_all_span').hide();
        $('.listItem').removeClass('hidden');
    }).on('mouseover', '*[tip]', function(event){
        // Bind the qTip within the event handler
        var my = $(this).attr('my') ? $(this).attr('my') : 'bottom center';
        var at = $(this).attr('at') ? $(this).attr('at') : 'top center';
        var delay = $(this).attr('tip-delay') ? $(this).attr('tip-delay') : 0;

        $(this).qtip({
            overwrite: false, // Make sure the tooltip won't be overridden once created
            content: $(this).attr('tip'),
            show: {
                delay: delay,
                event: event.type,
                ready: true
            },
            hide: {
                delay: 200,
                fixed: true
            },
            position: {
                my: my, // Use the corner...
                at: at,
                viewport: $(window)
            },
            style: {
                classes: 'qtip-light qtip-shadow qtip-normal qtip-bootstrap'
            }
        }, event); // Pass through our original event to qTip
    }).on('mouseover', '*[mtip]', function(event){
        $(this).qtip({
            overwrite: false, // Make sure the tooltip won't be overridden once created
            content: $(this).attr('mtip'),
            show: {
                event: event.type,
                ready: true
            },
            hide: {
                delay: 200,
                fixed: true
            },
            position: {
                target: 'mouse',
                my : 'bottom center',
                at : 'top center',
                viewport: $(window)
            },
            style: {
                classes: 'qtip-light qtip-shadow qtip-normal qtip-bootstrap'
            }
        }, event); // Pass through our original event to qTip
    }).on('mouseover', '*[xtip]', function(event){
        var cut = $(this).attr('cut') ? $(this).attr('cut') : 30;
        var content = $(this).attr('xtip');
        if (content.length > cut) {
            $(this).qtip({
                overwrite: false, // Make sure the tooltip won't be overridden once created
                content: $(this).attr('xtip'),
                show: {
                    event: event.type, // Use the same show event as the one that triggered the event handler
                    ready: true // Show the tooltip as soon as it's bound, vital so it shows up the first time you hover!
                },
                hide: {
                    delay: 200,
                    fixed: true,
                },
                position: {
                    my: 'bottom center', // Use the corner...
                    at: 'top center',
                    viewport: $(window)
                },
                style: {
                    classes: 'qtip-light qtip-shadow qtip-normal qtip-bootstrap'
                }
            }, event); // Pass through our original event to qTip
        }
    }).on('click', '.login_btn', function(event){
        event.preventDefault();
        console.log(window.location.href);
        var url = $(this).attr('href');
        var redirect = window.location.href;
        location.href = url+'?redirect='+encodeURIComponent(redirect);
    }).on('click', '.help_button, .help_link_custom, .open_rda_help_modal', function(event){

        var $loadModal = $('#help_modal');
        var $this = $(this);

        $.get(base_url + "page/help", function( data ){
            $loadModal
                .find('.modal-body')
                .html(data).end();

            var useTab = $this.data('help-tab') ? $this.data('help-tab') : 'overview';

            var urlStr = window.location.href;
            if (urlStr.indexOf('/search/#!') > 0) {
                useTab = 'search';
            } else if (urlStr.indexOf('/profile#!') > 0) {
                useTab = 'myrda';
            }

            $loadModal.find('.tab-link').removeClass('active');
            $loadModal.find('.tab-pane').removeClass('active');

            $('#'+useTab).addClass('active');
            $('#'+useTab+'_tab').addClass('active');
        });

    }).on('click', '.search_help', function(event){

        var $loadModal = $('#help_modal');
        $loadModal.find('.tab-link').removeClass('active');
        $loadModal.find('.tab-pane').removeClass('active');

        $('#search').addClass('active');
        $('#search_tab').addClass('active');

    }).on('click', '.help_link', function(event){
        var useTab = $(this).attr('id');
        var $loadModal = $('#help_modal');
        useTab = useTab.substr(0, useTab.indexOf('_link'));
        $loadModal.find('.tab-link').removeClass('active');
        $loadModal.find('.tab-pane').removeClass('active');
        $('#'+useTab).addClass('active');
        $('#'+useTab+'_tab').addClass('active');
    }).on('click', '#toggle-visualisation', function(event) {
        toggleGraphDisplay(event)
    }).on('click', '.visualisation-overlay', function(event) {
        toggleGraphDisplay(event)
    }).on('mouseover', '.visualisation-overlay', function(event){
        event.stopPropagation();
        $('#visualisation-notice').show();
    }).on('mouseout', '.visualisation-overlay', function(event){
        event.stopPropagation();
        $('#visualisation-notice').hide();
    });

    function toggleGraphDisplay(event) {
        event.stopPropagation();
        var viz = $('#graph-viz');
        var overlay = $('#visualisation-overlay');
        overlay.hide();
        if (viz.height() < 449) {
            viz.animate({height: 450}, 400, 'swing', function() {
                window.neo4jd3.zoomFit();
            });
        } else if (viz.height() > 149) {
            viz.animate({height: 150}, 400, 'swing', function() {
                window.neo4jd3.zoomFit();
            });
            overlay.show();
        }
    }
    $(document).ready(function() {
        $('.showWebsites').click(function (event) {
            event.stopPropagation();
            $('.showMoreWebsites').toggle();
            $('.morewebsites').toggle();
            $('.showLessWebsites').toggle();
        });
        $('.showPublications').click(function (event) {
            event.stopPropagation();
            $('.showMorePublications').toggle();
            $('.morepublications').toggle();
            $('.showLessPublications').toggle();
        });
    });
  // Requires jQuery!
  jQuery.ajax({
    url: "https://jira.ands.org.au/s/fa9c094ec4b2c10d80d7b8abe3ff2778-T/oboj49/712004/b631a3e63f12bafb8e515b7232486d1c/2.0.31/_/download/batch/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector-embededjs/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector-embededjs.js?locale=en-UK&collectorId=4d89e3f4",
    type: "get",
    cache: true,
    dataType: "script"
  });

  window.ATL_JQ_PAGE_PROPS =  {
    "triggerFunction": function(showCollectorDialog) {
      //Requires that jQuery is available!
      jQuery(".feedback_button, .myCustomTrigger").unbind().click(function(e) {
        e.preventDefault();
        showCollectorDialog();
      });
    }};

});;angular.module('record_components',['profile_components'])

.factory('record_factory', function($http){
	return{
        get_record: function(id) {
            var promise = $http.get(base_url+'registry_object/get/'+id+'/core').then(function(response){
                return response.data;
            });
            return promise;
        },
		stat: function(id) {
			var promise = $http.get(base_url+'registry_object/stat/'+id).then(function(response){
				return response.data;
			});
			return promise;
		},
        add_stat: function(id, type, value, url) {
            var data = {
                type:type,
                value:value, 
                url: url
            };
            var promise = $http.post(base_url+'registry_object/add_stat/'+id, {data:data}).then(function(response){
                return response.data;
            });
            return promise;
        }
	}
})

.controller('moveCtrl', function($scope, $log, $modalInstance, id, profile_factory, record_factory){
    $scope.base_url = base_url;
    $scope.id = id;

    if (angular.isArray($scope.id)) {
        $scope.records = $scope.id;
    } else {
        record_factory.get_record($scope.id).then(function(data){
            $scope.record = data;
        });
    }

    //handle empty
    $scope.empty = false;
    if (angular.isArray($scope.id) && $scope.id.length == 0) {
        $scope.empty = true;
    }

    if ($scope.id && !angular.isArray($scope.id)) {
        profile_factory.check_is_bookmarked($scope.id).then(function(data){
           if (data.status=='OK') {
              $scope.bookmarked = true;
           } else $scope.bookmarked = false;
        });
    }

    $scope.fetch = function(){
        $scope.folders = {};
        profile_factory.get_user().then(function(data){
            if(data.status=='ERROR') {
                $scope.loggedin = false;
            } else {
                $scope.loggedin = true;
                $scope.user = data;
                $scope.folders = profile_factory.get_user_folders($scope.user);
            }
        });
    }

    $scope.moveToFolder = function(folder) {
        // $log.debug(folder);
        if ($scope.record) {
            var records = [];
            records.push({
                id:$scope.record.core.id,
                slug:$scope.record.core.slug,
                group:$scope.record.core.group,
                title:$scope.record.core.title,
                type:$scope.record.core.type,
                class:$scope.record.core.class,
                folder:folder,
                saved_time:parseInt(new Date().getTime() / 1000),
                last_viewed:parseInt(new Date().getTime() / 1000)
            });
        } else if($scope.records) {
            var records = $scope.records;
            angular.forEach($scope.records, function(record){
                record.selected = false;
                record.folder = folder;
                record.last_viewed = parseInt(new Date().getTime() / 1000);
            });
        }
        // $log.debug(folder);
        if(records) {
            var action = 'modify';
            if (!$scope.bookmarked) action = 'add';
            // $log.debug(records, action);
            profile_factory.modify_user_data('saved_record', action, records).then(function(data){
                if(data.status=='OK') {
                    $scope.success_msg = 'Records successfully saved';
                    $scope.fetch();
                } else {
                    $scope.error_msg = 'An error has occured while saving '+records.length+' to folder '+folder;
                    $log.debug(data);
                }
            });
        }
    }

    $scope.unBookmark = function(id) {
        var records = [];
        records.push({id:id});
        profile_factory.modify_user_data('saved_record', 'delete', records).then(function(data){
            if(data.status=='OK') {
                $modalInstance.close();
            } else {
                // $log.debug(data);
            }
        });
    }

    $scope.inFolder = function(id, folder) {
        var ret = false;
        if($scope.user) {
            angular.forEach($scope.user.user_data.saved_record, function(rec) {
                if (rec.id==id && folder==rec.folder) {
                    ret = true;
                }
            });
        }
        return ret;
    }

    $scope.inRecordsFolder = function(records, folder) {
        var ret = true;
        angular.forEach(records, function(r){
            if(r.folder!=folder) ret = false;
        });
        return ret;
    }

    $scope.dismiss = function(){
        $modalInstance.dismiss();
    }

    $scope.getCurrentURL = function() {
        return encodeURIComponent(window.location.href);
    }

    $scope.fetch();

})


.controller('exportCtrl', function($scope, $log, $modalInstance, id, record_factory){
    $scope.id = id;

    $scope.empty = false;
    if (angular.isArray($scope.id) && $scope.id.length == 0) {
        $scope.empty = true;
    }

    if (angular.isArray($scope.id)) {
        $scope.records = $scope.id;
        // $log.debug($scope.records);
    } else {
        record_factory.get_record($scope.id).then(function(data){
            $scope.record = data;
        });
    }

    $scope.export = function(type) {

        var id = 0;
        var link = '';

        if ($scope.record) {
            id = $scope.record.id;
        } else if($scope.records) {
            var ids = [];
            angular.forEach($scope.records, function(record){
                ids.push(record.id);
            });
            id = ids.join('-');
        }

        if (type=='endnote') {
            link = base_url + "registry_object/export/endnote/"+id+'?source=portal_search';
        } else if(type=='endnote_web') {
            link = base_url + "registry_object/export/endnote_web/"+id+'?source=portal_search';
        }

        return link;

    }

    $scope.dismiss = function(){
        $modalInstance.dismiss();
    }

})

.controller('saveSearchCtrl', function($scope, $log, $modalInstance, saved_search_data, profile_factory){
    $scope.data = saved_search_data;
    $scope.base_url = base_url;
    $scope.saveSearch = function(){
        ngdata = [];
        ngdata.push($scope.data);
        profile_factory.add_user_data('saved_search', ngdata).then(function(data){
            if (data.status=='OK') {
                $scope.success_msg = 'Save Search has been successful';
            } else {
                $scope.error_msg = 'An error has occured while saving this search';
                $log.debug(data);
            }
        });
    }

    profile_factory.get_user().then(function(data){
        if(data.status=='ERROR') {
            $scope.loggedin = false;
        } else {
            $scope.loggedin = true;
            $scope.user = data;
        }
    });

    $scope.dismiss = function(){
        $modalInstance.dismiss();
    }

    $scope.getCurrentURL = function() {
        return encodeURIComponent(window.location.href);
    }
})

;;angular.module('profile_components',[])

.factory('profile_factory', function($http){
	return{
        check_is_bookmarked: function(id) {
           return $http.get(base_url+'profile/is_bookmarked/'+id).then(function(response){
               return response.data;
           });
        },
		add_user_data: function(type, data) {
			var promise = $http.post(base_url+'profile/modify_user_data/'+type+'/add', {'data':data}).then(function(response){
				return response.data;
			});
			return promise;
		},
        modify_user_data: function(type, action, data) {
            var promise = $http.post(base_url+'profile/modify_user_data/'+type+'/'+action, {'data':data}).then(function(response){
                return response.data;
            });
            return promise;
        },
		get_user: function() {
			var promise = $http.get(base_url+'profile/current_user').then(function(response){
				return response.data;
			});
			return promise;
		},
		get_specific_user: function(roleid) {
			var promise = $http.get(base_url+'profile/get_specific_user/?identifier='+roleid).then(function(response){
				return response.data;
			});
			return promise;
		},
		get_user_folders: function(user) {
			folders = {};
			folders['all'] = 0;

			if(user.user_data && user.user_data.saved_record) {
			    folders['all'] = Object.keys(user.user_data.saved_record).length;

			    angular.forEach(user.user_data.saved_record, function(record){
			        if (record.folder){
			            if(folders[record.folder] == undefined) {
			                folders[record.folder] = 1;
			            }
			            else{
			                folders[record.folder] = folders[record.folder] + 1;
			            }
			        }
			    });
			}
			return folders;
		},
		get_user_available_actions: function(){
			var actions = {
                "saved_record_group":['move', 'delete', 'export'],
                "saved_record":['open', 'move', 'delete', 'export'],
                "saved_search_group":['refresh', 'delete'],
                "saved_search":['refresh', 'delete']
            };

			return actions;
		}
	}
})

;;var app = angular.module('app', ['ngRoute', 'ngSanitize', 'portal-filters', 'ui.bootstrap', 'ui.utils', 'profile_components', 'record_components', 'queryBuilder', 'lz-string', 'angular-loading-bar', 'uiGmapgoogle-maps']);

app.config(function($interpolateProvider, $locationProvider, $logProvider){
	$interpolateProvider.startSymbol('[[');
	$interpolateProvider.endSymbol(']]');

	$locationProvider.hashPrefix('!');

	$logProvider.debugEnabled(true);
});

app.config(function (uiGmapGoogleMapApiProvider) {
  uiGmapGoogleMapApiProvider.configure({
    key: google_api_key,
    v: '3.17',
    libraries: 'weather,drawing,geometry,visualization'
  })
});angular.module('portal-filters', [])
	.filter('filter_name', function(){
		return function(text) {
			switch(text) {
				case 'q': return 'All Fields' ;break;
				case 'cq': return 'Advanced Query' ;break;
				case 'title': return 'Title' ;break;
				case 'identifier': return 'Identifier' ;break;
				case 'related_people': return 'Related People' ;break;
				case 'related_organisations': return 'Related Organisations' ;break;
				case 'description': return 'Description' ;break;
				case 'subject': return 'Subject' ;break;
				case 'access_rights': return 'Access'; break;
				case 'group': return 'Data Provider'; break;
				case 'license_class': return 'Licence'; break;
				case 'type': return 'Type'; break;
                case 'collection_type': return 'Type'; break;
				case 'subject_vocab_uri': return 'Subject Vocabulary URI'; break;
				case 'anzsrc-for': return 'Subjects ANZSRC-FOR'; break;
				case 'anzsrc-seo': return 'Subjects ANZSRC-SEO'; break;
				case 'anzsrc-for-2020': return 'Subjects ANZSRC-FOR-2020'; break;
				case 'anzsrc-seo-2020': return 'Subjects ANZSRC-SEO-2020'; break;
				case 'year_from': return 'Time Period (from)'; break;
				case 'year_to': return 'Time Period (to)'; break;
				case 'funding_scheme': return 'Funding Scheme'; break;
				case 'funding_from': return 'Funding From'; break;
				case 'funding_to': return 'Funding To'; break;
				case 'funders': return 'Funder'; break;
				case 'administering_institution': return 'Managing Institution'; break;
				case 'institution': return 'Institution'; break;
				case 'activity_status': return 'Status'; break;
				case 'researcher': return 'Researcher'; break;
				case 'related_party_one_id': return 'Related Researcher'; break;
				case 'scot': return 'Schools of Online Thesaurus'; break;
				case 'pont': return 'Powerhouse Museum Object Name Thesaurus'; break;
				case 'psychit': return 'Thesaurus of psychological index terms'; break;
				case 'anzsrc': return 'ANZSRC'; break;
				case 'apt': return 'Australian Pictorial Thesaurus'; break;
				case 'gcmd': return 'GCMD Keyword'; break;
				case 'iso639-3': return 'iso639-3 Language'; break;
				case 'lcsh': return 'LCSH'; break;
				case 'Type:software': return 'Software'; break;
				case 'keywords': return 'Keyword'; break;
				case 'refine': return 'Keyword'; break;
				case 'subject_value_resolved': return 'Subject'; break;
				case 'commencement_to': return 'Commencement To'; break;
				case 'commencement_from': return 'Commencement From'; break;
				case 'completion_to': return 'Completion To'; break;
				case 'completion_from': return 'Completion From'; break;
				case 'spatial': return 'Location'; break;
				case 'access_methods': return 'Access Method'; break;
				default: return text;
			}
		}
	})
	.filter('highlightreadable', function() {
		return function(text) {
			switch(text) {

				case 'identifier_value_search' : return 'Identifier' ; break;
				// case 'access' : return 'Access Details' ; break;
				case 'related_party_one_search' : return 'Related People' ; break;
				case 'related_party_multi_search' : return 'Related Organisations' ; break;
				case 'group_search' : return 'Data Provider' ; break;
				case 'related_activity_search' : return 'Related Project or Grant' ; break;
				case 'related_service_search' : return 'Related Tool or Service' ; break;
				case 'related_info_search' : return 'Related Resource' ; break;
				case 'subject_value_resolved_search' : return 'Subject' ; break;
				case 'description_value' : return 'Description' ; break;
				case 'date_to' : return 'Dates' ; break;
				case 'date_from' : return 'Coverage' ; break;
				case 'citation_info_search' : return 'Citation ' ; break;
				default : return text;
			}
		}
	})
	.filter('socialreadable', function(){
		return function(text) {
			switch(text) {
				case 'AUTHENTICATION_SOCIAL_FACEBOOK' : return 'Facebook'; break;
				case 'AUTHENTICATION_SOCIAL_TWITTER' : return 'Twitter'; break;
				case 'AUTHENTICATION_SOCIAL_GOOGLE' : return 'Google'; break;
				case 'AUTHENTICATION_SOCIAL_LINKEDIN' : return 'LinkedIn'; break;
			}
		}
	})
	.filter('filter_value', function($sce){
		return function(text) {
			if (angular.isArray(text)) {
				var html = '<ul>';
				angular.forEach(text, function(content) {
					html+='<li>'+content+'</li>';
				});
				html+='</ul>';
				return $sce.trustAsHtml(html);
			} else {
				return $sce.trustAsHtml(text);
			}
		}
	})
	.filter('toTitleCase', function($log){
		return function(str){
			return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
		}
	})
	.filter('formatFacet', function () {
		return function (str) {
			if(typeof(str) == "string") {
                str = str.toUpperCase();
				switch (str) {
					case 'OGC:WMTS':
						return 'OGC Web Map Tile Service';
						break;
					case 'OGC:WFS':
						return 'OGC Web Feature Service';
						break;
					case 'OGC:WMS':
						return 'OGC Web Map Service';
						break;
					case 'OGC:WCS':
						return 'OGC Web Coverage Service';
						break;
					case 'OGC:WPS':
						return 'OGC Web Processing Service';
						break;
					case 'LANDINGPAGE':
						return 'Landing Page';
						break;
					case 'DIRECTDOWNLOAD':
						return 'Direct Download';
						break;
					case 'GEOSERVER':
						return 'GeoServer';
						break;
					case 'THREDDS':
						return 'THREDDS';
						break;
					case 'THREDDS:WCS':
						return 'THREDDS Web Coverage Service';
						break;
					case 'THREDDS:WMS':
						return 'THREDDS Web Map Service';
						break;
					case 'THREDDS:OPENDAP':
						return 'THREDDS OPeNDAP';
						break;
					case 'ESRI:ARCGIS:GPSERVER':
						return 'ArcGIS GPS Server';
						break;
					case 'ESRI:ARCGIS:IMAGESERVER':
						return 'ArcGIS Image Server';
						break;
					case 'ESRI:ARCGIS:MAPSERVER':
						return 'ArcGIS Map Server';
						break;
					case 'CONTACTCUSTODIAN':
						return 'Contact Custodian';
						break;
					case '-TYPE:SOFTWARE':
						return 'Data';
						break;
					case 'TYPE:SOFTWARE':
						return 'Software';
						break;
					default:
						return str.replace(/\w\S*/g, function (txt) {
							return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
						});
						break;
				}
			}else{ return " ";}
		}
	})
	.filter('getLabelFor', function($log){
		return function(value, filter) {
			var ret = '';
			angular.forEach(filter, function(f){
				if(f.value==value) {
					ret = f.label;
				}
			});
			return ret;
		}
	})
	.filter('truncate', function () {
		return function (text, length, end) {
			if(text){
				if (isNaN(length))
					length = 10;
				if (end === undefined)
					end = "...";
				if (text.length <= length || text.length - end.length <= length) {
					return text;
				}
				else {
					return String(text).substring(0, length-end.length) + end;
				}
			}
		};
	})
	.filter('text', ['$sce', function($sce){
		return function(text){
			var tmp = document.createElement("DIV");
			tmp.innerHTML = text;
			return tmp.textContent || tmp.innerText || "";
			// var decoded = $('<div/>').html(text).text();
			// return decoded;
		}
	}])
	.filter('extractSentence', function($log){
		return function(text) {
            var sentences = text.split(/(.*?(?:\.|\?|!))(?: |$)/g);
			if (sentences) {
                if(sentences.length>0){
                        for(i=0;i<sentences.length;i++){
                            if(sentences[i].indexOf("&lt;b&gt")>-1) {

                                var first = sentences[i].trim().charAt(0);
                                var last = sentences[i].trim().slice(-1);
                                if (first === first.toLowerCase() && first !== first.toUpperCase())
                                {
                                    // first character is a lowercase letter
                                    sentences[i] =  "..."+sentences[i];
                                }
                                if(last!=".")
                                {
                                    sentences[i] = sentences[i]+"...";
                                }

                                return sentences[i];}

                    }

                }
                var first = sentences[0].trim().charAt(0);
                var last = sentences[0].trim().slice(-1);
                if (first === first.toLowerCase() && first !== first.toUpperCase())
                {
                    // first character is a lowercase letter
                    sentences[0] =  "..."+sentences[0];
                }
                if(last!=".")
                {
                    sentences[0] = sentences[0]+"...";
                }
                return sentences[0]
			} else {
                var first = text.trim().charAt(0);
                var last = text.trim().slice(-1);
                if (first === first.toLowerCase() && first !== first.toUpperCase())
                {
                    // first character is a lowercase letter
                    text =  "..."+text;
                }
                if(last!=".")
                {
                    text = text+"...";
                }
				return text;
			}
		}
	})
	.filter('trustAsHtml', ['$sce', function($sce){
		return function(text){
			var decoded = $('<div/>').html(text).text();
			return $sce.trustAsHtml(decoded);
		}
	}])
	.filter("timeago", function () {
	    //time: the time
	    //local: compared to what time? default: now
	    //raw: wheter you want in a format of "5 minutes ago", or "5 minutes"
	    return function (time, local, raw) {
	        if (!time) return "never";

	        if (!local) {
	            (local = Date.now())
	        }


	        if (angular.isDate(time)) {
	            time = time.getTime();
	        } else if (typeof time === "string") {
	        	var s = time;
				var bits = s.split(/\D/);
				var date = new Date(bits[0], --bits[1], bits[2], bits[3], bits[4]);
				time = date.getTime();
	        }


	        if (angular.isDate(local)) {
	            local = local.getTime();
	        }else if (typeof local === "string") {
	            local = new Date(local).getTime();
	        }

	        // console.log(local, time);

	        if (typeof time !== 'number' || typeof local !== 'number' || isNaN(time) || isNaN(local)) {
	            return;
	        }

	        var
	            offset = Math.abs((local - time) / 1000),
	            span = [],
	            MINUTE = 60,
	            HOUR = 3600,
	            DAY = 86400,
	            WEEK = 604800,
	            MONTH = 2629744,
	            YEAR = 31556926,
	            DECADE = 315569260;


	        if (offset <= MINUTE)              span = [ '', raw ? 'now' : parseInt(offset) + ' seconds' ];
	        else if (offset < (MINUTE * 60))   span = [ Math.round(Math.abs(offset / MINUTE)), 'min' ];
	        else if (offset < (HOUR * 24))     span = [ Math.round(Math.abs(offset / HOUR)), 'hr' ];
	        else if (offset < (DAY * 7))       span = [ Math.round(Math.abs(offset / DAY)), 'day' ];
	        else if (offset < (WEEK * 52))     span = [ Math.round(Math.abs(offset / WEEK)), 'week' ];
	        else if (offset < (YEAR * 10))     span = [ Math.round(Math.abs(offset / YEAR)), 'year' ];
	        else if (offset < (DECADE * 100))  span = [ Math.round(Math.abs(offset / DECADE)), 'decade' ];
	        else if (isNaN(offset))			   span = [''];
	        else                               span = [ '', 'a long time' ];

	        span[1] += (span[0] === 0 || span[0] > 1) ? 's' : '';
	        span = span.join(' ');

	        if (raw === true) {
	            return span;
	        }
	        return (time <= local && !isNaN(time)) ? span + ' ago' : 'in ' + span;
	    }
	})
	.filter('orderObjectBy', function($log) {
	  return function(items, field, reverse) {
	    var filtered = [];
	    angular.forEach(items, function(item) {
	      filtered.push(item);
	    });
	    filtered.sort(function (a, b) {
	    	var asort = (typeof(a[field])=='string' ? a[field].toLowerCase() : a[field]);
	    	var bsort = (typeof(b[field])=='string' ? b[field].toLowerCase() : b[field]);
	    	return (asort > bsort ? 1 : -1);
	    });
	    if(reverse) filtered.reverse();
	    return filtered;
	  };

}).filter('sortObjectBy', function($log) {
    return function(items, field, reverse) {
        var sortArray = ['open','conditional','restricted','open licence','non-commercial licence','non-derivative licence','restrictive licence','no licence','other','unknown']
        var filtered = [];
        sortArray.forEach(function(element){
            angular.forEach(items, function(item) {
                if(item.name==element){
                filtered.push(item);
                }
            });
        });
        return filtered;
    };

})

;

;;app.controller('QueryBuilderCtrl', function ($scope, $log, LZString ) {

    var data = '{"group":{"root": true, "operator":"AND","rules":[{"group":{"operator":"AND","rules":[{"condition":":","field":"_text_","data":"","$$hashKey":"064"},{"condition":":","field":"_text_","data":""}]},"$$hashKey":"05Z"}]}}';

    function htmlEntities(str) {
        return String(str).replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function computed(group) {
        if (!group || group.rules.length == 0) return "";

        for (var str = "", i = 0; i < group.rules.length; i++) {
            if(group.rules[i].data!='' && group.rules[i]!==undefined){
                i > 0 && (str += " " + group.operator + " ");
                if(group.rules[i].group) {
                    str += computed(group.rules[i].group)
                } else {
                    if(group.rules[i].condition=='-') {
                        if(group.rules[i].data.indexOf(' ') > -1) {
                            str += '-' + group.rules[i].field + ':' + '('+group.rules[i].data+')';
                        } else {
                            str += '-' + group.rules[i].field + ':' + group.rules[i].data;
                        }
                    }else {
                        if(group.rules[i].data.indexOf(' ')> -1) {
                            str += group.rules[i].field + "" + htmlEntities(group.rules[i].condition) + "" + '('+group.rules[i].data+')';
                        } else {
                            str += group.rules[i].field + "" + htmlEntities(group.rules[i].condition) + "" + group.rules[i].data;
                        }
                    }
                }
            }
        }

        return str + "";
    }

    $scope.json = null;

    $scope.filter = JSON.parse(data);

    $scope.$on('query', function(e, data){
        $scope.filter = $scope.parse(data);
    });

    $scope.$on('cq', function(e, cqdata){
        if (cqdata) {
            $scope.filter = JSON.parse(LZString.decompressFromEncodedURIComponent(cqdata));
        } else {
            $scope.filter = JSON.parse(data);
        }
    });

    $scope.$on('clearSearch', function(e){
        $scope.filter = JSON.parse(data);
    });

    $scope.parse = function(data){
        if (data.query.indexOf('(')==0) {
            data.query = data.query.substr(1);
            data.query = data.query.substr(0, data.query.length-1);
        }
        var ndata = {};
        ndata.group = {'operator': 'AND', 'rules':[]};
        return ndata;
    }

    $scope.convertType = function(type) {
        switch(type) {
            case 'q': return '_text_';break;
        }
        return type;
    }

    $scope.$watch('filter', function (newValue) {
        $scope.json = JSON.stringify(newValue, null, 0);
        $scope.output = computed(newValue.group);
        // $log.debug($scope.json, $scope.output);
        if ($scope.output!='()' && $scope.output!="" && $scope.output!='(())'){
            $scope.$emit('changePreFilter', {type:'cq', value:LZString.compressToEncodedURIComponent($scope.json),execute:false});
            $scope.$emit('changePreQuery', $scope.output);
        } else {
            $scope.$emit('changePreQuery', '');
        }
    }, true);

});

var queryBuilder = angular.module('queryBuilder', []);
queryBuilder.directive('queryBuilder', ['$compile', function ($compile, $log, search_factory) {
    return {
        restrict: 'E',
        scope: {
            group: '='
        },
        templateUrl: base_url+'assets/registry_object/templates/querybuilder.html',
        compile: function (element, attrs) {
            var content, directive;
            content = element.contents().remove();
            return function (scope, element, attrs) {
                scope.operators = [
                    { name: 'AND' },
                    { name: 'OR' }
                ];

                scope.fields = [
                    { name: '_text_', display: 'All Fields'},
                    { name: 'title_search', display: 'Title'},
                    { name: 'identifier_value_search', display: 'Identifier'},
                    { name: 'related_party_one_search', display: 'Related People'},
                    { name: 'related_party_multi_search', display: 'Related Organisation'},
                    { name: 'description_value', display: 'Description'}
                ]

                scope.conditions = [
                    { name: ':', display:'Contains' },
                    { name: '-', display:'Excludes'}
                ];

                scope.addCondition = function () {
                    scope.group.rules.push({
                        condition: ':',
                        field: '_text_',
                        data: ''
                    });
                };

                scope.removeCondition = function (index) {
                    scope.group.rules.splice(index, 1);
                };

                scope.addGroup = function () {
                    scope.group.rules.push({
                        group: {
                            operator: 'AND',
                            rules: [
                                {condition:":", field:'_text_', data:''}
                            ]
                        }
                    });
                };

                scope.removeGroup = function () {
                    "group" in scope.$parent && scope.$parent.group.rules.splice(scope.$parent.$index, 1);
                };

                directive || (directive = $compile(content));

                element.append(directive(scope, function ($compile) {
                    return $compile;
                }));
            }
        }
    }
}]);
queryBuilder.filter('getDisplayFor', function($log){
    return function(value, filter) {
        var ret = '';
        angular.forEach(filter, function(f){
            if(f.name==value) {
                ret = f.display;
            }
        });
        return ret;
    }
});;app.directive('facetSearch', function($http, $log){
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
                    text = "<strong>Open</strong>: Data is publicly accessible online.<br />"
                    text = text + "<strong>Conditional</strong>: Data is publicly accessible subject to certain conditions. For example:";
                    text = text + "<ul><li>a fee applies</li><li>the data is only accessible at a specific physical location</li></ul>";
                    text = text + "<strong>Restricted</strong>: Data access is limited.  For example.<br />";
                    text = text + "<ul><li>following an embargo period;</li><li>to a particular group of users;</li><li>where formal permission is granted.</li></ul>";
                    text = text + "<strong>Other</strong>: No value or user defined custom value.";

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
	};
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
					'open' : 'Data is publicly accessible online.',
					'conditional' : 'Data is publicly accessible subject to certain conditions. For example: <br/>- a fee applies<br/>- the data is only accessible at a specific physical location.',
					'restricted': 'Data access is limited. For example: <br/>- following an embargo period<br/>- to a particular group of users<br/>- where formal permission is granted.',
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
						angular.forEach(data, function(label, notation){
							scope.result.push({notation:notation,label:label});
						});
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
		template: '<span tip="{{title}}">{{title}}</span>',
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

					// key
					if (google_api_key) {
            src +="&key="+google_api_key
					}

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
});;app.factory('vocab_factory', function($http, $log){
	return {
		tree : {},
		subjects: {},
		get: function (term, filters, vocab) {
			var url = '';
			if (term) {
				url = '?uri='+term;
			}
			return $http.post(base_url+'registry_object/vocab/'+vocab+'/'+url, {'filters':filters}).then(function(response){
				return response.data;
			});
		},
		isSelected: function(item, filters) {
			if (filters['subject_vocab_uri']) {
				if(decodeURIComponent(filters['subject_vocab_uri'])==item.uri) {
					return true;
				} else if(angular.isArray(filters['subject_vocab_uri'])) {
					angular.forEach(filters['subject_vocab_uri'], function(content, index) {
						if(content==item.uri) {
							return true;
						}
					});
				}
			} else if(filters['subject']){
				var found = false;
				angular.forEach(this.subjects[filters['subject']], function(uri){
					if(uri==item.uri && !found) {
						found = true;
					}
				});
				return found;
			} else if(filters['anzsrc-for']){
				var found = false;
				if(filters['anzsrc-for']==item.notation){
					found = true;
				} else if (angular.isArray(filters['anzsrc-for'])) {
					angular.forEach(filters['anzsrc-for'], function(code){
						if((code==item.notation || item.notation.indexOf(code) == 0) && !found) {
							found =  true;
						}
					});
				} else if(item.notation.indexOf(filters['anzsrc-for']) == 0) {
					found = true;
				}
				return found;
			} else if(filters['anzsrc-for-2020']){
				var found = false;
				if(filters['anzsrc-for-2020']==item.notation){
					found = true;
				} else if (angular.isArray(filters['anzsrc-for-2020'])) {
					angular.forEach(filters['anzsrc-for-2020'], function(code){
						if((code==item.notation || item.notation.indexOf(code) == 0) && !found) {
							found =  true;
						}
					});
				} else if(item.notation.indexOf(filters['anzsrc-for-2020']) == 0) {
					found = true;
				}
				return found;
			} else if(filters['anzsrc-seo']) {
				var found = false;
				if(filters['anzsrc-seo']==item.notation){
					found = true;
				} else if (angular.isArray(filters['anzsrc-seo'])) {
					angular.forEach(filters['anzsrc-seo'], function(code){
						if((code==item.notation || item.notation.indexOf(code) == 0) && !found) {
							found =  true;
						}
					});
				} else if(item.notation.indexOf(filters['anzsrc-seo']) == 0) {
					found = true;
				}
				return found;
			} else if(filters['anzsrc-seo-2020']) {
				var found = false;
				if(filters['anzsrc-seo-2020']==item.notation){
					found = true;
				} else if (angular.isArray(filters['anzsrc-seo-2020'])) {
					angular.forEach(filters['anzsrc-seo-2020'], function(code){
						if((code==item.notation || item.notation.indexOf(code) == 0) && !found) {
							found =  true;
						}
					});
				} else if(item.notation.indexOf(filters['anzsrc-seo-2020']) == 0) {
					found = true;
				}
				return found;
			} else {
				return false;
			}
		},
		getSubjects: function(){
			return $http.get(base_url+'registry_object/getSubjects').then(function(response){
				return response.data;
			});
		},
		resolveSubjects: function(vocab, subjects){
			return $http.post(base_url+'registry_object/resolveSubjects/'+vocab, {data:subjects}).then(function(response){
				return response.data;
			});
		}
	};
});;(function () {
    'use strict';
    angular
        .module('app')
        .controller('searchCtrl', searchController);

    function searchController($scope, $log, $modal, search_factory, vocab_factory, uiGmapGoogleMapApi) {

        $scope.sf = search_factory;
        $scope.base_url = base_url;

        $scope.isArray = angular.isArray;

        //setting default search class
        if ($('#ro_id').length) {
            var search_class = $('#ro_class').val();
            search_factory.update_class(search_class);
        }

        //is the advanced search box open
        $scope.advancedSearchOpen = false;

        $scope.class_choices = $scope.sf.class_choices;

        $scope.vocab = 'anzsrc-for';
        $scope.vocab_choices = $scope.sf.vocab_choices;

        $scope.$watch(function(){
            return location.hash;
        },function(){
            var hash = location.hash ? location.href.split("#")[1] : '';
            var refer_q = $('#refer_q');
            hash = refer_q.length ? refer_q.val() : hash;

            $scope.filters = search_factory.ingest(hash);
            angular.copy($scope.filters, $scope.prefilters);
            $scope.sync();

            if($scope.filters.cq) {
                $scope.$broadcast('cq', $scope.filters.cq);
            }

            if ($scope.onBrowsePage() || $scope.onSearchPage()) {
                $scope.search();
            }
        });

        $scope.$on('toggleFilter', function(e, data){
            $scope.toggleFilter(data.type, data.value, data.execute);
        });

        $scope.$on('togglePreFilter', function(e, data){
            $scope.togglePreFilter(data.type, data.value, data.execute);
        });

        $scope.$on('advanced', function(e, data){
            $scope.advanced(data);
        });

        $scope.$on('changeFilter', function(e, data){
            $scope.changeFilter(data.type, data.value, data.execute);
        });

        $scope.$on('changePreFilter', function(e, data){
            $scope.prefilters[data.type] = data.value;
        });

        $scope.$on('changeQuery', function(e, data){
            $scope.query = data;
            $scope.filters['q'] = data;
            search_factory.update('query', data);
            search_factory.update('filters', $scope.filters);
        });

        $scope.$on('changePreQuery', function(e, data){
            $scope.prefilters['q'] = data;
        });

        $scope.$watch('query', function(newv,oldv){
            if(newv!=oldv) {
                if ($scope.search_type=='q') {
                    $scope.filters['q'] = newv;
                }
                else if($scope.search_type) {
                    $scope.filters[$scope.search_type] = newv;
                }
            }
        });

        $scope.setSearchType = function(value) {
            $scope.search_type = value;
        };

        $scope.$watch('search_type', function(newv,oldv){
            if (newv) {
                delete $scope.filters['q'];
                delete $scope.filters[oldv];
                $scope.filters[newv] = $scope.query;
            }
        });

        $scope.getLabelFor = function(filter, value) {
            if ($scope[filter]) {
                angular.forEach($scope[filter], function(f) {
                    if (f.value==value) {
                        return f.label;
                    }
                });
            }
        };

        $scope.hasFilter = function(){
            var has_filter = false;
            angular.forEach($scope.filters, function(val, index){
                if(index!='class' && index!='rows' && index!='sort') {
                    if(val!='') {
                        has_filter = true;
                    }
                }
            });
            if ($scope.query!='') has_filter = true;

            return has_filter;
        };

        $scope.filterExists = function(filter) {
            var ret = false;
            if ($scope.filters[filter]) {
                ret = true;
            }
            return ret;
        };

        $scope.clearSearch = function(){
            $scope.query = '';
            search_factory.reset();
            $scope.$broadcast('clearSearch');
            $scope.sync();
            $scope.hashChange();
            $('input[name=q]').focus();
        };

        $scope.isLoading = function(){
            return !!(location.href.indexOf('search') > -1 && $scope.loading);
        };

        $scope.newSearch = function(query) {
            if(query!='' && query!=undefined) {
                $scope.query = query;
                $scope.filters['sort'] = 'score desc';
            }
            $scope.filters['p'] = 1;
            $scope.hashChange();
        };

        //change to search page
        $scope.switchToSearch = function(){
            search_factory.update('filters', $scope.filters);
            var hash = search_factory.filters_to_hash(search_factory.filters);
            location.href = base_url+'search/#' + '!/' + hash;
        };

        $scope.filters_to_hash = function() {
            return search_factory.filters_to_hash(search_factory.filters);
        };

        $scope.hashChange = function(){
            // $log.debug('query', $scope.query, search_factory.query);
            // $scope.filters.q = $scope.query;
            if ($scope.search_type=='q') {
                $scope.filters.q = $scope.query;
            } else {
                $scope.filters[$scope.search_type] = $scope.query;
            }
            search_factory.update('filters', $scope.filters);
            // $log.debug(search_factory.filters, search_factory.filters_to_hash(search_factory.filters));
            var hash = search_factory.filters_to_hash(search_factory.filters);
            // $log.debug('changing hash to ', hash);
            // return false;

            //only change the hash at search page, other page will navigate to the search page
            if ($scope.onSearchPage()) {
                location.hash = '!/'+hash;
                $(window).scrollTop(0);
            } else if ($scope.onBrowsePage()) {
                location.hash = '!/'+hash;
            } else {
                location.href = base_url+'search/#' + '!/' + hash;
                $(window).scrollTop(0);
            }
        };

        $scope.onSearchPage = function() {
            var ret = false;
            if (location.href.indexOf(base_url+'search')==0) {
                ret = true;
            }
            return ret;
        };

        $scope.onBrowsePage = function() {
            var ret = false;
            if (location.href.indexOf(base_url+'subjects')==0) {
                ret = true;
            }
            return ret;
        };

        $scope.search = function(){
            $scope.loading = true;

            if (typeof urchin_id !== 'undefined' && typeof ga !== 'undefined' && urchin_id!='' && $scope.filters['q'] && $scope.filters['q']!='' && $scope.filters['q']!==undefined) {
                ga('send', 'pageview', '/search_results.php?q='+$scope.filters['q']);
            }

            if ($scope.onBrowsePage() || $scope.onSearchPage()) {
                search_factory.search($scope.filters).then(function(data){
                    $scope.loading = false;
                    $scope.fuzzy = data.fuzzy_result;
                    search_factory.update('result', data);
                    search_factory.update('facets', search_factory.construct_facets(data));
                    if ($scope.onSearchPage()) {
                        $scope.sync();
                    } else if($scope.onBrowsePage()) {
                        $scope.syncSubjectBrowse();
                    }
                    $scope.$broadcast('search_complete');
                    $scope.populateCenters($scope.result.response.docs);

                    //clear advanced flag if on
                    delete $scope.filters['advanced'];
                });
            } else {
                $scope.loading = false;
            }
        };

        $scope.addKeyWord = function(extra_keywords) {
            $scope.toggleFilter('refine', extra_keywords, true);
            $scope.extra_keywords = '';
        };



        $scope.syncSubjectBrowse = function(){
            $scope.filters = search_factory.filters;

            $scope.query = search_factory.query;
            $scope.search_type = search_factory.search_type;

            // $scope.$broadcast('query', {query:$scope.query, search_type:$scope.search_type});

            $scope.result = search_factory.result;
            $scope.facets = search_factory.facets;
            $scope.pp = search_factory.pp;
            $scope.sort = search_factory.sort;

            //construct the pagination
            if ($scope.result) {
                // $log.debug($scope.result);
                $scope.page = {
                    cur: ($scope.filters['p'] ? parseInt($scope.filters['p']) : 1),
                    rows: ($scope.filters['rows'] ? parseInt($scope.filters['rows']) : 15),
                    range: 3,
                    pages: []
                };
                $scope.page.end = Math.ceil($scope.result.response.numFound / $scope.page.rows);
                for (var x = ($scope.page.cur - $scope.page.range); x < (($scope.page.cur + $scope.page.range)+1);x++ ) {
                    if (x > 0 && x <= $scope.page.end) {
                        $scope.page.pages.push(x);
                    }
                }
            }

            // $log.debug('sync result', $scope.result);
        };


        $scope.sync = function(){
            $scope.filters = search_factory.filters;

            $scope.query = search_factory.query;
            $scope.search_type = search_factory.search_type;

            // $scope.$broadcast('query', {query:$scope.query, search_type:$scope.search_type});

            $scope.result = search_factory.result;
            $scope.facets = search_factory.facets;
            $scope.pp = search_factory.pp;
            $scope.sort = search_factory.sort;
            $scope.advanced_fields = search_factory.advanced_fields;

            if($scope.filters['class']=='activity') {
                $scope.advanced_fields = search_factory.advanced_fields_activity;
                $scope.sort = search_factory.activity_sort;
            }

            if($scope.filters['class']=='activity') {
                $scope.advanced_fields = search_factory.advanced_fields_activity;
            } else if($scope.filters['class']=='collection') {
                $scope.advanced_fields = search_factory.advanced_fields;
            } else if($scope.filters['class']=='party') {
                $scope.advanced_fields = search_factory.advanced_fields_party;
            } else if($scope.filters['class']=='service') {
                $scope.advanced_fields = search_factory.advanced_fields_service;
            }

            //construct the pagination
            if ($scope.result) {
                // $log.debug($scope.result);
                $scope.page = {
                    cur: ($scope.filters['p'] ? parseInt($scope.filters['p']) : 1),
                    rows: ($scope.filters['rows'] ? parseInt($scope.filters['rows']) : 15),
                    range: 3,
                    pages: []
                };
                $scope.page.end = Math.ceil($scope.result.response.numFound / $scope.page.rows);
                for (var x = ($scope.page.cur - $scope.page.range); x < (($scope.page.cur + $scope.page.range)+1);x++ ) {
                    if (x > 0 && x <= $scope.page.end) {
                        $scope.page.pages.push(x);
                    }
                }

                // $scope.temp
                $scope.temporal_range = search_factory.temporal_range($scope.result);
                $scope.earliest_year = $scope.temporal_range[0];
                $scope.latest_year = $scope.temporal_range[$scope.temporal_range.length - 1];
            }

            //duplicate record matching
            if ($scope.result) {
                var matchingdoc = [];
                angular.forEach($scope.result.response.docs, function(doc){
                    if (doc.matching_identifier_count) {
                        matchingdoc.push(doc);
                    }
                });
                angular.forEach(matchingdoc, function(doc) {
                    if(!doc.hide) {
                        search_factory.get_matching_records(doc.id).then(function(data){
                            var matches = data.data[0].identifiermatch;
                            if (doc && !doc.hide) {
                                doc.identifiermatch = matches;
                                angular.forEach(matches, function (match) {
                                    $scope.hidedoc(match.registry_object_id);
                                });
                            }
                        });
                    }
                });
            }

            $scope.hidedoc = function(id) {
                if ($scope.result) {
                    angular.forEach($scope.result.response.docs, function(doc){
                        if (doc.id==id && !doc.hide) {
                            doc.hide = true;
                        }
                    });
                }
            };

            //init vocabulary

            if ($scope.onBrowsePage() || $scope.onSearchPage()) {
                $scope.vocabInit();
            }

            //$log.debug('sync result', $scope.result);
        };

        /**
         * Getting the highlighting for a result
         * @param  id [result ID for matching]
         * @return bool    [false if there's no highlight, highlight object if there's any]
         */
        $scope.getHighlight = function(id){
            if ($scope.result.highlighting && !$.isEmptyObject($scope.result.highlighting[id])) {
                return $scope.result.highlighting[id];
            } else return false;
        };

        $scope.showFilter = function(filter_name, mode){
            if (!mode || mode=='undefined') mode = 'normal';
            var show = true;
            if (filter_name=='cq' || filter_name=='rows' || filter_name=='sort' || filter_name=='p' || filter_name=='class' || filter_name == 'advanced') {
                show = false;
            }
            if ($scope.filters[filter_name]=="" && mode == 'normal')  show = false;
            if ($scope.prefilters[filter_name]=="" && mode == 'advanced')  show = false;
            return show;
        };


        /**
         * Filter manipulation
         */
        $scope.toggleFilter = function(type, value, execute) {

            $scope.filters['p'] = 1;
            if($scope.filters[type]) {
                if($scope.filters[type]==value) {
                    $scope.clearFilter(type,value);
                } else {
                    if($scope.filters[type].indexOf(value)==-1) {
                        $scope.addFilter(type, value, false);
                    } else {
                        $scope.clearFilter(type,value, false);
                    }
                }
            } else {
                $scope.addFilter(type, value);
            }

            //hashChange event only on search page,
            //on browse page, no page refresh
            if ($scope.onBrowsePage()) {
                $scope.search();
            } else if (execute) {
                $scope.hashChange();
            }
        };

        //special function for only 1 subject at 1 time
        $scope.toggleSubject = function(item) {

            //close all tree that doesn't need to be open
            angular.forEach($scope.vocab_tree, function(i){
                if (item.notation.indexOf(i.notation) == -1) {
                    i.showsubtree = false;
                }
            });

            if (!item.subtree) {
                $scope.getSubTree(item);
                item.showsubtree = true;
            } else {
                item.showsubtree = !item.showsubtree;
            }

            if ($scope.filters['anzsrc-for'] != item.notation) {
                delete ($scope.filters['anzsrc-for']);
                $scope.filters['anzsrc-for'] = item.notation;
                $scope.filters['p'] = 1; //reset the pagination
                $scope.search();
            }
        };

        $scope.clearSubjectFilter = function(type, value){
            if(typeof $scope.filters[type]=='object') {
                var index = $scope.filters[type].indexOf(value);
                $scope.filters[type].splice(index, 1);
            }
        };

        $scope.addSubjectFilter = function (type, value){
            if($scope.filters[type]){
                if(typeof $scope.filters[type]=='string') {
                    var old = $scope.filters[type];
                    $scope.filters[type] = [];
                    $scope.filters[type].push(old);
                    $scope.filters[type].push(value);
                } else if(typeof $scope.filters[type]=='object') {
                    $scope.filters[type].push(value);
                }
            } else $scope.filters[type] = value;
        };

        $scope.isSubjectSelected = function(notation) {
            var found = false;
            if($scope.filters['anzsrc-for']) {
                if (angular.isArray($scope.filters['anzsrc-for'])) {
                    angular.forEach($scope.filters['anzsrc-for'], function(code){
                        if(!found && code == notation) {
                            found = true;
                        }
                    });
                }
            }
            return found;
        };

        $scope.isSubjectParentSelected = function(notation) {
            var found = false;
            if($scope.filters['anzsrc-for']) {
                if (angular.isArray($scope.filters['anzsrc-for'])) {
                    angular.forEach($scope.filters['anzsrc-for'], function(code){
                        if(indexOf(code ,notation) == 0) {
                            found = true;
                        }
                    });
                }
            }
            return found;
        };


        $scope.toggleAccessRights = function() {
            if ($scope.filters['access_rights']) {
                delete $scope.filters['access_rights'];
            } else {
                $scope.filters['access_rights'] = 'open';
            }
            if ( $scope.onSearchPage() ) {
                $scope.hashChange();
            }
        };

        $scope.addFilter = function(type, value) {
            if($scope.filters[type]){
                if(typeof $scope.filters[type]=='string') {
                    var old = $scope.filters[type];
                    $scope.filters[type] = [];
                    $scope.filters[type].push(old);
                    $scope.filters[type].push(value);
                } else if(typeof $scope.filters[type]=='object') {
                    $scope.filters[type].push(value);
                }
            } else $scope.filters[type] = value;
        };

        $scope.clearFilter = function(type, value, execute) {
            if(typeof $scope.filters[type]!='object') {
                if(type=='q') {
                    $scope.query = '';
                    search_factory.update('query', '');
                    $scope.filters['q'] = '';
                    delete $scope.filters['cq'];
                    $scope.$broadcast('cq');
                } else if(type=='description' || type=='title' || type=='identifier' || type == 'related_people' || type == 'related_organisations' || type == 'institution' || type == 'researcher') {
                    $scope.query = '';
                    search_factory.update('query', '');
                    delete $scope.filters[type];
                    delete $scope.filters['q'];
                }
                delete $scope.filters[type];
            } else if(typeof $scope.filters[type]=='object') {
                var index = $scope.filters[type].indexOf(value);
                $scope.filters[type].splice(index, 1);
            }
            if(execute) $scope.hashChange();
        };

        $scope.isFacet = function(type, value) {
            if($scope.filters[type]) {
                if(typeof $scope.filters[type]=='string' && $scope.filters[type]==value) {
                    return true;
                } else if(typeof $scope.filters[type]=='object') {
                    return $scope.filters[type].indexOf(value) != -1;
                }
                return false;
            }
            return false;
        };

        $scope.showFacet = function(facet) {
            var allowed = [];
            if ($scope.filters['class']=='collection') {
                allowed = ['subjects', 'group', 'access_rights', 'license_class', 'temporal', 'spatial', 'access_methods','collection_type'];
            } else if($scope.filters['class']=='activity') {
                allowed = ['type', 'activity_status', 'subjects', 'administering_institution', 'funders', 'funding_scheme', 'commencement_to', 'commencement_from', 'completion_to', 'completion_from', 'funding_amount'];
            } else if ($scope.filters['class'] == 'service') {
                allowed = ['type' ,'subjects', 'group', 'spatial'];
            } else {
                allowed = ['type' ,'subjects', 'group'];
            }
            return allowed.indexOf(facet) > -1;
        };

        $scope.isPrefilterFacet = function(type, value) {
            if($scope.prefilters[type]) {
                if(typeof $scope.prefilters[type]=='string' && $scope.prefilters[type]==value) {
                    return true;
                } else if(typeof $scope.prefilters[type]=='object') {
                    return $scope.prefilters[type].indexOf(value) != -1;
                }
                return false;
            }
            return false;
        };

        $scope.changeFilter = function(type, value, execute) {
            $scope.filters[type] = value;
            if (execute===true) {
                $scope.hashChange();
            }
        };

        $scope.goto = function(x) {
            $scope.filters['p'] = ''+x;
            $scope.hashChange();
            $scope.selected = [];
            $scope.selectState = 'selectAll';
            $("html, body").animate({ scrollTop: 0 }, 500);
        };


        /**
         * Record Selection Section
         */
        $scope.selected = [];
        $scope.selectState = 'selectAll';
        $scope.toggleResult = function(ro) {
            var exist = false;
            $.each($scope.selected, function(i,k){
                if(k && ro.id == k.id) {
                    $scope.selected.splice(i, 1);
                    exist = true;
                }
            });
            if(!exist) $scope.selected.push(ro);
            if($scope.selected.length != $scope.result.response.docs.length) {
                $scope.selectState = 'deselectSelected';
            }
            if($scope.selected.length == 0) {
                $scope.selectState = 'selectAll';
            }
        };

        $scope.isSelected = function(ro) {
            var ret = false;
            angular.forEach($scope.selected, function(x){
                ret = (ro.id == x.id ) ? true : ret;
            });
            return ret;
        };

        $scope.toggleResults = function() {
            if ($scope.selectState == 'selectAll') {
                $.each($scope.result.response.docs, function(){
                    this.select = true;
                    $scope.selected.push(this);
                });
                $scope.selectState = 'deselectAll';
            } else if ($scope.selectState=='deselectAll' || $scope.selectState=='deselectSelected') {
                $scope.selected = [];
                $.each($scope.result.response.docs, function(){
                    this.select = false;
                });
                $scope.selectState = 'selectAll';
            }
        };

        $scope.add_user_data = function(type) {
            var modalInstance = null;
            if (type=='saved_record') {
                modalInstance = $modal.open({
                    templateUrl: base_url+'assets/registry_object/templates/moveModal.html',
                    controller: 'moveCtrl',
                    windowClass: 'modal-center',
                    resolve: {
                        id: function () {
                            var selected = [];
                            angular.forEach($scope.selected, function(i) {
                                selected.push({
                                    id: i.id,
                                    title: i.title,
                                    slug: i.slug,
                                    group: i.group,
                                    class: $scope.filters.class,
                                    type: i.type,
                                    saved_time: parseInt(new Date().getTime() / 1000)
                                });
                            });
                            return selected;
                        }
                    }
                });
            } else if(type=='saved_search') {
                modalInstance = $modal.open({
                    templateUrl: base_url+'assets/registry_object/templates/saveSearchModal.html',
                    controller: 'saveSearchCtrl',
                    windowClass: 'modal-center',
                    resolve: {
                        saved_search_data: function () {
                            return {
                                id: Math.random().toString(36).substring(7),
                                query_title: '',
                                query_string: $scope.sf.filters_to_hash($scope.filters),
                                num_found: $scope.result.response.numFound,
                                num_found_since_last_check: 0,
                                num_found_since_saved: 0,
                                saved_time: parseInt(new Date().getTime() / 1000),
                                refresh_time: parseInt(new Date().getTime() / 1000),
                                last_ran: parseInt(new Date().getTime() / 1000)
                            };
                        }
                    }
                });
            } else if(type=='export') {
                modalInstance = $modal.open({
                    templateUrl: base_url+'assets/registry_object/templates/exportModal.html',
                    controller: 'exportCtrl',
                    windowClass: 'modal-center',
                    resolve: {
                        id: function () {
                            return $scope.selected;
                        }
                    }
                });
            }
            modalInstance.result.then(function(){
                //close
            }, function(){
                //dismiss
            });
        };

        /**
         * Advanced Search Section
         */
        $scope.prefilters = {};
        $scope.advanced = function(active){
            // $scope.prefilters = {};
            // $scope.preresult = {};
            angular.copy($scope.filters, $scope.prefilters);

            if (active && active!='close') {
                $scope.selectAdvancedField(active);
                $('#advanced_search').modal('show');
                $scope.advancedSearchOpen = true;
            } else if(active=='close'){
                $('#advanced_search').modal('hide');
                $scope.advancedSearchOpen = false;
            }else {
                $scope.selectAdvancedField('terms');
                $('#advanced_search').modal('show');
                $scope.advancedSearchOpen = true;
            }

            $scope.presearch();
        };

        $scope.presearch = function(){
            search_factory.search_no_record($scope.prefilters).then(function(data){
                $scope.preresult = data;
                $scope.prefacets = search_factory.construct_facets($scope.preresult, $scope.prefilters['class']);
                $scope.temporal_range = search_factory.temporal_range(data);
                $scope.populateCenters($scope.preresult.response.docs);
                vocab_factory.get(false, $scope.prefilters, $scope.vocab).then(function(data){
                    $scope.vocab_tree_tmp = data;
                });
            });
        };

        $scope.selectAdvancedField = function(name) {
            // $log.debug('selecting', name);
            angular.forEach($scope.advanced_fields, function(f){
                f.active = f.name == name;
            });

            $scope.prefilters2 = {};
            angular.copy($scope.prefilters, $scope.prefilters2);
            delete $scope.prefilters2[name];
            search_factory.search_no_record($scope.prefilters2).then(function(data){
                $scope.prefacets2 = search_factory.construct_facets(data, $scope.prefilters['class']);
            });

            $scope.presearch();
        };

        $scope.$watch('prefilters.class', function(newv){
            var tmp_filter = {};
            tmp_filter['class'] = newv;
            if(newv=='activity') {
                $scope.advanced_fields = search_factory.advanced_fields_activity;
                if ($scope.advancedSearchOpen) {
                    search_factory.search_no_record($scope.prefilters).then(function (data) {
                        $scope.temporal_range = search_factory.temporal_range(data);
                    });
                }
            } else if(newv=='collection') {
                $scope.advanced_fields = search_factory.advanced_fields;
                if ($scope.advancedSearchOpen) {
                    search_factory.search_no_record($scope.prefilters).then(function (data) {
                        $scope.temporal_range = search_factory.temporal_range(data);
                    });
                }
            } else if(newv=='party') {
                $scope.advanced_fields = search_factory.advanced_fields_party;
            } else if(newv=='service') {
                $scope.advanced_fields = search_factory.advanced_fields_service;
            }
            if ($scope.advancedSearchOpen) {
                $scope.presearch();
                $scope.cleanPrefilters();
            }
        });

        $scope.cleanPrefilters = function() {
            var cleanOut = ['year_from', 'year_to', 'group', 'subject', 'access_rights', 'license_class', 'temporal', 'spatial', 'type', 'group', 'activity_status', 'administering_institution', 'date_range', 'funders', 'funding_scheme', 'funding_amount', 'collection_type'];
            angular.forEach(cleanOut, function(f) {
                delete $scope.prefilters[f];
            });
        };

        $scope.cleanfiltersForSubjectBrowse = function() {
            $scope.prefilters = {};
            angular.forEach($scope.filters, function(f) {
                if(f != 'anzsrc-for'){
                    delete $scope.filters[f];
                }
            });
        };

        $scope.advancedSearch = function(){
            $scope.filters = {};
            angular.copy($scope.prefilters, $scope.filters);
            if($scope.prefilters['q']) {
                $scope.query = $scope.prefilters.q;
                $scope.filters['sort'] = 'score desc';
            } else {
                $scope.query = '';
                $scope.filters['q'] = '';
            }
          //  $log.debug($scope.filters);
            $scope.filters['p'] = 1;
            $scope.filters['advanced'] = true;
            $scope.hashChange();
            $('#advanced_search').modal('hide');
        };

        $scope.togglePreFilter = function(type, value, execute) {
            if($scope.prefilters[type]) {
                if($scope.prefilters[type]===value) {
                    $scope.clearPreFilter(type,value);
                } else {
                    if($scope.prefilters[type].indexOf(value)==-1 || ($scope.prefilters[type].indexOf(value)==1 && $scope.prefilters[type].indexOf("-")==0)) {
                        $scope.addPreFilter(type, value);
                    } else {
                        $scope.clearPreFilter(type,value);
                    }
                }
            } else {
                $scope.addPreFilter(type, value);
            }
            if(execute) $scope.presearch();
        };

        $scope.addPreFilter = function(type, value) {
            if($scope.prefilters[type]){
                if(typeof $scope.prefilters[type]=='string') {
                    var old = $scope.prefilters[type];
                    $scope.prefilters[type] = [];
                    $scope.prefilters[type].push(old);
                    $scope.prefilters[type].push(value);
                } else if(typeof $scope.prefilters[type]=='object') {
                    $scope.prefilters[type].push(value);
                }
            } else $scope.prefilters[type] = value;
        };

        $scope.clearPreFilter = function(type, value, execute) {
         //   $log.debug('clearing', type,value);
            if(typeof $scope.prefilters[type]!='object') {
                if(type=='q') $scope.q = '';
                delete $scope.prefilters[type];
                $scope.prefilters['cq'] = '';
                $scope.$broadcast('clearSearch');
            } else if(typeof $scope.prefilters[type]=='object') {
                var index = $scope.prefilters[type].indexOf(value);
                $scope.prefilters[type].splice(index, 1);
            }
            if(execute) $scope.presearch();
        };


        $scope.isAdvancedSearchActive = function(type) {
            var result = false;
            if($scope.advanced_fields.length){
                for (var i=0;i<$scope.advanced_fields.length;i++){
                    if($scope.advanced_fields[i].name==type && $scope.advanced_fields[i].active) {
                        result = true;
                        return true;
                    }
                }
            }
            return result;
        };

        $scope.clearSubject = function() {
            var fields_array = ['anzsrc-for', 'anzsrc-for-2020', 'anzsrc-seo', 'anzsrc-seo-2020', 'anzsrc', 'keywords', 'scot', 'pont', 'psychit', 'apt', 'gcmd', 'lcsh','iso639-3'];
            angular.forEach(fields_array, function(ss){
                delete $scope.prefilters[ss];
            });
            $scope.presearch();
        };

        $scope.sizeofField = function(type) {

            var ret = 0;
            var fields_array = [];
            if(type=='subject') {
                fields_array = ['anzsrc-for', 'anzsrc-for-2020', 'anzsrc-seo','anzsrc-seo-2020', 'anzsrc', 'keywords', 'scot', 'pont', 'psychit', 'apt', 'gcmd', 'lcsh','iso639-3'];
                angular.forEach(fields_array, function(ss){
                    ret = $scope.prefilters[ss] ? 1 : ret;
                });
            }

            if(type=='temporal') {
                fields_array = ['year_from', 'year_to'];
                angular.forEach(fields_array, function(ss){
                    ret = $scope.prefilters[ss] ? 1 : ret;
                });
            }

            if(type=='date_range') {
                fields_array = ['commence_from', 'commence_to', 'completed_from', 'completed_to'];
                angular.forEach(fields_array, function(ss){
                    ret = $scope.prefilters[ss] ? 1 : ret;
                });
            }

            if (type=='funding_amount') {
                fields_array = ['funding_from', 'funding_to'];
                angular.forEach(fields_array, function(ss){
                    ret = $scope.prefilters[ss] ? 1 : ret;
                });
            }

            if (type=='terms') {
                if ($scope.prefilters['q'] && $scope.prefilters['q']!='' ) {
                    ret = 1;
                }
            }


            if($scope.prefilters[type]) {
                if(typeof $scope.prefilters[type]!='object') {
                    ret = 1
                } else if(typeof $scope.prefilters[type]=='object') {
                    return $scope.prefilters[type].length;
                }
            } else if(type=='review'){
                if($scope.preresult && $scope.preresult.response) {
                    return $scope.preresult.response.numFound;
                } else {
                    ret = 0;
                }
            }

            return ret;
        };

        //VOCAB TREE
        $scope.$watch('vocab', function(newv, oldv){
            if (newv!=oldv && $scope.isAdvancedSearchActive('subject')) {
                $scope.loading_subjects = true;
                vocab_factory.get(false, $scope.filters, $scope.vocab).then(function(data){
                    $scope.vocab_tree_tmp = data;
                    $scope.loading_subjects = false;
                });
            }
        });

        $scope.setVocab = function(v) {
            $scope.vocab = v;
        };

        $scope.vocabInit = function() {
            $scope.vocab = 'anzsrc-for';

            //only loads in search page, other page don't have subject facet (yet)
            if ( $scope.onSearchPage() ) {
                vocab_factory.get(false, $scope.filters, $scope.vocab).then(function(data){
                    $scope.vocab_tree = data;
                    $scope.vocab_tree_tmp = $scope.vocab_tree;
                    $scope.openBranches();
                });
            }

            //only loads in browse page, other page don't have subject facet (yet)
            if ($scope.onBrowsePage()) {
                vocab_factory.get(false, $scope.filters, $scope.vocab).then(function(data){
                    $scope.vocab_tree = data;
                    $scope.openBranches();
                });
            }
        };

        $scope.openBranches = function() {
            angular.forEach($scope.vocab_tree, function(item){
                if ($scope.isVocabSelected(item) || $scope.isVocabParentSelected(item)) {
                    $scope.getSubTree(item);
                    item.showsubtree = true;
                }
            });
        };

        $scope.getSubTree = function(item) {
            item['showsubtree'] = !item['showsubtree'];
            if(!item['subtree'] && ($scope.vocab=='anzsrc-for' || $scope.vocab=='anzsrc-seo' || $scope.vocab=='anzsrc-for-2020' || $scope.vocab=='anzsrc-seo-2020')) {
                vocab_factory.get(item.uri, $scope.filters, $scope.vocab).then(function(data){
                    item['subtree'] = data;
                });
            }
        };

        $scope.isVocabSelected = function(item, filters) {
            if(!filters) filters = $scope.filters;
            var found = vocab_factory.isSelected(item, filters);
            if (found) {
                item.pos = 1;
            }
            return found;
        };

        $scope.isVocabParentSelected = function(item) {
            var found = false;
            //console.log(item);
            if($scope.filters['subject']){
                var subjects = vocab_factory.subjects;
                angular.forEach(subjects[$scope.filters['subject']], function(uri){
                    if(uri.indexOf(item.uri) != -1 && !found && uri!=item.uri) {
                        found = true;
                    }
                });
            } else if($scope.filters['anzsrc-for']) {
                if (angular.isArray($scope.filters['anzsrc-for'])) {
                    angular.forEach($scope.filters['anzsrc-for'], function(code){
                        if(code.indexOf(item.notation) == 0 && !found && code!=item.notation) {
                            found =  true;
                        }
                    });
                } else if ($scope.filters['anzsrc-for'].indexOf(item.notation) ==0 && !found && $scope.filters['anzsrc-for']!=item.notation){
                    found = true;
                }
            }else if($scope.filters['anzsrc-for-2020']) {
                if (angular.isArray($scope.filters['anzsrc-for-2020'])) {
                    angular.forEach($scope.filters['anzsrc-for-2020'], function(code){
                        if(code.indexOf(item.notation) == 0 && !found && code!=item.notation) {
                            found =  true;
                        }
                    });
                } else if ($scope.filters['anzsrc-for-2020'].indexOf(item.notation) ==0 && !found && $scope.filters['anzsrc-for-2020']!=item.notation){
                    found = true;
                }
            } else if($scope.filters['anzsrc-seo']) {
                if (angular.isArray($scope.filters['anzsrc-seo'])) {
                    angular.forEach($scope.filters['anzsrc-seo'], function(code){
                        if(code.indexOf(item.notation) == 0 && !found && code!=item.notation) {
                            found =  true;
                        }
                    });
                } else if ($scope.filters['anzsrc-seo'].indexOf(item.notation) ==0 && !found && $scope.filters['anzsrc-seo']!=item.notation){
                    found = true;
                }
            } else if($scope.filters['anzsrc-seo-2020']) {
                if (angular.isArray($scope.filters['anzsrc-seo-2020'])) {
                    angular.forEach($scope.filters['anzsrc-seo-2020'], function(code){
                        if(code.indexOf(item.notation) == 0 && !found && code!=item.notation) {
                            found =  true;
                        }
                    });
                } else if ($scope.filters['anzsrc-seo-2020'].indexOf(item.notation) ==0 && !found && $scope.filters['anzsrc-seo-2020']!=item.notation){
                    found = true;
                }
            }
            if(found) {
                item.pos = 1;
            }
            return found;
        };

        //MAP
        $scope.clearMap = function() {
            $scope.searchBox.setMap(null);
            $scope.searchBox = null;
            delete $scope.filters['spatial'];
            $scope.centres = [];
        };

        uiGmapGoogleMapApi.then(function() {
            $scope.map = {
                center:{
                    latitude:-25.397, longitude:133.644
                },
                zoom:4,
                bounds:{},
                options: {
                    disableDefaultUI: false,
                    panControl: true,
                    navigationControl: false,
                    scrollwheel: true,
                    scaleControl: true
                },
                events: {
                    tilesloaded: function(map){
                        $scope.$apply(function () {
                            $scope.mapInstance = map;
                        });
                    },
                    bounds_changed: function (map) {
                        $scope.$apply(function () {
                            $scope.mapInstance = map;
                        });
                    },
                    click: function(map) {
                        $scope.$apply(function () {
                            $scope.mapInstance = map;
                        });
                    }
                }
            };

            $scope.$watch('mapInstance', function(newv, oldv){
                if(newv && !angular.equals(newv,oldv)){
                    bindDrawingManager(newv);

                    //Draw the searchbox
                    if($scope.filters['spatial']) {
                        var wsenArray = $scope.filters['spatial'].split(' ');
                        var sw = new google.maps.LatLng(wsenArray[1],wsenArray[0]);
                        var ne = new google.maps.LatLng(wsenArray[3],wsenArray[2]);
                        //148.359375 -32.546813 152.578125 -28.998532
                        //LatLngBounds(sw?:LatLng, ne?:LatLng)
                        var rBounds = new google.maps.LatLngBounds(sw,ne);

                        if($scope.searchBox) {
                            $scope.searchBox.setMap(null);
                            $scope.searchBox = null;
                        }

                        $scope.searchBox = new google.maps.Rectangle({
                            fillColor:'#ffff00',
                            fillOpacity: 0.4,
                            strokeWeight: 1,
                            clickable: false,
                            editable: false,
                            zIndex: 1,
                            bounds:rBounds
                        });
                        // $log.debug($scope.geoCodeRectangle);
                        $scope.searchBox.setMap($scope.mapInstance);
                    }
                }

                if (newv) {
                    google.maps.event.trigger($scope.mapInstance, 'resize');
                }
            });

            function bindDrawingManager(map) {
                var polyOption = {
                    fillColor: '#ffff00',
                    fillOpacity: 0.4,
                    strokeWeight: 1,
                    clickable: false,
                    editable: false,
                    zIndex: 1
                };
                $scope.drawingManager = new google.maps.drawing.DrawingManager({
                    drawingControl: true,
                    drawingControlOptions: {
                        position: google.maps.ControlPosition.TOP_CENTER,
                        drawingModes: [
                            google.maps.drawing.OverlayType.RECTANGLE
                        ]
                    },
                    circleOptions: polyOption,
                    rectangleOptions: polyOption,
                    polygonOptions: polyOption,
                    polylineOptions: polyOption
                });
                $scope.drawingManager.setMap(map);

                google.maps.event.addListener($scope.drawingManager, 'overlaycomplete', function(e) {
                    if(e.type == google.maps.drawing.OverlayType.RECTANGLE) {

                        $scope.drawingManager.setDrawingMode(null);

                        if($scope.searchBox){
                            $scope.searchBox.setMap(null);
                            $scope.searchBox = null;
                        }

                        $scope.searchBox = e.overlay;
                        var bnds = $scope.searchBox.getBounds();
                        var north = bnds.getNorthEast().lat().toFixed(6);
                        var east = bnds.getNorthEast().lng().toFixed(6);
                        var south = bnds.getSouthWest().lat().toFixed(6);
                        var west = bnds.getSouthWest().lng().toFixed(6);

                        // drawing.setMap(null);

                        $scope.prefilters['spatial'] = west + ' ' + south + ' ' + east + ' ' + north;
                        $scope.centres = [];
                        $scope.presearch();
                    }
                });
            }

        });

        $scope.centres = [];
        $scope.populateCenters = function(results){
            angular.forEach(results, function(doc){
                if(doc.spatial_coverage_centres){
                    var pair = doc.spatial_coverage_centres[0];
                    if (pair) {
                        var split = pair.split(' ');
                        if (split.length == 1) {
                            split = pair.split(',');
                        }

                        if(split.length > 1 && split[0]!=0 && split[1]!=0){

                            var lon = split[0];
                            var lat = split[1];
                            // console.log(doc.spatial_coverage_centres,pair,split,lon,lat)
                            if(lon && lat){
                                $scope.centres.push({
                                    id: doc.id,
                                    title: doc.title,
                                    longitude: lon,
                                    latitude: lat,
                                    showw:true,
                                    onClick: function() {
                                        doc.showw=!doc.showw;
                                    }
                                });
                            }
                        }
                    }

                }
            });
            if ($scope.mapInstance) {
                google.maps.event.trigger($scope.mapInstance, 'resize');
            }
        }
    }

})();;(function () {
    'use strict';

    angular
        .module('app')
        .factory('search_factory', searchFactory);

    function searchFactory($http, $log) {
        return {
            status: 'idle',
            filters: [],
            query: '',
            search_type: 'q',
            result: null,
            facets: null,

            pp: [
                {value: 15, label: 'Show 15'},
                {value: 30, label: 'Show 30'},
                {value: 60, label: 'Show 60'},
                {value: 100, label: 'Show 100'}
            ],

            search_types: [
                {value: 'q', label: 'All Fields'},
                {value: 'title', label: 'Title'},
                {value: 'description', label: 'Description'},
                {value: 'identifier', label: 'Identifier'},
                {value: 'related_people', label: 'Related People'},
                {value: 'related_organisations', label: 'Related Organisations'}
            ],

            vocab_choices: [
                {value: 'anzsrc-for', label: 'ANZSRC FOR'},
                {value: 'anzsrc-for-2020', label: 'ANZSRC FOR 2020'},
                {value: 'anzsrc-seo', label: 'ANZSRC SEO'},
                {value: 'anzsrc-seo-2020', label: 'ANZSRC SEO 2020'},
                {value: 'anzsrc', label: 'ANZSRC'},
                {value: 'keywords', label: 'Keywords'},
                {value: 'scot', label: 'School of Online Thesaurus'},
                {value: 'pont', label: 'Powerhouse Museum Object Name Thesaurus'},
                {value: 'psychit', label: 'Thesaurus of Psychological Index Terms'},
                {value: 'apt', label: 'Australian Pictorial Thesaurus'},
                {value: 'lcsh', label: 'LCSH'},
                {value: 'gcmd', label: 'Global Change Master Directory Keywords'},
                {value: 'iso639-3', label: 'iso639-3 Language'}
            ],

            search_types_activities: [
                {value: 'q', label: 'All Fields'},
                {value: 'title', label: 'Title'},
                {value: 'description', label: 'Description'},
                {value: 'identifier', label: 'Identifier'},
                {value: 'institution', label: 'Institution'},
                {value: 'researcher', label: 'Researcher'}
            ],

            available_search_type: [
                'q', 'title', 'identifier', 'related_people', 'related_organisations', 'description'
            ],

            class_choices: [
                {value: 'collection', label: 'Data'},
                {value: 'party', label: 'People and Organisation'},
                {value: 'service', label: 'Services and Tools'},
                {value: 'activity', label: 'Grants and Projects'}
            ],

            default_filters: {
                'rows': 15,
                'sort': 'list_title asc',
                'class': 'collection'
            },

            sort: [
                {value: 'score desc', label: 'Relevance'},
                {value: 'list_title asc', label: 'Title A-Z'},
                {value: 'list_title desc', label: 'Title Z-A'},
                {value: 'record_created_timestamp desc', label: 'Date Added  <i class="fa fa-sort-amount-desc"></i>'}
            ],

            activity_sort: [
                {value: 'score desc', label: 'Relevance'},
                {value: 'list_title asc', label: 'Title A-Z'},
                {value: 'list_title desc', label: 'Title Z-A'},
                {value: 'earliest_year asc', label: 'Commencement <i class="fa fa-sort-amount-asc"></i>'},
                {value: 'earliest_year desc', label: 'Commencement <i class="fa fa-sort-amount-desc"></i>'},
                {value: 'latest_year asc', label: 'Completion <i class="fa fa-sort-amount-asc"></i>'},
                {value: 'latest_year desc', label: 'Completion <i class="fa fa-sort-amount-desc"></i>'},
                {value: 'funding_amount asc', label: 'Funding Amount <i class="fa fa-sort-amount-asc"></i>'},
                {value: 'funding_amount desc', label: 'Funding Amount <i class="fa fa-sort-amount-desc"></i>'}
            ],

            advanced_fields: [
                {'name': 'terms', 'display': 'Search Terms', 'active': true},
                {'name': 'collection_type', 'display': 'Type'},
                {'name': 'subject', 'display': 'Subject'},
                {'name': 'group', 'display': 'Data Provider'},
                {'name': 'access_rights', 'display': 'Access'},
                {'name': 'access_methods', 'display': 'Access Method'},
                {'name': 'license_class', 'display': 'Licence'},
                {'name': 'temporal', 'display': 'Time Period'},
                {'name': 'spatial', 'display': 'Location'},
                {'name': 'review', 'display': 'Review'},
                {'name': 'help', 'display': '<i class="fa fa-question-circle"></i> Help'}
            ],

            advanced_fields_party: [
                {'name': 'terms', 'display': 'Search Terms', 'active': true},
                {'name': 'type', 'display': 'Type'},
                {'name': 'subject', 'display': 'Subject'},
                {'name': 'group', 'display': 'Data Provider'},
                {'name': 'review', 'display': 'Review'},
                {'name': 'help', 'display': '<i class="fa fa-question-circle"></i> Help'}
            ],

            advanced_fields_service: [
                {'name': 'terms', 'display': 'Search Terms', 'active': true},
                {'name': 'type', 'display': 'Type'},
                {'name': 'subject', 'display': 'Subject'},
                {'name': 'group', 'display': 'Data Provider'},
                {'name': 'spatial', 'display': 'Location'},
                {'name': 'review', 'display': 'Review'},
                {'name': 'help', 'display': '<i class="fa fa-question-circle"></i> Help'}
            ],

            advanced_fields_activity: [
                {'name': 'terms', 'display': 'Search Terms', 'active': true},
                {'name': 'type', 'display': 'Type'},
                {'name': 'activity_status', 'display': 'Status'},
                {'name': 'subject', 'display': 'Subject'},
                {'name': 'administering_institution', 'display': 'Managing Institution'},
                {'name': 'date_range', 'display': 'Date Range'},
                {'name': 'funders', 'display': 'Funder'},
                {'name': 'funding_scheme', 'display': 'Funding Scheme'},
                {'name': 'funding_amount', 'display': 'Funding Amount'},
                {'name': 'review', 'display': 'Review'},
                {'name': 'help', 'display': '<i class="fa fa-question-circle"></i> Help'}
            ],

            collection_facet_order: ['collection_type','group', 'access_rights', 'access_methods','license_class','type'],
            activity_facet_order: ['type', 'activity_status', 'funding_scheme', 'administering_institution', 'funders'],

            ingest: function (hash) {
                this.filters = this.filters_from_hash(hash);
                if (this.filters.q) this.query = this.filters.q;
                var that = this;

                if (that.filters['class'] != 'activity') {
                    angular.forEach(this.search_types, function (x) {
                        var term = x.value;
                        if (that.filters.hasOwnProperty(term)) {
                            that.query = that.filters[term];
                            that.search_type = term;
                        }
                    });
                } else {
                    angular.forEach(this.search_types_activities, function (x) {
                        var term = x.value;
                        if (that.filters.hasOwnProperty(term)) {
                            that.query = that.filters[term];
                            that.search_type = term;
                        }
                    });
                }

                return this.filters;
            },

            reset: function () {
                var prev_class = this.filters['class'];
                this.filters = {q: '', 'class': prev_class};
                this.search_type = 'q';
                this.query = '';
            },

            update: function (which, what) {
                this[which] = what;
            },

            update_class: function (what) {
                this.default_filters['class'] = what;
            },

            search: function (filters) {
                filters = this.cleanFilters(filters);
                // $log.debug('search filters', filters);
                return $http.post(base_url + 'registry_object/filter', {'filters': filters}).then(function (response) {
                    if (response.data.response && response.data.responseHeader.status == 0) {
                        return response.data;
                    } else {
                        $log.debug(response);
                        return false;
                    }
                });
            },

            cleanFilters: function (filters) {
                angular.forEach(filters, function (value, index) {
                    if (value == '') delete filters[index];
                });
                return filters;
            },

            search_no_record: function (filters) {
                return $http.post(base_url + 'registry_object/filter/true', {'filters': filters}).then(function (response) {
                    return response.data;
                });
            },

            construct_facets: function (result, sclass) {
                var facets = [];

                //other facet fields
                if (result.error)  console.log(result);
                angular.forEach(result.facet_counts.facet_fields, function (item, index) {
                    facets[index] = [];
                    for (var i = 0; i < result.facet_counts.facet_fields[index].length; i += 2) {
                        var fa = {
                            name: result.facet_counts.facet_fields[index][i],
                            value: result.facet_counts.facet_fields[index][i + 1]
                        };
                        facets[index].push(fa);
                    }
                });

                facets['collection_type'] =[];
                angular.forEach(result.facet_counts.facet_queries, function (item, index) {
                    if(item > 0) {
                        var fa = {
                            name: index,
                            value: item
                        };
                        facets['collection_type'].push(fa);
                    }
                });

                var order = this.collection_facet_order;

                if (this.filters['class'] == 'activity') {
                    order = this.activity_facet_order;
                }

                if (sclass == 'collection') {
                    order = this.collection_facet_order;
                } else if (sclass == 'activity') {
                    order = this.activity_facet_order;
                }

                var orderedfacets = [];
                angular.forEach(order, function (item) {
                    // orderedfacets[item] = facets[item]
                    orderedfacets.push({
                        name: item,
                        value: facets[item]
                    });
                });
                return orderedfacets;
            },

            temporal_range: function (result) {
                var range = [];
                var earliest_year = false;
                var latest_year = false;

                // $log.debug(result.facet_counts.facet_fields.earliest_year);

                var earliest_array = result.facet_counts.facet_fields.earliest_year;
                var latest_array = result.facet_counts.facet_fields.latest_year;

                var i;
                for (i = 0; i < earliest_array.length - 1; i += 2) {
                    if (earliest_year && parseInt(earliest_array[i]) < earliest_year) {
                        earliest_year = parseInt(earliest_array[i]);
                    } else if (!earliest_year || earliest_year == '') {
                        earliest_year = parseInt(earliest_array[i]);
                    }
                }

                for (i = 0; i < latest_array.length - 1; i += 2) {
                    if (latest_year && parseInt(latest_array[i]) > latest_year) {
                        latest_year = parseInt(latest_array[i]);
                    } else if (!latest_year) {
                        latest_year = parseInt(latest_array[i]);
                    }
                }

                if (earliest_year && latest_year) {
                    // $log.debug(earliest_year, latest_year);
                    for (i = parseInt(earliest_year); i < parseInt(latest_year) + 1; i++) {
                        range.push(i);
                    }
                }

                return range;
            },

            filters_from_hash: function (hash) {

                var xp = hash.split('/');
                var filters = {};
                $.each(xp, function () {
                    var t = this.split('=');
                    var term = t[0];
                    var value = t[1];
                    if (term == 'rows' || term == 'year_from' || term == 'year_to' && value.trim() != '') value = parseInt(value);
                    if (term == 'funding_from' || term == 'funding_to') {
                        value = decodeURIComponent(value);
                        value = Number(value.replace(/[^0-9\.-]+/g, ""));
                    }

                    if (term == 'subject_value_resolved') {
                        value = decodeURIComponent(value);
                    }

                    if (term && value && term != '' && value != '') {

                        if (filters[term]) {
                            if (typeof filters[term] == 'string') {
                                var old = filters[term];
                                filters[term] = [];
                                filters[term].push(old);
                                filters[term].push(decodeURIComponent(value));
                            } else if (typeof filters[term] == 'object') {
                                filters[term].push(decodeURIComponent(value));
                            }
                        } else {
                            filters[term] = decodeURIComponent(value);
                        }
                    }
                });

                angular.forEach(this.default_filters, function (content, type) {
                    if (!filters[type]) filters[type] = content;
                });

                //auto switch to activity search in grants
                if (location.href.indexOf('grants') > -1) {
                    filters['class'] = 'activity';

                }

                if (filters['class'] == 'activity' && location.href.indexOf('search') > -1) {
                    $('#banner-image').css('background-image', "url('" + base_url + "assets/core/images/activity_banner.jpg')");
                } else if (location.href.indexOf('search') > -1) {
                    $('#banner-image').css('background-image', "url('" + base_url + "assets/core/images/collection_banner.jpg')");
                }

                return filters;
            },

            filters_to_hash: function (filters) {
                var hash = '';
                $.each(filters, function (i, k) {
                    if (typeof k != 'object') {
                        hash += i + '=' + encodeURIComponent(k) + '/';
                    } else if (typeof k == 'object') {
                        $.each(k, function () {
                            hash += i + '=' + encodeURIComponent(this) + '/';
                        });
                    }
                });
                return hash;
            },

            get_matching_records: function (id) {
                return $http
                    .get(api_url + '/registry/object/' + id + '/identifiermatch')
                    .then(function (response) {
                        return response.data;
                    });
            }
        }
    }
})();