<div class="related-researchers">
    <h4>Related People</h4>
    <ul class="list-unstyled">
        @if(is_array($related['researchers']) && $related['researchers']['total'] >0)
            @foreach($related['researchers']['contents'] as $col)
                   <li>
                    <i class="fa fa-user icon-portal"></i>
                    <?php $col_json = urlencode(json_encode($col));?>
                    <small>{{ $col['relations'][0]['relation_type_text'] }}</small>
               @if($col["to_identifier_type"]=="ro:id")
                    <a href="{{$col['to_url']}}"
                       title="{{ $col['to_title'] }}"
                       class="ro_preview"
                        ro_id="{{$col['to_identifier']}}">
                        {{$col['to_title']}}</a>
                @elseif($col["to_identifier_type"]!="uri")
                           <a href="{{$col['to_identifier']}}"
                              title="{{ $col['to_title'] }}"
                              class="ro_preview"
                              identifier_relation_id="{{$col_json}}">
                               {{$col['to_title']}}</a>
                 @endif

                </li>
            @endforeach
            @if($related['researchers']['total'] > 5)
                <li><a href="{{ $related['researchers']['searchUrl'] }}">View all {{ $related['researchers']['total'] }} related people</a></li>
            @endif
        @endif
    </ul>
</div>