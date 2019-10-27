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
    foreach($related['organisations']['docs'] as $col){
        $arrayNames[] = $col['to_title'];
    }
    foreach($related['researchers']['docs'] as $col){
        $arrayNames[] = $col['to_title'];
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

    @elseif (is_array($related['researchers']) && sizeof($related['researchers']['docs']) > 0)
        @foreach($related['researchers']['docs'] as $col)
            <?php
            // we need to detect if the related party occurs more than once,
            // and if so let's check if one of those occurrences has the same group as the current object's
            ?>
            @if ($multi[$col['to_title']]>1 AND $multi[$col['to_title']] != 'found')
                <?php
                $same_group_found = false;
                //now loop through all the organisations to get the one from the same group
                // if it exists, else just out put the first one
                foreach($related['researchers']['docs'] as $col2){
                if($col2['to_title'] == $col['to_title'] && $col2['to_group'] == $col['from_group']){
                $same_group_found = true;
                ?>
                <a href="<?php echo base_url()?>{{$col2['to_slug']}}/{{$col2['to_id']}}"
                   tip="{{ $col2['display_description'] }}"
                   class="ro_preview"
                   ro_id="{{$col2['to_id']}}"
                   style="margin-right:5px;">
                    {{ $col2['to_title'] }}
                    <small>({{ $col2['display_relationship'] }})</small>
                </a>
                <?php
                }
                }
                //if we have relationships to duplicate party but we can't find one from the same group, then just output the first one
                if(!$same_group_found){ ?>
                <a href="<?php echo base_url()?>{{$col['to_slug']}}/{{$col['to_id']}}"
                   tip="{{ $col['display_description'] }}"
                   class="ro_preview"
                   ro_id="{{$col['to_id']}}"
                   style="margin-right:5px;">
                    {{ $col['to_title'] }}
                    <small>({{ $col['display_relationship'] }})</small>
                </a>
                <?php
                }
                $multi[$col['to_title']] = 'found';
                ?>
            @elseif($multi[$col['to_title']] != 'found')
            <a href="<?php echo base_url()?>{{$col['to_slug']}}/{{$col['to_id']}}"
               tip="{{ $col['display_description'] }}"
               class="ro_preview"
               @if(isset($col['to_id']) AND $col['to_id'] != "false")
                    ro_id="{{$col['to_id']}}"
               @elseif(isset($col['relation_identifier_id']))
                    identifier_relation_id="{{ $col['relation_identifier_id'] }}"
               @endif
               style="margin-right:5px;">
                 {{ $col['to_title'] }}
                <small>({{ $col['display_relationship'] }})</small>
            </a>
            @endif
        @endforeach
        @if($related['researchers']['count'] > 5)
            <a href="{{ $related['researchers']['searchUrl'] }}">View all {{ $related['researchers']['count'] }} related researchers</a>
        @endif
    @elseif(is_array($related['organisations']) && sizeof($related['organisations']['docs']) > 0)
        @foreach($related['organisations']['docs'] as $col)
            <?php
            // we need to detect if the related party occurs more than once,
            // and if so let's check if one of those occurrences has the same group as the current object's
            ?>
            @if ($multi[$col['to_title']]>1 AND $multi[$col['to_title']] != 'found')
                <?php
                $same_group_found = false;
                //now loop through all the organisations to get the one from the same group
                // if it exists, else just out put the first one
                foreach($related['organisations']['docs'] as $col2){
                    if($col2['to_title'] == $col['to_title'] && $col2['to_group'] == $col['from_group']){
                        $same_group_found = true;
                        ?>
                        <a href="<?php echo base_url()?>{{$col2['to_slug']}}/{{$col2['to_id']}}"
                           tip="{{ $col2['display_description'] }}"
                           class="ro_preview"
                           ro_id="{{$col2['to_id']}}"
                           style="margin-right:5px;">
                            {{ $col2['to_title'] }}
                            <small>({{ $col2['display_relationship'] }})</small>
                        </a>
                        <?php
                    }
                }
               //if we have relationships to duplicate party but we can't find one from the same group, then just output the first one
                if(!$same_group_found){ ?>
                         <a href="<?php echo base_url()?>{{$col['to_slug']}}/{{$col['to_id']}}"
                       tip="{{ $col['display_description'] }}"
                       class="ro_preview"
                       ro_id="{{$col['to_id']}}"
                       style="margin-right:5px;">
                         {{ $col['to_title'] }}
                        <small>({{ $col['display_relationship'] }})</small>
                        </a>
                       <?php
                }
                $multi[$col['to_title']] = 'found';
                ?>
            @elseif($multi[$col['to_title']] != 'found')
            <a href="<?php echo base_url()?>{{$col['to_slug']}}/{{$col['to_id']}}"
               tip="{{ $col['display_description'] }}"
               class="ro_preview"
               ro_id="{{$col['to_id']}}"
               style="margin-right:5px;">
                 {{ $col['to_title'] }}
                <small>({{ $col['display_relationship'] }})</small>
            </a>
            @endif
        @endforeach
        @if($related['organisations']['count'] > 5)
            <a href="{{ $related['organisations']['searchUrl'] }}">View all {{ $related['organisations']['count'] }} related organisations</a>
        @endif
    @endif
@endif