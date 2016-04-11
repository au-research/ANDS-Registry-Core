/**
 * Created by leomonus on 19/02/16.
 */
(function () {
    'use strict';

    angular
        .module('app')
        .directive('widgetDirective', widgetDirective);

    function widgetDirective($templateCache, vocabs_factory) {
        return {
            restrict: 'AE',
            templateUrl: base_url + 'assets/vocabs/templates/widgetDirective.html',
            link: function (scope, elem) {

                scope.vocabList = [];
                scope.base_url = base_url;

                vocabs_factory.getAllWidgetable().then(function (data) {
                    scope.vocabList = data.response.docs;
                    // Preset the dropdown to the first vocab in the list, if any.
                    if (scope.vocabList instanceof Array
                        && scope.vocabList.length > 0) {
                        //scope.selectVocab(scope.vocabList[0]);
                    }
                });

                // Override the Bootstrap templates defined
                // at the end of assets/js/lib/ui-select/dist/select.js.
                
                $templateCache.put("bootstrap/select.tpl.html","<div class=\"ui-select-container ui-select-bootstrap dropdown swatch-white\" ng-class=\"{open: $select.open}\"><div class=\"ui-select-match\"></div><input type=\"text\" autocomplete=\"off\" tabindex=\"-1\" aria-expanded=\"true\" aria-label=\"{{ $select.baseTitle }}\" aria-owns=\"ui-select-choices-{{ $select.generatedId }}\" aria-activedescendant=\"ui-select-choices-row-{{ $select.generatedId }}-{{ $select.activeIndex }}\" class=\"form-control ui-select-search\" placeholder=\"{{$select.placeholder}}\" ng-model=\"$select.search\" ng-show=\"$select.searchEnabled && $select.open\"><div class=\"ui-select-choices\"></div></div>");

                $templateCache.put("bootstrap/match.tpl.html","<div class=\"ui-select-match\" ng-hide=\"$select.open\" ng-disabled=\"$select.disabled\" ng-class=\"{\'btn-default-focus\':$select.focus}\"><span tabindex=\"-1\" class=\"btn btn-primary form-control ui-select-toggle\" aria-label=\"{{ $select.baseTitle }} activate\" ng-disabled=\"$select.disabled\" ng-click=\"$select.activate()\" style=\"outline: 0;\"><span ng-show=\"$select.isEmpty()\" class=\"ui-select-placeholder text-muted\">{{$select.placeholder}}</span> <span ng-hide=\"$select.isEmpty()\" class=\"ui-select-match-text pull-left\" ng-class=\"{\'ui-select-allow-clear\': $select.allowClear && !$select.isEmpty()}\" ng-transclude=\"\"></span> <i class=\"caret pull-right\" ng-click=\"$select.toggle($event)\"></i> <a ng-show=\"$select.allowClear && !$select.isEmpty()\" aria-label=\"{{ $select.baseTitle }} clear\" style=\"margin-right: 10px\" ng-click=\"$select.clear($event)\" class=\"btn btn-xs btn-link pull-right\"><i class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></i></a></span></div>");

                scope.items = [];

                scope.selectVocab = function (vocab) {
                    scope.selectedVocab = vocab;
                }
            }
        }
    }

})();
