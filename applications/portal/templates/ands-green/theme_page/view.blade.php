<!DOCTYPE html>
<html lang="en" ng-app="portal_theme" ng-controller="init">
    @include('includes/header')
    <body>
        @include('includes/top-menu')
        <input type="hidden" value="{{$theme['slug']}}" id="slug">
        <div id="content">
        	<article>
                <section class="section swatch-white section-text-shadow section-innder-shadow element-short-top element-short-bottom">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12">
                                <header>
                                    <h1 class="hairline bordered-normal">{{$theme['title']}}</h1>
                                    <a href="{{portal_url('themes')}}"><i class="fa fa-arrow-left"></i> Return to Themed Collections</a>
                                </header>
                            </div>
                        </div>
                    </div>
                </section>
    		    <section class="section swatch-gray" style="z-index:1">
    		    	<div class="container">
    		    		<div class="row element-short-top">
    		    			<div class="col-md-9">
    		    				@include('theme_page/content', array('region'=>'left'))
    		    			</div>
    		    			<div class="col-md-3">
    		    				@include('theme_page/content', array('region'=>'right'))
    		    			</div>
    		    		</div>
    		    	</div>
    		    </section>
        	</article>
        </div>
        @include('includes/footer')
    </body>
</html>