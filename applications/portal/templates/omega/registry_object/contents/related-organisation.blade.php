<h4>Related Organisations</h4>
<p>
    @foreach($ro->relationships['party_multi'] as $col)

        @if($col['slug'] && $col['registry_object_id'])
            <i class="fa fa-group icon-portal"></i>
            <small>{{ readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class'],$col['class']) }}</small>
            <a href="{{ base_url() }}{{$col['slug']}}/{{$col['registry_object_id']}}"
               title="{{ $col['title'] }}"
               class="ro_preview"
               @if(isset($col['display_description']))
                tip="{{ $col['display_description'] }}"
               @endif
               ro_id="{{ $col['registry_object_id'] }}">
                {{$col['title']}}</a>
        @elseif(isset($col['identifier_relation_id']))
            <i class="fa fa-group icon-portal"></i>
            <small>{{ readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class'],$col['class']) }}</small>
            <a href="{{ base_url() }}"
               title="{{$col['title']}}"
               class="ro_preview"
               @if(isset($col['display_description']))
                tip="{{ $col['display_description'] }}"
               @endif
               identifier_relation_id="{{ $col['identifier_relation_id'] }}">
                {{$col['title']}}
            </a>
        @endif
        <br/>
    @endforeach
    @if($ro->relationships['party_multi_count'] > $relatedLimit)
        <a href="{{$relatedSearchQuery}}/class=party/type=group">View all {{$ro->relationships['party_multi_count']}} related organisations</a>
    @endif
</p>