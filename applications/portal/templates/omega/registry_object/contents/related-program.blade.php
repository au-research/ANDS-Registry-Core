<h4>Related Program</h4>
@foreach($related['programs'] as $col)
    @if($col['slug'] && $col['registry_object_id'])
        <i class="fa fa-flask icon-portal"></i>
        <small>{{ readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class']) }}</small>
        <a href="{{ base_url() }}{{$col['slug']}}/{{$col['registry_object_id']}}"
           title="{{ $col['title'] }}"
           class="ro_preview"
           tip="{{ $col['display_description'] }}"
           ro_id="{{ $col['registry_object_id'] }}">
            {{$col['title']}}</a>
        {{ isset($col['funder']) ? $col['funder'] : '' }}
    @elseif(isset($col['identifier_relation_id']))
        <i class="fa fa-flask icon-portal"></i>
        <small>{{ readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class']) }}</small>
        <a href="{{ base_url() }}" title="{{$col['title']}}"
           class="ro_preview"
           tip="{{ $col['display_description'] }}"
           identifier_relation_id="{{ $col['identifier_relation_id'] }}">
            {{$col['title']}}</a>
        {{ isset($col['funder']) ? $col['funder'] : '' }}
    @endif
    <br />
@endforeach