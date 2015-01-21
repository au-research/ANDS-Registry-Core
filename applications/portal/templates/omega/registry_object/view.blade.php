@extends('layouts/'.$theme)

@section('content')
<?php
	// var_dump($ro);
?>
<article class="post">
	<div class="post-body">
		@foreach ($contents as $content)
			@include('registry_object/contents/'.$content)
		@endforeach
	</div>
</article>
@stop

@section('sidebar')
<div class="panel panel-primary panel-content swatch-white">
	<div class="panel-heading">Suggested Datasets</div>
	<div class="panel-body">
		<div class="sidebar-widget widget_recent_entries">
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
	</div>
</div>
<div class="panel panel-primary panel-content swatch-white">
	<div class="panel-heading">Debug Menu</div>
	<div class="panel-body">
		<a href="{{$ro->api_url}}">API URL</a>
	</div>
</div>
@stop