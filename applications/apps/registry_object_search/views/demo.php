<?php
/*
 * XXX
 */
?>
<?php $this->load->view('header');?>

<div class="container" id="main-content">
  <h2 class="alert">The boring details</h2>
  <h3>Setup and initialisation</h3>
  <p>
    First, include javascript for css resources for <strong>jQuery</strong>, <strong>Bootstrap</strong>, and the widget itself:
  </p>
<pre class="prettyprint">
&lt;script src="http://code.jquery.com/jquery-1.9.1.min.js"&gt;&lt;/script&gt;
&lt;link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" rel="stylesheet"&gt;&lt;/link&gt;
&lt;script src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/js/bootstrap.min.js"&gt;&lt;/script&gt;
&lt;link href="css/rosearch_widget.css" rel="stylesheet"&gt;&lt;/link&gt;
&lt;script src="js/rosearch_widget.js"&gt;&lt;/script&gt;
</pre>
  <p>
    The input field is a normal text box:
  </p>
<pre class="prettyprint">
&lt;input type="text" id="rosearch"&gt;
</pre>
  <p>
    And there's a javascript snippet to initialise the widget:
  </p>
<pre class="prettyprint">
$(document).ready(function() {
  $("#rosearch").ro_search_widget();
});
</pre>
  <p>
    The widget takes some initialisation options, passed as a plain javascript object/hash:
  </p>
<pre class="prettyprint">
$(document).ready(function() {
  $("#rosearch").ro_search_widget(<strong>{[option_name]:[option_value],...}</strong>);
});
</pre>
  <h3>The options</h3>
  <dl class="dl-horizontal">
    <dt>class</dt>
    <dd>Registry object class to filter by (activity, party, collection, service). Defaults to 'all'.</dd>
    <dt>datasource</dt>
    <dd>Data Source ID to filter by. Defaults to 'all'.</dd>
    <dt>lock_presets</dt>
    <dd>If <em>class</em> or <em>datasource</em> are set [to something other than 'all'], should the selection be locked? Defaults to boolean <code>false</code>.</dd>
    <dt>endpoint</dt>
    <dd>URL for the search service. Defaults to <code>/registry/registry_object_search/</code></dd>
    <dt>error_msg</dt>
    <dd>Error message title. set to boolean <code>false</code> to suppress error messages (displayed using javascript alert)</dd>
    <dt>target_field</dt>
    <dd>Which registry object field to populate the input box with. Defaults to <code>key</code>, but can be anything that's available. (Inspect the attached record from the <code>selected.rosearch.ands</code> event to see what's in a registry object</dd>
  </dl>
  <h3>Further interaction</h3>
  <h4>Resetting</h4>
  <p>
    An initialised widget can be removed by passing in the <code>reset</code> command:
  </p>
<pre class="prettyprint">
...
$("#rosearch").ro_search_widget('reset');
</pre>
  <h4>Events</h4>
  <p>
    Once a search result has been selected form the widget's modal dialogue, a <code>selected.rosearch.ands</code> event will be triggered from the input box, with a registry object hash passed along. The input box will also have its content updated (according to the widget's <code>target_field</code> setting); the event is there to provide access to the full registry object record for additional use:
  </p>
<pre class="prettyprint">
$("#rosearch").on('selected.rosearch.ands', function(event, registry_object) { ... });
</pre>
  <h2 class="alert alert-success">The actual demonstrator</h2>
  <form class="form form-horizontal">
    <div class="control-group">
      <label class="control-label" for="rosearch">Related object</label>
      <div class="controls">
	<input type="text" id="rosearch"/>
	<span class="help-block">Note the widget adds the search button; all you need to provide is an input box</span>
      </div>
    </div>
  </form>
</div>
<?php $this->load->view('footer');?>
