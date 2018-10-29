<?php
$software_count = 0;
$data_count = 0;
foreach($related['data']['docs'] as $col){
    if(isset($col['to_id'])){
        if($col['to_type']=='software'){
            $software_count++;
        }else{
            $data_count++;
        }
   }
}
?>
@if($data_count>0)
<div class="related-data">
    <h4>Related Data</h4>
    <ul class="list-unstyled">
        @foreach($related['data']['docs'] as $col)
            @if(isset($col['to_id']) && $col['to_type']!='software')
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
            @endif
        @endforeach
        @if($data_count > 5)
            <li><a href="{{ $related['data']['searchUrl'] }}">View all {{ $data_count }} related data</a></li>
        @endif
    </ul>
</div>
@endif

@if($software_count>0)
    <div class="related-data">
        <h4>Related Software</h4>
        <ul class="list-unstyled">
            @foreach($related['data']['docs'] as $col)
                @if(isset($col['to_id']) && $col['to_type']=='software')
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
                @endif
            @endforeach

            @if($software_count > 5)
                <li><a href="{{ $related['data']['searchUrl'] }}">View all {{ $software_count }} related software</a></li>
            @endif
        </ul>
    </div>
    @endif
