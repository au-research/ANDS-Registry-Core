@if($pullback)
<div class="panel panel-primary panel-content swatch-white">

	<div class="panel-body">
		<h2 class="bordered bold">
			@if($pullback['identifier_doi_type'] =='collection')
				<i class="fa fa-folder-open icon-portal"></i>
			@elseif($pullback['identifier_doi_type'] =='publication')
				<i class="fa fa-book icon-portal"></i>
			@endif
		{{$pullback['title']}}</h2>
		<dl class='dl'>
			@if($pullback['type'])
			<dt>Type</dt><dd>{{$pullback['type']}}</dd>
			@endif
			@if($pullback['publisher'])
			<dt>Publisher</dt><dd>{{$pullback['publisher']}}</dd>
			@endif
		</dl>
		@if($pullback['description'])
		<p>{{$pullback['description']}}</p>
		@endif

		@if($ro)
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
		@endif
		<p>
			@if($ro)
				<a href="{{portal_url($ro->core['slug'].'/'.$ro->core['id'])}}" class="btn btn-primary btn-link btn-sm">View Record</a>
			@endif
			<a href="{{$pullback['url']}}" class="btn btn-primary btn-link btn-sm">View DOI</a>
		</p>
	</div>
</div>
@else
	Unresolvable DOI
@endif
