<!DOCTYPE html>
<html lang="en">
    @include('includes/header')
    <body>
        @include('includes/top-menu')
        <div id="content">
        	<article>
        		@include('includes/search-section')
    		    <section class="section swatch-gray" style="z-index:1">
    		    	<div class="container">
    		    		<div class="row element-short-top">

                            <div class="col-md-9">
                                <div class="panel panel-body swatch-white">
                                    @if($ro->logo)
                                    <img src="{{$ro->logo[0]}}" alt="logo" class="header-logo animated fadeInDown">
                                    @endif
                                    <h1 class="hairline bordered-normal">{{$ro->core['title']}}</h1>
                                    <small>{{$ro->core['group']}}</small>
                                    @include('registry_object/contents/related-parties')
                                </div>

                                <div>
                                    <div class="pull-left swatch-gray" style="position:relative;z-index:9999;margin:15px;width:350px;">
                                        @include('registry_object/contents/standard-getdatalicence')
                                    </div>
                                    @yield('content')
                                </div>

                            </div>

                            <div class="col-md-3">
                                @include('registry_object/contents/standard-metafunc')
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