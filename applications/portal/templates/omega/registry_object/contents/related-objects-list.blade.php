@if($ro->relationships)
<?php
	// var_dump($ro->relationships['activity']);
?>
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
	<div class="panel panel-primary element-no-top element-short-bottom panel-content">
		<div class="panel-heading">
	        <a href="">Related</a>
	    </div>
		<div class="panel-body swatch-white">
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

			@if($ro->relationships && isset($ro->relationships['party_multi']) && $ro->core['class']!='activity')
			<h4>Organisations</h4>
			<ul>
				@foreach($ro->relationships['party_multi'] as $col)
					@if($col)
						<li><a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a></li>
					@endif
				@endforeach
				@if(sizeof($ro->relationships['party_multi']) < $ro->relationships['party_multi_count'])
					<li><a href="{{portal_url()}}search/#!/related_{{$search_class}}_id={{$ro->core['id']}}/class=party/type=group">View all {{$ro->relationships['party_multi_count']}} related organisations</a></li>
				@endif
			</ul>
			@endif

			@if($ro->relationships && isset($ro->relationships['service']))
			<h4>Services</h4>
			<ul>
				@foreach($ro->relationships['service'] as $col)
					@if($col)
						<li><a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a></li>
					@endif
				@endforeach
				@if(sizeof($ro->relationships['service']) < $ro->relationships['service_count'])
					<li><a href="{{portal_url()}}search/#!/related_{{$search_class}}_id={{$ro->core['id']}}/class=service">View all {{$ro->relationships['service_count']}} related services</a></li>
				@endif
			</ul>
			@endif

			@if($ro->relationships && isset($ro->relationships['activity']))
			<h4>Programmes and Projects</h4>
			<ul>
				@foreach($ro->relationships['activity'] as $col)
					@if($col)
						<li><a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a></li>
					@endif
				@endforeach
				@if(sizeof($ro->relationships['activity']) < $ro->relationships['activity_count'])
					<li><a href="{{portal_url()}}search/#!/related_{{$search_class}}_id={{$ro->core['id']}}/class=activity">View all {{$ro->relationships['activity_count']}} related activities</a></li>
				@endif
			</ul>
			@endif
		</div>
	</div>
</div>
@endif