<div class="related-organisations">
    <h4>Related Organisations</h4>
    <ul class="list-unstyled">
        <? $relation_to_title = [];
        $dupes = 0;?>
        @foreach($related['organisations']['contents'] as $col)
            <?php
            $result = array();
            $relation_types = [];
            foreach ($col['relations'] as $element) {
                $relation_types[] = $element['relation_type_text'];
            }
            $relation_types = array_unique($relation_types);
            $relation_type_text =  implode($relation_types,", ");
            if(!isset($col['to_url'])){
                $col['to_url']="";
            }
            $relation_to_title[$col['to_title']][] = $col['to_title'];
            $dupes = count($relation_to_title[$col['to_title']]);
            ?>
            @if($dupes<2)
            <li>
                <i class="fa fa-group icon-portal"></i>
                <small>{{ $relation_type_text }}</small>
                <a href="{{$col['to_url']}}"
                   title="{{ $col['to_title'] }}"
                   class="ro_preview"
                   tip="{{ $col['to_title'] }}"
                   ro_id="{{ $col['to_identifier'] }}">
                    {{$col['to_title']}}</a>
                {{ isset($col['to_funder']) ? "(funded by ". $col['to_funder'] .")" : '' }}
            </li>
            @endif


        @endforeach
        @if($related['organisations']['total'] > 5)
            <li><a href="{{ $related['organisations']['searchUrl'] }}">View all {{ $related['organisations']['total'] }} related organisations</a></li>
        @endif
    </ul>
</div>