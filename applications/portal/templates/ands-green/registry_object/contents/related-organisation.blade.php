<div class="related-organisations">
    <h4>Related Organisations</h4>
    <ul class="list-unstyled">
        @foreach($related['organisations']['docs'] as $col)
            <li>
                <i class="fa fa-group icon-portal"></i>
                <small>{{ $col['_childDocuments_'][0]['relation_type_text'] }}</small>
                <a href="{{$col['to_url']}}"
                   title="{{ $col['to_title'] }}"
                   class="ro_preview"
                   ro_id="{{ $col['to_identifier'] }}">
                    {{$col['to_title']}}</a>
                {{ isset($col['to_funder']) ? "(funded by ". $col['to_funder'] .")" : '' }}
            </li>
        @endforeach
        @if($related['organisations']['count'] > 5)
            <li><a href="{{ $related['organisations']['searchUrl'] }}">View all {{ $related['organisations']['count'] }} related organisations</a></li>
        @endif
    </ul>
</div>