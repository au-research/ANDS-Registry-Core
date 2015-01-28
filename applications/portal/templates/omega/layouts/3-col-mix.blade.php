<!DOCTYPE html>
<html lang="en">
    @include('includes/header')
    <body>
        @include('includes/top-menu')
        <div id="content">
        	<article>
        		@include('includes/search-section')
    		    <section class="section swatch-gray" style="z-index:1">
    		    	<div class="container-fluid">
    		    		<div class="row element-short-top">
                            <div class="col-md-9" style="padding-right:0">
                                <div class="container-fluid" style="padding:0">
                                    <div class="col-md-12">
                                        <div class="panel panel-body swatch-white">
                                            @if($ro->logo)
                                            <img src="{{$ro->logo[0]}}" alt="logo" class="header-logo animated fadeInDown">
                                            @endif
                                            <h1 class="hairline bordered-normal">{{$ro->core['title']}}</h1>
                                            <small>{{$ro->core['group']}}</small>
                                            @include('registry_object/contents/related-parties')
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        @include('registry_object/contents/standard-getdatalicence')
                                        @include('registry_object/contents/standard-metafunc')
                                    </div>
                                    <div class="col-md-8">
                                        @yield('content')
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
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