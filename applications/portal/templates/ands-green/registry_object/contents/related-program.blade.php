<div class="related-programs">
    <h4>Related Program</h4>
    <ul class="list-unstyled">
        @foreach($related['programs']['docs'] as $col)
            <li>
                <i class="fa fa-flask icon-portal"></i>
                <small>{{ $col['_childDocuments_'][0]['relation_type_text'] }}</small>
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
        @if($related['programs']['count'] > 5)
            <li><a href="{{ $related['programs']['searchUrl'] }}">View all {{ $related['programs']['count'] }} related programs</a></li>
        @endif
    </ul>
</div>

