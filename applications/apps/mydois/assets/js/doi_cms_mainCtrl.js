(function(){
    'use strict';

    angular
        .module('doi_cms_app')
        .controller('mainCtrl', mainCtrl)
    ;

    function mainCtrl(APIDOIService, client, $scope, $location, $log, $sce) {
        var vm = this;
        vm.tab = "list";
        $scope.base_url = apps_url;
        vm.newdoixml = "";
        vm.pp = 50;

        var app_id = "";
        app_id = $location.search().app_id;
        if (!app_id) app_id = $location.search().app_id_select;

        if ($location.search().tab) vm.tab = $location.search().tab;

        vm.client = client.data.client;

        $scope.$watch('vm.tab', function(newv){
            vm.changeTab(newv);
        });

        vm.changeTab = function(tab) {
            switch (tab) {
                case 'list':
                    vm.refreshDOIs();
                    break;
                case 'log':
                    APIDOIService.getLog(vm.client.app_id).then(function(data){
                        vm.logs = data.data.activities;
                    });
                    break;
                case 'mint':
                    if(vm.client.mode=="test"){vm.client.datacite_prefix="10.5072";}
                    if(vm.client.datacite_prefix=="10.5072"){
                        var test_str = "TEST_DOI_";
                    }else{
                        var test_str = "";
                    }
                    vm.editxml = false;
                    vm.response = false;
                    vm.newdoi_url = '';
                    vm.newdoi_id = vm.client.datacite_prefix +'/'+ test_str + vm.uniqid();
                    vm.newdoixml = APIDOIService.getBlankDataciteXML(vm.newdoi_id);
                    break;
            }
        }

        vm.refreshDOIs = function(search) {
            if (!search) search = false;
                APIDOIService.getDOIList(vm.client.app_id, vm.pp, 0, search, vm.client.mode).then(function(data){
                vm.dois = data.data.dois;
                vm.total = data.data.total;
                vm.offset = vm.pp;
            });
        }

        vm.doisListNext = function(offset) {
            APIDOIService.getDOIList(vm.client.app_id, vm.pp, offset).then(function(data){
                vm.dois = vm.dois.concat(data.data.dois);
                vm.total = data.data.total;
                vm.offset += vm.pp;
            });
        }


        vm.view = function(doi, keep) {
            if (!keep) {
                vm.response = false;
            }
            vm.editxml = false;
            vm.tab = 'view';
            APIDOIService.getDOI(doi, vm.client.app_id).then(function(data){
                vm.readonly = true;
                $scope.$broadcast('readonly', vm.readonly);
                vm.viewdoi = data.data;
            });
        }

        vm.update = function(doi) {
            vm.editxml = false;
            vm.tab = 'view';
            vm.response = false;
            APIDOIService.getDOI(doi, vm.client.app_id).then(function(data){
                vm.readonly = false;
                $scope.$broadcast('readonly', vm.readonly);
                vm.viewdoi = data.data;
            });
        }
        // vm.update("10.5072/00/563978d704714");

        vm.mint = function() {
            $scope.$broadcast('update');
            var data = {
                xml : vm.stripBlankElements(vm.newdoixml),
                app_id : vm.client.app_id,
                url : vm.newdoi_url,
                doi : vm.newdoi_id,
                client_id: vm.client.client_id
            }
            vm.loading = true;
            vm.response = false;
            APIDOIService.mint(data).then(function(response){
                vm.loading = false;
                vm.response = response.response;
                if (vm.response.doi && vm.response.type!="failure") {
                    vm.view(vm.response.doi, true);
                }
            });
        }

        vm.cancel = function() {
            vm.viewdoi = false;
            vm.response = false;
            vm.tab = 'list';
        }

        vm.doupdate = function() {
            $scope.$broadcast('update');
            var data = {
                xml : vm.stripBlankElements(vm.viewdoi.datacite_xml),
                app_id : vm.client.app_id,
                url : vm.viewdoi.url,
                doi : vm.viewdoi.doi_id,
                client_id: vm.client.client_id
            }
            vm.loading = true;
            vm.response = false;
            APIDOIService.update(data).then(function(response){
                vm.loading = false;
                vm.response = response.response;
                if (vm.response.type!='failure' && vm.response.doi) {
                    vm.view(vm.response.doi, true);
                }
            });
        }

        vm.dodeactivate = function(doi_id) {
            var data = {
                app_id : vm.client.app_id,
                doi : doi_id,
                client_id: vm.client.client_id
            }
            vm.response = {};
            APIDOIService.deactivate(data).then(function(response){
                alert(response.response.message);
                vm.refreshDOIs();
            });
        }

        vm.doactivate = function(doi_id) {
            var data = {
                app_id : vm.client.app_id,
                doi : doi_id,
                client_id: vm.client.client_id
            }
            vm.response = {};
            APIDOIService.activate(data).then(function(response){
                alert(response.response.message);
                vm.refreshDOIs();
            });
        }

        vm.dolinkcheck = function() {
            vm.linkchecking = true;
            APIDOIService.checkLinks(vm.client.app_id).then(function(response){
                vm.linkchecking = false;
                vm.link_checker_result = $sce.trustAsHtml(response.message);
            });
        }

        vm.uniqid = function(prefix, more_entropy) {
          //  discuss at: http://phpjs.org/functions/uniqid/
          // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
          //  revised by: Kankrelune (http://www.webfaktory.info/)
          //        note: Uses an internal counter (in php_js global) to avoid collision
          //        test: skip
          //   example 1: uniqid();
          //   returns 1: 'a30285b160c14'
          //   example 2: uniqid('foo');
          //   returns 2: 'fooa30285b1cd361'
          //   example 3: uniqid('bar', true);
          //   returns 3: 'bara20285b23dfd1.31879087'

          if (typeof prefix === 'undefined') {
            prefix = '';
          }

          var retId;
          var formatSeed = function(seed, reqWidth) {
            seed = parseInt(seed, 10)
              .toString(16); // to hex str
            if (reqWidth < seed.length) { // so long we split
              return seed.slice(seed.length - reqWidth);
            }
            if (reqWidth > seed.length) { // so short we pad
              return Array(1 + (reqWidth - seed.length))
                .join('0') + seed;
            }
            return seed;
          };

          // BEGIN REDUNDANT
          if (!this.php_js) {
            this.php_js = {};
          }
          // END REDUNDANT
          if (!this.php_js.uniqidSeed) { // init seed with big random int
            this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
          }
          this.php_js.uniqidSeed++;

          retId = prefix; // start with prefix, add current milliseconds hex string
          retId += formatSeed(parseInt(new Date()
            .getTime() / 1000, 10), 8);
          retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string
          if (more_entropy) {
            // for more entropy we add a float lower to 10
            retId += (Math.random() * 10)
              .toFixed(8)
              .toString();
          }

          return retId;
        }

        vm.formatXml = function(xml) {
            var reg = /(>)\s*(<)(\/*)/g; // updated Mar 30, 2015
            var wsexp = / *(.*) +\n/g;
            var contexp = /(<.+>)(.+\n)/g;
            xml = xml.replace(reg, '$1\n$2$3').replace(wsexp, '$1\n').replace(contexp, '$1\n$2');
            var pad = 0;
            var formatted = '';
            var lines = xml.split('\n');
            var indent = 0;
            var lastType = 'other';
            // 4 types of tags - single, closing, opening, other (text, doctype, comment) - 4*4 = 16 transitions
            var transitions = {
                'single->single': 0,
                'single->closing': -1,
                'single->opening': 0,
                'single->other': 0,
                'closing->single': 0,
                'closing->closing': -1,
                'closing->opening': 0,
                'closing->other': 0,
                'opening->single': 1,
                'opening->closing': 0,
                'opening->opening': 1,
                'opening->other': 1,
                'other->single': 0,
                'other->closing': -1,
                'other->opening': 0,
                'other->other': 0
            };

            for (var i = 0; i < lines.length; i++) {
                var ln = lines[i];
                var single = Boolean(ln.match(/<.+\/>/)); // is this line a single tag? ex. <br />
                var closing = Boolean(ln.match(/<\/.+>/)); // is this a closing tag? ex. </a>
                var opening = Boolean(ln.match(/<[^!].*>/)); // is this even a tag (that's not <!something>)
                var type = single ? 'single' : closing ? 'closing' : opening ? 'opening' : 'other';
                var fromTo = lastType + '->' + type;
                lastType = type;
                var padding = '';

                indent += transitions[fromTo];
                for (var j = 0; j < indent; j++) {
                    padding += '\t';
                }
                if (fromTo == 'opening->closing')
                    formatted = formatted.substr(0, formatted.length - 1) + ln + '\n'; // substr removes line break (\n) from prev loop
                else
                    formatted += padding + ln + '\n';
            }

            return formatted;
        }

        vm.stripBlankElements = function(xml) {
            try {
                var dom = $.parseXML(xml);
                //$('*:empty', dom).remove();
                $("*", dom).filter(function(){
                    var tagName = $( this ).prop( "tagName" );
                    if (tagName !== "resourceType") {
                        return $.trim(this.textContent) === "";
                    }
                }).remove();
                return (new XMLSerializer()).serializeToString(dom);
            } catch (e) {
                return xml;
            }

        }

        // BULK Operation
        vm.bulk_types = [{'id':'url', 'label':'URL'}];
        vm.bulk_type = 'url';

        vm.bulkPreview = function() {
            var data = {
                app_id : vm.client.app_id,
                type : vm.bulk_type,
                from: vm.bulk_from,
                to: vm.bulk_to,
                preview: true
            }
            APIDOIService.bulkRequest(data).then(function(response){
                vm.bulkPreviewResponse = response.data;
                console.log( vm.bulkPreviewResponse );
            });
        };

        vm.sendBulkRequest = function() {
            if (!confirm('Are you sure you want to send a bulk request update?' +
                    ' This will affect ' + vm.bulkPreviewResponse.total + ' DOI(s)')
            ) {
                return;
            }
            var data = {
                app_id : vm.client.app_id,
                type : vm.bulk_type,
                from: vm.bulk_from,
                to: vm.bulk_to
            }
            APIDOIService.bulkRequest(data).then(function(response){
                vm.bulkRequestedResponse = response.data;
                vm.getBulkRequests();
                delete vm.bulkPreviewResponse;
            });
        }

        vm.getBulkRequests = function () {
            delete vm.bulkRequests;
            APIDOIService.bulk({
                client_id: vm.client.client_id,
                app_id: vm.client.app_id
            }).then(function (response) {
                vm.bulkRequests = response.data;
                angular.forEach(vm.bulkRequests, function (bulkRequest) {
                    bulkRequest.params = JSON.parse(bulkRequest.params);
                    bulkRequest.paramsString = JSON.stringify(bulkRequest.params, null, 2);
                    if (bulkRequest.counts.ERROR > 0) {
                        vm.setActiveStatus(bulkRequest, 'ERROR');
                    } else {
                        vm.setActiveStatus(bulkRequest, 'PENDING');
                    }
                });
            });
        }
        vm.getBulkRequests();

        vm.setActiveStatus = function (bulkRequest, status) {
            bulkRequest.activeStatus = status;
            bulkRequest.activeStatusList = bulkRequest[status];
        }

        vm.removeBulk = function(bulkRequest) {
            if (!confirm('Are you sure you want to delete this bulk request? ' +
                    'The bulk request log is available in the activity log')
            ) {
                return;
            }
            bulkRequest.deleting = true;
            APIDOIService.bulkRequest({
                app_id: vm.client.app_id,
                'delete': bulkRequest.id
            }).then(function () {
                vm.getBulkRequests();
            });
        }

    }


})();