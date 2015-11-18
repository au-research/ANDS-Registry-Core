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

})();