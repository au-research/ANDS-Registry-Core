<?php
/*
 * XXX
 */
?>
<?php $this->load->view('header');?>

<div class="container" id="main-content">
  <h2 class="alert alert-success">The actual demonstrator</h2>
  <form class="form form-horizontal">
    <fieldset>
      <legend>Predefined datetime of '2013-05-25T16:17:19Z'</legend>
      <div class="control-group">
	<div class="controls">
	  <input id="datetime" type="text" value="2013-05-25T16:17:19Z" />
	</div>
      </div>
    </fieldset>
    <fieldset>
      <legend>No predefined value (defaults to today and now)</legend>
      <div class="control-group">
	<div class="controls">
	  <input id="datetimenow" type="text"  />
	</div>
      </div>
    </fieldset>
  </form>
  <h2 class="alert">The boring details</h2>
  <h3>Setup and initialisation</h3>
  <p>
    First, include javascript for css resources for <strong>jQuery</strong>, <strong>Bootstrap</strong>, and the widget itself:
  </p>
<pre class="prettyprint">
&lt;script src="http://code.jquery.com/jquery-1.9.1.min.js"&gt;&lt;/script&gt;
&lt;link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" rel="stylesheet"&gt;&lt;/link&gt;
&lt;script src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/js/bootstrap.min.js"&gt;&lt;/script&gt;
&lt;link href="css/ands_datetimepicker.css" rel="stylesheet"&gt;&lt;/link&gt;
&lt;script src="js/ands_datetimepicker.js"&gt;&lt;/script&gt;
</pre>
  <p>
    The plugin will read the containing input field for a valid ISO8601 (Zulu Time) date string on initialisation. If the value is empty or invalid, the current date and time will be used instead (with an appropriate 'error.datepicker.ands' event triggered; see 'Events' section below for more details).
  </p>
  <p>
    Bind the widget to the input field:
  </p>
<pre class="prettyprint">
&lt;input id="datetime" type="text" value="2013-05-25T16:17:19Z"&gt;
</pre>
  <p>
    And there's a javascript snippet to initialise the widget:
  </p>
<pre class="prettyprint">
$(document).ready(function() {
  $("#datetime").ands_datetimepicker();
});
</pre>
  <p>
    The widget takes <strong>NO</strong> initialisation options, but there are some events you can subscribe to:
  </p>
  <h3>Events</h3>
  <dl>
    <dt>change.datepicker.ands</dt>
    <dd>Fired whenever the date is set (every calendar, date, timezone change triggers a datetime change).</dd>
    <dt>valid.datepicker.ands</dt>
    <dd>Fired on any <strong>change</strong> event (see above), or by a user-called <strong>validate</strong> function (see following section). Valid status provided by second parameter.</dd>
    <dt>error.datepicker.ands</dt>
    <dd>Fired on any error, but specifically on plugin initialisation (when an invalid date string is found).</dd>
  </dl>
  <h4>Example: binding to the events</h4>
<pre class="prettyprint">
$("#datetime").on('change.datepicker.ands', function(event, details) {
  console.log('UTC date: ' + details.utc + '\nTimezone: ' + details.tz);
});

$("#datetime").on('valid.datepicker.ands', function(event, isValid) {
  console.log('Current setting has is ' + (isValid ? '' : 'NOT ') + 'valid!');
});

$("#datetime").on('error.datepicker.ands', function(event, message) {
  console.log('Oops, a problem was encountered!\n' + message);
});


</pre>
  <h3>Validation</h3>
  <p>
    Validate the current value of the text box (against date parsing rules) using the <code>validate</code> function call:
  </p>
<pre class="prettyprint">
$("#datetime").ands_datetimepicker('validate');
</pre>
  <p>
    And listen out for a <strong>valid.datepicker.ands</strong> event; it will have an additional parameter with the valid status (true or false) as its value.
  </p>
</div>
<?php $this->load->view('footer');?>
