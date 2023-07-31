(function(){
    'use strict';
    angular
        .module('doi_cms_app1', ['ngRoute', 'ngDataciteXMLBuilder', 'APIRole', 'APIDOI'])
        .controller('indexCtrl', indexCtrl)
        .config(configuration)
    ;

    function configuration($routeProvider, $locationProvider) {
        $routeProvider
            .when('/', {
                templateUrl: base_url+'assets/js/xml_builder.html',
                controller: 'mainCtrl',
                controllerAs: 'vm',

            })
            ;
        ;
    }

    function indexCtrl(client, APIDOIService, $scope, $log) {
        vm.test = "this is a test";

    }

})();