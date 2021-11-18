<?php
    $order = Array('isFundedBy','isManagedBy','isAdministeredBy');
?>
@if($related['organisations'] && $related['organisations']['total'] > 0)
    @foreach ($related['organisations']['contents'] as $col)
        @foreach($order as $o)
            @foreach ($col['relations'] as $rel)
                @if($rel['relation_type'] == $o)
                    <br/>
                    <?php
                        $outputRelation = $rel['relation_type_text'];
                    //    switch($o) {
                    //        case "isFundedBy": $outputRelation = "Funded by"; break;
                    //        case "isManagedBy": $outputRelation = "Managed by"; break;
                    //        case "isAdministeredBy": $outputRelation = "Administered by"; break;
                    //    }
                    ?>
                    <strong>{{ $outputRelation }}</strong>
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