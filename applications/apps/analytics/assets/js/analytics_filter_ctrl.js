(function(){
    'use strict';
    angular.module('analytic_app')
        .controller('filterCtrl', filterCtrl)

    function filterCtrl($scope, $log, $modal, analyticFactory, filterService) {
        var vm = this;
        vm.groups = [];
        vm.filters = filterService.getFilters();
        analyticFactory.getGroups().then(function(data){
            vm.groups = data;
        });
    }
})();