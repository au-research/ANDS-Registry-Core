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
	@include('registry_object/contents/suggested-datasets')
    @include('registry_object/contents/theme')
	@include('registry_object/contents/debug')
	@include('registry_object/contents/ARDC_campaign')
@stop