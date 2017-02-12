/**
 * Core Data Source Javascript
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @package ands/registry_object
 * 
 */
var ds_id;
var fields = {};
var filters;
var sorts = {};
$(function(){

	/*
	 * suffix is determined in footer.php
	 * Example: #!/browse/lists/
	 * 			#!/view/115
	 *			#!/edit/115
	 *			#!/delete/115
	 */
	ds_id = $('#ds_id').val();
	if(ds_id!=0) fields.data_source_id = ds_id;//0 is for all data sources

	$(window).hashchange(function(){
		var hash = location.hash;
		if(hash.indexOf(suffix)==0){//if the hash starts with a particular suffix
			var words = hash.substring(suffix.length, hash.length).split('/');
			var action = words[0];//action will be the first word found
			try{
				$('section').hide();
				switch(action){
					case 'browse' : browse(words[1], words[2]);break;
					case 'view': load_ro(words[1], 'view');break;
					case 'preview': load_ro(words[1], 'preview');break;
					case 'edit': load_ro(words[1], 'edit', words[2]);break;
					case 'delete': load_ro_delete(words[1]);break;
					default: logErrorOnScreen('Invalid Operation: '+hash);break;
				}
			}catch(error){
				var template = $('#error-template').html();
				var output = Mustache.render(template, error);
				$('#main-content').append(output);
				$('section').hide();
			}
		}else{//there is no hash suffix
			browse('lists');
		}
	});
	$(window).hashchange();//initial hashchange event

	//event on item level
	$('.item').die().live({
		mouseenter: function(e){
			$('.btn-group', this).show();
		},
		mouseleave: function(e){
			$('.btn-group', this).hide();
		},
		dblclick: function(e){
			e.preventDefault();
			changeHashTo('view/'+$(this).attr('ro_id'));
		},
		click: function(){
			$(this).toggleClass('selected');
			updateSelected();
		}
	});

	//switch view button
	var currentView = 'thumbnails';
	$('#switch_view a').click(function(){
		changeHashTo('browse/'+$(this).attr('name'));
		currentView = $(this).attr('name');
	});

	//select all button
	$('#select_all').click(function(){
		if($(this).attr('name')=='select_all'){
			$(this).attr('name', 'deselect_all');
			$(this).text('Deselect all');
			$('.item').addClass('selected');
		}else{
			$(this).attr('name', 'select_all');
			$(this).text('Select All');
			$('.item').removeClass('selected');
		}
		updateSelected();
	});

	//toggling the filter chooser button
	$('.toggleFilter').die().live({
		click: function(){
			$('#filter_container').slideToggle();
		}
	});

	//load more button, this will init the get next 
	$('#load_more').click(function(){
		var page = parseInt($(this).attr('page'));
		page++;
		getRecords(fields, sorts, page);
		$(this).attr('page', page++);
	});

	//Search Form
	$('#search-records').submit(function(e){
		e.preventDefault();
		var query = $('input', this).val();
		var name = $('input', this).attr('name');
		if(query!==""){
			fields[name] = query;
		}else{
			delete fields[name];
		}
		clearItems();
		getRecords(fields);
	});

	//individual filters, construct the global fields[]
	$('.filter').die().live({
		click: function(e){
			e.preventDefault();
			var filtername = $(this).attr('name');
			var filtervalue = $(this).attr('value');
			fields[filtername] = filtervalue;
			filters = constructFilters(fields);
			changeHashTo('browse/'+currentView+'/'+filters);
		}
	});

	//destroying the fields[key] and do a browse based on currentView and current Filters
	$('.remove_filter').die().live({
		click: function(e){
			e.preventDefault();
			var field = $(this).attr('name');
			delete fields[field];
			filters = constructFilters(fields);
			changeHashTo('browse/'+currentView+'/'+filters);
		}
	});

	//sorting
	$('.sort').die().live({
		click: function(){
			var field = $(this).attr('name');
			var span = $(this).next('span');
			var direction = $(this).attr('direction');

			$('.sort').attr('direction', '');
			$('.sort').next('span').removeClass();
			
			if(direction==''){
				direction = 'asc';
				$(this).attr('direction', 'asc');
				$(span).removeClass().addClass('icon-chevron-up');
			}else{//there is already a direction
				if(direction=='asc'){
					direction = 'desc';
					$(this).attr('direction', 'desc');
					$(span).removeClass().addClass('icon-chevron-down');
				}else{
					direction = 'asc';
					$(this).attr('direction', 'asc');
					$(span).removeClass().addClass('icon-chevron-up');
				}
			}
			sorts = field + ' '+ direction;
			clearItems();
			getRecords(fields, sorts, 1);
		}
	});

	//bind the drag and drop
	$('#items')
		.drag("start",function( ev, dd ){
			return $('<div class="selection" />')
				.css('opacity', .65 )
				.appendTo( document.body );
		})
		.drag(function( ev, dd ){
			$( dd.proxy ).css({
				top: Math.min( ev.pageY, dd.startY ),
				left: Math.min( ev.pageX, dd.startX ),
				height: Math.abs( ev.pageY - dd.startY ),
				width: Math.abs( ev.pageX - dd.startX )
			});
		})
		.drag("end",function( ev, dd ){
			$( dd.proxy ).remove();
		});
	
	
	//bind viewing screen
	$('.tab-view-list li a').die().live({
		click:function(e){
			e.preventDefault();
			var view = $(this).attr('name');
			var id = $(this).attr('ro_id');
			changeHashTo(view+'/'+id);
		}
	});
	
});

