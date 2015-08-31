(function(){
    'use strict';
    angular.module('analytic_app')
        .controller('modalDetailCtrl', modalDetailCtrl)

    function modalDetailCtrl($log, data, analyticFactory) {

        var vm = this;
        vm.data = data;
        vm.filters = {};
        angular.copy(vm.data.filters, vm.filters);

        vm.header = vm.data.value;

        if (vm.data.type == 'showdate') {
            vm.filters.period = {
                'startDate': vm.data.value,
                'endDate': vm.data.value
            }
            vm.filters.log = 'rdalogs';
        } else if (vm.data.type == 'has_doi') {
            delete vm.filters.period;
            vm.filters.log = 'rda';
            vm.filters.type = 'has_doi';
        } else if (vm.data.type == 'missing_doi') {
            delete vm.filters.period;
            vm.filters.log = 'rda';
            vm.filters.type = 'missing_doi';
        }


        vm.getEvents = function() {
            analyticFactory.getEvents(vm.filters).then(function(data){
                vm.results = data;
            })
        }
        vm.getEvents();

    }
})();