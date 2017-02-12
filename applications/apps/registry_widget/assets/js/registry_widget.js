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

/**
 * Registry plugin for search and display of registry objects
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
 ;(function($) {
	
	//settings
	var WIDGET_NAME = "ANDS Registry service";
	var WIDGET_ID = "_registry_widget_list";

	//init the registry_widget()
	$.fn.registry_widget = function(options, param) {

		//set params
		param = typeof(param) === 'undefined' ? false : param;

		var defaults = {
			//jsonp proxy endpoint
			proxy: 'http://researchdata.ands.org.au/apps/registry_widget/proxy/',

			//mode: [search, display_single, display_result]
			mode: 'search',

			search: true,
			advanced: false,
			auto_search: false,
			wrapper: '<div class="rowidget_wrapper"></div>',
			search_btn_text: 'Search',
			search_btn_class: 'rowidget_search btn btn-small btn-default',
			search_callback:false,

			lookup:false,
			auto_lookup:false,
			lookup_btn_text: 'Resolve',
			lookup_btn_class: 'rowidget_lookup btn btn-small btn-default',
			lookup_callback: false,

			result_template: '<ul class="rowidget_results">{{#docs}}<li><a href="javascript:;" data-key="{{key}}" data-slug="{{slug}}" data-id="{{id}}">{{list_title}}</a></li>{{/docs}}</ul>',
			single_template: '<div class="rowidget_single"><h4><a href="{{rda_link}}" target="_blank">{{title}}</a></h4><span class="text-muted">{{group}}</span><div class="description">{{description}}</div></div>',

			//return_type: [key, slug, title, id]
			return_type:'key',
		};

		//ANDS Environment
		if (typeof(window.real_base_url) !== 'undefined'){
			defaults['proxy'] = window.real_base_url + 'apps/registry_widget/proxy/';
		}


		//bind and merge the defaults with the given options
		var settings;
		var handler;
		if (typeof(options) !== 'string') {
			settings = $.extend({}, defaults, options);
			//do some quick and nasty fixes
			settings.list_class = typeof(settings.list_class) === 'undefined' ? "" :
			settings._wname = WIDGET_NAME;
			settings._wid = WIDGET_ID;
			try {
				//bind the plugin handler to this
				return this.each(function() {
					var $this = $(this);
					$this.wrap(settings.wrapper);
					$this.p = $this.parent();

					if($this.attr('data-mode')=='display_single' || $this.attr('data-mode')=='display_result'){
						settings.mode=$this.attr('data-mode');
					}
					
					if($this.is('input') && settings.mode=='search'){
						bind_search($this, settings);
					}else if(settings.mode=='display_single'){
						bind_display_single($this, settings);
					}else if(settings.mode=='display_result'){
						bind_display_result($this, settings);
					}else{
						// alert('mode failed');
					}
				});
			}
			catch (err) { throw err; alert(err); }
		}
	}

	function bind_search(obj, s){

		if(s.lookup){
			var lookup_btn = $('<button>').html(s.lookup_btn_text).addClass(s.lookup_btn_class);
			obj.p.append(lookup_btn);
			$(lookup_btn).on('click', function(e){
				e.preventDefault();e.stopPropagation();
				_lookup(obj.val(),obj,s);
			});
		}

		if(s.search){
			var search_btn = $('<button>').addClass(s.search_btn_class).html(s.search_btn_text);
			obj.p.append(search_btn);
			$(search_btn).on('click', function(e){
				e.preventDefault();e.stopPropagation();
				_search(obj,s);
			});
			if(s.auto_search && obj.val()!="") _search(obj,s);
			if(s.advanced){
				var advanced_btn = $('<button>').addClass('btn btn-link').html('Advanced');
				obj.p.append(advanced_btn);
			}
		}	
		
	}

	function _search(obj, s){
		// console.log(obj.val());
		var value = obj.val();
		$.ajax({
			url:s.proxy+'search?q='+obj.val()+'&callback=?',
			dataType:'jsonp',
			timeOut:5000,
			success: function(data){
				if(data.status==0){
					if(s.search_callback && (typeof s.search_callback === 'function')){
						//if there's a predefined handler, use it instead
						s.search_callback(data, obj, s);
					}else{
						var template = s.result_template;
						$('.rowidget_results', obj.p).remove();
						$('.ro_widget_single', obj.p).remove();
						obj.p.append(template.renderTpl(data.result));
						$('.rowidget_results li', obj.p).on('click', function(e){
							e.preventDefault();
							obj.val($('a', this).attr('data-'+s.return_type));
							_lookup(obj.val(), obj, s);
							$('.rowidget_results', obj.p).remove();
						});
					}
				}else{
					$('.rowidget_results', obj.p).remove();
					obj.p.append('<div class="rowidget_results">'+data.message+'</div>');
				}
			}
		})
	}

	function bind_display_single(obj, s){
		if(typeof(obj.attr('data-query'))!='undefined'){
			_lookup(obj.attr('data-query'), obj, s);
		}
	}

	function _lookup(query, obj,s){
		$.ajax({
			url:s.proxy+'lookup?q='+encodeURIComponent(query)+'&callback=?',
			dataType:'jsonp',
			timeOut:5000,
			success: function(data){
				if(data.status==0){
					if(s.lookup_callback && (typeof s.lookup_callback === 'function')){
						//if there's a predefined handler, use it instead
						s.lookup_callback(data, obj, s);
					}else{
						var template = s.single_template;
						$('.rowidget_single', obj.p).remove();
						obj.p.append(template.renderTpl(data.result));
						if(s.return_type){
							if(typeof (data.result[s.return_type])!='undefined') obj.val(data.result[s.return_type]);
						}
					}
				}else{
					// console.log(data);
				}
			}
		});
	}

	function bind_display_result(obj, s){
		if(typeof(obj.attr('data-query'))!='undefined'){
			$.ajax({
				url:s.proxy+'search?custom_q='+encodeURIComponent(obj.attr('data-query'))+'&callback=?',
				dataType:'jsonp',
				timeOut:5000,
				success: function(data){
					if(data.status==0){
						var template = s.result_template;
						$('.rowidget_single', obj.p).remove();
						obj.p.append(template.renderTpl(data.result));
						if(s.return_type){
							if(typeof (data.result[s.return_type])!='undefined') obj.val(data.result[s.return_type]);
						}
					}else{
						// console.log(data);
					}
				}
			});
		}
	}

	String.prototype.renderTpl = function() {
		var args = arguments;
		if (typeof(args[0]) == 'undefined') return this; 
		var values = args[0];

		template = this.replace(/{{#(.*?)}}([\s\S]*?){{\/\1}}/g, function(match, subTplName, subTplValue) {         
			if (typeof(values[subTplName]) != 'undefined' 
				&& values[subTplName] instanceof Array 
				&& values[subTplName].length > 0)
			{
				replacement = ''; 
				for (i=0; i<values[subTplName].length; i++)
				{
					var partial = subTplValue.renderTpl(values[subTplName][i]);
					if (partial != subTplValue)
					{
						replacement += partial;
					}
				}
				return (replacement != '' ? replacement : subTplValue);
	 
			}
			else
				return '';
		});
		return template.replace(/{{([\s\S]*?)}}/g, function(match, field_name) { 
			return typeof values[field_name] != 'undefined'
			 ? values[field_name]
			 : match
			;
		}); 
	};

	//catch all .registry_widget and apply registry_widget() with default settings on
	$('.registry_widget').each(function(){
		var elem = $(this);
		var widget = elem.registry_widget();
	});
})( jQuery );

