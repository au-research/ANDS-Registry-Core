<!DOCTYPE html>
<html lang="en" ng-app="app">
    @include('includes/header')
    <body>
        @include('includes/top-menu')
        <div id="content" ng-controller="searchCtrl">
        	<article>
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
                                        <h1 class="hairline bordered-normal">{{$ro->core['title']}}
                                            @if($ro->existenceDates)
                                                [@include('registry_object/contents/existenceDates-list')]
                                            @endif
                                        </h1>
                                        @if(isset($ro->core['alt_title']))
                                        <small>Also known as:
                                            <span>{{implode(', ',$ro->core['alt_title'])}}</span>
                                        </small><br/>
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
                                                    <small itemprop="sourceOrganization">{{$ro->core['group']}}</small>
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
        </div>
        @include('includes/advanced_search')
        @include('includes/my-rda')
        @include('includes/footer')
    </body>
</html>