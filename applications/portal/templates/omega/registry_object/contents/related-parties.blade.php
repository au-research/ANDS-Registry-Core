@if($ro->relationships && isset($ro->relationships[0]['party_one']))

    @foreach($ro->relationships[0]['party_one'] as $col)
        <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}">{{$col['title']}}</a> {{$col['relation_type']}}
    @endforeach

@endif
