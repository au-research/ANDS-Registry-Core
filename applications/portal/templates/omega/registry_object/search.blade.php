@extends('layouts/left-sidebar-fw')
@section('content')
<div class="panel panel-primary element-no-top element-small-bottom" data-os-animation="fadeInUp">
    @include('includes/search-header')
    <div  ng-repeat="doc in result.response.docs" style="border-bottom:1px solid #eaeaea" class="panel-body swatch-white os-animation animated fadeInUp" style="-webkit-animation: 0.2s;">
        <div class="element-no-top element-no-bottom" data-os-animation="none" data-os-animation-delay="0s">
            <h2 class="post-title"> <a href="{{base_url()}}[[doc.slug]]/[[doc.id]]">[[doc.title]]</a> </h2>
            <div ng-repeat="x in doc.hl">
                <p ng-repeat="b in x" data-ng-bind-html="b"></p>
            </div>
            <p data-ng-bind-html="doc.description | trustAsHtml" ng-show="!doc.hl"></p>
            <div class="toolbar" style="margin-top:15px;">
                <input type="checkbox"> <a href="{{base_url()}}[[doc.slug]]/[[doc.id]]">View Record</a>
            </div>
        </div>
    </div>
</div>

<nav class="pull-right">
  <ul class="pagination pagination-sm" style="margin:0 auto;">
    <li><a href="" ng-click="goto(1)"><span aria-hidden="true">&laquo;</span><span class="sr-only">Previous</span></a></li>
    <li ng-repeat="x in page.pages" ng-class="{'active':page.cur==x}"><a href="" ng-click="goto(x)">[[x]]</a></li>
    <li><a href="" ng-click="goto(page.end)"><span aria-hidden="true">&raquo;</span><span class="sr-only">Next</span></a></li>
  </ul>
</nav>

<div class="clear"></div>
@stop
@section('sidebar')
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
        <ul class="list-unstyled">
            <li ng-repeat="filter in allfilters">
                <button class="btn btn-link btn-xs" ng-click="toggleFilter(filter.name,filter.value)">[[filter.value]] <i class="fa fa-remove"></i></button>
            </li>   
        </ul>
    </div>
</div>
    @foreach ($facets as $facet)
    	@include('registry_object/facet/'.$facet)
    @endforeach
@stop