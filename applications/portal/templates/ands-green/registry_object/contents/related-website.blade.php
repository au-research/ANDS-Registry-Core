<div class="related-websites">
    <h4>Related Websites</h4>
    <ul class="list-unstyled">
        @foreach($related['websites']['docs'] as $col)
            <li>

                <i class="fa fa-globe icon-portal"></i>
                <small>{{ $col['display_relationship'] }} </small>

                {{--Display the identifiers--}}
                {{ $col['to_title'] }}
                    <br/>
                @if(isset($col['relation_identifier_url']))
                    <a href="{{ $col['relation_identifier_url'] }}"
                       tip="{{ $col['display_description'] }}">
                        {{ $col['relation_identifier_identifier'] }}
                    </a>
                @else
                    {{ $col['relation_identifier_identifier'] }}
                @endif

            </li>
        @endforeach
    </ul>
</div>