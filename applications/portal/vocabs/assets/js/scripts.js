(function(){
    'use strict';

    angular
        .module('app', ['ngRoute', 'ngSanitize', 'ui.bootstrap', 'ui.utils', 'angular-loading-bar', 'angularFileUpload'])
        .config(function($interpolateProvider, $locationProvider, $logProvider){
            $interpolateProvider.startSymbol('[[');
            $interpolateProvider.endSymbol(']]');
            $locationProvider.hashPrefix('!');
            $logProvider.debugEnabled(true);
        });
})();


$(document).ready(function() {


$("#vocab-tree").vocab_widget({
    mode:'tree',
    endpoint: 'https://researchdata.ands.org.au/apps/vocab_widget/proxy/',
    display_count:false,
    repository:$("#vocab-tree").attr('vocab')})
    .on('treeselect.vocab.ands', function(event) {
        var target = $(event.target);
        var data = target.data('vocab');
    });
});

$(document).on('mouseover', 'a[tip]', function(event){
    $(this).qtip({
        content:{
            text:function(e,api){
                var tip = $(this).attr('tip');
                var content = tip;
                if(tip.indexOf('#')==0 || tip.indexOf('.')==0) {
                    if($(tip.toString()).length) {
                        content = $(tip.toString()).html();
                    }
                }
                return content;
            }
        },
        show: {
            event: 'mouseover, click',
            ready: true
        },
        hide: {
            delay: 1000,
            fixed: true
        },
        position: {target:'mouse', adjust: { mouse: false }, viewport: $(window) },
        style: {classes: 'qtip-light qtip-shadow qtip-normal qtip-bootstrap'}
    });
});

$(document).on('click', '.re_preview', function(event){
    event.preventDefault();
    $(this).qtip({
        show:{event:'click'},
        hide: {
            delay: 1000,
            fixed: true
        },
        content: {
            text:  function(event, api) {
                api.elements.content.html('Loading...');
                if ($(this).attr('related')) {
                   // return "we have some text for re "+$(this).attr('re_id');
                    var url = base_url+'vocabs/related_preview/?related='+$(this).attr('related')+'&v_id='+$(this).attr('v_id')+'&sub_type='+$(this).attr('sub_type');
                                }
                if (url) {
                    return $.ajax({
                        url:url
                    }).then(function(content){
                        return content;
                    },function(xhr,status,error){
                        api.set('content.text', status + ': ' + error);
                    });
                } else {
                    return 'Error displaying preview';
                }

            }
        },
        position: {target:'mouse', adjust: { mouse: false }, viewport: $(window) },
        style: {classes: 'qtip-light qtip-shadow qtip-normal qtip-bootstrap'},
        show: {
            event:event.type,
            ready:'true'
        }
    },event);
});
//Feedback button
window.ATL_JQ_PAGE_PROPS =  {
    "triggerFunction": function(showCollectorDialog) {
        //Requries that jQuery is available!
        jQuery(".feedback_button, .myCustomTrigger").click(function(e) {
            e.preventDefault();
            showCollectorDialog();
        });
    }};

$(document).on('click', '.ver_preview', function(event){
    event.preventDefault();
    $(this).qtip({
        show:{event:'click'},
        hide: {
            delay: 1000,
            fixed: true
        },
        content: {
            text:  function(event, api) {
                api.elements.content.html('Loading...');
                if ($(this).attr('version')) {
                    var url = base_url+'vocabs/version_preview/?version='+$(this).attr('version');
                }
                if (url) {
                    return $.ajax({
                        url:url
                    }).then(function(content){
                        return content;
                    },function(xhr,status,error){
                        api.set('content.text', status + ': ' + error);
                    });
                } else {
                    return 'Error displaying preview';
                }

            }
        },
        position: {target:'mouse', adjust: { mouse: false }, viewport: $(window) },
        style: {classes: 'qtip-light qtip-shadow qtip-normal qtip-bootstrap'},
        show: {
            event:event.type,
            ready:'true'
        }
    },event);
});

