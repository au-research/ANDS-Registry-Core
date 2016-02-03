<h4>Related Publications</h4>
@foreach($related['publications'] as $col)

    {{--Display citation itemprop for collection with related identifier not DOI--}}
    <span
        @if($ro->core['class'] == 'collection' && $col['identifier']['identifier_type'] == 'doi')
        itemprop="citation"
        @endif
    >

        <i class="fa fa-book icon-portal"></i>
        <small>{{ $col['display_relationship'] }} </small>

        {{--DOI relatedInfo identifier is resolvable--}}
        @if($col['identifier']['identifier_type'] == 'doi')
            <a href="" class="ro_preview"
               identifier_doi="{{ $col['identifier']['identifier_value'] }}"
               tip="{{ $col['title'] }}">
                {{ $col['title'] }}
            </a>
        @else
            {{ $col['title'] }}
        @endif
        <br/>

        {{--Display the identifiers--}}
        <b>{{ $col['identifier']['identifier_type'] }}</b> :
        @if(isset($col['identifier']['identifier_href']['href']))
            <a href="{{ $col['identifier']['identifier_href']['href'] }}"
               tip="{{ $col['display_description'] }}">
                {{ $col['identifier']['identifier_value'] }}
            </a>
        @else
            {{ $col['identifier']['identifier_value'] }}
        @endif
        <br/>

        {{--Relation URL display--}}
        @if(isset($col['relation']['url']))
            <p>
                <small>{{ $col['display_relationship'] }}</small>
                URI :
                <a href="{{ $col['relation']['url'] }}"
                   tip="{{ $col['display_description'] }}">
                    {{ $col['relation']['url'] }}
                </a>
            </p>
        @endif

        {{--Notes display for this relation--}}
        @if(isset($col['notes']))
            <p> {{ $col['notes'] }} </p>
        @endif
        <br/>

        {{--Close the span for the itemprop--}}
    </span>

@endforeach