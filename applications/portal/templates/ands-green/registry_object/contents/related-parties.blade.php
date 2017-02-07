@if( $ro->core['class'] != 'party' && $ro->core['class'] !='group' )
    @if (is_array($related['researchers']) && sizeof($related['researchers']['docs']) > 0)
        @foreach($related['researchers']['docs'] as $col)
            <?php
            $hasRights = false;
            if($ro->rights){
                foreach($ro->rights as $right){
                    if($right['type']=='rightsStatement') $hasRights=true;
                }
            }
            $itemprop = false;

            //construct itemprop
            if(isset($col['relation'])){
                if ( in_array('hasCollector', $col['relation'])
                        || in_array('IsPrincipalInvestigatorOf', $col['relation'])
                        || in_array('author', $col['relation'])
                ) {
                    $itemprop = "author creator";
                } elseif ( in_array('isParticipantIn', $col['relation']) ) {
                    $itemprop = "contributor";
                } elseif ( in_array('isOwnerOf', $col['relation']) && $hasRights) {
                    $itemprop = "copyrightHolder";
                } elseif ( in_array('isOwnedBy', $col['relation']) ) {
                    $itemprop = "accountablePerson";
                } else {
                    $itemprop = false;
                }
            }
            ?>

            <a href="<?php echo base_url()?>{{$col['to_slug']}}/{{$col['to_id']}}"
               tip="{{ $col['display_description'] }}"
               class="ro_preview"
               @if(isset($col['relation_identifier_id']))
                    identifier_relation_id="{{ $col['relation_identifier_id'] }}"
               @elseif(isset($col['to_id']))
                    ro_id="{{$col['to_id']}}"
               @endif
               style="margin-right:5px;">
            <span {{ $itemprop ? 'itemprop="'.$itemprop.'"' : '' }}>
                {{ $col['to_title'] }}
                <small>({{ $col['display_relationship'] }})</small>
            </span>
            </a>
        @endforeach
        @if($related['researchers']['count'] > 5)
            <a href="{{ $related['researchers']['searchUrl'] }}">View all {{ $related['researchers']['count'] }} related researchers</a>
        @endif
    @elseif(is_array($related['organisations']) && sizeof($related['organisations']['docs']) > 0)
        @foreach($related['organisations']['docs'] as $col)

            <?php
            $hasRights = false;
            if($ro->rights){
                foreach($ro->rights as $right){
                    if($right['type']=='rightsStatement') $hasRights=true;
                }
            }
            $itemprop = false;
            if(isset($col['relation'])){
            //construct itemprop
                if ( in_array('hasCollector', $col['relation'])
                        || in_array('IsPrincipalInvestigatorOf', $col['relation'])
                        || in_array('author', $col['relation'])
                ) {
                    $itemprop = "author creator";
                } elseif ( in_array('isParticipantIn', $col['relation']) ) {
                    $itemprop = "contributor";
                } elseif ( in_array('isOwnerOf', $col['relation']) && $hasRights) {
                    $itemprop = "copyrightHolder";
                } elseif ( in_array('isOwnedBy', $col['relation']) ) {
                    $itemprop = "accountablePerson";
                } else {
                    $itemprop = false;
                }
            }
            ?>

            <a href="<?php echo base_url()?>{{$col['to_slug']}}/{{$col['to_id']}}"
               tip="{{ $col['display_description'] }}"
               class="ro_preview"
               ro_id="{{$col['to_id']}}"
               style="margin-right:5px;">
            <span {{ $itemprop ? 'itemprop="'.$itemprop.'"' : '' }}>
                {{ $col['to_title'] }}
                <small>({{ $col['display_relationship'] }})</small>
            </span>
            </a>
        @endforeach
        @if($related['organisations']['count'] > 5)
            <a href="{{ $related['organisations']['searchUrl'] }}">View all {{ $related['organisations']['count'] }} related organisations</a>
        @endif
    @endif
@endif