function browse(view, filter){
	clearItems();
	if(view=='thumbnails' || view=='lists'){
		$('section').hide();
		$('#items').removeClass();
		$('#items').addClass(view);
		$('#browse-ro').fadeIn();
		if(filter){
			filter = filter.split('&');
			$.each(filter, function(){
				if(this!=''){
					var words = this.split('=');
					var filtername = words[0];
					var filtervalue = words[1];
					fields[filtername] = filtervalue;
				}
			});
		}else{
			clearFilters(fields);
		}
		getRecords(fields);
	}else{
		logErrorOnScreen('invalid View Argument');
	}
}

/*
 * Initialize the view
 * This opens the view for viewing ro, editing ro and possibly deleting ro
 * @TODO: view for deleting Registry Objects
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [int] registry object id, [string] view mode, [string|optional] tab to be init on
 * @returns: [void]
 */

function load_ro(ro_id, view, active_tab){
	$('#view-ro').html('Loading...');
	$.ajax({
		type: 'GET',
		url: base_url+'registry_object/get_record/'+ro_id,
		dataType: 'json',
		success: function(data){
			//console.log(data);
			var itemsTemplate = $('#item-template').html();
			var output = Mustache.render(itemsTemplate, data);
			$('#view-ro').html(output);


			//tab binding
			$('#view-ro .tab-content').hide();
			$('#view-ro .tab-view-list a').removeClass('active');
			$('#view-ro .tab-view-list a[name='+view+']').addClass('active');
			$('#view-ro .tab-content[name='+view+']').fadeIn();

			if(view=='view'){
				//magic?
				$('#view-ro .html-view').html(data.ro.view);

				var revisions = '';
				$.each(data.ro.revisions, function(time, id){
					//display the revisions
					revisions += '<li>'+time+'</li>';
				});
				$('#view-ro #ro-revisions').html(revisions);

				//equal heights?
				var highest=0;
				$('#view-ro').show();
				$('#view-ro .eqheight').each(function(){
					if($(this).height() > highest) highest = $(this).height();
				});
				$('#view-ro .eqheight .box').each(function(){
					$(this).height(highest);
				});
			}else if(view=='edit'){
				//set the active tab
				//console.log(data.ro.xml);
				/*var content = $('#view-ro .tab-content[name=edit]');
				var xmlDoc = $.parseXML(data.ro.xml);
				console.log($(xmlDoc).find('*').length);
				console.log($(xmlDoc).length);
				$(xmlDoc).children('key').each(function(){
 					console.log($(this).text());
				});*/
				$('#view-ro .tab-content[name=edit]').html('Loading...');
				$.ajax({
					type: 'GET',
					url: base_url+'registry_object/get_edit_form/'+ro_id,
					success:function(data){
						$('#view-ro .tab-content[name=edit]').html(data);
						if(active_tab && $('#'+active_tab).length > 0){//if an active tab is specified and exists
							$('.nav-tabs li a[href=#'+active_tab+']').click();
						}
						initEditForm();
					}
				});
			}
			$('#view-ro').show();
		}
	});
}

