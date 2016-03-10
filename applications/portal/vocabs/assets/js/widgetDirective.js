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
                scope.base_url = base_url;

                vocabs_factory.getAllWidgetable().then(function (data) {
                    scope.vocabList = data.response.docs;
                    scope.selectVocab(scope.vocabList[0]);
                });

                scope.items = [];

                scope.selectVocab = function (vocab) {
                    scope.selectedVocab = vocab;
                }
            }
        }
    }

})();