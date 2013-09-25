/**
 * Core Maintenance Javascript
 */

$(function(){
	initView();
	// setInterval(function(){
	// 	updateStat();
	// }, 2000);

	 setInterval(function(){
	 	updateStat();
	 }, 60000);
});

function initView(){
	updateStat();
	updateDataSourcesStat();
}

function updateStat() {

	//get Stat
	// $('#stat').css('opacity', '0.5');
	$.getJSON(base_url+'maintenance/getStat', function(data) {
		var template = $('#stat-template').html();
		var output = Mustache.render(template, data);
		$('#stat').html(output);
		$('#stat').css('opacity', '1');
		//$('.updateSOLRstat').click(updateSOLRstat);
	});
}

function updateDataSourcesStat(){
	//get Datasources stat
	$('#ds').css('opacity','0.5');
	$.getJSON(base_url+'maintenance/getDataSourcesStat', function(data) {
		var template = $('#ds-template').html();
		var output = Mustache.render(template, data);
		$('#ds').css('opacity', '1.0');
		$('#ds').html(output);
		$('#dataSourceSelect').chosen();
		$('.data-table').dataTable({
			"aaSorting": [[ 5, "desc" ]],
			"bJQueryUI": true,
			"sPaginationType": "full_numbers",
			"sDom": '<""l>t<"F"fp>'
		});
	});
}

$(document).on('click','button.task',function(){
	$(this).button('loading');
	var op = $(this).attr('op');
	var ds_id = $(this).attr('ds_id');
	var url;
	switch(op){
		case 'index_ds':url = base_url+'maintenance/indexDS/'+ds_id;break;
		case 'enrich_ds':url = base_url+'maintenance/enrichDS/'+ds_id;break;
		case 'clear_ds':url = base_url+'maintenance/clearDS/'+ds_id;break;
		case 'enrich_all': url= base_url+'maintenance/enrichAll/';break;
		case 'enrich_missing': url= base_url+'maintenance/enrichMissing/';break;
		case 'index_all': url= base_url+'maintenance/indexAll/';break;
		case 'index_missing': url= base_url+'maintenance/indexMissing/';break;
		case 'cleanNotExist': url= base_url+'maintenance/cleanNotExist/';break;
	}
	$.getJSON(url, function(data) {
		updateDataSourcesStat();
	});
}).on('click','#refresh', function(){
	initView();
}).on('click', '#syncRO', function(){
	var idkey = $('#idkey').val();
	$('#result').html('Syncing...')
	if(idkey!=''){
		$.ajax({
			url:base_url+'maintenance/sync', 
			type: 'POST',
			data: {idkey:idkey},
			success: function(data){
				$('#result').html(data.message);
			}
		});
	}
});