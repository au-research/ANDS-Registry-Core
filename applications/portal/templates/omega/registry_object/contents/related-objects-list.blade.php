@if($ro->relationships)

<?php

    $hasRelatedOrganisation = isset($ro->relationships['party_multi']);
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
    @if($hasRelatedPublication || $hasDerivedCollection || $hasRelatedOrganisation || $hasRelatedGrantsOrProjects || $hasRelatedServices || $hasRelatedInfo)
        <div class="swatch-white">
            <div class="panel panel-primary element-no-top element-short-bottom panel-content">
                <div class="panel-heading">
                    <a href="">Related</a>
                </div>
                <div class="panel-body swatch-white">

                    @if($ro->relatedInfo)

                        @if($hasRelatedPublication)
                            <h4>Related Publications</h4>
                            @foreach($ro->relatedInfo as $relatedInfo)
                                @if($relatedInfo['type']=='publication')
                                    <h5><a href="" class="ro_preview" identifier_doi="{{$relatedInfo['identifier']['identifier_value']}}"><i class="fa fa-book icon-portal"></i> {{$relatedInfo['title']}}</a></h5>
                                    <p>
                                        <b>{{$relatedInfo['identifier']['identifier_type']}}</b> :
                                        <?php if(isset($relatedInfo['identifier']['identifier_href']['href'])){ ?>

                                            <a href="{{$relatedInfo['identifier']['identifier_href']['href']}}">{{$relatedInfo['identifier']['identifier_value']}}</a><br />
                                        <?php }else{ ?>
                                            {{$relatedInfo['identifier']['identifier_value']}}
                                        <?php } ?>
                                    </p>
                                    @if($relatedInfo['relation']['url'])
                                        <p>URI : <a href="{{$relatedInfo['relation']['url']}}">{{$relatedInfo['relation']['url']}}</a></p>
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
                            @if($col && ($col['relation_type']=='hasDerivedCollection' || $col['relation_type']=='isDerivedFrom'))
                            <i class="fa fa-folder-open icon-portal"></i> <small>{{readable($col['relation_type'])}}</small> <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a><br />
                            @endif
                        @endforeach
                    </p>
                    @endif
                    @if($hasRelatedCollection && $ro->core['class']!='collection')
                    <h4>Related Data</h4>
                    <p>
                        @foreach($ro->relationships['collection'] as $col)
                        <i class="fa fa-folder-open icon-portal"></i> <small>{{readable($col['relation_type'])}}</small> <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a><br />
                        @endforeach
                    </p>
                    @endif
                    @if($hasRelatedOrganisation)
                    <h4>Related Organisations</h4>
                    <p>
                        @foreach($ro->relationships['party_multi'] as $col)
                            @if($col)
                        <i class="fa fa-group icon-portal"></i> <small>{{readable($col['relation_type'])}}</small> <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a><br/>
                            @endif
                        @endforeach
                        @if(sizeof($ro->relationships['party_multi']) < $ro->relationships['party_multi_count_solr'])
                            <a href="{{portal_url()}}search/#!/related_{{$search_class}}_id={{$ro->core['id']}}/class=party/type=group">View all {{$ro->relationships['party_multi_count_solr']}} related organisations</a>
                        @endif
                    </p>
                    @endif

                    @if($hasRelatedGrantsOrProjects)
                    <h4>Related Grants and Projects</h4>
                   <p>
                        @foreach($ro->relationships['activity'] as $col)
                            @if($col)
                            <i class="fa fa-flask icon-portal"></i> <small>{{readable($col['relation_type'])}}</small> <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a><br />
                            @endif
                        @endforeach
                        @if(sizeof($ro->relationships['activity']) < $ro->relationships['activity_count_solr'])
                            <a href="{{portal_url()}}search/#!/related_{{$search_class}}_id={{$ro->core['id']}}/class=activity">View all {{$ro->relationships['activity_count_solr']}} related activities</a>
                        @endif
                   </p>
                    @endif

                    @if($hasRelatedServices)
                    <h4>Related Services</h4>
                    <p>
                        @foreach($ro->relationships['service'] as $col)
                            @if($col)
                            <i class="fa fa-wrench icon-portal"></i> <small>{{readable($col['relation_type'])}}</small> <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a><br/>
                            @endif
                        @endforeach
                        @if(sizeof($ro->relationships['service']) < $ro->relationships['service_count_solr'])
                            <a href="{{portal_url()}}search/#!/related_{{$search_class}}_id={{$ro->core['id']}}/class=service">View all {{$ro->relationships['service_count_solr']}} related services</a>
                        @endif
                    </p>
                    @endif

                    @if($hasRelatedWebsite)
                    <h4>Related Websites</h4>
                    @foreach($ro->relatedInfo as $relatedInfo)
                        @if($relatedInfo['type']=='website')
                            @if($relatedInfo['title'])
                            <h5><i class="fa fa-globe fa-lg icon-portal""></i> {{$relatedInfo['title']}}</h5>
                                <p>
                                    <b>{{$relatedInfo['identifier']['identifier_type']}}</b> :
                                    @if($relatedInfo['identifier']['identifier_href'])
                                        <a href="{{$relatedInfo['identifier']['identifier_href']['href']}}">{{$relatedInfo['identifier']['identifier_value']}}</a><br />
                                    @else
                                        {{$relatedInfo['identifier']['identifier_value']}}
                                    @endif
                                </p>
                            @else
                                <p> <i class="fa fa-globe icon-portal"></i>
                                    <b>{{$relatedInfo['identifier']['identifier_type']}}</b> :
                                    @if($relatedInfo['identifier']['identifier_href'])
                                    <a href="{{$relatedInfo['identifier']['identifier_href']['href']}}">{{$relatedInfo['identifier']['identifier_value']}}</a><br />
                                    @else
                                    {{$relatedInfo['identifier']['identifier_value']}}
                                    @endif
                                </p>
                            @endif
                            @if($relatedInfo['relation']['url'])
                                <p>URI : <a href="{{$relatedInfo['relation']['url']}}">{{$relatedInfo['relation']['url']}}</a></p>
                            @endif
                        @endif
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
    @endif
@endif