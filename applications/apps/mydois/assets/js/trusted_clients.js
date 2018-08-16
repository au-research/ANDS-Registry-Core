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
		if($(this).attr('name') == "client_name" && $(this).val() == ""){
			$('#add_client_msg').html($(this).attr('name') + " can not be empty!").removeClass('alert-info').addClass('alert-important');
			thisButton.button('reset');
			return 0;
		}else{
			jsonData[$(this).attr('name')] = $(this).val();
		}
	});
	$('#result_msg').html('').removeClass('label');
	$.ajax({
		url:apps_url+'mydois/add_trusted_client/',
		type: 'POST',
		data: {jsonData:jsonData},
		success: function(data){
			if(data.errorMessages){
				console.log(data);
				$('#result_msg').html(data.errorMessages).addClass('label label-important');
				$('#add_trusted_client_form')[0].reset();
				thisButton.button('reset');
			}else{
				listTrustedClients();
				thisButton.button('reset');
				$('#add_trusted_client_form')[0].reset();
				$('#add_trusted_client_modal').modal('hide');
			}
		}
	});
}).on('click', '#edit_confirm', function(){
	var thisButton = $(this);
	thisButton.button('loading');
	var jsonData = {};
	$('#edit_trusted_client_form input, #edit_trusted_client_form select').each(function(){
		if($(this).attr('name') == "client_name" && $(this).val() == ""){
			$('#add_client_msg').html($(this).attr('name') + " can not be empty!").removeClass('alert-info').addClass('alert-important');
			thisButton.button('reset');
			return 0;
		}else{
			jsonData[$(this).attr('name')] = $(this).val();
		}
	});
	$('#result_msg').html('').removeClass('label');
	$.ajax({
		url:apps_url+'mydois/edit_trusted_client/', 
		type: 'POST',
		data: {jsonData:jsonData},
		success: function(data){
			if(data.errorMessages){
				$('#result_msg').html(data.errorMessages).addClass('label label-important');
				thisButton.button('reset');
				$('#edit_trusted_client_modal').modal('hide');
			}else{
				listTrustedClients();
				$('#edit_trusted_client_form')[0].reset();
				thisButton.button('reset');
				$('#edit_trusted_client_modal').modal('hide');
			}
		}
	});
}).on('click', '#app_id_show', function(){
	$(this).hide();
	$('#app_id_field').show();
	$('#app_id_field select').chosen();
}).on('click', '.remove', function(){
	var client_id = $(this).attr('client_id');
	if(confirm('Are you sure you want to delete this trusted client: ')){
		$.ajax({
			url:apps_url+'mydois/remove_trusted_client', 
			type: 'POST',
			data: {client_id:client_id},
			success: function(data){
				listTrustedClients();
			}
		});
	}
}).on('click', '.edit', function(){
	var client_id = $(this).attr('client_id');
	$.ajax({
		url:apps_url+'mydois/get_trusted_client', 
		type: 'POST',
		data: {id:client_id},
		success: function(data){
			$('#edit_trusted_client_form input[name=client_id]').val(data['client_id']);
			$('#edit_trusted_client_form input[name=client_name]').val(data['client_name']);
			$('#edit_trusted_client_form input[name=client_contact_name]').val(data['client_contact_name']);
			$('#edit_trusted_client_form input[name=client_contact_email]').val(data['client_contact_email']);
			$('#edit_trusted_client_form input[name=ip_address]').val(data['ip_address']);
			$('#edit_trusted_client_form input[name=domainList]').val(data['domain_list']);
			$('#edit_trusted_client_form input[name=app_id]').val(data['app_id']);
			$('#edit_trusted_client_form input[name=test_app_id]').val(data['test_app_id']);
			$('#edit_trusted_client_form select[name=datacite_prefix]').val(data['datacite_prefix']);
			$('#edit_trusted_client_form input[name=shared_secret]').val(data['shared_secret']);
			$('#edit_trusted_client_form input[name=test_shared_secret]').val(data['test_shared_secret']);
			$('#edit_trusted_client_modal').modal('show');

            $('#prefix_select').empty();
            $.each(data['available_prefixes'], function (i, item) {
                $('#prefix_select').append($('<option>', {
                    value: item,
                    text : item
                }));
            });

		}
	});
}).on('click', '.sec_gen', function(){
	var sec = $(this).attr('sec');
	$('#edit_trusted_client_form input[name=shared_secret]').val(sec)
}).on('click', '.test_sec_gen', function(){
	var test_sec = $(this).attr('test_sec');
	$('#edit_trusted_client_form input[name=test_shared_secret]').val(test_sec)
}).on('click', '#add_trusted_client_btn', function(){
	$.ajax({
		url:apps_url+'mydois/get_available_prefixes',
		type: 'GET',
		success: function(data){

			$('#add_prefix_select').empty();
			$.each(data, function (i, item) {
				console.log(item);
				$('#add_prefix_select').append($('<option>', {
					value: item,
					text : item
				}));
			});
			$('#add_trusted_client_modal').modal('show');
		}
	});
}).on('click', '#fetch_unassigned_prefixes_btn', function(){
	$.ajax({
		url:apps_url+'mydois/fetch_unassigned_prefix',
		type: 'GET',
		success: function(data){
			$('#result_msg').html(data.message).addClass('label alert-info');
		}
	});
});

function listTrustedClients() {
	$('#trusted_clients').html('loading');
	$('#result_msg').html("result").removeClass('label label-important');
	$.getJSON(apps_url+'mydois/list_trusted_clients/', function(data) {
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

/*


 */