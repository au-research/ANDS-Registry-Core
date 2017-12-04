(function () {
    'use strict';

    angular
        .module('sync_app', ['APITask', 'APIDataSource', 'APIRegistryObject', 'ngRoute', 'ui.bootstrap'])
        .config(configuration);

    function configuration($routeProvider) {
        $routeProvider
            .when('/', {
                controller: 'indexCtrl',
                templateUrl: apps_url + 'assets/sync_manager/templates/syncmenu_index.html'
            });
    }

})();
