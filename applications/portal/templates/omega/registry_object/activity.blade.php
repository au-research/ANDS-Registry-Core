@extends('layouts/'.$theme)

@section('content')
<?php
	// var_dump($ro);
?>
<article class="post">
	<div class="post-body">
		@foreach ($activity_contents as $content)
			@include('registry_object/activity_contents/'.$content)
		@endforeach
	</div>
</article>
@stop

@section('sidebar')

<div class="panel panel-primary panel-content swatch-white">
	<div class="panel-heading">Debug Menu</div>
	<div class="panel-body">
		<a href="{{$ro->api_url}}">API URL</a>
	</div>
</div>
@stop