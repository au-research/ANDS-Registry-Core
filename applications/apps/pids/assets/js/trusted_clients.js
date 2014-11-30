$(function(){
	init();
});
function init () {
	listTrustedClients();
}

$(document).on('click', '#add_confirm', function(){
	var thisButton = $(this);
	thisButton.button('loading');
	var jsonData = {};
	$('#add_trusted_client_form input, #add_trusted_client_form select').each(function(){
		jsonData[$(this).attr('name')] = $(this).val();
	});
	$('#result_msg').html('').removeClass('label');
	$.ajax({
		url:apps_url+'pids/add_trusted_client/', 
		type: 'POST',
		data: {jsonData:jsonData},
		success: function(data){
			if(data.errorMessages){
				$('#result_msg').html(data.errorMessages).addClass('label label-important');
				$('#add_trusted_client_form')[0].reset();				
				thisButton.button('reset');
			}else{
				listTrustedClients();
				$('#add_trusted_client_form')[0].reset();
				thisButton.button('reset');
				$('#add_trusted_client_modal').modal('hide');
			}
		}
	});
}).on('click', '#app_id_show', function(){
	$(this).hide();
	$('#app_id_field').show();
	$('#app_id_field select').chosen();
}).on('click', '.remove', function(){
	var ip = $(this).attr('ip');
	var app_id = $(this).attr('app_id');
	if(confirm('Are you sure you want to delete this trusted ip: ')){
		$.ajax({
			url:apps_url+'pids/remove_trusted_client', 
			type: 'POST',
			data: {ip:ip,app_id:app_id},
			success: function(data){
				listTrustedClients();
			}
		});
	}
});

function listTrustedClients() {
	$('#trsuted_clients').html('loading');
	$.getJSON(apps_url+'pids/list_trusted_clients/', function(data) {
		var template = $('#trusted_clients-template').html();
		var output = Mustache.render(template, data);
		$('#trusted_clients').html(output).css('opacity', '1');
		$('.data-table').dataTable({
			"aaSorting": [[ 1, "desc" ]],
			"bJQueryUI": true,
			"sPaginationType": "full_numbers",
			"sDom": '<""l>t<"F"fp>',
			"iDisplayLength": 10
		});
	});
}