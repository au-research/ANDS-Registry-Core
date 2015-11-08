(function(){
    'use strict';
    angular
        .module('doi_cms_app', ['ngRoute', 'ngDataciteXMLBuilder'])
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
                    client: function(doiFactory, $log) {
                        var user_id = $('#logged_in_user_id').val();
                        return doiFactory.getAppIDs(user_id);
                    }
                }
            })
            .when('/manage/:app_id', {
                templateUrl: apps_url+'assets/mydois/templates/doi_cms_app.html',
                controller: 'mainCtrl',
                controllerAs: 'vm',
                resolve: {
                    client: function(doiFactory, $route) {
                        var app_id = $route.current.params.app_id;
                        return doiFactory.getClient(app_id);
                    }
                }
            })
            ;
        ;
    }

    function indexCtrl(client, doiFactory, $scope, $log) {
        var vm = this;
        vm.client = client.data;

        vm.getClientDetails = function(){
            angular.forEach(vm.client.assoc_doi_app_id, function(app_id, index){
                doiFactory.getClient(app_id).then(function(data){
                    vm.client.assoc_doi_app_id[index] = data.data.client;
                });
            });
        }
        vm.getClientDetails();
    }

})();