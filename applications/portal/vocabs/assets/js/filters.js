(function () {
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
        .filter('removeSearchTail', function(){
            return function (text) {
                return text.replace("_search", "");
            }
        })
    ;
})();