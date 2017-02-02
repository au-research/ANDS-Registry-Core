@if (isset($related['researchers']) && sizeof($related['researchers']['docs']) > 0)
    <?php
        // preparation
        $researchers_array = array();
        foreach ($related['researchers']['docs'] as $col) {
            $researchers_array[$col['display_relationship']][] = $col;
        }
    ?>
<p>
    <strong>Researchers: </strong>
    @foreach($researchers_array as $relation=>$researchers)
        @foreach($researchers as $key=>$col)
            <a href="{{ base_url() }}{{$col['to_slug']}}/{{$col['to_id']}}"
               title="{{ $col['to_title'] }}"
               class="ro_preview"
               tip="{{ $col['display_description'] }}"
               ro_id="{{ $col['to_id'] }}">
                {{$col['to_title']}}
            </a>
            @if($key!=sizeof($researchers)-1)
                ,&nbsp;
            @endif
        @endforeach
        ({{ $relation }})
    @endforeach
    @if($related['researchers']['count'] > 5)
        <br>
        <a href="{{ $related['researchers']['searchUrl'] }}">View all {{ $related['researchers']['count'] }} related researchers</a>
    @endif
</p>
@endif