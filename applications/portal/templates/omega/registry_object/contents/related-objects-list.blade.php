@if($ro->relationships)

<?php

    $relation = Array('isFundedBy','isManagedBy','isAdministeredBy');
    if($ro->core['class']=='activity'){
        $hasRelatedOrganisation = false;
        if(isset($ro->relationships['party_multi'])){
            foreach($ro->relationships['party_multi'] as $aparty){
                if(!in_array($aparty['relation_type'],$relation)){
                    $hasRelatedOrganisation = true;
                }
            }
        }

    }else{
        $hasRelatedOrganisation = isset($ro->relationships['party_multi']);
    }
    $hasRelatedGrantsOrProjects = isset($ro->relationships['activity']);
    $hasRelatedServices = isset($ro->relationships['service']);
?>
    @if($ro->relatedInfo)
        <?php
            $hasRelatedInfo = true;
        ?>
    @else
        <?php
            $hasRelatedInfo = false; ?>
    @endif
<?php
    $hasRelatedPublication = false;
    $hasRelatedWebsite = false;
    $hasDerivedCollection = false;
    $hasRelatedCollection = false;

    $search_class = $ro->core['class'];
    if($ro->core['class']=='party') {
        if (strtolower($ro->core['type'])=='person'){
            $search_class = 'party_one';
        } elseif(strtolower($ro->core['type'])=='group') {
            $search_class = 'party_multi';
        }
    }

    $relatedSearchQuery = portal_url().'search/#!/related_'.$search_class.'_id='.$ro->core['id'];
    if($ro->identifiermatch && sizeof($ro->identifiermatch) > 0){
        foreach($ro->identifiermatch as $mm)
        {
            $relatedSearchQuery .= '/related_'.$search_class.'_id='.$mm['registry_object_id'];
        }
    }

    if($hasRelatedInfo){
        foreach($ro->relatedInfo as $relatedInfo) {
            if ($relatedInfo['type']=='publication'){
                $hasRelatedPublication = true;
                break; // stop looking once found!!!
            }
        }

        foreach($ro->relatedInfo as $relatedInfo) {
            if ($relatedInfo['type']=='website'){
                $hasRelatedWebsite = true;
                break; // stop looking once found!!!
            }
        }
    }


    if ($ro->relationships && isset($ro->relationships['collection'])) {
        $hasRelatedCollection = true;
        foreach ($ro->relationships['collection'] as $col) {
            if ($col['relation_type']=='hasDerivedCollection' || $col['relation_type']=='isDerivedFrom') {
                $hasDerivedCollection = true;
                break;
            }
        }
    }
