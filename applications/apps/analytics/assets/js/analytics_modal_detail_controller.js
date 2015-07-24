(function(){
    'use strict';
    angular.module('analytic_app')
        .controller('modalDetailCtrl', modalDetailCtrl)

    function modalDetailCtrl($log, data, analyticFactory) {

        var vm = this;
        vm.data = data;

        vm.filters = {};
        angular.copy(vm.data.filters, vm.filters);
        $log.debug(vm.filters);
        vm.filters.period = {
            'startDate': vm.data.date,
            'endDate': vm.data.date
        }

        vm.getEvents = function() {
            analyticFactory.getEvents(vm.filters).then(function(data){
                vm.results = data;
                $log.debug(vm.results);
            })
        }
        vm.getEvents();

    }
})();