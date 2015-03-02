<!DOCTYPE html>
<html lang="en" ng-app="app">
    @include('includes/header')
    <body ng-controller="searchCtrl">
        @include('includes/top-menu')
        <div id="content" >
            @include('includes/hidden-metadata')
        	
            @include('includes/search-section')
        	<article ng-controller="viewController">	
    		    <section class="section swatch-gray" style="z-index:1">
    		    	<div class="container">
    		    		<div class="row element-short-top">
                            <div class="col-md-9 view-content" style="padding-right:0"  itemscope itemtype="http://schema.org/Dataset">
                                <div class="panel panel-primary swatch-white panel-content">
                                    <div class="panel-body">
                                        @if($ro->logo)
                                        <img src="{{$ro->logo[0]}}" alt="logo" class="header-logo animated fadeInDown">
                                        @endif
                                        <h1 class="hairline bordered-normal"><span itemprop="name">{{$ro->core['title']}}</span></h1>
                                        @if(isset($ro->core['alt_title']))
                                            <small>Also known as:
                                                <span>{{implode(', ',$ro->core['alt_title'])}}</span>
                                            </small><br/>
                                        @endif
                                        <small itemprop="sourceOrganization">{{$ro->core['group']}}</small>

                                        @if(is_array($ro->identifiermatch) && sizeof($ro->identifiermatch) > 0)
                                        <a href="" tip="#identifiermatch"><i class="fa fa-caret-down"></i></a>
                                        <div id="identifiermatch" class="hide">
                                            <b>{{sizeof($ro->identifiermatch)}} linked Records:</b>
                                            <ul class="swatch-white">
                                                @foreach($ro->identifiermatch as $mm)
                                                <li><a href="{{base_url($mm['slug'].'/'.$mm['registry_object_id'])}}">{{$mm['title']}} <br/><small>Contributed by {{$mm['group']}}</small></a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        @endif

                                        <div class="clear"></div>
                                        
                                        <div class="container-fluid">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    @include('registry_object/contents/related-parties')
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="panel-body" style="padding:0 0 10px 0">
                                        <div class="panel-tools">
                                            <div ng-if="ro.stat">
                                                <a href="#" style="padding-right:4px;"><small>Viewed: </small>[[ro.stat.viewed]]</a>
                                                <a href="#" style="padding-right:4px;"><small>Accessed: </small>[[ro.stat.accessed]]</a>
                                            </div>
                                        </div>
                                        <div class="panel-tools">
                                            @include('registry_object/contents/social-sharing')
                                        </div>
                                    </div>
                                </div>

                                <div>

                                  <!--  <div class="pull-left swatch-white" style="position:relative;z-index:9999;margin:35px 15px 15px 15px;width:350px;">
                                        @include('registry_object/party_contents/wrap-getdatalicence')
                                    </div> -->
                                    @yield('content')
                                </div>

                            </div>

                            <div class="col-md-3">
                                @include('registry_object/party_contents/wrap-getdatalicence')
                                @yield('sidebar')
                            </div>

    		    		</div>
    		    	</div>
    		    </section>
        	</article>
        </div>
        @include('registry_object/contents/citation-modal')
        @include('includes/advanced_search')
        @include('includes/my-rda')
        @include('includes/footer')
    </body>
</html>