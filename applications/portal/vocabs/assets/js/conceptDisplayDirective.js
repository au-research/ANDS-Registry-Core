/**
 * Created by leomonus on 19/02/16.
 */
(function () {
    'use strict';


    angular
        .module('app')
        .directive('conceptDisplay', conceptDisplayDirective);

    function conceptDisplayDirective() {
        return {
            restrict: 'AE',

            templateUrl: base_url + 'assets/vocabs/templates/conceptDisplay.html',
            link: function (scope) {

                scope.switchField = function(newField, value) {
                    scope.target_field = newField;
                    var subjectValueInput = $('#sampleWidgetInput');
                    subjectValueInput.val(value);
                }
            }
        }


    }

})();
