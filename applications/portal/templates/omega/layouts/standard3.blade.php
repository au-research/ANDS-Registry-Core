<!DOCTYPE html>
<html lang="en">
    @include('includes/header')
    <body>
        @include('includes/top-menu')
        <div id="content">
        	<article>
        		<section class="section swatch-black section-text-shadow section-inner-shadow" style="overflow:visible">
        		   <div class="background-media skrollable skrollable-between" style="background-image: url(http://devl.ands.org.au/minh/assets/templates/omega/images/uploads/home-classic-1.jpg); background-attachment: fixed; background-size: cover; background-position: 50% 60%; background-repeat: no-repeat;" data-start="background-position:" data-70-top-bottom="background-position:">
        		   </div>
    		       <div class="background-overlay grid-overlay-30 " style="background-color: rgba(0,0,0,0.3);"></div>
    		        <div class="container">
    		            <div class="row">
    		                <div class="col-md-12 element-medium-top element-short-bottom os-animation animated fadeIn">
    		                    @include('includes/search-bar')
    		                </div>
    		            </div>
    		        </div>
    		    </section>
                <section class="section swatch-white section-text-shadow section-innder-shadow element-short-top element-short-bottom">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-8">
                                <header style="padding-bottom:10px">
                                    <h1 class="hairline bordered-normal">{{$ro->core['title']}}</h1>
                                    <small>{{$ro->core['group']}}</small>
                                </header>
                                <div class="btn-group animated fadeInRight" role="group" aria-label="...">
                                    <a class="btn btn-sm btn-default"><i class="fa fa-edit"></i> Cite</a>
                                    <a class="btn btn-sm btn-default"><i class="fa fa-cloud-download"></i> Export</a>
                                    <a class="btn btn-sm btn-default"><i class="fa fa-bookmark-o"></i> Bookmark</a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                
                            </div>
                        </div>
                    </div>
                </section>
    		    <section class="section swatch-gray" style="z-index:1">
    		    	<div class="container">
    		    		<div class="row element-short-top">
    		    			<div class="col-md-9">
                                @include('registry_object/contents/standard3-getdatalicence')
    		    				@yield('content')
    		    			</div>
    		    			<div class="col-md-3 sidebar animated slideInRight">
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