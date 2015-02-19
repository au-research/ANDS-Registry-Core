app.controller('QueryBuilderCtrl', function ($scope, $log) {
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
        var ndata = {};
        ndata.group = {'operator':'AND', 'rules':[]};

        if(data.query.indexOf('AND')>-1) {
            var literals = data.query.split('AND');
            angular.forEach(literals, function(literal){
                ndata.group.rules.push({
                    condition:':',
                    field:data.search_type,
                    data:literal
                });
            });
        } else {
            ndata.group.rules.push({
                condition:':',
                field:data.search_type,
                data:data.query
            });
        }
        $scope.filter = ndata;
        // $log.debug($scope.filter);
    });

    $scope.$on('cq', function(e, data){
        var ndata = {};
        ndata.group = {'operator':'AND', 'rules':[]};
        data = data.substr(1);
        data = data.substr(0, data.length-1);

        if(data.indexOf('AND')>-1) {
            var pairs = data.split('AND');
            angular.forEach(pairs, function(pair){
                var literals = pair.split(':');
                ndata.group.rules.push({
                    condition:':',
                    field:literals[0],
                    data:literals[1]
                });
            });
        } else {
            var literals = data.split(':');
            ndata.group.rules.push({
                condition:':',
                field:literals[0],
                data:literals[1]
            });
        }
        $scope.filter = ndata;

        $log.debug(data);
    });

    $scope.$watch('filter', function (newValue) {
        $scope.json = JSON.stringify(newValue, null, 2);
        $scope.output = computed(newValue.group);
        if ($scope.advanced_mode){
            $scope.$emit('changeFilter', {type:'cq', value:$scope.output,execute:false});
        }
    }, true);

    $scope.$on('addCondition', function(e, data){
        // $log.debug($scope.filter);
        $scope.advanced_mode = true;
    });

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
                   scope.$emit('addCondition');
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