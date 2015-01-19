@extends('layouts/right-sidebar')

@section('content')
<?php
	// var_dump($ro);
?>
<article class="post">
	<header class="post-head">
        <div class="post-title bordered">
            @foreach ($view_headers as $view_header)

                @include('registry_object/'.$view_header)
            @endforeach
        </div>
	</header>

	<div class="post-body">
		@foreach ($contents as $content)
			@include('registry_object/'.$content)
		@endforeach
	</div>
</article>
@stop

@section('sidebar')

<div class="sidebar-widget">
	<h3>Debug Menu</h3>
	<b>API URL</b> {{$ro->api_url}}
</div>

<div class="sidebar-widget widget_archive">

    @foreach ($aside as $side)
    @include('registry_object/'.$side)
    @endforeach

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