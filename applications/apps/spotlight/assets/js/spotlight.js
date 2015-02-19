$(function(){

	//textarea
	if(editor=='tinymce'){
		tinymce.init({
		    selector: "textarea.editor",
		    theme: "modern",
		    plugins: [
		        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
		        "searchreplace wordcount visualblocks visualchars code fullscreen",
		        "insertdatetime media nonbreaking save table contextmenu directionality",
		        "emoticons template paste"
		    ],
		    height:"250px",
		    width:"700px",
		    entity_encoding : "raw",
		    toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
		});
	}

	$('#item_list ul').sortable({
		items: "li:not(#new)",
		stop: function(ev, ui){
			var array = $(this).sortable('toArray');
			// console.log(array);
			$.ajax({
				url:real_base_url+'apps/spotlight/saveOrder', 
				type: 'POST',
				dataType: "html",
				data: {data:array},
				success: function(data){
					//console.log(data)
				}
			});
		}
	});

	$('#item_list a').click(function(){
		var id = $(this).parent().attr('id');
		$('.item-content, .flexslider').hide();
		$('#'+id+'-content, #'+id+'-preview').show();
		$('#item_list a').removeClass('active');
		$(this).addClass('active');
	});
	$('#item_list a:first').click();

	$('button.save').click(function(e){
		e.preventDefault();
		var id = $(this).attr('_id');
		var form = $('form[_id='+id+']');
		if(editor=='tinymce') tinyMCE.triggerSave();
		var jsonData = $(form).serializeArray();
		//console.log(jsonData);
		$.ajax({
			url:real_base_url+'apps/spotlight/save/'+id, 
			type: 'POST',
			data: jsonData,
			dataType: "html",
			success: function(data){
				if(data=='success') {
					location.reload();
				} else {
					alert(data);
				}
			}
		});
	});

	$('button.delete').click(function(e){
		var id = $(this).attr('_id');
		if(confirm('Are you sure you want to delete this record?')){
			$.ajax({
				url:real_base_url+'apps/spotlight/delete/'+id, 
				type: 'POST',
				dataType: "html",
				success: function(data){
					if(data=='success') {
						window.location.reload(true);
					} else log(data);
				}
			});
		}
	});

	$('button.add').click(function(e){
		e.preventDefault();
		var form = $('form[_id=new]');
		if(editor=='tinymce') tinyMCE.triggerSave();
		var jsonData = $(form).serializeArray();
		$.ajax({
			url:real_base_url+'apps/spotlight/add/', 
			type: 'POST',
			dataType: "html",
			data: jsonData,
			success: function(data){
				if(data=='success') {
				document.location.reload(true);
				} else log(data);
			}
		});
	});
});

function save(jsonData){
	$.ajax({
		url:real_base_url+'apps/spotlight/save', 
		type: 'POST',
		dataType: "html",
		data: {data:jsonData},
		success: function(data){
			console.log(data)
		}
	});
}