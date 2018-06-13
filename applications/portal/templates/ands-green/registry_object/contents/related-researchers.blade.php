<div class="related-researchers">
    <h4>Related Researchers</h4>
    <ul class="list-unstyled">
        @foreach($related['researchers']['docs'] as $col)
            <li>
                <i class="fa fa-group icon-portal"></i>
                <small>{{ $col['display_relationship'] }}</small>
                <a href="{{ base_url() }}{{$col['to_slug']}}/{{$col['to_id']}}"
                   title="{{ $col['to_title'] }}"
                   class="ro_preview"
                   tip="{{ $col['display_description'] }}"
                   ro_id="{{ $col['to_id'] }}">
                    {{$col['to_title']}}</a>
            </li>
        @endforeach
        @if($related['researchers']['count'] > 5)
            <li><a href="{{ $related['researchers']['searchUrl'] }}">View all {{ $related['researchers']['count'] }} related researchers</a></li>
        @endif
    </ul>
</div>