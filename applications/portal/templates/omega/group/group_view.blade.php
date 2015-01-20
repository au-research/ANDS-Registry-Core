@extends('layouts/right-sidebar')
@section('content')
  <?php var_dump($group); ?>
@stop

@section('sidebar')
<div class="sidebar-widget widget_archive">
    <h3 class="sidebar-header">Registry Contents</h3>
    <ul>
      @foreach($group['facet']['class'] as $class)
        <li class="clearfix"><a href="">{{$class['name']}} <small>{{$class['num']}}</small></a></li>
      @endforeach
    </ul>
</div>

<div class="sidebar-widget widget_archive">
    <h3 class="sidebar-header">Subjects Covered</h3>
    <ul>
      @foreach($group['facet']['subjects'] as $subject)
        <li class="clearfix"><a href="">{{$subject['name']}} <small>{{$subject['num']}}</small></a></li>
      @endforeach
    </ul>
</div>

<div class="sidebar-widget widget_archive">
    <h3 class="sidebar-header">Organisations & Groups</h3>
    <ul>
      @foreach($group['groups'] as $gr)
        <li class="clearfix"><a href="">{{$gr['title']}}</a></li>
      @endforeach
    </ul>
</div>

<div class="sidebar-widget widget_archive">
    <h3 class="sidebar-header">Last 5 Collections Added</h3>
    <ul>
      @foreach($group['latest_collections'] as $gr)
        <li class="clearfix"><a href="">{{$gr['title']}}</a></li>
      @endforeach
    </ul>
</div>
@stop