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
    @include('registry_object/contents/debug')
@stop