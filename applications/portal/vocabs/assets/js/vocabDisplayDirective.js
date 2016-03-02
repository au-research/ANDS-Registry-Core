/**
 * Created by leomonus on 19/02/16.
 */
(function () {
    'use strict';


    angular
        .module('app')
        .directive('widgetDisplayDirective', widgetDisplayDirective);

    function widgetDisplayDirective() {
        return {
            restrict: 'AE',
            scope : {
                vocab: '='
            },
            templateUrl: base_url + 'assets/vocabs/templates/widgetVocabDisplay.html',
            link: function (scope) {

                scope.widgetModes = [{name:'tree', label:'Tree View', selected:'selected'},{name:'search', label:'Search Mode', selected:''}];
//,{name:'narrow', label:'Narrow Mode'}

                scope.target_field

                scope.mode = "tree";

                scope.target_field = "label";

                scope.$watch('vocab' , function (newVal, oldVal){
                    if(newVal){
                        scope.concept = null;
                        resetVocabWidget(true);
                    }

                });

                scope.$watch('target_field' , function (newVal, oldVal){
                    if(newVal)
                        resetVocabWidget(false);
                });

                scope.switchMode = function(newMode) {
                    scope.mode = newMode;
                    if(scope.vocab){
                        scope.concept = null;
                        resetVocabWidget(true);

                    }
                }





                function resetVocabWidget(clearField){
                    if(scope.vocab){

                        var subjectValueInput = $('#sampleWidgetInput');
                        var sissvoc_end_point = scope.vocab.sissvoc_end_point;


                        if(clearField){
                            subjectValueInput.val("");
                        }
                        $('.vocab_list').remove();
                        $('.vocab_tree').remove();
                        scope.endpoint = 'https://devl.ands.org.au/workareas/leo/ANDS-Registry-Core/apps/vocab_widget/proxy/';
                        if(scope.mode == 'tree'){

                            $(subjectValueInput).qtip({
                                content:{text:'<div class="subject_chooser"></div>'},
                                prerender:true,
                                position:{
                                    my:'center left',
                                    at: 'center right',
                                    viewport:$(window)
                                },
                                show: {event: 'click',ready:false},
                                hide: {event: 'unfocus'},
                                events: {
                                    render: function(event, api) {
                                        scope.widget = $(".subject_chooser", this).vocab_widget({mode:scope.mode, repository:sissvoc_end_point, endpoint:scope.endpoint, display_count:false});

                                        scope.widget.on('treeselect.vocab.ands', function(event) {
                                            var target = $(event.target);
                                            var data = target.data('vocab');

                                            angular.forEach(data, function(val,key){
                                                if(key == scope.target_field)
                                                    subjectValueInput.val(val);
                                            });

                                            scope.$apply(function() {
                                                scope.concept = data;
                                            });
                                        });
                                        scope.widget.on('error.vocab.ands', function(event, data) {
                                            alert("This vocabulary is not suited for tree browsing most likely no top concept(s) are defined");
                                        });

                                        api.elements.content.find('.hasTooltip').qtip('repopsition');
                                        api.elements.content.find('.hasTooltip').qtip('update');
                                    }
                                },
                                style: {classes: 'qtip-bootstrap ui-tooltip-shadow ui-tooltip-bootstrap ui-tooltip-large'}
                            });
                        }
                        else{

                            scope.widget = subjectValueInput.vocab_widget({mode:scope.mode, repository:sissvoc_end_point, endpoint:scope.endpoint, target_field: scope.target_field});
                            scope.widget.on('searchselect.vocab.ands', function(event, data) {
                                scope.$apply(function() {
                                    scope.concept = data;
                                });
                            });

                        }
                    }
                }

            }
        }


    }

})();
