(function(){
    'use strict';

    angular.module('analytic_app')
        .controller('mainCtrl', mainCtrl);

    function mainCtrl($scope, $log, $modal, analyticFactory, filterService, orgs, superUser) {
        var vm = this;
        vm.query = '';
        vm.orgs = orgs;
        vm.superUser = superUser;

        vm.apps_url = apps_url;
    }
})();