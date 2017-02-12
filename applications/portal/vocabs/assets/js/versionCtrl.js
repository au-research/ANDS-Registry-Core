(function () {
    'use strict';

    angular
        .module('app')
        .controller('versionCtrl', versionCtrl);

    function versionCtrl($scope, $timeout, $modalInstance, $log, $upload, version, action, vocab, confluenceTip) {
        $log.debug(action);
        $scope.versionStatuses = ['current', 'superseded'];
        $scope.vocab = vocab;
        $scope.confluenceTip = confluenceTip;
        // Preserve the original data for later. We need this
        // specifically for the release_date value.
        $scope.original_version = angular.copy(version);
        $scope.version = version ? version : {provider_type: false};
        $scope.action = version ? 'save' : 'add';
        $scope.formats = ['RDF/XML', 'TTL', 'N-Triples', 'JSON', 'TriG', 'TriX', 'N3', 'CSV', 'TSV', 'XLS', 'XLSX', 'BinaryRDF', 'ODS', 'ZIP', 'XML', 'TXT', 'ODT', 'PDF'];
        $scope.types = [{"value": "webPage", "text": "Web page"},
            {"value": "apiSparql", "text": "API/SPARQL endpoint"},
            {"value": "file", "text": "File"}
        ];

        $scope.form = {
            apForm:{},
            versionForm:{}
        }

        $scope.newValue = {
            ap: {format:''}
        };
        $scope.uploadPercentage = 0;

        //calendar operation
        $scope.opened = false;
        $scope.open = function ($event) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.opened = !$scope.opened;
        };

        // Now follows all the code for special treatment of the release date.
        // See also vocabs_cms.js, which has a modified version of all of
        // this for vocabulary creation dates.

        /* Flag to determine when to reset the content of the release date
           text field. Set by set_release_date_textfield() and reset by the
           watcher put on version.release_date. */
        $scope.restore_release_date_value = false;

        /* Special handling for the release date field. Needed because of the
           combination of the text field, the off-the-shelf datepicker,
           and the desire to allow partial dates (e.g., year only). */
        $scope.set_release_date_textfield = function (scope) {
            // In some browser JS engines, the Date constructor interprets
            // "2005" not as though it were "2005-01-01", but as 2005 seconds
            // into the Unix epoch. But Date.parse() seems to cope better,
            // so pass the date field through Date.parse() first. If that
            // succeeds, it can then go through the Date constructor.
            var dateValParsed = Date.parse($scope.original_version.release_date);
            if (!isNaN(dateValParsed)) {
                var dateVal = new Date(dateValParsed);
                $scope.version.release_date = dateVal;
                // Set this flag, so that the watcher on the
                // version.release_date
                // field knows to reset the text field to the value we got
                // from the database.
                $scope.restore_release_date_value = true;
            }
        };

        /* Callback function used by the watcher on version.release_date.
           It overrides the content of the release date text field with
           the value we got from the database. */
        $scope.do_restore_release_date = function() {
            $('#release_date').val($scope.original_version.release_date);
        }

        /* Watcher for the version.release_data field. If we got notification
           (via the restore_release_date_value flag) to reset the text
           field value, schedule the reset. Need to use $timeout
           so that the reset happens after the current round of
           AngularJS model value propagation. */
        $scope.$watch('version.release_date', function() {
            if ($scope.restore_release_date_value) {
                $scope.restore_release_date_value = false;
                $timeout($scope.do_restore_release_date, 0);
            }
        });

        // Now invoke the special handling for release date.
        $scope.set_release_date_textfield($scope);


        $scope.addformat = function (obj) {
            if ($scope.validateAP() || $scope.version.provider_type == 'poolparty') {
                if (!$scope.version) $scope.version = {};
                if (!$scope.version['access_points'] || $scope.version['access_points'] == undefined) {
                    $scope.version['access_points'] = [];
                }
                var newobj = {};
                angular.copy(obj, newobj);
                $scope.version.access_points.push(newobj);

                // Clear out existing values.
                // If new fields are added to the form, please
                // add appropriate delete/reset statements here.
                obj.type = '';
                obj.format = '';
                obj.uri = '';
            } else return false;
        };

        $scope.addformatform = function (obj) {
            $scope.addformat(obj);
            $log.debug(obj.import, obj.publish);
            if (obj.import) {
                //add empty apiSparql endpoint
                $scope.addformat({
                    format: 'RDF/XML',
                    type: 'apiSparql',
                    uri: 'TBD'
                });
            }
            if (obj.publish) {
                //add empty sissvoc endpoint
                $scope.addformat({
                    format: 'RDF/XML',
                    type: 'webPage',
                    uri: 'TBD'
                });
            }
        };

        $scope.validateAP = function () {
            delete $scope.ap_error_message;
            if (!$scope.form.apForm.$valid) {
                $scope.ap_error_message = 'Form Validation Failed';
            }
            return !!$scope.form.apForm.$valid;
        };

        $scope.validFormat = function () {
            var validFormats = ['TTL', 'TriG', 'Trix', 'N3', 'RDF/XML'];
            if ($scope.newValue.ap.format && $scope.newValue.ap.type == 'file') {
                if (validFormats.indexOf($scope.newValue.ap.format) > -1) {
                    return true;
                }
            }
            $scope.newValue.ap.publish = false;
            $scope.newValue.ap.import = false;
            return false;
        };

        $scope.validateVersion = function () {
            delete $scope.error_message;
            if ($scope.form.versionForm.$valid) {

                //if there's already a current version, this one shouldn't be
                if ($scope.version.status == 'current') {
                    if (vocab.versions) {
                        var vocabhascurrent = false;
                        angular.forEach(vocab.versions, function (ver) {
                            if (ver.status == 'current' && ver.id != $scope.version.id) vocabhascurrent = true;
                        });
                        if (vocabhascurrent) {
                            $scope.error_message = 'Vocabulary already has a current version';
                            return false;
                        }
                    }
                }

                //at least 1 access point require
                if ($scope.version && $scope.version.access_points && $scope.version.access_points.length > 0) {
                    return true;
                } else {
                    $scope.error_message = 'At least 1 access point is required';
                    return false;
                }

            } else {
                $scope.error_message = 'Form Validation Failed';
                return false;
            }
        };

        $scope.save = function () {
            // CC-1267 "Work in progress".
            // If the access point details are filled out
            // correctly, and the Access Point Button is
            // active, then click it on behalf of the user.
            if (!!$scope.form.apForm.$valid) {
                $scope.addformatform($scope.newValue.ap);
            }

            if ($scope.validateVersion()) {
                // Save the date as it actually is in the input's textarea, not
                // as it is in the model.
                $scope.version.release_date = $('#release_date').val();
                var ret = {
                    'intent': $scope.action,
                    'data': $scope.version
                };
                $modalInstance.close(ret);
            } else return false;
        };

        //Import version from PoolParty
        $scope.importPP = function () {
            $scope.version.provider_type = 'poolparty';

            //add empty apiSparql endpoint
            $scope.addformat({
                format: 'RDF/XML',
                type: 'apiSparql',
                uri: 'TBD'
            });

            //add empty sissvoc endpoint
            $scope.addformat({
                format: 'RDF/XML',
                type: 'webPage',
                uri: 'TBD'
            });
        };

        $scope.upload = function (files, ap) {
            if (!ap) ap = {};
            var allowContinue = false;
            if (files && files.length) {
                allowContinue = true;
                for (var i = 0; i < files.length; i++) {
                    var file = files[i];
                    if(file.size > 50000000){
                        alert("The file '" + file.name + "' size:(" + file.size + ") byte exceeds the limit (50MB) allowed and cannot be saved");
                        allowContinue = false;
                    }
                }
            }
            if (allowContinue) {
                for (var i = 0; i < files.length; i++) {
                    var file = files[i];
                    $scope.uploading = true;
                    delete $scope.error_upload_msg;
                    $upload.upload({
                        url: base_url + 'vocabs/upload',
                        file: file
                    }).progress(function (evt) {
                        var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                        $log.debug('progress: ' + progressPercentage + '% ' + evt.config.file.name);
                        $scope.uploadPercentage = progressPercentage;
                    }).success(function (data, status, headers, config) {
                        $log.debug(config);
                        $scope.uploading = false;
                        if (data.status == 'OK' && data.url) {
                            ap.uri = data.url;
                        } else if (data.status == 'ERROR') {
                            $scope.error_upload_msg = data.message;
                        }
                    });
                }
            }
        };

        $scope.list_remove = function (type, index) {
            if (index > 0) {
                $scope.version[type].splice(index, 1);
            } else {
                $scope.version[type].splice(0, 1);
            }
        };

        $scope.dismiss = function () {
            $modalInstance.dismiss();
        }

        $scope.$watch('newValue.ap.type', function(newVal, oldVal){

            if(newVal == 'file'){
                $('#ap_upload').show();
                $('#ap_uri').hide();
                $('#ap_uri_label').hide();
            }
            else if(newVal == 'apiSparql'){
                $('#ap_upload').hide();
                $('#ap_uri').show();
                $('#ap_uri_label').show();
                $('#ap_uri_label').html("SPARQL endpoint URI");
            }
            else if(newVal == 'webPage'){
                $('#ap_upload').hide();
                $('#ap_uri').show();
                $('#ap_uri_label').show()
                $('#ap_uri_label').html("Webpage URL");
            }
            else{
                $('#ap_upload').hide();
                $('#ap_uri').hide();
                $('#ap_uri_label').hide();
            }
        });
        $scope.setImPubcheckboxes = function (elem) {

            if(elem == 'import'){
                if(angular.isDefined($scope.newValue.ap.import)){
                    $scope.newValue.ap.import = !$scope.newValue.ap.import;
                } else {
                    $scope.newValue.ap.import = true;
                }
                if($scope.newValue.ap.import == false){
                    $scope.newValue.ap.publish = false;
                }
            }

            if(elem == 'publish'){
               if(angular.isDefined($scope.newValue.ap.publish)){
                    $scope.newValue.ap.publish = !$scope.newValue.ap.publish;
                } else{
                $scope.newValue.ap.publish = true;
                }
                if($scope.newValue.ap.publish == true){
                    $scope.newValue.ap.import = true;
                }
            }
        }
    }
})();
