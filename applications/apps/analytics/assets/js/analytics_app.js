(function () {
    'use strict';

    angular.module("analytic_app",
        ['chart.js', 'daterangepicker', 'ui.bootstrap', 'ngRoute'])
        .config(configuration)
    ;

    function configuration($routeProvider, $locationProvider) {
        $routeProvider
            .when('/', {
                templateUrl: apps_url+'assets/analytics/pages/index.html',
                controller: 'mainCtrl',
                controllerAs: 'vm',
                resolve: {
                    orgs: function(analyticFactory) {
                        return analyticFactory.getOrg();
                    }
                }
            })
            .when('/rda/:role_id', {
                templateUrl: apps_url+'assets/analytics/pages/rda.html',
                controller: 'rdaCtrl',
                controllerAs: 'vm',
                resolve: {
                    org: function(analyticFactory, $route) {
                        var id = $route.current.params.role_id;
                        return analyticFactory.getOrg(id);
                    }
                }
            })
        ;
    }



})();