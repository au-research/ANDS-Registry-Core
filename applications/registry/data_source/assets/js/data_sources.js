/**
 * Core Data Source Javascript
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */
var lastLogId = null;
var logTimer = 1000;
var doLoadLogs = false;
$(function(){

	/*
	 * suffix is determined in footer.php
	 * Example: #!/browse/lists/
	 * 			#!/view/115
	 *			#!/edit/115
	 *			#!/delete/115
	 */
	 
	$(window).hashchange(function(){
		logTimer = 1000;
		doLoadLogs = false;
		var hash = location.hash;
		hash = decodeURIComponent(hash);
		if(hash.indexOf(suffix)==0){//if the hash starts with a particular suffix
			var words = hash.substring(suffix.length, hash.length).split('/');
			var action = words[0];//action will be the first word found
			try{
				$('section').hide();
				switch(action){
					case 'browse' : browse(words[1]);break;
					case 'view': load_datasource(words[1]);break;
					case 'settings': load_datasource_settings(words[1]);break;
					case 'edit': load_datasource_edit(words[1], words[2]);break;
					case 'delete': load_datasource_delete(words[1]);break;
					default: logErrorOnScreen('this functionality is currently being worked on');break;
				}
				$('#data_source_view_container').attr('data_source_id', words[1]);
			}catch(error){
				var template = $('#error-template').html();
				var output = Mustache.render(template, error);
				$('#main-content').append(output);
				$('section').hide();
			}
		}else{//there is no hash suffix
			browse('lists');
			load_more(1);//init the load_more function | load the first page
		}
	});
	$(window).hashchange();//initial hashchange event

	//switch view button binding
	var currentView = 'thumbnails';
	$('#switch_view a').click(function(){
		changeHashTo('browse/'+$(this).attr('name'));
		currentView = $(this).attr('name');
	});

	//load_more button binding, once clicked will increment the page value
	$('#load_more').click(function(){
		var page = parseInt($(this).attr('page'));
		page++;
		load_more(page);
		$(this).attr('page', page++);
	});


	//item button binding
	$('.page-control').live({
		click: function(e){
			e.preventDefault();
			var data_source_id = $(this).attr('data_source_id');
			if($(this).hasClass('view')){
				changeHashTo('view/'+data_source_id);
			}else if($(this).hasClass('edit')){
				changeHashTo('edit/'+data_source_id);
			}else if($(this).hasClass('settings')){
				changeHashTo('settings/'+data_source_id);
			}else if($(this).hasClass('mdr')){
				window.location = base_url+'data_source/manage_deleted_records/'+data_source_id;
			}else if($(this).hasClass('mmr')){
				window.location = base_url+'data_source/manage_records/'+data_source_id;
			}else if($(this).hasClass('export')){
				window.location = base_url+'data_source/export/'+data_source_id;
			}else if($(this).hasClass('report')){
				window.location = base_url+'data_source/report/'+data_source_id;
			}
		}
	});

	$('.activity-list a').live({
		click:function(e){
			e.preventDefault();
			$(this).next('.log').slideToggle();
		}
	});

	//data source chooser event
	$('#datasource-chooser').live({
		change: function(e){
			changeHashTo('view/'+$(this).val());
		}
	});

	//closing box header will go back in history
	$('.box-header .close').live({
		click: function(e){
			//changeHashTo('browse/'+currentView);
			window.history.back();
		}
	});

	$('.exportRecord').live({
		click: function(e){
			data_source_id = $('#data_source_view_container').attr('data_source_id');
			type = $(this).attr('type');
			var data = {};
			//let's construct the array using the form
			var form_data  = $('#data_source_export_form').serializeArray();
			form_data.push({name:"as",value:type});
			data = JSON.stringify(form_data);
			window.open(base_url+'data_source/exportDataSource/'+data_source_id+'?data='+data, '_blank');
		}
	})


	$('.delete_harvest').live({
		click: function(e){
			data_source_id = $(this).attr('data_source_id');
			harvest_id = $(this).attr('harvest_id');
			button = $(this);
			$.ajax({
				url: base_url+'data_source/cancelHarvestRequest/',
				data: {id:data_source_id, harvest_id:harvest_id},
				type: 'POST',
				dataType: 'json',
				success: function(data){
					checkResponse(data);
					$(button).parent().fadeOut();
				},
				failure: function(data){
					//console.log(data);
				}
			});
		}
	})

	$('.dataSourceReport').live({
		click: function(e){
			data_source_id = $('#data_source_view_container').attr('data_source_id');
			type = $(this).attr('type');
			var data = {};
			//let's construct the array using the form
			var form_data  = $('#data_source_report_form').serializeArray();
			form_data.push({name:"as",value:type});
			data = JSON.stringify(form_data);
			window.open(base_url+'data_source/getDataSourceReport/'+data_source_id+'?data='+data, '_blank');
		}
	});

	
	$('#AddNewDS_confirm').on({
		click: function(e){
			var form = $('#AddNewDS form');
			Core_bindFormValidation(form);
			if($(form).attr('valid')=='true'){
				var key = $('input[name=data_source_key]', form).val();
				$.ajax({
					url:base_url+'services/registry/check_unique/data_source_key', 
					type: 'POST',
					data: {key:key},
					success: function(data){
						checkResponse(data);
						if(data==0){
							//console.log('is unique');
							var title = $('input[name=title]',form).val();
							var record_owner = $('select[name=record_owner]').val()
							$.ajax({
								url:base_url+'data_source/add',
								type: 'POST',
								data: {key:key,title:title,record_owner:record_owner},
								success:function(data){
									$('#AddNewDS').modal('hide');
									window.location = base_url+'data_source/manage#!/view/'+data;
								}
							});
						}else{
							$('input[name=data_source_key]').closest('div.control-group').removeClass('success').addClass('error');
						}
					}
				});
			}
		}
	});

	$('#delete_data_source_button').live({
		click: function(e){
			e.preventDefault();
			var ds_id = $(this).attr('data_source_id');
			var ds_title = $(this).attr('data_source_title');
			if (window.confirm("You are about to delete data source '"+ ds_title +"'.\n Deleting this Data Source will remove it from the registry and delete all of its records.\n Do you want to continue?") === true)
			{
				$.ajax({
					url:base_url+'data_source/delete', 
					type: 'POST',
					data: {ds_id:ds_id},
					success: function(data){
						log(data);
						window.location = base_url+'data_source/manage';
					}
				});
			}
		}
	});

 	$('.more_error_detail').live({
 		click: function(e){
       	var title = $(this).attr('data-error-type');
       	var description = $(this).next('pre').html();
       	output = formatErrorDesciption(description, title);		
		$('#logModal .modal-body').html(output);
       	$('#logModal').modal('show');	
 		} 
 	} );

});

