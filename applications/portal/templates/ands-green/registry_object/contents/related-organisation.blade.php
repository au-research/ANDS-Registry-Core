<div class="related-organisations">
    <h4>Related Organisations</h4>
    <ul class="list-unstyled">
        @foreach($related['organisations']['contents'] as $col)
            <?php
            $result = array();
            $relation_type_text = "";
            foreach ($col['relations'] as $element) {
                $relation_type_text .= $element['relation_type_text'].", ";
              //  $to_identifier = $element['to_identifier'];
               // $result[$relation_type_text][$to_identifier] = $element;
            }
            $relation_type_text =  trim($relation_type_text,", ")
            ?>



            <li>
                <i class="fa fa-group icon-portal"></i>
                <small>{{ $relation_type_text }}</small>
                <a href="{{$col['to_url']}}"
                   title="{{ $col['to_title'] }}"
                   class="ro_preview"
                   ro_id="{{ $col['to_identifier'] }}">
                    {{$col['to_title']}}</a>
                {{ isset($col['to_funder']) ? "(funded by ". $col['to_funder'] .")" : '' }}
            </li>



        @endforeach
        @if($related['organisations']['total'] > 5)
            <li><a href="{{ $related['organisations']['searchUrl'] }}">View all {{ $related['organisations']['total'] }} related organisations</a></li>
        @endif
    </ul>
</div>