<?php
    $order = Array('isFundedBy','isManagedBy','isAdministeredBy');
?>
@if($related['organisations'] && $related['organisations']['total'] > 0)

    @foreach ($related['organisations']['contents'] as $col)
        <?php
        $result = array();
        foreach ($col['relations'] as $element) {
            $relation_type_text = $element['relation_type_text'];
            $to_identifier = $element['to_identifier'];
            $result[$relation_type_text][$to_identifier] = $element;
        }
        ?>
        @foreach($order as $o)
            @foreach ($result as $rel=>$to_id)
                @if($to_id[$col['to_identifier']]['relation_type'] == $o)
                    <br/>
                    <strong>{{$to_id[$to_identifier]['relation_type_text']}}</strong>
                    <a href="{{ $col['to_url'] }}"
                       class="ro_preview"
                       ro_id="{{ $col['to_identifier'] }}">
                        {{ trim($col['to_title']) }}
                    </a>
                @endif
            @endforeach
        @endforeach
    @endforeach

@endif