/*
 * Initialize the View
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [string] view ENUM thumbnails|lists
 * @returns: [void]
 */
function browse(view){
	if(view=='thumbnails' || view=='lists'){
		$('section').hide();
		$('#items').removeClass();
		$('#items').addClass(view);
		$('#browse-datasources').show();
		$('#datasource-chooser').focus();
	}else{
		logErrorOnScreen('invalid View Argument');
	}
	$("#datasource-chooser").chosen();
}

/*
 * Initialize the view
 * This load the view for the next page, append that to the main #items container
 * @TODO: remove the next page div when there is no_more
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [int] page value
 * @returns: [void]
 */
function load_more(page){
	$.ajax({
	        //FIXME: [scm] do we really need base_url on the front, or should we fix/ammend the 'manage' route? (registry/data_source/manage => registry/data_source)
		url: base_url + 'data_source/getDataSources/'+page,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			checkResponse(data);
			if(data.items.length==1&&page==1)
			{
				var url_to = base_url+'data_source/#!/view/'+data.items[0]['id'];
				window.location = url_to;				
			} else { 
				var itemsTemplate = $('#items-template').html();
				var output = Mustache.render(itemsTemplate, data);
				$('#items').append(output);
				$('.goto').click(function(){
					var type = $(this).attr('type');
					var name = $(this).attr('name');
					var data_source_id = $(this).attr('data_source_id');
					var url_to = base_url+'data_source/manage_records/'+data_source_id+'/#!/{"sort":{"updated":"desc"},"filter":{"'+type+'":"'+name+'"}}';
					window.location = url_to;
				});
			}
		}
	});
}

/*
 * Load a datasource view
 * With animation, slide the view into place, 
 * hide the browse section and hide other section in progress
 * @params data_source_id
 * @return false
 */
function load_datasource(data_source_id){
	$('#view-datasource').html('Loading');
	$('#browse-datasources').slideUp(500);
	lastLogId = null;
	$.ajax({
		url: 'data_source/getDataSource/'+data_source_id,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			checkResponse(data);
			//log(data);
			//console.log(data);
			var template = $('#data-source-view-template').html();
			var output = Mustache.render(template, data);
			var view = $('#view-datasource');
			$('#view-datasource').html(output);
			$('#view-datasource').fadeIn(500);
			
			var title = data.item.title;
			document.title = title;

			//draw the charts
			// drawCharts(data_source_id);
			doLoadLogs = true;
			loadDataSourceLogs(data_source_id);
			loadContributorPages(data_source_id);

			//button toggling at edit level
			$('#view-datasource  .normal-toggle-button').each(function(){
				if($(this).attr('value')=='t' || $(this).attr('value')=='1' || $(this).attr('value')=='true' ){
					$(this).find('input').attr('checked', 'checked');
				}
				$(this).toggleButtons({
					width:75,enable:false
				});
			});

			//list on the right hand side will take you to the manage my records screen with pre-filled filters
			$('.ro-list li').click(function(){
				var type = $(this).attr('type');
				var name = $(this).attr('name');
				var url_to = base_url+'data_source/manage_records/'+data_source_id+'/#!/{"sort":{"updated":"desc"},"filter":{"'+type+'":"'+name+'"}}';
				window.location = url_to;
			});
		}
	});

	return false;
}

/**
 * load the data source log
 * @param  {[type]} data_source_id [description]
 * @param  {[type]} offset         [description]
 * @param  {[type]} count          [description]
 * @return {[type]}                [description]
 */
