<div class="related-services">
    <h4>Related Services</h4>
    <ul class="list-unstyled">
        @foreach($related['services']['docs'] as $col)
            <li>
                <i class="fa fa-wrench icon-portal"></i>
                <small>{{ $col['display_relationship'] }}</small>
                <a href="{{ base_url() }}{{$col['to_slug']}}/{{$col['to_id']}}"
                   title="{{ $col['to_title'] }}"
                   class="ro_preview"
                   tip="{{ $col['display_description'] }}"

                    @if(isset($col['to_id']) && $col['to_id']!='false')
                        ro_id="{{ $col['to_id'] }}"
                    @elseif(isset($col["relation_identifier_id"]))
                        identifier_relation_id="{{ $col['relation_identifier_id'] }}"
                    @endif
                    >
                    {{$col['to_title']}}</a>
                {{ isset($col['to_funder']) ? "(funded by ". $col['to_funder'] .")" : '' }}
            </li>
        @endforeach
        @if($related['services']['count'] > 5)
            <li><a href="{{ $related['services']['searchUrl'] }}">View all {{ $related['services']['count'] }} related services</a></li>
        @endif
    </ul>
</div>