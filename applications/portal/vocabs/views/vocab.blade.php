<?php

$cc=$vocab['licence'];
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
        elseif($related['type']=='party'){
            $related_people[] =$related;
        }
        elseif($related['type']=='service'){
            $related_service[]=$related;
        }
        elseif($related['type']=='vocabulary'){
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
                @if(isset($vocab['language']))
                <h4>Languages</h4>
                <p>
                    @foreach($vocab['language'] as $language)
                    {{readable($language)}} |
                    @endforeach
                </p>
                @endif
                @if(isset($vocab['note']))
                <h4>Notes</h4>
                <p>{{$vocab['note']}}</p>
                @endif
                <h4>Licence</h4>
                <p>
                    @if($cc=='CC-BY')
                    <a href="http://creativecommons.org/licenses/by/3.0/au/" tip="Attribution"><img src="{{asset_url('images/icons/CC-BY.png', 'core')}}" class="img-cc" alt="CC-BY"></a> <br/>
                    @elseif($cc=='CC-BY-SA')
                    <a href="http://creativecommons.org/licenses/by-sa/3.0/au/" tip="Attribution-Shared Alike"><img src="{{asset_url('images/icons/CC-BY-SA.png', 'core')}}" class="img-cc" alt="CC-BY-SA"></a> <br/>
                    @elseif($cc=='CC-BY-ND')
                    <a href="http://creativecommons.org/licenses/by-nd/3.0/au/" tip="Attribution-No Derivatives"><img src="{{asset_url('images/icons/CC-BY-ND.png', 'core')}}" class="img-cc" alt="CC-BY-ND"></a> <br/>
                    @elseif($cc=='CC-BY-NC')
                    <a href="http://creativecommons.org/licenses/by-nc/3.0/au/" tip="Attribution-Non Commercial"><img src="{{asset_url('images/icons/CC-BY-NC.png', 'core')}}" class="img-cc" alt="CC-BY-NC"></a> <br/>
                    @elseif($cc=='CC-BY-NC-SA')
                    <a href="http://creativecommons.org/licenses/by-nc-sa/3.0/au/" tip="Attribution-Non Commercial-Shared Alike"><img src="{{asset_url('images/icons/CC-BY-NC-SA.png', 'core')}}" class="img-cc" alt="CC-BY-NC-SA"></a> <br/>
                    @elseif($cc=='CC-BY-NC-ND')
                    <a href="http://creativecommons.org/licenses/by-nc-nd/3.0/au/" tip="Attribution-Non Commercial-Non Derivatives"><img src="{{asset_url('images/icons/CC-BY-NC-ND.png', 'core')}}" class="img-cc" alt="CC-BY-NC-ND"></a> <br/>
                    @else
                    <span>Licence: {{sentenceCase($cc)}}</span>
                    @endif
                </p>

            </div>
        </div>

        <div visualise vocabid="{{ $vocab['id'] }}"></div>

        @if(isset($vocab['subjects']))
        <div class="panel swatch-white">
            <div class="panel-heading">Subjects</div>
            <div class="panel-body">
                @foreach($vocab['subjects'] as $subject)
                   <a  href="{{base_url()}}search/#!/subject={{$subject['subject']}}"> {{$subject['subject']}} </a> |
                @endforeach
            </div>
        </div>
        @endif

    </div>
</article>
@stop


@section('sidebar')
@if(isset($related_service))
<div class="panel swatch-white  panel-primary element-no-top element-short-bottom panel-content">
    <div class="panel-heading">Services that make use of this vocabulary</div>
    <div class="panel-body">

            @foreach($related_service as $service)
            <p><small>({{readable($service['relationship'])}})</small> <a href="" class="re_preview"  related='{{json_encode($service)}}' v_id="{{ $vocab['id'] }}">{{$service['title']}}</a></p>
            @endforeach

    </div>
</div>
@endif
@if($related_orgs||$related_people||$related_vocabs)
<div class="panel swatch-white  panel-primary element-no-top element-short-bottom panel-content">
    <div class="panel-heading">Related</div>
    <div class="panel-body">
        @if($related_orgs)
        <h4>Related organisations</h4>
        @foreach($related_orgs as $related)
        <p>
            <small>({{readable($related['relationship'])}})</small> <a href="" class="re_preview" related='{{json_encode($related)}}' v_id="{{ $vocab['id'] }}"> {{$related['title']}}</a>
        </p>
        @endforeach
        @endif
        @if($related_people)
        <h4>Related people</h4>
        @foreach($related_people as $related)
        <p>
            <small>({{readable($related['relationship'])}})</small> <a href="" class="re_preview"  related='{{json_encode($related)}}' v_id="{{ $vocab['id'] }}"> {{$related['title']}}</a>
        </p>
        @endforeach
        @endif
        @if($related_vocabs)
        <h4>Related vocabularies</h4>
        @foreach($related_vocabs as $related)
        <p>
            <small>({{readable($related['relationship'])}})</small> <a href="" class="re_preview"  related='{{json_encode($related)}}' v_id="{{ $vocab['id'] }}"> {{$related['title']}}</a>
        </p>
        @endforeach
        @endif
    </div>
</div>
@endif

@stop