/*
 * Initialize the edit form, ready to be use upon completion
 * @TODO: 
 * 
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [void]
 * @returns: [void]
 */

function initEditForm(){

	/*
	 * Toggle button
	 * toggle the plus and minus
	 * slidetoggle everything except div.aro_box_display
	 */
	$('#edit-form .toggle').die().live({
		click: function(e){
			e.preventDefault();
			$('i', this).toggleClass('icon-plus').toggleClass('icon-minus');
			var aro_box = $(this).parents('.aro_box');
			$(aro_box).children('*:not(.aro_box_display)').slideToggle();
		}
	});

	/*
	 * Prevents the form from submitting when hit any button
	 */
	$('#edit-form button').die().live({
		click: function(e){
			e.preventDefault();
		}
	});
	
	/*
	 * Enable typeahead on input of class.[something]
	 * Documentation of typeahead is on twitter bootstrap
	 * @TODO: 
	 		- write a service that takes in a vocab_type, vocab_class and vocab_scheme eg: RIFCSCollectionType
	 			-> returns a json array of results
	 *
	 */
	$('.input-large').typeahead({
		source: function(typeahead,query){
			$.ajax({
				type: 'GET',
				dataType : 'json',
				url: base_url+'services/registry/get_vocab/RIFCSCollectionType',
				success:function(data){
					return typeahead.process(data);
				}
			});
		},
		minLength:0
	});

	/*
	 * icon-chevron-down button that triggers the typeahead by focusing into the input
	 */
	$('.triggerTypeAhead').die().live({
		click: function(e){
			$(this).parent().children('input').focus()
		}
	});

	/*
	 * Generate the random key based on the services/registry/get_random_key dynamically on the server
	 * @TODO: make sure the key is unique accross system, returns error message if fails
	 */
	$('#generate_random_key').die().live({
		click:function(e){
			e.preventDefault();
			var input = $(this).prev('input');
			$.ajax({
				type: 'GET',
				url: base_url+'services/registry/get_random_key/',
				success:function(data){
					$(input).val(data.key);
				}
			});
		}
	});

	/*
	 * Replace the data source text input field with a chosen() select
	 * @TODO: ACL on which data source is accessible on services/registry/get_datasources_list
	 */
	var selected_data_source = $('#data_source_id_value').val();
	$.ajax({
		type: 'GET',
		dataType : 'json',
		url: base_url+'services/registry/get_datasources_list/',
		success:function(data){
			var data_sources = data.items;
			$('#data_sources_select').append('<option value="0"></option>');
			$.each(data.items, function(e){
				var id = this.id;
				var title = this.title;
				var selected = '';
				if(id==selected_data_source){
					selected='selected=selected';
				}
				$('#data_sources_select').append('<option value="'+id+'" '+selected+'>'+title+'</option>');
			});
			//284 is the default width for input-xlarge + padding
			$('#data_sources_select').width('284').chosen();
		}
	});

	

	/*
	 * Remove the parent element
	 * If a part is found, remove the part
	 * If no part is found, remove the box
	 * 
	 */
	$('.remove').die().live({
		click:function(){
			var target = $(this).parents('.aro_box_part')[0];
			if($(target).length==0) target = $(this).parents('.aro_box')[0];
			$(target).fadeOut(500, function(){
				$(target).remove();
			});
			//@TODO: check if it's inside a tooltip and perform reposition
		}
	});

	/*
	 * Add a new Element
	 * find a div.separate_line among the parents previous divs
	 * template is a div.template[type=] where type is defined in the @type attribute of the button itself
	 */
	$('.addNew').die().live({
		click:function(e){
			e.stopPropagation();
			e.preventDefault();
			var what = $(this).attr('type');
			var template = $('.template[type='+what+']')[0];
			var where = $(this).prevAll('.separate_line')[0];

			//FIND THE SEPARATE LINE!!!
			//@TODO: badly need an algorithm | refactor | or an easier way
			if(!where){//if there is no separate line found, go out 1 layer and find it
				where = $(this).parent().prevAll('.separate_line')[0];
				if(!where){
					where = $(this).parent().parent().prevAll('.separate_line')[0];
					if(!where){
						where = $(this).parent().parent().parent().prevAll('.separate_line')[0];
						if(!where){
							where= $(this).parent().parent().parent().parent().prevAll('.separate_line')[0];
						}
					}
				}
			}
			//found it, geez

			//add the DOM
			$(template).clone().removeClass('template').insertBefore(where).hide().slideDown();

			//@TODO: check if it's inside a tooltip and perform reposition


			/*
			 * Reason for this:
			 	- We don't want to init the editor onto hidden template element
			 	- We keep template element without the class editor
			 		And only add the class editor upon addition of the element
			 */
			if(what=='description' || what=='rights'){
				$('#descriptions_rights textarea').addClass('editor');
				initEditor();
			}

			//bind the tooltip parts UI in case of adding a new element with show Parts Elements
			bindPartsTooltip();
		}
	});

	

	//Export XML button for currentTab in pretty print and modal
	$('.export_xml').die().live({
		click: function(e){
			e.preventDefault();
			if(editor=='tinymce') tinyMCE.triggerSave();//so that we can get the tinymce textarea.value without using tinymce.getContents
			var currentTab = $(this).parents('.tab-pane');
			var xml = getRIFCSforTab(currentTab);
			$('#myModal .modal-body').html('<pre class="prettyprint linenums"><code class="language-xml">' + htmlEntities(formatXml(xml)) + '</code></pre>');
			//prettyPrint();
			$('#myModal').modal();
		}
	});

	//Export XML button for ALL TABS in pretty print and modal
	$('#master_export_xml').die().live({
		click: function(e){
			e.preventDefault();
			if(editor=='tinymce') tinyMCE.triggerSave();//so that we can get the tinymce textarea.value without using tinymce.getContents
			var allTabs = $('.tab-pane');
			var xml = '';

			$.each(allTabs, function(){
				xml += getRIFCSforTab(this);
			});
			$('#myModal .modal-header h3').html('<h3>Export RIFCS</h3>');
			$('#myModal .modal-body').html('<pre class="prettyprint linenums"><code class="language-xml">' + htmlEntities(formatXml(xml)) + '</code></pre>');
			$('#myModal .modal-footer').html('<button class="btn btn-primary">Download</button>');
			//prettyPrint();
			$('#myModal').modal();
		}
	});


	//Load external XML modal dialog
	$('#load_xml').die().live({
		click: function(e){
			e.preventDefault();
			$('#myModal .modal-header h3').html('<h3>Paste RIFCS Here</h3>');
			$('#myModal .modal-body').html('<textarea id="load_xml_rifcs"></textarea>');
			$('#myModal .modal-footer').html('<button id="load_edit_xml" class="btn btn-primary">Load</button>');
			$('#myModal').modal();
		}
	});

	//This button stays inside the Load xml modal dialog
	//This will post the input rifcs to the server and replace the current edit form with the response
	$('#load_edit_xml').die().live({
		click: function(e){
			var rifcs = $('textarea#load_xml_rifcs').val();
			var ro_id = $('#ro_id').val();
			console.log(ro_id);
			if(rifcs!=''){
				$('#view-ro .tab-content[name=edit]').html('Loading...');
				$.ajax({
					type: 'POST',
					data: {rifcs:rifcs},
					url: base_url+'registry_object/get_edit_form_custom/'+ro_id,
					success:function(data){
						$('#view-ro .tab-content[name=edit]').html(data);						
						initEditForm();
						$('#myModal').modal('hide');
					}
				});
			}
		}
	});

	//initalize the datepicker, format is optional
	$('#view-ro .tab-content[name=edit] input.datepicker').datepicker({
		format: 'yyyy-mm-dd'
	});
	//triggering the datepicker by focusing on it
	$('.triggerDatePicker').die().live({
		click: function(e){
			$(this).parent().children('input').focus();
		}
	});

	//Various calls to initialize different tabs
	/*
	 	@TODO: 
	 		- Related object resolving
			- Resolve subject with sissvoc
			- Resolve identifier (based on types)
			- short (1 line) for locations
			- short (1 line) for descriptions / rights
	 */
	initNames();
	initDescriptions();
	initRelatedInfos();
	bindPartsTooltip();
}


