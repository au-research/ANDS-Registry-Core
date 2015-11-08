(function(){
    'use strict';

    angular
        .module('doi_cms_app')
        .controller('mainCtrl', mainCtrl)
        .factory('doiFactory', doiFactory)
    ;

    function mainCtrl(doiFactory, client, $scope, $location, $log, $sce) {
        var vm = this;
        vm.tab = "list";
        $scope.base_url = apps_url;
        vm.newdoixml = "";

        var app_id = "";
        app_id = $location.search().app_id;
        if (!app_id) app_id = $location.search().app_id_select;

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
                    doiFactory.getLog(vm.client.app_id).then(function(data){
                        vm.logs = data.data.activities;
                    });
                    break;
                case 'mint':
                    var doi_client_id = vm.client.client_id;
                    if (vm.client.client_id < 10) {
                        doi_client_id = 0+""+vm.client.client_id;
                    }
                    vm.newdoi_id = vm.client.datacite_prefix + doi_client_id +'/'+ doiFactory.uniqid();
                    vm.newdoixml = doiFactory.getBlankXML(vm.newdoi_id);
                    break;
            }
        }

        vm.refreshDOIs = function() {
            doiFactory.getDOI(vm.client.app_id).then(function(data){
                vm.dois = data.data.dois;
            });
        }

        vm.view = function(doi) {
            vm.tab = 'view';
            doiFactory.get(doi, vm.client.app_id).then(function(data){
                vm.readonly = true;
                $scope.$broadcast('readonly', vm.readonly);
                vm.viewdoi = data.data;
            });
        }

        vm.update = function(doi) {
            vm.tab = 'view';
            doiFactory.get(doi, vm.client.app_id).then(function(data){
                vm.readonly = false;
                $scope.$broadcast('readonly', vm.readonly);
                vm.viewdoi = data.data;
            });
        }

        vm.mint = function() {
            var data = {
                xml : vm.newdoixml,
                app_id : vm.client.app_id,
                url : vm.newdoi_url,
                doi : vm.newdoi_id,
                client_id: vm.client.client_id
            }
            vm.response = {};
            doiFactory.mint(data).then(function(response){
                vm.response = response.response;
                if (vm.response.doi) {
                    vm.view(vm.response.doi);
                }
            });
        }

        vm.cancel = function() {
            vm.viewdoi = false;
            vm.tab = 'list';
        }

        vm.doupdate = function() {
            var data = {
                xml : vm.viewdoi.datacite_xml,
                app_id : vm.client.app_id,
                url : vm.viewdoi.url,
                doi : vm.viewdoi.doi_id,
                client_id: vm.client.client_id
            }
            vm.response = {};
            doiFactory.update(data).then(function(response){
                vm.response = response.response;
            });
        }

        vm.dodeactivate = function(doi_id) {
            var data = {
                app_id : vm.client.app_id,
                doi : doi_id,
                client_id: vm.client.client_id
            }
            vm.response = {};
            doiFactory.deactivate(data).then(function(response){
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
            doiFactory.activate(data).then(function(response){
                alert(response.response.message);
                vm.refreshDOIs();
            });
        }

        vm.dolinkcheck = function() {
            vm.linkchecking = true;
            doiFactory.checkLinks(vm.client.app_id).then(function(response){
                vm.linkchecking = false;
                vm.link_checker_result = $sce.trustAsHtml(response.message);
            });
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

    }

    function doiFactory($http, $log) {
        return {
            getClient: getClient,
            getDOI: getDOI,
            getLog: getLog,
            uniqid: uniqid,
            getBlankXML: getBlankXML,
            get:get,
            mint: mint,
            update:update,
            activate: activate,
            deactivate: deactivate,
            getClient: getClient,
            getAppIDs: getAppIDs,
            checkLinks: checkLinks
        }

        function checkLinks(app_id)
        {
            return $http({
                method  : 'POST',
                url     : apps_url+'mydois/runDoiLinkChecker',
                data    : $.param({
                    app_id:app_id
                }),
                headers : { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).then(returnRaw).catch(handleError);
        }

        function mint(data)
        {
            return $http({
                method  : 'POST',
                url     : apps_url+'mydois/mint.json/?manual_mint=true&url='+data.url+'&app_id='+data.app_id,
                data    : $.param({
                    xml:data.xml,
                    doi_id:data.doi,
                    client_id:data.client_id
                }),
                headers : { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).then(returnRaw).catch(handleError);
        }

        function deactivate(data)
        {
            return $http({
                method  : 'POST',
                url     : apps_url+'mydois/deactivate.json/?app_id='+data.app_id+'&doi='+data.doi,
                data    : $.param({
                    doi_update:data.doi,
                    client_id:data.client_id
                }),
                headers : { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).then(returnRaw).catch(handleError);
        }

        function activate(data)
        {
            return $http({
                method  : 'POST',
                url     : apps_url+'mydois/activate.json/?app_id='+data.app_id+'&doi='+data.doi,
                data    : $.param({
                    doi_id:data.doi,
                    client_id:data.client_id
                }),
                headers : { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).then(returnRaw).catch(handleError);
        }

        function update(data)
        {
            return $http({
                method  : 'POST',
                url     : apps_url+'mydois/update.json/?manual_update=true&doi='+data.doi+'&url='+data.url+'&app_id='+data.app_id,
                data    : $.param({
                    xml:data.xml,
                    doi_id:data.doi,
                    client_id:data.client_id
                }),
                headers : { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).then(returnRaw).catch(handleError);
        }

        function getBlankXML(doi)
        {
            var xml ='<?xml version="1.0" encoding="utf-8"?><resource xmlns="http://datacite.org/schema/kernel-3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://datacite.org/schema/kernel-3 http://schema.datacite.org/meta/kernel-3/metadata.xsd"><identifier identifierType="DOI">'+doi+'</identifier>';
            xml+='</resource>';
            return xml;
        }

        function get(doi, app_id)
        {
            return $http.get(api_url+'doi/'+doi+'?app_id='+app_id)
                .then(returnRaw)
                .catch(handleError);
        }

        function getLog(app_id)
        {
            return $http.get(api_url+'doi/log/?app_id='+app_id)
                .then(returnRaw)
                .catch(handleError);
        }

        function getDOI(app_id)
        {
            return $http.get(api_url+'doi/list/?app_id='+app_id)
                .then(returnRaw)
                .catch(handleError);
        }

        function getClient(app_id)
        {
            return $http.get(api_url+'doi/client/?app_id='+app_id)
                .then(returnRaw)
                .catch(handleError);
        }

        function getAppIDs(user_id)
        {
            return $http.get(api_url+'role/?roleId='+user_id+'&include=assoc_doi_app_id')
                .then(returnRaw)
                .catch(handleError)
        }

        function uniqid(prefix, more_entropy) {
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

        function returnRaw(response) {
            return response.data;
        }

        function handleError(error) {
            $log.error(error);
        }
    }
})();