app.directive('visualise', function ($log, $http) {
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
});