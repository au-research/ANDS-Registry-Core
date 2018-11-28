@if($related['software']['count']>0)
    <div class="related-data">
        <h4>Related Software</h4>
        <ul class="list-unstyled">
            @foreach($related['software']['docs'] as $col)
                    <li>
                        <i class="fa fa-file-code-o icon-portal"></i>
                        <small>{{ $col['display_relationship'] }}</small>
                        <a href="<?php echo base_url()?>{{$col['to_id']}}/{{$col['to_id']}}"
                           title="{{$col['to_title']}}"
                           class="ro_preview"
                           tip="{{ $col['display_description'] }}"
                           @if(isset($col['to_id']) && $col['to_id']!='false')
                           ro_id="{{ $col['to_id'] }}"
                           @elseif(isset($col["relation_identifier_id"]))
                           identifier_relation_id="{{ $col['relation_identifier_id'] }}"
                                @endif
                        >
                            {{$col['to_title']}}
                        </a>
                    </li>
            @endforeach
            @if($related['software']['count'] > 5)
                <li><a href="{{ $related['data']['searchUrl'] }}">View all {{ $related['software']['count'] }} related software</a></li>
            @endif
        </ul>
    </div>
@endif
