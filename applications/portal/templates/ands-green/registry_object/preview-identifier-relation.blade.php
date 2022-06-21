
<div class="panel panel-primary panel-content swatch-white">
	<div class="panel-body">
		@if(!$pullback && $record->connections_preview_div)
			<h2 class="bordered bold">
			@if($record->to_class =='collection' && $record->to_class=='software')
				<i class="fa fa-file-code-o icon-portal"></i>
			@elseif($record->to_class=='collection')
				<i class="fa fa-folder-open icon-portal"></i>
			@elseif($record->to_class=='activity')
				<i class="fa fa-flask icon-portal"></i>
			@elseif($record->to_class=='service')
				<i class="fa fa-wrench icon-portal"></i>
			@elseif($record->to_class=='party')
				@if($record->to_type=='party')
					<i class="fa fa-user icon-portal"></i>
				@else
					<i class="fa fa-group icon-portal"></i>
				@endif
			@endif
					<a href="">{{$record->to_title}}</a>
			</h2>
			{{$record->connections_preview_div}}
			@if($ro)
				<a href="{{portal_url($ro->core['slug'].'/'.$ro->core['id'])}}" class="btn btn-primary btn-link btn-sm">View Record</a>
			@endif
		@elseif($pullback)
			<h4>
				@if($pullback['name'] )
					{{$pullback['name'] }}
							<br/>
						<a href="{{ $pullback['url'] }}">
							{{  $pullback['url'] }}
						</a>
				@endif
			</h4>
			@if(!$pullback)
				<p>@include('registry_object/contents/the-description')</p>
			@elseif($pullback['relatedInfo_type'] == 'orcid' && isset($pullback['bio']))
				<p> {{ $pullback['bio']  }}</p>
			@elseif($pullback['relatedInfo_type'] == 'ror')
				<h5>Type</h5>
				<p> {{ $pullback['types']  }}</p>

				<h5>Country</h5>
				<p> {{ $pullback['country']  }}</p>

				@if($pullback['links'] != "")

				<?php
				$link_array = explode("'",$pullback['links'])
				?>
					<h5>Links</h5>
					@foreach($link_array as $link)
						<p><a href="{{$link}}" target="_blank">{{$link}}</p>
					@endforeach
				@endif
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
				@if(isset($pullback['bio']))
				<a href="{{ $pullback['url'] }} " class="btn btn-primary btn-link btn-sm">View profile in <img src="{{asset_url('img/orcid_tagline_small.png', 'base')}}" alt="" style="border: none;width: 50px;margin-top: -5px;
			margin-left: 5px;"></a>
				@elseif(isset($pullback['moreinfo']))
						<a href="{{ $pullback['url'] }} " class="btn btn-primary btn-link btn-sm">View profile in <img src="{{asset_url('img/ror-logo.svg', 'base')}}" alt="" style="border: none;width: 50px;margin-top: -5px;
			margin-left: 5px;"></a>
				@endif
			</p>
			

		@endif

<!-- RDA-703 show the first 5 related data to the given identifier (that we don't have an RO for
		TODO: find a portal search end to search for more data (if more than 5 related data exists)
-->
		@if($related_data['total'] > 0)
			<h4>More data related to {{$record->to_title}}</h4>
			<ul>
				@foreach($related_data['contents'] as $col)
						<li><a href="{{$col['from_url']}}" title="{{$col['from_title']}}"  ro_id="{{$col['from_id']}}">{{$col['from_title']}}</a></li>
				@endforeach
			</ul>
		@endif
	</div>
</div>