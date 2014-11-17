/**
 * ABS Querytool SDMX
 * 
 * @author Ben Greenwood <ben.greenwood@anu.edu.au>
 * @package ands/abs_sdmx_querytool
 * 
 */

$('#query_sender').click(function(){
	$('#output').html('Sending request...<br/><div class="progress progress-striped active" width="70%"><div class="bar" style="width: 100%;"></div></div>');
	$('#view_query_btn').hide();
	$.post(base_url + '/abs_sdmx_querytool/do_query', {'query': $('#query').val()}, function(data){
		
		$('#view_query_btn').show();
		$('#output').html(data.content);
		$('#query_header').html(data.headers);
		$('#query_content').html(data.query);
		
	},'json');
	
});
