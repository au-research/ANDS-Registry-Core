@if($ro->relationships)
<?php
    $hasRelatedOrganisation = isset($ro->relationships['party_multi']);
    $hasRelatedGrantsOrProjects = isset($ro->relationships['activity']);
    $hasRelatedServices = isset($ro->relationships['service']);
    $hasRelatedInfo = isset($ro->relatedInfo);
    $hasRelatedPublication = false;
    $hasRelatedWebsite = false;

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

    $hasDerivedCollection = false;
    if ($ro->relationships && isset($ro->relationships['collection'])) {
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
                                    <h5><a href="" class="ro_preview" identifier_doi="{{$relatedInfo['identifier']['identifier_value']}}"><img src="<?php echo base_url()?>assets/core/images/icons/publications.png" style="margin-top: -2px; height: 24px; width: 24px;"> {{$relatedInfo['title']}}</a></h5>
                                    <p>
                                        <b>{{$relatedInfo['identifier']['identifier_type']}}</b> :
                                        @if($relatedInfo['identifier']['identifier_href'])
                                            <a href="{{$relatedInfo['identifier']['identifier_href']['href']}}">{{$relatedInfo['identifier']['identifier_value']}}</a><br />
                                        @else
                                            {{$relatedInfo['identifier']['identifier_value']}}
                                        @endif
                                    </p>
                                    @if($relatedInfo['relation']['url'])
                                        <p>URI : <a href="{{$relatedInfo['relation']['url']}}">{{$relatedInfo['relation']['url']}}</a></p>
                                    @endif
                                @endif
                            @endforeach
                        @endif
                    @endif

                    <!-- Only display collections with the relationship of isDerivedFrom or hasDerivedCollection -->


                    @if($hasDerivedCollection)
                    <h4>Related Data</h4>
                    <p>
                        @foreach($ro->relationships['collection'] as $col)
                            @if($col && ($col['relation_type']=='hasDerivedCollection' || $col['relation_type']=='isDerivedFrom'))
                        <img src="<?php echo base_url()?>assets/img/collection.png" style="margin-top: -2px; height: 24px; width: 24px;"/> <small>{{readable($col['relation_type'])}}</small> <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a><br />
                            @endif
                        @endforeach
                    </p>
                    @endif

                    @if($hasRelatedOrganisation)
                    <h4>Related Organisations</h4>
                    <p>
                        @foreach($ro->relationships['party_multi'] as $col)
                            @if($col)
                        <img src="<?php echo base_url()?>assets/img/party.png" style="margin-top: -2px; height: 24px; width: 24px;"/><small>{{readable($col['relation_type'])}}</small> <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a><br/>
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
                        <img src="<?php echo base_url()?>assets/img/activity.png" style="margin-top: -2px; height: 24px; width: 24px;"/><small>{{readable($col['relation_type'])}}</small> <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a><br />
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
                        <img src="<?php echo base_url()?>assets/img/service.png" style="margin-top: -2px; height: 24px; width: 24px;"/><small>{{readable($col['relation_type'])}}</small> <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}" class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a><br/>
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
                            <h5> {{$relatedInfo['title']}}</h5>
                            <p>
                                <b>{{$relatedInfo['identifier']['identifier_type']}}</b> :
                                @if($relatedInfo['identifier']['identifier_href'])
                                    <a href="{{$relatedInfo['identifier']['identifier_href']['href']}}">{{$relatedInfo['identifier']['identifier_value']}}</a><img class="identifier_logo" src= '.portal_url().'assets/core/images/icons/nla_icon.png alt="External Link"/><br />
                                @else
                                    {{$relatedInfo['identifier']['identifier_value']}}
                                @endif
                            </p>
                            @if($relatedInfo['relation']['url'])
                                <p>URI : <a href="{{$relatedInfo['relation']['url']}}">{{$relatedInfo['relation']['url']}}</a><img class="identifier_logo" src= '<?=portal_url();?>'assets/core/images/icons/nla_icon.png alt="External Link"/></p>
                            @endif
                        @endif
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
    @endif
@endif