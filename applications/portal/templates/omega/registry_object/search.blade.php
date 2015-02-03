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
        <div class="stoolbar">
            <input type="checkbox" ng-model="doc.select" ng-change="toggleResult(doc)">
        </div>
        <div class="element-no-top element-no-bottom scontent">
            <h2 class="post-title"> <a href="{{base_url()}}[[doc.slug]]/[[doc.id]]">[[doc.title]]</a> </h2>
            <p><small>[[doc.group]]</small></p>
            <div ng-repeat="x in doc.hl">
                <p ng-repeat="b in x" data-ng-bind-html="b | trustAsHtml"></p>
            </div>
            <p data-ng-bind-html="doc.description | trustAsHtml" ng-show="!doc.hl"></p>
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
        <table class="table">
            <tr ng-repeat="filter in allfilters">
                <td style="text-align:right;font-weight:bold;">[[filter.name]]</td><td>[[filter.value]]</td><td><a href="" ng-click="toggleFilter(filter.name, filter.value, true)"><i class="fa fa-remove"></i></a></td>
            </tr>
        </table>
        <div class="panel-body swatch-white">
            <a href="" class="btn btn-primary" ng-click="add_user_data('saved_search')">Save Search</a>
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
</div>
    @foreach ($facets as $facet)
    	@include('registry_object/facet/'.$facet)
    @endforeach
@stop