@extends('layouts/single-with-search')
@section('content')
<article>

	<section class="section swatch-white element-short-bottom">
	   <div class="container">
		   <div class="row">
			   <div class="col-md-12">
				   <header class="text-center element-normal-top element-no-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
					   <h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;"> We're sorry </h1>
				   </header>
			   </div>
		   </div>
		   <div class="row">
				<div class="col-md-8 col-md-offset-2">
					<div id="grant-query-div" >
						<p>The page or record you are looking for cannot be found or displayed.</p><p>
						Were you looking for information about the {{$institution}} grant <b>{{$grantId}}</b>?</p>
						<p>The record for this grant is not available in Research Data Australia yet;<br/>
						however, we can send you a notification once the grant record has been published in Research Data Australia. To receive the notification, please complete the below form:</p>
						<header class="text-center element-normal-top element-no-bottom not-condensed os-animation animated fadeInUp" data-os-animation="fadeInUp" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
							<h1 class="bigger hairline bordered bordered-normal os-animation animated fadeIn" data-os-animation="fadeIn" data-os-animation-delay="0s" style="-webkit-animation: 0s;"> Register for Notification </h1>
						</header>

						<form id="grant-query-form" class="form" ng-controller="grantForm" ng-submit="processGrantRequestForm()">
							<div class="form-group">
								<label class="control-label" for="garnt-id-val">Grant ID: </label><br/>
								<input type="text" size="35" class="form-control" disabled="disabled" value="{{$grantId}}"/>
								<input type="hidden" id="grant_id" name="grant_id" value="{{$grantId}}"/>
								<input type="hidden" id="purl" name="purl" value="{{$purl}}"/>
								<input type="hidden" id="institution" name="institution" value="{{$institution}}"/>
								<p class="help-inline"><small></small></p>
							</div>
						   
							<div class="form-group">
								<label class="control-label" for="grant-title">Grant Title: </label><br/>
								<input type="text" size="80" class="form-control" name="grant_title" ng-model="grant_title" value="" placeholder="Title of the grant you were looking for">
								<p class="help-inline"><small></small></p>
							</div>

							<hr/>
							<div class="form-group">
								<label class="control-label" for="contact-name">Your Name: </label><br/>
								<input type="text" size="35" class="form-control" name="contact_name" ng-model="contact_name" value="" placeholder="Enter your full name" required/>
								<p class="help-inline"><small></small></p>
							</div>

							<div class="form-group">
								<label class="control-label" for="contact-company">Your Company / University / Affiliation: </label><br/>
								<input type="text" size="35" class="form-control" name="contact_company" ng-model="contact_company" value="" placeholder="Company/ university/ affiliation" required/>
								<p class="help-inline"><small></small></p>
							</div>
							<div class="form-group">
								<label class="control-label" for="contact-email">Your Contact Email: </label><br/>
								<input type="email" size="35" class="form-control" name="contact_email" ng-model="contact_email" value="" placeholder="Enter your email address" required/>
								<p class="help-inline"><small></small></p>
							</div>

							<button type="submit" class="btn btn-primary">Submit</button>

						</form>
					</div>
				</div>
			</div>
		</div>
	</section>

</article>
@stop