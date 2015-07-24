(function(){
    'use strict';
    angular.module('analytic_app')
        .controller('statCtrl', statCtrl)

    function statCtrl($scope, $log, $modal, filterService) {
        var vm = this;
        vm.filters = filterService.getFilters();
    }
})();