$(document).on('click', '.deleteVocab', function(e){
    e.preventDefault();
    if (confirm('Are you sure you want to delete this vocabulary including all endpoints? This action cannot be reversed.')) {
        var vocab_id = $(this).attr('vocab_id');
        $.ajax({
            url:base_url+'vocabs/delete',
            type: 'POST',
            data:{id:vocab_id},
            success: function(data) {
                location.reload();
            }
        });
    } else {
        return false;
    }
});


;(function () {
    'use strict';

    angular
        .module('app')
        .filter('trustAsHtml', ['$sce', function ($sce) {
            return function (text) {
                var decoded = $('<div/>').html(text).text();
                return $sce.trustAsHtml(decoded);
            }
        }])
        .filter('languageFilter', function ($log) {
            return function (ln, langs) {
                for (var i = 0; i < langs.length; i++) {
                    if (ln == langs[i].value) {
                        return langs[i].text
                    }
                }
                return ln;
            }
        })
    ;
})();;(function () {
    'use strict';

    angular
        .module('app')
        .directive('ngDebounce', function ($timeout) {
            return {
                restrict: 'A',
                require: 'ngModel',
                priority: 99,
                link: function (scope, elm, attr, ngModelCtrl) {
                    if (attr.type === 'radio' || attr.type === 'checkbox') return;

                    elm.unbind('input');

                    var debounce;
                    elm.bind('input', function () {
                        $timeout.cancel(debounce);
                        debounce = $timeout(function () {
                            scope.$apply(function () {
                                ngModelCtrl.$setViewValue(elm.val());
                            });
                        }, attr.ngDebounce || 1000);
                    });
                    elm.bind('blur', function () {
                        scope.$apply(function () {
                            ngModelCtrl.$setViewValue(elm.val());
                        });
                    });
                }

            }
        })
        .directive('languageValidation', function () {
            return {
                restrict: 'A',
                link: function (scope, elem, attr, ctrl) {

                }
            }
        })
    ;
})();;/**
 * Vocabulary ANGULARJS Factory
 * A component that deals with the vocabulary service point directly
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
(function () {
    'use strict';

    angular
        .module('app')
        .factory('vocabs_factory', function ($http) {
            return {
                getAll: function () {
                    return $http.get(base_url + 'vocabs/services/vocabs').then(function (response) {
                        return response.data;
                    });
                },
                add: function (data) {
                    return $http.post(base_url + 'vocabs/services/vocabs', {data: data}).then(function (response) {
                        return response.data;
                    });
                },
                get: function (slug) {
                    return $http.get(base_url + 'vocabs/services/vocabs/' + slug).then(function (response) {
                        return response.data;
                    });
                },
                modify: function (slug, data) {
                    return $http.post(base_url + 'vocabs/services/vocabs/' + slug, {data: data}).then(function (response) {
                        return response.data;
                    });
                },
                search: function (filters) {
                    return $http.post(base_url + 'vocabs/filter', {filters: filters}).then(function (response) {
                        return response.data;
                    });
                },
                toolkit: function (req) {
                    return $http.get(base_url + 'vocabs/toolkit?request=' + req).then(function (response) {
                        return response.data;
                    });
                },
                getMetadata: function (id) {
                    return $http.get(base_url + 'vocabs/toolkit?request=getMetadata&ppid=' + id).then(function (response) {
                        return response.data;
                    });
                },
                suggest: function (type) {
                    return $http.get(base_url + 'vocabs/services/vocabs/all/related?type=' + type).then(function (response) {
                        return response.data;
                    });
                },
                user: function () {
                    return $http.get(base_url + 'vocabs/services/vocabs/all/user').then(function (response) {
                        return response.data;
                    });
                }
            }
        });
})();;(function () {
    'use strict';

    angular
        .module('app')
        .controller('searchCtrl', searchController);

    function searchController($scope, $log, $location, vocabs_factory) {

        $scope.vocabs = [];
        $scope.filters = {};

        // $log.debug($location.search());
        // The form of filters value for this will be <base_url>+/#!/?<filter>=<value>
        // eg. <base_url>+/#!/?q=fish, #!/?q=fish&subjects=Fish
        $scope.filters = $location.search();

        $scope.search = function () {
            if ($scope.searchRedirect()) {
                window.location = base_url + '#!/?q=' + $scope.filters['q'];
            } else {
                $location.path('/').replace();
                window.history.pushState($scope.filters, 'ANDS Research Vocabulary', $location.absUrl());
                vocabs_factory.search($scope.filters).then(function (data) {
                    $log.debug(data);
                    $scope.result = data;
                    var facets = [];
                    angular.forEach(data.facet_counts.facet_fields, function (item, index) {
                        facets[index] = [];
                        for (var i = 0; i < data.facet_counts.facet_fields[index].length; i += 2) {
                            var fa = {
                                name: data.facet_counts.facet_fields[index][i],
                                value: data.facet_counts.facet_fields[index][i + 1]
                            };
                            facets[index].push(fa);
                        }
                    });
                    $scope.facets = facets;
                });
            }
        };

        $scope.searchRedirect = function () {
            return $('#search_app').length <= 0;
        };

        if (!$scope.searchRedirect()) {
            $scope.search();
        }

        // Works with ng-debounce="500" defined in the search field, goes into effect every 500ms
        $scope.$watch('filters.q', function (newv) {
            if ((newv || newv == '')) {
                $scope.search();
            }
        });

        //Below this line are all the searching directives

        $scope.getHighlight = function (id) {
            if ($scope.result.highlighting && !$.isEmptyObject($scope.result.highlighting[id])) {
                return $scope.result.highlighting[id];
            } else return false;
        };

        $scope.toggleFilter = function (type, value, execute) {
            if ($scope.filters[type]) {
                if ($scope.filters[type] == value) {
                    $scope.clearFilter(type, value);
                } else {
                    if ($scope.filters[type].indexOf(value) == -1) {
                        $scope.addFilter(type, value);
                    } else {
                        $scope.clearFilter(type, value);
                    }
                }
            } else {
                $scope.addFilter(type, value);
            }
            $scope.filters['p'] = 1;
            if (execute) $scope.search();
        };

        $scope.toggleFacet = function (facet_type) {
            $('#more'+facet_type).slideToggle();
            $('#link'+facet_type).toggle();
        };

        $scope.addFilter = function (type, value) {
            if ($scope.filters[type]) {
                if (typeof $scope.filters[type] == 'string') {
                    var old = $scope.filters[type];
                    $scope.filters[type] = [];
                    $scope.filters[type].push(old);
                    $scope.filters[type].push(value);
                } else if (typeof $scope.filters[type] == 'object') {
                    $scope.filters[type].push(value);
                }
            } else $scope.filters[type] = value;
        };

        $scope.clearFilter = function (type, value, execute) {
            if (typeof $scope.filters[type] != 'object') {
                if (type == 'q') {
                    $scope.query = '';
                    search_factory.update('query', '');
                    $scope.filters['q'] = '';
                } else if (type == 'description' || type == 'title' || type == 'identifier' || type == 'related_people' || type == 'related_organisations' || type == 'institution' || type == 'researcher') {
                    $scope.query = '';
                    search_factory.update('query', '');
                    delete $scope.filters[type];
                    delete $scope.filters['q'];
                }
                delete $scope.filters[type];
            } else if (typeof $scope.filters[type] == 'object') {
                var index = $scope.filters[type].indexOf(value);
                $scope.filters[type].splice(index, 1);
            }
            if (execute) $scope.search();
        };

        $scope.isFacet = function (type, value) {
            if ($scope.filters[type]) {
                if (typeof $scope.filters[type] == 'string' && $scope.filters[type] == value) {
                    return true;
                } else if (typeof $scope.filters[type] == 'object') {
                    return $scope.filters[type].indexOf(value) != -1;
                }
                return false;
            }
            return false;
        }
    }

})();;(function(){
    'use strict';
    angular
        .module('app')
        .directive('visualise', visualiseDirective);

    function visualiseDirective($http) {
        return {
            templateUrl: base_url + 'assets/vocabs/templates/visualise.html',
            scope: {
                vocabid: '='
            },
            link: function (scope) {
                scope.treeclass = 'classic-tree';
                $http.get(base_url + 'vocabs/services/vocabs/' + scope.vocabid + '/tree')
                    .then(function (response) {
                        scope.tree = response.data.message;
                    });
            }
        }
    }
})();
