@if (isset($related['researchers']) && sizeof($related['researchers']['docs']) > 0)
    <?php
        // preparation
        $researchers_array = array();
        foreach ($related['researchers']['docs'] as $col) {
            $col['display_relationship'] = str_replace("Participant, ","", $col['display_relationship']);
            $col['display_relationship'] = str_replace(", Participant","", $col['display_relationship']);
            $researchers_array[$col['display_relationship']][] = $col;
        }
        $displayNum = 0;
    ?>
<p>
    <strong>Researchers: </strong>
    @foreach($researchers_array as $relation=>$researchers)
        @foreach($researchers as $key=>$col)
            <?php $displayNum++; ?>
            <a href="{{ base_url() }}{{$col['to_slug']}}/{{$col['to_id']}}"
               title="{{ $col['to_title'] }}"
               class="ro_preview"
               tip="{{ $col['display_description'] }}"
               ro_id="{{ $col['to_id'] }}">
                {{$col['to_title']}}
            </a>
            @if($relation!="Participant")
                ({{ $relation }})
            @endif
            @if($displayNum<$related['researchers']['count'])
                ,&nbsp;
            @endif
        @endforeach

    @endforeach
    @if($related['researchers']['count'] > 5)
        <br>
        <a href="{{ $related['researchers']['searchUrl'] }}">View all {{ $related['researchers']['count'] }} related researchers</a>
    @endif
</p>
@endif