(function () {
    'use strict';

    angular
        .module('app')
        .controller('relatedCtrl', relatedCtrl);

    function relatedCtrl($scope, $modalInstance, $log, entity, type, vocabs_factory) {
        $scope.relatedEntityRelations = [
            {"value": "publishedBy", "text": "Publisher"},
            {"value": "hasAuthor", "text": "Author"},
            {"value": "hasContributor", "text": "Contributor"},
            {"value": "pointOfContact", "text": "Point of contact"},
            {"value": "implementedBy", "text": "Implementer"},
            {"value": "consumerOf", "text": "Consumer"}];

        $scope.relatedEntityTypes = ['publisher', 'vocabulary', 'service'];
        $scope.entity = false;
        $scope.intent = 'add';
        if (entity) {
            $scope.entity = entity;
            $scope.intent = 'save';
        }
        $scope.type = type;

        if ($scope.type == 'publisher') {
            $scope.type = 'party';
            if (!$scope.entity) {
                $scope.entity = {
                    relationship: ['publishedBy']
                }
            }
        }

        $scope.populate = function (item, model, label) {
            $log.debug(item,model,label);
            $scope.entity.email = item.email;
            $scope.entity.phone = item.phone;
            $scope.entity.id = item.id;

            if (!$scope.entity.urls || $scope.entity.urls.length == 0) $scope.entity.urls = item.urls;
            if (!$scope.entity.identifiers || $scope.entity.identifiers.length == 0) $scope.entity.identifiers = item.identifiers;
        };

        $scope.list_add = function (type, obj) {
            if (!obj) obj = {};
            if (type == 'identifiers') {
                obj = {id: ''};
            } else if (type == 'url') {
                obj = {url: ''};
            }
            if (!$scope.entity) $scope.entity = {};
            if (!$scope.entity[type]) $scope.entity[type] = [];
            $scope.entity[type].push(obj);
        };

        $scope.list_remove = function (type, index) {
            if (index > 0) {
                $scope.entity[type].splice(index, 1);
            } else {
                $scope.entity[type].splice(0, 1);
            }
        };

        $scope.save = function () {
            if ($scope.validateEntity()) {
                var ret = {
                    'intent': $scope.intent,
                    'data': $scope.entity
                };
                $modalInstance.close(ret);
            } else return false;
        };

        $scope.validateEntity = function () {
            delete $scope.error_message;
            if ($scope.reForm.$valid) {

                //at least 1 relationship
                if (!$scope.entity || !$scope.entity.relationship || $scope.entity.relationship.length == 0) {
                    $scope.error_message = 'At least 1 relationship is required';
                    return false
                }

                //at least 1 identifier
                if (!$scope.entity || !$scope.entity.identifiers || $scope.entity.identifiers.length == 0) {
                    $scope.error_message = 'At least 1 identifier is required';
                    return false
                }


                return true;
            } else {
                $scope.error_message = 'Form Validation Failed';
                return false;
            }
        };

        $scope.dismiss = function () {
            $modalInstance.dismiss();
        };

        vocabs_factory.suggest(type).then(function (data) {
            if (data.status == 'OK') {
                $scope.suggestions = data.message;
            }
        });

    }
})();