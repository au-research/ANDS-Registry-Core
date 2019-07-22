(function () {
    'use strict';
    angular
        .module('ngDataciteXMLBuilder', [])
        .directive('dataciteXmlBuilder', dataciteXMLBuilder)
        .directive('dataciteTwinItemForm', dataciteTwinItemForm)
    ;

    function dataciteXMLBuilder() {
        return {
            restrict: 'ACME',
            scope: {
                ngModel: '=',
                xml: '=',
                readonly: '='
            },
            transclude: true,
            templateUrl: apps_url + 'assets/mydois/js/angular_datacite_xml_builder.html',
            link: function (scope) {

                scope.$watch('readonly', function () {
                    scope.$broadcast('readonly', scope.readonly);
                });

                scope.$watch('xml', function (newv) {
                    if (newv) {
                        scope.objectModel = scope.xmlToJson(newv);
                        scope.fixValues();
                    }
                });

                scope.$on('update', function () {
                    scope.update();
                });

                scope.$watch('objectModel', function (newv, oldv) {
                    if (newv && newv != oldv) {
                        // scope.update();
                    }
                }, true);

                scope.show_recommended = true;

                scope.availableOptions = {
                    'title': ['AlternativeTitle', 'Subtitle', 'TranslatedTitle','Other']
                };

                scope.availableOptions['nameType'] = ['Organizational', 'Personal'];

                scope.availableOptions['contributorType'] = ['ContactPerson', 'DataCollector', 'DataCurator',
                    'DataManager', 'Distributor', 'Editor', 'HostingInstitution', 'Producer', 'ProjectLeader',
                    'ProjectManager', 'ProjectMember', 'RegistrationAgency', 'RegistrationAuthority',
                    'RelatedPerson', 'Researcher', 'ResearchGroup', 'RightsHolder', 'Sponsor',
                    'Supervisor', 'WorkPackageLeader', 'Other'];

                scope.availableOptions['relatedIdentifierType'] = ['ARK', 'arXiv', 'bibcode', 'DOI', 'EAN13',
                    'EISSN', 'Handle', 'IGSN', 'ISBN', 'ISSN', 'ISTC', 'LISSN', 'LSID', 'PMID', 'PURL',
                    'UPC', 'URL', 'URN', 'w3id'];

                scope.availableOptions['relationType'] = ['IsCitedBy', 'Cites', 'IsSupplementTo', 'IsSupplementedBy',
                    'IsContinuedBy', 'Continues', 'HasMetadata', 'IsMetadataFor', 'IsNewVersionOf', 'IsPreviousVersionOf',
                    'IsPartOf', 'HasPart', 'IsReferencedBy', 'References', 'IsDocumentedBy', 'Documents', 'IsCompiledBy',
                    'Compiles', 'IsVariantFormOf', 'IsOriginalFormOf', 'IsIdenticalTo', 'IsReviewedBy', 'Reviews', 'IsDerivedFrom',
                    'IsSourceOf', 'IsDescribedBy', 'Describes', 'HasVersion', 'IsVersionOf', 'IsRequiredBy', 'Requires', 'Obsoletes', 'IsObsoletedBy'];

                scope.availableOptions['descriptionType'] = ['Abstract', 'Methods', 'SeriesInformation', 'TableOfContents', 'TechnicalInfo', 'Other'];

                scope.availableOptions['funderIdentifierType'] = ['ISNI', 'GRID', 'Crossref Funder ID', 'Other'];

                scope.availableOptions['resourceTypeGeneral'] = ['Audiovisual', 'Collection', 'Dataset', 'DataPaper', 'Event', 'Image',
                    'InteractiveResource', 'Model', 'PhysicalObject', 'Service', 'Software', 'Sound', 'Text', 'Workflow', 'Other'];


                scope.setOption = function (item, attr, value) {
                    if (!item._attr) item._attr = {};
                    if (!item._attr[attr]) item._attr[attr] = {};
                    item._attr[attr]._value = value;
                };

                scope.add = function (list, elem) {
                    var obj = [{}];
                    if (elem == 'creator') {
                        obj = {
                            'creatorName': [{}],
                            'givenName': [{}],
                            'familyName': [{}],
                            'nameIdentifier': [{}],
                            'affiliation': [{}]
                        }
                    } else if (elem == 'geoLocation') {
                        obj = {
                            'geoLocationPoint': [{
                                'pointLongitude': [{}],
                                'pointLatitude': [{}]
                            }],
                            'geoLocationBox': [{
                                'westBoundLongitude': [{}],
                                'eastBoundLongitude': [{}],
                                'southBoundLatitude': [{}],
                                'northBoundLatitude': [{}]
                            }],
                            'geoLocationPlace': [{}],
                            'geoLocationPolygon': [{
                                'polygonPoint': [
                                    {'pointLongitude':[{}], 'pointLatitude': [{}]},
                                    {'pointLongitude':[{}], 'pointLatitude': [{}]},
                                    {'pointLongitude':[{}], 'pointLatitude': [{}]},
                                    {'pointLongitude':[{}], 'pointLatitude': [{}]}
                                ],
                                'inPolygonPoint': [{'pointLongitude':[{}], 'pointLatitude': [{}]}]
                            }]
                        }
                    } else if (elem == 'contributor') {
                        obj = {
                            'contributorName': [{}],
                            'familyName': [{}],
                            'givenName': [{}],
                            'nameIdentifier': [{}],
                            'affiliation': [{}]
                        }
                    } else if (elem == 'fundingReference') {
                        obj = {
                            'funderName': [{}],
                            'funderIdentifier': [{}],
                            'awardNumber': [{}],
                            'awardTitle': [{}]
                        }
                    } else if (elem == 'polygonPoint') {
                        obj = {
                            'polygonPoint': [{}]
                        }
                    } else if (elem == 'geoLocationPolygon'){
                        obj = {
                            'polygonPoint': [
                                {'pointLongitude':[{}], 'pointLatitude': [{}]},
                                {'pointLongitude':[{}], 'pointLatitude': [{}]},
                                {'pointLongitude':[{}], 'pointLatitude': [{}]},
                                {'pointLongitude':[{}], 'pointLatitude': [{}]}
                            ],
                            'inPolygonPoint': [{'pointLongitude':[{}], 'pointLatitude': [{}]}]
                        }
                    }
                    if (!list) {
                        var parent = elem + 's'; //title becomes titles
                        if (elem == 'rights') parent = 'rightsList';
                        scope.objectModel.resource[0][parent] = [{}];
                        if (!scope.objectModel.resource[0][parent][0][elem]) {
                            scope.objectModel.resource[0][parent][0][elem] = [];
                        }
                        scope.objectModel.resource[0][parent][0][elem].push(obj);
                    } else {

                        if (!list[elem]) list[elem] = [];
                        list[elem].push(obj);
                    }
                };

                scope.remove = function (list, index) {
                    list.splice(index, 1);
                };

                scope.update = function () {
                    scope.xml = scope.jsonToXml(scope.objectModel);
                };

                scope.tagsToReplace = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;'
                };

                scope.replaceTag = function (tag) {
                    return scope.tagsToReplace[tag] || tag;
                };

                scope.safe_tags_replace = function (str) {
                    if (typeof str == 'string') {
                        return str.replace(/[&<>]/g, scope.replaceTag);
                    } else {
                        return str+"";
                    }
                };

                scope.fixValues = function () {
                    var valuesToFix = ['publisher', 'publicationYear', 'resourceType', 'language', 'version'];
                    angular.forEach(valuesToFix, function (val) {
                        if (!scope.objectModel.resource[0][val]) {
                            scope.objectModel.resource[0][val] = [];
                            scope.objectModel.resource[0][val].push({});
                        }
                    });
                    if (scope.objectModel.resource[0].creators) {
                        angular.forEach(scope.objectModel.resource[0].creators[0].creator, function (creator, index) {
                            var fields = ['creatorName', 'givenName', 'familyName', 'nameIdentifier', 'affiliation'];
                            var newCreator = {};
                            angular.forEach(fields, function (fi) {
                                if (!creator[fi]) creator[fi] = [{}];
                                newCreator[fi] = creator[fi];
                            });

                            // copy all other stuff over, like _attr
                            angular.forEach(creator, function(x, key){
                                if (!newCreator[key]) newCreator[key] = creator[key];
                            });
                            scope.objectModel.resource[0].creators[0].creator[index] = newCreator;
                        });
                    }

                    if (scope.objectModel.resource[0].contributors) {
                        angular.forEach(scope.objectModel.resource[0].contributors[0].contributor, function (contributor, index) {
                            var fields = ['contributorName', 'givenName', 'familyName', 'nameIdentifier', 'affiliation'];
                            var newContributor = {};
                            angular.forEach(fields, function (fi) {
                                if (!contributor[fi]) contributor[fi] = [{}];
                                newContributor[fi] = contributor[fi];
                            });

                            // copy all other stuff over, like _attr
                            angular.forEach(contributor, function(x, key){
                                if (!newContributor[key]) newContributor[key] = contributor[key];
                            });
                            scope.objectModel.resource[0].contributors[0].contributor[index] = newContributor;
                        });
                    }

                    if (scope.objectModel.resource[0].geoLocations) {
                        angular.forEach(scope.objectModel.resource[0].geoLocations[0].geoLocation, function(geoLocation, index){
                            var fields = ['geoLocationPlace','geoLocationPoint', 'geoLocationBox', 'geoLocationPolygon'];
                            var n = {};
                            angular.forEach(fields, function (fi) {
                                if (!geoLocation[fi]) geoLocation[fi] = [{}];
                                n[fi] = geoLocation[fi];
                            });
                            angular.forEach(geoLocation.geoLocationPoint, function(point, index){
                                if (!point['pointLongitude']) point['pointLongitude'] = [{}];
                                if (!point['pointLatitude']) point['pointLatitude'] = [{}];
                            });
                            angular.forEach(geoLocation.geoLocationPolygon, function(polygon, index){
                                if (!polygon['polygonPoint']) {
                                    polygon['polygonPoint'] = [
                                        {'pointLongitude': [{}], 'pointLatitude': [{}]},
                                        {'pointLongitude': [{}], 'pointLatitude': [{}]},
                                        {'pointLongitude': [{}], 'pointLatitude': [{}]},
                                        {'pointLongitude': [{}], 'pointLatitude': [{}]}
                                    ];
                                }
                                if (!polygon['inPolygonPoint']) {
                                    polygon['inPolygonPoint'] = [{'pointLongitude':[{}], 'pointLatitude': [{}]}];
                                }
                            });
                            angular.forEach(geoLocation.geoLocationBox, function(box, index){
                                if (!box['westBoundLongitude']) box['westBoundLongitude'] = [{}];
                                if (!box['eastBoundLongitude']) box['eastBoundLongitude'] = [{}];
                                if (!box['southBoundLatitude']) box['southBoundLatitude'] = [{}];
                                if (!box['northBoundLatitude']) box['northBoundLatitude'] = [{}];
                            });
                            scope.objectModel.resource[0].geoLocations[0].geoLocation[index] = n;
                        });
                    }
                };
                
                scope.addGeoLocationPolygonPoint = function(parent) {
                    if (!parent.polygonPoint) parent.polygonPoint = [];
                    parent.polygonPoint.push({'pointLongitude':[{}], 'pointLatitude':[{}]});
                };




                scope.jsonToXml = function (json) {
                    if (json) {
                        var xml = '';
                        var xmlns = "http://datacite.org/schema/kernel-4";
                        var xmlnsxsi = "http://www.w3.org/2001/XMLSchema-instance";
                        var xsischemaLocation = "http://datacite.org/schema/kernel-4 http://schema.datacite.org/meta/kernel-4.2/metadata.xsd";
                        xml += '<?xml version="1.0" encoding="utf-8"?>';
                        xml += '<resource xmlns="' + xmlns + '" xmlns:xsi="' + xmlnsxsi + '" xsi:schemaLocation="' + xsischemaLocation + '">';
                        xml += '<identifier identifierType="' + json.resource[0].identifier[0]['_attr']['identifierType']['_value'] + '">' + json.resource[0].identifier[0]['_text'] + '</identifier>';

                        //single values
                        var singleValues = ['publisher', 'publicationYear', 'language', 'version', 'resourceType'];
                        angular.forEach(singleValues, function (module) {
                            if (json.resource[0][module] &&
                                json.resource[0][module].length &&
                                (
                                    json.resource[0][module][0]['_text'] ||
                                    (module== 'resourceType' && json.resource[0][module][0]['_attr'])
                                )
                            ) {
                                var item = json.resource[0][module][0];
                                xml += '<' + module;
                                if (item['_attr']) {
                                    angular.forEach(item['_attr'], function (value, key) {
                                        if (key == 'lang') {
                                            key = 'xml:lang';
                                        }
                                        xml += ' ' + key + '="' + value['_value'] + '"';
                                    });
                                }
                                xml += '>';
                                if (item['_text']) {
                                    xml += scope.safe_tags_replace(item['_text']) + "";
                                }
                                xml += '</' + module + '>';
                            }
                        });

                        //similar modules
                        var modules = ['title', 'subject', 'date', 'alternateIdentifier', 'relatedIdentifier', 'size',
                            'format', 'description', 'rights', 'geoLocation', 'creator', 'contributor', 'fundingReference'];
                        angular.forEach(modules, function (module) {
                            var container = module + 's';
                            if (module == 'rights') container = 'rightsList';
                            if (json.resource[0][container]
                                && json.resource[0][container][0][module]
                                && json.resource[0][container][0][module].length > 0) {
                                xml += '<' + container + '>';
                                angular.forEach(json.resource[0][container][0][module], function (item) {

                                    xml += '<' + module;
                                    if (item['_attr']) {
                                        angular.forEach(item['_attr'], function (value, key) {
                                            if (value['_value']) {
                                                if (key == 'lang') {
                                                    key = 'xml:lang';
                                                }
                                                xml += ' ' + key + '="' + scope.safe_tags_replace(value['_value']) + '"';
                                            }
                                        });
                                    }
                                    xml += '>';

                                    angular.forEach(item, function (sitem, subitemkey) {
                                        xml += scope.getItemXML(sitem, subitemkey);
                                    });

                                    if (item['_text']) {
                                        // xml+=item['_text'];
                                        xml += scope.safe_tags_replace(item['_text']) + "";
                                    }
                                    xml += '</' + module + '>';
                                });
                                xml += '</' + container + '>';
                            }
                        });

                        xml += '</resource>';

                        return xml;
                    }

                };

                scope.getItemXML = function(sitem, subitemkey) {
                    var xml = "";

                    if (subitemkey != '_ns' && subitemkey != '_attr' && subitemkey != '_text') {
                        angular.forEach(sitem, function (subitem) {
                            xml += '<' + subitemkey;
                            if (subitem['_attr']) {
                                angular.forEach(subitem['_attr'], function (subitemvalue, subitemkey) {
                                    if (subitemvalue['_value']) {
                                        if (subitemkey == "lang") {
                                            subitemkey = "xml:lang";
                                        }
                                        xml += ' ' + subitemkey + '="' + scope.safe_tags_replace(subitemvalue['_value']) + '"';
                                    }
                                });
                            }
                            xml += '>';

                            angular.forEach(subitem, function(subsubitem, subsubitemkey){
                                if (subsubitem && subsubitem[0] && subsubitemkey!= '_ns' && subsubitemkey!='_text') {
                                    if (subsubitem[0]['_text']) {
                                        xml += "<" + subsubitemkey + ">";
                                        xml += subsubitem[0]['_text'] + "";
                                        xml += "</" + subsubitemkey + ">";
                                    } else {
                                        // even deeper for polygonPoint!
                                        if (subsubitemkey == 'polygonPoint' || subsubitemkey == 'inPolygonPoint') {
                                            angular.forEach(subsubitem, function (point) {
                                                xml += '<'+subsubitemkey+'>';
                                                var longitude = point.pointLongitude[0]['_text'] ? point.pointLongitude[0]['_text']: "";
                                                var lattitude = point.pointLatitude[0]['_text'] ? point.pointLatitude[0]['_text'] : "";
                                                xml += '<pointLongitude>' + longitude + '</pointLongitude>';
                                                xml += '<pointLatitude>' + lattitude + '</pointLatitude>';
                                                xml += '</'+subsubitemkey+'>';
                                            });
                                        }
                                    }
                                }
                            });

                            if (subitem && subitem['_text']) {
                                xml += scope.safe_tags_replace(subitem['_text'])+"";
                            }
                            xml += '</' + subitemkey + '>';
                        });
                    }
                    return xml;
                };


                scope.xmlToJson = function (xml) {
                    var options = {
                        mergeCDATA: true,   // extract cdata and merge with text nodes
                        grokAttr: false,     // convert truthy attributes to boolean, etc
                        grokText: false,     // convert truthy text/attr to boolean, etc
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
                    var result = xmlToJSON.parseString(xml, options);
                    return result;

                }

            }
        }
    }

    function dataciteTwinItemForm() {
        return {
            restrict: 'ACME',
            scope: {
                'item': '=',
                'list': '=',
                'index': '=',
                'optiontype': '=',
                'label': '=',
                'alabel': '=',
                'custom': '=',
                'readonly': '=',
                'attribute' : '=',
                // 'attributeValue' : '=?'
            },
            transclude: true,
            templateUrl: apps_url + 'assets/mydois/js/angular_datacite_twin_form.html',
            link: function (scope) {
                scope.availableOptions = {
                    'title': ['AlternativeTitle', 'Subtitle', 'TranslatedTitle']
                };


                scope.availableOptions['nameType'] = ['Organizational', 'Personal'];
                scope.availableOptions['titleType'] = ['AlternativeTitle', 'Subtitle', 'TranslatedTitle', 'Other'];
                scope.availableOptions['dateType'] = ['Accepted', 'Available', 'Copyrighted', 'Collected', 'Created',
                    'Issued', 'Submitted', 'Updated', 'Valid', 'Withdrawn', 'Other'];
                scope.availableOptions['resourceTypeGeneral'] = ['Audiovisual', 'Collection', 'Dataset', 'DataPaper',
                    'Event', 'Image', 'InteractiveResource', 'Model', 'PhysicalObject', 'Service', 'Software',
                    'Sound', 'Text', 'Workflow', 'Other'];
                scope.availableOptions['descriptionType'] = ['Abstract', 'Methods', 'SeriesInformation',
                    'TableOfContents', 'Other'];

                scope.remove = function () {
                    scope.list.splice(scope.index, '1');
                };

                scope.setOption = function (item, attr, value) {
                    if (!item) item = {};
                    if (!item._attr) item._attr = {};
                    if (!item._attr[attr]) item._attr[attr] = {};
                    item._attr[attr]._value = value;
                };

                if (scope.attribute) {
                    scope.setOption(scope.item, scope.attribute, "");
                }
            }
        }
    }

})();