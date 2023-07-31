(function(){
    'use strict';
    angular
        .module('APIRole', ['APIService'])
        .service('APIRoleService', APIRoleService)
    ;

    function APIRoleService(APIService) {
        return {
            getAPPIDsByRole: function(user_id) {
                return APIService.get(
                    'role', {
                        'roleId': user_id,
                        'include':'assoc_doi_app_id'
                    }
                );
            }
        }

    }
})();