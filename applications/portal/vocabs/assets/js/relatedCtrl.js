(function () {
    'use strict';

    angular
        .module('app')
        .controller('relatedCtrl', relatedCtrl);

    function relatedCtrl($scope, $modalInstance, $log, $timeout, entity, type, vocabs_factory) {
        $scope.relatedEntityRelations = [
            {"value": "publishedBy", "text": "Publisher"},
            {"value": "hasAuthor", "text": "Author"},
            {"value": "hasContributor", "text": "Contributor"},
            {"value": "pointOfContact", "text": "Point of contact"},
            {"value": "implementedBy", "text": "Implementer"},
            {"value": "consumerOf", "text": "Consumer"},
            {"value": "hasAssociationWith", "text": "Associated with"},
            {"value": "isPresentedBy", "text": "Presented by"},
            {"value": "isUsedBy", "text": "Used by"},
            {"value": "isDerivedFrom", "text": "Derived from"},
            {"value": "enriches", "text": "Enriches"},
            {"value": "isPartOf", "text": "Part of"}
        ]

        $scope.relatedEntityTypes = ['publisher', 'vocabulary', 'service'];
        $scope.entity = false;
        $scope.intent = 'add';
        if (entity) {
            $scope.entity = entity;
            $scope.intent = 'save';
        }
        $scope.type = type;
        $scope.newrel = '';

        $scope.form = {
            reForm:{}
        }

        if ($scope.type == 'publisher') {
            $scope.type = 'party';
            if (!$scope.entity) {
                $scope.entity = {
                    relationship: ['publishedBy']
                }
            }
        }

        $scope.onFocus = function (e) {
            $timeout(function () {
              $(e.target).trigger('input');
              $(e.target).trigger('change'); // for IE
            });
          };

        $scope.populate = function (item, model, label) {
            $log.debug(item,model,label);
            $scope.entity.email = item.email;
            $scope.entity.phone = item.phone;
            $scope.entity.id = item.id;

            if (!$scope.entity.urls || $scope.entity.urls.length == 0) $scope.entity.urls = item.urls;
            if (!$scope.entity.identifiers || $scope.entity.identifiers.length == 0) $scope.entity.identifiers = item.identifiers;
        };

        $scope.list_add = function (type, obj) {
            if (type == 'identifiers') {
                obj = {id: ''};
            } else if (type == 'urls') {
                obj = {url: ''};
            }
            if (!$scope.entity) $scope.entity = {};
            if (!$scope.entity[type]) $scope.entity[type] = [];
            if (obj) {
                $scope.entity[type].push(obj);
            }
        };

        $scope.getRelation = function() {
            if ($scope.type=='party') {
                return [
                    {"value": "publishedBy", "text": "Publisher"},
                    {"value": "hasAuthor", "text": "Author"},
                    {"value": "hasContributor", "text": "Contributor"},
                    {"value": "pointOfContact", "text": "Point of contact"},
                    {"value": "implementedBy", "text": "Implementer"},
                    {"value": "consumerOf", "text": "Consumer"}
                ];
            } else if ($scope.type=='service') {
                return [
                    {"value": "hasAssociationWith", "text": "Associated with"},
                    {"value": "isPresentedBy", "text": "Presented by"},
                    {"value": "isUsedBy", "text": "Used by"}
                ];
            } else if ($scope.type=='vocabulary') {
                return [
                    {"value": "hasAssociationWith", "text": "Associated with"},
                    {"value": "isDerivedFrom", "text": "Derived from"},
                    {"value": "enriches", "text": "Enriches"},
                    {"value": "isPartOf", "text": "Part of"}
                ];
            }
        }

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
            if ($scope.form.reForm.$valid) {

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