<h4>Related Websites</h4>
@foreach($related['website'] as $col)
    <i class="fa fa-globe icon-portal"></i>
    <small>{{ $col['display_relationship'] }} </small>
    {{ isset($col['title']) ? $col['title'] : '' }}
    <p>
        @if($col['identifier']['identifier_href']['display_text'])
            <b>{{$col['identifier']['identifier_href']['display_text']}}</b> :
        @else
            <b>{{$col['identifier']['identifier_type']}}</b>:
        @endif

        @if($col['identifier']['identifier_href'])
            <a href="{{$col['identifier']['identifier_href']['href']}}" {{ $col['display_description'] }}>{{$col['identifier']['identifier_value']}}</a><br />
        @else
            {{$col['identifier']['identifier_value']}}
        @endif
    </p>
@endforeach