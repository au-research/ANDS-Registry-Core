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


$("#vocab-tree").vocab_widget({
    mode:'tree',
    endpoint: 'https://researchdata.ands.org.au/apps/vocab_widget/proxy/',
    display_count:false,
    repository:$("#vocab-tree").attr('vocab')})
    .on('treeselect.vocab.ands', function(event) {
        var target = $(event.target);
        var data = target.data('vocab');
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


