/**
 * Created by leomonus on 19/02/16.
 */
(function () {
    'use strict';


    angular
        .module('app')
        .directive('widgetDirective', widgetDirective);

    function widgetDirective(vocabs_factory) {
        return {
            restrict: 'AE',
            templateUrl: base_url + 'assets/vocabs/templates/widgetDirective.html',
            link: function (scope, elem) {

                scope.vocabList = [];

                vocabs_factory.getAllWidgetable().then(function(data){
                   scope.vocabList = data.response.docs;
                });

                scope.items = [];

                scope.selectVocab = function(vocab) {
                    scope.selectedVocab = vocab;
                }

            }
        }





    }

})();