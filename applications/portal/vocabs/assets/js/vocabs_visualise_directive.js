(function(){
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
                        if(scope.tree.length>1){$("#concept").hide();}
                    });
            }
        }
    }
})();
