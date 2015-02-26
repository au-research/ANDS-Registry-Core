@extends('layouts/left-sidebar-fw')

@section('content')
<div class="panel panel-primary element-no-top element-small-bottom" data-os-animation="fadeInUp">
    <div class="panel-body swatch-white" style="padding:10px" ng-cloak ng-show="result">
        <a href="" class="btn btn-link btn-sm" ng-click="toggleResults()">
            <span ng-show="selectState=='deselectAll'"><i class="fa fa-check-square-o"></i> Deselect All</span>
            <span ng-show="selectState=='selectAll'"><i class="fa fa-square-o"></i> Select All</span>
            <span ng-show="selectState=='deselectSelected'"><i class="fa fa-minus-square-o"></i> Deselect Selected</span>
        </a>
        <nav ng-hide="loading" ng-cloak class="pull-right">
            <ul class="pagi">
                <li><a href="" ng-click="goto(1)"><span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span></a></li>
                <li ng-repeat="x in page.pages" ng-class="{'active':page.cur==x}"><a href="" ng-click="goto(x)">[[x]]</a></li>
                <li><a href="" ng-click="goto(page.end)"><span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span></a></li>
            </ul>
        </nav>
    </div>

    <div ng-repeat="doc in result.response.docs" style="border-bottom:1px solid #eaeaea" class="panel-body swatch-white os-animation animated fadeInLeft sresult" ng-cloak>
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
                        <p ng-repeat="(index, content) in getHighlight(doc.id)">
                            <span data-ng-bind-html="content | trustAsHtml"></span> <small><b>[[index]]</b></small>
                        </p>
                        <p ng-if="getHighlight(doc.id)===false">
                            [[doc.description | text | truncate:500]]
                        </p>
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
                <li><a href="" ng-click="goto(1)"><span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span></a></li>
                <li ng-repeat="x in page.pages" ng-class="{'active':page.cur==x}"><a href="" ng-click="goto(x)">[[x]]</a></li>
                <li><a href="" ng-click="goto(page.end)"><span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span></a></li>
            </ul>
        </nav>
    </div>
</div>
<div class="clear"></div>
@stop

@section('sidebar')
<div class="panel panel-primary" ng-cloak>
    <div class="panel-heading">Current Search</div>
    <!-- <div class="panel-body swatch-white">
        [[filters]]
    </div> -->
    <div class="panel-body swatch-white">
        <div ng-repeat="(name, value) in filters" ng-if="showFilter(name)">
            <h4>[[name | filter_name]]</h4>
            <ul class="listy" ng-show="isArray(value) && name!='anzsrc-for'">
                <li ng-repeat="v in value track by $index"> <a href="" ng-click="toggleFilter(name, v, true)">[[v]]<small><i class="fa fa-remove"></i></small></a> </li>
            </ul>
            <ul class="listy" ng-show="isArray(value)===false && name!='anzsrc-for'">
                <li> <a href="" ng-click="toggleFilter(name, value, true)">[[value]]<small><i class="fa fa-remove"></i></small></a> </li>
            </ul>
            <div resolve ng-if="name=='anzsrc-for'" subjects="value" vocab="anzsrc-for"></div>
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

    <!-- Subject Facet -->
    <div class="panel-body swatch-white" ng-if="filter.class=='collection'">
        <h4>Subjects</h4>
        <ul class="listy">
          <li ng-repeat="item in vocab_tree | orderBy:'pos' | limitTo:5">
            <input type="checkbox" ng-checked="isVocabSelected(item)" ui-indeterminate="isVocabParentSelected(item)" ng-click="toggleFilter('anzsrc-for', item.notation, true)">
            <a href="" ng-click="toggleFilter('anzsrc-for', item.notation, true)">
                [[item.prefLabel]]
                <small>[[item.collectionNum]]</small>
            </a>
          </li>
            <li><a href="" ng-click="advanced('subject')">View More</a></li>
        </ul>
    </div>
    
    <div class="panel-body swatch-white" ng-repeat="facet in facets | orderBy:'name':true">
        <h4>[[facet.name | filter_name]]</h4>
        <ul class="listy">
            <li ng-repeat="item in facet.value | limitTo:5 | orderBy:'item.value'">
                <input type="checkbox" ng-checked="isFacet(facet.name, item.name)" ng-click="toggleFilter(facet.name, item.name, true)">
                <a href="" ng-click="toggleFilter(facet.name, item.name, true)">[[item.name | truncate:30]] <small>[[item.value]]</small></a>    
            </li>
            <li><a href="" ng-click="advanced(facet.name)">View More</a></li>
        </ul>
    </div>

    <!-- Temporal Facet -->
    <div class="panel-body swatch-white" ng-if="filters.class=='collection'">
        <h4>Temporal</h4>
        <select ng-model="filters.year_from" ng-options="year_from as year_from for year_from in temporal_range" class="form-control">
            <option value="" style="display:none">From Year</option>
        </select>
        <select ng-model="filters.year_to" ng-options="year_to as year_to for year_to in temporal_range | orderBy:year_to:true" class="form-control">
            <option value="" style="display:none">To Year</option>
        </select>
        <button class="btn btn-primary" ng-click="hashChange()"><i class="fa fa-search"></i> Search</button>
    </div>

    
</div>
    
@stop