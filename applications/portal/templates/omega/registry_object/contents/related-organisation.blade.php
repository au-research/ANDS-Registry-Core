<div class="related-organisations">
    <h4>Related Organisations</h4>
    <ul class="list-unstyled">
        @foreach($related['organisations']['docs'] as $col)
            <li>
                <i class="fa fa-group icon-portal"></i>
                <small>{{ $col['display_relationship'] }}</small>
                <a href="{{ base_url() }}{{$col['to_slug']}}/{{$col['to_id']}}"
                   title="{{ $col['to_title'] }}"
                   class="ro_preview"
                   tip="{{ $col['display_description'] }}"
                   ro_id="{{ $col['to_id'] }}">
                    {{$col['to_title']}}</a>
                {{ isset($col['to_funder']) ? "(funded by ". $col['to_funder'] .")" : '' }}
            </li>
        @endforeach
        @if($related['organisations']['count'] > 5)
            <li><a href="{{ $related['organisations']['searchUrl'] }}">View all {{ $related['organisations']['count'] }} related organisations</a></li>
        @endif
    </ul>
</div>