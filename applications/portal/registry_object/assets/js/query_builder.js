app.controller('QueryBuilderCtrl', function ($scope, $log, LZString ) {

    var data = '{"group":{"root": true, "operator":"AND","rules":[{"group":{"operator":"AND","rules":[{"condition":":","field":"fulltext","data":"","$$hashKey":"064"},{"condition":":","field":"fulltext","data":""}]},"$$hashKey":"05Z"}]}}';

    function htmlEntities(str) {
        return String(str).replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function computed(group) {
        if (!group || group.rules.length == 0) return "";
        
        // if(!group.root) {
        //     var hasdata = true;
        //     for (var i=0;i < group.rules.length; i++) {
        //         if (group.rules[i].data!='') {
        //             hasdata = false;
        //         }
        //     }
        //     if (!hasdata) return "";
        // }
        
        for (var str = "", i = 0; i < group.rules.length; i++) {
            if(group.rules[i].data!='' && group.rules[i]!==undefined){
                i > 0 && (str += " " + group.operator + " ");
                if(group.rules[i].group) {
                    str += computed(group.rules[i].group)
                } else {
                    if(group.rules[i].condition=='-') {
                        if(group.rules[i].data.indexOf(' ') > -1) {
                            str += '-' + group.rules[i].field + ':' + '('+group.rules[i].data+')';
                        } else {
                            str += '-' + group.rules[i].field + ':' + group.rules[i].data;
                        }
                    }else {
                        if(group.rules[i].data.indexOf(' ')> -1) {
                            str += group.rules[i].field + "" + htmlEntities(group.rules[i].condition) + "" + '('+group.rules[i].data+')';
                        } else {
                            str += group.rules[i].field + "" + htmlEntities(group.rules[i].condition) + "" + group.rules[i].data;
                        }
                    }
                }
            }
        }

        return str + "";
    }

    $scope.json = null;

    $scope.filter = JSON.parse(data);

    $scope.$on('query', function(e, data){
        $scope.filter = $scope.parse(data);
    });

    $scope.$on('cq', function(e, data){
        $scope.filter = JSON.parse(LZString.decompressFromEncodedURIComponent(data));
    });

    $scope.$on('clearSearch', function(e){
        $scope.filter = JSON.parse(data);
    });

    $scope.parse = function(data){
        if (data.query.indexOf('(')==0) {
            data.query = data.query.substr(1);
            data.query = data.query.substr(0, data.query.length-1);
        }
        var ndata = {};
        ndata.group = {'operator': 'AND', 'rules':[]};
        return ndata;
    }

    $scope.convertType = function(type) {
        switch(type) {
            case 'q': return 'fulltext';break;
        }
        return type;
    }

    $scope.$watch('filter', function (newValue) {
        $scope.json = JSON.stringify(newValue, null, 0);
        $scope.output = computed(newValue.group);
        // $log.debug($scope.json, $scope.output);
        if ($scope.output!='()' && $scope.output!="" && $scope.output!='(())'){
            $scope.$emit('changePreFilter', {type:'cq', value:LZString.compressToEncodedURIComponent($scope.json),execute:false});
            $scope.$emit('changePreQuery', $scope.output);
        } else {
            $scope.$emit('changePreQuery', '');
        }
    }, true);

});

var queryBuilder = angular.module('queryBuilder', []);
queryBuilder.directive('queryBuilder', ['$compile', function ($compile, $log, search_factory) {
    return {
        restrict: 'E',
        scope: {
            group: '='
        },
        templateUrl: base_url+'assets/registry_object/templates/querybuilder.html',
        compile: function (element, attrs) {
            var content, directive;
            content = element.contents().remove();
            return function (scope, element, attrs) {
                scope.operators = [
                    { name: 'AND' },
                    { name: 'OR' }
                ];

                scope.fields = [
                    { name: 'fulltext', display: 'All Fields'},
                    { name: 'title_search', display: 'Title'},
                    { name: 'identifier_value_search', display: 'Identifier'},
                    { name: 'related_party_one_search', display: 'Related People'},
                    { name: 'related_party_multi_search', display: 'Related Organisation'},
                    { name: 'description_value', display: 'Description'}
                ]

                scope.conditions = [
                    { name: ':', display:'Contains' },
                    { name: '-', display:'Excludes'}
                ];

                scope.addCondition = function () {
                    scope.group.rules.push({
                        condition: ':',
                        field: 'fulltext',
                        data: ''
                    });
                };

                scope.removeCondition = function (index) {
                    scope.group.rules.splice(index, 1);
                };

                scope.addGroup = function () {
                    scope.group.rules.push({
                        group: {
                            operator: 'AND',
                            rules: [
                                {condition:":", field:'fulltext', data:''}
                            ]
                        }
                    });
                };

                scope.removeGroup = function () {
                    "group" in scope.$parent && scope.$parent.group.rules.splice(scope.$parent.$index, 1);
                };

                directive || (directive = $compile(content));

                element.append(directive(scope, function ($compile) {
                    return $compile;
                }));
            }
        }
    }
}]);
queryBuilder.filter('getDisplayFor', function($log){
    return function(value, filter) {
        var ret = '';
        angular.forEach(filter, function(f){
            if(f.name==value) {
                ret = f.display;
            }
        });
        return ret;
    }
});