@extends('layouts/activity')
@section('content')
<article>
    <section class="section swatch-white element-normal-bottom">
        <div class="container">
            <div class="row">
                <div id="grant-query-div" >
                    <h3>We're sorry...</h3>
                    <p>The page or record you are looking for cannot be found or displayed.</p><p>
                        Were you looking for information about the {{$institution}} grant <b>{{$grantId}}</b>?</p>
                    <p>The record for this grant is not available in Research Data Australia yet;<br/>
                        however, we can send you a notification once the grant record has been published in Research Data Australia. To receive the notification, please complete the below form:</p>
                    <h4>Register for notification</h4>

                    <form id="grant-query-form"  ng-controller="grantForm" ng-submit="processGrantRequestForm()">

                        <div class="control-group">
                            <div class="controls">
                                <label class="control-label" for="garnt-id-val">Grant ID: </label><br/>
                                <input type="text" size="35" class="input-xlarge" disabled="disabled" name="garnt-id-val" value="{{$grantId}}"/>
                                <input type="hidden" id="grant_id" name="grant_id" value="{{$grantId}}"/>
                                <input type="hidden" id="purl" name="purl" value="{{$purl}}"/>
                                <input type="hidden" id="institution" name="institution" value="{{$institution}}"/>
                                <p class="help-inline"><small></small></p>
                            </div>
                        </div>

                        <div class="control-group">
                            <div class="controls">
                                <label class="control-label" for="grant-title">Grant Title: </label><br/>
                                <input type="text" size="80" class="input-xlarge" name="grant_title" ng-model="grant_title" value="" placeholder="Title of the grant you were looking for">
                                <p class="help-inline"><small></small></p>
                            </div>
                        </div>
                        <hr/>
                        <div class="control-group">
                            <div class="controls">
                                <label class="control-label" for="contact-name">Your Name: </label><br/>
                                <input type="text" size="35" class="input-xlarge" name="contact_name" ng-model="contact_name" value="" placeholder="Enter your full name" required/>
                                <p class="help-inline"><small></small></p>
                            </div>
                        </div>

                        <div class="control-group">
                            <div class="controls">
                                <label class="control-label" for="contact-company">Your Company / University / Affiliation: </label><br/>
                                <input type="text" size="35" class="input-xlarge" name="contact_company" ng-model="contact_company" value="" placeholder="Company/ university/ affiliation" required/>
                                <p class="help-inline"><small></small></p>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="controls">
                                <label class="control-label" for="contact-email">Your Contact Email: </label><br/>
                                <input type="email" size="35" class="input-xlarge" name="contact_email" ng-model="contact_email" value="" placeholder="Enter your email address" required/>
                                <p class="help-inline"><small></small></p>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="grant-query-send-button">Submit</button>
                    </form>
            </div>
        </div>
    </section>
</article>
@stop