/*
 * Binds .showParts to display a qtip element
 * @TODO: #fix reposition issue when interacting with the DOM inside a tooltip (eg addNew, remove)
 * finds the next div.parts and use that as content
 * The content will be removed from the DOM and append to the body with the id of ui-tooltip-x
 * This target will be defined at the button level by the attribute aria-describedby
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [void]
 * @returns: [void]
 */

function bindPartsTooltip(){
	$('.showParts').each(function(){
		var parts = $(this).next('.parts')[0];
		if(parts){
			var button = this;
			$(this).qtip({
				content:{text:$(parts)},
				position:{
					my:'center left',
					at: 'center right'
				},
				show: {event: 'click'},
				hide: {event: 'unfocus'},
				events: {
					show: function(event, api) {
						//console.log(api.id, button);
					}
				},
				style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap ui-tooltip-large'}
			});
		}
	});
}

/*
 * Initialize the names tab (aro_box_display)
 * the heading takes values from name Parts
 * @TODO: write a service that takes in a list of name part & class => spits out the display_title
 * Currently this function gives the primary name, or the first name part
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [void]
 * @returns: [void]
 */

function initNames(){
	var names = $('#names .aro_box[type=name]');
	$.each(names, function(){
		var name = this;
		var display = $(name).children('.aro_box_display').find('h1');
		var type = $(name).children('.aro_box_display').find('input[name=type]').val();
		var parts = $(name).children('.aro_box_part');
		var display_name = '';
		var temp_name = '';
		$.each(parts, function(){
			var thisPart = [];
			var type = $(name).find('input[name=type]').val();
			var value = $(name).find('input[name=value]').val();
			//logic here
			temp_name = value;
			if(type=='primary'){
				display_name = value;
			}
		});
		if(display_name=='') display_name=temp_name;
		$(display).html(display_name);
	});

	$('#names input').die().live({
		blur:function(e){
			var thisName = $(this).parents('.aro_box[type=name]');
			initNames();
		}
	});
}