function loadDataSourceLogs(data_source_id, offset, count)
{
	offset = typeof offset !== 'undefined' ? offset : 0;
	count = typeof count !== 'undefined' ? count : 10;
	//var log_class = $('select.log-class').val();
	var log_type = $('select.log-type').val();
	// log(log_class, log_type, offset, count);
	if(doLoadLogs)
	{
		$.ajax({
			url: 'data_source/getDataSourceLogs/',
			data: {id:data_source_id, offset:offset, count:count, log_type:log_type},
			type: 'POST',
			dataType: 'json',
			success: function(data){
				checkResponse(data);
				var logsTemplate = $('#data_source_logs_template').html();
				if(offset == 0)
				lastLogId = data.last_log_id;
				var output = Mustache.render(logsTemplate, data);
				$('#data_source_log_container').append(output);

				$('#show_more_log').show();

				$('#data_source_log_container').fadeIn(500);
				$('#log_summary').html('Viewing ' + data.next_offset + ' of ' + data.log_size + ' log entries');
				//$('#log_summary_bottom').html('viewing ' + data.next_offset + ' of ' + data.log_size + ' log entries');
				$('#show_more_log').attr('next_offset', data.next_offset);
				if(data.next_offset=='all'){
					$('#show_more_log').hide();
				}
				// var bottom_offset = $('#data_source_log_container').offset().top + $('#data_source_log_container').height();
				//$('body').animate({"scrollTop": bottom_offset}, 100);
				//logTimer = 2000
				if(lastLogId){
					logTimer = 2000;
					window.setTimeout(function(){loadTopLogs(data_source_id)}, logTimer);
				}
				else{
					logTimer = logTimer * 2;
					window.setTimeout(function(){loadDataSourceLogs(data_source_id, offset, count)}, logTimer);
				}
			}
		});


		$('#show_more_log').die().live({
			click:function(){
				var next_offset = $(this).attr('next_offset');
				loadDataSourceLogs(data_source_id, next_offset, count);
			}
		});

		$('select.log-class, select.log-type').off('change').on('change',function(){
			$('#data_source_log_container').empty();
			loadDataSourceLogs(data_source_id);
		});
	}
	return false;
}
function _getVocab(vocab)
{
	vocab = vocab.replace("collection", "Collection");
	vocab = vocab.replace("party", "Party");
	vocab = vocab.replace("service", "Service");
	vocab = vocab.replace("activity", "Activity");
	return vocab;
}
function initVocabWidgets(container){
	var container_elem;
	if(container){
		container_elem = container;
	}else container_elem = $(document);
	$(".rifcs-type", container_elem).each(function(){
		//log(this, 'bind vocab widget');
		var elem = $(this);
		var widget = elem.vocab_widget({mode:'advanced'});
		var vocab = _getVocab(elem.attr('vocab'));
		var dataArray = Array();
		if(vocab == 'RIFCSClass')
		{				
			dataArray.push({value:'Party', subtext:'Party'});
			dataArray.push({value:'Activity', subtext:'Activity'});			
			dataArray.push({value:'Collection', subtext:'Collection'});
			dataArray.push({value:'Service', subtext:'Service'});
			elem.typeahead({source:dataArray,items:16});
		}else{
			elem.on('narrow.vocab.ands', function(event, data) {	

				if(vocab == 'RIFCSSubjectType')
				{				
					$.each(data.items, function(idx, e) {
						dataArray.push({value:e.notation, subtext:e.definition});
					});
					$(elem).off().on("change",function(e){
						// $(elem).prev().val('');
						initSubjectWidget(elem);
					});
				
					initSubjectWidget(elem);
				}	
				else
				{
					$.each(data.items, function(idx, e) {
						dataArray.push({value:e.label, subtext:e.definition});
					});
				}
				elem.typeahead({source:dataArray,items:16});
			});

			elem.on('error.vocab.ands', function(event, xhr) {
			//console.log(xhr);
		});
		widget.vocab_widget('repository', 'rifcs');
		widget.vocab_widget('narrow', "http://purl.org/au-research/vocabulary/RIFCS/1.4/" + vocab);	
		}	 
	});

}

function loadHarvestLogs(logid, refresh){
	log("loadHarvestLogs: " + logid);	
	if(refresh !== 'undefined' && refresh == true)
	{
		$('#test_harvest_activity_log .modal-body').html('');
	}
	$.ajax({
		url: 'data_source/getDataSourceLogs/',
		data: {id: $('#data_source_id_input').val(), logid:logid},
		type: 'POST',
		dataType: 'json',
		success: function(data){
			var logsTemplate = "<table class='table table-hover'>"+
			"<thead><tr><th>#</th><th>DATE</th><th>TYPE</th><th>LOG</th></tr></thead>" +
			"<tbody>" +
			"{{#items}}" +
				"<tr class='{{type}}'><td>{{id}}</td><td>{{date_modified}}</td><td>{{type}}</td><td>{{log}}</td></tr>" +
			"{{/items}}" +
			"</tbody></table>";
			var output = Mustache.render(logsTemplate, data);
			$('#test_harvest_activity_log .modal-body').html(output);
			$.each(data.items, function(i, v) {

			    if (v.log.indexOf("Test harvest completed successfully") >= 0||v.log.indexOf("error") >= 0) {
			        return false;
			    }else{
			    	window.setTimeout(function(){loadHarvestLogs(logid)}, 2000);
			    }			    
			});			
		},
		error: function(data){
		//console.log(data);
		}
	});	
	return false;
}

function loadTopLogs(data_source_id)
{
	if(doLoadLogs && lastLogId)
	{
		$('#activity_log_switcher').prepend(' <img src="'+base_url+'assets/img/ajax-loader.gif" alt="Loading..Please wait.."/>')
		var log_type = $('select.log-type').val();
		$.ajax({
			url: 'data_source/getDataSourceLogs/',
			data: {id: data_source_id, logid:lastLogId, log_type:log_type},
			type: 'POST',
			dataType: 'json',
			success: function(data){
				$('#activity_log_switcher img').fadeOut(900);
				var logsTemplate = $('#data_source_logs_template').html();
				var output = Mustache.render(logsTemplate, data);
				if(data.last_log_id != '' && lastLogId != data.last_log_id){
					logTimer = 1000;
					lastLogId = data.last_log_id;
				}else{
					logTimer = logTimer * 2;
				}
				$('#data_source_log_container').prepend(output);
				window.setTimeout(function(){loadTopLogs(data_source_id)}, logTimer);		    		
			}
		});
	}
	return false;

}

function loadContributorPages(data_source_id)
{

	$.ajax({
		url: 'data_source/getContributorGroups/',
		data: {id:data_source_id},
		type: 'POST',
		dataType: 'json',
		success: function(data){
			//console.log(data.contributorPages)
			var contributorsTemplate = "<p>"+data.contributorPages+"</p>"+
			"<table class='table table-hover headings-left'>"+
			"<thead><tr><th class='align-left'>GROUP</th><th class='align-left'>Contributor Page Key</th></tr></thead>" +
			"<tbody>" +
			"{{#items}}" +
				"<tr ><td>{{group}}</td><td>{{{contributor_page}}}</td></tr>" +
			"{{/items}}" +
			"</tbody></table>";
			var output = Mustache.render(contributorsTemplate, data);
			$('#contributor_groups').html(output);				
		},
		error: function(data){
		//console.log(data);
		}
	});
	
	return false;
}

