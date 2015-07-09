(function(){
    'use strict';
    angular
        .module('analytic_app')
        .factory('analyticFactory', analyticFactory);

    function analyticFactory($http, $log) {
        return {
            summary: getSummaryData,
            getGroups: getGroups,
            testdata: getTestData
        };

        function getSummaryData(filters) {
            return $http.post(apps_url+'analytics/summary', {filters:filters})
                    .then(returnData)
                    .catch(handleError);
        }

        function getTestData() {
            return $http.get(apps_url+'analytics/summary')
                        .then(returnData)
                        .catch(handleError);
        }

        function returnData(response) {
            var result = {
                labels: [],
                series: ['View', 'Search'],
                data: [[],[]]
            };
            angular.forEach(response.data.result, function (obj, index) {
                result.labels.push(index);
                result.data[0].push(obj['portal_view']);
                if (obj['portal_search']) {
                    result.data[1].push(obj['portal_search'])
                } else {
                    result.data[1].push(0);
                }
            });
            return result;
        }

        function getGroups() {
            var groups = [];
            return $http.get(base_url+'registry_object/getGroupSuggestor')
                    .then(function(response){
                        angular.forEach(response.data, function(obj){
                            groups.push(obj.value);
                        });
                        return groups;
                    }).catch(handleError);
        }

        function handleError(error) {
            $log.error(error);
        }
    }
})();