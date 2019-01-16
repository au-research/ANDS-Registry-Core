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

    function addVocabsCtrl($log, $scope, $sce, $timeout,
                           $location, $modal, vocabs_factory) {

        $scope.form = {};

        // Initialize sections that can have multiple instances.
        // Note the distinction between sections which are optional,
        // and those for which there must be at least one instance.
        $scope.vocab = {
            top_concept: [],
            subjects: [
                {
                    subject_source: "anzsrc-for",
                    subject_label: "",
                    subject_iri: "",
                    subject_notation: ""
                }
            ],
            language: [""]
        };
        /**
         * Collect all the user roles, for vocab.owner value
         */
        vocabs_factory.user().then(function (data) {
            $scope.user_orgs = data.message['affiliations'];
            $scope.user_orgs_names = [];
            for (var i=0; i<data.message['affiliations'].length; ++i)
            {
                // Use the affiliation as the 'id', and then use the affiliation
                // to look up the full name, and use that as the 'name'.
                $scope.user_orgs_names.push({'id':data.message['affiliations'][i],'name': data.message['affiliationsNames'][ data.message['affiliations'][i] ]});
            }
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
        $scope.licence = ["CC-BY", "CC-BY-SA", "CC-BY-ND",
                          "CC-BY-NC", "CC-BY-NC-SA", "CC-BY-NC-ND",
                          "ODC-By", "GPL", "AusGoalRestrictive",
                          "NoLicence", "Unknown/Other"];

        // Initialize subject sources
        $scope.subject_sources = [];
        // $scope.subject_sources will be an array of objects; one
        // for each vocabulary in the subject source dropdown.
        // Each object has keys "id", "label", and "mode".
        // For id other than "local", there will also be
        // keys "resolvingService" and "uriprefix".
        // The data comes from the config/vocab.php's
        // 'vocab_resolving_services' setting.
        // For legacy reasons, the resolvingService setting
        // typically ends with a slash, but the vocab widget
        // requires a repository setting _without_ a trailing
        // slash. So we remove such during initialization.
        // E.g., {"id":"anzsrc-for",
        //        "label":"ANZSRC Field of Research",
        //        "mode":"tree",
        //        "resolvingService":"http://...",
        //        "uriprefix":"http://purl.org/.../"
        //       }
        // See if there are vocab resolving services.
        if (typeof vocab_resolving_services !== 'object') {
            alert('Unable to populate subject source dropdown');
            return;
        }
        // Vocab resolving services are available.
        for (var v in vocab_resolving_services) {
            var vo = {id: v};
            $.each(vocab_resolving_services[v],
                   function (key, value) {
                       if (key === 'resolvingService') {
                           // Special treatment. Remove any
                           // trailing slash(es).
                           vo[key] = value.replace(/\/$/, "");
                       } else {
                           vo[key] = value;
                       }
                   });
            $scope.subject_sources.push(vo);
        }

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
         * Proceed to overwrite the vocab object with the one fetched
         * from the vocabs_factory.get()
         * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
         */
        if ($('#vocab_slug').val()) {
            vocabs_factory.get($('#vocab_id').val()).then(function (data) {
                $log.debug('Editing ', data.message);
                // Preserve the original data for later. We need this
                // specifically for the creation_date value.
                $scope.original_data = data.message;
                // Make a deep copy. This used to be
                //    $scope.vocab = data.message;
                // But that is a copy by reference ... subsequent changes
                // to $scope.vocab affect data.message too, making
                // it impossible to refer to the original values.
                $scope.vocab = angular.copy(data.message);
                $scope.vocab.user_owner = $scope.user_owner;
                $scope.mode = 'edit';
                $scope.decide = true;
                // Special handling for creation date.
                $scope.set_creation_date_textfield($scope);
                $log.debug($scope.form.cms);
            });
        }

        // Now follows all the code for special treatment of the creation date.
        // See also versionCtrl.js, which has a modified version of all of
        // this for version release dates.

        /* Flag to determine when to reset the content of the creation date
           text field. Set by set_creation_date_textfield() and reset by the
           watcher put on vocab.creation_date. */
        $scope.restore_creation_date_value = false;

        /* Special handling for the creation date field. Needed because of the
           combination of the text field, the off-the-shelf datepicker,
           and the desire to allow partial dates (e.g., year only). */
        $scope.set_creation_date_textfield = function (scope) {
            // In some browser JS engines, the Date constructor interprets
            // "2005" not as though it were "2005-01-01", but as 2005 seconds
            // into the Unix epoch. But Date.parse() seems to cope better,
            // so pass the date field through Date.parse() first. If that
            // succeeds, it can then go through the Date constructor.
            var dateValParsed = Date.parse($scope.original_data.creation_date);
            if (!isNaN(dateValParsed)) {
                var dateVal = new Date(dateValParsed);
                $scope.vocab.creation_date = dateVal;
                // Set this flag, so that the watcher on the vocab.creation_date
                // field knows to reset the text field to the value we got
                // from the database.
                $scope.restore_creation_date_value = true;
            }
        };

        /* Callback function used by the watcher on vocab.creation_date.
           It overrides the content of the creation date text field with
           the value we got from the database. */
        $scope.do_restore_creation_date = function() {
            $('#creation_date').val($scope.original_data.creation_date);
        }

        /* Watcher for the vocab.creation_data field. If we got notification
           (via the restore_creation_date_value flag) to reset the text
           field value, schedule the reset. Need to use $timeout
           so that the reset happens after the current round of
           AngularJS model value propagation. */
        $scope.$watch('vocab.creation_date', function() {
            if ($scope.restore_creation_date_value) {
                $scope.restore_creation_date_value = false;
                $timeout($scope.do_restore_creation_date, 0);
            }
        });


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
            // CC-1799: now need to support both TriG and Turtle.
            // Future work: just strip the file suffixes, in
            // both Toolkit GetMetadataTransformProvider (i.e.,
            // don't put it into the generated JSON), and then here.
            var order_trig = ['concepts.ttl', 'concepts.trig',
                              'adms.ttl', 'adms.trig',
                              'void.ttl', 'void.trig'];
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
                //if selection was made
                //otherwise assume the pooplParty ID is still in the field unprocessed!!!
                if(typeof project.id != 'undefined'){
                    $scope.vocab.pool_party_id = project.id;
                } else {
                    $scope.vocab.pool_party_id = project;
                }

                $scope.decide = true;
                //populate with metadata from toolkit, overwrite the previous data where need be
                vocabs_factory.getMetadata($scope.vocab.pool_party_id).then(function (data) {
                    if (data) {

                        // CC-1447. Provide some feedback, if the Toolkit
                        // returned with either an error or an exception.
                        // This can happen, e.g., if the PP project does
                        // not exist, or if the RDF data is invalid (and
                        // therefore can not be parsed to extract metadata).
                        if (("error" in data) || ("exception" in data)) {
                            alert("Unable to get project metadata from PoolParty. Fields will not be pre-filled.");
                            return;
                        }

                        if (data['dcterms:title']) {
                            $scope.vocab.title = $scope.choose(data['dcterms:title']);
                            if (angular.isArray($scope.vocab.title)) $scope.vocab.title = $scope.vocab.title[0];
                        }

                        if (data['dcterms:description']) {
                            $scope.vocab.description = $scope.choose(data['dcterms:description']);
                            if (angular.isArray($scope.vocab.description)) $scope.vocab.description = $scope.vocab.description[0];
                        }

                        if (data['dcterms:subject']) {
                            //overwrite the previous ones
                            var chosen = $scope.choose(data['dcterms:subject']);

                            $scope.vocab.subjects = [];
                            angular.forEach(chosen, function (theone) {
                                $scope.vocab.subjects.push(
                                    {subject_source: 'local',
                                     subject_label: theone,
                                     subject_iri: '',
                                     subject_notation: ''
                                     });
                            });
                        }
                        if (data['dcterms:language']) {
                            var chosen = $scope.choose(data['dcterms:language']);
                            $scope.vocab.language = [];
                            angular.forEach(chosen, function (lang) {
                                $scope.vocab.language.push(lang);
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
         * Get any alert text to be displayed, after save/publish.
         * Alert text is found by going through data.message.import_log,
         * looking for values that begin with the string 'Alert: '.
         * All such values are concatenated, separated by 'br' tags.
         * @param data Data returned from the save/publish service.
         * @return The alert message to be displayed.
         */
        $scope.get_alert_text_after_save = function (data) {
            var alert_message = '';
            if ((typeof data == 'object')
                && (typeof data.message == 'object')
                && (typeof data.message.import_log == 'object')
                && (data.message.import_log.length > 0)) {
                alert_message = data.message.import_log.reduce(
                    function (previousValue, currentValue,
                              currentIndex, array) {
                        if (currentValue.startsWith('Alert: ')) {
                            if (previousValue != '') {
                                previousValue += '<br />';
                            }
                            return previousValue + currentValue;
                        }
                        return previousValue;
                    }, '');
            }
            return alert_message;
        };

        /**
         * Show any alert, after save/publish. When the alert
         * is hidden (by the user closing it), invoke the
         * hide_callback function.
         * If no alert is to be shown, invoke the hide_callback
         * function immediately.
         * @param data Data returned from save/publish service.
         * @param hide_callback Callback function to be invoked
         *     when the alert is hidden.
         */
        $scope.show_alert_after_save = function (data, hide_callback) {
            var alert_message = $scope.get_alert_text_after_save(data);
            if (alert_message != '') {
                $('body').qtip({
                    content: {
                        text: alert_message,
                        title: 'Alert',
                        button: 'Close'
                    },
                    style: {
                        classes: 'qtip-bootstrap cms-help-tip'
                    },
                    position: {
                        my: 'center',
                        at: 'center',
                        target: $(window)
                    },
                    show: {
                        modal: true,
                        when : false
                    },
                    hide: {
                        // Overrides the default of 'mouseleave'.
                        // Otherwise, clicking a link in the
                        // alert text that has target="_blank"
                        // opens a new tab/window, but also
                        // closes the modal. With this setting,
                        // the user can go back to the original
                        // tab/window and still see the modal.
                        event: ''
                    },
                    events: {
                        hide: hide_callback
                    }
                });
                $('body').qtip('show');
            } else {
                hide_callback();
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
            if(status == 'discard'){
                window.location.replace(base_url + 'vocabs/myvocabs');
                return false;
            }
            // Tidy up empty fields before validation.
            $scope.tidy_empty();

            // Validation.
            // First, rely on Angular's error handling.
            if ($scope.form.cms.$invalid) {
                // Put back the multi-value lists ready for more editing.
                $scope.ensure_all_minimal_lists();
                return false;
            }
            // Then, do our own validation.
            if (!$scope.validate()) {
                // Put back the multi-value lists ready for more editing.
                $scope.ensure_all_minimal_lists();
                return false;
            }

            // Save the date as it actually is in the input's textarea, not
            // as it is in the model.
            $scope.vocab.creation_date = $('#creation_date').val();

            if ($scope.mode == 'add' ||
                ($scope.vocab.status == 'published' && status == 'draft')) {
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
                            $scope.show_alert_after_save(data,
                                function() {
                                    window.location.replace(base_url +
                                        data.message.prop.slug);
                                });
                        }
                        else{
                        // $log.debug(data.message.prop[0].slug);
                            $scope.success_message = data.message.import_log;
                            $scope.success_message.push('Successfully saved to a Draft. <a href="' + base_url + "vocabs/edit/" + data.message.prop.id + '">Click Here edit the draft</a>');
                            $scope.show_alert_after_save(data,
                                function() {
                                    window.location.replace(base_url +
                                        "vocabs/edit/" +
                                        data.message.prop.id +
                                        '/#!/?message=saved_draft');
                                });
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
                            $scope.show_alert_after_save(data, function() {
                                window.location.replace(base_url +
                                                        'vocabs/myvocabs');
                            });
                        }
                        else{
                            $scope.show_alert_after_save(data, function() {
                                window.location.replace(base_url +
                                                        $scope.vocab.slug);
                            });
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
                    $scope.error_message = 'There must be at least one language';
                }

                //subject validation
                if (!$scope.vocab.subjects || $scope.vocab.subjects.length == 0 || $scope.subjects_has_no_complete_anzsrc_for_elements()) {
                    $scope.error_message = 'There must be at least one subject drawn from the "ANZSRC Field of Research" vocabulary';
                }
                if ($scope.subjects_has_an_only_partially_valid_element()) {
                    $scope.error_message = 'There is a partially-completed subject. Either complete it or remove it.';
                }

                //publisher validation
                if (!$scope.vocab.related_entity) {
                    $scope.error_message = 'There must be at least one related entity that is a publisher';
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

        /**
         * Filter for finding publishers. Used for instant validation.
         */
        $scope.getPublishers = function (relEntity) {
            var hasPublisher = false;
            if (relEntity.relationship) {
                angular.forEach(relEntity.relationship, function (rel) {
                    if (rel == 'publishedBy') hasPublisher = true;
                });
            }
            return hasPublisher;
        };

        /** Create a tooltip that points to Confluence documentation.
            Because of the use of a confluence_tip attribute, which
            would otherwise be stripped out during processing of
            ng-bind-html, we need to use $sce.trustAsHtml.
            confluenceTip() is used not only on the main CMS page, but it is also
            injected (using resolve) into the relatedmodal and versionmodal
            modal dialogs.
            Unfortunately, the name of the page (which determines the
            beginning of all the anchor names) is hard-coded here. :-(
            Is there a better way?
        */
        $scope.confluenceTip = function (anchor) {
            return $sce.trustAsHtml('<span confluence_tip="' +
              'PopulatingRVAPortalMetadataFields(OptimisedforRVATooltips)-' +
              anchor + '"><span class="fa fa-info-circle" ' +
              'style="color: #17649a; font-size: 13px"></span></span>');
        };

        // CC-1518 Need the related entity index, because we send a
        // copy of the related entity to the modal, and then need to
        // copy it back into the correct place after a Save.
        $scope.relatedmodal = function (action, type, index) {
            var modalInstance = $modal.open({
                templateUrl: base_url + 'assets/vocabs/templates/relatedModal.html',
                controller: 'relatedCtrl',
                windowClass: 'modal-center',
                resolve: {
                    entity: function () {
                        if (action == 'edit') {
                            // CC-1518 Operate on a copy of the related entity.
                            return angular.copy($scope.vocab.related_entity[index]);
                        } else {
                            return false;
                        }
                    },
                    type: function () {
                        return type;
                    },
                    confluenceTip: function () {
                        return $scope.confluenceTip;
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
                    // CC-1518 Copy the modified related entity back into place.
                    $scope.vocab.related_entity[index] = obj.data;
                }
            }, function () {
                //dismiss
            });
        };

        // CC-1518 Need the version index, because we send a copy of the version
        // to the modal, and then need to copy it back into the correct place
        // after a Save.
        $scope.versionmodal = function (action, index) {
            var modalInstance = $modal.open({
                templateUrl: base_url + 'assets/vocabs/templates/versionModal.html',
                controller: 'versionCtrl',
                windowClass: 'modal-center',
                resolve: {
                    version: function () {
                        if (action == 'edit') {
                            // CC-1518 Operate on a copy of the version.
                            return angular.copy($scope.vocab.versions[index]);
                        } else {
                            return false;
                        }
                    },
                    vocab: function () {
                        return $scope.vocab
                    },
                    action: function () {
                        return action;
                    },
                    confluenceTip: function () {
                        return $scope.confluenceTip;
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
                    // CC-1518 Copy the modified version back into place.
                    $scope.vocab.versions[index] = obj.data;
                }
            }, function () {
                //dismiss
            });
        };

        /** A list of the multi-valued elements that are the elements
            of $scope.vocab. Useful when iterating over all of these. */
        $scope.multi_valued_lists = [ 'language', 'subjects', 'top_concept' ];

        /**
         * Add an item to an existing vocab
         * Primarily used for adding multivalued contents to the vocabulary
         * @param name of list: one of the values in $scope.multi_valued_lists,
         *   e.g., 'top_concept'.
         */
        $scope.addtolist = function (list) {
            if (!$scope.vocab[list]) $scope.vocab[list] = [];

            var newValue;
            // 'subjects' has two parts; special treatment.
            if (list == 'subjects') {
                newValue = {subject_source: '',
                            subject_label: '',
                            subject_iri: '',
                            subject_notation: ''};
            } else {
                // Otherwise ('language' and 'top_concept') ...
                newValue = '';
            }

            // Add new blank item to list.
            $scope.vocab[list].push(newValue);

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
                $scope.vocab[type].splice(index, 1);
            } else {
                $scope.vocab[type].splice(0, 1);
            }
            $scope.ensure_minimal_list(type);
        }

        /** Ensure that a multi-value field has a minimal content, ready
            for editing. For some types, this could be an empty list;
            for others, a list with one (blank) element. */
        $scope.ensure_minimal_list = function (type) {
            if ($scope.vocab[type].length == 0) {
                // Now an empty list. Do we put back a placeholder?
                switch (type) {
                case 'language':
                    $scope.vocab[type] = [""];
                    break;
                case 'subjects':
                    $scope.vocab[type] = [{
                        subject_source: "anzsrc-for",
                        subject_label: "",
                        subject_iri: "",
                        subject_notation: ""
                    }];
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

        /** Tidy up all empty fields. To be used before saving.
            Note that this does not guarantee validity.
            To be specific, this does not remove subjects that are
            only partially valid.
         */
        $scope.tidy_empty = function() {
            $scope.vocab.top_concept = $scope.vocab.top_concept.filter(Boolean);
            $scope.vocab.language = $scope.vocab.language.filter(Boolean);
            $scope.vocab.subjects = $scope.vocab.subjects.filter($scope.partially_valid_subject_filter);
        }

        /** Utility function for validation of fields that can have
            multiple entries. The list is supposed to have at least one
            element that is a non-empty string. This method returns true
            if this is not the case. */
        $scope.array_has_no_nonempty_strings = function (list) {
            return list === undefined || list.filter(Boolean).length == 0;
        }

        /** Utility function for testing if a value is a non-empty
            string. It is careful not to fail on non-string values. */
        $scope.is_non_empty_string = function(str) {
            return (typeof str != "undefined") &&
                (str != null) &&
                (typeof str.valueOf() == "string") &&
                (str.length > 0);
        }

        /** Filter function for one subject object. Returns true
            if the subject is valid, i.e., contains both a non-empty
            source and a non-empty subject label. */
        $scope.valid_subject_filter = function(el) {
            return ('subject_source' in el) &&
                ($scope.is_non_empty_string(el.subject_source)) &&
                ('subject_label' in el) &&
                ($scope.is_non_empty_string(el.subject_label));
        }

        /** Filter function for one subject object. Returns true
            if the subject has both source = 'anzsrc-for' and a
            non-empty subject label. */
        $scope.valid_anzsrc_for_subject_filter = function(el) {
            return ('subject_source' in el) &&
                (el.subject_source == 'anzsrc-for') &&
                ('subject_label' in el) &&
                ($scope.is_non_empty_string(el.subject_label));
        }

        /** Filter function for one subject object. Returns true
            if the subject has at least one part valid,
            i.e., contains either a non-empty
            source or a non-empty subject. */
        $scope.partially_valid_subject_filter = function(el) {
            return (('subject_source' in el) &&
                    ($scope.is_non_empty_string(el.subject_source))) ||
                (('subject_label' in el) &&
                 ($scope.is_non_empty_string(el.subject_label)));
        }

        /** Filter function for one subject object. Returns true
            if the subject has exactly one part valid,
            i.e., contains either a non-empty
            source or a non-empty subject label,
            but not both. */
        $scope.only_partially_valid_subject_filter = function(el) {
            return (('subject_source' in el) &&
                    ($scope.is_non_empty_string(el.subject_source))) !=
                (('subject_label' in el) &&
                 ($scope.is_non_empty_string(el.subject_label)));
        }

        /** Utility function for validation of subjects. In order
            to help the user not lose a partially-complete subject,
            call this function to check if the user has a subject for
            which there is only a source or a subject label, but not both. */
        $scope.subjects_has_an_only_partially_valid_element = function () {
            return $scope.vocab.subjects.filter(
                $scope.only_partially_valid_subject_filter).length > 0;
        }

        /** Utility function for validation of subjects. The list
            of subjects is supposed to have at least one
            element that has both a non-empty source and a non-empty
            subject label. This method returns true
            if this is not the case. */
        $scope.subjects_has_no_complete_anzsrc_for_elements = function () {
            return $scope.vocab.subjects == undefined ||
                $scope.vocab.subjects.filter($scope.valid_subject_filter) == 0;
        }

        /** Utility function for validation of subjects. This function
            implements a new business rule (CC-1623) that requires that each
            vocabulary have at least one subject drawn from
            ANZSRC-FOR.  So, the list of subjects is supposed to have at
            least one element that has both source = 'anzsrc-for' and
            a non-empty subject label. This method returns true if
            this is not the case. */
        $scope.subjects_has_no_complete_anzsrc_for_elements = function () {
            return $scope.vocab.subjects == undefined ||
                $scope.vocab.subjects.filter(
                    $scope.valid_anzsrc_for_subject_filter) == 0;
        }

    }

    /* Load help document containing the tooltips.
       Note the URL: configure the web server to
       proxy the pages.  Here is a suitable rva_doc.conf to go
       into /etc/httpd/conf.d:

SSLProxyEngine on

# Use /ands_doc/tooltips in the code as the location of the RVA portal tooltips.
# Then rewrite it here to point to the appropriate Confluence page.
# Doing it here means you don't have to change the code if the original
# page moves.

RewriteRule ^/ands_doc/tooltips$  /ands_doc/pages/viewpage.action?pageId=22478849  [PT]

# Need to proxy the Confluence pages so as to be able to get images too.
<Location /ands_doc>

  ProxyPass https://documentation.ands.org.au/
  ProxyPassReverse https://documentation.ands.org.au/

  # Confluence adds this header. Once you view such a page in Firefox,
  # it "infects" access to _this_ server, preventing non-SSL access via
  # browser to all ports (e.g., including 8080).
  Header unset Strict-Transport-Security
</Location>

    */
    $(document).ready(function() {
        $.get("/ands_doc/tooltips", function (data) {
            var data_replaced = data.replace(/src="/gi, 'src="/ands_doc');
            var html = $(data_replaced).find('#content-column-0');
            $('#all_help').html(html);
            // Make all external links in these tooltips open a new
            // tab/window. Courtesy of:
            // https://confluence.atlassian.com/display/CONFKB/How+to+force+links+to+open+in+a+new+window
            $('#all_help').find(".external-link").attr("target", "_blank");
        });
    });

    // Directive based on:
    // http://stackoverflow.com/questions/26278711/using-the-enter-key-as-tab-using-only-angularjs-and-jqlite

    angular.module('app').directive('topConceptsEnter', function () {
        return function (scope, element, attrs) {
            element.bind("keydown keypress", function (event) {
                if (event.which === 13) {
                    // User pressed Enter. Inhibit default behaviour.
                    event.preventDefault();
                    var elementToFocus = element.next().find('input')[0];
                    if (angular.isDefined(elementToFocus)) {
                        elementToFocus.focus();
                    } else {
                        // Add a new row to the model. We are not in the Angular
                        // execution cycle at this point, so we need $apply
                        // so that the change is propagated to the DOM.
                        scope.$apply(function() { scope.addtolist('top_concept'); });
                        // We should now have a new element. Move the focus to it.
                        var newelementToFocus = element.next('tr').find('input')[0];
                        if (angular.isDefined(newelementToFocus))
                            newelementToFocus.focus();
                    }
                }
            });
        };
    });




})();