function loadContributorPagesEdit(data_source_id,inst_pages)
{
	$.ajax({
		url: 'data_source/getContributorGroupsEdit/',
		data: {id:data_source_id,inst_pages:inst_pages},
		type: 'POST',
		dataType: 'json',
		success: function(data){
			//console.log(data)
			var contributorsTemplate = "<table class='table table-hover'>"+
			"<thead><tr><th class='align-left'>GROUP</th><th class='align-left'>Contributor Page Key</th></tr></thead>" +
			"<tbody>" +
			"{{#items}}" + 
			"<tr ><td>{{group}}</td><td>{{{contributor_page}}}</td></tr>" + 
			"{{/items}}" +
			"</tbody></table>";
			var output = Mustache.render(contributorsTemplate, data);
			$('#contributor_groups').html(output);	
			$('#contributor_groups2').html(output);	
			bind_ro_search();
		},
		error: function(data){
			log(data);
		}
	});
	
	return false;

}

function drawCharts(data_source_id){
	
	/* Draw registry object progression chart */
	$.ajax({
		url: 'charts/getRegistryObjectProgression/' + data_source_id,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			//console.log(data);
			checkResponse(data);
			$('#ro-progression').height('350').html('');

			var options = {
				axes:{
			        xaxis:{
			              renderer:$.jqplot.DateAxisRenderer, 
				          tickOptions:{formatString:data.formatString},
			        },
			    },
			    seriesDefaults: {renderer:$.jqplot.BezierCurveRenderer},
			    series:[{lineWidth:4, rendererOptions: {
                    smooth: true
                }}],
			    highlighter: {
			        show: true,
			        sizeAdjust: 7.5,
			        yvalues: 1,
				    yvalues: 3,
    				formatString:'%s: %s registry objects'
			    },
			    cursor: {
			        show: false
			    }
			};

			$.jqplot('ro-progression', [data.table] , options);

		},
		failure: function(data){
			$('#ro-progression').height('350').html('Unable to load chart...');
		}
	});
}

function validatePrimary_1(jsonData, ds_id, errorStr){
	//we need to check the key exists and is published and is from this ds

	for(var i=0; i<jsonData.length; i++) {
      	 if (jsonData[i]['name'] == 'primary_key_1') 
        {        	
        	var theKey = jsonData[i]['value'];       	
        }
    }
	$.ajax({
		url:'data_source/get_datasource_object', 
		type: 'POST',
		data:	{ 
			key: theKey, 
			data_source_id: ds_id
		}, 
		dataType: 'json',
		success: function(data){
			//console.log(data.status);
			if(data.message)
			{
				errorStr = data.message + " 1. <br />";	
			}
		},
		error: function(data){
			errorStr = errorStr + "Could not validate the primaty key given";
		},
  	async: false
	});
	return errorStr;	
}
function validatePrimary_2(jsonData, ds_id, errorStr){
	//we need to check the key exists and is published and is from this ds

	for(var i=0; i<jsonData.length; i++) {
      	 if (jsonData[i]['name'] == 'primary_key_2') 
        {        	
        	var theKey = jsonData[i]['value'];       	
        }
    }
	$.ajax({
		url:'data_source/get_datasource_object', 
		type: 'POST',
		data:	{ 
			key: theKey, 
			data_source_id: ds_id
		}, 
		dataType: 'json',
		success: function(data){
			//console.log(data.status);
			if(data.message)
			{
				errorStr = data.message + " 2. <br />";	
			}	
		},
		error: function(data){
			errorStr = errorStr + "Could not validate the primary key given";
		},
  	async: false
	});
	return errorStr;	
}

/*
 * Validate the fields and values 
 * @params jsonData
 * @return string
 */
function validateFields(jsonData, ds_id){

	var errorStr = '';

	if(included(jsonData,'create_primary_relationships') && (!included(jsonData,'primary_key_1')||!included(jsonData,'service_rel_1')||!included(jsonData,'activity_rel_1')||!included(jsonData,'party_rel_1')||!included(jsonData,'collection_rel_1')))
	{
		errorStr = errorStr + "You must provide a key and specify all relationtypes for the primary relationship.<br /><br />";

	}

	if(included(jsonData,'create_primary_relationships') && (included(jsonData,'primary_key_1')&&included(jsonData,'service_rel_1')&&included(jsonData,'activity_rel_1')&&included(jsonData,'party_rel_1')&&included(jsonData,'collection_rel_1')))
	{
		errorStr = errorStr + validatePrimary_1(jsonData, ds_id, errorStr);
	}

	if(included(jsonData,'primary_key_2') && (!included(jsonData,'primary_key_2')||!included(jsonData,'service_rel_2')||!included(jsonData,'activity_rel_2')||!included(jsonData,'collection_rel_2')||!included(jsonData,'party_rel_2')))
	{
		errorStr = errorStr +  "You must provide a key and specify all relationtypes for the primary relationship.<br />";	
	}
	if(included(jsonData,'create_primary_relationships') && (included(jsonData,'primary_key_2')&&included(jsonData,'service_rel_2')&&included(jsonData,'activity_rel_2')&&included(jsonData,'party_rel_2')&&included(jsonData,'collection_rel_2')))
	{
		errorStr = errorStr + validatePrimary_2(jsonData, ds_id, errorStr);
	}	

	if(!included(jsonData,'title'))
	{
		errorStr = errorStr + "You must provide a title. <br />";

	}

	if(included(jsonData,'push_to_nla') && !included(jsonData,'isil_value'))
	{
		errorStr = errorStr + "If you select 'Party records to NLA' you must provide an ISIL value <br />";

	}

	if(included(jsonData,'contact_email')&&!validateEmail(jsonData,'contact_email'))
	{
		errorStr = errorStr + "You have not provided a valid contact email address<br />";
	}

	if(included(jsonData,'assessment_notify_email_addr')&&!validateEmail(jsonData,'assessment_notify_email_addr'))
	{
		errorStr = errorStr + "You have not provided a valid assessment notification email address<br />";
	}

	return  errorStr;
}


