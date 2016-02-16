/**
 * File:  subjectDirective
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
(function () {
    'use strict';


    angular
        .module('app')
        .directive('subjectDirective', subjectDirective);

    function subjectDirective() {
        return {
            restrict: 'AE',
            scope : {
                subjectType: '=',
                subjectValue : '='
            },
            templateUrl: base_url + 'assets/vocabs/templates/subjectDirective.html',
            link: function (scope, elem) {

                scope.updateVocab = function(vocab) {
                    scope.subjectValue = vocab + "Should init vocab widget again for vocab ";
                }
            }
        }
    }

})();