/*
 * Initialize the descriptions tab (aro_box_display)
 * only init the editor for now (@see:editor)
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [void]
 * @returns: [void]
 */

function initDescriptions(){
	initEditor();
}

/*
 * Initialize all related Info heading (aro_box_display)
 * the heading takes values from title > notes and then identifier
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [void]
 * @returns: [void]
 */

function initRelatedInfos(){
	var relatedInfos = $('#relatedinfos .aro_box[type=relatedInfo]');
	$.each(relatedInfos, function(){
		var ri = this;//ri is the current related info
		var display = $('.aro_box_display h1', ri);
		var todisplay = $('input[name=title]', ri).val();
		if(!todisplay){//if there is none, grab the notes
			todisplay = $('input[name=notes]', ri).val();
		}
		if(!todisplay){//if there is none, grab the identifier
			todisplay = $('input[name=identifier]', ri).val();
		}
		$(display).html(todisplay);
	});
}

/*
 * Initialize all the Editors on screen
 * Cater for multiple types of wysiwyg html editor
 * The editor value is set as tinymce and will be able to change dynamically
 * @see: registry_object/controllers/registry_object
 * @see: registry_object/views/registry_object_index
 * @see: engine/views/footer
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [void]
 * @returns: [void] > affecting all textarea.editor on screen
 */

