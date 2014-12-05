@extends('layouts/left-sidebar-fw')

@section('content')
	<article class="post post-showinfo" ng-repeat="doc in result.response.docs">
        <header class="post-head">
            <h2 class="post-title"> <a href="{{base_url()}}[[doc.slug]]/[[doc.id]]">[[doc.title]]</a> </h2>
            <small> by <a href="">2 comments</a> </small>
            <!-- <span class="post-icon"> <i class="fa fa-picture-o"></i> </span> -->
        </header>
        <div class="post-body">
        	<p data-ng-bind-html="doc.description | trustAsHtml"></p>
        </div>
    </article>
@stop

@section('sidebar')

<div class="sidebar-widget widget_search">
	<h3 class="sidebar-header">Refine search result</h3>
	<form action="">
		<div class="input-group">
            <input type="text" value="" name="s" class="form-control" placeholder="Add more keywords">
            <span class="input-group-btn">
	            <button class="btn" type="submit" value="Search">
	                <i class="fa fa-search"></i> Go
	            </button>
	        </span>
        </div>
	</form>
</div>

@foreach ($facets as $facet)
	@include('registry_object/facet/'.$facet)
@endforeach



@stop