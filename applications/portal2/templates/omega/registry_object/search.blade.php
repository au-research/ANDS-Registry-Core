@extends('layouts/left-sidebar-fw')

@section('content')
	main content
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

<div class="sidebar-widget widget_search">
	<h3 class="sidebar-header">By Subject</h3>
	<ul class="list-unstyled">
		<li><input type="checkbox"> <a href="">Lorem ipsum dolor sit.</a></li>
		<li><input type="checkbox"> <a href="">Lorem ipsum dolor sit.</a></li>
		<li><input type="checkbox"> <a href="">Lorem ipsum dolor sit.</a></li>
		<li><input type="checkbox"> <a href="">Lorem ipsum dolor sit.</a></li>
		<li><a href="">View More...</a></li>
	</ul>
</div>

@stop