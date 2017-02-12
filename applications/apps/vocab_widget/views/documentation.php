<?php
/*
 * XXX
 */
?>
<?php $this->load->view('header');?>
<div class="container" id="main-content">

  <section>

    <div class="row">
      <div class="span12">
	<div class="box">
	  <div class="box-header clearfix">
	    <h1><?=$title;?></h1>
	  </div>
	  <div class="row-fluid">
	    <div class="span8">
	      <div class="alert alert-info">
		<strong>Developer Zone!</strong><br/> Some basic web development knowledge may be needed to implement this widget.
	      </div>
	      <h2>Contents</h2>
	      <ul>
		<li><a href="#whatiswhat">What is this widget?</a></li>
		<li><a href="#hownow">How does it work?</a>
		<ul>
		  <li><a href="#search">Searching</a></li>
		  <li><a href="#narrow">Narrow / Collection</a></li>
		  <li><a href="#tree">Tree</a></li>
		  <li><a href="#core">Core usage</a></li>
		</ul>
		</li>
		<li><a href="conf">Configuration</a>
		<ul>
		  <li><a href="conf-common">Common options</a></li>
		  <li><a href="conf-search">Search options</a></li>
		  <li><a href="conf-narrow">Narrow/inCollection options</a></li>
		</ul>
		</li>
		<li><a href="#events">Events</a></li>
		<li><a href="#data">Data</a></li>
	      </ul>
	      <hr/>
	      <a name="whatiswhat"></a>
	      <h2>What is this widget?</h2>
	      <p>
		The ANDS Vocabulary Widget allows you to instantly add Data Classification capabilities to your data capture tools through the ANDS Vocabulary Service.
	      </p>
	      <p>
		The widget has been written in the style of a jQuery plugin, allowing complete control over styling and functionality with just a few lines of javascript. The widget also ships with some UI helper modes for:
	      </p>
	      <dl>
		<dt><strong>Search</strong>ing for vocabulary terms</dt>
		<dd>Creates a navigable "autocomplete" widget, with users able to search for the appropriate controlled vocabulary classification when inputting data.</dd>
		<dt><strong>Narrow</strong>ing on a (hierarchical) vocabulary item</dt>
		<dd>Populates a select list (or autocomplete textbox) with items comprising a base vocabulary classification URI.</dd>
		<dt>Browsing a (hierarchical) vocabulary set as a <strong>tree</strong></dt>
		<dd>Creates a tiered term tree (such as that used in the <a href="http://researchdata.ands.org.au/browse">RDA "Browse" screen</a>)</dd>
	      </dl>
	      <p>
		It is also possible to use the widget in a more programmatic manner; refer to the 'core usage' section below for more details.
	      </p>
	      <p>
		<a target="_blank" class="btn btn-success" href="<?=base_url('vocab_widget/demo/');?>"><i class="icon-circle-arrow-right icon-white"></i> View the Demo</a>.
	      </p>
	      <p>
		The demonstrator provides examples of all helper modes, and core usage.
	      </p>
	      <a name="hownow"></a>
	      <h2>How does it work?</h2>
	      <p>
		The widget requires jQuery; load this, and the plugin itself (and associated CSS styles) in your document's &lt;head&gt;&lt;/head&gt; segment:
	      </p>
