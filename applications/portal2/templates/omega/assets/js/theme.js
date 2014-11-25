/* ========================================================================
 * Omega: theme.js
 * Main Theme Javascript file
 * ========================================================================
 * Copyright 2014 Oxygenna LTD
 * ======================================================================== */

'use strict';

// ignore camel case because it breaks jshint for vars from php localisation
/* jshint camelcase: false */

/* global jQuery: false, skrollr: false, Odometer: false */

// If script is not localized apply defaults

var oxyThemeData = oxyThemeData || {
    navbarHeight : 90,
    navbarScrolled : 70,
    navbarScrolledPoint : 200,
    navbarScrolledSwatches: {
        up: 'swatch-white',
        down: 'swatch-white'
    },
    scrollFinishedMessage : 'No more items to load.',
    hoverMenu : {
        hoverActive : false,
        hoverDelay : 1,
        hoverFadeDelay : 200
    }
};

jQuery(document).ready(function( $ ) {
    // Parallax Scrolling - on desktops only
    // ======================
    if(!(/Android|iPhone|iPad|iPod|BlackBerry|Windows Phone/i).test(navigator.userAgent || navigator.vendor || window.opera)){
        skrollr.init({
            forceHeight: false
        });
    } else {
        // Assign the 'oxy-agent' class when not assigned by PHP - for the html Version
        // ======================
        $('body:not([class*="oxy-agent-"])').addClass('oxy-agent-');
        if((/iPhone/i).test(navigator.userAgent || navigator.vendor || window.opera)){
            $('body').not('.oxy-agent-iphone').addClass('oxy-agent-iphone');
        }
        if((/iPad/i).test(navigator.userAgent || navigator.vendor || window.opera)){
            $('body').not('.oxy-agent-ipad').addClass('oxy-agent-ipad');   
        }
    }

    // Flexslider Init
    // ======================

    function flexInit( element ) {
        // We use data atributes on the flexslider items to control the behaviour of the slideshow
        var slider = $(element),

        //data-slideshow: defines if the slider will start automatically (true) or not
        sliderShow = slider.attr('data-flex-slideshow') === 'false' ? false : true,

        //data-flex-animation: defines the animation type, slide (default) or fade
        sliderAnimation = !slider.attr('data-flex-animation') ? 'slide' : slider.attr('data-flex-animation'),

        //data-flex-speed: defines the animation speed, 7000 (default) or any number
        sliderSpeed = !slider.attr('data-flex-speed') ? 7000 : slider.attr('data-flex-speed'),

        //data-flex-sliderdirection: defines the slide direction
        direction = !slider.attr('data-flex-sliderdirection') ? 'horizontal' : slider.attr('data-flex-sliderdirection'),

        //data-flex-duration: defines the transition speed in milliseconds
        sliderDuration = !slider.attr('data-flex-duration') ? 600 : slider.attr('data-flex-duration'),

        //data-flex-directions: defines the visibillity of the nanigation arrows, hide (default) or show
        sliderDirections = slider.attr('data-flex-directions') === 'hide' ? false : true,

        //data-flex-controls: defines the visibillity of the nanigation controls, hide (default) or show
        sliderControls = slider.attr('data-flex-controls') === 'thumbnails' ? 'thumbnails' : slider.attr('data-flex-controls') === 'hide' ? false : true,

        //data-flex-controlsposition: defines the positioning of the controls, inside (default) absolute positioning on the slideshow, or outside
        sliderControlsPosition = slider.attr('data-flex-controlsposition') === 'inside' ? 'flex-controls-inside' : 'flex-controls-outside',

        //data-flex-controlsalign: defines the alignment of the controls, center (default) left or right
        sliderControlsAlign = !slider.attr('data-flex-controlsalign') ? 'flex-controls-center' : 'flex-controls-' + slider.attr('data-flex-controlsalign'),

        //data-flex-controlsalign: defines the alignment of the controls, center (default) left or right
        sliderControlsVericalAlign = !slider.attr('data-flex-controlsvertical') ? '' : 'flex-controls-' + slider.attr('data-flex-controlsvertical'),

        //data-flex-itemwidth: the width of each item in case of a multiitem carousel, 0 (default for 100%) or a nymber representing pixels
        sliderItemWidth = !slider.attr('data-flex-itemwidth') ? 0 : parseInt(slider.attr('data-flex-itemwidth'), 10),

        //data-flex-itemmax: the max number of items in a carousel
        sliderItemMax = !slider.attr('data-flex-itemmax') ? 0 : parseInt(slider.attr('data-flex-itemmax'), 0),

        //data-flex-itemmin: the max number of items in a carousel
        sliderItemMin = !slider.attr('data-flex-itemmin') ? 0 : parseInt(slider.attr('data-flex-itemmin'), 0),

            //data-flex-captionvertical: defines the horizontal positioning of the captions, left or right or alternate
        sliderCaptionsHorizontal = slider.attr('data-flex-captionhorizontal') === 'alternate' ? 'flex-caption-alternate' : 'flex-caption-' + slider.attr('data-flex-captionhorizontal');

        slider.addClass(sliderControlsPosition).addClass(sliderControlsAlign).addClass(sliderControlsVericalAlign).addClass(sliderCaptionsHorizontal);

        slider.flexslider({
            slideshow: sliderShow,
            animation: sliderAnimation,
            direction: direction,
            slideshowSpeed: parseInt(sliderSpeed),
            animationSpeed: parseInt(sliderDuration),
            itemWidth: sliderItemWidth,
            minItems: sliderItemMin,
            maxItems: sliderItemMax,
            controlNav: sliderControls,
            directionNav: sliderDirections,
            prevText: '',
            nextText: '',
            smoothHeight: true,
            useCSS : false
        });
    }

    // Flexslider Init
    $( '.flexslider[id]' ).each(function() {
        var that = this;
        $(this).imagesLoaded().done( function( instance ) {
            flexInit(that);
        });
    });

    // Portfolio Isotope
    // ======================

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
                        onScrollInit( $items.find( '.portfolio-os-animation' ), $container );
                        onScrollInit( $items.find( '.blog-os-animation' ), $container );
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

    $('.section[data-color-set]').each( function() {
        var $section = $(this);
        var duration = $section.attr('data-color-duration');
        var colors = $section.attr('data-color-set');
        // do we have any colours?
        if( colors.length > 0 ) {
            colors = colors.split(',');
            // wait duration then call animate background
            setTimeout(function() {
                oxyAnimateSectionBackgrounds( $section, colors, duration );
            }, duration);
        }
    });

    function oxyAnimateSectionBackgrounds( $section, colors, duration ) {
        var speed = $section.attr('data-color-speed');
        var color = colors.shift();
        colors.push(color);

        $section.animate({
            backgroundColor: color
        }, {
            duration: parseInt(speed),
            complete: function() {
                setTimeout(function() {
                    oxyAnimateSectionBackgrounds( $section, colors, duration );
                }, duration);
            }
        });
    }

    // Portfolio Filters
    // ======================

    $('.portfolio-filter').click( function() {
        var $button = $(this);
        var filter = $button.attr( 'data-filter' );
        var $filtersRow = $button.parents('div.row');
        $filtersRow.find( '.portfolio-title span' ).text( $button.text() );
        var $portfolio = $filtersRow.next();
        $portfolio.isotope( { filter: filter } );
    });

    $('.portfolio-order').click( function() {
        var $button = $(this);
        var order = $button.attr( 'data-value' );
        var $portfolio = $button.parents( 'div.row' ).next();
        $portfolio.isotope( { sortAscending: order === 'true' } );
    });

    $('.portfolio-sort').click( function() {
        var $button = $(this);
        var sort = $button.attr( 'data-sort' );
        var $portfolio = $button.parents('div.row').next();
        $portfolio.isotope( { sortBy: sort } );
    });

    // Theme Sections
    // ======================

    // Adjust full height sections on IOS
    $('[class*="oxy-agent-"] .section-fullheight').removeClass('section-fullheight').css('min-height', $(window).height()).find('.container, .container-fullwidth').css('min-height', $(window).height()).find('.row, [class*="col-md"]').css('position', 'static');

    // fix placeholders for IE 8 & 9
    $('html.ie9').find('input, textarea').placeholder();

    // Setiing responsive videos
    $( '.video-wrapper' ).fitVids();
    
    // Play videos & audio    
    $('audio').mediaelementplayer({
        pauseOtherPlayers: false,
        enableAutosize: false,        
        setDimensions:false,
    });
    
    $('.section-background-video').mediaelementplayer({
        pauseOtherPlayers: false,
        enableAutosize: false,        
        setDimensions:false,
        success: function(mediaElement, node, player) {            
            // video tag is initially hidden ( in order to hide the poster and display the cover bg only )
            $(mediaElement).parent().css('background-image','url(\'' + $(mediaElement).attr('poster') + '\')');                        
            var $section = $(mediaElement).closest('section');            
            
            // loadded data event does not trigger on ios, its dektop only             
            mediaElement.addEventListener('loadeddata', function () {                                     
                // player arg has media property filled only after loadeddata even triggers ( first frame loaded! )                    
                var containerHeight    = $section.outerHeight();
                var containerWidth     = $section.outerWidth();
                var playerHeight       = player.media.videoHeight;                    
                var playerWidth        = player.media.videoWidth;                    
                var aspectRatio        = ( playerHeight / playerWidth * 100 ) + '%';                   
                var scaleFactor        = containerWidth /playerWidth;
                var playerActualHeight = playerHeight * scaleFactor; 

                $(mediaElement).parent().css('padding-bottom', aspectRatio);   
                if( playerActualHeight >= containerHeight ){  
                    $(mediaElement).css('top', ( containerHeight - (playerHeight * scaleFactor) )/2 );                
                }    
                else {
                    $(mediaElement).css('background-image', '');
                } 

                $(mediaElement).show();                                                    
                
                $(window).smartresize(function(){                                            
                        containerHeight    = $section.outerHeight();
                        containerWidth     = $section.outerWidth();                    
                        scaleFactor        = containerWidth /playerWidth;
                        playerActualHeight = playerHeight * scaleFactor;
                    if( playerActualHeight >= containerHeight ){  
                        $(mediaElement).css('top', ( containerHeight - (playerHeight * scaleFactor) )/2 );
                    }
                    else {
                        $(mediaElement).css('background-image', '');
                    }                                                     
                });                  
            });            
            
            if( !$('body:not([class*="oxy-agent-"])').length ) {
                // if mobile show video immediately ( no loadeddata event here )
                $(mediaElement).show();
            }
            // ipad safari needs a javascript controller for the video
            if( $('body').hasClass('oxy-agent-ipad') ) {                 
                $section.on('click', function (e) {
                    if(mediaElement.paused) {
                        mediaElement.play();
                    }
                    else {
                        mediaElement.pause();
                    }       
                });   
            }           
        }
    });  

    // Feature List Shortcode
    // ======================

    // Feature list lines
    $('ul.features-connected').each(function() {
        var element = $(this);
        var div = $('<div class="features-connected-line"></div>').css('border-color' , element.attr('data-linecolor'));
        element.find('li').append( div );
    });

    // Countdown Shortcode
    // ======================

    $('.countdown').each(function() {
        var countdown = $(this);
        var date = countdown.attr('data-date' );
        countdown.countdown( date ).on('update.countdown', function(event) {
            $(this).find('.counter-days').html( event.offset.totalDays );
            $(this).find('.counter-hours').html( event.offset.hours );
            $(this).find('.counter-minutes').html( event.offset.minutes );
            $(this).find('.counter-seconds').html( event.offset.seconds );
        });
    });

    $('.counter').each(function() {
        var $counter = $(this);
        var $odometer = $counter.find('.odometer-counter');
        if($odometer.length > 0 ) {
            var od = new Odometer({
                el: $odometer[0],
                value: $odometer.text(),
                format: $counter.attr('data-format')
            });
            $counter.waypoint(function() {
                window.setTimeout(function() {
                    $odometer.html( $counter.attr( 'data-count' ) );
                }, 500);
            },{
                triggerOnce: true,
                offset: 'bottom-in-view'
            });
        }
    });

    // Woocommerce
    // ======================

    // WooCommerce minicart
    $('body.woo-cart-popup').on('click', '.mini-cart-overview a', function() {
        var $cartLink = $(this);
        $('.mini-cart-underlay').toggleClass('cart-open');
        $cartLink.attr( 'href' , '#mini-cart-container' );
        $('#mini-cart-container').toggleClass('active');
        $('body').toggleClass('mini-cart-opened');
    });

    // Theme Forms
    // ======================

    // Add wrapper to select boxes
    $('select').wrap('<div class="select-wrap">');


    // Bullet navigation
    // ======================
    var menu = $('#masthead');
    var menuInitOffset = $('#masthead').position();
    menuInitOffset = menuInitOffset === undefined ? 0 : menuInitOffset.top;

    var menuItems = $('.navbar').find('a');
    var scrollMenuItems = menuItems.map(function(){
        var item = this.hash;
        if (item && $(item).length ) {
            return item;
        }
    });

    var bulletNav = $('article .bullet-nav');
    if( bulletNav.length || scrollMenuItems.length ) {
        var sections = [];
        $('article').find('section').each( function() {
            // if section has an id
            if( this.id ) {
                sections.push(this);
            }
        });
        if( sections.length ) {
            menuItems.parent().removeClass('active current-menu-item');
            $.each( sections, function( index, section) {
                var $section = $(section);
                // create bullet nav
                if( bulletNav.length ){
                    var list = bulletNav.find('ul');
                    var tooltipAtts = '';
                    if( 'on' === bulletNav.attr( 'data-show-tooltips' ) ) {
                         tooltipAtts = 'data-toggle="tooltip" data-placement="left" title="' + $section.attr('data-label') + '"';
                    }
                    list.append('<li><a href="#' + section.id + '" ' + tooltipAtts + '>' + section.id + '</a>');
                }
                // set all section up waypoints
                $section.waypoint({
                    offset: function() {
                        return sectionWaypointOffset( $section, 'up', menu );
                    },
                    handler: function(direction) {
                        if( 'up' === direction ) {
                            sectionWaypointHandler( scrollMenuItems, menuItems, bulletNav, section );
                        }
                    }
                });
                // set all section down waypoints
                $section.waypoint({
                    offset: function() {
                        return sectionWaypointOffset( $section, 'down', menu );
                    },
                    handler: function(direction) {
                        if( 'down' === direction ) {
                            sectionWaypointHandler( scrollMenuItems, menuItems, bulletNav, section );
                        }
                    }
                });
            });
            // show the bullets if we have any
            if( bulletNav.length ){
                bulletNav.show();
                bulletNav.css('top', ( $(window).height() - bulletNav.height() )/ 2 );
            }
        }
    }

    function sectionWaypointOffset( $section, direction, menu ) {
        // if we are going up start from -1 to make sure event triggers
        var offset = direction === 'up' ? -($section.height() / 2) : 0;
        var menuHeight = parseInt(oxyThemeData.navbarHeight);
        if( menu.length && menu.hasClass('navbar-sticky') && menu.hasClass('navbar-scrolled') ){
            menuHeight = parseInt(oxyThemeData.navbarScrolled);
        }
        var sectionOffset = $section.offset().top;
        var menuOffset = menu.length? menu.position().top : 0;
        if( menu.length && menu.hasClass('navbar-sticky') && (  (menuOffset + menuHeight)  <= sectionOffset ) ){
            offset += menuHeight;
        }
        return offset;
    }

    function sectionWaypointHandler( scrollMenuItems, menuItems, bulletNav, section ) {
        if( scrollMenuItems.length ) {
            menuItems.parent().removeClass('active current-menu-item').end().filter('[href$="' + section.id + '"]').parent().addClass('active current-menu-item');
        }
        if( bulletNav.length ) {
            bulletNav.find('a').removeClass('active').filter('[href$="' + section.id + '"]').addClass('active');
        }
    }

    // Scroll To Section
    // ======================

    $('.navbar a, a.scroll-to-id, .bullet-nav a').on('click', function(e) {
        var target = this.hash;
        var offset = 0;

        if(target && $(target).length){
            e.preventDefault();

            var targetOffset = $(target).offset().top;
            var menuHeight = parseInt(oxyThemeData.navbarHeight);

            if(menu !== undefined && menu.hasClass('navbar-sticky') && ( menuInitOffset + menuHeight <= targetOffset)){
                var scrollPoint = parseInt( oxyThemeData.navbarScrolledPoint );
                var navHeightBeforeScrollPoint = parseInt( oxyThemeData.navbarHeight );
                var navHeightAfterScrollPoint = parseInt( oxyThemeData.navbarScrolled );

                offset = scrollPoint <= targetOffset ? navHeightAfterScrollPoint : navHeightBeforeScrollPoint;
            }

            $.scrollTo( $(target), 800, {
                axis: 'y',
                offset: -offset + 1
            } );
        }
    });

    // Calendar Widget
    // ======================

    // make WP calendar use boostrap table class
    $('#wp-calendar').addClass( 'table' );


    // Search Widget in Navbar
    // ======================

    // Add top bar functionallity
    $('.top-bar, #masthead').find('.widget_search form').wrap('<div class="top-search">');
    $('.top-search').append('<i class="fa fa-search search-trigger navbar-text"></i><svg class="search-close" preserveAspectRatio="none"  version="1.1" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path d="M25 25 L75 75" stroke-width="2"></path><path d="M25 75 L75 25" stroke-width="2"></path></svg>');
    $('body').on('click', '.search-trigger', function() {
        $('.top-search').toggleClass('active');
        $('#content').toggleClass('blur');
        $('#masthead').toggleClass('search-active');
        $('.top-search').find('form').fadeToggle(150).find('input').focus();

    });
    $('body').on('click', '.search-close', function() {
        $('.top-search').toggleClass('active');
        $('#content').toggleClass('blur');
        $('.top-search').find('form').fadeToggle(150);
        $('#masthead').toggleClass('search-active');
    });

    // Scrolling Events
    // ======================

    // header menu changes
    var header = $('body').not('.transparent-header').find('.navbar-sticky');

    // fix for menus in content
    $('#masthead').parents('.section').css({
        'overflow': 'visible',
        'position': 'relative',
        'z-index': '1100'
    });
    // stop navbar when at top of the page
    header.waypoint('sticky', {
        stuckClass: 'navbar-stuck'
    });

    // trigger the waypoint only for fixed position navbar
    var menuContainer = $('#masthead.navbar-sticky');
    if(menuContainer.length && menuContainer.hasClass('navbar-sticky')){
        // calculate menu offset in case menu is placed inside the page
        var menuOffset =  menuContainer.position().top;
        $('body').waypoint({
            offset: -( parseInt( oxyThemeData.navbarScrolledPoint ) + menuOffset ),
            handler: function(direction) {
                // add / remove scrolled class
                menuContainer.toggleClass('navbar-scrolled');
                // remove swatch class
                var prefix = 'swatch-';
                var classes = menuContainer[0].className.split(' ').filter(function(c) {
                    return c.lastIndexOf(prefix, 0) !== 0;
                });
                menuContainer[0].className = classes.join(' ');
                // add back swatch class depending on direction above / below scroll point
                menuContainer.addClass(oxyThemeData.navbarScrolledSwatches[direction]);

                menuContainer.one('MSTransitionEnd webkitTransitionEnd oTransitionEnd transitionend', function(){
                    // refresh waypoints only once transition ends in order to get correct offsets for sections.
                    if( !menuContainer.hasClass( 'refreshing' ) ) {
                        $.waypoints('refresh');
                    }
                    menuContainer.toggleClass('refreshing');
                });
            }
        });
    }

    $('body').waypoint({
        offset: -200,
        handler: function(direction) {
            if(direction === 'down'){
                $('.go-top').css('bottom', '12px').css('opacity', '1');
            }
            else{
                $('.go-top').css('bottom', '-44px').css('opacity', '0');
            }
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


    // Goto top button
    // ======================

    // Animate the scroll to top
    $('.go-top').click(function(event) {
        event.preventDefault();
        $('html, body').animate({scrollTop: 0}, 300);
    });


    // Bootstrap Tooltips
    // ======================

    // Function to init bootstrap's tooltip
    $('[data-toggle="tooltip"]').tooltip();


    // Bootstrap Carousel
    // ======================

    $('.carousel').carousel({
        interval: 7000
    });

    // Bootstrap Accordion Arrows
    // ======================

    // Function to fix accordion arrows
    $('.accordion-body').on('hide', function () {
        $(this).parent('.accordion-group').find('.accordion-toggle').addClass('collapsed');
    });


    // PIE Chart
    // ======================

    $('.chart').each( function() {
        var $chart = $(this);
        $chart.waypoint(function() {
            var $waypoint = $(this);
            $waypoint.easyPieChart({
                barColor: $waypoint.attr('data-bar-color'),
                trackColor: $waypoint.attr('data-track-color'),
                lineWidth: $waypoint.attr('data-line-width'),
                scaleColor: false,
                animate: 1000,
                size: $waypoint.attr('data-size'),
                lineCap: 'square'
            });
        },{
            triggerOnce: true,
            offset: 'bottom-in-view'
        });

        $chart.css('left', '50%');
        $chart.css('margin-left', - $chart.attr('data-size') / 2 );
    });


    // Fix for embedded videos
    // ======================

    var frames = document.getElementsByTagName( 'iframe' );
    for( var i = 0; i < frames.length; i++ ) {
        if( frames[i].src.indexOf('?') === -1 ) {
            frames[i].src += '?wmode=opaque';
        }
        else{
            frames[i].src += '&wmode=opaque';
        }
    }

    // Scroll to hide nav in iOS < 7
    // ======================

    if( ( navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPod/i) ) && !navigator.userAgent.match(/(iPad|iPhone);.*CPU.*OS 7_\d/i) ) {
        if(window.addEventListener){
            window.addEventListener('load', function() {
                // Set a timeout...
                setTimeout(function(){
                    // Hide the address bar!
                    window.scrollTo(0, 1);
                }, 0);
            });
        }
    }


    // Icon Animations
    // ======================

    $('[data-animation]').each(function(){
        var element         = $(this);

        element.on('mouseenter', function(){
            if( element.attr('data-animation') !== 'none' ){
                element.addClass('animated ' + element.attr('data-animation'));
            }
        });

        element.on('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(e) {
            element.removeClass('animated ' + element.attr('data-animation'));
        });

    });


    // Isotope Infinite Scroll
    // ======================

    var $container = $('div.blog-masonry.isotope');
    if($container.length > 0){
        $container.infinitescroll({
            navSelector  : 'div.infinite-scroll',    // selector for the paged navigation
            nextSelector : 'div.infinite-scroll a',  // selector for the NEXT link (to page 2)
            itemSelector : '.masonry-item',     // selector for all items you'll retrieve
            loading: {
                finishedMsg: oxyThemeData.scrollFinishedMessage,
            },
            prefill:true
        },
            // call Isotope as a callback
            function( newElements ) {
                $(newElements).each(function(index, element){

                    // Set the masonry column width
                    var columns = 3;
                    var screenWidth = $(window).width();
                    var wideColumns = 2;
                    var padding = $container.attr( 'data-padding' );

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

                    var itemWidth = Math.floor( $container.width() / columns );

                    var item  = $(this);
                    if( item.hasClass( 'masonry-wide' ) ) {
                        item.css( 'width', itemWidth * wideColumns );
                    }
                    else {
                        item.css( 'width', itemWidth );
                    }
                    item.find('.post-masonry-inner').css( 'padding', padding / 2 + 'px' );
                });
                $container.isotope( 'appended', $( newElements ), function(){
                    $container.isotope( 'reLayout', function() {
                        $.waypoints('refresh');
                    });
                });
            }
        );
    }

    // Magnific Image Popup
    // ======================

    $('.magnific').magnificPopup({
        type:'image',
        removalDelay: 300,
        mainClass: 'mfp-fade'
    });

    // Magnific Video Popup
    // ======================

    $('.magnific-youtube, .magnific-vimeo, .magnific-gmaps').magnificPopup({
        disableOn: 700,
        type: 'iframe',
        mainClass: 'mfp-fade',
        removalDelay: 300,
        preloader: false,
        fixedContentPos: false
    });

    // Magnific Gallery Popup
    // ======================

    $('.magnific-gallery').each(function(index , value){
        var gallery = $(this);
        var galleryImages = $(this).data('links').split(',');
        var items = [];
        for(var i=0;i<galleryImages.length; i++){
            items.push({
                src:galleryImages[i],
                title:''
            });
        }
        gallery.magnificPopup({
            mainClass: 'mfp-fade',
            items:items,
            gallery:{
                enabled:true,
                tPrev: $(this).data('prev-text'),
                tNext: $(this).data('next-text')
            },
            type: 'image'
        });
    });

    // Magnific Product Popup
    // ======================

    $('.product-gallery').magnificPopup({
        delegate: 'li figcaption a',
        type: 'image',
        mainClass: 'mfp-fade',
        gallery:{
            enabled:true,
            navigateByImgClick:true
        }
    });


    // Social Icons Hover Colours
    // ======================

    $('[data-iconcolor]').each(function(){
        var element         = $(this);
        var originalColor  = $(element).css('background-color');
        var originalTextColor  = $(element).find('i').css('color');
        element.on('mouseenter', function(){
            element.css('background-color' , element.attr('data-iconcolor'));
            element.find('i').css('color' , '#ffffff');
            if (element.parents('.social-icons').hasClass('social-simple')) {
                element.find('i').css('color' , element.attr('data-iconcolor'));
            }
        });
        element.on('mouseleave', function(){
            element.css('background-color' ,originalColor);
            element.find('i').css('color' , originalTextColor);
            if (element.parents('.social-icons').hasClass('social-simple')) {
                element.find('i').css('color' , originalTextColor);
            }
        });

    });

    // Hover menu
    // ======================
    if (oxyThemeData.hoverMenu.hoverActive === true) {
        $('.navbar .dropdown').hover(function() {
            $(this).find('.dropdown-menu').first().stop(true, true).delay(oxyThemeData.hoverMenu.hoverDelay).fadeIn(parseInt(oxyThemeData.hoverMenu.hoverFadeDelay));
        }, function() {
            $(this).find('.dropdown-menu').first().stop(true, true).delay(oxyThemeData.hoverMenu.hoverDelay).fadeOut(parseInt(oxyThemeData.hoverMenu.hoverFadeDelay));
        });
    }
});