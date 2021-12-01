<div class="related-grants-and-projects">
    <h4>Related Grants and Projects</h4>
    <ul class="list-unstyled">
        @foreach($related['grants_projects']['contents'] as $col)
            <li>
                <i class="fa fa-flask icon-portal"></i>
                <small>{{ $col['relations'][0]['relation_type_text'] }}</small>
                @if($col["to_identifier_type"]=="ro:id")
                    <a href="{{$col['to_url']}}"
                       title="{{ $col['to_title'] }}"
                       class="ro_preview"
                       ro_id="{{$col['to_identifier']}}">
                        {{$col['to_title']}}</a>
                @elseif($col["to_identifier_type"]!="ro:id")
                    <?php  $col_json = urlencode(json_encode($col)); ?>
                    <a href="{{$col['to_identifier']}}"
                       title="{{ $col['to_title'] }}"
                       class="ro_preview"
                       identifier_relation_id="{{$col_json}}">
                        {{$col['to_title']}}</a>
                @endif

                {{ isset($col['to_funder']) ? "(funded by ". $col['to_funder'] .")" : '' }}
            </li>
        @endforeach
        @if($related['grants_projects']['total'] > 5)
            <li><a href="{{ $related['grants_projects']['searchUrl'] }}">View all {{ $related['grants_projects']['total'] }} related grants and projects</a></li>
        @endif
    </ul>
</div>