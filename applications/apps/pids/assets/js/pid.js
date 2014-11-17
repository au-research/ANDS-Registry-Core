$(function(){
	$('#reassign').hide();
});

$(document).on('click', '#update_confirm', function(){
	var theButton = this;
	var jsonData = {};
	jsonData['handle'] = $(this).attr('handle');
	$(this).button('loading');
	jsonData['values'] = [];
	$('#edit_form input[changed=true]').each(function(){
		var type = $(this).attr('name');
		jsonData['values'].push({
			type:type,
			idx:($(this).attr('idx')?$(this).attr('idx'):-1),
			value:$(this).val()
		});
	});
	// console.log(jsonData);
	$(theButton).hide();
	var counter = jsonData['values'].length;
	var increment = 100 / counter;
	var current = 0;
	$('#progress-bar').show();
	var ok_to_reload = true;
	$.each(jsonData['values'], function(){
		$.ajax({
			url:apps_url+'pids/update_handle',
			async:true, 
			type: 'POST',
			data: {
				idx:this.idx,
				value:this.value,
				type:this.type,
				handle:jsonData['handle']
			},
			success: function(data){
				console.log(data);
				var theBar = $('<div class="bar"></div>').css({width:increment+'%'});
				if(data.result=='FAILURE'){
					$(theBar).addClass('bar-danger');
					ok_to_reload = false;
				}else{
					$(theBar).addClass('bar-success');
				}

				$(theBar).attr('tip', data.message);
				$('#progress-bar').append($(theBar));
				// console.log(data);
				// current = current+increment;
				// $('#progress-bar').css({width:current+'%'})
				counter--;
				if(counter==0 && ok_to_reload){
					location.reload();
				}else{
					$(theButton).show().button('reset');
				}
			}
		});
	});

}).on('change', "#edit_modal input", function(){
	$(this).attr('changed', 'true');
}).on('click', '#reassign_toggle', function(){
	$(this).hide();
	$('#reassign').show();
}).on('click', '#confirm_reassign', function(){
	var this_handle = $(this).attr('handle');
	var new_handle = $('#reassign_value').val();
	var jsonData = {};
	jsonData['current'] = this_handle;
	jsonData['reassign'] = new_handle;
	$.ajax({
		url:apps_url+'pids/update_ownership/', 
		type: 'POST',
		data: {jsonData:jsonData},
		success: function(data){
			window.location = apps_url+'pids';
		}
	});
}).on('click', '.add_new', function(){
	var type = $(this).attr('add-type');
	if(type=='desc'){
		var new_dom = $('#new_desc').clone().insertBefore($('#separate_line')).removeClass('hide').removeAttr('id');
	}else if(type=='url'){
		var new_dom = $('#new_url').clone().insertBefore($('#separate_line')).removeClass('hide').removeAttr('id');
	}
});