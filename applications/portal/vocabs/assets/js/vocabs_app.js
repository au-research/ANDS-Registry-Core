(function(){
    'use strict';

    angular
        .module('app', ['ngRoute', 'ngSanitize', 'ui.bootstrap', 'ui.utils', 'angular-loading-bar', 'angularFileUpload'])
        .config(function($interpolateProvider, $locationProvider, $logProvider){
            $interpolateProvider.startSymbol('[[');
            $interpolateProvider.endSymbol(']]');
            $locationProvider.hashPrefix('!');
            $logProvider.debugEnabled(true);
        });
})();


$(document).ready(function() {

    $("#widget-info").hide();

    if($("#widget-info").length == 0)
        $("#widget-link").hide();

    $('#widget-link').click(function(){
        $('html, body').animate({
            scrollTop: $( $.attr(this, 'href') ).offset().top
        }, 1000);
        if($("#widget-info").is( ":hidden" )){
            $("#widget-toggle").click();
        }

        return false;
    });

    $("#widget-toggle").click(function() {

        if($("#widget-info").is( ":visible" ))
            $("#widget-toggle").text("Show code");
        else
            $("#widget-toggle").text("Hide code");

        $("#widget-info").slideToggle("slow");
    });


});

$(document).on('mouseover', 'a[tip]', function(event){
    $(this).qtip({
        content:{
            text:function(e,api){
                var tip = $(this).attr('tip');
                var content = tip;
                if(tip.indexOf('#')==0 || tip.indexOf('.')==0) {
                    if($(tip.toString()).length) {
                        content = $(tip.toString()).html();
                    }
                }
                return content;
            }
        },
        show: {
            event: 'mouseover, click',
            ready: true
        },
        hide: {
            delay: 1000,
            fixed: true
        },
        position: {target:'mouse', adjust: { mouse: false }, viewport: $(window) },
        style: {classes: 'qtip-light qtip-shadow qtip-normal qtip-bootstrap'}
    });
});

$(document).on('click', '.re_preview', function(event){
    event.preventDefault();
    $(this).qtip({
        show:{event:'click'},
        hide: {
            delay: 1000,
            fixed: true
        },
        content: {
            text:  function(event, api) {
                api.elements.content.html('Loading...');
                if ($(this).attr('related')) {
                   // return "we have some text for re "+$(this).attr('re_id');
                    var url = base_url+'vocabs/related_preview/?related='+$(this).attr('related')+'&v_id='+$(this).attr('v_id')+'&sub_type='+$(this).attr('sub_type');
                                }
                if (url) {
                    return $.ajax({
                        url:url
                    }).then(function(content){
                        return content;
                    },function(xhr,status,error){
                        api.set('content.text', status + ': ' + error);
                    });
                } else {
                    return 'Error displaying preview';
                }

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
//Feedback button
window.ATL_JQ_PAGE_PROPS =  {
    "triggerFunction": function(showCollectorDialog) {
        //Requries that jQuery is available!
        jQuery(".feedback_button, .myCustomTrigger").click(function(e) {
            e.preventDefault();
            showCollectorDialog();
        });
    }};

$(document).on('click', '.ver_preview', function(event){
    event.preventDefault();
    $(this).qtip({
        show:{event:'click'},
        hide: {
            delay: 1000,
            fixed: true
        },
        content: {
            text:  function(event, api) {
                api.elements.content.html('Loading...');
                if ($(this).attr('version')) {
                    var url = base_url+'vocabs/version_preview/?version='+$(this).attr('version');
                }
                if (url) {
                    return $.ajax({
                        url:url
                    }).then(function(content){
                        return content;
                    },function(xhr,status,error){
                        api.set('content.text', status + ': ' + error);
                    });
                } else {
                    return 'Error displaying preview';
                }

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

$(document).on('click', '.deleteVocab', function(e){
    e.preventDefault();
    if (confirm('Are you sure you want to delete this vocabulary including all endpoints? This action cannot be reversed.')) {
        var vocab_id = $(this).attr('vocab_id');
        $.ajax({
            url:base_url+'vocabs/delete',
            type: 'POST',
            data:{id:vocab_id},
            success: function(data) {
                location.reload();
            }
        });
    } else {
        return false;
    }
});


