@if($ro->relationships && isset($ro->relationships['party_one']))
    @foreach($ro->relationships['party_one'] as $col)
    	@if($col['slug'] && $col['registry_object_id'])
        <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" class="ro_preview" ro_id="{{$col['registry_object_id']}}" style="margin-right:5px;">{{$col['title']}} <small>({{$col['relation_type']}}) </small></a> 
    	@elseif($col['identifier_relation_id'])
    	<a href="<?php echo base_url()?>" class="ro_preview" identifier_relation_id="{{$col['identifier_relation_id']}}" style="margin-right:5px;">{{$col['title']}} <small>({{$col['relation_type']}}) </small></a> 
    	@endif
    @endforeach
    @if(sizeof($ro->relationships['party_one']) < $ro->relationships['party_one_count_solr'])
		<a href="{{portal_url()}}search/#!/related_party_one_id={{$ro->core['id']}}/class=party/type=person">View all {{$ro->relationships['party_one_count_solr']}} related parties</a></li>
	@endif
@endif
