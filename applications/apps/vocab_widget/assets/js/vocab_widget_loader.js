/* this handler is for the plugin */
$(document).ready(function() {
  function searchmode() {
    $("#anzsrc-vocab").vocab_widget({mode: 'search',
				     cache: false,
				     repository: 'anzsrc-for',
				     target_field: 'label'});
  }

  function narrowmode() {
    $("#rifcs-idtype").vocab_widget({mode:"narrow",
	       			     mode_params:"http://purl.org/au-research/vocabulary/RIFCS/1.4/RIFCSIdentifierType",
	       			     repository:"rifcs",
	       			     cache: false,
	       			     fields: ['definition'],
	       			     target_field: 'label'});


    $("#rifcs-idtype-input").vocab_widget({mode:"narrow",
					   mode_params:"http://purl.org/au-research/vocabulary/RIFCS/1.4/RIFCSIdentifierType",
					   repository:"rifcs",
					   cache: false,
					   fields: ['label', 'definition', 'about'],
					   target_field: 'label'});
  }

  function collectionmode()
  {
  	var collection_uri = "http://purl.org/au-research/vocabulary/RIFCS/1.5/RIFCS" +
  							$("#rifcs-relation-from").val() +
  							"To" +
  							$("#rifcs-relation-to").val() +
  							"RelationType";
  	$("#rifcs-relationtype").unbind();
  	$("#rifcs-relationtype").vocab_widget({
  	   mode:"collection",
	   mode_params:collection_uri,
	   repository:"rifcs15",
	   cache: false,
	   fields: ['label'],
	   target_field: 'label'});
  }
  // Update widget on change
   $("#rifcs-relation-from, #rifcs-relation-to").on('change', function()
   {
   	collectionmode();
   });


  function coremode() {
    var elem = $("#vocab-core");
    var widget = elem.vocab_widget({repository:'rifcs', cache: false});
    //set up some handlers
    elem.on('narrow.vocab.ands', function(event, data) {
      var list = elem.append('<ul />');
      $.each(data.items, function(idx, e) {
	var link = $('<a href="' + e['about'] + '">' +
		     e['label'] + '</a>');
	var item = $('<li />');
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
  }

  function treemode() {
    $("#vocab-tree").vocab_widget({mode:'tree',
				   repository:'anzsrc-for'})
      .on('treeselect.vocab.ands', function(event) {
	var target = $(event.target);
	var data = target.data('vocab');
	alert('You clicked ' + data.label + '\r\n<' + data.about + '>');
      });
  }

  searchmode();
  narrowmode();
  treemode();
  coremode();
  collectionmode();
  $(document).trigger('loaded.internal');

});

$(document).on('loaded.internal', function() {
  var hash = window.location.hash;
  if (typeof(hash) !== 'undefined') {
    $('a[name="' + hash + '"]').click();
  }
});

/* this handler is for the demonstration helpers */
$(document).ready(function() {
  $(".note-toggle").on('click',
		       function(e) {
			 e.preventDefault();
			 var target = $(e.target);
			 var parent = target.parent().parent();
			 var note = parent.find("div.note");
			 if (note.is(":visible")) {
			   note.slideUp();
			   target.text('?');
			 }
			 else {
			   note.slideDown();
			   target.text('X');
			 }
			 return false;
		       });
})
