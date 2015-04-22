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
                    $container.isotope( {
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

        if(readCookie('help_shown') != 'true')
        {
            $('.help_button').click();
        }

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
    //
    //


    $('.counter').each(function() {

        var $counter = $(this);
        var $odometer = $counter.find('.odometer-counter');
        if($odometer.length > 0 ) {
            var od = new Odometer({
                el: $odometer[0],
                value: $odometer.text(),
                format: $counter.attr('data-format')
            });
            console.log(od);
            $counter.waypoint(function() {
                window.setTimeout(function() {
                    $odometer.html( $counter.attr( 'data-count' ) );
                }, 1500);
            },{
                triggerOnce: true,
                offset: 'bottom-in-view'
            });
        }
    });




// Init On scroll animations
    function onScrollInit( items, trigger ) {
        items.each( function() {
            var osElement = $(this),
                osAnimationClass = osElement.attr('data-os-animation'),
                osAnimationDelay = osElement.attr('data-os-animation-delay');

            osElement.css({
                '-webkit-animation-delay':  osAnimationDelay,
                '-moz-animation-delay':     osAnimationDelay,
                'animation-delay':          osAnimationDelay
            });

            var osTrigger = ( trigger ) ? trigger : osElement;

            osTrigger.waypoint(function() {
                osElement.addClass('animated').addClass(osAnimationClass);
            },{
                triggerOnce: true,
                offset: '90%'
            });
        });
    }

    onScrollInit( $('.os-animation') );
    onScrollInit( $( '.staff-os-animation' ), $('.staff-list-container') );
    onScrollInit( $( '.recent-simple-os-animation' ), $('.recent-simple-os-container') );


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
        var my = $(this).attr('my');
        var at = $(this).attr('at');
        if(!my){
            my = 'bottom center';
        }
        if(!at){
            at = 'top center';
        }
        $(this).qtip({
            overwrite: false, // Make sure the tooltip won't be overridden once created
            content: $(this).attr('tip'),
            show: {
                event: event.type, // Use the same show event as the one that triggered the event handler
                ready: true // Show the tooltip as soon as it's bound, vital so it shows up the first time you hover!
            },
            hide: {
                delay: 200,
                fixed: true,
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
        };
    }).on('click', '.login_btn', function(event){
        event.preventDefault();
        console.log(window.location.href);
        var url = $(this).attr('href');
        var redirect = window.location.href;
        location.href = url+'?redirect='+encodeURIComponent(redirect);
    }).on('click', '.help_button', function(event){
        var urlStr = window.location.href;
        var useTab = 'overview';
        if(urlStr.indexOf('/search/#!') > 0)
        {
            useTab = 'search';
        }
        else if(urlStr.indexOf('/profile#!') > 0)
        {
            useTab = 'myrda';
        }
        $('#overview_tab').removeClass('active');
        $('#search_tab').removeClass('active');
        $('#myrda_tab').removeClass('active');
        $('#advsearch_tab').removeClass('active');
        $('#overview').removeClass('active');
        $('#search').removeClass('active');
        $('#myrda').removeClass('active');

        $('#'+useTab).addClass('active');
        $('#'+useTab+'_tab').addClass('active');
    }).on('click', '.search_help', function(event){
        $('#overview_tab').removeClass('active');
        $('#myrda_tab').removeClass('active');
        $('#advsearch_tab').removeClass('active');
        $('#overview').removeClass('active');
        $('#myrda').removeClass('active');
        $('#search').addClass('active');
        $('#search_tab').addClass('active');
    });


    //Feedback button
    window.ATL_JQ_PAGE_PROPS =  {
    "triggerFunction": function(showCollectorDialog) {
        //Requries that jQuery is available!
        jQuery(".feedback_button, .myCustomTrigger").click(function(e) {
            e.preventDefault();
            showCollectorDialog();
        });
    }};


});