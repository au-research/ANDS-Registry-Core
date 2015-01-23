@extends('layouts/right-sidebar')
@section('header')
<section class="section swatch-white section-text-shadow section-innder-shadow element-short-top element-short-bottom">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <header>
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

    <div class="panel panel-primary panel-content swatch-white">
        <div class="panel-heading">Subjects Covered</div>
        <div class="panel-body widget_tag_cloud">
            <div class="tag-cloud">
                <ul>
                    @foreach($group['facet']['subjects'] as $subject)
                        <li><a href="">{{$subject['name']}} <span>({{$subject['num']}})</span></a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@stop

@section('sidebar')
<div class="panel panel-primary panel-content swatch-white">
    <div class="panel-heading">Registry Contents</div>
    <div class="panel-body">
        <ul class="listy">
            @foreach($group['facet']['class'] as $class)
                <li><a href="">{{readable($class['name'])}} <small>({{$class['num']}})</small></a></li>
            @endforeach
        </ul>
    </div>
</div>

<div class="panel panel-primary panel-content swatch-white">
    <div class="panel-heading">Organisations & Groups</div>
    <div class="panel-body">
        <ul class="listy">
            @foreach($group['groups'] as $gr)
                <li><a href="">{{$gr['title']}}</a></li>
            @endforeach
        </ul>
    </div>
</div>

<div class="panel panel-primary panel-content swatch-white">
    <div class="panel-heading">Last 5 Collections Added</div>
    <div class="panel-body">
        <ul class="listy">
            @foreach($group['latest_collections'] as $gr)
                <li><a href="">{{$gr['title']}}</a></li>
            @endforeach
        </ul>
    </div>
</div>

@stop