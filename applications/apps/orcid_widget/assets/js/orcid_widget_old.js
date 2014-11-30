/*
  Copyright 2009 The Australian National University
  Licensed under the Apache License, Version 2.0 (the "License");
  you may not use this file except in compliance with the License.
  You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

  Unless required by applicable law or agreed to in writing, software
  distributed under the License is distributed on an "AS IS" BASIS,
  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
  See the License for the specific language governing permissions and
  limitations under the License.
*******************************************************************************/

;(function($) {
    var WIDGET_NAME = "ANDS Orcid service";
    var WIDGET_ID = "_orcid_widget_list";
    var WIDGET_DATA = "orcid_data";


    $.fn.orcid_widget = function(options, param) {

	param = typeof(param) === 'undefined' ? false : param;

	var defaults = {
	    //location (absolute URL) of the jsonp proxy
	    endpoint: 'http://pub.orcid.org/search/orcid-bio?q=',
	    //UI helper mode. currently, 'search' and 'narrow', and 'tree' are available
	    mode: "search",
	    //at most, how many results should be returned?
	    max_results: 100,

	    //should we cache results? yes by default
	   // cache: true,

	    //search mode: what to show when no hits? set to boolean(false) to supress
	    nohits_msg: '<p>No matches found<br/>If you wish to register for an orcid please click <a href="https://orcid.org/register" target="_blank" style="float:none;padding:0px">here</a></p>',

	    //what to show when there's some weird error? set to boolean(false)
	    //to supress
	    error_msg: WIDGET_NAME + " error.",

	    //provide CSS 'class' references. Separate multiple classes by spaces
	    list_class: "orcid_list",


	    //what data field should be stored upon selection?
	    target_field: "['orcid-profile']['orcid']",

	    //display count or not
	    display_count: true
	};

	// Default changes if we're running within the ANDS environments
	if (typeof(window.real_base_url) !== 'undefined')
	{
		defaults['endpoint'] = window.real_base_url + 'apps/orcid_widget/proxy/';
	} 

	var settings;
	var handler;

	if (typeof(options) !== 'string') {
	    settings = $.extend({}, defaults, options);
	    //do some quick and nasty fixes
	    settings.list_class = typeof(settings.list_class) === 'undefined' ?
		"" :
		settings.list_class;

	    settings._wname = WIDGET_NAME;
	    settings._wid = WIDGET_ID;
	    try {
		return this.each(function() {
		    var $this = $(this);
		    handler = new SearchHandler($this, settings);
		    if (typeof(handler) !== 'undefined') {;
			$this.data('_handler', handler);
			handler.ready();
		//	console.log(handler)
		    }
		    else {
			_alert('Handler not initialised');
		    }

		});
	    }
	    catch (err) {
		throw err;
		_alert(err);
	    }

	}
	
	function _alert(msg)
	{
	    alert(WIDGET_NAME + ': \r\n' + msg + '\r\n(reload the page before retrying)');
	}


	/**
	 * if we're here, an error has occurred; lose focus and unbind to avoid
	 * continuous errors
	 */
	try {
	    handler.detach();
	}
	catch (e) {}
	$(this).blur();
	return false;
    };

    /* Simple JavaScript Inheritance
     * By John Resig http://ejohn.org/
     * MIT Licensed.
     */
    // Inspired by base2 and Prototype
    (function() {
	var initializing = false;
	var fnTest = /xyz/.test(function(){xyz;}) ? /\b_super\b/ : /.*/;

	// The base Class implementation (does nothing)
	this.Class = function(){};

	// Create a new Class that inherits from this class
	Class.extend = function(prop) {
	    var _super = this.prototype;

	    // Instantiate a base class (but only create the instance,
	    // don't run the init constructor)
	    initializing = true;
	    var prototype = new this();
	    initializing = false;

	    // Copy the properties over onto the new prototype
	    for (var name in prop) {
		// Check if we're overwriting an existing function
		prototype[name] = typeof prop[name] == "function" &&
		    typeof _super[name] == "function" && fnTest.test(prop[name]) ?
		    (function(name, fn){
			return function() {
			    var tmp = this._super;

			    // Add a new ._super() method that is the same method
			    // but on the super-class
			    this._super = _super[name];

			    // The method only need to be bound temporarily, so we
			    // remove it when we're done executing
			    var ret = fn.apply(this, arguments);
			    this._super = tmp;

			    return ret;
			};
		    })(name, prop[name]) :
		prop[name];
	    }

	    // The dummy class constructor
	    function Class() {
		// All construction is actually done in the init method
		if ( !initializing && this.init )
		    this.init.apply(this, arguments);
	    }

	    // Populate our constructed prototype object
	    Class.prototype = prototype;

	    // Enforce the constructor to be what we expect
	    Class.prototype.constructor = Class;

	    // And make this class extendable
	    Class.extend = arguments.callee;

	    return Class;
	};
    })();

var OrcidHandler = Class.extend({

	init: function(input, settings) {

	    this._uid = this.makeUid();
	    this._input = input;
	    if(this._input.parent().is("span"))
	    {
	    	var afterPos = this._input.parent();
	    	var beforePos = this._input.parent();
		}else{
			var afterPos = this._input;
			var beforePos = this._input;
		}
	    this.settings = settings;
	    this._inputLabel = $("<span id='"+this._uid+"-url'>http://orcid.org/ </span>");
	    this._inputMessage = $("<span class='orcid_message' id='"+this._uid+"-input-message' style='padding-left:100px'>ORCID Identifier<span><br />")
	    this._inputLabel.insertBefore(beforePos);
	    this._inputMessage.insertBefore(this._inputLabel);
	    //console.log(this._input);
	    this._button = $('<a style="margin-left:1em;float:none;line-height:17px;" class="btn btn-small" data-toggle="modal" role="button">Search</i></a>');
	    this._lookupbutton = $('<a style="margin-left:1em;positon:relative;float:none;line-height:17px;" class="btn btn-small lookup-btn" data-toggle="modal" role="button">Lookup</a>');	    
	    this._button.attr('id', this._uid);
	    this._button.insertAfter(afterPos);
	    this._lookupbutton.insertAfter(afterPos);
	    //set up the id lookups we're going to use
	    this._uids = [];
	    this._uids['modalid'] = this._uid + '-modal';
	    this._uids['modalReturnId'] = this._uid + '-modal-return';	    
	    this._uids['labelid'] = this._uid + '-modal-label';
	    this._uids['txtid'] = this._uid + '-modal-input';
	    this._uids['formid'] = this._uid + '-modal-form';
	    this._uids['txtid'] = this._uid + '-modal-input';

	},

	/**
	 * Basic generator for a unique identifier
	 * @return a unique identifier for an element's 'id' attribute
	 */
	makeUid: function() {
	    var i = 1;
	    var uid = WIDGET_ID + i;
	    while (document.getElementById(WIDGET_ID + i) !== null) {
		i = i + 1;
		uid = WIDGET_ID + i;
	    }
	    return uid;
	},

	/**
	 * simplistic throwable template for input validation
	 */
	_throwing: function(val, rule, settings) {
	    throw "'" + val + "' must be " + rule +
		" (was: " + settings[val] + ")";
	},

	/**
	 * View helper; sets up the modal form, event handlers etc.
	 */
	makeModal: function() {
		//alert(Url.Content);
	    var modal = $('<div id="' + this._uids['modalid'] + '" class="modal hide fade orcid-modal" tabindex="-1" role="dialog" aria-hidden="true"></div>');
	    //header
	    modal.append('<div class="orcid-header">' +
			 '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button><div class="orcid-image"> </div>' +
			 '<h4 id="' + this._uids['labelid'] + '" class="orcid">Lookup</h4> ' + 
			 '<hr /></div>')
	    //body
		.append('<div class="modal-body">' +
			'<form id="' + this._uids['formid'] + '" class="form" accept-charset="UTF-8">' +
			'<div class="control-group">' +
			'<label class="control-label" for="' + this._uids['txtid'] + '">Researcher name</label>' +
			'<div class="controls input-append" style="display:block">' +
			'<input type="text" autofocus="autofocus" id="' + this._uids['txtid'] + '" class="search_input">' +
			'<button class="btn-primary search_orcid" type="submit" id="' + this._uids['txtid'] + '_button"><i class="icon-white icon-search"> </i> </button>' +
			'</div></div>' +
			'</form>' +
			'<div class="results"></div>' +
			'</div>')
	    modal.attr('aria-labelledby', this._uids['labelid']);
	    modal.attr('id', this._uids['modalid']);
	    return modal;
	},
	makeReturnModal: function() {
	    var returnDiv = $('<div id="' + this._uids['modalReturnId'] + '"  tabindex="-1" role="dialog" aria-hidden="true"></div>');
	    //header
	 //   modal.append('<div class="modal-header">' +
		//	 '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
		//	 '<h4 id="' + this._uids['labelid'] + '">Search Orcid</h4>' +
		//	 '</div>')
	    //body
		returnDiv.append('<div>' +
			'<div class="return" > </div>' +
			'</div>')
	    returnDiv.attr('aria-labelledby', this._uids['labelid']);
	    returnDiv.attr('id', this._uids['modalReturnId']);
	    return returnDiv;
	},

	preconditions: function() {
	    return [];
	},

	/**
	 * Validates the handler for operation: reads in
	 * this.preconditions() and iterates over the rules to process
	 * @param the js object that holds the `fields` defined in
	 * this.preconditions(). If not provided, `this.settings` is used.
	 * @return bool, true or false depending on the outcome of
	 * validating preconditions.
	 */
	validate: function(settings) {
	    var options = typeof(settings) === 'undefined' ? this.settings : settings;

	    var is_valid = true;
	    var handler = this;
	    $.each(this.preconditions(), function(ridx, rule) {
		$.each(rule.fields, function(fidx, field) {
		    try {
			is_valid = is_valid && rule.test(options[field]);
		    }
		    catch (e) {
			is_valid = false;
		    }
		    if (!is_valid) {
			handler._throwing(field, rule.description, options);
			return false;
		    }
		});
		if (!is_valid) {
		    return false;
		}
	    });
	    return is_valid;
	},


	/**
	 * simplistic throwable template for input validation
	 */
	_throwing: function(val, rule, settings) {
	    throw "'" + val + "' must be " + rule + " (was: " + settings[val] + ")";
	},


	/**
	 * basic error handler
	 */
	_err: function(xhr) {
	    if (typeof(this.settings['error_msg']) === 'boolean' &&
		this.settings['error_msg'] === false) {
	    }
	    else {
		var cid = this._container.attr('id');
		var footer;
		if (typeof(cid) === 'undefined') {
		    footer = "[Bound element has no id attribute; " +
			"If you add one, I'll report it here.]";
		}
		else {
		    footer = '(id: ' + cid + ')';
		}
		alert(this.settings['error_msg'] + "\r\n"
		      + xhr.responseText +
		      "\r\n" + footer);
	    }
	    this._container.blur();
	    return false;
	},

	__url: function(mode, lookfor) {
		    var url =  this.settings.endpoint;
	    return url;
	},

	destroy: function(){

		alert("we need to detach the widget!!")
	}

    });

    var UIHandler = OrcidHandler.extend({



	
	/**
	 * reset the list
	 */
	_reset: function() {
	    if (!this._list.data('persist')) {
		this._list.empty();
	    }
	    this._list.hide();
	},

    });


    var NarrowHandler = UIHandler.extend({
	preconditions: function() {
	    var preconds = this._super();
	    preconds.push({
		fields: ["mode_params"],
		description: "mode-specific parameters",
		test: function(val) { return (typeof(val) !== 'undefined'); }
	    });
	    preconds.push({
		fields: ["mode"],
		description: "mode 'narrow'",
		test: function(val) { return val === 'narrow'; }
	    });

	    return preconds;
	},


	__call: function(callee) {
	    if (typeof(callee) === 'undefined') {
		callee = this._container;
	    }
	    return this._narrow({uri:this.settings.mode_params,
				 callee:callee});
	},

	
	do_ready: function() {
	    var handler = this;
	     handler._container.on('search.orcid.ands',
				  function(event, xhr) {
				      handler._err(xhr);
				  });
	    handler._container.on('error.orcid.ands',
				  function(event, xhr) {
				      handler._err(xhr);
				  });
	   
	}, 

	/**
	 * silly wrapper to provide input buffering.
	 * `lookup` makes the ajax call
	 */
	orcid_lookup: function (event) {
	    //this._reset();
	    if (this._container.data('orcid_timer')) {
		clearTimeout(this._container.data('orcid_timer'));
	    }
	    var handler = this;
	    this._container.data('orcid_timer',
				 setTimeout(function() {handler.lookup()},
					    handler.settings.delay));
	},
	lookup: function() {
	    var handler = this;
	    var lookfor = this._container.val().toLowerCase();
	    var matches;
	    if (true || lookfor.length) {
		this._list.children('li').hide();
		this._list.show();
		matches = $.grep(this._list.children('li[role="orcid_item"]'),
				 function(e,i) {
				     var item = $(e);
				     var data = item.data(WIDGET_DATA);
				     for (var fi in handler.settings.fields) {
					 var field = handler.settings.fields[fi];
					 if ((typeof(data[field]) !== 'undefined') &&
					     data[field].substring(0,lookfor.length)
					     .toLowerCase() === lookfor) {
					     return true;
					 }
				     }
				     return false;
				 });
		$(matches).show();
	    }
	},
	
    });

 var SearchHandler = UIHandler.extend({
 
	ready: function() {

		$.ajaxSetup({
     		timeout: 20000
  		});

	    var handler = this;


	  	  this._modal = this.makeModal();
	  	  this._modal.insertAfter(this._button);

	   	 var modal = this._modal;
	    	this._button.on('click', function() {
			modal.modal();
	    	});

	    	this._modal.on('hidden', function() {
			modal.find('div.results').empty();
			$("#" + handler._uids['txtid']).val('');
		
	    	});
	    this._returnModal = handler.makeReturnModal();
	    this._returnModal.insertAfter(handler._button);
	    var returnModal = this._returnModal;
	   

	    var doSearch = function(e) {

		e.preventDefault();

		var searchVal = $("#" + handler._uids['txtid']).val();

		/* this will set up an exact match on the supplied words */
		/* need clarification on best use                        */

		var searchTerms = searchVal.split(" ");
		var searchStr = '';
		for(i=0;i<searchTerms.length-1;i++)
		{
			searchStr = searchStr + searchTerms[i] + "+AND+";
		}
		searchStr = searchStr + searchTerms[searchTerms.length-1];
		//alert("#" + this._uids['formid'] + "_button")
		var rdiv = $("#" + handler._uids['modalid'] + " div.results");
		if(searchStr!='')
		{
			var surl = handler.settings.endpoint;
			var theAddress = 'http://pub.orcid.org/search/orcid-bio?q='+searchStr+'&start=0&rows=100'+'&wt=json';
 			//var rdiv = $("#" + handler._uids['modalid'] + " div.results");
			rdiv.html('Loading results... (100 maximum)');
			var xhr = $.getJSON(surl,
				    {
					'address': theAddress
				    },
				    function(data) {
				    if(data!=null)
				    {
						if (typeof(data['orcid-search-results']) === 'undefined' ||
					   	 data['orcid-search-results'].length === 0) {
					    	rdiv.html('<div align="center">' +
						      	handler.settings.nohits_msg +'</div>');
						}
						else {
					    	rdiv.html(data['orcid-search-results']['orcid-search-result'].length + ' results (100 max.):');
					    	var list = $('<ul class="unstyled" style="text-align:left;"/>');
					    	$.each(data['orcid-search-results']['orcid-search-result'], function(i, e) {
					    		var orcid = e['orcid-profile']['orcid'].value;
								var givenNames = e['orcid-profile']['orcid-bio']['personal-details']['given-names'].value;
								var familyName = e['orcid-profile']['orcid-bio']['personal-details']['family-name'].value;		
								var lb = $('<a class="orcidsearch-btn btn btn-small btn-block"></a>');
								lb.data('orcid', e);
								var infoStr = '';
								var tooltip = toHtml(e['orcid-profile'],infoStr);
								if(tooltip.length>1200)
								tooltip = tooltip.substring(0,1200) + " ..."
								lb.append('<span class="class preview" title="'+tooltip+'" disname="'+givenNames+' '+familyName +'"> ' +givenNames+' '+familyName+ ' ' + ' ['+orcid+' ]</span>');
								var li = $('<li style="text-align:left"/>');
								li.append(lb);
								list.append(li);
					   	 	});
					    	rdiv.append(list);
					    	$('.preview').each(function(){       
   								$(this).qtip({
       							 content: {
            						text: $(this).attr('title'),
            					},
       							position: {
           						 	my: 'left center',
          						  	at: 'right center',
           						 	viewport: $(window)
        						},
        						show: {
            						event: 'mouseover',
        						},
        						hide: {
            						event: 'mouseout'
        						},
        						style: {
            						classes: 'ui-tooltip-light ui-tooltip-shadow orcidPreview',
            						width: 550
       							 }
    							}); 
    						});
						}
					}else{
						 rdiv.html('<div class="alert alert-small alert-error">' +
				  'Search service failed... try again?</div>');
					}
				 })
		    .done(function() {
			rdiv.find('a.orcidsearch-btn').on('click', function(e) {		
			    var record = $(e.currentTarget).data('orcid');
			   // console.log(record)
			    if (typeof(record[handler.settings.target_field]) === 'undefined') {
				handler._input.val(record['orcid-profile']['orcid'].value);
				handler._lookupbutton.trigger('click');
				handler._inputMessage.html('ORCID Identifier<br />')
				handler._inputMessage.attr('class','orcid_message')
				if(handler._input.hasClass('error')) handler._input.removeClass('error')
			    }
			    else {
				handler._input.val(record[handler.settings.target_field]);
			    }
			    modal.modal('hide');
			});
		    })
		    .fail(function(data) {
				rdiv.html('<div class="alert alert-small alert-error">' +
				  'Search service failed... try again?</div>');
		    });
		}else{
			rdiv.html('<div align="center">You must provide a search value.</div>');
		}
	    }

	    $(document).on('keydown', '.search_input', function(e){
	    	if (e.keyCode == 13) {
      			$('.search_orcid').trigger('click');
   			}
	    });

	 	$('.search_orcid').click(doSearch);	
	 	
		this._lookupbutton.on('click', function() {

		searchStr = handler._input.val();
		var surl = handler.settings.endpoint;
		var theAddress = 'http://pub.orcid.org/'+searchStr+'/orcid-bio';
 		var rdiv = $("#" + handler._uids['modalReturnId']);
		rdiv.html('Loading result...');
		var xhr = $.getJSON(surl,
			{
				'address': theAddress
			},
			function(data) {
				if(data!=null)
				{
					if (typeof(data['orcid-profile']) === 'undefined' ||
					   	 data['orcid-profile'].length === 0) {
					   	 	handler._inputMessage.html('Invalid ORCID Identifier<br />')
					   	 	handler._inputMessage.attr('class','error-message')
					   	 	handler._input.addClass('error')
					    	rdiv.html('<div align="center"></div>');
						}
						else {
					    	rdiv.html('<div align="center"></div>');
					    	var list = $('<ul class="unstyled" style="text-align:left;"/>');
					    	var obj = data['orcid-profile'];

					    	var resStr ='';
					    	var dataArray = toHtml(data['orcid-profile'],resStr);
							handler._inputMessage.html('ORCID Identifier<br />')
							handler._inputMessage.attr('class','orcid_message')
							handler._input.removeClass('error')
					    	rdiv.append(dataArray)
						}
					}else{
						 rdiv.html('<div class="alert alert-small alert-error">' +
				  'Search service failed... try again?</div>');
					}
				 })
		    .done(function() {
			rdiv.find('a.orcidsearch-btn').on('click', function(e) {		
			    var record = $(e.currentTarget).data('orcid');
			    if (typeof(record[handler.settings.target_field]) === 'undefined') {
				handler._input.val(record['orcid-profile']['orcid'].value);
			    }
			    else {
				handler._input.val(record[handler.settings.target_field]);
			    }
			    modal.modal('hide');
			});
		    })
		    .fail(function(data) {
				rdiv.html('<div class="alert alert-small alert-error">' +
				  'Search service failed... try again?</div>');
		    });
				returnModal.show();
	    });

	},

    });


function toHtml(obj,resStr) {
	resStr += "<div class='info-box'>"
	resStr += "<h6>ORCID Identifier</h6>";
	var orcid = eval(obj['orcid'])
	resStr += orcid.value;

	if(obj['orcid-bio']['biography'])
	{	
					    		
		if(obj['orcid-bio']['biography'].value!='')	
		{
			resStr += "<h6>Biography</h6>";
			var biography = obj['orcid-bio']['biography'].value		
			biography = biography.replace(/"/g,'&quot;');		
			resStr +="<p>"+biography+"</p>";
		}
	}
	    					    	
 	if(obj['orcid-bio']['personal-details'])
 	{
		resStr +="<h6>Personal Details</h6>"		 					    		
		resStr +="<p>";
 		if(obj['orcid-bio']['personal-details']['credit-name'])
 			resStr +="Credit name: "+obj['orcid-bio']['personal-details']['credit-name'].value+" <br />";
 		if(obj['orcid-bio']['personal-details']['family-name']) 					    
 			resStr +="Family name: "+obj['orcid-bio']['personal-details']['family-name'].value+" <br />"; 
 		if(obj['orcid-bio']['personal-details']['given-names'])  					    
  			resStr +="Given names: "+obj['orcid-bio']['personal-details']['given-names'].value+" <br />";
 		if(obj['orcid-bio']['personal-details']['other-names'])
 		{   					    		
   			resStr +="Other names: ";
   			var count = 0;	
   			for(i=0; i< (obj['orcid-bio']['personal-details']['other-names'].length -1);i++)
   			{
   				resStr += obj['orcid-bio']['personal-details']['other-names']['other-name'][i].value + ", ";
   				count++;
   			}
   				resStr += obj['orcid-bio']['personal-details']['other-names']['other-name'][count].value + "<br />";
   		} 	
 	}

 	if(obj['orcid-bio']['keywords'])
 	{ 					    		
   		resStr +="<h6>Keywords</h6>"
   		var count = 0;	
   		for(i=0; i< (obj['orcid-bio']['keywords']['keyword'].length -1);i++)
   		{
   			resStr += obj['orcid-bio']['keywords']['keyword'][i].value + ", ";
   			count++;
   		}
   		resStr += obj['orcid-bio']['keywords']['keyword'][count].value + "<br />"; 					    		
 	}

 	if(obj['orcid-bio']['researcher-urls'])
 	{ 					    		
   		resStr +="<h6>Research URLs</h6>"
   		var count = 0;	
   		for(i=0; i< (obj['orcid-bio']['researcher-urls']['researcher-url'].length -1);i++)
   		{
   			if(obj['orcid-bio']['researcher-urls']['researcher-url'][i].url.value!='')
   				resStr += "URL : " + obj['orcid-bio']['researcher-urls']['researcher-url'][i].url.value + "<br /> ";
   			if(obj['orcid-bio']['researcher-urls']['researcher-url'][i]['url-name'].value)
   				resStr += "URL Name : " + obj['orcid-bio']['researcher-urls']['researcher-url'][i]['url-name'].value + "<br /> ";   					    			
   			count++;
   		}
   		if(obj['orcid-bio']['researcher-urls']['researcher-url'][count].url.value!='')
   			resStr += "URL : " + obj['orcid-bio']['researcher-urls']['researcher-url'][count].url.value + "<br />";
   		if(obj['orcid-bio']['researcher-urls']['researcher-url'][count]['url-name'].value!='')
   			resStr += "URL Name : " + obj['orcid-bio']['researcher-urls']['researcher-url'][count]['url-name'].value + "<br />";   					    		
 	} 					    					    					    						    	
	resStr += "</div>"
    return resStr;
}
/* Load the defined orcid widgets */

    $('.orcid_widget').each(function(){
	   	var elem = $(this);
		var widget = elem.orcid_widget();

    })

  /*      $('.orcid_lookup').each(function(){
	   	var elem = $(this);
		var widget = elem.orcid_widget({ mode: 'lookup' });

    }) */

   

})( jQuery );
