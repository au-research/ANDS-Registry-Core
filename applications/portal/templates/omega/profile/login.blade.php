@extends('layouts/single-no-search')
@section('content')
<input type="hidden" id="default_authenticator" value="{{$default_authenticator}}">
<section class="section swatch-white">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <header class="element-normal-top element-short-bottom">
                    <h1 class="bigger hairline bordered bordered-normal"> Login to access MyRDA </h1>
                </header>
            </div>
        </div>
        <div ng-view></div>
    </div>
</section>

<div class="row hide" id="main">
    <div class="col-lg-6 col-lg-offset-3 col-xs-12 col-md-12">
        
        <div class="element-small-top element-short-bottom os-animation animated fadeInLeft" data-os-animation="fadeInLeft" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
            <div class="tabbable ''">
                <ul class="nav nav-tabs" data-tabs="tabs">
                	@foreach($authenticators as $auth)
						<li ng-class="{'<?php echo $auth['slug']?>':'active'}[tab]"> <a href="#{{$auth['slug']}}">{{$auth['display']}}</a> </li>
                	@endforeach
                </ul>
                @foreach($authenticators as $auth)
                	@include('profile/login/'.$auth['slug'])
                @endforeach
            </div>
            <div class="alert alert-danger os-animation animated fadeInUp" ng-show="message">[[message]]</div>
        </div>

        <div class="element-small-top">
            <p>By logging into Research Data Australia, you will have access to additional features including the ability to save records and searches, and contribute to the Research Data Australia community by adding tags (keywords) to records.</p>
            <p><small>Research Data Australia. <a href="{{ base_url('home/privacy') }}">Privacy Policy</a></small></p>
        </div>
    </div>
</div>

@stop