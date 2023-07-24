<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>API Example</title>

	<link rel="stylesheet" href="<?php echo asset_url('app.css'); ?>">

</head>
<body>

<header class="navbar navbar-default navbar-fixed-top" role="banner">
	<div class="container">
		<div class="navbar-header">
			<button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			</button>
			<a href="#" class="navbar-brand">API Documentation</a>
		</div>

		<nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
			<ul class="nav navbar-nav">
				<li><a href="#getting_started">Getting started</a></li>
			</ul>
		</nav>
	</div>
</header>

<div class="container bs-docs-container">
	<div class="row">
		
		<div class="col-md-3">
			<div class="bs-sidebar" role="complementary" >
				<ul class="nav bs-sidenav">
					<li>
						<a href="#getting_started">Getting Started</a>
						<ul class="nav">
							<li><a href="#first">First</a></li>
							<li><a href="#second">Second</a></li>
						</ul>
					</li>
					<li><a href="#code_example">Code Example</a></li>
				</ul>
			</div>
		</div>

		<div class="col-md-9" role="main">

			<div class="page-header"><h1 id="getting_started">Getting Started</h1></div>
			<p class="lead">This is an example of getting started on something</p>

			<p>
				Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Phasellus hendrerit. Pellentesque aliquet nibh nec urna. In nisi neque, aliquet vel, dapibus id, mattis vel, nisi. Sed pretium, ligula sollicitudin laoreet viverra, tortor libero sodales leo, eget blandit nunc tortor eu nibh. Nullam mollis. Ut justo. Suspendisse potenti.
			</p>
			

			<h3>Download</h3>
			<p>
				The fastest way to download is to click the button
			</p>
			<p class="alert alert-info">The Download Button is big!</p>
			<a class="btn btn-lg btn-primary" href="#">Download Button</a>
			
			<div class="page-header"><h2 id="first">First</h2></div>
			<p class="lead">There is always a first of something</p>
			<h3>Example Code Block</h3>
			<pre>&lt;p&gt;Sample text here...&lt;/p&gt;</pre>
			

			<div class="page-header"><h2 id="second">Second</h2></div>
			<p class="lead">Pretty Syntax Highlight</p>
			<pre class="prettyprint">&lt;p&gt;Sample text here...&lt;/p&gt;</pre>
			<pre class="prettyprint">
$(document).on('load', 'stuff', function(){

});
			</pre>

			<div class="page-header"><h2 id="code_example">Code Example</h2></div>
			<p class="lead">GET and POST</p>
			<h3>HTTP POST to Calls</h3>
			<p>To make a call go to:</p>
			<pre>http://example.com/do_stuff/{api_key}?action={action}</pre>
			<h3>POST Parameters</h3>
			<table class="table">
				<thead>
					<tr><th>Parameter</th><th>Description</th></tr>
				</thead>
				<tbody>
					<tr><td>api_key</td><td><code>Required</code> because of reasons</td></tr>
					<tr><td>action</td><td>To do stuff with the <code>URL</code></td></tr>
				</tbody>
			</table>

		</div>


	</div>
</div>

<footer>
	
</footer>

<script src="<?php echo asset_url('app.js') ?>"></script>


</body></html>