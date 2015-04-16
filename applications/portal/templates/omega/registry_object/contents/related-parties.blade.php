<?php
$relatedSearchQuery = portal_url().'search/#!/related_'.$ro->core['class'].'_id='.$ro->core['id'];
if($ro->identifiermatch && sizeof($ro->identifiermatch) > 0){
foreach($ro->identifiermatch as $mm)
{
$relatedSearchQuery .= '/related_'.$ro->core['class'].'_id='.$mm['registry_object_id'];
}
}
?>


@if($ro->relationships && isset($ro->relationships['party_one']))
    @foreach($ro->relationships['party_one'] as $col)
        @if($col['slug'] && $col['registry_object_id'])
        <?php
        $description = '';
        if(isset($col['relation_description']) && $col['relation_description']!='')
        {
            $description = 'tip="'.$col['relation_description'].'"';
        }
        ?>
        <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" {{$description}} class="ro_preview" ro_id="{{$col['registry_object_id']}}" style="margin-right:5px;">{{$col['title']}} <small>({{readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class'])}}) </small></a>
        @elseif(isset($col['identifier_relation_id']))
        <a href="<?php echo base_url()?>" class="ro_preview" identifier_relation_id="{{$col['identifier_relation_id']}}" ">{{$col['title']}} <small>({{readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class'])}}) </small></a>
        @endif
    @endforeach
    @if($ro->relationships['party_one_count'] > $relatedLimit)
		<a href="{{$relatedSearchQuery}}/class=party/type=person">View all {{$ro->relationships['party_one_count_solr']}} related parties</a></li>
	@endif
@endif

@if($ro->relationships && !(isset($ro->relationships['party_one'])) && isset($ro->relationships['party_multi']))
    @foreach($ro->relationships['party_multi'] as $col)
        @if($col['slug'] && $col['registry_object_id'])
        <?php
        $description = '';
        if(isset($col['relation_description']) && $col['relation_description']!='')
        {
            $description = 'tip="'.$col['relation_description'].'"';
        }
        ?>
        <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" {{$description}} class="ro_preview" ro_id="{{$col['registry_object_id']}}" style="margin-right:5px;">{{$col['title']}} <small>({{readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class'])}}) </small></a>
        @elseif(isset($col['identifier_relation_id']))
        <a href="<?php echo base_url()?>" class="ro_preview" identifier_relation_id="{{$col['identifier_relation_id']}}" ">{{$col['title']}} <small>({{readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class'])}}) </small></a>
        @endif
    @endforeach
    @if($ro->relationships['party_multi_count'] > $relatedLimit)
    <a href="{{$relatedSearchQuery}}/class=party/type=group">View all {{$ro->relationships['party_multi_count_solr']}} related parties</a></li>
    @endif
@endif