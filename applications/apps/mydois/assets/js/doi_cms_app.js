(function(){
    'use strict';
    angular
        .module('doi_cms_app', ['ngRoute', 'ngDataciteXMLBuilder', 'APIRole', 'APIDOI'])
        .controller('indexCtrl', indexCtrl)
        .config(configuration)
    ;

    function configuration($routeProvider, $locationProvider) {
        $routeProvider
            .when('/', {
                templateUrl: apps_url+'assets/mydois/templates/index.html',
                controller: 'indexCtrl',
                controllerAs: 'vm',
                resolve: {
                    client: function(APIRoleService, $log) {
                        var user_id = $('#logged_in_user_id').val();
                        return APIRoleService.getAPPIDsByRole(user_id);
                    }
                }
            })
            .when('/manage/:app_id/', {
                templateUrl: apps_url+'assets/mydois/templates/doi_cms_app.html',
                controller: 'mainCtrl',
                controllerAs: 'vm',
                resolve: {
                    client: function(APIDOIService, $route) {
                        var app_id = $route.current.params.app_id;
                        return APIDOIService.getClient(app_id);
                    }
                }
            })
            ;
        ;
    }

    function indexCtrl(client, APIDOIService, $scope, $log) {
        var vm = this;
        vm.client = client.data;

        vm.getClientDetails = function(){
            angular.forEach(vm.client.assoc_doi_app_id, function(app_id, index){
                APIDOIService.getClient(app_id).then(function(data){
                    vm.client.assoc_doi_app_id[index] = data.data.client;
                });
            });
        }
        vm.getClientDetails();
    }

})();