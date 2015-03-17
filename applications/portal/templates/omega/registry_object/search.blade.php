@extends('layouts/left-sidebar-fw')

@section('content')

<div class="panel panel-primary element-no-top element-small-bottom" data-os-animation="fadeInUp">

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
        <p>The search term <b>[[ query ]]</b> did not return any results</p>
        <p>Some suggestion for searching in Research Data Australia</p>
        <ul>
            <li>Make sure that all words are spelled correctly</li>
            <li>Try using different or more general search keywords</li>
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
                        [[doc.type]]
                    </div>
                    <div class="scontent">
                        <h2 class="post-title"> <a href="{{base_url()}}[[doc.slug]]/[[doc.id]]/?refer_q=[[filters_to_hash()]]">[[doc.title]]</a> </h2>
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
                                <span ng-bind-html="c | trustAsHtml"></span> <small><b>[[index | highlightreadable]]</b></small>
                            </div>
                        </div>

                        <p ng-if="getHighlight(doc.id)===false && doc.list_description">
                            [[ doc.list_description | text | truncate:500 ]]
                        </p>
                        <p ng-if="getHighlight(doc.id)===false && !doc.list_description && doc.description">
                            [[ doc.description | text | truncate:500 ]]
                        </p>
                        <div ng-if="doc.administering_institution">
                            <b>Administering Institution</b>: [[doc.administering_institution.join(',')]]
                        </div>
                        <div ng-if="doc.researchers">
                            <b>Researchers: </b> [[doc.researchers.join(',')]]
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
<div class="panel panel-primary" ng-cloak>
    <div class="panel-heading">
        Current Search
        <div class="pull-right">
            <span classicon fclass="filters.class"></span> [[ filters.class | getLabelFor:class_choices ]]
        </div>
    </div>
    <!-- <div class="panel-body swatch-white">
        [[filters]]
    </div> -->
    <div class="panel-body swatch-white">
        <div>
            
        </div>
        <div ng-repeat="(name, value) in filters" ng-if="showFilter(name)">
            <h4 ng-if="name!='q' || (name=='q' && !filters.cq)">[[name | filter_name]]</h4>
            <h4 ng-if="name=='q' && filters.cq">Advanced Search</h4>
            <ul class="listy no-bottom" ng-show="isArray(value) && (name!='anzsrc-for' && name!='anzsrc-seo')">
                <li ng-repeat="v in value track by $index"> 
                    <a href="" ng-click="toggleFilter(name, v, true)">[[ v | truncate:30 ]]<small><i class="fa fa-remove"></i></small> </a>
                </li>
            </ul>
            <ul class="listy no-bottom" ng-show="isArray(value)===false && (name!='anzsrc-for' && name!='anzsrc-seo')">
                <li>
                    <a href="" ng-click="toggleFilter(name, value, true)">
                        <span ng-if="name!='related_party_one_id'">[[ value | truncate:30 ]]</span>
                        <span ng-if="name=='related_party_one_id'" resolve-ro roid="value">[[value]]</span>
                        <small><i class="fa fa-remove"></i></small>
                    </a>
                </li>
            </ul>
            <div resolve ng-if="name=='anzsrc-for'" subjects="value" vocab="'anzsrc-for'"></div>
            <div resolve ng-if="name=='anzsrc-seo'" subjects="value" vocab="'anzsrc-seo'"></div>
        </div>
        <div class="panel-body swatch-white">
            <a href="" class="btn btn-primary" ng-click="add_user_data('saved_search')"><i class="fa fa-save"></i> Save Search</a>
            <a href="" class="btn" ng-click="clearSearch()">Clear Search</a>
        </div>
    </div>
