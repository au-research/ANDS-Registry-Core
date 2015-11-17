@extends('layouts/left-sidebar-subject')

@section('content')

<div class="panel element-no-top element-small-bottom animated slideInleft">

    <div class="panel-body swatch-white" ng-if="fuzzy" ng-cloak>
        Your search for '<b>[[ query ]]</b>' returned 0 results. Below are some alternatives which closely match your query.
    </div>

    <div class="panel-body swatch-white" style="padding:10px" ng-cloak ng-show="result">
        <a href="" class="btn btn-link btn-sm" ng-click="toggleResults()" ng-if="result.response.numFound > 0">
            <span ng-show="selectState=='deselectAll'"><i class="fa fa-check-square-o"></i> Deselect All</span>
            <span ng-show="selectState=='selectAll'"><i class="fa fa-square-o"></i> Select All</span>
            <span ng-show="selectState=='deselectSelected'"><i class="fa fa-minus-square-o"></i> Deselect Selected</span>
        </a>
        <nav ng-hide="loading" ng-cloak class="pull-right">
            <ul class="pagi">
                <li><small>Page [[ page.cur ]] / [[ page.end ]]</small></li>
                <li ng-if="page.cur!=1"><a href="" ng-click="goto(1)"><span aria-hidden="true">&laquo;</span><span class="sr-only">First</span></a></li>
                <li ng-repeat="x in page.pages" ng-class="{'active':page.cur==x}"><a href="" ng-click="goto(x)">[[x]]</a></li>
                <li ng-if="page.cur!=page.end"><a href="" ng-click="goto(page.end)"><span aria-hidden="true">&raquo;</span><span class="sr-only">Last</span></a></li>
            </ul>
        </nav>
    </div>

    <div class="panel-body swatch-white" ng-if="result.response.docs.length < 1" ng-cloak>
        <p>Your search did not return any results</p>
        <p>Some suggestion for searching in Research Data Australia</p>
        <ul>
            <li>Make sure that all words are spelled correctly</li>
            <li>Try using different or more general search keywords</li>
            <li>Try beginning with a broad search, and then gradually apply filters and keywords to narrow down your results.</li>
        </ul>
    </div>

    <div ng-repeat="doc in result.response.docs" ng-if="!doc.hide" style="border-bottom:1px solid #eaeaea" class="panel-body swatch-white os-animation animated fadeInLeft sresult" ng-cloak>
        <div class="container-fluid">
            <div class="row">

                <div class="element-no-top element-no-bottom" ng-class="{'col-md-8':filters.spatial}">
                    <div class="stoolbar">
                        <input type="checkbox" ng-model="doc.select" ng-change="toggleResult(doc)">
                    </div>
                    <div class="pull-right" ng-if="filters.class=='activity'">
                        [[ doc.type | toTitleCase ]]
                    </div>
                    <div class="scontent" myWidth>
                        <h2 class="post-title">
                            <a href="{{base_url()}}[[doc.slug]]/[[doc.id]]/?refer_q=[[filters_to_hash()]]" ng-bind-html="doc.title"></a>
                        </h2>
                        <p><small>[[doc.group]]</small></p>
                        <p ng-if="doc.matching_identifier_count">[[ doc.matching_identifier_count ]] Linked Records</p>
                        <p ng-if="doc.identifiermatch">
                            <ul>
                                <li ng-repeat="idd in doc.identifiermatch">
                                    <a href="[[base_url]][[idd.slug]]/[[idd.registry_object_id]]">[[idd.title]]</a>
                                    <small>(Contributor: [[idd.group]])</small>
                                </li>
                            </ul>
                        </p>

                        <div ng-repeat="(index, content) in getHighlight(doc.id)" class="element-shorter-bottom">
                            <div ng-repeat="c in content track by $index" class="element-shortest-bottom">
                                <span ng-bind-html="c | extractSentence | trustAsHtml" ng-if="index=='description_value'"> </span>
                                <span ng-bind-html="c | trustAsHtml" ng-if="index!='description_value'"> </span> <span class="muted">(in [[index | highlightreadable]])</span>
                            </div>
                        </div>

                        <p ng-if="getHighlight(doc.id)===false && doc.list_description">
                            [[ doc.list_description | text | truncate:500 ]]
                        </p>
                        <p ng-if="getHighlight(doc.id)===false && !doc.list_description && doc.description">
                            [[ doc.description | text | truncate:500 ]]
                        </p>
                        <div ng-if="doc.administering_institution">
                            <b>Managing Institution</b>: [[ doc.administering_institution.join(',') | toTitleCase ]]
                        </div>
                        <div ng-if="doc.researchers" class="oneLineTruncate" tip="[[doc.researchers.join(', ')]]">
                            <b>Researchers: </b> [[doc.researchers.join(', ')]]
                        </div>
                    </div>

                   <!--  <p data-ng-bind-html="doc.description" ng-show="!doc.hl"></p> -->
                </div>
                <div class="col-md-4" ng-if="filters.spatial">
                    <div mappreview sbox="filters.spatial" centres="doc.spatial_coverage_centres" polygons="doc.spatial_coverage_polygons" draw="'static'"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel-body swatch-white" style="padding:10px" ng-cloak>
        <small ng-hide="loading" ng-cloak class="pull-left"><b>[[result.response.numFound]]</b> results ([[result.responseHeader.QTime]] milliseconds)</small>
        <nav ng-hide="loading" ng-cloak class="pull-right">
            <ul class="pagi">
                <li><small>Page [[ page.cur ]] / [[ page.end ]]</small></li>
                <li ng-if="page.cur!=1"><a href="" ng-click="goto(1)"><span aria-hidden="true">&laquo;</span><span class="sr-only">First</span></a></li>
                <li ng-repeat="x in page.pages" ng-class="{'active':page.cur==x}"><a href="" ng-click="goto(x)">[[x]]</a></li>
                <li ng-if="page.cur!=page.end"><a href="" ng-click="goto(page.end)"><span aria-hidden="true">&raquo;</span><span class="sr-only">Last</span></a></li>
            </ul>
        </nav>
    </div>
