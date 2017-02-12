<?php
    $order = Array('isFundedBy','isManagedBy','isAdministeredBy');
?>
@if($related['organisations'] && $related['organisations']['count'] > 0)
    @foreach ($related['organisations']['docs'] as $col)
        @foreach($order as $o)
            @if(in_array($o, $col['relation']))
                <br/>
                <?php
                    $outputRelation = $o;
                    switch($o) {
                        case "isFundedBy": $outputRelation = "Funded by"; break;
                        case "isManagedBy": $outputRelation = "Managed by"; break;
                        case "isAdministeredBy": $outputRelation = "Administered by"; break;
                    }
                ?>
                <strong>{{ $outputRelation }}</strong>
                <a href="{{ base_url($col['to_slug'].'/'.$col['to_id']) }}"
                   class="ro_preview"
                   ro_id="{{ $col['to_id'] }}">
                    {{ trim($col['to_title']) }}
                </a>
            @endif
        @endforeach
    @endforeach
@endif