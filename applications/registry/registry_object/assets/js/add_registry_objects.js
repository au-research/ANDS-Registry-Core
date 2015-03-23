/**
 */
$(function(){
	$('.addButton').live({
		click:function(e){
			e.preventDefault();
			//console.log($(this).attr('id'));
			var ro_class = $(this).attr('id');
			var theInput = '<input type="text" class="input-xlarge rifcs-type" vocab="RIFCS'+ro_class+'Type" name="type" value="" required autocomplete="off">';
			$('#ro_type').html(theInput);
			$('#AddNewDS_confirm').attr('ro_class', ro_class);
			$('#AddNewDS_confirm').html('Add New '+ro_class);

			$("#AddNewDS").modal('show');
			initVocabWidgets();

			//Core_bindFormValidation($('#AddNewDS form'));
		}
	});	
    $('input[name=key]').on({
    	blur: function(e){
    		e.preventDefault();
    		$('.alert-error').hide();
    	}
    });

	$('#generate_random_key').live({
		click:function(e){
			e.preventDefault();
			var input = $(this).prev('input');
			$.ajax({
				type: 'GET',
				url: base_url+'services/registry/get_random_key/',
				success:function(data){
					$('.alert-error').hide();
					$(input).val(data.key)
				},
				error:function(data){
					console.log(data.responseText);
				}
			});
		}
	});

	$('#AddNewDS_confirm').die().live({
		click:function(e){
			e.preventDefault();

			if(Core_checkValidForm($('#AddNewDS form'))){
				registry_object_key = $('input[name=key]').val();
				ro_class = $(this).attr('ro_class');
				type = $('input[name=type]').val();
				group = $('input[name=group]').val();
				data_source_id = $('select[name=data_source_id]').val();
				originating_source = $('input[name=originatingSource]').val();
				if(isUniqueMsg = isUniqueKey(registry_object_key, null, data_source_id))
				{				
					Core_addValidationMessage($('input[name=key]'), 'error', isUniqueMsg);
					// $('input[name=key]').parent().append('<div class="alert alert-error validation">'+ isUniqueMsg+ '</div>');
				}
				else
				{
					var data = {data_source_id:data_source_id, registry_object_key:registry_object_key, ro_class:ro_class, type:type, group:group, originating_source:originating_source};
					$.ajax({
						type: 'POST',
						url: base_url+'registry_object/add_new',
						data:{data:data},
						dataType:'JSON',
						success:function(data){
							if(data.status != 'ERROR')
							{
								$("#AddNewDS").modal('hide');
								if(data.success) window.location = base_url+'registry_object/edit/'+data.ro_id+'#!/advanced/admin';
							}
							else{
								$('.alert-error').html(data.message);
								$('.alert-error').show();
							}
						},
						error:function(data){
							console.log("error: " + data);
						}
					});
				}
			}
		}
	});



});

function _getVocab(vocab)
{
	vocab = vocab.replace("collection", "Collection");
	vocab = vocab.replace("party", "Party");
	vocab = vocab.replace("service", "Service");
	vocab = vocab.replace("activity", "Activity");
	return vocab;
}
function initVocabWidgets(container){
	var container_elem;
	if(container){
		container_elem = container;
	}else container_elem = $(document);
	$(".rifcs-type", container_elem).each(function(){
		//log(this, 'bind vocab widget');
		var elem = $(this);
		var widget = elem.vocab_widget({mode:'advanced'});
		var vocab = _getVocab(elem.attr('vocab'));
		elem.on('narrow.vocab.ands', function(event, data) {	
			var dataArray = Array();
			if(vocab == 'RIFCSSubjectType')
			{				
				$.each(data.items, function(idx, e) {
					dataArray.push({value:e.notation, subtext:e.definition});
				});
				$(elem).off().on("change",function(e){
					// $(elem).prev().val('');
					initSubjectWidget(elem);
				});
				
				initSubjectWidget(elem);
				elem.typeahead({source:dataArray});
			}
			else if(vocab == 'GroupSuggestor')
			{
				$.getJSON(base_url+'registry_object/getGroupSuggestor', function(data){
					elem.removeClass('rifcs-type-loading');
					elem.typeahead({source:data});
				});
			}
			else
			{
				$.each(data.items, function(idx, e) {
					dataArray.push({value:e.label, subtext:e.definition});
				});
				elem.typeahead({source:dataArray});
			}
			
		});

		elem.on('error.vocab.ands', function(event, xhr) {
			log(xhr);
		});
		widget.vocab_widget('repository', 'rifcs16');
		widget.vocab_widget('narrow', "http://purl.org/au-research/vocabulary/RIFCS/1.6/" + vocab);
	});

}