function initEditor(){
	if(editor=='tinymce'){
		tinyMCE.init({
		    theme : "advanced",
		    mode : "specific_textareas",
		    editor_selector : "editor",
		    theme_advanced_toolbar_location : "top",
		    theme_advanced_buttons1 : "bold,italic,underline,separator,link,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,outdent,indent,separator,undo,redo,code",
		    theme_advanced_buttons2 : "",
		    theme_advanced_buttons3 : "",
		    height:"250px",
		    width:"600px"
		});
	}
}

/*
 * Minh's Black Magic
 * Getting the RIFCS fragment for the given tab
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [object] tab
 * @returns: [string] RIFCS fragment ready for validation
 */

function getRIFCSforTab(tab){
	var currentTab = $(tab);
	var boxes = $('.aro_box', currentTab);
	var xml = '';
	$.each(boxes, function(){
		var fragment ='';
		var fragment_type = '';


		/*
		 * Getting the fragment header
		 * In the form of <name> or <name type="sometype">
		 * The name => the "type" attribute of the box
		 * The type => the input[name=type] of the box display (heading)
		 */
		fragment +='<'+$(this).attr('type')+'';
		var valid_fragment_meta = ['type', 'dateFrom', 'dateTo', 'style', 'rightsURI'];//valid input type to be put as attributes
		var this_box = this;
		$.each(valid_fragment_meta, function(index, value){
			var fragment_meta = '';
			var input_field = $('input[name='+value+']',this_box);
			if($(input_field).length>0 && $(input_field).val()!=''){
				fragment_meta += ' '+value+'="'+$(input_field).val()+'"';
			}
			fragment +=fragment_meta;
		});
		fragment +='>';
		//finish fragment header

		//onto the body of the fragment
		var parts = $(this).children('.aro_box_part');
		if(parts.length > 0){//if there is a part, data is spread out in parts
			$.each(parts, function(){

				/*
				 * If there is a part
				 * Usually there will be a type attribute for these part
				 * Special cases are dealt with using else ifs
				 * Generic case have 2 outcome, a tag with type attribute and no type attribute
				 * If there is no type attribute for the part itself, it's an element <name>value</name> thing
				 */

				if($(this).attr('type')){//if type is there for this part
					//deal with the type
					var type = $(this).attr('type');
					if(type=='relation'){//special case for related object relation
						fragment += '<'+type+' type="'+$('input[name=type]',this).val()+'">';
						if($('input[name=description]', this).val()!=''){//if the relation has a description
							fragment += '<description>'+$('input[name=description]', this).val()+'</description>';
						}
						if($('input[name=url]', this).val()!=''){//if the relation has a url
							fragment += '<url>'+$('input[name=url]', this).val()+'</url>';
						}
						fragment += '</'+type+'>';
					}else if(type=='relatedInfo'){//special case for relatedInfo
						//identifier is required
						fragment += '<identifier type="'+$('input[name=identifier_type]', this).val()+'">'+$('input[name=identifier]', this).val()+'</identifier>';
						//title and notes are not required, but useful nonetheless
						if($('input[name=title]', this).val()!=''){
							fragment += '<title>'+$('input[name=title]', this).val()+'</title>';
						}
						if($('input[name=notes]', this).val()!=''){
							fragment += '<notes>'+$('input[name=notes]', this).val()+'</notes>';
						}
					}else if(type=='date'){
						var dates = $('.aro_box', this);//tooltip not init
						if($('button.showParts', this).attr('aria-describedby')){//tooltip has been init
							var dates = $('#'+$('button.showParts', this).attr('aria-describedby')+' .ui-tooltip-content .aro_box');
						}
						$.each(dates, function(){
							fragment += '<'+$(this).attr('type')+' type="'+$('input[name=type]', this).val()+'" >';
							fragment += $('input[name=value]', this).val();
							fragment +='</'+$(this).attr('type')+'>';
						});
					}else if(type=='rightsStatement' || type=='licence' || type=='accessRights' ){
						 	fragment += '<'+$(this).attr('type')+' rightsUri="'+$('input[name=rightsUri]', this).val()+'">'+$('input[name=value]', this).val()+'</'+$(this).attr('type')+'>';	
					}else if(type=='contributor'){
							var contributors = $('.aro_box', this);//tooltip not init
							if($('button.showParts', this).attr('aria-describedby')){//tooltip has been init
								var contributors = $('#'+$('button.showParts', this).attr('aria-describedby')+' .ui-tooltip-content .aro_box');
							}
							$.each(contributors, function(){
								fragment += '<'+$(this).attr('type')+' seq="'+$('input[name=seq]', this).val()+'" >';
								var contrib_name_part = $('.aro_box_part', this);
								$.each(contrib_name_part, function(){
									fragment += '<'+$(this).attr('type')+' type="'+$('input[name=type]', this).val()+'" >';
									fragment += $('input[name=value]', this).val();
									fragment +='</'+$(this).attr('type')+'>';
								});
								fragment +='</'+$(this).attr('type')+'>';
							});
					}else{//generic
						//check if there is an input[name="type"] in this box_part so that we can use as a type attribute
						var type = $('input[name=type]', this).val();
						if(type){
							fragment += '<'+$(this).attr('type')+' type="'+$('input[name=type]', this).val()+'">'+$('input[name=value]', this).val()+'</'+$(this).attr('type')+'>';	
						}else{
							var type = $(this).attr('type');
							fragment += '<'+type+'>'+$('input[name=value]', this).val()+'</'+type+'>';
						}
					}
				}else{//it's an element
					fragment += '<'+$('input', this).attr('name')+'>'+$('input', this).val()+'</'+$('input', this).attr('name')+'>';
				}
			});
		}else{//there is no part
			//check if there is any subbox content, this is a special case for LOCATION
			var sub_content = $(this).children('.aro_subbox');
			if(sub_content.length >0){
				//there are subcontent, for location
				$.each(sub_content, function(){
					var subbox_type = $(this).attr('type');
					var subbox_fragment ='';

					subbox_fragment +='<'+subbox_type+'>';
					var parts = $(this).children('.aro_box_part');
					if(parts.length>0){
						$.each(parts, function(){
							var this_fragment = '';
							this_fragment +='<'+$(this).attr('type')+' type="'+$('input[name=type]', this).val()+'">';//opening tag
							if($(this).attr('type')=='electronic'){
								this_fragment +='<value>'+$('input[name=value]',this).val()+'</value>';
								//deal with args here
								var args = $('.aro_box_part', this);
								if($('button.showParts', this).attr('aria-describedby')){//tooltip has been init
									var args = $('#'+$('button.showParts', this).attr('aria-describedby')+' .ui-tooltip-content .aro_box_part');
								}
								$.each(args, function(){
									this_fragment += '<'+$(this).attr('type')+' type="'+$('input[name=type]', this).val()+'" required="'+$('input[name=required]', this).val()+'" use="'+$('input[name=use]', this).val()+'">';
									this_fragment += $('input[name=value]', this).val();
									this_fragment +='</'+$(this).attr('type')+'>';
								});
							}else if($(this).attr('type')=='physical'){
								//deal with address parts here
								var address_parts = $('.aro_box_part', this);
								if($('button.showParts', this).attr('aria-describedby')){//tooltip has been init
									var address_parts = $('#'+$('button.showParts', this).attr('aria-describedby')+' .ui-tooltip-content .aro_box_part');
								}
								$.each(address_parts, function(){
									this_fragment += '<'+$(this).attr('type')+' type="'+$('input[name=type]', this).val()+'">';
									this_fragment += $('input[name=value]', this).val();
									this_fragment +='</'+$(this).attr('type')+'>';
								});
								
							}else{
								//duh, if the type of this fragment being neither physical nor electronic, we have a problem here
							}
							this_fragment +='</'+$(this).attr('type')+'>';//closing tag
							subbox_fragment+=this_fragment;
						});
					}else{
						//there is no parts, spatial?

					}
						
					subbox_fragment +='</'+subbox_type+'>';//closing tag
					fragment+=subbox_fragment;//add the sub box fragments to the main fragment
				});
				
			}else{//data is right at this level, grab it!
				//check if there's a text area
				if($('textarea', this).length>0){
					fragment += htmlEntities($('textarea', this).val());
				}else if($('input[name=value]', this).length>0){
					fragment += $('input[name=value]', this).val();//there's no textarea, just normal input
				}
			}
			
		}
		fragment +='</'+$(this).attr('type')+'>';

		//SCENARIO on Access Policies

		xml += fragment;
		
	});
	return xml;
}


