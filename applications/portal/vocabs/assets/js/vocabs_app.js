
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
})


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
                if ($(this).attr('re_id')) {
                    return "we have some text for re "+$(this).attr('re_id');
                    var url = base_url+'related_entity/preview/?re_id='+$(this).attr('re_id');
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

var app = angular.module('app', ['ngRoute', 'ngSanitize', 'ui.bootstrap', 'ui.utils', 'angular-loading-bar']);

app.config(function($interpolateProvider, $locationProvider, $logProvider){
	$interpolateProvider.startSymbol('[[');
	$interpolateProvider.endSymbol(']]');
	$locationProvider.hashPrefix('!');
	$logProvider.debugEnabled(true);
});
