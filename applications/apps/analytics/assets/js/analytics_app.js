(function () {
    'use strict';

    angular.module("analytic_app",
        ['chart.js', 'daterangepicker', 'ui.bootstrap', 'ngRoute'])
        .config(configuration)
    ;

    function configuration($routeProvider, $locationProvider) {
        $routeProvider
            .when('/', {
                templateUrl: apps_url + 'assets/analytics/pages/index.html',
                controller: 'mainCtrl',
                controllerAs: 'vm',
                resolve: {

                    superUser: function (analyticFactory) {
                        return analyticFactory.getUser();
                    },

                    orgs: function (analyticFactory) {
                        return analyticFactory.getOrg();
                    }
                }
            })
            .when('/report/:role_id', {
                templateUrl: apps_url + 'assets/analytics/pages/report.html',
                controller: 'reportCtrl',
                controllerAs: 'vm',
                resolve: {
                    org: function (analyticFactory, $route) {
                        var id = $route.current.params.role_id;
                        return analyticFactory.getOrg(id);
                    }
                }
            })
            .when('/masterview', {
                templateUrl: apps_url + 'assets/analytics/pages/report.html',
                controller: 'reportCtrl',
                controllerAs: 'vm',
                resolve: {
                    org: function (analyticFactory) {
                        return analyticFactory.getOrgAPI("Masterview");
                    }
                }
            })
        ;
    }


})();