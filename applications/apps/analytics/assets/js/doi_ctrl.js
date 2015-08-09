(function(){
    'use strict';
    angular.module('analytic_app')
        .controller('doiCtrl', doiCtrl);

    function doiCtrl($scope, $log, $modal, analyticFactory, filterService, org) {
        var vm = this;
        vm.org = org;

        vm.filters = filterService.getFilters();
        vm.filters['doi_app_id'] = vm.org.doi_app_id;
        vm.filters['groups'] = vm.org.groups;
        // vm.filters['class'] = 'collection';
        $log.debug(vm.filters['doi_app_id']);

        $scope.$watch('vm.filters', function(data){
            if (data) vm.getDOISummary();
        }, true);

        vm.getDOISummary = function(filters) {

            //get doi breakdown
            analyticFactory.getStat('doi', vm.filters).then(function(data){
                vm.doiChartData = {
                    labels: ["Missing DOI", "Has DOI"],
                    data: [data['missing_doi'], data['has_doi']]
                }
            });



        }

    }

})();