function included(arr, obj) {
    for(var i=0; i<arr.length; i++) {

        if (arr[i]['name'] == obj) 
        { 
        	if(arr[i]['name']=='create_primary_relationships' && !arr[i]['value'])
        	{
        		return false;
        	}
        	if(!arr[i]['value']||arr[i]['value']=='')
        	{
        		return false;
        	}
        	return true;
        }
    }
}

function validateEmail(arr, obj) 
{
    var re = /\S+@\S+\.\S+/;

    for(var i=0; i<arr.length; i++) {
        if (arr[i]['name'] == obj) 
        { 
        return re.test(arr[i]['value']);	
        }
    }
    
}

function load_datasource_settings(data_source_id){
	$('#view-datasource').html('Loading');
	$('#browse-datasources').slideUp(500);
	$.ajax({
		url: 'data_source/getDataSource/'+data_source_id,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			checkResponse(data);
			var template = $('#data-source-settings-template').html();
			var output = Mustache.render(template, data);
			var view = $('#view-datasource');

			var title = data.item.title;
			document.title = title + ' - Settings';

			$('#settings-datasource').html(output);
			$('#settings-datasource').fadeIn(500);

			$('#settings-datasource  .normal-toggle-button').each(function(){
				if($(this).attr('value')=='t' || $(this).attr('value')=='1' || $(this).attr('value')=='true' ){
					$(this).find('input').attr('checked', 'checked');
				}
				$(this).toggleButtons({
					width:75,enable:false
				});
			});
			$('.checkbox_view1').each(function(){
				var whatsThere = $(this).html();
				$(this).html('<span class="label label-info"><i class="icon icon-white icon-ok"></i> On </span> '+ '&nbsp;' + whatsThere);
			});
			$('.checkbox_viewt').each(function(){
				var whatsThere = $(this).html();
				$(this).html('<span class="label label-info"><i class="icon icon-white icon-ok"></i> On </span> '+ ' &nbsp;' + whatsThere);
			});
			$('.checkbox_view0').each(function(){
				var whatsThere = $(this).html();
				$(this).html('<span class="label label-info"><i class="icon icon-white icon-remove"></i> Off </span> ' + '&nbsp; ' + whatsThere);
			});
			$('.checkbox_viewf').each(function(){
				var whatsThere = $(this).html();
				$(this).html('<span class="label label-info"><i class="icon icon-white icon-remove"></i> Off </span> ' + '&nbsp; ' + whatsThere);
			});					
			//draw the charts
			// drawCharts(data_source_id);
			//loadDataSourceLogs(data_source_id);
			loadContributorPages(data_source_id);
		}
	});
	return false;
}

function checkResponse(data)
{
	if (data.status == "ERROR")
	{
		log(data.message);
		logErrorOnScreen(data.message);
	}
}

/*
 * Load a datasource edit view (redundancy)
 * @TODO: refactor
 * With animation, slide the view into place, 
 * hide the browse section and hide other section in progress
 * @params data_source_id
 * @return [void]
 */