</div>
<div class="clear"></div>
@stop

@section('sidebar')
<div class="panel panel-primary panel-green element-no-top element-no-bottom os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0.2s" style="-webkit-animation: 0.2s;">
    <div class="panel-heading">
        <h3 class="panel-title"><span tip="Australia and New Zealand Standard Research Classification - Fields of Research (ANZSRC-FOR). <a href='http://google.com'>Learn more...</a>">Subjects</span></h3>
    </div>
    <div facet-search facets="facets" type="type" ng-if="showFacet('type')"></div>
    <div facet-search facets="facets" type="activity_status" ng-if="showFacet('activity_status')"></div>

    <!-- Subject Facet -->
    <div class="panel-body swatch-white" ng-if="showFacet('subjects')">


            <ul class="tree" ng-if="vocab_tree" ng-cloak>
                <li ng-repeat="item in vocab_tree | orderObjectBy:'prefLabel'">
                    <span class="caret" ng-click="getSubTree(item)"> </span><input type="checkbox" ng-checked="isVocabSelected(item, filters)" ui-indeterminate="isVocabParentSelected(item)" ng-click="toggleFilter(vocab, item.notation, true);">
                    <a href="" ng-click="getSubTree(item)" ng-if="item.has_narrower">[[item.prefLabel | toTitleCase]] ([[ item.collectionNum ]])</a>
                    <span ng-click="getSubTree(item)" ng-if="!item.has_narrower">[[item.prefLabel | toTitleCase]] ([[ item.collectionNum ]])</span>
                    <ul ng-if="(isVocabSelected(item, filters)) || isVocabParentSelected(item)">
                        <li ng-repeat="item2 in item.subtree">
                            <span class="caret" ng-click="getSubTree(item2)"> </span><input type="checkbox" ng-checked="isVocabSelected(item2, filters)" ui-indeterminate="isVocabParentSelected(item2)" ng-click="toggleFilter(vocab, item2.notation, true);">
                            <a href="" ng-click="getSubTree(item2)" ng-if="item2.has_narrower">[[item2.prefLabel | toTitleCase]] ([[ item2.collectionNum ]])</a>
                            <span ng-if="!item2.has_narrower">[[item2.prefLabel | toTitleCase]] ([[ item2.collectionNum ]])</span>
                            <ul ng-if="item2.subtree && item2.showsubtree">
                                <li ng-repeat="item3 in item2.subtree">
                                    <input type="checkbox" ng-checked="isVocabSelected(item3, filters)" ui-indeterminate="isVocabParentSelected(item3, filters)" ng-click="toggleFilter(vocab, item3.notation, true);">
                                    <a href="" ng-click="getSubTree(item3)" ng-if="item3.has_narrower">[[item3.prefLabel | toTitleCase]] ([[ item3.collectionNum ]])</a>
                                    <span ng-if="!item3.has_narrower">[[item3.prefLabel | toTitleCase]] ([[ item3.collectionNum ]])</span>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>

    </div>
</div>

@stop
