@extends('layouts/right-sidebar')
@section('content')
<article>
  <section class="section swatch-white">
    
	@include('profile/dashboard_modules/saved_searches')
	@include('profile/dashboard_modules/saved_records')
  </section>
</article>
@stop

@section('sidebar')
  <?php
    $modules = array('meta', 'links');
  ?>
  @foreach($modules as $module)
    @include('profile/dashboard_modules/'.$module)
  @endforeach
@stop