function getRecords(fields, sorts, page){
	if(!page) page = 1;
	$.ajax({
		type: 'POST',
		url: base_url+'registry_object/get_records/',
		data: {fields:fields, sorts:sorts, page:page},
		dataType: 'json',
		success: function(data){
			console.log(data);
			var itemsTemplate = $('#items-template').html();
			var output = Mustache.render(itemsTemplate, data);
			$('#items').append(output);

			//deal with facets
			$.each(data.facets, function(facet, array){
				var facetDom = $('.facets[name='+facet+'] ul');
				var html = '';
				$.each(array, function(field, value){
					//console.log(field, value);
					html +='<li><a href="javascript:;" class="filter" name="'+facet+'" value="'+field+'">'+field+' ('+value+')'+'</a></li>';
				});
				$(facetDom).html(html);
			});

			//applied filters
			$('#applied_filters').html('');
			$.each(fields, function(field, value){
				//on dedicated div for applied filters
				var html;
				html = '<a href="javascript:;" class="tag remove_filter" name="'+field+'">'+value+'<i class="icon-remove"></i></a>';
				$('#applied_filters').append(html);

				//on facet view
				$('.filter[name='+field+']').removeClass('filter').addClass('remove_filter').append(' <i class="icon-remove"></i>');
			});

			//bind the drag and drop select
			$('#items .item')
				.drop("start",function(){
					//$( this ).addClass("active");
				})
				.drop(function( ev, dd ){
					$( this ).addClass("selected");
					updateSelected();
				})
				.drop("end",function(){
					$( this ).removeClass("active");
				});
			$.drop({ multi: true });

			//binds the button within the item
			$('.item .item-control button').die().live({
				click: function(e){
					e.preventDefault();
					if($(this).hasClass('view')){
						changeHashTo('view/'+$(this).attr('ro_id'));
					}else if($(this).hasClass('edit')){
						changeHashTo('edit/'+$(this).attr('ro_id'));
					}else if($(this).hasClass('delete')){
						changeHashTo('delete/'+$(this).attr('ro_id'));
					}
				}
			})

			updateSelected();
		}
	});
}

//clear every items on screen
function clearItems(){
	$('#items').html('');
}

//delete all current filters
function clearFilters(fields){
	$.each(fields, function(key, value){
		if(key!='data_source_id'){
			delete fields[value];
		}
	});
}

//helper function: returns the filter string
function constructFilters(fields){
	var inc = 0;
	var filters = '';
	$.each(fields, function(field, value){
		if(inc>0){
			filters+='&';
		}
		if(field!='data_source_id'){
			filters +=field+'='+value;
		}
		inc++;
	});
	return filters;
}


//unuse: update how many registry objects have been selected
function updateSelected(){
	var totalSelected = $('#items .selected').length;
	if(totalSelected > 0){
		//$('#items_info').slideDown();
		var message = '<b>'+totalSelected + '</b> registry objects has been selected';
		$('#items_info').html(message);
	}else{
		$('#items_info').slideUp();
	}
}