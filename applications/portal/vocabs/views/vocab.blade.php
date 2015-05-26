<?php
$current_version = '';
if(isset($vocab['versions'])){
    foreach($vocab['versions'] as $version){
        if($version['status']=='current'){
            $current_version = $version;
        }
    }
}

$related_orgs = array();
$related_people = array();
$related_vocabs = array();
$related_service = array();
if(isset($vocab['related_entity'])){
    foreach($vocab['related_entity'] as $related){
        if($related['type']=='publisher'){
            $related_orgs[]=$related;
        }
        elseif($related['type']=='contributor'){
            $related_people[] =$related;
        }
        elseif($related['type']=='service'){
            $related_service[]=$related;
        }
        elseif($related['type']=='vocab'){
            $related_vocabs[]=$related;
        }
    }
}

?>

@extends('layout/vocab_2col_layout')
@section('content')

<article class="post">
    <div class="post-body">
        <div class="swatch-gray">
            <div class="panel panel-primary element-no-top element-short-bottom panel-content">
                <div class="panel-body swatch-gray">
                    {{ $vocab['description'] }}
                </div>
             </div>

        </div>

        <!-- set up header and content for vocab tree is if exists -->
        <div class="swatch-gray">
            <div class="panel panel-primary element-no-top element-no-bottom panel-content">
                <div class="panel-body swatch-grey element-no-top">
                    <h3>Browse</h3>
                </div>
            </div>
        </div>

        <div class="swatch-gray">
            <div class="panel panel-primary element-no-top element-short-bottom panel-content">
                <div class="panel-body swatch-gray">
                    <div id="vocab-tree" vocab="{{$vocab['slug']}}"></div>
                </div>
            </div>
        </div>

@if($vocab['top_concept'])
        <div class="swatch-gray">
            <div class="panel panel-primary element-no-top element-short-bottom panel-content">
                <div class="panel-body swatch-grey">

                    <h3>Top level Concepts</h3>
                    <p>
                    @foreach($vocab['top_concept'] as $concept)
                        {{$concept}} |
                    @endforeach
                    </p>
                    <h3>Total number of concepts</h3>
                    <p>Not sure what element is actually required to display here</p>
                </div>
            </div>
        </div>
@endif
        @if($related_orgs||$related_people||$related_vocabs)
        <div class="swatch-gray">
            <div class="panel panel-primary element-no-top element-short-bottom panel-content">
                <div class="panel-body swatch-gray">
                    @if($related_orgs)
                        <h3>Related organisations</h3>
                        @foreach($related_orgs as $related)
                            <p>
                                {{$related['title']}}
                            </p>
                        @endforeach
                    @endif
                    @if($related_people)
                        <h3>Related people</h3>
                        @foreach($related_people as $related)
                            <p>
                                {{$related['title']}}
                            </p>
                        @endforeach
                    @endif
                    @if($related_vocabs)
                        <h3>Related vocabularies</h3>
                        @foreach($related_vocabs as $related)
                            <p>
                                {{$related['title']}}
                            </p>
                        @endforeach
                    @endif

                </div>
            </div>
        </div>
        @endif
@if($vocab['subjects'])
        <div class="swatch-gray">
            <div class="panel panel-primary element-no-top element-short-bottom panel-content">
                <div class="panel-body swatch-grey">

                    <h3>Subjects</h3>
                    <p>
                    @foreach($vocab['subjects'] as $subject)
                        {{$subject['subject']}}|
                    @endforeach
                    </p>

                </div>
            </div>
        </div>
@endif
@if($vocab['creation_date'] || $vocab['note'] || $vocab['language'] || $current_version['note'])
        <div class="swatch-gray">
            <div class="panel panel-primary element-no-top element-short-bottom panel-content">
                <div class="panel-body swatch-gray">
                     @if($vocab['creation_date'])
                        <h3>Release date</h3>
                        <p>{{$vocab['creation_date']}}</p>
                    @endif
                    @if($current_version['note'])
                        <h3>Version note</h3>
                        <p>{{$current_version['note']}}</p>
                    @endif
                    @if($vocab['note'])
                    <h3>Languages</h3>
                    <p>
                        @foreach($vocab['language'] as $language)
                        {{$language}} |
                        @endforeach
                    </p>
                    @endif

                    @if($vocab['note'])
                        <h3>Notes</h3>
                        <p>{{$vocab['note']}}</p>
                    @endif
                </div>
            </div>
        </div>
@endif
        </div>
    </article>
@stop


@section('sidebar')
@if($related_service)
<div class="panel panel-primary panel-content swatch-white">
    <div class="panel-heading">Services that make use of this vocabulary</div>
    <div class="panel-body">
        <ul>
            @foreach($related_service as $service)
            <li>{{$service['relationship']}} <a href="{{$service['URL']}}">{{$service['title']}}</a></li>
            @endforeach
        </ul>
    </div>
</div>
@endif
@if($vocab['versions'])
<div class="panel panel-primary panel-content swatch-white">
    <div class="panel-heading">Versions</div>
    <div class="panel-body">
        <ul>
            @foreach($vocab['versions'] as $version)

            <li><a href="">{{$version['title']}}</a><br/>{{$version['status']}}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

@stop