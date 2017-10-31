<div class="related-publications">
    <h4>Related Publications</h4>
    <ul class="list-unstyled">
        @foreach($related['publications']['docs'] as $col)
            <li>
                <i class="fa fa-book icon-portal"></i>
                <small>{{ $col['display_relationship'] }} </small>

                {{--DOI relatedInfo identifier is resolvable--}}
                @if($col['relation_identifier_type'] == 'doi')
                    <a href="" class="ro_preview"
                       identifier_doi="{{ $col['relation_identifier_identifier'] }}"
                       tip="{{ $col['to_title'] }}">
                        {{ $col['to_title'] }}
                    </a>
                @else
                    {{ $col['to_title'] }}
                @endif
                <br/>

                {{--Display the identifiers--}}
                <b>{{ $col['relation_identifier_type'] }}</b> :
                @if(isset($col['relation_identifier_url']))
                    <a href="{{ $col['relation_identifier_url'] }}"
                       tip="{{ $col['display_description'] }}">
                        {{ $col['relation_identifier_identifier'] }}
                    </a>
                @else
                    {{ $col['relation_identifier_identifier'] }}
                @endif
                <br/>

                {{--Relation URL display--}}
                @if(array_key_exists('relation_url', $col))
                    @foreach ($col['relation_url'] as $url)
                        <p>
                            <small>{{ $col['display_relationship'] }}</small>
                            URI :
                            <a href="{{ $url }}"
                               tip="{{ $col['display_description'] }}">
                                {{ $url }}
                            </a>
                        </p>
                    @endforeach
                @endif

                {{--Notes display for this relation--}}
                @if(isset($col['relation_notes']))
                @foreach ($col['relation_notes'] as $note)
                   <p> {{ $note }} </p>
                @endforeach
                @endif

            </li>
        @endforeach
    </ul>
</div>

