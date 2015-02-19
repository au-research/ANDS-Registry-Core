<?php
/*
 * XXX
 */
?>
<?php $this->load->view('header');?>
<div class="container" id="main-content">

<section>

<div class="row">
	<div class="span12">
		<div class="box">
			<div class="box-header clearfix">
				<h1><?=$title;?></h1>
			</div>

			<div class="row-fluid">

				<div class="span8">
				    <div class="alert alert-info">
					  <strong>Developer Zone!</strong> Some basic web development knowledge may be needed to implement this widget.
					</div>

			    	 <h4>What is this widget?</h4>
			    	 <p>
			    		 The ANDS Location Capture Widget allows you to instantly enrich your data
				    	 capture system, adding geospatial capabilities such as custom drawings and
				    	 place name resolution (using the Australian Gazetteer Service and Google Maps API).
			    	 </p>
			    	 <p>
			    		 <a target="_blank" class="btn btn-success" href="<?=base_url('location_capture_widget/demo/');?>"><i class="icon-circle-arrow-right icon-white"></i> View the Demo</a>
			    	 </p>
			    	 <br/>


			    	 <h4>How does it work?</h4>
			    	 <p>Simply drop the following lines of HTML into your web form. You only need to
			    	 specify the name of the form field and the widget will do the rest!
			    	 </p>

			    	 <em>Step 1.</em> Drop this code somewhere in the &lt;head&gt;&lt;/head&gt; of your web page
			    	 <pre class="prettyprint">
&lt;script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&libraries=drawing&v=3"&gt;&lt;/script&gt;
&lt;script src='http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.js'&gt;&lt;/script&gt;
&lt;script type="text/javascript" src="http://services.ands.org.au/api/resolver/location_capture_widget.js"&gt;&lt;/script&gt;
&lt;link rel="stylesheet" type="text/css" href="http://services.ands.org.au/api/resolver/location_capture_widget.css" /&gt;
					</pre>

					<em>Step 2.</em> Invoke the plugin using this code towards the bottom of the page. This example invokes the plugin on an HTML element with id <code>mapContainer</code>, and stores the final coordinate data in the HTML element with id <code>coordinates</code>:
					<pre class="prettyprint">
