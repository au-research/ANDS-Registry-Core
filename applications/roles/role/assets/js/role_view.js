$(document).on('click','.remove_relation',function(){
	var parent = $(this).attr('parent');
	var child = $(this).attr('child');
	var elem = this;
	$.ajax({
		url:base_url+'role/remove_relation/', 
		type: 'POST',
		data: {parent:parent,child:child},
		success: function(data){
			// $(elem).parent().fadeOut();
			location.reload();
		}
	});

}).on('click', '.add_role', function(){
	var parent = $(this).prevAll('select.chosen').val();
	var child = $(this).attr('child');
	if($(this).hasClass('add_role_reverse')){
		parent = $(this).attr('child');
		child = $(this).prevAll('select.chosen').val();
	}
	if(parent!=''){
		$.ajax({
			url:base_url+'role/add_relation/', 
			type: 'POST',
			data: {parent:parent,child:child},
			success: function(data){
				location.reload();
			}
		});
	}
}).on('click', '#delete_role', function(){
	if(confirm('Are you sure you want to delete this role and all role relations related to this role? This action is irreversible')){
		var role_id = $(this).attr('role_id');
		$.ajax({
			url:base_url+'role/delete/', 
			type: 'POST',
			data: {role_id:role_id},
			success: function(data){
				window.location.href=base_url+'role/';
			}
		});
	}
}).on('change', '.chosen', function(){
	// $(this).trigger("liszt:updated");
}).on('click', '#reset_pw', function(){
	var role_id = $(this).attr('role_id');
	$('#msg').html('').removeClass('label label-success label-important');
	$.ajax({
		url:base_url+'role/resetPassphrase/'+role_id, 
		type: 'GET',
		success: function(data){
			if(data.success){
				$('#msg').addClass('label label-success').html('Success');
			}else{
				$('#msg').addClass('label label-important').html('Failed');
			}
			console.log(data);
			// location.reload();
		}
	});
})
;