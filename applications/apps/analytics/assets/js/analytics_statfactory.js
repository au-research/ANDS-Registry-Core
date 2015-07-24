(function(){
    'use strict';
    angular
        .module('analytic_app')
        .factory('statFactory', statFactory);

    function statFactory($http, $log) {
        return {
            getStat: getStat
        }

        function getStat(type, filters) {
            return $http.post(apps_url+'analytics/getStat/'+type, {filters:filters})
                    .then(returnRaw)
                    .catch(handleError);
        }

        function returnRaw(response) {
            return response.data;
        }

        function handleError(error) {
            $log.error(error);
        }
    }
})();