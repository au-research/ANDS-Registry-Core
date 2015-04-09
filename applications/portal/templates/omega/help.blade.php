@extends('layouts/right-sidebar')
@section('header')
  <div class="row element element-short-top"></div>
@stop

@section('content')
<div class="panel swatch-white">
  <div class="panel-heading">Search</div>
  <div class="panel-body">
  </div>
</div>
<div class="panel swatch-white">
  <div class="panel-heading">Advanced Search</div>
  <div class="panel-body">
    @include('includes/help-adv-search')
  </div>
</div>
@stop

@section('sidebar')
<div class="panel swatch-white">
  <div class="panel-heading">Search</div>
  <div class="panel-body">
    <ul>
      <li><a href="#perform_a_search">Performing a Search</a></li>
    </ul>
  </div>
</div>
@stop