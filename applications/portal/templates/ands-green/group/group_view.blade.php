@extends('layouts/right-sidebar')
@section('header')
<div class="row element element-short-top">
    <div class="col-md-12">
        <div class="panel swatch-white">
            <div class="panel-body">
                <header> <div class="header_title">
                    <?php

                        $logo = false;

                        if (isset($group['logo'])) {
                            $logo = $group['logo'];
                        }

                        if ($group['has_custom_data']) {
                            if (isset($group['custom_data']['hide_logo'])) {
                                if ($group['custom_data']['hide_logo']) {
                                    if(isset($group['custom_data']['logo'])) {
                                        $logo = $group['custom_data']['logo'];
                                    }
                                }
                            }
                        }
                    ?>
                    @if($logo)
                        <img src="{{$logo}}" alt="Logo" class="header-logo animated fadeInDown"/>
                    @endif
                  <h1 class="big hairline os-animation animated fadeIn">{{$group['title']}}</h1>
                    </div>  </header>
            </div>

        </div>
    </div>
</div>
@stop

@section('content')
    <?php
        $subjects_list = '';
        if (sizeof($group['facet']['subjects']) > 3) {
            $subjects_list = 'including <a href="'.base_url('search').'#!/group='.$group['title'].'/subject_value_resolved='.rawurlencode($group['facet']['subjects'][0]['name']).'">'.$group['facet']['subjects'][0]['name'].'</a>, <a href="'.base_url('search').'#!/group='.$group['title'].'/subject_value_resolved='.rawurlencode($group['facet']['subjects'][1]['name']).'">'.$group['facet']['subjects'][1]['name'].'</a> and <a href="'.base_url('search').'#!/group='.$group['title'].'/subject_value_resolved='.rawurlencode($group['facet']['subjects'][2]['name']).'">'.$group['facet']['subjects'][2]['name'].'</a>';
        } elseif (sizeof($group['facet']['subjects']) > 0) {
            $subjects_list = 'including ';
            foreach ($group['facet']['subjects'] as $s) {
                $subjects_list .= ',  <a href="'.base_url('search').'#!/group='.$group['title'].'/subject_value_resolved='.rawurlencode($s['name']).'">'.$s['name'].'</a> ';
            }
        }
    ?>

    @if($group['has_custom_data'])
        @if(isset($group['custom_data']['overview']))
        <div class="panel swatch-white">
            <!-- <div class="panel-heading">Overview</div> -->
            <div class="panel-body">{{$group['custom_data']['overview']}}</div>
        </div>
        @endif
    @endif

    @if($group['has_custom_data'])
        @if(isset($group['custom_data']['researchdarea']))
        <div class="panel swatch-white">
            <div class="panel-heading">Research and Key Research Areas</div>
            <div class="panel-body">{{$group['custom_data']['researchdarea']}}</div>
        </div>
        @endif
    @endif

    <div class="panel swatch-white">
        <div class="panel-heading">Data Profile</div>
        <div  class="panel-body">
            {{$group['title']}}  has <a href="{{ base_url('search') }}#!/class=collection/group={{ rawurlencode($group['title']) }}">{{$group['counts']}} data records</a> in Research Data Australia,
            which cover {{sizeof($group['facet']['subjects'])}} subjects areas {{$subjects_list}} and involve  <a href="{{base_url()}}search#!/group={{$group['title']}}/type=group/class=party">{{$group['groups_count']}} group(s)</a>.
            All of the information provided by  {{$group['title']}} can be accessed from the box on the right hand side of this page.
        </div>
        @if($group['has_custom_data'])
            @if(isset($group['custom_data']['researchdataprofile']))
            <div class="panel-body">
                {{$group['custom_data']['researchdataprofile']}}
            </div>
            @endif
        @endif
    </div>

    @if($group['has_custom_data'])
        @if(isset($group['custom_data']['researchsupport']))
        <div class="panel swatch-white">
            <div class="panel-heading">Research Support</div>
            <div class="panel-body">{{$group['custom_data']['researchsupport']}}</div>
        </div>
        @endif
    @endif

    @if(sizeof($group['facet']['subjects']) > 0)
    <div class="panel swatch-white">
        <div class="panel-heading">Subjects Covered</div>
        <div class="panel-body widget_tag_cloud">
            <div class="tag-cloud">
                <ul>
                    <?php
                        if(sizeof($group['facet']['subjects'] > 50)) {
                            $orig = sizeof($group['facet']['subjects']);
                        }
                        if($this->input->get('m')!='allsubjects') array_splice($group['facet']['subjects'], 50);
                    ?>
                    @foreach($group['facet']['subjects'] as $subject)
                        <li><a href="{{base_url()}}search#!/group={{$group['title']}}/subject_value_resolved={{ rawurlencode($subject['name']) }}">{{ ucwords(strtolower($subject['name'])) }} <span>({{$subject['num']}})</span></a></li>
                    @endforeach
                </ul>
                @if($orig > sizeof($group['facet']['subjects']))
                    Displaying: {{sizeof($group['facet']['subjects'])}} out of {{$orig}} available subjects. <a href="{{current_url()}}?m=allsubjects">View All</a>
                @endif
            </div>
        </div>
    </div>
    @endif