&lt;script type="text/javascript"&gt;
  $(document).ready(function() {
    $("#mapContainer").ands_location_widget({target:'coordinates'});
  });
 </pre>

			    	 <em>Step 3.</em> Load the web page and see the new widget appear! <br/><br/>Once submitted, the
			    	 coordinates of the location selected will be in the form value you chose for <code>target</code>


			    	 <br/><br/>
				 <h4>Functions</h4>
				 <p>The ANDS Location Capture widget plugin supports the following functions:</p>
				 <dl class="dl-horizontal">
				   <dt>init</dt>
				   <dd>Initialise the plugin against a jQuery object; use a jQuery selector to define the object.</dd>
				   <dd><strong>example:</strong><br/><code>$("#mapContainer").ands_location_widget('init');</code></dd>
				   <dt>googlemap</dt>
				   <dd>Once initialised, call the plugin on the jQuery object again to access the underlying google map object.</dd>
				   <dd><strong>example:</strong><br/><code>var map = $("#mapContainer").ands_location_widget('googlemap');</code></dd>
				 </dl>
			    	 <h4>Initialisation options</h4>
				 <p>The ANDS Location Capture widget plugin's <code>init</code> function has the following options:</p>
				 <dl class="dl-horizontal">
				   <dt>mode</dt>
				   <dd>Under normal operation, the widget opens in map view. Use this setting to open either in the 'search' or 'coordinates' dialogue.
				   <dd><strong>valid modes:</strong> search, coords</dd>
				   <dd><strong>default:</strong> n/a</dd>
				   <dd><strong>example:</strong><br/> <code>$("#mapContainer").ands_location_widget('init', {mode:'search'});</code></dd>
				   <dt>zoom</dt>
				   <dd>The initial zoom level of the map</dd>
				   <dd><strong>default:</strong> 3</dd>
				   <dd><strong>example:</strong><br/> <code>$("#mapContainer").ands_location_widget('init', {zoom:7});</code></dd>
				   <dt>start</dt>
				   <dd>The initial map view, given as a string of "<i>longitude</i>, <i>latitude</i>"</dd>
				   <dd><strong>default:</strong> "133, -27"</dd>
				   <dd><strong>example:</strong><br/> <code>$("#mapContainer").ands_location_widget('init', {start:"130, -16"});</code></dd>
				   <dt>target</dt>
				   <dd>The HTML id attribute of the element to store the final coordinate data in. If no such element exists, it wil be created for you, and inserted immediately after the plugin element.</dd>
				   <dd><strong>default:</strong> geoLocation</dd>
				   <dd><strong>example:</strong><br/> <code>$("#mapContainer").ands_location_widget('init', {target:'coordinate_data'});</code></dd>
				   <dt>lonLat</dt>
				   <dd>Initial coordinate data to display on the map. Coordinates are specified as a comma-delimited string <i>longitude</i>, <i>latitude</i>. Coordinate data can be a single point, or an array of points representing a region. Regions should be closed (that is, the first and last points should be the same).</dd>
				   <dd><strong>default:</strong> n/a</dd>
				   <dd><strong>example:</strong><br/> <code>$("#mapContainer").ands_location_widget('init', {lonLat:'133, -27'});</code></dd>
				   <dt>jumpToPoint</dt>
				   <dd>When entering 'point' mode, <code>jumpToPoint</code> defines whether the map view should jump to the existing point, or stay at the current map view.</dd>
				   <dd><strong>default:</strong> true</dd>
				   <dd><strong>example:</strong><br/> <code>$("#mapContainer").ands_location_widget('init', {jumpToPoint:false});</code></dd>
				   <dt>endpoint</dt>
				   <dd>The ANDS resolver service to use. Change this when you want to use your own resolver service.</dd>
				   <dd><strong>default:</strong> services.ands.org.au/api/resolver/</dd>
				   <dd><strong>example:</strong><br/> <code>$("#mapContainer").ands_location_widget('init', {endpoint:'http://localhost.localdomain/myresolver/'});</code></dd>
				   <dt>gasset_protocol</dt>
				   <dd>The HTTP protocol to use for google asset (image) requests : choose <code>http://</code> or <code>https://</code></dd>
				   <dd><strong>default:</strong> (the current window's protocol)</dd>
				   <dd><strong>example:</strong><br/> <code>$("#mapContainer").ands_location_widget('init', {gasset_protocol:'https://'});</code></dd>
				 </dl>

				 <h4>Questions and answers</h4>
			    	 <h5>I'm getting an "insecure content" warning? Can the widget run under HTTPS?</h5>
			    	 <p>Yes! Ensure that all the <code>&lt;script&gt;</code> and <code>&lt;link&gt;</code> tags (from Step 1 &amp; 2) are pointing to the securely-hosted version of the resource.
			    	 	In other words, the URL starts with <b>https://</b> (such as <code>https://maps.google.com/api...</code>).
			    	 </p>
			    	 <br/>


			    	 <h5>What service is doing the placename resolution? </h5>
			    	 <p>ANDS hosts a resolver proxy service that provides JSONP results based on the response from the Gazetteer service.
			    	 	An example of this script is included in the source code package. You can customise this proxy service yourself
			    	 	and change the location by passing <code>endpoint</code> (and optionally, <code>protocol</code>) options to the widget's <code>init</code> function.
			    	 </p>
			    	 <br/>

			    	 <h5>How can I customise the widget / not use the ANDS-hosted resources? </h5>
			    	 <p>Full source code for this widget is available and licensed under Apache License, Version 2.0.</p>
			    	 <br/>


			    </div>



			    <div class="span4">
			    	<h5>Enrich your web forms in seconds...</h5>
			    	<p>
				    	<a href="<?=base_url('location_capture_widget/demo');?>"><img src="<?=asset_url('img/resolution_widget1.png');?>" class="img-rounded" alt="Resolution of Place Names" /></a>
						<small class="pull-right"><em>Resolve place names to coordinates using the Australian Gazetteer Service and Google Maps API.</em></small>
					</p>
					<br/>
					<p>
				    	<a href="<?=base_url('location_capture_widget/demo');?>"><img src="<?=asset_url('img/resolution_widget2.png');?>" class="img-rounded" alt="Draw Regions" /></a>
						<small class="pull-right"><em>Allow your users to provide richer location content by defining their own regions by drawing them on the map.</em></small>
					</p>

			    	 <br/><br/>
			    	<h4>Download Sourcecode</h4>
			    	 <a class="btn btn-success" href="<?=asset_url('location_capture_widget_v1.1.zip');?>"><i class="icon-download icon-white"></i> &nbsp;Download Now - v1.1</a>

			    	 <br/><br/>
					<h4>License</h4>
			    	 Licensed under the Apache License, Version 2.0. <br/>
			    	 <a href="http://www.apache.org/licenses/LICENSE-2.0">http://www.apache.org/licenses/LICENSE-2.0</a>

			    </div>


		   </div>
		</div>

	</div>
</div>


</section>

</div>
<?php $this->load->view('footer');?>