<pre class="prettyprint pre-scrollable" style="min-height:5em">
&lt;script src='http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.js'&gt;&lt;/script&gt;
&lt;script type="text/javascript" src="http://services.ands.org.au/api/resolver/vocab_widget.js"&gt;&lt;/script&gt;
&lt;link rel="stylesheet" type="text/css" href="http://services.ands.org.au/api/resolver/vocab_widget.css" /&gt;
</pre>
	      <p>
		What happens next very much depends on what you want to achieve:
	      </p>
              <a name="search"></a>
	      <h3>Searching</h3>
	      <p>
		Search a vocabulary for matching terms, provided in an autocomplete-style list:
	      </p>
	      <div class="row">
		<div class="span1"></div>
		<div class="thumbnail span6">
		  <img  src="<?php echo asset_url('demo/search_eg.png');?>">
		  <div class="caption">
		    <a class="btn btn-primary" target="_new" href="<?=base_url('vocab_widget/demo/#search');?>">View live example</a><br/>
		    <small>(Click the orange button for sample code.)</small>
		  </div>
		</div>
	      </div>
              <a name="narrow"></a>
              <h3>Narrow/Collection</h3>
	      <p>
		Narrow or collection mode can be attached to a select element, or a text input box for an autocomplete-style list:
	      </p>
	      <div class="row">
		<div class="span1"></div>
		<div class="thumbnail span6">
		  <img  src="<?php echo asset_url('demo/narrow_eg.png');?>">
		  <div class="caption">
		    View live example:
		    <a class="btn btn-primary" target="_new" href="<?php echo asset_url('demo.html#narrow-select'); ?>"><small><code>&lt;select /&gt</code></small></a>
		    <a class="btn btn-primary" target="_new" href="<?php echo asset_url('demo.html#narrow-input'); ?>"><small><code>&lt;input /&gt</code></small></a><br/>
		    <small>(Click the orange button for sample code.)</small>
		  </div>
		</div>
	     </div>
 		<div>
 			<small><ul><li><code>narrow</code> mode usually expresses a direct parent-child relationship in the vocabulary (such as <em>skos:narrower</em>).</li>
 				<li><code>collection</code> is used to express less strong groupings of concepts in a vocabulary (such as <em>skos:Collection</em> or even <em>rdf:list</em>).</li>
 			</ul></small>
 		</div>

	      <a name="tree"></a>
	      <h3>Tree</h3>
	      <p>
		Tree mode constructs a clickable vocabulary tree for a given repository. Bind to the <code>treeselect.vocab.ands</code> event to handle user selection.
	      </p>
	      <div class="row">
		<div class="span1"></div>
		<div class="thumbnail span6">
		  <img  src="<?php echo asset_url('demo/tree_eg.png');?>">
		  <div class="caption">
		    <a class="btn btn-primary" target="_new" href="<?=base_url('vocab_widget/demo/#tree');?>">View live example</a><br/>
		    <small>(Click the orange button for sample code.)</small>
		  </div>
		</div>
	      </div>

              <a name="core"></a>
              <h3>Core usage</h3>
	      <p>
		Invoking the plugin with no 'mode' argument exposes core functionality, without having to use form input (text, select) elements or the like. Instead, you hook into javascript Events, building the UI as best fits your needs. A very basic example is shown below: it constructs a list of RIFCS identifier types. (For core usage, sample code is shown here as well as on the demo page). <a class="btn btn-primary" target="_new" href="<?php echo asset_url('demo.html#core'); ?>">View live example</a>
	      </p>
<pre class="prettyprint pre-scrollable">
&lt;div id="ident-list"&gt;&lt;/div&gt;
...
&lt;script text="text/javascript"&gt;
  $(document).ready(function() {
    var elem = $("#ident-list");
    var widget = elem.vocab_widget({repository:'rifcs'});

    //set up some handlers
    elem.on('narrow.vocab.ands, function(event, data) {
        var list = elem.append('&lt;ul /&gt;');
        $.each(data.items, function(idx, e) {
	    var link = $('&lt;a href="' + e['about'] + '"&gt;' +
		         e['label'] + '&lt;/a&gt;');
	    var item = $('&lt;li /&gt;');
	    item.append(link).append(' (' + e.definition + ')');
	    item.data('data', e);
	    list.append(item);
        });
    });
    elem.on('error.vocab.ands, function(event, xhr) {
        elem.addClass('error')
	    .empty()
	    .text('There was an error retrieving vocab data: ' + xhr);
    });

    //now, perform the vocab lookup
    widget.vocab_widget('narrow',
		        'http://purl.org/au-research/vocabulary/RIFCS/1.4/RIFCSIdentifierType');

  }
&lt;/script&gt;

</pre>
              <h4>Functions</h4>
	      <p>
		Core usage exposes 3 functions:
	      </p>
	      <ul>
		<li>search</li>
		<li>narrow</li>
		<li>top</li>
	      </ul>
	      <p>
		These take a single additional parameter, which can look like any of the following:
	      </p>
	      <dl>
		<dt>plain string</dt>
		<dd>Search term (for search call) or narrow URI (for narrow call)</dd>
		<dt>object <code>{uri:'...', callee:'...'}</code></dt>
		<dd>'uri' works as the plain string description above, and should be set <code>false</code>.
		</dd>
		<dd>'callee' defines the object that will fire the subsequent javascript event. Defaults to the containing element (what you invoked the widget on)</dd>
	      </dl>
	      <a name="conf"></a>
	      <h2>Configuration</h2>
              <p>
		The plugin accepts a suite of options, detailed below. <strong>Please note</strong> that some options are required, and don't have default values (such as <code>repository</code>: you must provide values for such options. Incorrectly configured plugins will result in a javascript 'alert' box being displayed, describing the nature of the configuration problem.
	      </p>
	      <p>
		Options are passed into the plugin using a Javascript hash/object, such as
	      </p>