function load_datasource_edit(data_source_id, active_tab){
	$('#edit-datasource').html('Loading');
	$('#browse-datasources').slideUp(500);
	$('#view-datasources').slideUp(500);
	$.ajax({
		url: 'data_source/getDataSource/'+data_source_id,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		error: function(data)
		{
			checkResponse(data);
		},
		success: function(data){
			//console.log(data);
			checkResponse(data);
			
			var template = $('#data-source-edit-template').html();
			var output = Mustache.render(template, data);
			$('#edit-datasource').html(output);
			$('#edit-datasource').fadeIn(500);

			var title = data.item.title;
			document.title = title + ' - Edit Settings';

			if(active_tab && $('#'+active_tab).length > 0){//if an active tab is specified and exists
				$('.nav-tabs li a[href=#'+active_tab+']').click();
			}
			$('#edit-datasource  .normal-toggle-button').each(function(){
				

				if($(this).hasClass('create-primary')){
						if($(this).attr('value')=='t' || $(this).attr('value')=='1' || $(this).attr('value')=='true' ){
							$(this).find('input').attr('checked', 'checked');
							$('#primary-relationship-form').show();
						}else{
							$('#primary-relationship-form').hide();
						}
						$(this).toggleButtons({
							width:75,enable:true,
							onChange:function(){
								$(this).find('input').attr('checked', 'checked');
								$('#primary-relationship-form').toggle();
							}
						});	
				}else if ($(this).hasClass('push_to_nla')){
					if($(this).attr('value')=='t' || $(this).attr('value')=='1' || $(this).attr('value')=='true' ){
						$(this).find('input').attr('checked', 'checked');
						$('#nla-push-div').toggle();
					}
					$(this).toggleButtons({
						width:75,enable:true,
						onChange:function(){
							$(this).find('input').attr('checked', 'checked');
							$('#nla-push-div').toggle();
						}
					});
				}else if($(this).hasClass('manual_publish')){
					if($(this).attr('value')=='t' || $(this).attr('value')=='1' || $(this).attr('value')=='true' ){
						$(this).find('input').attr('checked', 'checked');
						$('#manual_publish').toggle();
					}
					$(this).toggleButtons({
						width:75,enable:true,
						onChange:function(e){								
							$(this).find('input').attr('checked', 'checked');
							$('#manual_publish').toggle();	
							var val = $('#check_manual_publish').attr('checked');
							if(val)
							{					
								$('#myModal').modal();
								data = "Enabling the ‘Manually Publish Records’ option will require you to <br />manually publish your approved records via the Manage My Records screen."
								data2 = "<br /><a href='#' class='btn' data-dismiss='modal'>OK</a>";
								$('#myModal .modal-body').html("<br/><pre>" + data + "</pre> " + data2);
							}else{
								
								$('#myModal').modal();
								data = "Disabling the ‘Manually Publish Records’ option <br />will cause your approved records to be published automatically. <br/>This means your records will be publically visible in <br />Research Data Australia immediately after being approved.";
								data2 = "<br /><a href='#' class='btn' data-dismiss='modal'>OK</a>";
								$('#myModal .modal-body').html("<br/><pre>" + data + "</pre> " + data2);
							}
						}
					});

				}else if($(this).hasClass('qa_flag')){			
					if($(this).attr('value')=='t' || $(this).attr('value')=='1' || $(this).attr('value')=='true' ){
						$(this).find('input').attr('checked', 'checked');
						$('#qa_flag').toggle();
					}
					$(this).toggleButtons({
						width:75,enable:true,
						onChange:function(){
							$(this).find('input').attr('checked', 'checked');
							var val = ($('#check_qa_flag').attr('checked'));
							
							$('#qa_flag').toggle();
							if(val)
							{					
								if($('#qa_flag_set').html()=="no")
								{
								$('#myModal').modal();
								data = "Enabling the ‘Quality Assessment Required’ option will <br/>send any records entered into the ANDS registry from this data source <br />through the Quality Assessment workflow."
								data2 = "<br /><a href='#' class='btn' data-dismiss='modal'>OK</a>";
								$('#myModal .modal-body').html("<br/><pre>" + data + "</pre> " + data2);
								}else{
									$('#qa_flag_set').html("no");
								}
							}else{
								var publishStr = '';
								var submittedAssessment = '.';
								var assessmentProgress = '.';

								$('.publish_count').each(function(){
									if($(this).attr('id')=="Submitted for Assessment")
									{
										submittedAssessment = $(this).html();
									}
									if($(this).attr('id')=="Assessment In Progress")
									{
										assessmentProgress = $(this).html();
									}
								});	
					
								if(submittedAssessment!='.')
								{
									publishStr = submittedAssessment + " <em>'Submitted for Assessment'</em> ";
									if(assessmentProgress!='.') publishStr = publishStr + " and <br />";
								}	
								if(assessmentProgress!='.')
								{
									publishStr = publishStr + assessmentProgress + " <em>'Assessment in Progress'</em> ";
								}		

								if (publishStr ==''){
									publishStr = " 0 ";
								}	
								var pubStat = " published";								
								var status = $('#check_manual_publish').attr('checked');
								if(status){pubStat = " approved";}
								$('#myModal').modal();
								data = "Disabling the ‘Quality Assessment Required’ option will cause <br />"+publishStr+"records to be automatically"+pubStat+". <br />It will also prevent any future records from being sent through <br />the Quality Assessment workflow.";
								data2 = "<br /><a href='#' class='btn' data-dismiss='modal'>OK</a> <a href='#' class='btn cancel_qa'  data-dismiss='modal'>Cancel</a>";
								$('#myModal .modal-body').html("<br/><pre>" + data + "</pre> " + data2);
							}							
						}
				});
			
				}else{
					if($(this).attr('value')=='t' || $(this).attr('value')=='1' || $(this).attr('value')=='true' ){
						$(this).find('input').attr('checked', 'checked');
					}
					$(this).toggleButtons({
						width:75,enable:true,
						onChange:function(){		
							$(this).find('input').attr('checked', 'checked');
						}
					});	

				}
				

			});

			$('#edit-datasource  .contributor-page').each(function(){
				if($('#institution_pages').val()=='') {$('#institution_pages').val('0'); }
				if($(this).attr('value')== $('#institution_pages').val() ){
					$(this).attr('checked', 'checked');
					var inst_pages = $('#institution_pages').val();
					loadContributorPagesEdit(data_source_id,inst_pages);				}
			});

			$('#edit-datasource  .contributor-page').live().change(function(){
				if ($(this).val() != '0')
				{
					var alert_message = "";
					if ($(this).val() == '1')
					{
						alert_message += "Contributor pages will be generated for the new group(s) located in this data source. ";
					}
					alert_message += "The contributor home page will be a public web document representing your organisation. ANDS advises that you should use only approved text and consult appropriate authorities within your organisation.";
					alert(alert_message);
				}
				$(this).attr('checked', 'checked');
				$('#institution_pages').val($(this).val());		
				loadContributorPagesEdit(data_source_id,$(this).val());			
			});
			
			$('#removeHarvestDate').click(function(e){
				e.preventDefault();
				$('input[name=harvest_date]').val('');
			});

			// Update harvest options once the chzn are setup
			$('#edit-datasource .chzn-select').each(function(){

				var input = $('#'+$(this).attr('for'));
				$(this).val($(input).val());

				// Setup the form options on first load
				if($(this).attr('for')=='harvest_method')
				{
					updateHarvestAvailableOptions($(this));
				}
				else if($(this).attr('for')=='provider_type')
				{
					$('#'+$(this).attr('for')).val("rif");
				}


				// And then trigger it for all subsequent changes
				$(this).chosen().change(function(){

					var input = $('#'+$(this).attr('for'));
					$(input).val($(this).val());

					if($(this).attr('for')=='harvest_method')
					{
						updateHarvestAvailableOptions($(this));
					}
				});
				$(this).trigger("liszt:updated");
			});
			


			//initalize the datepicker on click, format is optional
			$('#edit-datasource  .datepicker').click(function(){
				$(this).ands_datetimepicker();
				$(this).focus();				
			});

			initVocabWidgets('#primary-relationship-form');
		}

	});

	return false;
}

function bind_ro_search()
{
	$(".ro_search").each(function(){
		if ($(this).attr('name').match(/contributor/))
		{
			$(this).ro_search_widget({ endpoint: base_url + "registry_object_search/", 'class': "party", ds: $('#data_source_id_input').val() });
		}
		else
		{
			$(this).ro_search_widget({ endpoint: base_url + "registry_object_search/", datasource: $('#data_source_id_input').val(), lock_presets: true });
		}
		
	});
}

