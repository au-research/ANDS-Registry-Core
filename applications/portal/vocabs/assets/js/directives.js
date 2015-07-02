(function () {
    'use strict';

    angular
        .module('app')
        .directive('ngDebounce', function ($timeout) {
            return {
                restrict: 'A',
                require: 'ngModel',
                priority: 99,
                link: function (scope, elm, attr, ngModelCtrl) {
                    if (attr.type === 'radio' || attr.type === 'checkbox') return;

                    elm.unbind('input');

                    var debounce;
                    elm.bind('input', function () {
                        $timeout.cancel(debounce);
                        debounce = $timeout(function () {
                            scope.$apply(function () {
                                ngModelCtrl.$setViewValue(elm.val());
                            });
                        }, attr.ngDebounce || 1000);
                    });
                    elm.bind('blur', function () {
                        scope.$apply(function () {
                            ngModelCtrl.$setViewValue(elm.val());
                        });
                    });
                }

            }
        })
        .directive('languageValidation', function () {
            return {
                restrict: 'A',
                link: function (scope, elem, attr, ctrl) {

                }
            }
        })
    ;
})();