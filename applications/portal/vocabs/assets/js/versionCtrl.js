(function () {
    'use strict';

    angular
        .module('app')
        .controller('versionCtrl', versionCtrl);

    function versionCtrl($scope, $modalInstance, $log, $upload, version, action, vocab) {
        $log.debug(action);
        $scope.versionStatuses = ['current', 'superseded', 'deprecated'];
        $scope.vocab = vocab;
        $scope.version = version ? version : {provider_type: false};
        $scope.action = version ? 'save' : 'add';
        $scope.formats = ['RDF/XML', 'TTL', 'N-Triples', 'JSON', 'TriG', 'TriX', 'N3', 'CSV', 'TSV', 'XLS', 'XLSX', 'BinaryRDF', 'ODS', 'ZIP', 'XML', 'TXT', 'ODT', 'TEXT'];
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

        $scope.addformat = function (obj) {
            if ($scope.validateAP() || $scope.version.provider_type == 'poolparty') {
                if (!$scope.version) $scope.version = {};
                if (!$scope.version['access_points'] || $scope.version['access_points'] == undefined) {
                    $scope.version['access_points'] = [];
                }
                var newobj = {};
                angular.copy(obj, newobj);
                $scope.version.access_points.push(newobj);

                $scope.newap = {};
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
            if ($scope.validateVersion()) {
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

            //add empty file
            $scope.addformat({
                format: 'RDF/XML',
                type: 'file',
                uri: 'TBD'
            });

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