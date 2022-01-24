@if( $ro->core['class'] != 'party' && $ro->core['class'] !='group' )
    <?php
    $contributors = '';
    ?>
   @if($ro->citations)
        @foreach($ro->citations as $citation)
            @if($citation['type']=='metadata' && $citation['contributors']!='')
             <?php  $contributors = explode(";",$citation['contributors']); ?>
            @endif
        @endforeach


    @endif
    <?php
    /*
    Set up an array which detects how many times a related party is linked.
    Currently this is based on exact match of to_title as we do not index identifiers of the related parties
    This is needed to avoid displaying the same party multiple times when aggregators
    have registered the same party maultiple times as separate objects
    */
    $arrayNames = array();

    if(isset($related['researchers'])){
        foreach($related['researchers']['contents'] as $col){
            $arrayNames[] = $col['to_title'];
        }
    }
    if(isset($related['organisations'])){
        foreach($related['organisations']['contents'] as $col){
            $arrayNames[] = $col['to_title'];
        }
    }

    //count each to_title occurrence
    $multi = array_count_values($arrayNames);
    ?>
    @if($contributors!='')
       <?php  $contributorString = ''; ?>
        @foreach($contributors as $contributor)
            <?php $contributorString .= ' <span id="contributor">'.$contributor."</span>; "; ?>
        @endforeach
            <?php $contributorString = trim($contributorString, "; ") ;?>
            {{$contributorString}}

    @elseif (isset($related['researchers']['contents']) && sizeof($related['researchers']['contents']) > 0)
        @foreach($related['researchers']['contents'] as $col)
            <?php
            $col_json = urlencode(json_encode($col));
            $result = array();
            $relation_types = [];
            $relation_url = [];
            foreach ($col['relations'] as $element) {
                $relation_types[] = $element['relation_type_text'];
            }
            $relation_types = array_unique($relation_types);
            $relation_type_text =  implode($relation_types,", ");
             // we need to detect if the related party occurs more than once,
            // and if so let's check if one of those occurrences has the same group as the current object's

            $relation_to_title[$col['to_title'].$relation_type_text][] = $col['to_title'];
            $relation_dupe_title[$col['to_title']][] = $col;
            $relation_dupe_title[$col['to_title'].'same_group_found'] = 'false';
            $dupe_type_text[$col['to_title'].'dupe_type_text'] = $relation_type_text;
            $dupe_type_text[$col['to_title'].$col['to_identifier']] = $relation_type_text;
            ?>
        @endforeach

        @foreach($related['researchers']['contents'] as $col)
            <?php $dupes = count($relation_to_title[$col['to_title'].$dupe_type_text[$col['to_title'].'dupe_type_text']]);
            //if we have duplicates - check for a record from the same group
            ?>
            @if($dupes>1 && $relation_dupe_title[$col['to_title'].'same_group_found'] == 'false')
                @foreach($relation_dupe_title[$col['to_title']] as $col2)
                    @if($col['from_group'] == $col2['to_group'] && $col['to_title'] == $col2['to_title'])
                        <?php //if record from the same group is found set found to true display the record
                        $relation_dupe_title[$col['to_title'].'same_group_found'] = 'true';?>

                        <a  style="margin-right:5px;"
                            @if($col2["to_identifier_type"]=="ro:id")
                            href="{{$col2['to_url']}}"
                            title="{{ $col2['to_title'] }}"
                            class="ro_preview"
                            ro_id="{{$col2['to_identifier']}}">
                            {{$col2['to_title']}}
                            @elseif($col2["to_identifier_type"]!="ro:id")
                                href="false"
                                title="{{ $col2['to_title'] }}"
                                class="ro_preview"
                                <?php $col_json = urlencode(json_encode($col2)); ?>
                                identifier_relation_id="{{$col_json}}">
                                {{$col['to_title']}}
                            @endif
                            <small>({{ $dupe_type_text[$col['to_title'].'dupe_type_text'] }})</small>
                        </a>
                    @endif
                @endforeach
                @if(  $relation_dupe_title[$col['to_title'].'same_group_found'] == 'false')
                    <?php
                    //if no record from the same group is found display 1st record
                    $col2 = $relation_dupe_title[$col['to_title']][0] ?>
                    <a  style="margin-right:5px;"
                        @if($col2["to_identifier_type"]=="ro:id")
                        href="{{$col2['to_url']}}"
                        title="{{ $col2['to_title'] }}"
                        class="ro_preview"
                        ro_id="{{$col2['to_identifier']}}">
                        {{$col2['to_title']}}
                        @elseif($col2["to_identifier_type"]!="ro:id")
                            href="false"
                            title="{{ $col2['to_title'] }}"
                            class="ro_preview"
                            <?php $col_json = urlencode(json_encode($col2)); ?>
                            identifier_relation_id="{{$col_json}}">
                            {{$col['to_title']}}
                        @endif
                        <small>({{ $dupe_type_text[$col['to_title'].'dupe_type_text'] }})</small>
                    </a>
                @endif

            @elseif($dupes<2)
                <a  style="margin-right:5px;"
                    @if($col["to_identifier_type"]=="ro:id")
                    href="{{$col['to_url']}}"
                    title="{{ $col['to_title'] }}"
                    class="ro_preview"
                    ro_id="{{$col['to_identifier']}}">
                    {{$col['to_title']}}
                    @elseif($col["to_identifier_type"]!="ro:id")
                        href="false"
                        title="{{ $col['to_title'] }}"
                        class="ro_preview"
                        <?php $col_json = urlencode(json_encode($col2)); ?>
                        identifier_relation_id="{{$col_json}}">
                        {{$col['to_title']}}
                    @endif
                    <small>({{  $dupe_type_text[$col['to_title'].$col['to_identifier']] }})</small>
                </a>
            @endif
        @endforeach

        @if($related['researchers']['total'] > 5)
            <a href="{{ $related['researchers']['searchUrl'] }}">View all {{ $related['researchers']['total'] }} related researchers</a>
        @endif


    @elseif(is_array($related['organisations']['contents']) && sizeof($related['organisations']['contents']) > 0)
        @foreach($related['organisations']['contents'] as $col)
            <?php
            $col_json = urlencode(json_encode($col));
            $result = array();
            $relation_types = [];
            $relation_url = [];
            foreach ($col['relations'] as $element) {
                $relation_types[] = $element['relation_type_text'];
            }
            $relation_types = array_unique($relation_types);
            $relation_type_text =  implode($relation_types,", ");
            // we need to detect if the related party occurs more than once,
            // and if so let's check if one of those occurrences has the same group as the current object's

            $relation_to_title[$col['to_title'].$relation_type_text][] = $col['to_title'];
            $relation_dupe_title[$col['to_title']][] = $col;
            $relation_dupe_title[$col['to_title'].'same_group_found'] = 'false';
            $dupe_type_text[$col['to_title'].'dupe_type_text'] = $relation_type_text;
            $dupe_type_text[$col['to_title'].$col['to_identifier']] = $relation_type_text;
            ?>
        @endforeach

        @foreach($related['researchers']['contents'] as $col)
            <?php $dupes = count($relation_to_title[$col['to_title'].$dupe_type_text[$col['to_title'].'dupe_type_text']]);
            //if we have duplicates - check for a record from the same group
            ?>
            @if($dupes>1 && $relation_dupe_title[$col['to_title'].'same_group_found'] == 'false')
                @foreach($relation_dupe_title[$col['to_title']] as $col2)
                    @if($col['from_group'] == $col2['to_group'] && $col['to_title'] == $col2['to_title'])
                        <?php //if record from the same group is found set found to true display the record
                        $relation_dupe_title[$col['to_title'].'same_group_found'] = 'true';?>

                        <a  style="margin-right:5px;"
                            @if($col2["to_identifier_type"]=="ro:id")
                            href="{{$col2['to_url']}}"
                            title="{{ $col2['to_title'] }}"
                            class="ro_preview"
                            ro_id="{{$col2['to_identifier']}}">
                            {{$col2['to_title']}}
                            @elseif($col2["to_identifier_type"]!="ro:id")
                                href="false"
                                title="{{ $col2['to_title'] }}"
                                class="ro_preview"
                                <?php $col_json = urlencode(json_encode($col2)); ?>
                                identifier_relation_id="{{$col_json}}">
                                {{$col['to_title']}}
                            @endif
                            <small>({{ $dupe_type_text[$col['to_title'].'dupe_type_text'] }})</small>
                        </a>
                    @endif
                @endforeach
                @if(  $relation_dupe_title[$col['to_title'].'same_group_found'] == 'false')
                    <?php
                    //if no record from the same group is found display 1st record
                    $col2 = $relation_dupe_title[$col['to_title']][0] ?>
                    <a  style="margin-right:5px;"
                        @if($col2["to_identifier_type"]=="ro:id")
                        href="{{$col2['to_url']}}"
                        title="{{ $col2['to_title'] }}"
                        class="ro_preview"
                        ro_id="{{$col2['to_identifier']}}">
                        {{$col2['to_title']}}
                        @elseif($col2["to_identifier_type"]!="ro:id")
                            href="false"
                            title="{{ $col2['to_title'] }}"
                            class="ro_preview"
                            <?php $col_json = urlencode(json_encode($col2)); ?>
                            identifier_relation_id="{{$col_json}}">
                            {{$col['to_title']}}
                        @endif
                        <small>({{ $dupe_type_text[$col['to_title'].'dupe_type_text'] }})</small>
                    </a>
                @endif

            @elseif($dupes<2)
                <a  style="margin-right:5px;"
                    @if($col["to_identifier_type"]=="ro:id")
                    href="{{$col['to_url']}}"
                    title="{{ $col['to_title'] }}"
                    class="ro_preview"
                    ro_id="{{$col['to_identifier']}}">
                    {{$col['to_title']}}
                    @elseif($col["to_identifier_type"]!="ro:id")
                        href="false"
                        title="{{ $col['to_title'] }}"
                        class="ro_preview"
                        <?php $col_json = urlencode(json_encode($col2)); ?>
                        identifier_relation_id="{{$col_json}}">
                        {{$col['to_title']}}
                    @endif
                    <small>({{  $dupe_type_text[$col['to_title'].$col['to_identifier']] }})</small>
                </a>
            @endif
        @endforeach
        @if(isset($related['organisations']['total']) && $related['organisations']['total'] > 5)
            <a href="{{ $related['organisations']['searchUrl'] }}">View all {{ $related['organisations']['total'] }} related organisations</a>
        @endif
    @endif
@endif