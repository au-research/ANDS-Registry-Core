@if($related['software']['count']>0)
    <div class="related-software">
        <h4>Related Software</h4>
        <ul class="list-unstyled">
            @foreach($related['software']['docs'] as $col)
                    <li>
                        <i class="fa fa-file-code-o icon-portal"></i>
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

                    </li>
            @endforeach
            @if($related['software']['count'] > 5)
                <li><a href="{{ $related['software']['searchUrl'] }}">View all {{ $related['software']['count'] }} related software</a></li>
            @endif
        </ul>
    </div>
@endif
