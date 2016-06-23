@extends('layout/vocab_layout')
@section('comment')
CSS styling is done in                       applications/portal/vocabs/assets/less/ands-vocabs-widget-explorer.less
The "widget-directive" is implemented in     applications/portal/vocabs/assets/js/widgetDirective.js
That directive pulls in the template         applications/portal/vocabs/assets/templates/widgetDirective.html
That uses the "widget-display-directive",
  which is implemented in                    applications/portal/vocabs/assets/js/vocabDisplayDirective.js
That directive pulls in the template         applications/portal/vocabs/assets/templates/widgetVocabDisplay.html
That uses the "concept-display" directive,
  which is implemented in                    applications/portal/vocabs/assets/js/conceptDisplayDirective.js
That directive pulls in the template         applications/portal/vocabs/assets/templates/conceptDisplay.html
@stop
@section('content')
<section class="section element-short-bottom element-short-top">
    <div class="container">
        <div class="row element-short-bottom">
            <div class="col-md-12">
                <header class="text-center">
                    <h1 class="bigger hairline bordered bordered-normal">Widget Explorer</h1>
                </header>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <p>The Research Vocabularies Australia widget allows you to add data classification capabilities to your
                    data discovery tools. By incorporating the widget (<a href="http://developers.ands.org.au/widgets/controlled-vocabulary-widget-v2/"
                        target="_blank">using a simple jQuery plugin</a>) in a data discovery workflow using your
                    chosen vocabularies, your users can browse and select concepts to aid in their discovery of your resources. In addition, you
                    can incorporate the widget into your description tools to allow those describing your resources to easily make use of controlled
                    terminology. Below you’ll find a five-step process to explore and configure the vocabulary widget for use in your web applications.
                    Start by browsing the “widgetable” vocabularies listed in the dropdown under step 1.</p>
                <p>If there is a vocabulary you’d like to use via the Research Vocabularies Australia widget that you can’t find in the RVA portal, please <a href="mailto:services@ands.org.au">let us know!</a></p>
            </div>
        </div>
    </div>
</section>

<section class="section post">
    <div widget-directive class="post-body"></div>
</section>
@stop

@section('sidebar')

@stop
