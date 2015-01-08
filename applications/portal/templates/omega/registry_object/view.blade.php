@extends('layouts/right-sidebar')

@section('content')
<?php
	// var_dump($ro);
?>
<article class="post">
	<header class="post-head">
        <div class="post-title bordered">
		<h2 ><a href="#">{{$ro->core['title']}}</a></h2>
        @if($ro->relationships && isset($ro->relationships[0]['party_one']))
            @foreach($ro->relationships[0]['party_one'] as $col)
            <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}">{{$col['title']}}</a> {{$col['relation_type']}}
            @endforeach

        @endif
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