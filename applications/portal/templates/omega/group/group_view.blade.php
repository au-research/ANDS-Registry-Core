@extends('layouts/right-sidebar')
@section('header')
<section class="section swatch-white section-text-shadow section-innder-shadow element-short-top element-short-bottom">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <header>
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
                    
                    <h1 class="hairline bordered-normal">{{$group['title']}}</h1>
                </header>
            </div>
        </div>
    </div>
</section>
@stop

@section('content')
    <?php
        $subjects_list = '';
        if (sizeof($group['facet']['subjects']) > 3) {
            $subjects_list = 'including '.$group['facet']['subjects'][0]['name'].', '.$group['facet']['subjects'][1]['name'].' and '.$group['facet']['subjects'][2]['name'];
        } elseif (sizeof($group['facet']['subjects']) > 0) {
            $subjects_list = 'including ';
            foreach ($group['facet']['subjects'] as $s) {
                $subjects_list .= ', '. $s['name'] .'';
            }
        }
    ?>

    <div class="panel panel-primary swatch-white">
        <div class="panel-body">
            To date, {{$group['title']}} has {{$group['counts']}} collection records in Research Data Australia, which covers {{sizeof($group['facet']['subjects'])}} subjects areas {{$subjects_list}}, {{sizeof($group['groups'])}} research groups 
            have been actively involved in collecting data and creating metadata records for the data.  All the Collections, Parties, Activities and Services associated with {{$group['title']}} 
            can be accessed from the Registry Contents box on the right hand side of this page.
        </div>
    </div>

    @if($group['has_custom_data'])
        @if(isset($group['custom_data']['overview']))
        <div class="panel panel-primary panel-content swatch-white">
            <div class="panel-heading">Overview</div>
            <div class="panel-body">{{$group['custom_data']['overview']}}</div>
        </div>
        @endif
        @if(isset($group['custom_data']['researchdarea']))
        <div class="panel panel-primary panel-content swatch-white">
            <div class="panel-heading">Research and Key Research Areas</div>
            <div class="panel-body">{{$group['custom_data']['researchdarea']}}</div>
        </div>
        @endif
        @if(isset($group['custom_data']['researchdataprofile']))
        <div class="panel panel-primary panel-content swatch-white">
            <div class="panel-heading">Research Data Profile</div>
            <div class="panel-body">{{$group['custom_data']['researchdataprofile']}}</div>
        </div>
        @endif
        @if(isset($group['custom_data']['researchsupport']))
        <div class="panel panel-primary panel-content swatch-white">
            <div class="panel-heading">Research Support</div>
            <div class="panel-body">{{$group['custom_data']['researchsupport']}}</div>
        </div>
        @endif
    @endif

    

    <div class="panel panel-primary panel-content swatch-white">
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
                        <li><a href="{{base_url()}}search#!/group={{$group['title']}}/subject_value_resolved={{$subject['name']}}">{{$subject['name']}} <span>({{$subject['num']}})</span></a></li>
                    @endforeach
                </ul>
                @if($orig > sizeof($group['facet']['subjects']))
                    Displaying: {{sizeof($group['facet']['subjects'])}} out of {{$orig}} available subjects. <a href="{{current_url()}}?m=allsubjects">View All</a>
                @endif
            </div>
        </div>
    </div>
@stop

@section('sidebar')

@if($group['has_custom_data'])
    @if(isset($group['custom_data']['contact']))
    <div class="panel panel-primary panel-content swatch-white">
        <div class="panel-heading">Contact</div>
        <div class="panel-body">{{$group['custom_data']['contact']}}</div>
    </div>
    @endif
@endif

<div class="panel panel-primary panel-content swatch-white">
    <div class="panel-heading">Registry Contents</div>
    <div class="panel-body">
        <ul class="listy">
            @foreach($group['facet']['class'] as $class)
                <li><a href="{{base_url()}}search#!/group={{$group['title']}}/class={{$class['name']}}">{{readable($class['name'])}} <small>({{$class['num']}})</small></a></li>
            @endforeach
        </ul>
    </div>
</div>

@if($group['groups'])
<div class="panel panel-primary panel-content swatch-white">
    <div class="panel-heading">Organisations & Groups</div>
    <div class="panel-body">
        <ul class="listy">
            @foreach($group['groups'] as $gr)
                <li><a href="{{base_url()}}{{$gr['slug']}}/{{$gr['id']}}">{{$gr['title']}}</a></li>
            @endforeach
        </ul>
    </div>
</div>
@endif

@if($group['latest_collections'])
<div class="panel panel-primary panel-content swatch-white">
    <div class="panel-heading">Last 5 Collections Added</div>
    <div class="panel-body">
        <ul class="listy">
            @foreach($group['latest_collections'] as $gr)
                <li><a href="{{base_url()}}{{$gr['slug']}}/{{$gr['id']}}">{{$gr['title']}}</a></li>
            @endforeach
        </ul>
    </div>
</div>
@endif

@if($group['has_custom_data'])
    @if(isset($group['custom_data']['identifiers']))
    <div class="panel panel-primary panel-content swatch-white">
        <div class="panel-heading">Identifiers</div>
        <div class="panel-body">{{$group['custom_data']['identifiers']}}</div>
    </div>
    @endif
@endif

@stop