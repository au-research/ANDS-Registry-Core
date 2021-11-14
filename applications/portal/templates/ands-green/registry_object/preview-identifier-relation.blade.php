<div class="panel panel-primary panel-content swatch-white">
	<div class="panel-heading">
	{{$record->_childDocuments_[0]->relation_type}}
	@if($record->to_title)
		{{$record->to_title}}
	@endif
	</div>

	<div class="panel-body">

		@if(!$pullback && $record->connections_preview_div)
			{{$record->connections_preview_div}}
			@if($ro)
				<a href="{{portal_url($ro->core['slug'].'/'.$ro->core['id'])}}" class="btn btn-primary btn-link btn-sm">View Record</a>
			@endif
		@elseif($pullback)
			<h4>
				@if($record->related_title)
					{{$record->related_title}}
					@if($pullback['orcidRecord'])
						<br/>
						<a href="{{ $pullback['orcidRecord']->url }}">
							{{  $pullback['orcidRecord']->url }}
						</a>
					@endif
				@else
					{{ $pullback['name'] }} ({{ $pullback['orcidRecord'] ? $pullback['orcidRecord']->url : '' }})
				@endif
			</h4>

			@if(!$pullback)
				<p>@include('registry_object/contents/the-description')</p>
			@else
				<p> {{ $pullback['bio_content']  }}</p>
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
				<a href="{{ $pullback['orcidRecord']->url }} " class="btn btn-primary btn-link btn-sm">View profile in <img src="{{asset_url('img/orcid_tagline_small.png', 'base')}}" alt="" style="border: none;width: 50px;margin-top: -5px;
			margin-left: 5px;"></a>
			</p>
			

		@endif
	</div>
</div>