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
    }

    // Re initialise isotope on window resize
    $(window).smartresize(function(){
        isotopeInit();
    });

    // Init the isotope
    isotopeInit();

    $(document).on('click', '.togglediv', function(e){
        e.preventDefault();
        var div = $(this).attr('data-toggle');
        console.log(div, $(div), $(div).length);
        $(div).toggle();
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
                delay: 1000,
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
                    delay: 1000,
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
    });


    //Feedback button
    window.ATL_JQ_PAGE_PROPS =  {
        "triggerFunction": function(showCollectorDialog) {
            //Requries that jQuery is available!
            jQuery(".myCustomTrigger").click(function(e) {
                e.preventDefault();
                showCollectorDialog();
            });
        }};

});