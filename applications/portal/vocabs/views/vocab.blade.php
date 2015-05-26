<?php
$current_version =  $vocab['current_version'] ;
$publisher = array();
$related_orgs = array();
$related_people = array();
$related_vocabs = array();
$related_service = array();
if(isset($vocab['related_entity'])){
    foreach($vocab['related_entity'] as $related){
        if($related['type']=='publisher'){
            $publisher=$related;
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
        <div class="panel swatch-white panel-primary element-no-top element-short-bottom panel-content">
            <div class="panel-body">
                {{ $vocab['description'] }}
            </div>
         </div>


        <!-- set up header and content for vocab tree is if exists -->
        <div class="panel swatch-white">
            <div class="panel-heading">Browse</div>
            <div class="panel-body element-no-top">
                <div id="vocab-tree" vocab="{{$vocab['slug']}}"></div>
            </div>
        </div>

        <!-- Top Concepts -->
        @if($vocab['top_concept'])
        <div class="panel swatch-white">
            <div class="panel-heading">Top Level Concepts</div>
            <div class="panel-body">
                @foreach($vocab['top_concept'] as $concept)
                    {{$concept}} |
                @endforeach
                </p>
                <h4>Total number of concepts</h4>
                <p>Not sure what element is actually required to display here</p>
            </div>
        </div>
        @endif

        @if($related_orgs||$related_people||$related_vocabs)
        <div class="panel swatch-white">
            <div class="panel-heading">Related</div>
            <div class="panel-body">
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
        @endif

        @if($vocab['subjects'])
        <div class="panel swatch-white">
            <div class="panel-heading">Subjects</div>
            <div class="panel-body">
                @foreach($vocab['subjects'] as $subject)
                    {{$subject['subject']}}|
                @endforeach
            </div>
        </div>
        @endif


        @if( $current_version['release_date']|| $vocab['note'] || $vocab['language'] || $current_version['note'])
        <div class="panel swatch-white">
            <div class="panel-body">
                @if($current_version['release_date'])
                    <h3>Release date</h3>
                    <p>{{$current_version['release_date']}}</p>
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
        @endif

    </div>
</article>
@stop


@section('sidebar')
@if($related_service)
<div class="panel swatch-white">
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


@stop