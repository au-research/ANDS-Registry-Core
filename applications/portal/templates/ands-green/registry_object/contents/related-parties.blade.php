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

    if(isset($related['researchers']['contents'])){
        foreach($related['researchers']['contents'] as $col){
            $arrayNames[] = $col['to_title'];
        }
    }
    if(isset($related['organisations']['contents'])){
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
             // we need to detect if the related party occurs more than once,
            // and if so let's check if one of those occurrences has the same group as the current object's
            ?>
            <a  style="margin-right:5px;"
            @if ($multi[$col['to_title']]>1 AND $multi[$col['to_title']] != 'found')
                <?php
                $same_group_found = false;
                //now loop through all the organisations to get the one from the same group
                // if it exists, else just out put the first one
                foreach($related['researchers']['contents'] as $col2){
                if($col2['to_title'] == $col['to_title'] && isset($col2['to_group']) && $col2['to_group'] == $col['from_group']){
                $same_group_found = true;
                ?>

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
                    identifier_relation_id="{{$col_json}}">
                    {{$col['to_title']}}
                @endif
                <?php
                }
                }
                //if we have relationships to duplicate party but we can't find one from the same group, then just output the first one
                if(!$same_group_found){ ?>
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
                    identifier_relation_id="{{$col_json}}">
                    {{$col['to_title']}}
                @endif
                <?php
                }
                $multi[$col['to_title']] = 'found';
                ?>
            @elseif($multi[$col['to_title']] != 'found')
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
                       identifier_relation_id="{{$col_json}}">
                        {{$col['to_title']}}
                  @endif
                <small>({{ $col['relations'][0]['relation_type_text'] }})</small>
            </a>
            @endif
        @endforeach
        @if($related['researchers']['total'] > 5)
            <a href="{{ $related['researchers']['searchUrl'] }}">View all {{ $related['researchers']['total'] }} related researchers</a>
        @endif
    @elseif(is_array($related['organisations']['contents']) && sizeof($related['organisations']['contents']) > 0)
        @foreach($related['organisations']['contents'] as $col)
            <?php
            // we need to detect if the related party occurs more than once,
            // and if so let's check if one of those occurrences has the same group as the current object's
            ?>
            @if ($multi[$col['to_title']]>1 AND $multi[$col['to_title']] != 'found')
                <?php
                $same_group_found = false;
                //now loop through all the organisations to get the one from the same group
                // if it exists, else just out put the first one
                foreach($related['organisations']['contents'] as $col2){
                    if($col2['to_title'] == $col['to_title'] && $col2['to_group'] == $col['from_group']){
                        $same_group_found = true;
                        ?>
                        <a href="{{$col2['to_url']}}"
                           class="ro_preview"
                           ro_id="{{$col2['to_identifier']}}"
                           style="margin-right:5px;">
                            {{ $col2['to_title'] }}
                            <small>({{ $col2['relations'][0]['relation_type_text'] }})</small>
                        </a>
                        <?php
                    }
                }
               //if we have relationships to duplicate party but we can't find one from the same group, then just output the first one
                if(!$same_group_found){ ?>
                         <a href="{{$col['to_url']}}}"
                            class="ro_preview"
                       ro_id="{{$col['to_identifier']}}"
                       style="margin-right:5px;">
                         {{ $col['to_title'] }}
                             <small>({{ $col2['relations'][0]['relation_type_text'] }})</small>
                        </a>
                       <?php
                }
                $multi[$col['to_title']] = 'found';
                ?>
            @elseif($multi[$col['to_title']] != 'found')
            <a href="{{$col['to_url']}}"
               class="ro_preview"
               ro_id="{{$col['to_identifier']}}"
               style="margin-right:5px;">
                 {{ $col['to_title'] }}
                <small>({{ $col['relations'][0]['relation_type_text'] }})</small>
            </a>
            @endif
        @endforeach
        @if(isset($related['organisations']['total']) && $related['organisations']['total'] > 5)
            <a href="{{ $related['organisations']['searchUrl'] }}">View all {{ $related['organisations']['total'] }} related organisations</a>
        @endif
    @endif
@endif