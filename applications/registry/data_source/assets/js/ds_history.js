/**
 */
$(function(){
	$('.activity-list a').live({
		click:function(e){
			e.preventDefault();
			$(this).next('.more').slideToggle('fast');
		}
	});

	$('.viewrecord').click(function(){
		var recordKey = $(this).attr('record_key');
		$('#myModal .modal-body').html('');
		$('div[name=resultScreen] #myModal').html('');
		try{
			var rifcs = $("#"+recordKey).html();
			rifcs = rifcs.replace(/&lt;/g, '<');
			rifcs = rifcs.replace(/&gt;/g, '>');
			rifcs = rifcs.replace(/&gamp;/g, '&');
			$('#myModal .undelete_record').show();
			$('#myModal .modal-body').html('<pre class="prettyprint linenums"><code class="language-xml">' + htmlEntities(formatXml(rifcs)) + '</code></pre>');
			prettyPrint();
		}
		catch(e)
		{
			$('#myModal .modal-body').html('The record data '+recordKey+' is missing');
		}
		$('.modal-footer .undelete_record').attr('record_key',recordKey); 
		$('#myModal').modal();
	});

	$('.undelete_record').live({
		click: function(e){
			var recordKey = $(this).attr('record_key');
			var button = $(this);
			$('#myModal').modal();
			$('#myModal .modal-body').html('');
			$('div[name=resultScreen] #myModal').html('');
			/* fire off the ajax request */
			$.ajax({
				url: base_url + 'data_source/reinstateRecordforDataSource', 	
				type: 'POST',
				data:	{ 
					deleted_registry_object_id: recordKey,
					data_source_id: $('#data_source_id').val()
				}, 
				success: function(data)
						{		
							if(data.response == "success")
							{
								output = Mustache.render($('#import-screen-success-report-template').html(), data);
								$('#myModal .modal-body').html(output);
								deleteEntry(recordKey);
								$('#myModal .undelete_record').hide();
							}
							else
							{
								$('#myModal .modal-body').html("<pre>" + data.log + "</pre>");
							}
							$('.modal-footer a').toggle();
						}, 
				error: function(data)
						{
							$('#myModal .modal-body').html('data');
						},
				dataType: 'json'
			});
		}
	});

	function deleteEntry(recordKey){
		var button = $('#'+recordKey);
		var list = $('#'+recordKey).closest('li');
		var count = $('a', list).length;
		if(count==1){
			$(list).closest('.widget-box').remove();
		}else{
			$(list).remove();
		}
	}

});