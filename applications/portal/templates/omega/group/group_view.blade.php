@extends('layouts/right-sidebar')
@section('content')
  <article class="post">
    <header class="post-head">
          <div class="post-title bordered">
              <h1>{{$group['title']}}</h1>
          </div>
    </header>

    <div class="post-body">
      To date, {{$group['title']}} has {{$group['counts']}} collection records in Research Data Australia, which covers {{sizeof($group['facet']['subjects'])}} subjects areas including 
      {{$group['facet']['subjects'][0]['name']}}, {{$group['facet']['subjects'][1]['name']}} and {{$group['facet']['subjects'][2]['name']}}, {{sizeof($group['groups'])}} research groups 
      have been actively involved in collecting data and creating metadata records for the data.  All the Collections, Parties, Activities and Services associated with {{$group['title']}} 
      can be accessed from the Registry Contents box on the right hand side of this page.
    </div>
  </article>
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