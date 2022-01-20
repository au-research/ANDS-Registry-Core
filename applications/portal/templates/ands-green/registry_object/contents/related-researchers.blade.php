<div class="related-researchers">
    <h4>Related People</h4>

    <ul class="list-unstyled">
        @if(is_array($related['researchers']) && $related['researchers']['total'] >0)
            <?  $relation_to_title = [];
                $relation_dupe_title = []
;               $dupes = 0;?>
            @foreach($related['researchers']['contents'] as $col)
                <?php
                $result = array();
                $relation_types = [];
                $relation_url = [];
                foreach ($col['relations'] as $element) {
                    $relation_types[] = $element['relation_type_text'];
                }
                $relation_types = array_unique($relation_types);
                $relation_type_text =  implode($relation_types,", ");
                $relation_to_title[$col['to_title'].$relation_type_text][] = $col['to_title'];
                $relation_dupe_title[$col['to_title']][] = $col;
                $relation_dupe_title[$col['to_title'].'same_group_found'] = 'false';
                $dupe_type_text[$col['to_title'].'dupe_type_text'] = $relation_type_text;
                 ?>
            @endforeach

            @foreach($related['researchers']['contents'] as $col)

             <?php $dupes = count($relation_dupe_title[$col['to_title']]);
            //if we have duplicates - check for a record from the same group
            ?>
             @if($dupes>1 && $relation_dupe_title[$col['to_title'].'same_group_found'] == 'false')
                    @foreach($relation_dupe_title[$col['to_title']] as $col2)
                        @if($col['from_group'] == $col2['to_group'] && $col['to_title'] == $col2['to_title'])
                         <?php //if record from the same group is found set found to true display the record
                             $relation_dupe_title[$col['to_title'].'same_group_found'] = 'true';?>
                             <li>
                                 <i class="fa fa-user icon-portal"></i>
                                 <small>{{ $dupe_type_text[$col['to_title'].'dupe_type_text'] }}</small>
                                 <?php
                                 if (!isset($col2['to_url'])){
                                     $col['to_url']="";
                                 } ?>
                                 <a href="{{ $col2['to_url'] }}"
                                    title="{{ $col2['to_title'] }}"
                                    class="ro_preview"
                                    tip="{{ $col2['to_title']}}"
                                    @if($col2['to_identifier_type'] == "ro:id")
                                        ro_id="{{ $col2['to_identifier'] }}"
                                    @else
                                    <?php $col_json = urlencode(json_encode($col2)); ?>
                                    identifier_relation_id = "{{ $col_json }}"
                                         @endif>
                                     {{$col2['to_title']}}</a>
                             </li>
                         @endif
                    @endforeach
                    @if(  $relation_dupe_title[$col['to_title'].'same_group_found'] == 'false')
                         <?php
                            //if no record from the same group is found display 1st record
                            $col2 = $relation_dupe_title[$col['to_title']][0] ?>
                         <li>
                             <i class="fa fa-user icon-portal"></i>
                             <small>{{$dupe_type_text[$col['to_title'].'dupe_type_text'] }}</small>
                             <?php
                             if (!isset($col2['to_url'])){
                                 $col['to_url']="";
                             } ?>
                             <a href="{{ $col2['to_url'] }}"
                                title="{{ $col2['to_title'] }}"
                                class="ro_preview"
                                tip="{{ $col2['to_title']}}"
                                @if($col2['to_identifier_type'] == "ro:id")
                                ro_id="{{ $col2['to_identifier'] }}"
                                @else

                                <?php $col_json = urlencode(json_encode($col2)); ?>
                                identifier_relation_id = "{{ $col_json }}"
                                     @endif>
                                 {{$col2['to_title']}}</a>
                         </li>
                    @endif

            @elseif($dupes<2)
            <li>
                <i class="fa fa-user icon-portal"></i>
                <small>{{ $dupe_type_text[$col['to_title'].'dupe_type_text'] }}</small>
               <?php
                if (!isset($col['to_url'])){
                    $col['to_url']="";
                } ?>
                <a href="{{ $col['to_url'] }}"
                   title="{{ $col['to_title'] }}"
                   class="ro_preview"
                   tip="{{ $col['to_title']}}"
                   @if($col['to_identifier_type'] == "ro:id")
                       ro_id="{{ $col['to_identifier'] }}"
                   @else

                        <?php $col_json = urlencode(json_encode($col)); ?>
                        identifier_relation_id = "{{ $col_json }}"
                    @endif>
                     {{$col['to_title']}}</a>
            </li>
            @endif
            @endforeach
            @if($related['researchers']['total'] > 5)
                <li><a href="{{ $related['researchers']['searchUrl'] }}">View all {{ $related['researchers']['total'] }} related people</a></li>
            @endif
        @endif
    </ul>
</div>