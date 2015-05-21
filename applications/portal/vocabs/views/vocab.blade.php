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
        <div class="swatch-gray">
            <div class="panel panel-primary element-no-top element-short-bottom panel-content">
                <div class="panel-body swatch-gray">

                    <h3>Related organisations</h3>
                    <p>xxx|xxx|xxxxx|xxxxx</p>

                    <h3>Related people</h3>
                    <p>xxx</p>

                    <h3>Related vocabularies</h3>
                    <p>xxx</p>
                </div>
            </div>
        </div>
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
@if($vocab['creation_date'] || $vocab['note'])
        <div class="swatch-gray">
            <div class="panel panel-primary element-no-top element-short-bottom panel-content">
                <div class="panel-body swatch-gray">
                     @if($vocab['creation_date'])
                        <h3>Release date</h3>
                        <p>{{$vocab['creation_date']}}</p>
                    @endif
                    <h3>Version note</h3>
                    <p>xxx</p>

                    <h3>Languages</h3>
                    <p>xxx</p>
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

<div class="panel panel-primary panel-content swatch-white">
    <div class="panel-heading">Services that make use of this vocabulary</div>
    <div class="panel-body">
        <ul>
            <li>Supports <a href="">Service 1</a></li>
        </ul>
    </div>
</div>

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