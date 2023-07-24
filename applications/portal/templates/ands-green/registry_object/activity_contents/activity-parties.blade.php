<?php
    $order = Array('isFundedBy','isManagedBy','isAdministeredBy');
    $processed = array();
?>
{{--
use the order to find organisations to display in the header
if an organisation exists with that given relationship
display it but only once
store the relationship and title of the organisation in an array to stop them being displayed multiple times

--}}
@if(isset($related['organisations']['contents'] ) && sizeof($related['organisations']['contents']) > 0)

    @foreach($order as $o)
        @foreach ($related['organisations']['contents'] as $col)
            @foreach ($col['relations'] as $element)
                @if($element['relation_type'] == $o && !in_array($element['relation_type'].$col['to_title'], $processed))
                    <br/>
                    <strong>{{ $element['relation_type_text']}}</strong>
                    <a href="{{ $col['to_url'] }}" class="ro_preview" ro_id="{{ $col['to_identifier'] }}">
                        {{ trim($col['to_title']) }}
                    </a>
                   <?php $processed[] = $element['relation_type'].$col['to_title'];?>
                @endif
            @endforeach
        @endforeach
    @endforeach

@endif