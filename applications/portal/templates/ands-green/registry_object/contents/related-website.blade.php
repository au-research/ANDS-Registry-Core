<div class="related-websites">
    <h4>Related Websites</h4>
    <ul class="list-unstyled">
        @foreach($related['websites']['contents'] as $col)
            <li>

                <i class="fa fa-globe icon-portal"></i>
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
   </ul>
</div>