</div>
<div class="panel panel-primary panel-green element-no-top element-no-bottom os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0.2s" style="-webkit-animation: 0.2s;">
    <div class="panel-heading">
        <h3 class="panel-title">Refine search results</h3>
    </div>
    <div class="panel-body swatch-white">
        <form ng-submit="addKeyWord(extra_keywords)">
            <div class="input-group">
                <input type="text" value="" name="s" class="form-control" placeholder="Add more keywords" ng-model="extra_keywords">
                <span class="input-group-btn">
                    <button class="btn" type="submit" value="Search">
                        <i class="fa fa-search"></i> Go
                    </button>
                </span>
            </div>
        </form>
    </div>

    <div facet-search facets="facets" type="type" ng-if="showFacet('type')"></div>
    <div facet-search facets="facets" type="activity_status" ng-if="showFacet('activity_status')"></div>

    <!-- Subject Facet -->
    <div class="panel-body swatch-white" ng-if="showFacet('subjects')">
        <h4>Subjects</h4>
        <ul class="listy">
          <li ng-repeat="item in vocab_tree | orderBy:'pos' | orderBy:prefLabel | limitTo:5">
            <input type="checkbox" ng-checked="isVocabSelected(item)" ui-indeterminate="isVocabParentSelected(item)" ng-click="toggleFilter('anzsrc-for', item.notation, true)">
            <a href="" ng-click="toggleFilter('anzsrc-for', item.notation, true)">
                [[ item.prefLabel | toTitleCase | truncate:30 ]]
                <small>[[item.collectionNum]]</small>
            </a>
          </li>
            <li><a href="" ng-click="advanced('subject')">View More</a></li>
        </ul>
    </div>

    <div facet-search facets="facets" type="group" ng-if="showFacet('group')"></div>
    <div facet-search facets="facets" type="access_rights" ng-if="showFacet('access_rights')"></div>
    <div facet-search facets="facets" type="license_class" ng-if="showFacet('license_class')"></div>

    <div facet-search facets="facets" type="administering_institution" ng-if="showFacet('administering_institution')"></div>
    <div facet-search facets="facets" type="funders" ng-if="showFacet('funders')"></div>

    

    <!-- Funding Amount for Activity Search-->
    <div class="panel-body swatch-white" ng-show="showFacet('funding_amount')">
        <h4>Funding Amount</h4>
        <input type="text" ng-model="filters.funding_from" class="form-control" placeholder="Funding From"/>
        <input type="text" ng-model="filters.funding_to" class="form-control" placeholder="Funding To"/>
        <button class="btn btn-primary" ng-click="hashChange()"><i class="fa fa-search"></i> Go</button>
    </div>

    <!-- Commencement date for activity search -->
    <div class="panel-body swatch-white" ng-show="showFacet('commencement_date')">
        <h4>Commencement date</h4>
        <select ng-model="filters.commence_from" ng-options="year_from as year_from for year_from in temporal_range" class="form-control">
            <option value="" style="display:none">From Year</option>
        </select>
        <select ng-model="filters.commence_to" ng-options="year_to as year_to for year_to in temporal_range | orderBy:year_to:true" class="form-control">
            <option value="" style="display:none">To Year</option>
        </select>
        <button class="btn btn-primary" ng-click="hashChange()"><i class="fa fa-search"></i> Go</button>
    </div>

    <!-- Completion date for activity search -->
    <div class="panel-body swatch-white" ng-show="showFacet('completion_date')">
        <h4>Completion date</h4>
        <select ng-model="filters.completion_from" ng-options="year_from as year_from for year_from in temporal_range" class="form-control">
            <option value="" style="display:none">From Year</option>
        </select>
        <select ng-model="filters.completion_to" ng-options="year_to as year_to for year_to in temporal_range | orderBy:year_to:true" class="form-control">
            <option value="" style="display:none">To Year</option>
        </select>
        <button class="btn btn-primary" ng-click="hashChange()"><i class="fa fa-search"></i> Go</button>
    </div>

    <!-- Temporal Facet -->
    <div class="panel-body swatch-white" ng-show="showFacet('temporal')">
        <h4>Time Period</h4>
        <select ng-model="filters.year_from" ng-options="year_from as year_from for year_from in temporal_range" class="form-control">
            <option value="" style="display:none">From Year</option>
        </select>
        <select ng-model="filters.year_to" ng-options="year_to as year_to for year_to in temporal_range | orderBy:year_to:true" class="form-control">
            <option value="" style="display:none">To Year</option>
        </select>
        <button class="btn btn-primary" ng-click="hashChange()"><i class="fa fa-search"></i> Go</button>
    </div>

    <!-- Location Facet -->
    <div class="panel-body swatch-white" ng-show="showFacet('spatial')">
        <h4>Location <i class="fa fa-info" tip="Geographical area ( either a point location or an area) where data was collected or a place which is the subject of a data collection"></i></h4>
        <ul class="listy">
            <li> <a href="" ng-click="advanced('spatial')">View Map <i class="fa fa-globe"></i></a></li>
        </ul>
    </div>
    
</div>
    
@stop