<pre>
$("#vocabInput").vocab_widget({cache: false});
</pre>
              <p>
		Be sure to quote strings, and separate multiple options with a comma (<code>,</code>).
	      </p>
	      <p>
		Alternatively, options can be set after initialisation using the following form:
	      </p>
<pre>
$(...).vocab_widget('[option name]', [option value]);
</pre>
              <p>
		This works for all options <strong>except</strong> <code>mode</code>, which must be specified at initialisation (or omitted for core usage).
              </p>

	      <p>
		Some options are specific to the chosen mode; the tables below are grouped in a way that makes this easy to comprehend. Core usage of the widget exposes all "common" options.
	      </p>
	      <div class="alert">
		<strong>Note:</strong> 'tree' mode has no specific configuration other than the widget's common options.
	      </div>
	      <a name="conf-common"></a>
	      <table class="table" style="font-size:0.9em">
		<caption>
		  <h3>Common options</h3>
		  <strong>Legend:</strong>
		  <span class="badge badge-info">S</span>: String,
		  <span class="badge badge-info">I</span>: Integer,
		  <span class="badge badge-info">B</span>: Boolean,
		  <span class="badge badge-info">[n]</span>: Array of 'n'<br/>
		  (required options with no default are marked <span style="background-color:#FFAAAA">like this</span>)
		</caption>
		<thead>
		  <tr>
		    <th style="width:22%;text-align:left">Option</th>
		    <th style="text-align:left">Default value</th>
		    <th style="text-align:left">Description</th>
		  </tr>
		</thead>
		<tbody style="font-size:0.9em">
		  <tr>
		    <td>mode <span class="pull-right badge badge-info">S</span></td>
		    <td>-</td>
		    <td>Vocab widget mode: <code>search</code> provides an autocomplete widget on an HTML input element, while <code>narrow</code> or  <code>collection</code> populate an HTML select element with appropriate data. <code>advanced</code> mode exposes the core widget with no UI helpers.</td>
		  </tr>
		  <tr  class="required">
		    <td><span style="background-color:#FFAAAA">repository</span> <span class="pull-right badge badge-info">S</span></td>
		    <td>-</td>
		    <td>The SISSvoc repository to query (e.g. <code>anzsrc-for</code>, <code>rifcs</code>)</td>
		  </tr>
		  <tr>
		    <td>max_results <span class="pull-right badge badge-info">I</span></td>
		    <td>100</td>
		    <td>At most, how many results should be returned?</td>
		  </tr>
		  <tr>
		    <td>cache <span class="pull-right badge badge-info">B</span></td>
		    <td>true</td>
		    <td>Cache SISSvoc responses?</td>
		  </tr>
		  <tr>
		    <td>error_msg <span class="pull-right badge badge-info">S</span> <span class="pull-right badge badge-info">B</span></td>
		    <td>"ANDS Vocabulary Widget service error"</td>
		    <td>Message title to display (via a js 'alert' call) when an error is encountered. Set to <span class="badge badge-info">B</span> <code>false</code> to suppress such messages</td>
		  </tr>
		  <tr>
		    <td>endpoint <span class="pull-right badge badge-info">S</span></td>
		    <td>"http://services.ands.org.au/api/resolver/vocab_widget/"</td>
		    <td>Location (absolute URL) of the (JSONP) SISSvoc provider.</td>
		  </tr>
		</tbody>
	      </table>

	      <a name="conf-search"></a>
	      <table class="table" style="font-size:0.9em">
		<caption>
		  <h3>"Search" helper options</h3>
		  <strong>Legend:</strong>
		  <span class="badge badge-info">S</span>: String,
		  <span class="badge badge-info">I</span>: Integer,
		  <span class="badge badge-info">B</span>: Boolean,
		  <span class="badge badge-info">[n]</span>: Array of 'n'<br/>
		  (required options with no default are marked <span class="required">like this</span>)
		</caption>
		<thead>
		  <tr>
		    <th style="width:30%;text-align:left">Option</th>
		    <th style="width:30%;text-align:left">Default value</th>
		    <th style="text-align:left">Description</th>
		  </tr>
		</thead>
		<tbody style="font-size:0.9em">
		  <tr>
		    <td>min_chars <span class="pull-right badge badge-info">I</span></td>
		    <td>3</td>
		    <td>How many characters are required before a search is executed?</td>
		  </tr>
		  <tr>
		    <td>delay <span class="pull-right badge badge-info">I</span></td>
		    <td>500</td>
		    <td>How long to wait (after initial user input) before executing the search? Provide in milliseconds</td>
		  </tr>
		  <tr>
		    <td>nohits_msg <span class="pull-right badge badge-info">S</span> <span class="pull-right badge badge-info">B</span></td>
		    <td>"No matches found"</td>
		    <td>Message to display when no matching concepts are found. Set to <span class="badge badge-info">B</span> <code>false</code> to suppress such messages</td>
		  </tr>
		  <tr>
		    <td>list_class <span class="pull-right badge badge-info">S</span></td>
		    <td>"vocab_list"</td>
		    <td>CSS 'class' references for the dropdown list. Separate multiple classes by spaces</td>
		  </tr>
		  <tr>
		    <td>fields <span class="pull-right badge badge-info">[S]</span></td>
		    <td>["label", "notation", "about"]</td>
		    <td>Which fields do you want to display? Available fields are defined by the chosen repository.<br/>
		  </tr>
		  <tr>
		    <td>target <span class="pull-right badge badge-info">S</span></td>
		    <td>"notation"</td>
		    <td>What data field should be stored upon selection?</td>
		  </tr>
		</tbody>
	      </table>

	      <a name="conf-narrow"></a>
	      <table class="table" style="font-size:0.9em">
		<caption>
		  <h3>"Narrow" or "Collection" helper options</h3>
		  <strong>Legend:</strong>
		  <span class="badge badge-info">S</span>: String,
		  <span class="badge badge-info">I</span>: Integer,
		  <span class="badge badge-info">B</span>: Boolean,
		  <span class="badge badge-info">[n]</span>: Array of 'n'<br/>
		  (required options with no default are marked <span style="background-color:#FFAAAA">like this</span>)
		</caption>
		<thead>
		  <tr>
		    <th style="width:30%;text-align:left">Option</th>
		    <th style="width:30%;text-align:left">Default value</th>
		    <th style="text-align:left">Description</th>
		  </tr>
		</thead>
		<tbody style="font-size:0.9em">
		  <tr>
		    <td><span style="background-color:#FFAAAA">mode_params</span> <span class="pull-right badge badge-info">S</span></td>
		    <td>-</td>
		    <td>For narrow mode, <code>mode_params</code> defines the vocabulary item upon which to narrow.</td>
		  </tr>
		  <tr>
		    <td>fields <span class="pull-right badge badge-info">[S]</span></td>
		    <td>["label", "notation", "about"]</td>
		    <td>In narrow mode, this option <strong>must be overridden</strong> to be a single-element array of string  <span class="badge badge-info">[S]</span>. This selection defines the label for the select list options.</td>
		  </tr>
		  <tr>
		    <td>target <span class="pull-right badge badge-info">S</span></td>
		    <td>"notation"</td>
		    <td>What data field should be stored upon selection? In narrow mode, this field is used as the <code>value</code> attribute for the select list options</td>
		  </tr>
		</tbody>
	      </table>

	      <a name="events"></a>
	      <h2>Events</h2>
	      <p>
		When run in advance mode, events are fired to allow you to hook into the workflow and implement your customisations as you see fit.
	      </p>
	      <div class="alert alert-info">
		Plugin event are placed in the <code>vocab.ands</code> namespace
	      </div>
	      <table class="table" style="font-size:0.9em">
		<thead>
		  <tr>
		    <th style="width:20%;text-align:left">Event name</th>
		    <th style="width:40%;text-align:left">Parameters</th>
		    <th style="text-align:left">Description</th>
		  </tr>
		</thead>
		<tbody style="font-size:0.9em">
		  <tr>
		    <td>search.vocab.ands</td>
		    <td>
		      <ol>
			<li>JS Event object</li>
			<li>SISSVOC data object</li>
			</ul>
			</li>
		      </ol>
		    </td>
		    <td>
		      Hook into the plugin's <code>search</code> core function; <code>data</code> is the search response.
		    </td>
		  </tr>
		  <tr>
		    <td>narrow.vocab.ands</td>
		    <td>
		      <ol>
			<li>JS Event object</li>
			<li>SISSVOC data object</li>
		      </ol>
		    </td>
		    <td>
		      Hook into the plugin's <code>narrow</code> core function; <code>data</code> is the search response.
		    </td>
		  </tr>
		  <tr>
		    <td>top.vocab.ands</td>
		    <td>
		      <ol>
			<li>JS Event object</li>
			<li>SISSVOC data object</li>
		      </ol>
		    </td>
		    <td>
		      Hook into the plugin's <code>top</code> core function; <code>data</code> is the search response.
		    </td>
		  </tr>
		  <tr>
		    <td>treeselect.vocab.ands</td>
		    <td>
		      <ol>
			<li>JS Event object</li>
		      </ol>
		    </td>
		    <td>
		      Fired when a tree item is clicked. The selected item is the <code>event</code> target. The target will have a 'vocab' data object, containing all the details found in a SISSVOC data object.
		    </td>
		  </tr>
		  <tr>
		    <td>error.vocab.ands</td>
		    <td>
		      <ol>
			<li>JS Event object</li>
			<li>XMLHttpRequest*</li>
		      </ol>
		    </td>
		    <td>
		      This event is fired whenever there is a problem communicating with the plugin's <code>endpoint</code>.<br/>
		      <span class="label label-warning">Note:</span> <span>If the error occurred during an AJAX call, the object will be a bona fide XMLHttpRequest / xhr. Otherwise, a dummy plain object with 'status' and 'responseText' properties will be available.</span>
		    </td>
		  </tr>
		</tbody>
	      </table>

	      <a name="data"></a>
	      <h2>Data</h2>
	      <p>
		The SISSVOC data object returned by the above events (and also attached to the 'treeselect' event's 'vocab' data object) is a plain javscript object with the following properties:
	      </p>
	      <dl>
		<dt>status</dt>
		<dd>'OK' if all good, something else (most likely 'ERROR' if not)</dd>
		<dt>message</dt>
		<dd>description of the underlying system call by default, or information on status when something went wrong</dd>
		<dt>limit</dt>
		<dd>the maximum number of records requested</dd>
		<dt>items</dt>
		<dd>an array of SISSVOC vocabulary items:
		  <dl class="dl-horizontal">
		    <dt>definition</dt>
		    <dd>item description</dd>
		    <dt>label</dt>
		    <dd>item label</dd>
		    <dt>about</dt>
		    <dd>item definition / URL</dd>
		    <dt>broader</dt>
		    <dd>parent term (if it exists)</dd>
		    <dt>narrower</dt>
		    <dd>child terms (if they exist, otherwise boolean false)</dd>
		    <dt>count</dt>
		    <dd>fequency of use among ANDS registry objects (experimental; works best on ANZSRC-FOR, not so well on RIFCS)</dd>
		  </dl>
		</dd>
		<dt>count</dt>
		<dd>the number of items returned</dd>
	      </dl>
	    </div>



	    <div class="span4">
	      <h3>Enrich your web forms in seconds...</h3>
	      <p>
		<a href="<?=asset_url('demo.html');?>"><img src="<?=asset_url('img/vocab_widget_screenshot.png');?>" class="img-rounded" alt="Resolution of Place Names" /></a>
		<small class="pull-right"><em>Allow data capture tools to quickly classify data through supported vocabularies (FOR, RIFCS, etc).</em></small>
	      </p>

	      <br/><br/>
	      <h3>Download Sourcecode</h3>
	      <a class="btn btn-success" href="<?=asset_url('vocab_widget.zip');?>"><i class="icon-download icon-white"></i> &nbsp;Download Now - v0.1</a>

	      <br/><br/>
	      <h3>License</h3>
	      Apache License, Version 2.0: <br/>

	      <a href="http://www.apache.org/licenses/LICENSE-2.0">http://www.apache.org/licenses/LICENSE-2.0</a>
	    </div>
	  </div>
	</div>
      </div>
    </div>
  </section>
</div>
<?php $this->load->view('footer');?>
