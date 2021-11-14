<div class="related-researchers">
    <h4>Related People</h4>
    <ul class="list-unstyled">
        @foreach($related['researchers']['docs'] as $col)
               <li>
                <i class="fa fa-user icon-portal"></i>

                <small>{{ $col['_childDocuments_'][0]['relation_type_text'] }}</small>
           @if($col["to_identifier_type"]=="ro:id")
                <a href="{{$col['to_url']}}"
                   title="{{ $col['to_title'] }}"
                   class="ro_preview"
                    ro_id="{{$col['to_identifier']}}">
                    {{$col['to_title']}}</a>
            @elseif($col["to_identifier_type"]=="uri")
                       <a href="{{$col['to_identifier']}}"
                          title="{{ $col['to_title'] }}"
                          class="ro_preview"
                          identifier_relation_id="{{$col['_childDocuments_'][0] ["id"]}}">
                           {{$col['to_title']}}</a>
             @endif

            </li>
        @endforeach
        @if($related['researchers']['count'] > 5)
            <li><a href="{{ $related['researchers']['searchUrl'] }}">View all {{ $related['researchers']['count'] }} related people</a></li>
        @endif
    </ul>
</div>