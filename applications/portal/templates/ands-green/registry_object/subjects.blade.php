@extends('layouts/left-sidebar-subject')

@section('content')

<div class="panel element-no-top element-small-bottom animated slideInleft" ng-if="hasFilter('anzsrc-for')">

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
<div class="panel panel-primary panel-green element-no-top element-no-bottom os-animation animated fadeInUp">
    <div class="panel-heading">
        <h3 class="panel-title"><span tip="Australia and New Zealand Standard Research Classification - Fields of Research (ANZSRC-FOR).
        <a href='http://www.abs.gov.au/ausstats/abs@.nsf/Latestproducts/1297.0Main%20Features32008?opendocument&tabname=Summary&prodno=1297.0&issue=2008'>Learn more...</a>">ANZSRC-FOR Subjects</span></h3>
    </div>

    <!-- Subject Facet -->
    <div class="panel-body swatch-white">

        <span ng-if="!vocab_tree">
            <i class="fa fa-refresh fa-spin"></i> Loading...
        </span>

        <ul class="tree animated fadeInUp" ng-if="vocab_tree" ng-cloak>
            <li ng-repeat="item in vocab_tree | orderObjectBy:'prefLabel'"
                ng-class="{true:'selected'}[isVocabSelected(item)]">
                <!-- <a href="" ng-click="getSubTree(item)">
                    <i class="fa"
                        ng-class="{undefined: 'fa-plus', false: 'fa-plus', true: 'fa-minus'}[item.showsubtree]"
                        ng-if="item.has_narrower"></i>
                </a> -->
                <a href="" ng-click="toggleSubject(item)" ng-if="item.collectionNum > 0">
                    [[item.prefLabel | toTitleCase]] ([[ item.collectionNum ]])
                </a>
                <ul ng-if="item.subtree && item.showsubtree">
                    <li ng-repeat="item2 in item.subtree"
                        ng-class="{true:'selected'}[isVocabSelected(item2)]">
                        <!-- <a href="" ng-click="getSubTree(item2)">
                            <i class="fa"
                                ng-class="{undefined: 'fa-plus', false: 'fa-plus', true: 'fa-minus'}[item2.showsubtree]"
                                ng-if="item2.has_narrower"></i>
                        </a> -->
                        <a href="" ng-click="toggleSubject(item2)" ng-if="item2.collectionNum > 0">
                            [[item2.prefLabel | toTitleCase]] ([[ item2.collectionNum ]])
                        </a>
                        <ul ng-if="item2.subtree && item2.showsubtree">
                            <li ng-repeat="item3 in item2.subtree"
                                ng-class="{true:'selected'}[isVocabSelected(item3)]">
                                <a href="" ng-click="toggleSubject(item3)" ng-if="item3.collectionNum > 0">
                                    [[item3.prefLabel | toTitleCase]] ([[ item3.collectionNum ]])
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>

    </div>
    @if(\ANDS\Util\config::get('app.ardc_campaign')=="TRUE")
        <div class="padding-top">
            <a href="[[setMyVariable(filters).link]]" style="margin-right:5px;">
                <img src=" [[setMyVariable(filters).image]]" />
            </a>
        </div>
    @endif
</div>

@stop
