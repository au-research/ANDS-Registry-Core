<div class="related-data">
    <h4>Related Data</h4>
    <ul class="list-unstyled">
        @foreach($related['data']['docs'] as $col)
                <li>
                    <i class="fa fa-folder-open icon-portal"></i>
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
        @if($related['data']['count']> 5)
            <li><a href="{{ $related['data']['searchUrl'] }}">View all {{ $related['data']['count'] }} related data</a></li>
        @endif
    </ul>
</div>

