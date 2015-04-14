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
                            <div class="col-md-12">
                                <header class="pull-left">
                                    <h1 class="hairline bordered-normal">{{$ro->core['title']}} App</h1>
                                    <small>{{$ro->core['group']}}</small>
                                </header>
                                <div class="btn-group btn-group-vertical pull-right animated fadeInRight" role="group" aria-label="...">
                                    <a class="btn btn-default"><i class="fa fa-edit"></i> Cite</a>
                                    <a class="btn btn-default"><i class="fa fa-cloud-download"></i> Export</a>
                                    <a class="btn btn-default"><i class="fa fa-bookmark-o"></i> Bookmark</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
    		    <section class="section swatch-gray" style="z-index:1">
    		    	<div class="container-fluid">
    		    		<div class="row element-short-top">
    		    			<div class="col-md-9">

                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-md-4 animated fadeInLeft">
                                            <div class="panel panel-primary swatch-white">
                                                <div class="panel-body">
                                                    <a href="" class="btn btn-lg btn-primary btn-block">Get Data</a>
                                                    @foreach ($aside as $side)
                                                        @include('registry_object/contents/'.$side)
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8 animated fadeInDown">
                                            <div class="panel panel-primary swatch-white">
                                                <div class="panel-body">
                                                    @include('registry_object/contents/simple-description')
                                                    <a href="">Read more...</a>
                                                    <p>
                                                        Placeholder for record quick view, useful information about this record will be shown here. 
                                                        Like people, orgs, dates
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="animated fadeInDown">
                                    @yield('content')
                                </div>
    		    				
    		    			</div>
    		    			<div class="col-md-3 sidebar animated fadeInRight">
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