?>
    @if(($hasRelatedCollection && $ro->core['class']!='collection') || $hasRelatedPublication || $hasDerivedCollection || $hasRelatedOrganisation || $hasRelatedGrantsOrProjects || $hasRelatedServices)
        <div class="swatch-white">
            <div class="panel panel-primary element-no-top element-short-bottom panel-content">
                <!-- <div class="panel-heading"> Related </div> -->
                <div class="panel-body swatch-white">

                    @if($ro->relatedInfo)

                        @if($hasRelatedPublication)
                            <h4>Related Publications</h4>
                            @foreach($ro->relatedInfo as $relatedInfo)
                                @if($relatedInfo['type']=='publication')
                                    <?php
                                    $description = '';
                                    $relationship = '';
                                    if(isset($relatedInfo['relation']['relation_type']) && $relatedInfo['relation']['relation_type']!=''){
                                        $relationship = readable($relatedInfo['relation']['relation_type']);
                                    }
                                     if(isset($relatedInfo['relation']['description'])&& $relatedInfo['relation']['description']!=''){
                                       $description .= 'tip="'.$relatedInfo['relation']['description'].'"';
                                     }

                                     elseif(isset($relatedInfo['identifier']['identifier_href']['hover_text'])&&$relatedInfo['identifier']['identifier_href']['hover_text']!=''){
                                        $description = 'tip="'.$relatedInfo['identifier']['identifier_href']['hover_text'].'"';
                                     }
                                     elseif(isset($relatedInfo['identifier']['identifier_value'])&& $relatedInfo['identifier']['identifier_value']!=''){
                                         $description = 'tip="'.$relatedInfo['identifier']['identifier_value'].'"';
                                     }
                                     else{
                                        $description = 'tip="'.$relatedInfo['title'].'"';
                                     }
                                    ?>
                                     <?php if($relatedInfo['identifier']['identifier_type'] == 'doi'  && isset($relatedInfo['title']) && $relatedInfo['title']!=''){ ?>

                                    <i class="fa fa-book icon-portal"></i> <small>{{$relationship}} </small> <a  href="" class="ro_preview" identifier_doi="{{$relatedInfo['identifier']['identifier_value']}}" tip="{{$relatedInfo['title']}}">{{$relatedInfo['title']}}</a><p>
                                    <?php }
                                     elseif($relatedInfo['identifier']['identifier_type'] != 'doi' && isset($relatedInfo['title']) && $relatedInfo['title']!=''){ ?>
                                        <i class="fa fa-book icon-portal"></i><small>{{$relationship}} </small> {{$relatedInfo['title']}}.<p>
                                    <?php }
                                    else{
                                        ?>
                                        <i class="fa fa-book icon-portal"></i>{{$relationship}}
                                    <?php
                                    }
                                     ?>

                                     <b>{{$relatedInfo['identifier']['identifier_type']}}</b> :
                                        <?php if(isset($relatedInfo['identifier']['identifier_href']['href'])){ ?>

                                            <a href="{{$relatedInfo['identifier']['identifier_href']['href']}}" {{$description}}>{{$relatedInfo['identifier']['identifier_value']}}</a><br />
                                        <?php }else{ ?>
                                            {{$relatedInfo['identifier']['identifier_value']}}
                                        <?php } ?>
                                    </p>
                                    @if($relatedInfo['relation']['url'])
                                        <p><small>{{$relationship}} </small>URI : <a href="{{$relatedInfo['relation']['url']}}" {{$description}}>{{$relatedInfo['relation']['url']}}</a></p>
                                    @endif
                        @if($relatedInfo['notes'])
                        <p>
                            {{$relatedInfo['notes']}}
                        </p>
                        @endif
                                @endif
                            @endforeach
                        @endif
                    @endif

                    <!-- Only display collections with the relationship of isDerivedFrom or hasDerivedCollection -->


                    @if($hasDerivedCollection && $ro->core['class']=='collection')
                    <h4>Related Data</h4>
                    <p>
                        @foreach($ro->relationships['collection'] as $col)
                        <?php
                        $description = '';
                        if(isset($col['relation_description']) && $col['relation_description']!=''){
                            $description = 'tip="'.$col['title']."<br/>".$col['relation_description'].'"';
                        }else{
                            $description = 'tip="'.$col['title'].'"';
                        }
                        ?>
                            @if($col['slug'] && $col['registry_object_id'])
                            <i class="fa fa-folder-open icon-portal"></i> <small>{{readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class'])}}</small> <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" {{$description}} class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a><br />
                            @elseif(isset($col['identifier_relation_id']))
                            <i class="fa fa-folder-open icon-portal"></i> <small>{{readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class'])}}</small> <a href="<?php echo base_url()?>" title="{{$col['title']}}" {{$description}} class="ro_preview" identifier_relation_id="{{$col['identifier_relation_id']}}">{{$col['title']}}</a><br/>
                            @endif
                        @endforeach
                    </p>
                    @endif
                    @if($hasRelatedCollection && $ro->core['class']!='collection')
                    <h4>Related Data</h4>
                    <p>
                        @foreach($ro->relationships['collection'] as $col)
                        <?php
                        $description = '';
                        if(isset($col['relation_description']) && $col['relation_description']!=''){
                            $description = 'tip="'.$col['title']."<br/>".$col['relation_description'].'"';
                        }else{
                            $description = 'tip="'.$col['title'].'"';
                        }
                        ?>
                            @if($col['slug'] && $col['registry_object_id'])
                            <i class="fa fa-folder-open icon-portal"></i> <small>{{readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class'])}}</small> <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" {{$description}} ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a><br />
                            @elseif(isset($col['identifier_relation_id']))
                            <i class="fa fa-folder-open icon-portal"></i> <small>{{readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class'])}}</small> <a href="<?php echo base_url()?>" title="{{$col['title']}}" class="ro_preview" {{$description}} identifier_relation_id="{{$col['identifier_relation_id']}}">{{$col['title']}}</a><br/>
                            @endif
                        @endforeach
                        @if($ro->relationships['collection_count'] > $relatedLimit)
                        <a href="{{$relatedSearchQuery}}/class=collection">View all {{$ro->relationships['collection_count']}} related data</a>
                        @endif
                    </p>
                    @endif
                    @if($hasRelatedOrganisation)
                    <h4>Related Organisations</h4>
                    <p>
                        @foreach($ro->relationships['party_multi'] as $col)

                        <?php
                        if($ro->core['class']!='activity' || ($ro->core['class']=='activity'&& !in_array($col['relation_type'],$relation))){
                        if(isset($col['relation_description']) && $col['relation_description']!=''){
                            $description = 'tip="'.$col['title']."<br/>".$col['relation_description'].'"';
                        }else{
                            $description = 'tip="'.$col['title'].'"';
                        }
                        ?>
                            @if($col['slug'] && $col['registry_object_id'])
                            <i class="fa fa-group icon-portal"></i> <small>{{readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class'],$col['class'])}}</small> <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" {{$description}} ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a><br/>
                            @elseif(isset($col['identifier_relation_id']))
                            <i class="fa fa-group icon-portal"></i> <small>{{readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class'],$col['class'])}}</small> <a href="<?php echo base_url()?>" title="{{$col['title']}}" class="ro_preview" {{$description}} identifier_relation_id="{{$col['identifier_relation_id']}}">{{$col['title']}}</a><br/>
                            @endif
                        <?php } ?>
                        @endforeach
                        @if($ro->relationships['party_multi_count'] > $relatedLimit)
                            <a href="{{$relatedSearchQuery}}/class=party/type=group">View all {{$ro->relationships['party_multi_count']}} related organisations</a>
                        @endif
                    </p>
                    @endif

                    @if($hasRelatedGrantsOrProjects)
                    <h4>Related Grants and Projects</h4>
                   <p>
                        @foreach($ro->relationships['activity'] as $col)
                       <?php
                       $funder = '';
                       if(isset($col['relation_description']) && $col['relation_description']!=''){
                           $description = 'tip="'.$col['title']."<br/>".$col['relation_description'].'"';
                       }else{
                           $description = 'tip="'.$col['title'].'"';
                       }
                       if(isset($col['funder'])) $funder = $col['funder'];
                       ?>
                           @if($col['slug'] && $col['registry_object_id'])
                           <i class="fa fa-flask icon-portal"></i> <small>{{readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class'])}}</small> <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" {{$description}} ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a> {{$funder}}<br />
                           @elseif(isset($col['identifier_relation_id']))
                           <i class="fa fa-flask icon-portal"></i> <small>{{readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class'])}}</small> <a href="<?php echo base_url()?>" title="{{$col['title']}}" class="ro_preview" {{$description}} identifier_relation_id="{{$col['identifier_relation_id']}}">{{$col['title']}}</a> {{$funder}}<br/>
                           @endif
                        @endforeach
                        @if($ro->relationships['activity_count'] > $relatedLimit)
                            <a href="{{$relatedSearchQuery}}/class=activity">View all {{$ro->relationships['activity_count']}} related activities</a>
                        @endif
                   </p>
                    @endif

                    @if($hasRelatedServices)
                    <h4>Related Services</h4>
                    <p>
                        @foreach($ro->relationships['service'] as $col)

                        <?php
                        if(isset($col['relation_description']) && $col['relation_description']!=''){
                            $description = 'tip="'.$col['title']."<br/>".$col['relation_description'].'"';
                        }else{
                            $description = 'tip="'.$col['title'].'"';
                        }
                        ?>
                            @if($col['slug'] && $col['registry_object_id'])
                            <i class="fa fa-wrench icon-portal"></i> <small>{{readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class'])}}</small> <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" {{$description}} ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a><br/>
                            @elseif(isset($col['identifier_relation_id']))
                            <i class="fa fa-wrench icon-portal"></i> <small>{{readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class'])}}</small> <a href="<?php echo base_url()?>" title="{{$col['title']}}" class="ro_preview" {{$description}} identifier_relation_id="{{$col['identifier_relation_id']}}">{{$col['title']}}</a><br/>
                            @endif
                        @endforeach
                        @if($ro->relationships['service_count'] > $relatedLimit)
                            <a href="{{$relatedSearchQuery}}/class=service">View all {{$ro->relationships['service_count']}} related services</a>
                        @endif
                    </p>
                    @endif

                    @if($hasRelatedWebsite)
                    <h4>Related Websites</h4>
                    @foreach($ro->relatedInfo as $relatedInfo)
                        @if($relatedInfo['type']=='website')
                    <?php
                    $description = '';
                    $relationship = '';
                    if(isset($relatedInfo['relation']['relation_type']) && $relatedInfo['relation']['relation_type']!=''){
                        $relationship = readable($relatedInfo['relation']['relation_type']);
                    }
                    if(isset($relatedInfo['relation']['description'])&& $relatedInfo['relation']['description']!=''){
                        $description .= 'tip="'.$relatedInfo['relation']['description'].'"';
                    }
                    elseif(isset($relatedInfo['identifier']['identifier_href']['hover_text'])&&$relatedInfo['identifier']['identifier_href']['hover_text']!=''){
                        $description = 'tip="'.$relatedInfo['identifier']['identifier_href']['hover_text'].'"';
                    }
                    elseif(isset($relatedInfo['identifier']['identifier_value'])&& $relatedInfo['identifier']['identifier_value']!=''){
                        $description = 'tip="'.$relatedInfo['identifier']['identifier_value'].'"';
                    }
                    else{
                        $description = 'tip="'.$relatedInfo['title'].'"';
                    }
                    ?>
                            @if($relatedInfo['title'])
                            <i class="fa fa-globe icon-portal""></i>  <small>{{$relationship}} </small> {{$relatedInfo['title']}}
                                <p>
                                    @if($relatedInfo['identifier']['identifier_href']['display_text'])
                                    <b>{{$relatedInfo['identifier']['identifier_href']['display_text']}}</b> :
                                    @else
                                    <b>{{$relatedInfo['identifier']['identifier_type']}}</b>:
                                    @endif
                                    @if($relatedInfo['identifier']['identifier_href'])
                                        <a href="{{$relatedInfo['identifier']['identifier_href']['href']}}" {{$description}}>{{$relatedInfo['identifier']['identifier_value']}}</a><br />
                                    @else
                                        {{$relatedInfo['identifier']['identifier_value']}}
                                    @endif
                                </p>
                            @else
                                <p> <i class="fa fa-globe icon-portal"></i> <small>{{$relationship}} </small>
                                    @if($relatedInfo['identifier']['identifier_href']['display_text'])
                                        <b>{{$relatedInfo['identifier']['identifier_href']['display_text']}}</b> :
                                    @else
                                        <b>{{$relatedInfo['identifier']['identifier_type']}}</b> :
                                    @endif
                                    @if($relatedInfo['identifier']['identifier_href'])

                                    <a href="{{$relatedInfo['identifier']['identifier_href']['href']}}" {{$description}} >{{$relatedInfo['identifier']['identifier_value']}}</a><br />
                                    @else
                                    {{$relatedInfo['identifier']['identifier_value']}}
                                    @endif
                                </p>
                            @endif
                            @if($relatedInfo['relation']['url'])

                                <p>URI : <a href="{{$relatedInfo['relation']['url']}}" tip="Resolve this URI">{{$relatedInfo['relation']['url']}}</a></p>
                            @endif
                            @if($relatedInfo['notes'])
                            <p>
                                {{$relatedInfo['notes']}}
                            </p>
                            @endif
                        @endif
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
    @endif
@endif