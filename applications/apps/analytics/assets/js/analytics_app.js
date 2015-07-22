(function () {
    'use strict';

    angular.module("analytic_app", ['chart.js', 'daterangepicker']);

    angular.module('analytic_app')
        .controller('mainCtrl', mainCtrl)

    function mainCtrl($log, analyticFactory) {
        var vm = this;
        vm.types = ['Line', 'Bar'];
        vm.chartType = vm.types[0];
        vm.groups = [];

        vm.filters = {
            'log': 'portal',
            'period': {'startDate': '2015-06-01', 'endDate': '2015-06-06'},
            'group': {
                'type':'group', 'value':'State Records Authority of New South Wales'
            },
            'dimensions': [
                'portal_view', 'portal_search'
            ]
        };

        vm.onClick = function (points, evt) {
            $log.debug(points, evt);
        };

        vm.getData = function() {
            vm.loading = true;
            return analyticFactory.summary(vm.filters).then(function(data){
                vm.loading = false;
                vm.chartData = data;
            });
        }
        vm.getData();
        analyticFactory.getGroups().then(function(data){
            vm.groups = data;
        });

    }

})();