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
		
			@if($ro->relatedInfo)
			<h4>Related Publications</h4>
			@foreach($ro->relatedInfo as $relatedInfo)
				@if($relatedInfo['type']=='publication')
	                <h5><a href="" class="ro_preview" identifier_doi="{{$relatedInfo['identifier']['identifier_value']}}"><img src="<?php echo base_url()?>assets/core/images/icons/publications.png" style="margin-top: -2px; height: 24px; width: 24px;"> {{$relatedInfo['title']}}</a></h5>
	                <p>
	                    <b>{{$relatedInfo['identifier']['identifier_type']}}</b> : 
	                    @if($relatedInfo['identifier']['identifier_href'])
	                        <a href="{{$relatedInfo['identifier']['identifier_href']['href']}}">{{$relatedInfo['identifier']['identifier_value']}}</a><br />
	                    @else
	                        {{$relatedInfo['identifier']['identifier_value']}}
	                    @endif
	                </p>
	                @if($relatedInfo['relation']['url'])
	                    <p>URI : <a href="{{$relatedInfo['relation']['url']}}">{{$relatedInfo['relation']['url']}}</a></p>
	                @endif
	            @endif
            @endforeach
            @endif

			<!-- Only display collections with the relationship of isDerivedFrom or hasDerivedCollection -->

			<?php
				$hasDerivedCollection = false;
				if ($ro->relationships && isset($ro->relationships['collection'])) {
					foreach ($ro->relationships['collection'] as $col) {
						if ($col['relation_type']=='hasDerivedCollection' || $col['relation_type']=='isDerivedFrom') {
							$hasDerivedCollection = true;
						}
					}
				}
			?>

			@if($hasDerivedCollection)
			<h4>Related Data</h4>
			<ul>
				@foreach($ro->relationships['collection'] as $col)
					@if($col && ($col['relation_type']=='hasDerivedCollection' || $col['relation_type']=='isDerivedFrom'))
					<li><a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}} <small>{{readable($col['relation_type'])}}</small></a></li>
					@endif
				@endforeach
			</ul>
			@endif

			@if($ro->relationships && isset($ro->relationships['party_multi']))
			<h4>Related Organisations</h4>
			<ul>
				@foreach($ro->relationships['party_multi'] as $col)
					@if($col)
						<li><a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}} <small>{{readable($col['relation_type'])}}</small></a></li>
					@endif
				@endforeach
				@if(sizeof($ro->relationships['party_multi']) < $ro->relationships['party_multi_count_solr'])
					<li><a href="{{portal_url()}}search/#!/related_{{$search_class}}_id={{$ro->core['id']}}/class=party/type=group">View all {{$ro->relationships['party_multi_count_solr']}} related organisations</a></li>
				@endif
			</ul>
			@endif

			@if($ro->relationships && isset($ro->relationships['activity']))
			<h4>Related Grants and Projects</h4>
			<ul>
				@foreach($ro->relationships['activity'] as $col)
					@if($col)
						<li><a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}} <small>{{readable($col['relation_type'])}}</small></a></li>
					@endif
				@endforeach
				@if(sizeof($ro->relationships['activity']) < $ro->relationships['activity_count_solr'])
					<li><a href="{{portal_url()}}search/#!/related_{{$search_class}}_id={{$ro->core['id']}}/class=activity">View all {{$ro->relationships['activity_count_solr']}} related activities</a></li>
				@endif
			</ul>
			@endif

			@if($ro->relationships && isset($ro->relationships['service']))
			<h4>Related Services</h4>
			<ul>
				@foreach($ro->relationships['service'] as $col)
					@if($col)
						<li><a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}} <small>{{readable($col['relation_type'])}}</small></a></li>
					@endif
				@endforeach
				@if(sizeof($ro->relationships['service']) < $ro->relationships['service_count_solr'])
					<li><a href="{{portal_url()}}search/#!/related_{{$search_class}}_id={{$ro->core['id']}}/class=service">View all {{$ro->relationships['service_count_solr']}} related services</a></li>
				@endif
			</ul>
			@endif


			@if($ro->relatedInfo)
			<h4>Related Websites</h4>
			@foreach($ro->relatedInfo as $relatedInfo)
				@if($relatedInfo['type']=='website')
	                <h5><a href="" class="ro_preview" identifier_doi="{{$relatedInfo['identifier']['identifier_value']}}"><img src="<?php echo base_url()?>assets/core/images/icons/publications.png" style="margin-top: -2px; height: 24px; width: 24px;"> {{$relatedInfo['title']}}</a></h5>
	                <p>
	                    <b>{{$relatedInfo['identifier']['identifier_type']}}</b> : 
	                    @if($relatedInfo['identifier']['identifier_href'])
	                        <a href="{{$relatedInfo['identifier']['identifier_href']['href']}}">{{$relatedInfo['identifier']['identifier_value']}}</a><br />
	                    @else
	                        {{$relatedInfo['identifier']['identifier_value']}}
	                    @endif
	                </p>
	                @if($relatedInfo['relation']['url'])
	                    <p>URI : <a href="{{$relatedInfo['relation']['url']}}">{{$relatedInfo['relation']['url']}}</a></p>
	                @endif
	            @endif
            @endforeach
            @endif
		</div>
	</div>
</div>
@endif