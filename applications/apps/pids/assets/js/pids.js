var params = {};
$(function(){
	initView();
});

$(document).on('click', '.nav li', function(){
    var tab = $(this).attr('name');
    if($('.widget-content[name='+tab+']').length) {
        $('.nav li').removeClass('active');
        $(this).addClass('active');
        $('.widget-content').hide();
        $('.widget-content[name='+tab+']').show();
    }
    initView();
});

$(document).on('click', '#mint_confirm', function(){
	if($(this).hasClass('disabled')) return false;
	$(this).button('loading');
	var theButton = this;
	var url = $('#mint_form input[name=url]').val();
	var desc = $('#mint_form input[name=desc]').val();
	

	var mint_url = apps_url+'pids/mint';
	if($('#batch_mint_toggle').attr('checked')) {
		var counter = $('#mint_form input[name=counter]').val();
		var mint_url = apps_url+'pids/batch_mint';
	}

	var data = {
		url: $('#mint_form input[name=url]').val(),
		desc: $('#mint_form input[name=desc]').val(),
		counter: $('#mint_form input[name=counter]').val()
	}

	$.ajaxSetup({
		processData: true,
        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
	});

	if($('#csv_file').val()!="") {
		data = new FormData();
		data.append( 'file', $('#csv_file')[0].files[0] );
		mint_url = apps_url+'pids/upload_csv';
		$.ajaxSetup({
			processData: false,
	        contentType: false,
		});
	}

	$('#mint_result').html('').removeClass('alert');

	$.ajax({
		url: mint_url,
		type: 'POST',
		data: data,
		success: function(data){
			if(data.error || data.status=='ERROR' || data.result=='error'){
            	var message = data.error ? data.error : data.message;
                $('#mint_result').html(message).addClass('alert alert-error');
                $(theButton).button('reset');
            }else{
        		var message = 'Your pid has been minted successfully: <a target="_blank" href="'+apps_url+'pids/view/?handle='+data.handle+'">'+data.handle+'</a>';
	        	if(data.csv_file_path){
	        		var message = 'Your batch mint is completed successfully. <a target="_blank" href="'+data.file_path+'">'+data.file+'</a>';
	        	}
	        	if(data.message) {
	        		message = data.message;
	        	}
	        	if(data.log_file) {
	        		message += "<br/><a target='_blank' href='../assets/uploads/pids/" + data.log_file + "'>view log</a>"
	        	}
	            $('#mint_result').html(message).removeClass('alert-error').addClass('alert alert-success');
	            $(theButton).button('reset');
            	$.ajaxSetup({
            		processData: true,
                    contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            	});
            }
		}
	});
}).on('click', '.load_more', function(){
	params['offset'] = $(this).attr('next_offset');
	var button = $(this);
	$.ajax({
		url: apps_url+'pids/list_pids', 
		type: 'POST',
		data: {params:params},
		success: function(data){
			var template = $('#pids-more-template').html();
			var output = Mustache.render(template, data);
			button.after(output);
			button.remove();
		}
	});
}).on('submit', '.form-search', function(e){
	e.preventDefault();
	params['offset']=0;
	params['searchText'] = $('#search_query').val();
	listPIDs(params);
}).on('change', '#pid_chooser', function(){
	window.location = "?identifier="+$(this).val();
}).on('change', '#mint_form input[name=agree]', function(){
	if(this.checked){
		$('#mint_confirm').removeClass('disabled');
	}else $('#mint_confirm').addClass('disabled');
}).on('click', '#toggleTerms', function(){
	$('#terms').toggle();
}).on('click', '#upload_confirm', function(){
    $('#upload_result').html("").removeClass('alert alert-important alert-success');
    $(this).button('Uploading....');
    var theButton = this;
    var fd = new FormData();
    fd.append( 'file', $('#csv_file')[0].files[0] );
    $.ajax({
        url: apps_url+'pids/upload_csv',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        success: function(data){
            if(data.error){
                $('#upload_result').html(data.error).addClass('alert alert-important');
                $(theButton).button('reset');
            }else{
                $('#upload_result').html(data.message+"<br/><a target='_blank' href='../assets/uploads/pids/" + data.log_file + "'>view log</a>").addClass('alert alert-success');
            }
        }
    });
}).on('change', '#batch_mint_toggle', function(){
	if($(this).attr('checked')) {
		$('#pids_counter').show();
	} else $('#pids_counter').hide();
}).on('click', '#clear_csv_file', function(){
	$('#csv_file').val('');
});

function initView(){
	params['offset'] = 0;
	listPIDs(params);
}

function listPIDs(params) {
	$('#pids').html('');
	params['identifier'] = $('#identifier').val();
	if(params['identifier']=='') params['identifier'] = 'My Identifiers';
	$('#pid_chooser').val(params['identifier']).trigger('liszt:updated');
	if(params['identifier']!='My Identifiers'){
		params['authDomain'] = 'researchdata.ands.org.au';
	}else{
		delete params['identifier'];
		delete params['authDomain'];
	}
	$.ajax({
		url: apps_url+'pids/list_pids', 
		type: 'POST',
		data: {params:params},
		success: function(data){
			var template = $('#pids-list-template').html();
			var output = Mustache.render(template, data);
			$('#pids').html(output);
		}
	});
}

