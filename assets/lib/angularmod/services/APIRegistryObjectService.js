/**
 * File:  APIRegistryObjectService
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
(function () {
    'use strict';

    angular
        .module('APIRegistryObject', ['APIService'])
        .service('APIRegistryObjectService', APIRegistryObjectService);

    function APIRegistryObjectService(APIService) {
        return {
            syncRecord: function(id) {
                return APIService.get('registry/records/' + id + '/sync', {});
            }
        }

    }
})();