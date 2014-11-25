@extends('layouts/right-sidebar')

@section('content')
<?php
	// var_dump($ro);
?>
<article class="post">
	<header class="post-head">
		<h2 class="post-title bordered"><a href="#">{{$ro->core['title']}}</a></h2>
	</header>
	<div class="post-body">
		@include('registry_object/descriptions')
		@include('registry_object/related-objects-list')
	</div>
</article>
@stop

@section('sidebar')
<div class="sidebar-widget widget_archive">
	<h3 class="sidebar-header">Metadata Information</h3>
	<ul>
		<li><a href="">Descriptions (6)</a></li>
		<li><a href="">Related Publications (2)</a></li>
		<li><a href="">Related Researchers (2)</a></li>
		<li><a href="">Related Services (2)</a></li>
		<li><a href="">Related Services (1)</a></li>
		<li><a href="">Related Projects (1)</a></li>
	</ul>
</div>
<div class="sidebar-widget widget_recent_entries">
	<h3 class="sidebar-header">Suggested Datasets</h3>
	<ul>
		<li class="clearfix">
			<div class="post-icon">
				<a href=""><i class="fa fa-bolt"></i></a>
			</div>
			<a href="">Suggested Dataset 1</a>
			<small>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Tempora accusantium vitae, odit aliquam. Modi perferendis labore iste dolores eius excepturi magni quidem ipsam! Velit dolore, quia placeat sapiente, veniam obcaecati.</small>
		</li>
		<li class="clearfix">
			<div class="post-icon">
				<a href=""><i class="fa fa-bolt"></i></a>
			</div>
			<a href="">Suggested Dataset 2</a>
			<small>Lorem ipsum dolor sit amet, veniam obcaecati.</small>
		</li>
		<li class="clearfix">
			<div class="post-icon">
				<a href=""><i class="fa fa-bolt"></i></a>
			</div>
			<a href="">Suggested Dataset 3</a>
		</li>
	</ul>
</div>
@stop