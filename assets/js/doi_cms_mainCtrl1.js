(function(){
    'use strict';

    angular
        .module('doi_cms_app1')
        .controller('mainCtrl', mainCtrl)
    ;

    function mainCtrl(APIDOIService, $scope, $location, $log, $sce) {
        var vm = this;
       // vm.tab = "list";
        $scope.base_url = apps_url;
        $scope.real_base_url = base_url;
        vm.newdoixml = "";

        vm.editxml = false;
        vm.response = false;
        vm.newdoi_url = '';
        vm.newdoixml = '<?xml version="1.0" encoding="utf-8"?><resource xmlns="http://datacite.org/schema/kernel-4" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://datacite.org/schema/kernel-4 http://schema.datacite.org/meta/kernel-4/metadata.xsd"><identifier identifierType="DOI">xx.xxx/doi_string</identifier><creators><creator> <creatorName></creatorName> </creator> </creators><titles> <title></title> </titles></resource>';

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


    }


})();