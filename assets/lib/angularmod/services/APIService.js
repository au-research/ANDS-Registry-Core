(function(){
    'use strict';
    angular
        .module('APIService', [])
        .service('APIService', APIService)
    ;

    function APIService ($http, $log) {
        return {
            get: function (path, data) {
                data['api_key'] = internal_api_key;
                return $http({
                    url: api_url+path,
                    method: "GET",
                    params: data
                }).then(returnRaw, handleError);
            },
            post: function(path, data) {
                data['api_key'] = internal_api_key;
                return $http({
                    url: api_url+path+'/?api_key='+data['api_key'],
                    method: "POST",
                    data: $.param(data),
                    headers : { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).then(returnRaw, handleError);
            },
            postlegacy: function (path, data) {
                return $http({
                    method  : 'POST',
                    url     : path,
                    data    : $.param(data),
                    headers : { 'Content-Type': 'application/x-www-form-urlencoded' }
                }).then(returnRaw, handleError);
            }
        };

        function returnRaw(response) {
            return response.data;
        }

        function handleError(error) {
            $log.error(error.data.data, error);
            return error;
        }
    }
})();