function updateHarvestAvailableOptions($harvest_method)
{
	if($harvest_method.val()=="GET")
	{
		$("#advanced option[value='INCREMENTAL']").remove();
		$("#advanced").trigger("liszt:updated");
		$("#test-harvest").hide();
		$("#oai_set_container").val("").hide();
	}
	if($harvest_method.val()=="RIF")
	{
		$("#advanced option[value='INCREMENTAL']").remove();						
		$("#advanced").append('<option value="INCREMENTAL">Incremental Mode</option>');
		$("#advanced").trigger("liszt:updated");		
		$("#test-harvest").show();
		$("#oai_set_container").show();
	}
}

$('.cancel_qa').live({
	click:function(e){
		$('#qa_flag_set').html("yes");
		$('.qa_flag').trigger('click');	
		$('.qa_flag').attr('value', true);	
		$('#check_qa_flag').attr('checked', 'checked');			
	}
});

$('#cancel-edit-form').live({
	click: function(e){
		e.preventDefault();
		var ds_id = $(this).attr('data_source_id')
		if (window.confirm("All unsaved changes will be lost. Are you sure you want to exit?") === true)
		{
			changeHashTo('settings/'+ds_id);
		}
	}
});

$(document).on('click', '#save-edit-form', function(e){
	e.preventDefault();
	$(this).button('loading');
	var jsonData = [];
	$('#save-edit-form').button('loading');
	var ds_id = $(this).attr('data_source_id')
	jsonData.push({name:'data_source_id', value:ds_id});
	
	$('#edit-datasource #edit-form input, #edit-datasource #edit-form textarea').each(function(){
		var label = $(this).attr('name');
		var value = $(this).val();

		if($(this).attr('type')=='checkbox'){
			var label = $(this).attr('for');
			var value = $(this).is(':checked');
			//if(label=='qa_flag'||label=='manual_publish')
			//{
				//var old_value = $('#'+label+'_old').html();
				//if(old_value=='f'){old_value=false;}
				//if(old_value=='t'){old_value=true;}
				//if(value!=old_value)
				//{					
					//alert (label + "::" + value + ' old::'+ $('#'+label+'_old').html());
				//}
			//}
		}

		if($(this).attr('type')!='radio'){
		//if(value!='' && value){
			//console.log(label + " will be set to " + value)
			jsonData.push({name:label, value:value});

		//}
		}
	});

	$('#edit-datasource #edit-form select').each(function(){
		label = $(this).attr('for');
		jsonData.push({name:label, value:$(this).val()});
	});


	var validationErrors = validateFields(jsonData, ds_id);

	if(validationErrors){
		$('#myModal').modal();
		logErrorOnScreen(validationErrors, $('#myModal .modal-body'));
		$('#save-edit-form').button('reset');
	}else{
		var form = $('#edit-form');
		var valid = Core_checkValidForm(form);
		//console.log(valid);
		$.ajax({
			url:'data_source/updateDataSource', 
			type: 'POST',
			data: jsonData,
			success: function(data){
				log(data.status);
				if (data.status != "OK"){
					$('#myModal').modal();
					logErrorOnScreen("An error occured whilst saving your changes!", $('#myModal .modal-body'));
					$('#myModal .modal-body').append("<br/><pre>" + data.message + "</pre>");
				}else{
					changeHashTo('view/'+ds_id);
					createGrowl(true, "Data Source settings were successfully updated");
					updateGrowls();
				}
			},
			error: function(data){
				log(data)
				$('#myModal').modal();
				logErrorOnScreen("An error occured whilst saving your changes!", $('#myModal .modal-body'));
				$('#myModal .modal-body').append("<br/><pre>Could't communicate with server</pre>");
			}
		});
	}
});


$('#importFromHarvesterLink').live({
	click: function(e){
		e.preventDefault();
		var jsonData = [];

		jsonData.push({name:'data_source_id', value:$('#data_source_view_container').attr('data_source_id')});

		$.ajax({
			url:'data_source/triggerHarvest', 
			type: 'POST',
			data: jsonData,
			dataType: 'json',
			success: function(data){
				checkResponse(data);
				if (data.status == "OK")
				{
					changeHashTo('view/'+$('#data_source_view_container').attr('data_source_id'));
					createGrowl(true, "<center>Your harvest been queued. <br/>Check the Activity Log for details.</center>");
					updateGrowls();
				}
			},
			error: function()
			{
				logErrorOnScreen("An error occured whilst testing your harvest!");
			}
		});
		logTimer = 1000;
		//loadDataSourceLogs($('#data_source_view_container').attr('data_source_id'));

	}
});

$('#test-harvest').live({
	click: function(e){
		e.preventDefault();
		var jsonData = [];
		$(this).button('Running...');
		var ds_id = $(this).attr('data_source_id');
		jsonData.push({name:'data_source_id', value:ds_id});
		
		$('#edit-datasource #edit-form input, #edit-datasource #edit-form textarea').each(function(){
			var label = $(this).attr('name');
			var value = $(this).val();
			if($(this).attr('type')=='checkbox'){
				var label = $(this).attr('for');
				var value = $(this).is(':checked');
			}
			if(value!='' && value){
				jsonData.push({name:label, value:value});
			}
		});

		$.ajax({
			url:'data_source/testHarvest', 
			type: 'POST',
			data: jsonData,
			success: function(data){
				//console.log(data);
					if (data.status == "OK")
					{
						$('#test_harvest_activity_log').modal();
						loadHarvestLogs(data.logid-1, true);
					}
					else
					{
						$('#test_harvest_activity_log').modal();
						logErrorOnScreen("An error occured whilst testing your harvest!", $('#test_harvest_activity_log .modal-body'));
					}
			},
			error: function()
			{
				$('#test_harvest_activity_log').modal();
				logErrorOnScreen("An error occured whilst testing your harvest!", $('#test_harvest_activity_log .modal-body'));
			}
			
		});

		$(this).button('reset');
	}
});

