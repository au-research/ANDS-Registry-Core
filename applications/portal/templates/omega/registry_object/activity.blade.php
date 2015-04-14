@extends('layouts/'.$theme)

@section('content')
<article class="post">
	<div class="post-body">
        @include('registry_object/activity_contents/descriptions')
        @foreach ($contents as $content)
            @if($content!='descriptions')
                @include('registry_object/contents/'.$content)
            @endif
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