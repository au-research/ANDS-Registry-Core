<div class="related-data">
    <h4>Related Data</h4>
    <ul class="list-unstyled">
        @foreach($related['data']['contents'] as $col)
                <li>
                    <i class="fa fa-folder-open icon-portal"></i>
                    <small>{{ $col['relations'][0]['relation_type_text'] }}</small>
                    @if($col["to_identifier_type"]=="ro:id")
                        <a href="{{$col['to_url']}}"
                           title="{{ $col['to_title'] }}"
                           class="ro_preview"
                           ro_id="{{$col['to_identifier']}}">
                            {{$col['to_title']}}</a>
                    @elseif($col["to_identifier_type"]!="ro:id")
                        <?php $col_json = urlencode(json_encode($col));?>
                        <a href="{{$col['to_identifier']}}"
                           title="{{ $col['to_title'] }}"
                           class="ro_preview"
                           identifier_relation_id="{{$col_json}}">
                            {{$col['to_title']}}</a>
                    @endif
                </li>
        @endforeach
        @if($related['data']['total']> 5)
            <li><a href="{{ $related['data']['searchUrl'] }}">View all {{ $related['data']['total'] }} related data</a></li>
        @endif
    </ul>
</div>

