<div class="related-grants-and-projects">
    <h4>Related Grants and Projects</h4>
    <ul class="list-unstyled">
        @foreach($related['grants_projects']['contents'] as $col)
            <?php
            $result = array();
            $relation_types = [];
            foreach ($col['relations'] as $element) {
                $relation_types[] = $element['relation_type_text'];
            }
            $relation_types = array_unique($relation_types);
            $relation_type_text =  implode($relation_types,", ");
            ?>
            <li>
                <i class="fa fa-flask icon-portal"></i>
                <small>{{ $relation_type_text }}</small>
                <?php
                if(!isset($col['to_url'])){
                    $col['to_url']="";
                } ?>

                    <a href="{{$col['to_url']}}"
                       title="{{ $col['to_title'] }}"
                       tip="{{ $col['to_title'] }}"
                       class="ro_preview"
                       @if($col["to_identifier_type"]=="ro:id")
                            ro_id="{{$col['to_identifier']}}"
                       @else
                            <?php  $col_json = urlencode(json_encode($col)); ?>
                           identifier_relation_id="{{$col_json}}"
                        @endif>
                        {{$col['to_title']}}</a>


                {{ isset($col['to_funder']) ? "(funded by ". $col['to_funder'] .")" : '' }}
            </li>
        @endforeach
        @if($related['grants_projects']['total'] > 5)
            <li><a href="{{ $related['grants_projects']['searchUrl'] }}">View all {{ $related['grants_projects']['total'] }} related grants and projects</a></li>
        @endif
    </ul>
</div>