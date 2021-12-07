<div class="related-websites">
    <h4>Related Websites</h4>
    <ul class="list-unstyled">
        @foreach($related['websites']['contents'] as $col)
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
                <i class="fa fa-globe icon-portal"></i>
                <small>{{ $relation_type_text  }}</small>

                {{--Display the identifiers--}}
                {{ $col['to_title'] }}
                <br/>
                @if(isset($col["to_url"]))
                    <a href="{{ $col['to_url'] }}"
                       tip="{{ $col['to_title'] }}">
                        {{ $col['to_identifier'] }}
                    </a>
                @else
                    {{ $col['to_identifier'] }}
                @endif
                {{--Notes display for this relation--}}
                @if(isset($col['to_notes']))

                    <p> {{ $col['to_notes']}} </p>

                @endif
           </li>
       @endforeach
   </ul>
</div>