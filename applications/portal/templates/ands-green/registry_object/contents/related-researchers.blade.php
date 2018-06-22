<div class="related-researchers">
    <h4>Related People</h4>
    <ul class="list-unstyled">
        @foreach($related['researchers']['docs'] as $col)
            <li>
                <i class="fa fa-user icon-portal"></i>
                <small>{{ $col['display_relationship'] }}</small>
                <a href="{{ base_url() }}{{$col['to_slug']}}/{{$col['to_id']}}"
                   title="{{ $col['to_title'] }}"
                   class="ro_preview"
                   tip="{{ $col['display_description'] }}"
                   @if(isset($col['relation_identifier_id']))
                    identifier_relation_id="{{ $col['relation_identifier_id'] }}"
                   @elseif(isset($col['to_id']))
                    ro_id="{{$col['to_id']}}"
                   @endif>
                    {{$col['to_title']}}</a>
            </li>
        @endforeach
        @if($related['researchers']['count'] > 5)
            <li><a href="{{ $related['researchers']['searchUrl'] }}">View all {{ $related['researchers']['count'] }} related people</a></li>
        @endif
    </ul>
</div>