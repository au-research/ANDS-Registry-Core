<h4>Related Publications</h4>
@foreach($related['publications'] as $relatedInfo)

    {{--Display citation itemprop for collection with related identifier not DOI--}}
    <span
        @if($ro->core['class'] == 'collection' && $relatedInfo['identifier']['identifier_type'] == 'doi')
        itemprop="citation"
        @endif
    >

        <i class="fa fa-book icon-portal"></i>
        <small>{{ $relatedInfo['display_relationship'] }} </small>

        {{--DOI relatedInfo identifier is resolvable--}}
        @if($relatedInfo['identifier']['identifier_type'] == 'doi')
            <a href="" class="ro_preview"
               identifier_doi="{{ $relatedInfo['identifier']['identifier_value'] }}"
               tip="{{ $relatedInfo['title'] }}">
                {{ $relatedInfo['title'] }}
            </a>
        @else
            {{ $relatedInfo['title'] }}
        @endif

        {{--Display the identifiers--}}
        <p>
            <b>{{ $relatedInfo['identifier']['identifier_type'] }}</b> :
            @if(isset($relatedInfo['identifier']['identifier_href']['href']))
                <a href="{{ $relatedInfo['identifier']['identifier_href']['href'] }}"
                   tip="{{ $relatedInfo['display_description'] }}">
                    {{ $relatedInfo['identifier']['identifier_value'] }}
                </a>
            @else
                {{ $relatedInfo['identifier']['identifier_value'] }}
            @endif
        </p>

        {{--Relation URL display--}}
        @if($relatedInfo['relation']['url'])
            <p>
                <small>{{ $relatedInfo['display_relationship'] }}</small>
                URI :
                <a href="{{ $relatedInfo['relation']['url'] }}"
                   tip="{{ $relatedInfo['display_description'] }}">
                    {{ $relatedInfo['relation']['url'] }}
                </a>
            </p>
        @endif

        {{--Notes display for this relation--}}
        @if($relatedInfo['notes'])
            <p> {{$relatedInfo['notes']}} </p>
        @endif

        {{--Close the span for the itemprop--}}
    </span>

@endforeach