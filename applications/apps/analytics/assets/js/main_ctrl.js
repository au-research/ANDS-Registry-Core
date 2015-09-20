(function(){
    'use strict';

    angular.module('analytic_app')
        .controller('mainCtrl', mainCtrl);

    function mainCtrl($scope, $log, $modal, analyticFactory, filterService, orgs) {
        var vm = this;
        vm.query = '';
        vm.orgs = orgs;

        vm.apps_url = apps_url;
    }
})();