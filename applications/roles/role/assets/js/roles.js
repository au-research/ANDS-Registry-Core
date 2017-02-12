/**
 * Core Maintenance Javascript
 */

$(function(){
	initView();
	// setInterval(function(){
	// 	updateStat();
	// }, 2000);

	 // setInterval(function(){
	 // 	updateStat();
	 // }, 10000);
});

function initView(){
	listRoles();
}

function listRoles() {

	//get Stat
	// $('#stat').css('opacity', '0.5');
	$.getJSON(base_url+'role/list_roles/', function(data) {
		console.log(data);
		var template = $('#roles-template').html();
		var output = Mustache.render(template, data);
		$('#roles').html(output).css('opacity', '1');
		$('.data-table').dataTable({
			"aaSorting": [[ 1, "desc" ]],
			"bJQueryUI": true,
			"sPaginationType": "full_numbers",
			"sDom": '<""l>t<"F"fp>',
			"iDisplayLength": 10
		});
		//$('.updateSOLRstat').click(updateSOLRstat);
	});
}