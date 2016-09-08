(function(){
    'use strict';
    angular
        .module('APIDOI', ['APIService'])
        .service('APIDOIService', APIDOIService)
    ;

    function APIDOIService(APIService) {
        return {
            getClient: function (app_id) {
                return APIService.get(
                    'doi/client/', {'app_id': app_id, }
                );
            },
            getDOIList: function (app_id, limit, offset, search) {
                if (!limit) limit = 5;
                if (!offset) offset = 0;
                if (!search) search = '';
                return APIService.get(
                    'doi/list/', {
                        'app_id': app_id,
                        'limit': limit,
                        'offset': offset,
                        'search' : search
                    }
                );
            },
            getDOI: function (doi, app_id) {
                return APIService.get(
                    'doi/'+doi,{'app_id':app_id }
                );
            },
            getLog: function (app_id) {
                return APIService.get(
                    'doi/log/', {'app_id': app_id, }
                );
            },
            getBlankDataciteXML: function(doi) {
                var xml ='<?xml version="1.0" encoding="utf-8"?><resource xmlns="http://datacite.org/schema/kernel-3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://datacite.org/schema/kernel-3 http://schema.datacite.org/meta/kernel-3/metadata.xsd"><identifier identifierType="DOI">'+doi+'</identifier><creators><creator> <creatorName></creatorName> </creator> </creators><titles> <title></title> </titles>';
                xml+='</resource>';
                return xml;
            },
            checkLinks: function(app_id) {
                return APIService.postlegacy(
                    apps_url+'mydois/runDoiLinkChecker', {
                        app_id:app_id
                    }
                );
            },
            mint: function (data) {
                return APIService.postlegacy(
                    api_url+'doi/mint.json/?manual=true&url='+data.url+'&app_id='+data.app_id, {
                        xml:data.xml,
                        doi_id:data.doi,
                        client_id:data.client_id
                    }
                );
            },
            update: function (data) {
                return APIService.postlegacy(
                    api_url+'doi/update.json/?manual=true&doi='+data.doi+'&url='+data.url+'&app_id='+data.app_id, {
                        xml:data.xml,
                        doi_id:data.doi,
                        client_id:data.client_id
                    }
                );
            },
            activate: function (data) {
                return APIService.postlegacy(
                    api_url+'doi/activate.json/?manual=true&app_id='+data.app_id+'&doi='+data.doi, {
                        doi_id:data.doi,
                        client_id:data.client_id
                    }
                );
            },
            deactivate: function (data) {
                return APIService.postlegacy(
                    api_url+'doi/deactivate.json/?manual=true&app_id='+data.app_id+'&doi='+data.doi, {
                        doi_update:data.doi,
                        client_id:data.client_id
                    }
                );
            }
        }


    }
})();