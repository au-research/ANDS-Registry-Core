<div class="related-researchers">
    <h4>Related People</h4>

    <ul class="list-unstyled">
        @if(is_array($related['researchers']) && $related['researchers']['total'] >0)
            @foreach($related['researchers']['contents'] as $col)
                <?php
                $result = array();
                $relation_types = [];
                $relation_url = [];
                foreach ($col['relations'] as $element) {
                    $relation_types[] = $element['relation_type_text'];
                }
                $relation_types = array_unique($relation_types);
                $relation_type_text =  implode($relation_types,", ");
                ?>
                <li>
                    <i class="fa fa-user icon-portal"></i>
                    <small>{{ $relation_type_text }}</small>
                   <?php
                    if (!isset($col['to_url'])){
                        $col['to_url']="";
                    } ?>
                    <a href="{{ $col['to_url'] }}"
                       title="{{ $col['to_title'] }}"
                       class="ro_preview"
                       tip="{{ $col['to_title']}}"
                       @if($col['to_identifier_type'] == "ro:id")
                           ro_id="{{ $col['to_identifier'] }}"
                       @else

                            <?php $col_json = urlencode(json_encode($col)); ?>
                            identifier_relation_id = "{{ $col_json }}"
                        @endif>
                        {{$col['to_title']}}</a>
                </li>
            @endforeach
            @if($related['researchers']['total'] > 5)
                <li><a href="{{ $related['researchers']['searchUrl'] }}">View all {{ $related['researchers']['total'] }} related people</a></li>
            @endif
        @endif
    </ul>
</div>