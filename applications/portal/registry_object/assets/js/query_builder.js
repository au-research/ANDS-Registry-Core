app.controller('QueryBuilderCtrl', function ($scope, $log, LZString ) {

    var data = '{"group": {"operator": "AND","rules": []}}';

    function htmlEntities(str) {
        return String(str).replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function computed(group) {
        if (!group) return "";
        for (var str = "(", i = 0; i < group.rules.length; i++) {
            i > 0 && (str += " " + group.operator + " ");
            str += group.rules[i].group ?
                computed(group.rules[i].group) :
                group.rules[i].field + "" + htmlEntities(group.rules[i].condition) + "" + group.rules[i].data;
        }

        return str + ")";
    }

    $scope.json = null;

    $scope.filter = JSON.parse(data);
    $scope.advanced_mode = false;

    $scope.$on('query', function(e, data){
        $scope.filter = $scope.parse(data);
    });

    $scope.$on('cq', function(e, data){
        // var string = "This is my compression test.";
        // $log.debug("Size of sample is: " + string.length);
        // var compressed = LZString.compress(string);
        // $log.debug(compressed);
        // $log.debug("Size of compressed sample is: " + compressed.length);
        // string = LZString.decompress(compressed);
        // $log.debug("Sample is: " + string);
        // $log.debug(data);
        // $log.debug(LZString.decompress('N4Ig5gTg9grgDiAXKKcCmECGAXKEkgCCAcgCIgA0IEMANmgM5IDaoAxlAHYAmAltry4FElEADNeaWtwJi6tbGgAe2UdxyYCARgBMAZlEASQwAtMDEwGk0ATwJQARgCs0bbIj0BWLSAC+Fdi4+ASERESoJKRkRAWx6AH0GNEwINhM1DW19I1NzK1t7Z1d3ABY9AA4'));
        // $scope.filter = LZString.decompressFromBase64(data);
        // $log.debug($scope.filter);
        // $log.debug(LZString.decompressFromEncodedURIComponent(data));
        $scope.filter = JSON.parse(LZString.decompressFromEncodedURIComponent(data));
    });

    $scope.parse = function(data){
        if (data.query.indexOf('(')==0) {
            data.query = data.query.substr(1);
            data.query = data.query.substr(0, data.query.length-1);
        }
        // $log.debug(data.query);
        var ndata = {};
        ndata.group = {'operator': 'AND', 'rules':[]};
        // if(data.query.indexOf('AND')>-1) {
        //     ndata.group = {'operator':'AND', 'rules':[]};
        //     var literals = data.query.split('AND');
        //     angular.forEach(literals, function(literal){
        //         if(literal.indexOf(':')>-1) {
        //             var pair = literal.split(':');
        //             $log.debug(pair);
        //             ndata.group.rules.push({
        //                 condition:':',
        //                 field:$scope.convertType(pair[0]).trim(),
        //                 data:pair[1].trim()
        //             });
        //         } else {
        //             ndata.group.rules.push({
        //                 condition:':',
        //                 field:$scope.convertType(data.search_type),
        //                 data:literal
        //             });
        //         }
        //     });
        // } else if(data.query.indexOf('OR')>-1){
        //     ndata.group = {'operator':'OR', 'rules':[]};
        //     var literals = data.query.split('OR');
        //     angular.forEach(literals, function(literal){
        //         ndata.group.rules.push({
        //             condition:':',
        //             field:$scope.convertType(data.search_type),
        //             data:literal
        //         });
        //     });
        // } else {
        //     ndata.group.rules.push({
        //         condition:':',
        //         field:$scope.convertType(data.search_type),
        //         data:data.query
        //     });
        // }
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
        if ($scope.output!='()'){
            $scope.$emit('changeFilter', {type:'cq', value:LZString.compressToEncodedURIComponent($scope.json),execute:false});
            $scope.$emit('changeQuery', $scope.output);
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
                    { name: 'fulltext'},
                    { name: 'title_search'},
                    { name: 'identifier_value_search'},
                    { name: 'related_party_one_search'},
                    { name: 'related_party_multi_search'},
                    { name: 'description_value'}
                ];

                scope.conditions = [
                    { name: ':' }
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
                            rules: []
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