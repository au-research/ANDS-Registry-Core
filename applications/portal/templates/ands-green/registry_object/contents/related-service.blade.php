<div class="related-services">
    <h4>Related Services</h4>
    <ul class="list-unstyled">

        @foreach($related['services']['contents'] as $col)

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
                <i class="fa fa-wrench icon-portal"></i>
                <small>{{ $relation_type_text }}</small>
                <?php
                if(!isset($col['to_url'])){
                    $col['to_url']="";
                } ?>
                <a href="{{ $col['to_url'] }}"
                   title="{{ $col['to_title'] }}"
                   class="ro_preview"
                   tip="{{ $col['to_title'] }}"

                   @if($col["to_identifier_type"]=="ro:id")
                        ro_id="{{$col['to_identifier']}}"
                   @else
                        <?php $col_json = urlencode(json_encode($col));?>
                         identifier_relation_id="{{ $col_json }}"
                   @endif>
                   {{$col['to_title']}}</a>
                {{ isset($col['to_funder']) ? "(funded by ". $col['to_funder'] .")" : '' }}
            </li>
        @endforeach

        @if($related['services']['total'] > 5)
            <li><a href="{{ $related['services']['searchUrl'] }}">View all {{ $related['services']['total'] }} related services</a></li>
        @endif
    </ul>
</div>