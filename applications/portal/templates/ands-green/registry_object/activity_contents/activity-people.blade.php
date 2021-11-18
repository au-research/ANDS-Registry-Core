@if (isset($related['researchers']) && sizeof($related['researchers']['contents']) > 0)
    <?php
        // preparation
        $researchers_array = array();
        foreach ($related['researchers']['contents'] as $col) {
            $col['relations'][0]['relation_type_text'] = str_replace("Participant, ","", $col['relations'][0]['relation_type_text']);
            $col['relations'][0]['relation_type_text'] = str_replace(", Participant","", $col['relations'][0]['relation_type_text']);
            $researchers_array[$col['relations'][0]['relation_type_text']][] = $col;
        }
        $displayNum = 0;
    ?>
<p>
    <strong>Researchers: </strong>
    @foreach($researchers_array as $relation=>$researchers)
        @foreach($researchers as $key=>$col)
            <?php $displayNum++; ?>
            <a href="{{$col["to_url"]}}"
               title="{{ $col['to_title'] }}"
               class="ro_preview"
               tip="{{ $col['relations'][0]['relation_type_text'] }}"
               ro_id="{{ $col['to_identifier'] }}">
                {{$col['to_title']}}
            </a>
            @if($relation!="Participant")
                ({{ $relation }})
            @endif
            @if($displayNum<$related['researchers']['total'])
                ,&nbsp;
            @endif
        @endforeach

    @endforeach
    @if($related['researchers']['total'] > 5)
        <br>
        <a href="{{ $related['researchers']['searchUrl'] }}">View all {{ $related['researchers']['total'] }} related researchers</a>
    @endif
</p>
@endif