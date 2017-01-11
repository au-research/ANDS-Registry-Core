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
    @include('registry_object/contents/debug')
@stop