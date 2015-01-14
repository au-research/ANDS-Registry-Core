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
 * jQuery plugin for Grant Widget integration
 * @author Liz Woods <liz.woods@ands.org.au>
 */
;(function($) {


	//settings
    var WIDGET_NAME = "ANDS Grant service";

    //init the grant_widget()
    $.fn.grant_widget = function(options, param) {

        var WIDGET_ID = generateUUID()
    	//set params
		param = typeof(param) === 'undefined' ? false : param;

		var defaults = {
		    //location (absolute URL) of the jsonp proxy
		    search_endpoint: 'http://researchdata.ands.org.au/registry/services/api/getGrants/?',
		   	lookup_endpoint: 'http://researchdata.ands.org.au/registry/services/api/getGrants/?id=',

		    //auto _lookup once init
		    pre_lookup: false,

		    //Text Settings
		    search: true,
		    pre_open_search: false,
		    tooltip:false,
		    info_box_class:'info-box',
		    search_text: '<i class="icon-search"></i> Search',
		    search_class: 'grant_search btn btn-default btn-small',
		    lookup: true,
		    lookup_text: 'Look up',
		    lookup_class: 'grant_lookup btn btn-default btn-small',
		    before_html: '<span class="grant_before_html">Grant id</span>',
		    wrap_html: '<div class="grant_wrapper"></div>',
		    result_success_class: 'grant_success_div',
		    result_error_class: 'grant_error_div',
		    search_div_class: 'grant_search_div',
		    nohits_msg: '<p>No matches found.</p>',
		    query_text: '<span class="form_label">Search Query:</span>',

		    search_text_btn: 'Search',
		    close_search_text_btn: '[x]',

            //allow custom settings for the funders and search fields
            funder_lists: false,
            funders: '',
            search_fields: '{"search_fields":["title","person","principalInvestigator","institution","description","id"]}',

		    //custom hooks and handlers
		    lookup_error_handler: false,
		    lookup_success_handler: false,
		    post_lookup_success_handler: false,

		    //auto close the search box once a value is chosen
		    auto_close_search: true,


		};



        //ANDS Environment
        if (typeof(window.real_base_url) !== 'undefined'){
            defaults['search_endpoint'] = window.real_base_url + 'registry/services/api/getGrants/?';
            defaults['lookup_endpoint'] = window.real_base_url + 'registry/services/api/getGrants/?id=';
        }

        //bind and merge the defaults with the given options
		var settings;
		var handler;
		if (typeof(options) !== 'string') {
		    settings = $.extend({}, defaults, options);
		    //do some quick and nasty fixes
		    settings.list_class = typeof(settings.list_class) === 'undefined' ? "" :
			settings.list_class;
		    settings._wname = WIDGET_NAME;
		    settings._wid = WIDGET_ID;
		    try {
		    	//bind the plugin handler to this
		    	return this.each(function() {
				    var $this = $(this);
				    bind_grant_plugin($this, settings);
				});
		    }
		    catch (err) {  alert(err); }
		}
	}

	/**
	 * this function primarily will bind all the needed DOM onto the target input field
	 * @param  {object} obj      javascript object represent the input target field
	 * @param  {object} settings local settings of the jQuery plugin
	 * @return {void}            DOM modification
	 */
	function bind_grant_plugin(obj, settings){

		//set obj.p as the parent, this parent is used throughout the plugin as a reference point
		if(obj.parent().is('span.inputs_group')){
			//important for local ANDS scripts inputs_groups element
			var p = obj.closest('.controls');
			obj.p = p;
		}else{
			//not in ANDS inputs_group wrapper, wrap it with defined wrapping
			obj.wrap(settings.wrap_html);
			var p = obj.parent();
			obj.p = p;
		}
        obj[0].name = '#'+settings._wid;


		//init a lookup if settings told you to
		if(settings.pre_lookup || (obj.attr('data-autolookup')==='true')) _lookup(obj, settings);

		//use lookup
		if(settings.lookup){
			var lookup_btn = $('<button>').addClass(settings.lookup_class).html(settings.lookup_text);
			p.append(lookup_btn);
			$(lookup_btn).on('click', function(e){
				e.preventDefault();
				_lookup(obj, settings);
			});
		}

		//use settings
		if(settings.search){
             var funders_list = '';
			 if(settings.funder_lists)
             {
                funders_list = '<span class="form_label">Funding Orgs:</span> <select name="funders" class="funder"><option value="All">All</option>'
                var theList = jQuery.parseJSON((settings.funders));
                for(i=0;i<theList.funder_list.length;i++){
                    funders_list = funders_list + '<option value="'+theList.funder_list[i]+'">'+theList.funder_list[i]+'</option>';
                }
                funders_list = funders_list +'</select><br />'

             }
            if(settings.search_fields)
            {
                var field_list = '<span class="form_label">Fields:</span>';
                var fields = jQuery.parseJSON(settings.search_fields)
                for(i=0;i<fields['search_fields'].length;i++)
                {
                     var field_display =fields['search_fields'][i].replace(/([A-Z])/g, ' $1').replace(/^./, function(str){ return str.toUpperCase(); })
                    field_list += field_display+'<span class="space"> </span><input type="checkbox" name="'+fields['search_fields'][i]+'" value="'+fields['search_fields'][i]+'" checked class="search_fields'+settings._wid+'"/> &nbsp; &nbsp;';
                }
             }
            var return_spot = $('<a name="'+settings._wid+'" id="return'+settings._wid+'"></a>')
			var search_btn = $('<button>').addClass(settings.search_class).html(settings.search_text);
			var search_html = '<p>'+settings.query_text+' <span class="indent"><input type="text" class="grant_search_input"/></p><p>'+funders_list+'</p><p>'+field_list+' </span></p><p><a class="search_grant"><button>'+settings.search_text_btn+'</button></a><div class="grant_search_result"></div><a class="close_search">'+settings.close_search_text_btn+'</a></p>';
			var search_div = $('<div id="'+settings._wid+'">').addClass(settings.search_div_class).html(search_html);

            p.append(return_spot)
			p.append(search_btn).append(search_div);
            if(!settings.pre_open_search)  $('#'+settings._wid).hide()
			$(search_btn).on('click', function(e){
				e.preventDefault();
                $('.grant_success_div').hide()
				_search_form(obj, settings._wid);
			});
			$('input.grant_search_input', p).on('keypress', function(e){
				//enter key
				if(e.keyCode==13){
                    var searching_fields = _get_fields(fields,settings);
                    if($('select.funder').val())
                    {
                        var funder = $('select.funder').val()
                    }else{
                        var funder = 'All'
                    }

					_search($(this).val(), funder, searching_fields ,obj, settings); return false;
				}
			});
			$('.search_grant', p).on('click', function(e){
				//click grant search
 				e.preventDefault();
				e.stopPropagation();
				var query = $('.grant_search_input', p).val();
                var searching_fields = _get_fields(fields,settings);
                if($('select.funder').val())
                {

                    var funder = $('select.funder').val()
                }else{
                    var funder = 'All'
                }
				_search(query,funder,searching_fields, obj, settings);
			});
			$('.close_search', p).on('click', function(){
				//close button
				$('#'+settings._wid, p).slideUp();
			});

		}
		
		//before_html
		if(settings.before_html) obj.before(settings.before_html);

		$(obj).on('keypress', function(e){
			//enter key
			if(e.keyCode==13){
				_lookup(obj, settings); return false;
			}
		});
	}

	/**
	 * execute a look up functionality on the Grant jsonp service
	 * @param  {object} obj      javascript object represent the target input field
	 * @param  {object} settings local settings of the jQuery plugin
	 * @return {void}            this will modify the DOM based on the return value
	 */
	function _lookup(obj, settings){
        $('.'+settings.uid).slideUp()
		var value = obj.val().replace('http://','');
		$.ajax({
			url:settings.lookup_endpoint+encodeURIComponent(value)+'&callback=?',
			dataType: 'JSONP',
			timeout: 1000,
			success: function(data){
				if(settings.lookup_success_handler && (typeof settings.lookup_success_handler === 'function')){
					//if there's a predefined handler, use it instead
					settings.lookup_success_handler(data, obj, settings);
				}else{
					_clean(obj, settings);
					var html = _constructGrantHTML(data.message.recordData,settings);
					var result_div = $('<div>').addClass(settings.result_success_class).html(html);
					obj.p.append(result_div);
					if(settings.post_lookup_success_handler && (typeof settings.post_lookup_success_handler ==='function')){
						//if there's a hook defined, use it after success
						settings.post_lookup_success_handler(data,obj,settings);
					}
				}
			},
			error: function(xhr){
				if(settings.lookup_error_handler && (typeof settings.lookup_error_handler === 'function')){
					settings.lookup_error_handler(xhr);
				}else{
					_clean(obj, settings);
					var result_div = $('<div>').addClass(settings.result_error_class).html(settings.nohits_msg);
					obj.p.append(result_div);
					obj.addClass('error');
				}
			}
		});
	}

	/**
	 * construct a HTML string out from an Grant object for display
	 * @param  {grant['grant-profile']} obj grant-profile array of the returned object
	 * @return {string}     HTML string
	 */

	function _constructGrantHTML(obj,settings) {

		var resStr = '';
		resStr += "<div class='"+settings.info_box_class+"'>"
        if(obj.length==1)
        {
            resStr += "<h4>Grant Information</h4>";
            if(obj[0]['title'])
            {
                resStr += "<h6>Title</h6>";
                var title = $('<textarea />').html(obj[0]['title']).text();
                title = title.replace(/"/g,'&quot;');
                resStr +="<p>"+title+"</p>";
            }
            if(obj[0]['description'])
            {
                resStr += "<h6>Description</h6>";
                var description = $('<textarea />').html(obj[0]['description']).text();
                description = description.replace(/"/g,'&quot;');
                resStr +="<p>"+description+"</p>";
            }
            if(obj[0]['identifiers'])
            {
                var i;
                var identifier = '';

                for (i in obj[0]['identifier_type']) {
                    if (obj[0]['identifier_type'].hasOwnProperty(i)) {
                        identifier = obj[0]['identifier_type'][i]
                    }
                }

                for (j in obj[0]['identifier_type']) {
                    if (obj[0]['identifier_type'].hasOwnProperty(j)) {
                        if(j=='purl')
                        {
                           identifier = obj[0]['identifier_type'][j]
                        }
                    }
                }

                resStr += "<h6>Identifier</h6>";

                resStr +="<p>"+identifier+"</p>";
                obj[0]['identifier'] = identifier
            }
            if(obj[0]['relations'])
            {
                resStr += "<h6>Relationships</h6>";

                if(typeof(obj[0]['relations']['isFundedBy'])=='string')
                {
                    resStr +="<p>Is funded by</p>";
                    resStr +="<p>"+obj[0]['relations']['isFundedBy']+"</p>";
                }else if (typeof(obj[0]['relations']['isFundedBy'])=='object'){
                    resStr +="<p>Is funded by</p><p>";
                    for(i=0;i<obj[0]['relations']['isFundedBy'].length;i++)
                    {
                        resStr +=obj[0]['relations']['isFundedBy'][i]+", ";
                    }
                    resStr = resStr.replace(/(^\s*,)|(,\s*$)/g, '');
                    resStr +="</p>";
                }
                if(typeof(obj[0]['relations']['isManagedBy'])=='string')
                {
                    resStr +="<p>Is managed by</p>";
                    resStr +="<p>"+obj[0]['relations']['isManagedBy']+"</p>";
                }else if (typeof(obj[0]['relations']['isManagedBy'])=='object'){
                    resStr +="<p>Is managed by</p><p>";
                    for(i=0;i<obj[0]['relations']['isManagedBy'].length;i++)
                    {
                        resStr +=obj[0]['relations']['isManagedBy'][i]+", ";
                    }
                    resStr = resStr.replace(/(^\s*,)|(,\s*$)/g, '');
                    resStr +="</p>";
                }
                if(typeof(obj[0]['relations']['isParticipantIn'])=='string')
                {
                    resStr +="<p>Has participant</p>";
                    resStr +="<p>"+obj[0]['relations']['isParticipantIn']+"</p>";
                }else if(typeof(obj[0]['relations']['isParticipantIn'])=='object'){
                    resStr +="<p>Has participant</p><p>";
                    for(i=0;i<obj[0]['relations']['isParticipantIn'].length;i++)
                    {
                        resStr +=obj[0]['relations']['isParticipantIn'][i]+", ";
                    }
                    resStr = resStr.replace(/(^\s*,)|(,\s*$)/g, '');
                    resStr +="</p>";
                }
                if(typeof(obj[0]['relations']['isPrincipalInvestigatorOf'])=='string')
                {
                    resStr +="<p>Has principal investigator</p>";
                    resStr +="<p>"+obj[0]['relations']['isPrincipalInvestigatorOf']+"</p>";
                }else if (typeof(obj[0]['relations']['isPrincipalInvestigatorOf'])=='object'){
                    resStr +="<p>Has principal investigator</p><p>";
                    for(i=0;i<obj[0]['relations']['isPrincipalInvestigatorOf'].length;i++)
                    {
                        resStr +=obj[0]['relations']['isPrincipalInvestigatorOf'][i]+", ";
                    }
                    resStr = resStr.replace(/(^\s*,)|(,\s*$)/g, '');
                    resStr +="</p>";
                }
             }
        }
        else if(obj.length==0)
        {
            resStr += "<p>0 results returned. Please supply a valid grant id</p>"
        }else{
            resStr += "<p>Multiple results returned. Please supply an exact grant id</p>"
        }

		resStr += "</div>"
		return resStr;
	}

    /**
     * construct a string out from an Grant object to obtain identifier value
     * @param  {grant['grant-profile']} obj grant-profile array of the returned object
     * @return {string}    identifier value
     */
    function _getIdentifier(obj,settings) {
        if(obj.length==1)
        {
            if(obj[0]['identifiers'])
            {
                var i;
                var identifier = '';

                for (i in obj[0]['identifier_type']) {
                    if (obj[0]['identifier_type'].hasOwnProperty(i)) {
                        identifier = obj[0]['identifier_type'][i]
                    }
                }

                for (j in obj[0]['identifier_type']) {
                    if (obj[0]['identifier_type'].hasOwnProperty(j)) {
                        if(j=='purl')
                        {
                            identifier = obj[0]['identifier_type'][j]
                        }
                    }
                }

            }

        }
        return identifier;
    }

	/**
	 * isset equivalent for javascript
	 * @param  variable something to check
	 * @return {boolean}       
	 */
	function isset(variable){
		if(typeof(variable) != "undefined" && variable !== null) {
		    return true;
		}else return false;
	}

	/**
	 * init a search request on the ORCID jsonp service
	 * @param  {string} query    search query
	 * @param  {object} obj      javascript object represent the input target field
	 * @param  {object} settings local settings of the plugin
	 * @return {void}            this will modify the DOM based on the search result
	 */
	function _search(query, funder, fields, obj, settings){
 		var p = obj.p;
		var result_div = p.find('.'+settings.grant_search_result);
        var thefields = jQuery.parseJSON(fields)
        var funder_list = '';
        if(funder!='All')
        {
            funder_list = '&group='+funder;
        }
		if($.trim(query)==""){
			$('.grant_search_result', p).html('Please enter a search string');
		}else{
            var matches = '';
			$('.grant_search_result', p).html('Loading...');
            var html = '';
            for(i=0;i<thefields['search_fields'].length;i++)
            {       $.ajax({
				    url:settings.search_endpoint+thefields['search_fields'][i]+"="+encodeURIComponent(query)+funder_list+'&start=0&rows=10&callback=?',
                    indexValue: i,
				    dataType: 'JSONP',
				    success: function(data){
					if(settings.success_handler && (typeof settings.success_handler === 'function')){
						settings.success_handler(data, obj, settings);
					}else{
						if(data.message['numFound']>0){

                            matches += data.message['numFound']
                            html += '<a class="show_list" id="'+this.indexValue+settings._wid+'"> + </a>'+data.message['numFound']+" found in <i>"+ thefields['search_fields'][this.indexValue]+"</i><br />";
							html +='<div id="div_'+this.indexValue+settings._wid+'" class="listdiv" style="display:none"><ul>';

							$.each(data.message['recordData'], function(){
 								var titleStr = "";
                                var obj = new Array(this);
                                var identifier = _getIdentifier(obj,settings);
                                if(settings.tooltip) titleStr = 'title="'+_constructGrantHTML(obj,settings)+'"';

								html+='<li>';
								html+='<a class="select_grant_search_result preview" grant-id="'+identifier+'" '+titleStr+' >'+this.title+'</a>';
								html+='</li>';
							});
							html+='</ul></div>';
							$('.grant_search_result', p).html(html);
						}
					}
					$('.select_grant_search_result', p).on('click', function(){
						obj.val($(this).attr('grant-id'));
						_lookup(obj, settings);
						if(settings.auto_close_search) $('#'+settings._wid).slideUp();

                        $(document.body).scrollTop($('#return'+settings._wid).offset().top -100);

					});
					if(settings.tooltip){
						$('.preview').each(function(){       
   								$(this).qtip({
       							 content: {
            						text: $(this).attr('title')
            					},
       							position: {
           						 	my: 'left center',
          						  	at: 'right center',
           						 	viewport: $(window)
        						},
        						show: {
            						event: 'mouseover'
        						},
        						hide: {
            						event: 'mouseout'
        						},
        						style: {
            						classes: 'ui-tooltip-light ui-tooltip-shadow grantPreview',
            						width: 550
       							 }
    							}); 
    					});
					}
				},
				error: function(xhr){
					if(settings.error_handler && (typeof settings.error_handler === 'function')){
						settings.error_handler(xhr);
					}else{
						console.error(xhr.responseText);
					}
				}

			});
                if(matches == '0')
                {
                    $('.grant_search_result', p).html(settings.nohits_msg);
                }
            }
        }



	}

	//remove (if exist) error and result
	function _clean(obj, settings){
		obj.removeClass('error');
		if(obj.p.children('.'+settings.result_error_class).length>0){
			obj.p.children('.'+settings.result_error_class).remove();
		}
		if(obj.p.children('.'+settings.result_success_class).length>0){
			obj.p.children('.'+settings.result_success_class).remove();
		}
	}

	//open the search form
	function _search_form(obj, wid){
		$('#'+wid).slideToggle();
	}

    function _get_fields(searching_fields,settings)
    {
        var fields ='';
        $.each($('.search_fields'+settings._wid),function()
        {
            if($(this).prop('checked'))
            {
                if(fields==''){
                    fields += '{"search_fields":["'+$(this).attr('name')+'"'
                }else{
                    fields += ', "'+$(this).attr('name')+'"'
                }
            }
        })
        if(fields=='')
        {
            fields= '{"search_fields":[';
            for(field in searching_fields.search_fields)
            {
                if(searching_fields.search_fields.hasOwnProperty(field))
                {
                    fields += '"'+searching_fields.search_fields[field]+'",'
                }
            }
        }
        fields = fields.replace(/(^,)|(,$)/g, "")
        fields += ']}'
        return fields;
    }

    function generateUUID(){
        var d = new Date().getTime();
        var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = (d + Math.random()*16)%16 | 0;
            d = Math.floor(d/16);
            return (c=='x' ? r : (r&0x7|0x8)).toString(16);
        });
        return uuid;
    };

    function scrollTo(hash) {
        location.hash = "#" + hash;
    }
	//catch all .grant_widget and apply grant_widget() with default settings on

	$('.grant_widget').each(function(){
	   	var elem = $(this);
	   	var widget = elem.grant_widget();
    });

    $(document).on("click", ".show_list", function(e) {
        var theUl = $(this).attr('id');
        if($('#div_'+theUl).css('display')=='none')
        {
            $('#'+theUl).html(' - ')
        }else{
            $('#'+theUl).html(' + ')
        }
        $('#div_'+theUl).slideToggle();

    });


})( jQuery );