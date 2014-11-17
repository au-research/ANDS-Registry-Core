<?php
/******
 *
 * XXX
 *
 */?>

<?php $this->load->view('header');?>
      <div class="formarea">
	<h1>Vocab Widget Demonstrator</h1>
        <h2>Example Data Classification/Submission System</h2>
        <hr/>

        <div class="formfields">
          <form id="searchform">
	    <fieldset>
	      <legend>Details</legend>
              <dl>
		<dt>Chief Investigator Name:</dt>
		<dd><input type="text" name="cia_name" value="Joe Bloggs" size="40" /></dd>

		<dt>Project Name:</dt>
		<dd><input type="text" name="project_name" value="Coral community and extent mapping of the Wide Bay - Burnett Coastal fringe project" size="40" /></dd>
	      </dl>
	    </fieldset>
	    <a name="search"></a>
	    <fieldset>
	      <legend>Vocab widget: ANZSRC searching <button type="link" class="note-toggle">?</button></legend>
	      <div class="note">
		This form section is using the widget in the 'search' helper mode:
		<pre>
$("#anzsrc-vocab").vocab_widget({
    mode: 'search',
    cache: false,
    repository: 'anzsrc-for',
    target_field: 'label'});
		</pre>
	      </div>
	      <dl>
		<dt>ANZSRC Field of Research:</dt>
		<dd><input type="text" id="anzsrc-vocab" name="anzsrc-for" value="" size="40" /></dd>
	      </dl>

	      <i>(autocomplete; begin typing something (e.g. "BIOL"))</i>
	    </fieldset>
	    <a name="narrow-select"></a>
	    <fieldset>
	      <legend>Vocab widget: RIFCS narrowing <button type="link" class="note-toggle">?</button></legend>
	      <div class="note">
		This form section is using the widget in the 'narrow' helper mode on a standard selection list:
		<pre>
$("#rifcs-idtype").vocab_widget({
    mode:"narrow",
    mode_params:"http://purl.org/au-research/vocabulary/RIFCS/1.4/RIFCSIdentifierType",
    repository:"rifcs",
    cache: false,
    fields: ['definition'],
    target_field: 'label'});
		</pre>
	      </div>
	      <dl>
		<dt>RIFCS Identifier Type:</dt>
		<dd><select id="rifcs-idtype" name="rifcs-id-for" value=""></select></dd>
	      </dl>
	    </fieldset>
	    <a name="narrow-input"></a>
	    <fieldset>
	      <legend>Vocab widget: RIFCS narrowing w/ autocomplete <button type="link" class="note-toggle">?</button></legend>
	      <div class="note">
		This form section is using the widget in the 'narrow' helper mode on a autocomplete-style text box:
		<pre>
$("#rifcs-idtype-input").vocab_widget({
    mode:"narrow",
    mode_params:"http://purl.org/au-research/vocabulary/RIFCS/1.4/RIFCSIdentifierType",
    repository:"rifcs",
    cache: false,
    fields: ['label', 'definition', 'about'],
    target_field: 'label'});
		</pre>
	      </div>
	      <dl>
		<dt>RIFCS Identifier Type:</dt>
		<dd><input type="text" size="40" id="rifcs-idtype-input" name="rifcs-id-for-input" value="" /></dd>
	      </dl>
	      <i>(autocomplete; begin typing something (e.g. "ABN"), or press the down arrow to browse selections)</i>
	    </fieldset>
	    <a name="narrow-collection"></a>
	    <fieldset>
	      <legend>Vocab widget: Collection mode <button type="link" class="note-toggle">?</button></legend>
	      <small>(specific Record-to-Record Relation Types (new in v1.5))</small>
	      <div class="note">
		This form section is using the widget in the 'collection' helper mode (similar to "narrow" mode, but for weaker membership relationships (skos:collection))
		<pre>
$("#rifcs-relationtype").vocab_widget({
    mode:"collection",
    mode_params:"http://purl.org/au-research/vocabulary/RIFCS/1.5/RIFCSPartyToPartyRelationType,
    repository:"rifcs15",
    cache: false,
    fields: ['label'],
    target_field: 'label'});
		</pre>
	      </div>
	      <dl>
		<dt>RIFCS Relation Type:</dt>
		<dd>
			Between <select id="rifcs-relation-from"><option>Activity</option><option selected>Collection</option><option>Party</option><option>Service</option></select>
			and <select id="rifcs-relation-to"><option>Activity</option><option selected>Collection</option><option>Party</option><option>Service</option></select>		
		<dd><select id="rifcs-relationtype" name="rifcs-relation-dropdown" value=""></select></dd>
	      </dl>
	    </fieldset>


	    <a name="tree"></a>
	    <fieldset>
	      <legend>Vocab widget: tree mode  <button type="link" class="note-toggle">?</button></legend>
	      <div class="note">
		This section illustrates the use of the vocab widget as a tree browse list. Clicking a listed term toggles the state of that branch, fetching child data as required. A 'treeselect.vocab.ands' even gets fired when a list item (but not it's icon) is clicked.
		<pre>
$("#vocab-tree").vocab_widget({mode:'tree',
			       repository:'anzsrc-for'})
    .on('treeselect.vocab.ands', function(event) {
	var target = $(event.target);
	var data = target.data('vocab');
	alert('You clicked ' + data.label + '\r\n<' + data.about + '>');
    });
</pre>
	      </div>
	      <div id="vocab-tree">
	      </div>
	    </fieldset>
	    <a name="core"></a>
	    <fieldset>
	      <legend>Vocab widget: core use  <button type="link" class="note-toggle">?</button></legend>
	      <div class="note">
		This form section is using the widget with no helpers; it outputs a list of known <code>rifcs</code> identifier types:
		<pre>
var elem = $("#vocab-core");
var widget = elem.vocab_widget({repository:'rifcs', cache: false});
//set up some handlers
elem.on('narrow.vocab.ands', function(event, data) {
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
elem.on('error.vocab.ands', function(event, xhr) {
    elem.addClass('error')
	.empty()
	.text('There was an error retrieving vocab data: ' + xhr);
});

//now, perform the vocab lookup
widget.vocab_widget('narrow',
		    'http://purl.org/au-research/vocabulary/RIFCS/1.4/RIFCSIdentifierType');
		</pre>
	      </div>
	      <div id="vocab-core">
		<p>RIFCS Identifier types:</p>
	      </div>
	    </fieldset>
            <p><input type="submit"/></p>
          </form>
        </div>
      </div>
    </div>
<?php $this->load->view('footer');?>