@extends('layouts/right-sidebar')
@section('content')
<article>
  <section class="section swatch-white">
    {{$this->user->name()}}
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