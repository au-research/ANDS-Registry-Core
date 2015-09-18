<?php
$cc=$vocab['licence'];
$current_version =  $vocab['current_version'] ;
$publisher = array();
$related_people = array();
$related_vocabs = array();
$related_service = array();
$sesameFormats = array();

$sesameFormats["rdf"] = "RDF/XML";
$sesameFormats["nt"] = "N-Triples";
$sesameFormats["ttl"] = "Turtle";
$sesameFormats["n3"] = "Notation3";
$sesameFormats["nq"] = "N-Quads";
$sesameFormats["json"] = "RDF/JSON";
$sesameFormats["trix"] = "TriX";
$sesameFormats["trig"] = "TriG";
$sesameFormats["bin"] = "Sesame Binary RDF";

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

@section('og-description')
@if(gettype($vocab) == "array" && isset($vocab['description']))
	<?php
		$clean_description = htmlspecialchars(substr(str_replace(array('"','[[',']]'), '', $vocab['description']), 0, 200));
	?>
@endif
@if(isset($clean_description))
	<meta ng-non-bindable property="og:description" content="{{ $clean_description }}" />
@else
	<meta ng-non-bindable property="og:description" content="Find, access, and re-use vocabularies for research" />
@endif
@stop
@section('og-other-meta')
<meta property="og:url" content="{{ base_url().$vocab['slug'] }}" />
<meta property="og:title" content="{{ htmlspecialchars($vocab['title']) }}" />
@stop
@extends('layout/vocab_2col_layout')
@section('content')
<article class="post">
    <div class="post-body">
        <div class="panel swatch-white panel-primary element-no-top element-short-bottom panel-content">

            <div class="container-fluid" >
                <div class="row">
                    @if($vocab['current_version'])
                    <div class="col-md-4 panel-body text-center">
                    <div style="border-color:#aaaaaa; solid; border-style: solid; border-width: 5px; padding: 10px;">
                        <span class="current"">current version</span>
                        <h4 style="margin-top:5px;" ng-non-bindable>{{ htmlspecialchars($vocab['current_version']['title']) }}</h4>
                        @if(isset($vocab['current_version']['version_access_points']) && is_array($vocab['current_version']['version_access_points']))
                        @foreach($vocab['current_version']['version_access_points'] as $ap)
                            @if($ap['type']=='file')
                                <a class="btn btn-lg btn-block btn-primary" style="white-space: normal;" href="{{ json_decode($ap['portal_data'])->uri }}" title="{{ json_decode($ap['portal_data'])->format }}"><i class="fa fa-cube"></i> Download File <span class="small">({{ json_decode($ap['portal_data'])->format }})</span></a>
                            @endif
                        @endforeach
                        @foreach($vocab['current_version']['version_access_points'] as $ap)
                            @if($ap['type'] == 'sesameDownload')
                        <div class="btn-group btn-group-justified element element-no-bottom element-no-top" role="group" aria-label="...">
                            <a title="Select Download Formats" href="javascript:;" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-edit"></i>
                                Access {{ $ap['type'] }}
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                @foreach($sesameFormats as $key=>$val)
                                <li><a href="{{ json_decode($ap['portal_data'])->uri }}{{$key}}">{{ $val }}</a></li>
                                @endforeach
                            </ul>
                        </div>

                            @elseif($ap['type']!='file')
                                <div class="btn-group btn-group-justified element element-no-bottom element-no-top" role="group" aria-label="...">
                                    <?php
                                    $url = json_decode($ap['portal_data'])->uri;
                                    if($ap['type'] == 'sissvoc'){
                                        $sissvocEndPoint = $url;
                                        $url = $url.'/concept';
                                    }
                                    ?>
                                    <a class="btn btn-sm btn-default {{$ap['type']}}" href="{{ $url }}" target="_blank"><i class="fa fa-edit"></i>
                                        Access {{ $ap['type'] }}
                                        @if(isset(json_decode($ap['portal_data'])->format))
                                        ({{ json_decode($ap['portal_data'])->format }})
                                        @endif
                                    </a>
                                </div>
                            @endif
                        @endforeach
                        @endif
                        <p class="element-short-top">{{ isset($vocab['current_version']['note']) ? $vocab['current_version']['note']: '' }}</p>
                    </div>
                        @if(isset($vocab['versions']) && is_array($vocab['versions']))
                        <ul class="list-unstyled text-left" style="margin-top: 12px;">
                            @foreach($vocab['versions'] as $version)
                            @if($version['status']!='current')
                                <li>
                                    <a href="" class="ver_preview" version='{{json_encode(str_replace("'"," ",$version))}}' ng-non-bindable>{{ htmlspecialchars($version['title']) }} </a>
                                    @if(isset($version['note']))
                                    <a href="" tip="{{ $version['release_date'] }} <hr />{{str_replace('"',"&quot;",$version['note'])}}"><i class="fa fa-info"></i></a>
                                    @endif
                                    <small class="{{$version['status']}}" style="float: right;"> {{ $version['status'] }} </small>
                                </li>
                            @endif
                            @endforeach
                        </ul>
                        @endif
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
                        @if(isset($cc)&&$cc!='')
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
                            <span>Licence: {{ $cc }}</span>
                            @endif
                        </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if(isset($vocab['top_concept']))
        <div class="panel swatch-white">
            <div class="panel-heading">Top Concepts</div>
            <div class="panel-body">
                <table class="table">
                    <tbody>
                        @foreach($vocab['top_concept'] as $concept)
                            <tr><td>{{$concept}}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if(isset($sissvocEndPoint))
        <div visualise vocabid="{{ $vocab['id'] }}"></div>
        <div id="widget" class="panel swatch-white">
            <div class="panel-body">Use this code snippet to describe or discover resources with {{$vocab['title']}} in your system<span class="btn-widget-link"><img class="widget-icon" height="32" width="32"src="{{asset_url('images/cogwheels.png', 'core')}}"/>widgetable</span>
            <br/><b>Example:</b> Search for and select concepts in this vocabulary</div>
            <div class="panel-body">
            <input type="text" id="{{$vocab['slug']}}" name="{{$vocab['slug']}}" placeholder="Search" size="80" autocomplete="off">
                <script>
                $("#{{$vocab['slug']}}").vocab_widget({
                mode: 'search',
                cache: false,
                repository: '{{$sissvocEndPoint}}',
                target_field: 'label',
                endpoint: '{{ portal_url("apps/vocab_widget/proxy/") }}'
                });
                </script>
            </div>
            &nbsp;&nbsp;<button id="widget-toggle">Show code</button><div class="pull-right"><a target="_blank" href="http://developers.ands.org.au/widgets/vocab_widget/">Learn more</a></div>
            <br/>
            <div id="widget-info" class="toggle">
            <pre class="panel-body prettyprint">
&lt;input type="text" id="{{$vocab['slug']}}" name="{{$vocab['slug']}}" value="" size="80" autocomplete="off"&gt;
&lt;script&gt;
    $("#{{$vocab['slug']}}").vocab_widget({
        mode: 'search',
        cache: false,
        repository: '{{$sissvocEndPoint}}',
        target_field: 'label',
        endpoint: '{{ portal_url("apps/vocab_widget/proxy/") }}'
    });
&lt;/script&gt;
            </pre>
            </div>
        </div>



        @endif

        @if(isset($vocab['subjects']))
        <div class="panel swatch-white">
            <div class="panel-heading">Subjects</div>
            <div class="panel-body">
                <?php $sub_count=0; ?>
                @foreach($vocab['subjects'] as $subject)
                <?php $sub_count++; ?>
                   <a  href="{{base_url()}}#!/?subjects={{$subject['subject']}}"> {{$subject['subject']}} </a> <?php if($sub_count<count($vocab['subjects'])) echo " | "; ?>
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
