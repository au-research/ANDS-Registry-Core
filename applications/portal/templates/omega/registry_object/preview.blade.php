<?php 
	$search_class = $ro->core['class'];
	if($ro->core['class']=='party') {
		if (strtolower($ro->core['type'])=='person'){
			$search_class = 'party_one';
		} elseif(strtolower($ro->core['type'])=='group') {
			$search_class = 'party_multi';
		}
	}
?>
<div class="swatch-white">
	<h2 class="bordered bold">{{$ro->core['title']}}</h2>
	<p>@include('registry_object/contents/the-description')</p>
	@if($ro->core['class']=='party')
		@include('registry_object/contents/contact-info')
	@endif
	@if($ro->relationships && isset($ro->relationships['collection']))
	<h4>Related Collections</h4>
	<ul>
		@foreach($ro->relationships['collection'] as $col)
			@if($col)
			<li><a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a></li>
			@endif
		@endforeach
		@if(sizeof($ro->relationships['collection']) < $ro->relationships['collection_count'])
			<li><a href="{{portal_url()}}search/#!/related_{{$search_class}}_id={{$ro->core['id']}}/class=collection">View all {{$ro->relationships['collection_count']}} related collections</a></li>
		@endif
	</ul>
	@endif
	<a href="{{portal_url($ro->core['slug'].'/'.$ro->core['id'])}}" class="btn btn-primary btn-link btn-sm">View Record</a>
</div>