@stop

@section('sidebar')

@if(isset($group['has_custom_data']))
    @if(isset($group['custom_data']['contact']))
    <div class="panel swatch-white">
        <div class="panel-heading">Contact</div>
        <div class="panel-body">{{$group['custom_data']['contact']}}</div>
    </div>
    @endif
@endif

<div class="panel swatch-white">
    <!-- <div class="panel-heading">Registry Contents</div> -->
    <div class="panel-body">
        <ul class="listy">
            @if($group['facet']['class']['collection']!=0)
                <li><a href="{{base_url()}}search#!/group={{$group['title']}}/class=collection">{{class_name('collection_data')}} <small>({{$group['facet']['class']['collection']-$group['facet']['types']['software']}})</small></a></li>
            @endif
            @if($group['facet']['types']['software']!=0)
                <li><a href="{{base_url()}}search#!/group={{$group['title']}}/class=collection">{{class_name('collection_software')}} <small>({{$group['facet']['types']['software']}})</small></a></li>
            @endif
            @if($group['facet']['class']['party']!=0)
                <li><a href="{{base_url()}}search#!/group={{$group['title']}}/class=party">{{class_name('party')}} <small>({{$group['facet']['class']['party']}})</small></a></li>
            @endif
            @if($group['facet']['class']['activity']!=0)
                <li><a href="{{base_url()}}search#!/group={{$group['title']}}/class=activity">{{class_name('activity')}} <small>({{$group['facet']['class']['activity']}})</small></a></li>
            @endif
            @if($group['facet']['class']['service']!=0)
                <li><a href="{{base_url()}}search#!/group={{$group['title']}}/class=service">{{class_name('service')}} <small>({{$group['facet']['class']['service']}})</small></a></li>
            @endif
        </ul>
    </div>
</div>

@if(isset($group['groups']) && is_array($group['groups']) && sizeof($group['groups']) > 0)
<div class="panel swatch-white">
    <div class="panel-heading">Organisations & Groups</div>
    <div class="panel-body">
        <ul class="listy">
            <?php $i = 0  ?>
            @foreach($group['groups'] as $gr)
            @if($i++ < 5)
                <li><a href="{{base_url()}}{{$gr['slug']}}/{{$gr['id']}}">{{$gr['title']}}</a></li>
            @else
                <li class="listItem hidden"><a href="{{base_url()}}{{$gr['slug']}}/{{$gr['id']}}">{{$gr['title']}}</a></li>
            @endif
            @endforeach
            @if($group['groups_count'] > 5)
            <span><a href="{{base_url()}}search#!/group={{$group['title']}}/type=group/class=party">View All {{$group['groups_count']}}.</a></span>
            @endif
        </ul>
    </div>
</div>
@endif

@if(isset($group['latest_collections']))
<div class="panel swatch-white">
    <?php
    if($group['counts']>4 ){
        $count = 5;
    }else{
        $count=$group['counts'];
    } ?>
    <div class="panel-heading">Last {{$count}} Data Records Added</div>
    <div class="panel-body">
        <ul class="listy">
            @foreach($group['latest_collections'] as $gr)
                <li><a href="{{base_url()}}{{$gr['slug']}}/{{$gr['id']}}">{{$gr['title']}}</a></li>
            @endforeach
            <?php if($group['counts']>5) { ?>
            <li><a href="{{ base_url() }}search#!/group={{ $group['title'] }}/class=collection">View All Collections</a></li>
           <?php ;} ?>
        </ul>
    </div>
</div>
@endif

@if(isset($group['has_custom_data']))
    @if(isset($group['custom_data']['identifiers']))
    <div class="panel swatch-white">
        <div class="panel-heading">Identifiers</div>
        <div class="panel-body">{{$group['custom_data']['identifiers']}}</div>
    </div>
    @endif
@endif

@stop