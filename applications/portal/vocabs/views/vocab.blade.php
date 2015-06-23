<?php

$cc=$vocab['licence'];
$current_version =  $vocab['current_version'] ;
$publisher = array();
$related_people = array();
$related_vocabs = array();
$related_service = array();
if(isset($vocab['related_entity'])){
    foreach($vocab['related_entity'] as $related){
        if($related['type']=='party'){
            if(isset($related['relationship'])){
                if (is_array($related['relationship'])) {
                    $relationships = implode($related['relationship'], ',');
                } else {
                    $relationships = $related['relationship'];
                }
            }
            if($relationships=='publishedBy'){
                $publisher[]=$related;
            }else{
                $related_people[] =$related;
            }
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

            <div class="container-fluid">
                <div class="row">
                    @if($vocab['current_version'])
                    <div class="col-md-4 panel-body text-center">
                        <h4>{{ titlecase($vocab['current_version']['title']) }}</h4>
                        
                        @foreach($vocab['current_version']['access_points'] as $ap)
                            @if($ap['type']=='file')
                                <a class="btn btn-lg btn-block btn-primary" href="{{ portal_url('vocabs/download/?file='.$ap['uri']) }}"><i class="fa fa-cube"></i> Download File</a>
                            @endif
                        @endforeach
                        @foreach($vocab['current_version']['access_points'] as $ap)
                            @if($ap['type']!='file')
                                <div class="btn-group btn-group-justified element element-no-bottom element-no-top" role="group" aria-label="...">
                                    <a class="btn btn-sm btn-default" href="{{ $ap['uri'] }}"><i class="fa fa-edit"></i> Access {{ $ap['type'] }} ({{ $ap['format'] }})</a>
                                </div>
                            @endif
                        @endforeach

                        <p class="element-short-top">{{ isset($vocab['current_version']['note']) ? $vocab['current_version']['note']: '' }}</p>

                        <ul class="">
                            @foreach($vocab['versions'] as $version)
                            @if($version['status']!='current')
                                <li>
                                    <a href="" class="ver_preview" version='{{json_encode(str_replace("'"," ",$version))}}'>{{ titlecase($version['title']) }} </a>
                                    <small>({{ $version['status'] }}) </small>
                                    @if(isset($version['note']))
                                    <a href="" tip="{{ $version['release_date'] }} <hr />{{$version['note']}}"><i class="fa fa-info"></i></a>
                                    @endif
                                </li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <div class="col-md-8 panel-body">
                        {{ $vocab['description'] }}
                        @if(isset($vocab['language']))
                        <h4>Languages</h4>
                        <p>
                            <?php
                            $pipe_count = 0;
                            foreach($vocab['language'] as $language)
                            {
                                echo readable($language);
                                $pipe_count++;
                                if($pipe_count<count($vocab['language'])){
                                    echo " | ";
                                }
                            }
                            ?>

                        </p>
                        @endif
                        @if(isset($vocab['note']))
                            <h4>Notes</h4>
                            <p>{{ $vocab['note'] }}</p>
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
            </div>
        </div>

        <div visualise vocabid="{{ $vocab['id'] }}"></div>

        @if(isset($vocab['subjects']))
        <div class="panel swatch-white">
            <div class="panel-heading">Subjects</div>
            <div class="panel-body">
                <?php $sub_count=0; ?>
                @foreach($vocab['subjects'] as $subject)
                <?php $sub_count++; ?>
                   <a  href="{{base_url()}}search/#!/subject={{$subject['subject']}}"> {{$subject['subject']}} </a> <?php if($sub_count<count($vocab['subjects'])) echo " | "; ?>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</article>
@stop


@section('sidebar')
@if(isset($related_service[0]['title']))

<div class="panel swatch-white  panel-primary element-no-top element-short-bottom panel-content">
    <div class="panel-heading">Services that make use of this vocabulary</div>
    <div class="panel-body">

            @foreach($related_service as $service)
            <p><small>
                <?php 
                    if (isset($service['relationship'])) {
                        if (is_array($service['relationship'])) {
                            echo readable(implode($service['relationship'], ','));
                        } else {
                            echo readable($service['relationship']);
                        }
                    }
                ?>
            </small> <a href="" class="re_preview"  related='{{json_encode($service)}}' v_id="{{ $vocab['id'] }}">{{$service['title']}}</a></p>
            @endforeach

    </div>
</div>
@endif
@if($related_people||$related_vocabs)
<div class="panel swatch-white  panel-primary element-no-top element-short-bottom panel-content">
    <div class="panel-heading">Related</div>
    <div class="panel-body">

        @if($related_people)
        <h4>Related people and organisations</h4>
        @foreach($related_people as $related)

        <p>

            <small>
                <?php 
                    if (isset($related['relationship'])) {
                        if (is_array($related['relationship'])) {
                            echo readable(implode($related['relationship'], ','));
                        } else {
                            echo readable($related['relationship']);
                        }
                    }
                ?>
            </small> <a href="" class="re_preview"  related='{{json_encode($related)}}' v_id="{{ $vocab['id'] }}"> {{$related['title']}}</a>
        </p>
        @endforeach
        @endif
        @if($related_vocabs)
        <h4>Related vocabularies</h4>
        @foreach($related_vocabs as $related)
        <p>
            <small>
                <?php 
                    if (isset($related['relationship'])) {
                        if (is_array($related['relationship'])) {
                            echo implode($related['relationship'], ',');
                        } else {
                            echo readable($related['relationship']);
                        }
                    }
                ?>
            </small> <a href="" class="re_preview"  related='{{json_encode($related)}}' v_id="{{ $vocab['id'] }}"> {{$related['title']}}</a>
        </p>
        @endforeach
        @endif
    </div>
</div>
@endif

@stop