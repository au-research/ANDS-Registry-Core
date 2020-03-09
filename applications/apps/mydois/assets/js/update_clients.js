$(function(){
	init();
});
function init () {
	updateTrustedClients();
}

function updateTrustedClients() {
	$('#update_clients').html('loading.....');
	$('#result_msg').html("result").removeClass('label label-important');
	$.getJSON(apps_url+'mydois/update_trusted_clients/', function(data) {
		for(var key in data){
			var client = data[key];
			if (client.status == 'ACTIVE'){
				data[key].display=true;
			}else{
				data[key].display=false;
			}
		}
		var template = $('#update_clients-template').html();
		var output = Mustache.render(template, data);
		$('#updated_clients').html(output).css('opacity', '1');
		$('.data-table').dataTable({
			"aaSorting": [[ 1, "desc" ]],
			"bJQueryUI": true,
			"sPaginationType": "full_numbers",
			"sDom": '<""l>t<"F"fp>',
			"iDisplayLength": 10
		});
	});
}

