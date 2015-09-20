(function(){
    'use strict';
    angular.module('analytic_app')
        .directive('ro', roDirective)
        .directive('da', daDirective);

    function roDirective($log, $http) {
        return {
            templateUrl: apps_url + 'assets/analytics/templates/ro.html',
            scope: {
                obj: '='
            },
            link: function(scope, elem, attr, ngModel) {
                scope.ro = {};
                scope.base_url = portal_url;
                $http.get(apps_url+'/analytics/getRO/'+scope.obj.key)
                    .then(function(response){
                        if (response.data!='notfound') {
                            scope.ro = response.data;
                        } else {
                            scope.ro = {
                                'title': scope.obj.key,
                                'slug': '',
                                'roid': scope.obj.key
                            };
                        }
                    });
            }
        }
    }
    function daDirective($log, $http) {
        return {
            templateUrl: apps_url + 'assets/analytics/templates/da.html',
            scope: {
                obj: '='
            },
            link: function(scope, elem, attr, ngModel) {
                scope.da = {};
                scope.base_url = portal_url;
                $http.get(apps_url+'/analytics/getRO/'+scope.obj.key)
                    .then(function(response){
                        if (response.data!='notfound') {
                            scope.da = response.data;
                        } else {
                            scope.da = {
                                'title': scope.obj.key,
                                'slug': '',
                                'roid': scope.obj.key
                            };
                        }
                    });
            }
        }
    }

})();