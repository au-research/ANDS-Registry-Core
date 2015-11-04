(function(){
    'use strict';
    angular
        .module('doi_cms_app', ['ngRoute', 'ngDataciteXMLBuilder'])
        .config(configuration)
    ;

    function configuration($routeProvider, $locationProvider) {
        $routeProvider
            .when('/', {
                templateUrl: apps_url+'assets/mydois/templates/index.html',
                controller: 'mainCtrl',
                controllerAs: 'vm',
                resolve: {
                    client: function($location, doiFactory){
                        var app_id = "";
                        app_id = $location.search().app_id;
                        if (!app_id) app_id = $location.search().app_id_select;
                        return doiFactory.getClient(app_id);
                    }
                }
            });
        ;
    }

})();