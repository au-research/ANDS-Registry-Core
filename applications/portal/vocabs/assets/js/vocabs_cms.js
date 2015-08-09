/**
 * Primary Controller for the Vocabulary CMS
 * For adding / editing vocabulary metadata
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
(function () {
    'use strict';

    angular
        .module('app')
        .controller('addVocabsCtrl', addVocabsCtrl);

    Date.prototype.isValid = function () {
        // An invalid date object returns NaN for getTime() and NaN is the only
        // object not strictly equal to itself.
        return this.getTime() === this.getTime();
    };

    function addVocabsCtrl($log, $scope, $location, $modal, vocabs_factory) {

        $scope.form = {};

        $scope.vocab = {top_concept: [], subjects: []};
        /**
         * Collect all the user roles, for vocab.owner value
         */
        vocabs_factory.user().then(function (data) {
            $scope.user_orgs = data.message['affiliations'];
            $scope.user_owner = data.message['role_id'];
        });
        $scope.vocab.user_owner = $scope.user_owner;
        $scope.mode = 'add'; // [add|edit]
        $scope.langs = [
            {"value": "zh", "text": "Chinese"},
            {"value": "en", "text": "English"},
            {"value": "fr", "text": "French"},
            {"value": "de", "text": "German"},
            {"value": "it", "text": "Italian"},
            {"value": "ja", "text": "Japanese"},
            {"value": "mi", "text": "Māori"},
            {"value": "ru", "text": "Russian"},
            {"value": "es", "text": "Spanish"}
        ];
        $scope.licence = ["CC-BY", "CC-BY-SA", "CC-BY-ND", "CC-BY-NC", "CC-BY-NC-SA", "CC-BY-NC-ND", "ODC-By", "GPL", "AusGoalRestrictive", "NoLicence", "Unknown/Other",""];
        $scope.subject_sources = ['ANZSRC-FOR', 'local'];

        $scope.opened = false;
        $scope.decide = false;

        $scope.creation_date = '';
        $scope.creation_date_changed = false;

        $scope.status = 'idle';

        $scope.open = function ($event) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.opened = !$scope.opened;
        };

        /**
         * If there is a slug available, this is an edit view for the CMS
         * Proceed to overwrite the vocab object with the one fetched from the vocabs_factory.get()
         * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
         */
        if ($('#vocab_slug').val()) {
            vocabs_factory.get($('#vocab_id').val()).then(function (data) {
                $log.debug('Editing ', data.message);
                $scope.vocab = data.message;
                $scope.vocab.user_owner = $scope.user_owner;
                $scope.mode = 'edit';
                $scope.decide = true;
                $log.debug($scope.form.cms);
            });
        }

        if ($location.search().skip) {
            $scope.decide = true;
        } else if($location.search().message == 'saved_draft') {
            $scope.success_message = [];
            $scope.success_message.push('Successfully saved to a Draft.');
        }

        /**
         * Collect All PoolParty Project
         */
        $scope.projects = [];
        $scope.ppid = {};
        vocabs_factory.toolkit('listPoolPartyProjects').then(function (data) {
            $scope.projects = data;
        });



        $scope.projectSearch = function (q) {
            return function (item) {
                if (item.title.toLowerCase().indexOf(q.toLowerCase()) > -1 || item['id'].toLowerCase().indexOf(q.toLowerCase()) > -1) {
                    return true;
                } else return false;
            }
        };


        $scope.skip = function () {
            $scope.decide = true;
        };


        /**
         * Helper method for helping choosing between the dcterms
         * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
         * @return chosen      the chosen one/s
         * @param mess
         */
        $scope.choose = function (mess) {

            //the order we should look
            var order_trig = ['concepts.trig', 'adms.trig', 'void.trig'];
            var order_lang = ['value_en', 'value'];

            //find the one with the right trig, default to the first one if none was found
            var which = false;
            angular.forEach(order_trig, function (trig) {
                if (mess[trig] && !which) which = mess[trig];
            });
            if (!which) which = mess[0];
            // $log.debug(which);

            //find the right value for the right trig, default to the first one
            var chosen = false;
            angular.forEach(order_lang, function (lang) {
                if (which[lang] && !chosen) chosen = which[lang];
            });
            if (!chosen) chosen = which[0];
            // $log.debug(trig);

            return chosen;
        };

        /**
         * Populate the vocab with data
         * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
         * @param project
         * @param project
         */
        $scope.populate = function (project) {
            if (project) {

                //populate data from the PP API first
                $scope.vocab.pool_party_id = project.id;
                $scope.vocab.title = project.title;
                $scope.vocab.description = project.description;
                $scope.vocab.vocab_uri = project.uri;
                $scope.decide = true;
                if (project.availableLanguages) {
                    $scope.vocab.language = [];
                    angular.forEach(project.availableLanguages, function (lang) {
                        if (lang.toLowerCase() == 'en') lang = 'English';
                        $scope.vocab.language.push(lang);
                    });
                }
                if (project.subject) {
                    $scope.vocab.subjects = [];
                    $scope.vocab.subjects.push({subject: project.subject, subject_source: 'local'});
                }

                //populate with metadata from toolkit, overwrite the previous data where need be
                vocabs_factory.getMetadata($scope.vocab.pool_party_id).then(function (data) {
                    // $log.debug(data);
                    if (data) {

                        if (data['dcterms:title']) {
                            $scope.vocab.title = $scope.choose(data['dcterms:title']);
                            if (angular.isArray($scope.vocab.title)) $scope.vocab.title = $scope.vocab.title[0];
                        }

                        if (data['dcterms:description']) {
                            $scope.vocab.description = $scope.choose(data['dcterms:description']);
                            if (angular.isArray($scope.vocab.description)) $scope.vocab.description = $scope.vocab.description[0];
                        }

                        $log.debug($scope.vocab);

                        if (data['dcterms:subject']) {
                            //overwrite the previous ones
                            var chosen = $scope.choose(data['dcterms:subject']);

                            $scope.vocab.subjects = [];
                            angular.forEach(chosen, function (theone) {
                                $scope.vocab.subjects.push({subject: theone, subject_source: 'local'});
                            });
                        }

                        //related entity population
                        if (!$scope.vocab.related_entity) $scope.vocab.related_entity = [];

                        //Go through the list to determine the related entities to add
                        var rel_ent = [
                            {field: 'dcterms:publisher', relationship: 'publishedBy'},
                            {field: 'dcterms:contributor', relationship: 'hasContributor'},
                            {field: 'dcterms:creator', relationship: 'hasAuthor'}
                        ];
                        angular.forEach(rel_ent, function (rel) {
                            if (data[rel.field]) {
                                var chosen = $scope.choose(data[rel.field]);
                                var list = [];
                                if (angular.isString(chosen)) {
                                    list.push(chosen);
                                } else {
                                    angular.forEach(chosen, function (item) {
                                        list.push(item);
                                    });
                                }
                                angular.forEach(list, function (item) {

                                    //check if same item exist
                                    var exist = false;
                                    angular.forEach($scope.vocab.related_entity, function (entity) {
                                        if (entity.title == item) exist = entity;
                                    });

                                    if (exist) {
                                        exist.relationship.push(rel.relationship);
                                    } else {
                                        $scope.vocab.related_entity.push({
                                            title: item,
                                            type: 'party',
                                            relationship: [rel.relationship]
                                        });
                                    }

                                })
                            }
                        });

                    }
                });
            } else {
                console.log('no project to decide');
            }
        };

        /**
         * Saving a vocabulary
         * Based on the mode, add and edit will call different service point
         * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
         */
        $scope.save = function (status) {

            $scope.error_message = false;
            $scope.success_message = false;


            //validation
            if (!$scope.validate()) {
                return false;
            }

            if ($scope.mode == 'add' || ($scope.vocab.status == 'published' && status == 'draft')) {
                $scope.vocab.status = status;
                $scope.status = 'saving';
                $log.debug('Adding Vocab', $scope.vocab);
                vocabs_factory.add($scope.vocab).then(function (data) {
                    $scope.status = 'idle';
                    $log.debug('Data Response from saving vocab', data);
                    if (data.status == 'ERROR') {
                        $scope.error_message = data.message;
                    } else {//success
                        //navigate to the edit form if on the add form
                        if (status == 'published') {
                            window.location.replace(base_url + data.message.prop.slug);
                        }
                        else{
                        // $log.debug(data.message.prop[0].slug);
                            $scope.success_message = data.message.import_log;
                            $scope.success_message.push('Successfully saved to a Draft. <a href="' + base_url + "vocabs/edit/" + data.message.prop.id + '">Click Here edit the draft</a>');
                            window.location.replace(base_url + "vocabs/edit/" + data.message.prop.id+'/#!/?message=saved_draft');
                        }
                    }
                });
            } else if ($scope.mode == 'edit') {
                $scope.vocab.status = status;
                $scope.status = 'saving';
                $log.debug('Saving Vocab', $scope.vocab);
                vocabs_factory.modify($scope.vocab.id, $scope.vocab).then(function (data) {
                    $scope.status = 'idle';
                    $log.debug('Data Response from saving vocab (edit)', data);
                    if (data.status == 'ERROR') {
                        $scope.error_message = data.message;
                    } else {//success
                        $scope.success_message = data.message.import_log;
                        $scope.success_message = [
                            'Successfully saved Vocabulary.'
                        ];
                        if ($scope.vocab.status=='published') {
                            $scope.success_message.push(
                                '<a href="'+base_url+$scope.vocab.slug+'">View Vocabulary</a>'
                            )
                        }
                        if (status == 'draft') {
                            vocabs_factory.get($scope.vocab.id).then(function (data) {
                                $scope.vocab = data.message;
                            });
                        } else if(status == 'deprecated'){
                            window.location.replace(base_url + 'vocabs/myvocabs');
                        }
                        else{
                            window.location.replace(base_url + $scope.vocab.slug);
                        }
                    }
                });
            }
        };

        $scope.validate = function () {

            $log.debug($scope.form.cms);
            if ($scope.form.cms.$valid) {

                //language validation
                if (!$scope.vocab.language || $scope.vocab.language.length == 0) {
                    $scope.error_message = 'There must be at least 1 language';
                }

                //subject validation
                if (!$scope.vocab.subjects || $scope.vocab.subjects.length == 0) {
                    $scope.error_message = 'There must be at least 1 subject';
                }

                //publisher validation
                if (!$scope.vocab.related_entity) {
                    $scope.error_message = 'There must be at least 1 related entity that is a publisher';
                } else {
                    var hasPublisher = false;
                    angular.forEach($scope.vocab.related_entity, function (obj) {
                        if (obj.relationship) {
                            angular.forEach(obj.relationship, function (rel) {
                                if (rel == 'publishedBy') hasPublisher = true;
                            });
                        }
                    });
                    if (!hasPublisher) {
                        $scope.error_message = 'There must be a publisher related to this vocabulary';
                    }
                }

            }

            return $scope.error_message == false;
        };

        $scope.relatedmodal = function (action, type, obj) {
            var modalInstance = $modal.open({
                templateUrl: base_url + 'assets/vocabs/templates/relatedModal.html',
                controller: 'relatedCtrl',
                windowClass: 'modal-center',
                resolve: {
                    entity: function () {
                        if (action == 'edit') {
                            return obj;
                        } else {
                            return false;
                        }
                    },
                    type: function () {
                        return type;
                    }
                }
            });
            modalInstance.result.then(function (obj) {
                //close
                if (obj.intent == 'add') {
                    var newObj = obj.data;
                    newObj['type'] = type;
                    if (newObj['type'] == 'publisher') newObj['type'] = 'party';
                    if (!$scope.vocab.related_entity) $scope.vocab.related_entity = [];
                    $scope.vocab.related_entity.push(newObj);
                } else if (obj.intent == 'save') {
                    obj = obj.data;
                }
            }, function () {
                //dismiss
            });
        };

        $scope.versionmodal = function (action, obj) {
            var modalInstance = $modal.open({
                templateUrl: base_url + 'assets/vocabs/templates/versionModal.html',
                controller: 'versionCtrl',
                windowClass: 'modal-center',
                resolve: {
                    version: function () {
                        if (action == 'edit') {
                            return obj;
                        } else {
                            return false;
                        }
                    },
                    vocab: function () {
                        return $scope.vocab
                    },
                    action: function () {
                        return action;
                    }
                }
            });
            modalInstance.result.then(function (obj) {
                //close
                if (obj.intent == 'add') {
                    var newObj = obj.data;
                    if (!$scope.vocab.versions) $scope.vocab.versions = [];
                    $scope.vocab.versions.push(newObj);
                } else {
                    obj = obj.data;
                }
            }, function () {
                //dismiss
            });
        };

        /**
         * Add an item to an existing vocab
         * Primarily used for adding multivalued contents to the vocabulary
         * @param list
         * @param item enum
         * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
         */

        $scope.addtolist = function (list, item) {
            if (!$scope.vocab[list]) $scope.vocab[list] = [];

            //some validation
            if (list == 'language' && !item) return false;
            if (list == 'top_concept' && !item) return false;
            if (list == 'subjects' && !(item.subject && item.subject_source)) return false;

            //pass validation
            $scope.vocab[list].push(item);
            $scope.resetValues();
        };

        $scope.resetValues = function () {
            $scope.newValue = {
                language: "",
                subject: {subject: '', subject_source: ''}
            }
        };
        $scope.resetValues();

        $scope.list_remove = function (type, index) {
            if (index > 0) {
                $scope.vocab[type].splice(index, 1);
            } else {
                $scope.vocab[type].splice(0, 1);
            }
        }

    }
})();