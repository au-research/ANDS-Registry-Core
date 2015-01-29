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
    <div class="col-md-6 col-md-offset-3">
        <div class="element-small-top element-normal-bottom os-animation animated fadeInLeft" data-os-animation="fadeInLeft" data-os-animation-delay="0s" style="-webkit-animation: 0s;">
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
    </div>
</div>

@stop