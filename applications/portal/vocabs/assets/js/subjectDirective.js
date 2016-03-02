/**
 * File:  subjectDirective
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
(function () {
    'use strict';


    angular
        .module('app')
        .directive('subjectDirective', subjectDirective);

    function subjectDirective() {
        return {
            restrict: 'AE',
            scope : {
                subjectType: '=',
                subjectLabel : '=',
                subjectNotation : '=',
                subjectUri : '=',
                index: '=',
                length: '='
            },
            templateUrl: base_url + 'assets/vocabs/templates/subjectDirective.html',
            link: function (scope, elem) {

                scope.items = [];


                scope.$watch('length', function(newv){
                    scope.vocabSubjectLength = newv;
                });


                var subjectTypeInput = $(elem).find('.subject-type');
                var widget = subjectTypeInput.vocab_widget({mode:'advanced'});
                subjectTypeInput.on('narrow.vocab.ands', function(event, data) {
                    $.each(data.items, function(idx, e) {

                        if(e.notation == scope.subjectType){
                           subjectTypeInput.selected = scope.subjectType;
                            console.log(scope.subjectType);
                        }

                        scope.items.push({id: e.notation, label: e.label});

                    });

                    scope.$apply(function(){

                        angular.forEach(scope.items, function(item){
                            if (item.id == scope.subjectType) {
                                scope.selected = item;
                            }
                        });
                    });


                    subjectTypeInput.off().on("change",function(e){
                        subjectTypeInput.selected = scope.subjectType;
                        initSubjectWidget(elem);
                    });

                    //initSubjectWidget(elem);
                });

                subjectTypeInput.on('error.vocab.ands', function(event, xhr) {
                    console.log(xhr);
                });
                scope.setVocab = function() {
                    scope.subjectType = scope.selected.id;
                    initSubjectWidget(elem, scope.subjectType);
                    //scope.subjectValue = vocab + "Should init vocab widget again for vocab ";
                }
                widget.vocab_widget('repository','rifcs1.6.1');
                widget.vocab_widget('narrow', "http://purl.org/au-research/vocabulary/RIFCS/1.6.1/RIFCSSubjectType");

                scope.deleteSubject = function () {
                    scope.$emit('removeVocabSubject', scope.index);
                }


                function initSubjectWidget(elem, subjectType){

                    var subjectValueInput = $(elem).find('.subject-value');

                    var vocab = subjectType;

                    var vocab_term = subjectValueInput.val();
                    var dataArray = Array();
                    // WE MIGHT NEED A WHITE LIST HERE

                    if(vocab == 'anzsrc-for' || vocab =='anzsrc-seo'){

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
                                    $(".subject_chooser", this).vocab_widget({mode:'tree', repository:vocab, display_count:false})
                                        .on('treeselect.vocab.ands', function(event) {
                                            var target = $(event.target);
                                            var data = target.data('vocab');
                                            //alert('You clicked ' + data.label + '\r\n<' + data.about + '>');
                                            subjectValueInput.val(data.label);
                                            scope.$apply(function() {
                                                scope.subjectLabel = data.label;
                                                scope.subjectNotation = data.notation;
                                                scope.subjectUri = data.about;
                                            });
                                        });
                                    api.elements.content.find('.hasTooltip').qtip('repopsition');
                                    api.elements.content.find('.hasTooltip').qtip('update');
                                }
                            },
                            style: {classes: 'qtip-bootstrap ui-tooltip-shadow ui-tooltip-bootstrap ui-tooltip-large'}
                        });
                    }
                    else if(vocab == 'GCMD'){
                        $(subjectValueInput).vocab_widget({mode:'search', repository:'gcmd-sci', target_field: 'label'});
                    }
                    else{
                        subjectValueInput.qtip("destroy");
                    }
                }
            }
        }





    }

})();