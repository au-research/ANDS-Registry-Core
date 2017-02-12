(function () {
    'use strict';

    angular
        .module('app')
        .controller('relatedCtrl', relatedCtrl);

    function relatedCtrl($scope, $modalInstance, $log, $timeout, entity, type, vocabs_factory, confluenceTip) {

        /* Define all the relationships, per type. The "types" value
           is an array of the types for which this relation is valid.
           For now, only "hasAssociationWith" has more than one supported
           type. */
        $scope.allRelatedEntityRelations = [
            {"value": "publishedBy", "text": "Publisher", "types": ["party"]},
            {"value": "hasAuthor", "text": "Author", "types": ["party"]},
            {"value": "hasContributor", "text": "Contributor", "types": ["party"]},
            {"value": "pointOfContact", "text": "Point of contact", "types": ["party"]},
            {"value": "implementedBy", "text": "Implementer", "types": ["party"]},
            {"value": "consumerOf", "text": "Consumer", "types": ["party"]},
            {"value": "hasAssociationWith", "text": "Associated with", "types": ["service", "vocabulary"]},
            {"value": "isPresentedBy", "text": "Presented by", "types": ["service"]},
            {"value": "isUsedBy", "text": "Used by", "types": ["service"]},
            {"value": "isDerivedFrom", "text": "Derived from", "types": ["vocabulary"]},
            {"value": "enriches", "text": "Enriches", "types": ["vocabulary"]},
            {"value": "isPartOf", "text": "Part of", "types": ["vocabulary"]}
        ];

        $scope.relatedEntityTypes = ['publisher', 'vocabulary', 'service'];
        $scope.entity = false;
        $scope.intent = 'add';
        $scope.type = type;
        $scope.confluenceTip = confluenceTip;

        if (entity) {
            $scope.entity = entity;
            $scope.intent = 'save';
        } else {
            // Add a placeholder for one relationship.
            $scope.entity = {'relationship':['']};
        }

        /* Please note behaviour here. Magic value for type
           "publisher": this sets up a relationship in which
           the type is in fact "party'. */
        if ($scope.type == "publisher") {
            $scope.type = 'party';
            $scope.entity = {
                'relationship': ['publishedBy']
            }
        }

        /* Filter the entity relations based on type. */
        $scope.getRelation = function() {
            return $scope.allRelatedEntityRelations.filter(
                function(rel) { return rel.types.indexOf($scope.type) != -1; }
            );
        }

        /* Set valid relations for this type. Note that this statement
           must occur _after_ the preceding "if" statement because
           of the treatment of type="publisher". */
        $scope.relatedEntityRelations = $scope.getRelation();

        $scope.form = {
            reForm:{}
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

        /** A list of the multi-valued elements that are the elements
            of $scope.vocab. Useful when iterating over all of these. */
        $scope.multi_valued_lists = [ 'relationship' ];
        // For future work:
        // $scope.multi_valued_lists = [ 'relationship', 'identifiers', 'urls' ];

        /**
         * Add an item to a multi-valued list.
         * @param name of list: for now, should be 'relationship'.
         */
        $scope.addtolist = function (list) {
            if (!$scope.entity[list]) $scope.entity[list] = [];

            var newValue;
            newValue = '';

            // Add new blank item to list.
            $scope.entity[list].push(newValue);
        };

        /**
         * Remove an item from a multi-valued list. The list
         * is left in good condition: specifically,
         * $scope.ensure_minimal_list is called after the item
         * is removed.
         * @param name of list: one of the values in $scope.multi_valued_lists,
         *   e.g., 'top_concept'.
         * @param index of the item to be removed.
         */
        $scope.list_remove = function (type, index) {
            if (index > 0) {
                $scope.entity[type].splice(index, 1);
            } else {
                $scope.entity[type].splice(0, 1);
            }
            $scope.ensure_minimal_list(type);
        };

        /** Ensure that a multi-value field has a minimal content, ready
            for editing. For some types, this could be an empty list;
            for others, a list with one (blank) element. */
        $scope.ensure_minimal_list = function (type) {
            if ($scope.entity[type].length == 0) {
                // Now an empty list. Do we put back a placeholder?
                switch (type) {
                case 'relationship':
                    $scope.entity[type] = [""];
                    break;
                default:
                }
            }
        }

        /** Ensure that all multi-value fields have minimal content, ready
            for editing. For some types, this could be an empty list;
            for others, a list with one (blank) element. */
        $scope.ensure_all_minimal_lists = function () {
            angular.forEach($scope.multi_valued_lists, function (type) {
                $scope.ensure_minimal_list(type);
            });
        }

        /** Utility function for validation of fields that can have
            multiple entries. The list is supposed to have at least one
            element that is a non-empty string. This method returns true
            if this is not the case. */
        $scope.array_has_no_nonempty_strings = function (list) {
            return list === undefined || list.filter(Boolean).length == 0;
        }

        /** Tidy up all empty fields. To be used before saving.
            Note that this does not guarantee validity.
         */
        $scope.tidy_empty = function() {
            $scope.entity.relationship = $scope.entity.relationship.filter(Boolean);
            // For future work:
            // $scope.entity.identifiers = $scope.entity.identifiers.filter(Boolean);
            // $scope.entity.urls = $scope.entity.urls.filter(Boolean);
        }

        $scope.save = function () {
            if ($scope.validateEntity()) {
                var ret = {
                    'intent': $scope.intent,
                    'data': $scope.entity
                };
                $modalInstance.close(ret);
            } else {
                // Put back the multi-value lists ready for more editing.
                $scope.ensure_all_minimal_lists();
                return false;
            }
        };

        $scope.validateEntity = function () {
            delete $scope.error_message;

            // Tidy up empty fields before validation.
            $scope.tidy_empty();

            if ($scope.form.reForm.$valid) {

                //at least 1 relationship
                if (!$scope.entity || !$scope.entity.relationship || $scope.entity.relationship.length == 0) {
                    $scope.error_message = 'At least 1 relationship is required';
                    return false
                }

                //at least 1 identifier, changed CC-1257, identifier no longer required
                // if (!$scope.entity || !$scope.entity.identifiers || $scope.entity.identifiers.length == 0) {
                //     $scope.error_message = 'At least 1 identifier is required';
                //     return false
                // }


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
