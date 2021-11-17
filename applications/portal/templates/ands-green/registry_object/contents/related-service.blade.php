<div class="related-services">
    <h4>Related Services</h4>
    <ul class="list-unstyled">

        @foreach($related['services']['docs'] as $col)
            <li>
                <i class="fa fa-wrench icon-portal"></i>
                <?php //dd($col); ?>
                <?php $col_json = urlencode(json_encode($col));?>
                <small>{{ $col['_childDocuments_'][0]['relation_type_text'] }}</small>
                @if($col["to_identifier_type"]=="ro:id")
                    <a href="{{$col['to_url']}}"
                       title="{{ $col['to_title'] }}"
                       class="ro_preview"
                       ro_id="{{$col['to_identifier']}}">
                        {{$col['to_title']}}</a>
                @elseif($col["to_identifier_type"]!="ro:id")
                    <a href="{{$col['to_identifier']}}"
                       title="{{ $col['to_title'] }}"
                       class="ro_preview"
                       identifier_relation_id="{{$col_json}}">
                        {{$col['to_title']}}</a>
                @endif

            </li>
        @endforeach

        @if($related['services']['count'] > 5)
            <li><a href="{{ $related['services']['searchUrl'] }}">View all {{ $related['services']['count'] }} related services</a></li>
        @endif
    </ul>
</div>