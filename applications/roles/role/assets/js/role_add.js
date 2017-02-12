$(function(){
	
});
var forceSubmit = false;
$(document).on('change', '#role_type_id', function(){
	if($(this).val()!='ROLE_USER'){
		$('#authentication_id').hide();
	}else{
		$('#authentication_id').show();
	}
}).on('submit', '#add_form', function(){
	if(forceSubmit) return true;
	var role_id = $('#role_id').val();
	$.ajax({
		url: base_url+'role/checkUniqueRoleId/'+role_id,
		async: false,
		type: 'GET',
		success: function(data){
			if(data.unique){
				forceSubmit = true;
				$('#add_form').submit();
			}else{
				$('#msg').html('Role ID '+role_id+' already exists').show();
			}
		}
	});
	return false;
});