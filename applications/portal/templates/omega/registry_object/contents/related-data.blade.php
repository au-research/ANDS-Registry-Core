<h4>Related Data</h4>
@foreach($related['data'] as $col)

    @if($col['slug'] && $col['registry_object_id'])
        <i class="fa fa-folder-open icon-portal"></i>
        <small>{{ readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class']) }}</small>
        <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}"
           title="{{$col['title']}}"
           class="ro_preview"
           tip="{{ $col['display_description'] }}"
           ro_id="{{$col['registry_object_id']}}">
            {{$col['title']}}
        </a>
    @elseif(isset($col['identifier_relation_id']))
        <i class="fa fa-folder-open icon-portal"></i>
        <small>{{ readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class']) }}</small>
        <a href="<?php echo base_url()?>"
           title="{{$col['title']}}"
           class="ro_preview"
           tip="{{ $col['display_description'] }}"
           identifier_relation_id="{{$col['identifier_relation_id']}}">
            {{$col['title']}}
        </a>
    @endif
    <br/>

    @if($ro->relationships['collection_count'] > $relatedLimit)
        <p>
            <a href="{{ $related['searchQuery'] }}/class=collection">View all {{ $ro->relationships['collection_count'] }} related data</a>
        </p>
    @endif

@endforeach
