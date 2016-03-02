/**
 * Vocabulary ANGULARJS Factory
 * A component that deals with the vocabulary service point directly
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
(function () {
    'use strict';

    angular
        .module('app')
        .factory('vocabs_factory', function ($http) {
            return {
                getAll: function () {
                    return $http.get(base_url + 'vocabs/services/vocabs').then(function (response) {
                        return response.data;
                    });
                },
                getAllWidgetable: function () {
                    var filters = {
                        widgetable: true,
                        pp: 31
                    }
                    return this.search(filters);
                },
                add: function (data) {
                    return $http.post(base_url + 'vocabs/services/vocabs', {data: data}).then(function (response) {
                        return response.data;
                    });
                },
                get: function (slug) {
                    return $http.get(base_url + 'vocabs/services/vocabs/' + slug).then(function (response) {
                        return response.data;
                    });
                },
                modify: function (slug, data) {
                    return $http.post(base_url + 'vocabs/services/vocabs/' + slug, {data: data}).then(function (response) {
                        return response.data;
                    });
                },
                search: function (filters) {
                    return $http.post(base_url + 'vocabs/filter', {filters: filters}).then(function (response) {
                        return response.data;
                    });
                },
                toolkit: function (req) {
                    return $http.get(base_url + 'vocabs/toolkit?request=' + req).then(function (response) {
                        return response.data;
                    });
                },
                getMetadata: function (id) {
                    return $http.get(base_url + 'vocabs/toolkit?request=getMetadata&ppid=' + id).then(function (response) {
                        return response.data;
                    });
                },
                suggest: function (type) {
                    return $http.get(base_url + 'vocabs/services/vocabs/all/related?type=' + type).then(function (response) {
                        return response.data;
                    });
                },
                user: function () {
                    return $http.get(base_url + 'vocabs/services/vocabs/all/user').then(function (response) {
                        return response.data;
                    });
                }
            }
        });
})();