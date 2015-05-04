<!DOCTYPE html>
<html lang="en" ng-app="app">
    @include('includes/header')
    <body>
        @include('includes/top-menu')
        <div id="content" ng-controller="searchCtrl">
        	<article ng-controller="viewController">

                @include('includes/hidden-metadata')
        		@include('includes/search-section')
                <section class="section swatch-gray" style="z-index:1">
                    <div class="container">
                        <div class="row element-short-top">
                            <div class="col-md-9" style="padding-right:0">
                                <div class="panel panel-primary element-no-top element-short-bottom panel-content">
                                    <div class="panel-tools">
                                        @include('registry_object/contents/icon')
                                    </div>
                                    <div class="panel-body {{$ro->core['type']}}">
                                        <h1 class="hairline bordered-normal" style="line-height:1.1em">{{$ro->core['title']}}
                                            @if($ro->existenceDates)
                                                [@include('registry_object/contents/existenceDates-list')]
                                            @endif
                                        </h1>
                                        @if(isset($ro->core['alt_title']))
                                            @foreach($ro->core['alt_title'] as $aTitle)
                                                @if($aTitle!=$ro->core['title'])
                                                 <small>Also known as:</small> {{$aTitle}}<br />
                                                @endif
                                            @endforeach
                                        @endif

                                        @if(is_array($ro->identifiermatch) && sizeof($ro->identifiermatch) > 0)
                                        @if($show_dup_identifier_qtip)
                                        <a href="" qtip="#identifiermatch" tip_popup="{{sizeof($ro->identifiermatch)}} linked Records"><i class="fa fa-caret-down"></i></a>
                                        @else
                                        <a href="" qtip="#identifiermatch"><i class="fa fa-caret-down"></i></a>
                                        @endif
                                        <div id="identifiermatch" class="hide">
                                            <b>{{sizeof($ro->identifiermatch)}} linked Records:</b>
                                            <ul class="swatch-white">
                                                @foreach($ro->identifiermatch as $mm)
                                                <li><a href="{{base_url($mm['slug'].'/'.$mm['registry_object_id'])}}{{$fl}}">{{$mm['title']}} <br/><small>Contributed by {{$mm['group']}}</small></a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        @endif

                                        <div class="clear"></div>

                                        <div class="container-fluid">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    @include('registry_object/activity_contents/activity-parties')
                                                    @if($ro->core['group']=='National Health and Medical Research Council')
                                                    <br /><strong>Provided by </strong>&nbsp;  <a href="{{ portal_url('view') }}?key=http://dx.doi.org/10.13039/501100000925"s  tip="Record provided by {{$ro->core['group']}}"><span itemprop="sourceOrganization">{{$ro->core['group']}}</span></a>
                                                    @elseif($ro->core['group']=='Australian Research Council')
                                                    <br /><strong>Provided by </strong>&nbsp; <a href="{{ portal_url('view') }}?key=http://dx.doi.org/10.13039/501100000923"  tip="Record provided by {{$ro->core['group']}}"><span itemprop="sourceOrganization">{{$ro->core['group']}}</span></a>
                                                    @else
                                                    <br /><strong>Provided by </strong>&nbsp;  <a href="{{base_url('contributors')}}/{{$group_slug}}" tip="Record provided by {{$ro->core['group']}}" title="Record provided by {{$ro->core['group']}}"><span itemprop="sourceOrganization">{{$ro->core['group']}}</span></a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>

                                   @yield('content')
                                </div>

                            </div>

                            <div class="col-md-3">
                                @include('registry_object/activity_contents/activity-metafunc')
                                @yield('sidebar')
                            </div>

                        </div>
                    </div>
                </section>
        	</article>
            @include('includes/advanced_search')
            @include('includes/my-rda')
        </div>
        
        @include('includes/footer')
    </body>
</html>