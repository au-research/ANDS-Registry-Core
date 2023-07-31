$(function(){
	init();
});
function init () {
	mergeTrustedClients();
}

function mergeTrustedClients() {
	$('#merged_clients').html('loading');
	$('#result_msg').html("result").removeClass('label label-important');
	$.getJSON(apps_url+'mydois/merge_trusted_clients/', function(data) {
		var template = $('#merge_clients-template').html();
		var output = Mustache.render(template, data);
		$('#merged_clients').html(output).css('opacity', '1');
		$('.data-table').dataTable({
			"aaSorting": [[ 1, "desc" ]],
			"bJQueryUI": true,
			"sPaginationType": "full_numbers",
			"sDom": '<""l>t<"F"fp>',
			"iDisplayLength": 10
		});
	});
};