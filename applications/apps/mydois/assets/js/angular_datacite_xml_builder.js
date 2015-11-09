(function(){
    'use strict';
    angular
        .module('ngDataciteXMLBuilder', [])
        .directive('dataciteXmlBuilder', dataciteXMLBuilder)
        .directive('dataciteTwinItemForm', dataciteTwinItemForm)
    ;

    function dataciteXMLBuilder($log) {
        return {
            restrict: 'ACME',
            scope: {
                ngModel: '=',
                xml: '=',
                readonly:'='
            },
            transclude: true,
            templateUrl: apps_url+'assets/mydois/js/angular_datacite_xml_builder.html',
            link: function(scope) {

                scope.$watch('readonly', function(newv){
                    scope.$broadcast('readonly', scope.readonly);
                });

                scope.$watch('xml', function(newv, oldv){
                    if (newv) {
                        scope.objectModel = scope.xmlToJson(newv);
                        scope.fixValues();
                    }
                });

                scope.$watch('objectModel', function(newv, oldv){
                    if (newv && newv!=oldv) {
                        // scope.update();
                    }
                }, true);

                scope.availableOptions = {
                    'title': ['AlternativeTitle', 'Subtitle', 'TranslatedTitle']
                }

                scope.availableOptions['contributorType'] = ['ContactPerson', 'DataCollector', 'DataCurator', 'DataManager', 'Distributor', 'Editor', 'Funder', 'HostingInstitution', 'Producer', 'ProjectLeader', 'ProjectManager', 'ProjectMember', 'RegistrationAgency', 'RegistrationAuthority', 'RelatedPerson', 'Researcher', 'ResearchGroup', 'RightsHolder', 'Sponsor', 'Supervisor', 'WorkPackageLeader', 'Other'];

                scope.availableOptions['relatedIdentifierType'] = ['ARK', 'arXiv', 'bibcode', 'DOI', 'EAN13', 'EISSN', 'Handle', 'ISBN', 'ISSN', 'ISTC', 'LISSN', 'LSID', 'PMID', 'PURL', 'UPC', 'URL', 'URN  '];

                scope.availableOptions['relationType'] = ['IsCitedBy', 'Cites', 'IsSupplementTo', 'IsSupplementedBy', 'IsContinuedBy', 'Continues', 'HasMetadata', 'IsMetadataFor', 'IsNewVersionOf', 'IsPreviousVersionOf', 'IsPartOf', 'HasPart', 'IsReferencedBy', 'References', 'IsDocumentedBy', 'Documents', 'IsCompiledBy', 'Compiles', 'IsVariantFormOf', 'IsOriginalFormOf', 'IsIdenticalTo', 'IsReviewedBy', 'Reviews', 'IsDerivedFrom', 'IsSourceOf'];

                scope.setOption = function(item, attr, value) {
                    if (!item._attr) item._attr = {};
                    if (!item._attr[attr]) item._attr[attr] = {};
                    item._attr[attr]._value = value;
                }

                scope.add = function(list, elem) {
                    var obj={};
                    if (elem=='creator') {
                        obj = {
                            'creatorName':[{}],
                            'nameIdentifier':[{}],
                            'affiliation':[{}]
                        }
                    } else if (elem=='geoLocation') {
                        obj = {
                            'geoLocationPoint':[{}],
                            'geoLocationBox':[{}],
                            'geoLocationPlace':[{}]
                        }
                    } else if (elem=='contributor') {
                        obj = {
                            'contributorName':[{}],
                            'nameIdentifier':[{}],
                            'affiliation':[{}]
                        }
                    }
                    if (!list) {
                        var parent = elem+'s'; //title becomes titles
                        if (elem=='rights') parent = 'rightsList';
                        scope.objectModel.resource[0][parent] = [{}];
                        if (!scope.objectModel.resource[0][parent][0][elem]) {
                            scope.objectModel.resource[0][parent][0][elem] = [];
                        }
                        scope.objectModel.resource[0][parent][0][elem].push(obj);
                    } else {
                        if (!list[elem]) list[elem] = [];
                        list[elem].push(obj);
                    }

                }

                scope.remove = function(list, index) {
                    list.splice(index, 1);
                }

                scope.update = function(){
                    scope.xml = scope.jsonToXml(scope.objectModel);
                }

                scope.tagsToReplace = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;'
                };

                scope.replaceTag = function(tag) {
                    return scope.tagsToReplace[tag] || tag;
                }

                scope.safe_tags_replace = function (str) {
                    if (typeof str=='string' ) {
                        return str.replace(/[&<>]/g, scope.replaceTag);
                    } else {
                        return str;
                    }
                }

                scope.fixValues = function() {
                    var valuesToFix = ['publisher', 'publicationYear', 'resourceType', 'language', 'version'];
                    angular.forEach(valuesToFix, function(val){
                        if (!scope.objectModel.resource[0][val]) {
                            scope.objectModel.resource[0][val] = [];
                            scope.objectModel.resource[0][val].push({});
                        }
                    });

                    if (scope.objectModel.resource[0].creators) {
                        angular.forEach(scope.objectModel.resource[0].creators[0].creator, function(creator){
                            var fields = ['creatorName', 'affiliation', 'nameIdentifier'];
                            angular.forEach(fields, function(fi){
                                if (!creator[fi]) creator[fi] = [{}];
                            });
                        });
                    }

                }

                scope.jsonToXml = function(json) {
                    if (json) {
                        var xml = '';
                        xml += '<?xml version="1.0" encoding="utf-8"?>';

                        //resource
                        var xmlns = json.resource[0]['_attr']['xmlns']['_value'];
                        var xmlnsxsi = json.resource[0]['_attr']['xmlns:xsi']['_value'];
                        var xsischemaLocation = json.resource[0]['_attr']['schemaLocation']['_value'];
                        // $log.debug(xmlns, xmlnsxsi, xsischemaLocation);
                        xml += '<resource xmlns="'+xmlns+'" xmlns:xsi="'+xmlnsxsi+'" xsi:schemaLocation="'+xsischemaLocation+'">';

                        xml += '<identifier identifierType="'+json.resource[0].identifier[0]['_attr']['identifierType']['_value']+'">'+json.resource[0].identifier[0]['_text']+'</identifier>';



                        //single values
                        var singleValues = ['publisher', 'publicationYear', 'language', 'version', 'resourceType'];
                        angular.forEach(singleValues, function(module){
                            if (json.resource[0][module] && json.resource[0][module].length && json.resource[0][module][0]['_text']) {
                                var item = json.resource[0][module][0];
                                xml+='<'+module;
                                if (item['_attr']) {
                                    angular.forEach(item['_attr'], function(value, key) {
                                        xml+=' '+key+'="'+value['_value']+'"';
                                    });
                                }
                                xml+='>';
                                if (item['_text']) {
                                    xml+=scope.safe_tags_replace(item['_text']);
                                }
                                xml+='</'+module+'>';
                            }
                        });

                        //similar modules
                        var modules = ['title', 'subject', 'date', 'alternateIdentifier', 'relatedIdentifier', 'size', 'format', 'description', 'rights', 'geoLocation', 'creator', 'contributor'];
                        angular.forEach(modules, function(module){
                            var container = module+'s';
                            if (module == 'rights') container = 'rightsList';
                            if (json.resource[0][container] && json.resource[0][container][0][module].length > 0) {
                                xml+='<'+container+'>';
                                angular.forEach(json.resource[0][container][0][module], function(item){
                                    xml+='<'+module;
                                    if (item['_attr']) {
                                        angular.forEach(item['_attr'], function(value, key) {
                                            if (value['_value']) {
                                                xml+=' '+key+'="'+scope.safe_tags_replace(value['_value'])+'"';
                                            }
                                        });
                                    }
                                    xml+='>';

                                    angular.forEach(item, function(subitem, subitemkey){
                                        if (subitemkey!='_ns' && subitemkey!='_attr' && subitemkey!='_text') {
                                            xml+='<'+subitemkey;
                                            if (subitem[0]['_attr']) {
                                                angular.forEach(subitem[0]['_attr'], function(subitemvalue, subitemkey) {
                                                    if (subitemvalue['_value']) {
                                                        xml+=' '+subitemkey+'="'+scope.safe_tags_replace(subitemvalue['_value'])+'"';
                                                    }
                                                });
                                            }
                                            xml+='>';
                                            if (subitem[0] && subitem[0]['_text']) {
                                                xml+=scope.safe_tags_replace(subitem[0]['_text']);
                                            }
                                            xml+='</'+subitemkey+'>';
                                        }
                                    });



                                    if (item['_text']) {
                                        // xml+=item['_text'];
                                        xml+=scope.safe_tags_replace(item['_text']);
                                    }
                                    xml+='</'+module+'>';
                                });
                                xml+='</'+container+'>';
                            }
                        });

                        xml+='</resource>';

                        return xml;
                    }

                }



                scope.xmlToJson = function(xml) {
                    var options = {
                        mergeCDATA: true,   // extract cdata and merge with text nodes
                        grokAttr: true,     // convert truthy attributes to boolean, etc
                        grokText: true,     // convert truthy text/attr to boolean, etc
                        normalize: true,    // collapse multiple spaces to single space
                        xmlns: true,        // include namespaces as attributes in output
                        namespaceKey: '_ns',    // tag name for namespace objects
                        textKey: '_text',   // tag name for text nodes
                        valueKey: '_value',     // tag name for attribute values
                        attrKey: '_attr',   // tag for attr groups
                        cdataKey: '_cdata',
                        attrsAsObject: true,    // if false, key is used as prefix to name, set prefix to '' to merge children and attrs.
                        stripAttrPrefix: true,  // remove namespace prefixes from attributes
                        stripElemPrefix: true,  // for elements of same name in diff namespaces, you can enable namespaces and access the nskey property
                        childrenAsArray: true   // force children into arrays
                    };
                    return xmlToJSON.parseString(xml, options);
                }

            }
        }
    }

    function dataciteTwinItemForm($log) {
        return {
            restrict: 'ACME',
            scope: {
                'item': '=',
                'list': '=',
                'index': '=',
                'optiontype': '=',
                'label': '=',
                'readonly': '='
            },
            transclude: true,
            templateUrl: apps_url+'assets/mydois/js/angular_datacite_twin_form.html',
            link: function(scope) {
                scope.availableOptions = {
                    'title': ['AlternativeTitle', 'Subtitle', 'TranslatedTitle']
                }
                scope.availableOptions['titleType'] = ['AlternativeTitle', 'Subtitle', 'TranslatedTitle'];
                scope.availableOptions['dateType'] = ['Accepted', 'Available', 'Copyrighted', 'Collected', 'Created', 'Issued', 'Submitted', 'Updated', 'Valid'];
                scope.availableOptions['resourceTypeGeneral'] = ['Audiovisual', 'Collection', 'Dataset', 'Event', 'Image', 'InteractiveResource', 'Model', 'PhysicalObject', 'Service', 'Software', 'Sound', 'Text', 'Workflow', 'Other'];
                scope.availableOptions['descriptionType'] = ['Abstract', 'Methods', 'SeriesInformation', 'TableOfContents', 'Other', ]

                scope.remove = function() {
                    scope.list.splice(scope.index, '1');
                }
                scope.setOption = function(item, attr, value) {
                    if (!item) item = {};
                    if (!item._attr) item._attr = {};
                    if (!item._attr[attr]) item._attr[attr] = {};
                    item._attr[attr]._value = value;
                }

            }
        }
    }


/* Copyright 2015 William Summers, MetaTribal LLC
 * adapted from https://developer.mozilla.org/en-US/docs/JXON
 *
 * Licensed under the MIT License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://opensource.org/licenses/MIT
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
/**
 * @author William Summers
 *
 */

var xmlToJSON = (function () {

    this.version = "1.3";

    var options = { // set up the default options
        mergeCDATA: true, // extract cdata and merge with text
        grokAttr: true, // convert truthy attributes to boolean, etc
        grokText: true, // convert truthy text/attr to boolean, etc
        normalize: true, // collapse multiple spaces to single space
        xmlns: true, // include namespaces as attribute in output
        namespaceKey: '_ns', // tag name for namespace objects
        textKey: '_text', // tag name for text nodes
        valueKey: '_value', // tag name for attribute values
        attrKey: '_attr', // tag for attr groups
        cdataKey: '_cdata', // tag for cdata nodes (ignored if mergeCDATA is true)
        attrsAsObject: true, // if false, key is used as prefix to name, set prefix to '' to merge children and attrs.
        stripAttrPrefix: true, // remove namespace prefixes from attributes
        stripElemPrefix: true, // for elements of same name in diff namespaces, you can enable namespaces and access the nskey property
        childrenAsArray: true // force children into arrays
    };

    var prefixMatch = new RegExp(/(?!xmlns)^.*:/);
    var trimMatch = new RegExp(/^\s+|\s+$/g);

    this.grokType = function (sValue) {
        if (/^\s*$/.test(sValue)) {
            return null;
        }
        if (/^(?:true|false)$/i.test(sValue)) {
            return sValue.toLowerCase() === "true";
        }
        if (isFinite(sValue)) {
            return parseFloat(sValue);
        }
        return sValue;
    };

    this.parseString = function (xmlString, opt) {
        return this.parseXML(this.stringToXML(xmlString), opt);
    }

    this.parseXML = function (oXMLParent, opt) {

        // initialize options
        for (var key in opt) {
            options[key] = opt[key];
        }

        var vResult = {},
            nLength = 0,
            sCollectedTxt = "";

        // parse namespace information
        if (options.xmlns && oXMLParent.namespaceURI) {
            vResult[options.namespaceKey] = oXMLParent.namespaceURI;
        }

        // parse attributes
        // using attributes property instead of hasAttributes method to support older browsers
        if (oXMLParent.attributes && oXMLParent.attributes.length > 0) {
            var vAttribs = {};

            for (nLength; nLength < oXMLParent.attributes.length; nLength++) {
                var oAttrib = oXMLParent.attributes.item(nLength);
                vContent = {};
                var attribName = '';

                if (options.stripAttrPrefix) {
                    attribName = oAttrib.name.replace(prefixMatch, '');

                } else {
                    attribName = oAttrib.name;
                }

                if (options.grokAttr) {
                    vContent[options.valueKey] = this.grokType(oAttrib.value.replace(trimMatch, ''));
                } else {
                    vContent[options.valueKey] = oAttrib.value.replace(trimMatch, '');
                }

                if (options.xmlns && oAttrib.namespaceURI) {
                    vContent[options.namespaceKey] = oAttrib.namespaceURI;
                }

                if (options.attrsAsObject) { // attributes with same local name must enable prefixes
                    vAttribs[attribName] = vContent;
                } else {
                    vResult[options.attrKey + attribName] = vContent;
                }
            }

            if (options.attrsAsObject) {
                vResult[options.attrKey] = vAttribs;
            } else {}
        }

        // iterate over the children
        if (oXMLParent.hasChildNodes()) {
            for (var oNode, sProp, vContent, nItem = 0; nItem < oXMLParent.childNodes.length; nItem++) {
                oNode = oXMLParent.childNodes.item(nItem);

                if (oNode.nodeType === 4) {
                    if (options.mergeCDATA) {
                        sCollectedTxt += oNode.nodeValue;
                    } else {
                        if (vResult.hasOwnProperty(options.cdataKey)) {
                            if (vResult[options.cdataKey].constructor !== Array) {
                                vResult[options.cdataKey] = [vResult[options.cdataKey]];
                            }
                            vResult[options.cdataKey].push(oNode.nodeValue);

                        } else {
                            if (options.childrenAsArray) {
                                vResult[options.cdataKey] = [];
                                vResult[options.cdataKey].push(oNode.nodeValue);
                            } else {
                                vResult[options.cdataKey] = oNode.nodeValue;
                            }
                        }
                    }
                } /* nodeType is "CDATASection" (4) */
                else if (oNode.nodeType === 3) {
                    sCollectedTxt += oNode.nodeValue;
                } /* nodeType is "Text" (3) */
                else if (oNode.nodeType === 1) { /* nodeType is "Element" (1) */

                    if (nLength === 0) {
                        vResult = {};
                    }

                    // using nodeName to support browser (IE) implementation with no 'localName' property
                    if (options.stripElemPrefix) {
                        sProp = oNode.nodeName.replace(prefixMatch, '');
                    } else {
                        sProp = oNode.nodeName;
                    }

                    vContent = xmlToJSON.parseXML(oNode);

                    if (vResult.hasOwnProperty(sProp)) {
                        if (vResult[sProp].constructor !== Array) {
                            vResult[sProp] = [vResult[sProp]];
                        }
                        vResult[sProp].push(vContent);

                    } else {
                        if (options.childrenAsArray) {
                            vResult[sProp] = [];
                            vResult[sProp].push(vContent);
                        } else {
                            vResult[sProp] = vContent;
                        }
                        nLength++;
                    }
                }
            }
        } else if (!sCollectedTxt) { // no children and no text, return null
            if (options.childrenAsArray) {
                vResult[options.textKey] = [];
                vResult[options.textKey].push(null);
            } else {
                vResult[options.textKey] = null;
            }
        }

        if (sCollectedTxt) {
            if (options.grokText) {
                var value = this.grokType(sCollectedTxt.replace(trimMatch, ''));
                if (value !== null && value !== undefined) {
                    vResult[options.textKey] = value;
                }
            } else if (options.normalize) {
                vResult[options.textKey] = sCollectedTxt.replace(trimMatch, '').replace(/\s+/g, " ");
            } else {
                vResult[options.textKey] = sCollectedTxt.replace(trimMatch, '');
            }
        }

        return vResult;
    }


    // Convert xmlDocument to a string
    // Returns null on failure
    this.xmlToString = function (xmlDoc) {
        try {
            var xmlString = xmlDoc.xml ? xmlDoc.xml : (new XMLSerializer()).serializeToString(xmlDoc);
            return xmlString;
        } catch (err) {
            return null;
        }
    }

    // Convert a string to XML Node Structure
    // Returns null on failure
    this.stringToXML = function (xmlString) {
        try {
            var xmlDoc = null;

            if (window.DOMParser) {

                var parser = new DOMParser();
                xmlDoc = parser.parseFromString(xmlString, "text/xml");

                return xmlDoc;
            } else {
                xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
                xmlDoc.async = false;
                xmlDoc.loadXML(xmlString);

                return xmlDoc;
            }
        } catch (e) {
            return null;
        }
    }

    return this;
}).call({});

if (typeof module != "undefined" && module !== null && module.exports) module.exports = xmlToJSON;
else if (typeof define === "function" && define.amd) define(function() {return xmlToJSON});
})();