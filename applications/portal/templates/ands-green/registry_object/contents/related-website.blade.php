<div class="related-websites">

    <h4>Related Websites</h4>
    <?php     $disp_count = 0 ; ?>
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
            $disp_count++;
            ?>
            @if($disp_count<6)
                <li>
             @else
                    <li class="morewebsites" style="display:none">
             @endif
                    <i class="fa fa-globe icon-portal"></i>
                    <small>{{ $relation_type_text  }} {{$disp_count}}</small>

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
            @if($disp_count==5 AND $related['websites']['total']>5)
                <a href="" class="showMoreWebsites showWebsites" >Show all {{$related['websites']['total']}} related websites</a>
            @elseif(($disp_count==$related['websites']['total']))
                <a href="" class="showLessWebsites showWebsites" style="display:none">Show less related websites</a>
            @endif

       @endforeach
   </ul>
</div>