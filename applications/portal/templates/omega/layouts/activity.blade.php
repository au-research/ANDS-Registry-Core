<!DOCTYPE html>
<html lang="en" ng-app="app">
    @include('includes/header')
    <body ng-controller="searchCtrl">
        @include('includes/top-menu')
        <div id="content">
        	<article>
        		@include('includes/search-section')
                <section class="section swatch-gray" style="z-index:1">
                    <div class="container">
                        <div class="row element-short-top">
                            <div class="col-md-9" style="padding-right:0">
                                <div class="panel panel-primary element-no-top element-short-bottom panel-content">
                                    <div class="panel-body {{$ro->core['type']}}">
                                        @if($ro->logo)
                                        <img src="{{$ro->logo[0]}}" alt="logo" class="header-logo animated fadeInDown">
                                        @endif
                                        <h1 class="hairline bordered-normal">{{$ro->core['title']}}
                                            @if($ro->existenceDates)
                                                [@include('registry_object/contents/existenceDates-list')]
                                            @endif
                                        </h1>
                                        @include('registry_object/activity_contents/activity-parties')
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
        @include('includes/footer')
    </body>
</html>