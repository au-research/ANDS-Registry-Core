
@extends('layout/vocab_layout')
@section('content')
<article class="post">
    <input id=portalUrl" type="hidden" value="{{portal_url('apps/vocab_widget/proxy/')}}"/>
    <div class="post-body" widget-directive>


    </div>
</article>
@stop

@section('sidebar')

@stop
