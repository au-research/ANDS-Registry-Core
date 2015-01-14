/*
  Copyright 2013 The Australian National University
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
    var WIDGET_NAME = "ANDS Registry Object search widget";
    var WIDGET_ID = "rosearch-";
    var WIDGET_DATA = "rosearch_data";

    $.fn.ro_search_widget = function(options, param) {
	param = typeof(param) === 'undefined' ? false : param;

	var defaults = {
	    //search a specific class (by default)
	    'class': "all",
	    //search a specific datasource (by default)
	    'datasource': "all",
	    //where is the search proxy?
	    'endpoint': "http://ands3.anu.edu.au/workareas/smcphill/ands-online-services/arms/src/registry/registry_object_search/",
	    /**
	     * what to show when there's some weird error? set to boolean(false)
	     * to supress
	     */
	    'error_msg': WIDGET_NAME + " error.",
	    /*
	     * what data field should be stored upon selection?
	     * can be any of the registry object attributes
	     * (id|title|key|class)
	     */
	    'target_field': "key",
	    /*
	     * if default values are supplied for 'class' and 'datasource',
	     * should we lock the corresponding form elements?
	     */
	    'lock_presets': false
	};

	// Default changes if we're running within the ANDS environments
	if (typeof(window.real_base_url) !== 'undefined')
	{
		defaults['endpoint'] = window.real_base_url + 'apps/registry_object_search/';
	}

	var settings;
	var handler;

	if (typeof(options) !== 'string') {
	    settings = $.extend({}, defaults, options);
	    settings._wname = WIDGET_NAME;
	    try {
		return this.each(function() {
		    var $this = $(this);
		    //let's see if we've been initialised before.
		    var wdata = $this.data(WIDGET_DATA);
		    if (typeof(wdata) === 'undefined') {
			wdata = {};
		    }
		    if (typeof(wdata.handler) === 'undefined') {
			wdata.handler = new SearchHandler($this, settings);
			$this.data(WIDGET_DATA, wdata);
		    }

		    handler = wdata.handler;

		    if (typeof(handler) !== 'undefined') {
			handler.ready();
		    }
		    else {
			_alert('Handler not initialised');
		    }

		    //from here on, the handler takes care of everything.
		});
	    }
	    catch (err) {
		throw err;
		_alert(err);
	    }
	}
	else
	{
	    //We've been passed a string argument; handle accordingly
	    return this.each(function() {
		var op = options;
		var $this = $(this);
		var wdata = $this.data(WIDGET_DATA);
		handler = wdata.handler;
		if (typeof(handler) === 'undefined') {
		    _alert('Plugin handler not found; ' +
			   'instantiate before using');
		}

		switch(op) {
		case 'reset':
		    handler.detach();
		    $this.removeData(WIDGET_DATA);
		    break;
		default:
		    if (typeof(defaults[op]) !== 'undefined')
		    {
			handler.settings[op] = param;
		    }
		    else
		    {
			_alert("invalid operation '" + op + "'");
		    }
		    break;
		}
	    });
	}

	function _alert(msg)
	{
	    alert(WIDGET_NAME + ': \r\n' + msg +
		  '\r\n(reload the page before retrying)');
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


    var SearchHandler = Class.extend({

	init: function(input, settings) {

	    this._uid = this.makeUid();
	    this._input = input;
	    this.settings = settings;
	    this._button = $('<a style="margin-left:1em" class="btn" data-toggle="modal" role="button"><i class="icon-search"> </i></a>');
	    this._button.attr('id', this._uid);
	    this._button.insertAfter(this._input);

	    //set up the id lookups we're going to use
	    this._uids = [];
	    this._uids['modalid'] = this._uid + '-modal';
	    this._uids['labelid'] = this._uid + '-modal-label';
	    this._uids['txtid'] = this._uid + '-modal-input';
	    this._uids['formid'] = this._uid + '-modal-form';
	    this._uids['classid'] = this._uid + '-modal-select-class';
	    this._uids['dsid'] = this._uid + '-modal-select-ds';
	    this._uids['pubid'] = this._uid + '-modal-only-published';
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
	    var modal = $('<div id="' + this._uids['modalid'] + '" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true"></div>');
	    //header
	    modal.append('<div class="modal-header">' +
			 '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
			 '<h4 id="' + this._uids['labelid'] + '">Search registry objects</h4>' +
			 '</div>')
	    //body
		.append('<div class="modal-body">' +
			'<form id="' + this._uids['formid'] + '" class="form-horizontal" accept-charset="UTF-8">' +
			'<div class="control-group">' +
			'<label class="control-label" for="' + this._uids['classid'] + '">Filter by class</label>' +
			'<div class="controls">' +
			'<select id="' + this._uids['classid'] + '"><option value="all"></option></select>' +
			'</div></div>' +
			'<div class="control-group">' +
			'<label class="control-label" for="' + this._uids['dsid'] + '">Filter by datasource</label>' +
			'<div class="controls">' +
			'<select id="' + this._uids['dsid'] + '"><option value="all"></option></select>' +
			'</div></div>' +
			'<div class="control-group">' +
			'<label class="control-label" for="' + this._uids['pubid'] + '">Only published objects?</label>' +
			'<div class="controls">' +
			'<input type="checkbox" id="' + this._uids['pubid'] + '" checked>' +
			'</div></div>' +
			'<div class="control-group">' +
			'<label class="control-label" for="' + this._uids['txtid'] + '">Search</label>' +
			'<div class="controls input-append" style="display:block">' +
			'<input type="text" autofocus="autofocus" id="' + this._uids['txtid'] + '" >' +
			'<button class="btn btn-primary" type="submit"><i class="icon-white icon-search"> </i> </button>' +
			'</div></div>' +
			'</form>' +
			'<div class="results"></div>' +
			'</div>')
	    modal.attr('aria-labelledby', this._uids['labelid']);
	    modal.attr('id', this._uids['modalid']);
	    return modal;
	},

	/**
	 * prep widget for user interaction
	 */
	ready: function() {
	    var handler = this;
	    this._modal = this.makeModal();
	    this._modal.insertAfter(this._button);
	    //let's populate the filter lists now
	    $.getJSON(this.settings.endpoint + 'types/',
		      function(types) {
			  $.each(types, function(idx, t) {
			      var opt = $('<option value="' + t.key + '">' + t.label + '</option>');
			      if (t.key.toString() === handler.settings['class'].toString()) {
				  opt.prop('selected', true);
			      }

			      $("#" + handler._uids['classid']).append(opt);
			  });
		      });

	    $.getJSON(this.settings.endpoint + 'sources/',
		      function(sources) {
			  $.each(sources, function(idx, s) {
			      var source = $('<option value="' + s.key + '">' + s.label + '</option>');
			      if (s.key.toString() === handler.settings['datasource'].toString()) {
				  source.prop('selected', true);
			      }
			      $("#" + handler._uids['dsid']).append(source);
			  });
		      });

	    $("#" + handler._uids['classid']).prop('disabled',
	    					   handler.settings['lock_presets'] && handler.settings['class'] !== 'all');

	    $("#" + handler._uids['dsid']).prop('disabled',
	    					handler.settings['lock_presets'] && handler.settings['datasource'] !== 'all');

	    var modal = this._modal;
	    this._button.on('click', function() {
		modal.modal();
	    });

	    this._modal.on('hidden', function() {
		modal.find('div.results').empty();
		$("#" + handler._uids['txtid']).val('');
		$("#" + handler._uids['classid']).val(handler.settings['class']);
		$("#" + handler._uids['dsid']).val(handler.settings['datasource']);
		$("#" + handler._uids['pubid']).prop('checked', true);
	    });

	    $("#" + this._uids['formid']).on('submit', function(e) {
		e.preventDefault();
		var theclass = $("#" + handler._uids['classid']).val();
		var theds = $("#" + handler._uids['dsid']).val();
		var dopub = $("#" + handler._uids['pubid']).prop('checked') ?
		    'yes' :
		    'no';
		var surl = handler.settings.endpoint +
		    'search/' + theclass + '/' + theds + '/';
		var rdiv = $("#" + handler._uids['modalid'] + " div.results");
		rdiv.html('Loading results... (100 maximum)');
		var xhr = $.getJSON(surl,
				    {
					'onlyPublished': dopub,
					'term': $("#" + handler._uids['txtid']).val()
				    },
				    function(data) {
					if (typeof(data.results) === 'undefined' ||
					    data.results.length === 0) {
					    rdiv.html('<div class="alert alert-small ' +
						      'alert-error">' +
						      'No matching records</div>');
					}
					else {
					    rdiv.html(data.results.length + ' results (100 max.):');
					    var list = $('<ul class="unstyled"/>');
					    $.each(data.results, function(i, result) {
						var lb = $('<a class="rosearch-btn btn btn-small btn-block" type="button">' + result.title + '</a>');
						lb.data('rosearch-details', result);
						lb.append('<span class="status" style="background-color:' + result.color + '">' + result.status + '</span>');
						lb.append('<span class="class">' + result['class'] + '</span>');
						var li = $('<li/>');
						li.append(lb);
						list.append(li);
					    });
					    rdiv.append(list);
					}
				    })
		    .done(function() {
			rdiv.find('a.rosearch-btn').on('click', function(e) {
			    var record = $(e.target).data('rosearch-details');
			    handler._input.trigger('selected.rosearch.ands', record);
			    if (typeof(record[handler.settings.target_field]) === 'undefined') {
				handler._input.val(record.key);
			    }
			    else {
				handler._input.val(record[handler.settings.target_field]);
			    }
			    handler._input.trigger('input');
			    modal.modal('hide');
			   	handler._input.trigger('input');
			});
		    })
		    .fail(function() {
			rdiv.html('<div class="alert alert-small alert-error">' +
				  'Search service failed... try again?</div>');
		    });
	    });
	},

	/**
	 * disable widget's user interaction
	 */
	detach: function() {
	    this._button.remove();
	    this._modal.remove();
	    return false;
	},

	/**
	 * basic error handler
	 */
	_err: function(xhr) {
	    if (typeof(this.settings['error_msg']) === 'boolean' &&
		this.settings['error_msg'] === false) {
	    }
	    else {
		var cid = this._input.attr('id');
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
	    this._input.blur();
	    return false;
	}
    });
})( jQuery );
