@if (isset($related['researchers']['contents']) && sizeof($related['researchers']['contents']) > 0)
    <?php
        // preparation
      //  $researchers_array = array();
      //  foreach ($related['researchers']['contents'] as $col) {
      //      $col['relations'][0]['relation_type_text'] = str_replace("Participant, ","", $col['relations'][0]['relation_type_text']);
       //     $col['relations'][0]['relation_type_text'] = str_replace(", Participant","", $col['relations'][0]['relation_type_text']);
       //     $researchers_array[$col['relations'][0]['relation_type_text']][] = $col;
      // }
       // var_dump($researchers_array );
        $displayNum = 0;
    ?>
<p>
    <strong>Researchers: </strong>


        @foreach($related['researchers']['contents'] as $col)

            <?php


                $result = array();
                $relation_types = [];
                $relation_url = [];
                foreach ($col['relations'] as $element) {
                    if($element['relation_type_text']!="" && $element['relation_type_text']!="Undertaken by"
                        && $element['relation_type_text']!="Participant")
                    $relation_types[] = $element['relation_type_text'];

                }
                $relation_types = array_unique($relation_types);
                $relation_type_text =  implode($relation_types,", ");
                $relation_to_title[$col['to_title'].$relation_type_text][] = $col['to_title'];
                $dupes = count($relation_to_title[$col['to_title'].$relation_type_text]);

                $displayNum++; ?>
            @if($col["to_identifier_type"]=="ro:id")
                <a href="{{$col['to_url']}}"
                title="{{ $col['to_title'] }}"
                class="ro_preview"
                ro_id="{{$col['to_identifier']}}">
                    {{$col['to_title']}} </a>
            @elseif($col["to_identifier_type"]!="ro:id")
                <?php  $col_json = urlencode(json_encode($col));
                if(!isset($col['to_title'])) $col['to_title'] = $col['to_identifier'];
                ?>
                <a href="false"
                title="{{ $col['to_title'] }}"
                class="ro_preview"
                identifier_relation_id="{{$col_json}}">
               {{$col['to_title']}}
                </a>
            @endif

            @if($relation_type_text !="")
                ({{$relation_type_text }})
            @endif
            @if($displayNum<$related['researchers']['total'] && $displayNum < 5)
                ,&nbsp;
            @endif

        @endforeach


    @if($related['researchers']['total'] > 5)
        <br>
        <a href="{{ $related['researchers']['searchUrl'] }}">View all {{ $related['researchers']['total'] }} related researchers</a>
    @endif
</p>
@endif