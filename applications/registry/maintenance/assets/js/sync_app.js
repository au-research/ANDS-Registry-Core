(function () {
    'use strict';

    angular
        .module('sync_app', ['APITask', 'APIDataSource', 'ngRoute'])
        .config(configuration);

    function configuration($routeProvider) {
        $routeProvider
            .when('/', {
                controller: 'indexCtrl',
                templateUrl: base_url + 'assets/maintenance/templates/syncmenu_index.html'
            });
    }

})();
