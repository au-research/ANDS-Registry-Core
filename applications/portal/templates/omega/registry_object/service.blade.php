@extends('layouts/'.$theme)

@section('content')
<article class="post">
	<div class="post-body">
        @foreach ($contents as $content)
            @include('registry_object/contents/'.$content)
        @endforeach
	</div>
</article>
@stop

@section('sidebar')

@if(is_dev())
<div class="panel panel-primary panel-content swatch-white">
    <div class="panel-heading">Debug Menu</div>
    <div class="panel-body">
        <a href="{{$ro->api_url}}">API URL</a>
    </div>
</div>
@endif
@stop