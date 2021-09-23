/**
 * Implementation of the "subject-directive" attribute,
 * used for subject entry on the vocabs CMS page.
 */
(function () {
    'use strict';

    angular
        .module('app')
        .directive('subjectDirective', subjectDirective);

    function subjectDirective()
    {
        return {
            restrict: 'AE',
            scope: {
                // Isolated scope. The following are visible in
                // the local scope.
                subjectType: '=',
                subjectLabel: '=',
                subjectNotation: '=',
                subjectIri: '=',
                index: '=',
                close: '&onClose'
            },
            templateUrl: (base_url
                          + 'assets/vocabs/templates/subjectDirective.html'),
            link: function (scope, elem) {
                // Set the vocab proxy. Do this up-front:
                // the setting can get used fairly early on because
                // of triggering of the event watchers.
                // First, set a default. This value is taken from
                // applications/apps/vocab_widget/assets/js/vocab_widget.js
                scope.subject_vocab_proxy_setting =
                    'https://researchdata.ands.org.au/apps/vocab_widget/proxy/';
                // Then, see if there is an override present.
                if (typeof subject_vocab_proxy === 'string') {
                    scope.subject_vocab_proxy_setting = subject_vocab_proxy;
                }

                // See vocabs_cms.js for a description of the format
                // of subject_sources.
                // This gives us a shortcut to the (shared) value
                // from the parent scope.
                // NB: This is used in subjectDirective.html to
                // populate the subject source dropdown!
                scope.subject_sources = scope.$parent.subject_sources;

                // Invoke this after a change to the parent scope's
                // vocabs.subjects setting, e.g., after a deletion.
                // This forces an update of the dropdown.
                scope.setSelected = function() {
                    var oldSelected = scope.selected;
                    scope.selected = undefined;
                    for (var v in scope.subject_sources) {
                        if (scope.subject_sources[v].id ===
                            scope.subjectType) {
                            scope.selected = scope.subject_sources[v];
                            break;
                        }
                    }
                    if (oldSelected !== scope.selected) {
                        // The value changed. Redo the subject
                        // label widget.
                        if (oldSelected !== undefined) {
                            removeSubjectWidget(elem);
                        }
                        if (scope.selected !== undefined) {
                            initSubjectWidget(elem, scope);
                        }
                    }
                }
                // And call it now, to set selected and initialize
                // the subject label widget.
                scope.setSelected();

                // The model for the subject source dropdown
                // is "selected", not "subjectType".
                // So we need to set the subject type explicitly after making
                // a selection from the dropdown.
                scope.setVocab = function() {
                    if (scope.selected == null) {
                        // User set the dropdown back to the placeholder.
                        scope.subjectType = '';
                    } else {
                        scope.subjectType = scope.selected.id;
                    }
                }

                // And conversely, set a watch on the subjectType value
                // to update scope.selected.
                scope.$watch('subjectType', function(newValue, oldValue) {
                    if (newValue !== oldValue) {
                        scope.setSelected();
                    }
                });

                // Locate the subject source dropdown.
                var subjectTypeInput = $(elem).find('.subject-type');
                // And set a watch on it.
                subjectTypeInput.on(
                    "change",
                    function (e) {
                        // Wipe out any existing label. Note use of
                        // $apply to make sure AngularJS notices the
                        // changes to model values.
                        scope.$apply(
                            function () {
                                scope.subjectLabel = '';
                                scope.subjectNotation = '';
                                scope.subjectIri = '';
                            });
                        // Wipe out any existing subject label widget.
                        removeSubjectWidget(elem);
                        // And create an appropriate new one.
                        initSubjectWidget(elem, scope);
                    }
                );

                // Remove any existing subject label widget.
                function removeSubjectWidget(elem) {
                    $(elem).find('.vocab_list').remove();
                    var tipapi =
                        $(elem).find('.subject-label').qtip('api')
                    if ((tipapi !== undefined) && (tipapi !== null)) {
                        var tip = $(tipapi.elements.tooltip);
                        tip.find('.vocab_tree').remove();
                        tipapi.destroy(true);
                    }
                }

                // Add the appropriate subject label widget.
                function initSubjectWidget(elem, scope)
                {
                    var selected = scope.selected;
                    var subjectType = scope.subjectType;
                    var subjectValueInput = $(elem).find('.subject-label');

                    var vocab = subjectType;

                    var vocab_term = subjectValueInput.val();
                    // TODO: Please remove the hard-coding of vocab
                    // names (i.e., testing for 'anzsrc-for', etc.
                    // We are probably almost there now;
                    // we can distinguish the required behaviour
                    // based on the mode (tree/search/freetext).
                    if (vocab == 'anzsrc-for' || vocab == 'anzsrc-seo' || vocab == 'anzsrc-for-2020' || vocab == 'anzsrc-seo-2020') {
                        $(subjectValueInput).qtip(
                            {
                                content: {
                                    text:
                                    '<div class="subject_chooser"></div>'
                                },
                                prerender: true,
                                position: {
                                    my: 'center left',
                                    at: 'center right',
                                    adjust: {
                                        mouse: false,
                                        method: 'shift'
                                    },
                                    viewport: $(window)
                                },
                                show: {event: 'click',
                                    ready: false},
                                hide: {event: 'unfocus'},
                                events: {
                                    render: function (
                                        event,
                                        api
                                    ) {
                                        $(".subject_chooser",
                                          this).vocab_widget({
                                              endpoint:
                                                scope.subject_vocab_proxy_setting,
                                              mode: selected.mode,
                                              repository:
                                                selected.resolvingService,
                                              display_count: false
                                          })
                                        .on(
                                            'treeselect.vocab.ands',
                                            function (event) {
                                                var target = $(event.target);
                                                var data   = target.data('vocab');
                                                scope.$apply(
                                                    function () {
                                                        scope.subjectLabel =
                                                            data.label;
                                                        scope.subjectNotation
                                                            = data.notation;
                                                        scope.subjectIri =
                                                            data.about;
                                                    }
                                                );
                                            }
                                        );
                                        api.elements.content.find(
                                            '.hasTooltip').qtip('reposition');
                                        api.elements.content.find(
                                            '.hasTooltip').qtip('update');
                                    }
                                },
                                style: {classes:
                                   'qtip-bootstrap ui-tooltip-shadow ui-tooltip-bootstrap ui-tooltip-large'}
                            }
                        );
                    } else if (vocab == 'GCMD') {


                        $(subjectValueInput).qtip(
                            {
                                content: {
                                    text:
                                    '<div id="subject_widget_' + scope.index + '" class="subject_chooser">' +
                                        '<input id="subject_widget_search_' + scope.index + '" type="text" ' +
                                        'class="form-control subject-value" placeholder="Search"/></div>'
                                },
                                position: {
                                    my: 'top right',
                                    at: 'bottom right',
                                    adjust: {
                                        mouse: false,
                                        method: 'shift'
                                    },
                                    viewport: $(window)
                                },
                                show: {event: 'click',
                                       ready: false},
                                hide: {event: 'unfocus'},
                                events: {
                                    render: function (event, api) {
                                        var subjectValueInputText =
                                            $('#subject_widget_search_'
                                              + scope.index);
                                        $(subjectValueInputText).vocab_widget({
                                            endpoint:
                                             scope.subject_vocab_proxy_setting,
                                            mode: selected.mode,
                                            repository:
                                              selected.resolvingService,
                                            target_field: 'label'});
                                        // Track changes made by the widget.
                                        subjectValueInputText.on(
                                            "searchselect.vocab.ands",
                                            function (e, data) {
                                                scope.$apply(
                                                    function () {
                                                        // Feed the modified
                                                        // value back
                                                        // into the model.
                                                        scope.subjectLabel =
                                                            data.label;
                                                        scope.subjectIri =
                                                            data.about;
                                                    });
                                            }
                                        );
                                    },
                                    visible: function (event, api) {
                                        // Focus the input field.
                                        var subjectValueInputText =
                                            $('#subject_widget_search_'
                                              + scope.index);
                                        subjectValueInputText.focus();
                                    },
                                    hide: function (event, api) {
                                        // On hide, wipe out the contents
                                        // of the text field,
                                        // so that a future search starts with
                                        // carte blanche.
                                        var subjectValueInputText =
                                            $('#subject_widget_search_'
                                              + scope.index);
                                        subjectValueInputText.val('');
                                    }
                                },
                                style: {
                                    classes:
                                      'qtip-bootstrap ui-tooltip-shadow ui-tooltip-bootstrap ui-tooltip-large cms-search-widget-tip'}
                            }
                        );
                    } else {
                        subjectValueInput.qtip("destroy");
                    } // end if
                }
            }
        }
    }
})();
