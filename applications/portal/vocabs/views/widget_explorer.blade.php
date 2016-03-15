
@extends('layout/vocab_layout')
@section('content')
<section class="section swatch-white element-short-bottom element-short-top">
    <div class="container">
        <div class="row element-short-bottom">
            <div class="col-md-12">
                <header class="text-center">
                    <h1 class="bigger hairline bordered bordered-normal">Widget Explorer</h1>
                </header>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <img src="{{ asset_url('images/vocabwidgetexample.png') }}" alt="Vocab Widget Example"/>
            </div>
            <div class="col-md-8">
                <p>The Research Vocabularies Australia widget allows you to add data classification capabilities to your
                    data discovery tools. By incorporating the widget (using a simple jQuery plugin) in a data discovery workflow using your
                    chosen vocabularies, your users can browse and select concepts to aid in their discovery of your resources. In addition, you
                    can incorporate the widget into your description tools to allow those describing your resources to easily make use of controlled
                    terminology. Below you’ll find a demonstration of how the widget might look in your tools.
                    You can browse “widgetable” vocabularies by checking out the vocabularies listed under “1. Select a widgetable vocab”
                    in the demo below, or by looking for this label in the Research Vocabularies Australia Portal:</p>
                <p class="text-center"><span class="label label-default text-center"><img class="widget-icon" height="16" width="16"src="{{asset_url('images/cogwheels_white.png', 'core')}}"/> widgetable</span></p>
                <p>If there is a vocabulary you’d like to use via the Research Vocabularies Australia widget that you can’t find in the RVA portal, please <a href="mailto:services@ands.org.au">let us know!</a></p>
            </div>
        </div>
    </div>
</section>

<section class="post">
    <div widget-directive class="post-body"></div>
</section>
@stop

@section('sidebar')

@stop
