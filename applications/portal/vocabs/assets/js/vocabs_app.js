
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

var app = angular.module('app', ['ngRoute', 'ngSanitize', 'ui.bootstrap', 'ui.utils', 'angular-loading-bar']);

app.config(function($interpolateProvider, $locationProvider, $logProvider){
	$interpolateProvider.startSymbol('[[');
	$interpolateProvider.endSymbol(']]');
	$locationProvider.hashPrefix('!');
	$logProvider.debugEnabled(true);
});
