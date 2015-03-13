@if($ro->relationships && isset($ro->relationships['party_one']))
    @foreach($ro->relationships['party_one'] as $col)
    	@if(isset($col['identifier_relation_id']))
    	<a href="<?php echo base_url()?>" class="ro_preview" identifier_relation_id="{{$col['identifier_relation_id']}}" ">{{$col['title']}} <small>({{readable($col['relation_type'])}}) </small></a>
        @elseif($col['slug'] && $col['registry_object_id'])
        <?php
        $description = '';
        if(isset($col['relation_description']) && $col['relation_description']!='')
        {
            $description = 'tip="'.$col['relation_description'].'"';
        }
        ?>
        <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" {{$description}} class="ro_preview" ro_id="{{$col['registry_object_id']}}" style="margin-right:5px;">{{$col['title']}} <small>({{readable($col['relation_type'])}}) </small></a>
    	@endif
    @endforeach
    @if(sizeof($ro->relationships['party_one']) < $ro->relationships['party_one_count_solr'])
		<a href="{{portal_url()}}search/#!/related_{{$ro->core['class']}}_id={{$ro->core['id']}}/class=party/type=person">View all {{$ro->relationships['party_one_count_solr']}} related parties</a></li>
	@endif
@endif

@if($ro->relationships && !(isset($ro->relationships['party_one'])) && isset($ro->relationships['party_multi']))
    @foreach($ro->relationships['party_multi'] as $col)
        @if(isset($col['identifier_relation_id']))
        <a href="<?php echo base_url()?>" class="ro_preview" identifier_relation_id="{{$col['identifier_relation_id']}}" ">{{$col['title']}} <small>({{readable($col['relation_type'])}}) </small></a>
        @elseif($col['slug'] && $col['registry_object_id'])
        <?php
        $description = '';
        if(isset($col['relation_description']) && $col['relation_description']!='')
        {
            $description = 'tip="'.$col['relation_description'].'"';
        }
        ?>
        <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" {{$description}} class="ro_preview" ro_id="{{$col['registry_object_id']}}" style="margin-right:5px;">{{$col['title']}} <small>({{readable($col['relation_type'])}}) </small></a>
        @endif
    @endforeach
    @if(sizeof($ro->relationships['party_multi']) < $ro->relationships['party_multi_count_solr'])
    <a href="{{portal_url()}}search/#!/related_{{$ro->core['class']}}_id={{$ro->core['id']}}/class=party/type=group">View all {{$ro->relationships['party_multi_count_solr']}} related parties</a></li>
    @endif
@endif

