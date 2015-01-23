@if($ro->relationships && isset($ro->relationships[0]['party_one']))

    @foreach($ro->relationships[0]['party_one'] as $col)
        <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" style="margin-right:5px;">{{$col['title']}} <small>({{$col['relation_type']}}) </small></a> 
    @endforeach

@endif