$('#importRecordsFromURLModal .doImportRecords').live({
	click: function(e){
		var thisForm = $('#importRecordsFromURLModal');
		$('#remoteSourceURLDisplay', thisForm).html($('form input[name="url"]').val());
		/* fire off the ajax request */
		$.ajax({
			url:'importFromURLtoDataSource', 
			type: 'POST',
			data:	{ 
				url: $('form input[name="url"]').val(), 
				data_source_id: $('#data_source_view_container').attr('data_source_id') 
			}, 
			success: function(data)
					{
						var thisForm = $('#importRecordsFromURLModal');
						thisForm.attr('loaded','true');
						var output = '';
				
						if(data.response == "success")
						{
							output = Mustache.render($('#import-screen-success-report-template').html(), data);
							$('.modal-body', thisForm).hide();
							$('div[name=resultScreen]', thisForm).html(output).fadeIn();
						}
						else
						{
							$('.modal-body', thisForm).hide();
							logErrorOnScreen(data.message, $('div[name=resultScreen]', thisForm));
							$('div[name=resultScreen]', thisForm).append("<pre>" + data.log + "</pre>");
							$('div[name=resultScreen]', thisForm).fadeIn();
						}
						$('.modal-footer a').toggle();

					}, 
			error: function(data)
					{
						$('.modal-body', thisForm).hide();
						logErrorOnScreen(data.responseText, $('div[name=resultScreen]', thisForm));
						$('div[name=resultScreen]', thisForm).fadeIn();
						$('.modal-footer a').toggle();
					},
			dataType: 'json'
		});
		
		$(this).button('loading');
		
		if (thisForm.attr('loaded') != 'true')
		{
			$('.modal-body', thisForm).hide();
			$('div[name=loadingScreen]', thisForm).fadeIn();
		}
		
		$('#importRecordsFromURLModal').on('hide',
			function()
			{
				load_datasource($('#data_source_view_container').attr('data_source_id'));
				thisForm.attr('loaded','false');
				$('.modal-footer a').hide();
				$('#importRecordsFromURLModal .doImportRecords').button('reset').show();
				$('.modal-body', thisForm).hide();
				$('div[name=selectionScreen]', thisForm).show();
			}
				
		);
		logTimer = 1000;
		loadDataSourceLogs($('#data_source_view_container').attr('data_source_id'));			
		
	}
});

$('#importRecordsFromXMLModal .doImportRecords').live({
	click: function(e){
		var thisForm = $('#importRecordsFromXMLModal');
		/* fire off the ajax request */
		$.ajax({
			url:'importFromXMLPasteToDataSource', 	//?XDEBUG_TRACE=1', //XDEBUG_PROFILE=1&
			type: 'POST',
			data:	{ 
				xml: $('#xml_paste').val(), 
				data_source_id: $('#data_source_view_container').attr('data_source_id') 
			}, 
			success: function(data)
					{
						var thisForm = $('#importRecordsFromXMLModal');
						thisForm.attr('loaded','true');
						var output = '';
				
						if(data.response == "success")
						{
							output = Mustache.render($('#import-screen-success-report-template').html(), data);
							$('.modal-body', thisForm).hide();
							$('div[name=resultScreen]', thisForm).html(output).fadeIn();
						}
						else
						{
								$('.modal-body', thisForm).hide();
								logErrorOnScreen(data.message, $('div[name=resultScreen]', thisForm));
								$('div[name=resultScreen]', thisForm).append("<pre>" + data.log + "</pre>");
								$('div[name=resultScreen]', thisForm).fadeIn();
						}
						$('.modal-footer a').toggle();

					}, 
			error: function(data)
					{
						$('.modal-body', thisForm).hide();
						logErrorOnScreen(data.responseText, $('div[name=resultScreen]', thisForm));
						$('div[name=resultScreen]', thisForm).fadeIn();
						$('.modal-footer a').toggle();
					},
			dataType: 'json'
		});
		
		$(this).button('loading');
		
		if (thisForm.attr('loaded') != 'true')
		{
			$('.modal-body', thisForm).hide();
			$('div[name=loadingScreen]', thisForm).fadeIn();
		}
		
		$('#importRecordsFromXMLModal').on('hide',
			function()
			{
				load_datasource($('#data_source_view_container').attr('data_source_id'));
				thisForm.attr('loaded','false');
				$('.modal-footer a').hide();
				$('#importRecordsFromXMLModal .doImportRecords').button('reset').show();
				$('.modal-body', thisForm).hide();
				$('div[name=selectionScreen]', thisForm).show();
			}
				
		);
		logTimer = 1000;
		loadDataSourceLogs($('#data_source_view_container').attr('data_source_id'));			
		
	}


});
function formatErrorDesciption(description, title)
{
	var harvesterErrors = ["HARVESTER RESPONDED UNEXPECTEDLY"];
	var documentLoadErrors = ["Could not parse Registry Object XML"];
	var validationErrors = ["Unable to validate XML document"];

	var formattedError = "<hr/>" + "<b>Log Message</b><pre>" + description + "</pre>";
	
	for (i in harvesterErrors)
	{
		if (description.indexOf(harvesterErrors[i]) >= 0)
		{
			return $('#harvesterErrorTpl').html() + formattedError;
		}
	}
	for (i in documentLoadErrors)
	{
		if (description.indexOf(documentLoadErrors[i]) >= 0)
		{
			return $('#documentLoadErrorTpl').html() + formattedError;
		}
	}
	for (i in validationErrors)
	{
		if (description.indexOf(validationErrors[i]) >= 0)
		{
			return $('#validationErrorTpl').html() + formattedError;
		}
	}
	return "An error occured. Details below: " + formattedError;

}
