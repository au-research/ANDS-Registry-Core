/**
 * Core Data Source Javascript
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */

$(function(){

	/*
	 * suffix is determined in footer.php
	 * Example: #!/browse/lists/
	 * 			#!/view/115
	 *			#!/edit/115
	 *			#!/delete/115
	 */

	$(window).hashchange(function(){
		var hash = location.hash;
		if(hash.indexOf(suffix)==0){//if the hash starts with a particular suffix
			var words = hash.substring(suffix.length, hash.length).split('/');
			var action = words[0];//action will be the first word found
			try{
				$('section').hide();
				switch(action){
					case 'browse' 	: browse(words[1]);break;
					case 'view'		: load_vocab(words[1]);break;
					case 'edit'		: load_vocab_edit(words[1]);break;
					case 'delete'	: load_vocab_delete(words[1]);break;
					case 'add'		: load_vocab_add();break;		
					default: logErrorOnScreen('this functionality is currently being worked on');break;
				}
				$('#vocab_view_container').attr('vocab_id', words[1]);
			}catch(error){
				var template = $('#error-template').html();
				var output = Mustache.render(template, error);
				$('#main-content').append(output);
				$('section').hide();
			}
		}else{//there is no hash suffix
			browse('lists');
		}
	});
	$(window).hashchange();//initial hashchange event

	//switch view button binding
	var currentView = 'thumbnails';
	$('#switch_view a').click(function(){
		changeHashTo('browse/'+$(this).attr('name'));
		currentView = $(this).attr('name');
	});

	load_more(1);//init the load_more function | load the first page

	//load_more button binding, once clicked will increment the page value
	$('#load_more').click(function(){
		var page = parseInt($(this).attr('page'));
		page++;
		load_more(page);
		$(this).attr('page', page++);
	});

	//item level binding
	$('.item').live({
		mouseenter: function(e){
			$('.btn-group', this).show();
		},
		mouseleave: function(e){
			$('.btn-group', this).hide();
		},
		dblclick: function(e){
			e.preventDefault();
			changeHashTo('view/'+$(this).attr('vocab_id'));
		},
		click: function(){
			
		}
	});

	//item button binding
	$('.btn').live({
		click: function(e){
			e.preventDefault();
			var vocab_id = $(this).attr('vocab_id');
			if($(this).hasClass('view')){
				changeHashTo('view/'+vocab_id);
			}else if($(this).hasClass('edit')){
				changeHashTo('edit/'+vocab_id);
			}else if($(this).hasClass('delete')){
				changeHashTo('delete/'+vocab_id);
			}else if($(this).hasClass('add')){
				changeHashTo('add/');
			}
		}
	});


	//vocab chooser event
	$('#vocab-chooser').live({
		change: function(e){
			changeHashTo('view/'+$(this).val());
		}
	});

	//vocab version format chooser event
	$('#versionFormatType').live({
		change: function(e){
			var theChoice = $(this).val();
			var thebox = $('#versionFormatValueBox').html();
			if(theChoice == 'file')
				{
					$('#versionFormatValueBox').html('<input type="file" id="versionFormatValue" style="display:inline"/><br />');
				} else {
					$('#versionFormatValueBox').html('<input type="text" value="" id="versionFormatValue" style="width:300px"/><br />');			
				}
		}
	});
	
	//closing box header will go back in history
	$('.box-header .close').live({
		click: function(e){
			//changeHashTo('browse/'+currentView);
			window.history.back();
		}
	});

});

/*
 * Initialize the View
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [string] view ENUM thumbnails|lists
 * @returns: [void]
 */
function browse(view){
	if(view=='thumbnails' || view=='lists'){
		$('section').hide();
		$('#items').removeClass();
		$('#items').addClass(view);
		$('#browse-vocabs').fadeIn();
	}else{
		logErrorOnScreen('invalid View Argument');
	}
	$("#vocab-chooser").chosen();
}

/*
 * Initialize the view
 * This load the view for the next page, append that to the main #items container
 * @TODO: remove the next page div when there is no_more
 * 
 * @author: Minh Duc Nguyen (minh.nguyen@ands.org.au)
 * @param: [int] page value
 * @returns: [void]
 */
function load_more(page){
	$.ajax({
		url: 'vocab_service/getVocabs/'+page,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			var itemsTemplate = $('#items-template').html();
			var output = Mustache.render(itemsTemplate, data);
			if(!data.more) $('#load_more_container').hide();
			$('#items').append(output);
		}
	});
}

/*
 * Load a vocab view
 * With animation, slide the view into place, 
 * hide the browse section and hide other section in progress
 * @params vocab_id
 * @return false
 */
function load_vocab(vocab_id){
	$('#edit-vocab').hide();
	$('#view-vocab').html('Loading');
	$('#browse-vocabs').slideUp(500);
	$.ajax({
		url: 'vocab_service/getVocab/'+vocab_id,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			if(data.status=='ERROR') logErrorOnScreen(data.message);
			var template = $('#vocab-view-template').html();
			var output = Mustache.render(template, data);
			var view = $('#view-vocab');
			$('#view-vocab').html(output);
			$('#view-vocab').fadeIn(500);

			loadVersions(vocab_id,'view');

			//bind tooltips on formats
			$('.format').each(function(){
				var vocab_id = $(this).attr('vocab_id');
				var format = $(this).attr('format');
				$(this).qtip({
					content:{
						text:'Loading...',
						ajax:{
							url: 'vocab_service/getDownloadableByFormat/'+vocab_id+'/'+format,
							type: 'GET',
							success: function(data, status){
								var template = $('#vocab-format-downloadable-template').html();
								var output = Mustache.render(template, data);
								this.set('content.text', output);
							}
						}
					},
					position:{
						my:'top left',
						at: 'bottom center'
					},
					show: {event: 'click'},
					hide: {event: 'click'},
					events: {},
					style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap ui-tooltip-large'}
				});
			});

			$('.viewChange').each(function(){
				var change_description = $(this).attr('change_description');
				$(this).qtip({
					content:change_description,
					position:{
						my:'center right',
						at: 'center left'
					},
					show: {event: 'click'},
					hide: {event: 'unfocus'},
					events: {},
					style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap ui-tooltip-large'}
				});
			});
			
			$('#contactPublisher').click(function(){
				var form = $('#contactPublisherForm').html();
				$('#myModal .modal-body').html(form);
				$('#myModal').modal();

				

				$('#myModal button.confirmContactPublisher').click(function(){
					var jsonData = [];
					jsonData.push({name:'vocab_id',value:$(this).attr('vocab_id')});
					var theForm = $('#myModal form.contactPublisherForm');
					// Core_bindFormValidation(theForm);
					$('input,textarea', theForm).each(function(){
						var label = $(this).attr('name');
						var value = $(this).val();
						if(value!='' && value){
							jsonData.push({name:label, value:value});
						}
					});
					
					if(Core_checkValidForm(theForm)){
						$.ajax({
							url:'vocab_service/contactPublisher/', 
							type: 'POST',
							data: jsonData,
							success: function(data){
								$('#myModal .modal-body').html('Your message has been sent to the vocabulary publisher');
							},
							error: function(data){
								logErrorOnScreen(data);
							}
						});
					}else{
						console.log('form is not valid');
					}
				});
			});

			$('#deleteVocab').qtip({
				content:$('#deleteVocabForm'),
				position:{
					my:'center right',
					at:'center left'
				},
				show:{event:'click'},
				hide:{event:'unfocus'},
				style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap ui-tooltip-large'}
			});

			$('#deleteVocabConfirm').click(function(){
				var vocab_id = $(this).attr('vocab_id');
				$.ajax({
					url:'vocab_service/deleteVocab/', 
					type: 'POST',
					data: {vocab_id:vocab_id},
					success: function(data){
						$('#items').html('');
						load_more(1);
						changeHashTo('browse/lists');
						$('div.qtip:visible').qtip('hide');
					},
					error: function(data){
						logErrorOnScreen(data);
					}
				});
			});
			
		}
	});
	return false;
}

function loadVersions(vocab_id, view){
	$.ajax({
		url: 'vocab_service/getVersions/'+vocab_id+'/'+view,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){			
			if(data.status=='ERROR') logErrorOnScreen(data.message);
			var template = $('#vocab-versions').html();
			data.view = view;
			var output = Mustache.render(template, data);
			if(view=='view'){
				$('#versions-view').html(output);
				$('#versions-view .addVersion').attr('view','reloadView');
			}else if(view=='edit'){
				$('#versions-edit').html(output);
			}else if(view='reloadView'){
				load_vocab(vocab_id,'view');
			}
			bindVocabVersioning(view);
		}
	});
}


function bindVocabVersioning(view){
	//bind the tooltips on versions
	$('.version').each(function(){
		var version_id = $(this).attr('version_id');
		var vocab_id = $(this).parents('.box').attr('vocab_id');
		var target = $(this).parent();
		$(target).qtip({
			content:{
				text:'Loading...',
				ajax:{
					url: 'vocab_service/getFormatByVersion/'+version_id+'/'+view,
					type: 'GET',
					success: function(data, status){
						var template = $('#vocab-format-downloadable-template-by-version').html();
						var output = Mustache.render(template, data);
						this.set('content.text', output);

						$('.editVersion').click(function(){
							var version_id = $(this).attr('version_id');
							$('.editVersionForm[version_id='+version_id+']').slideDown(100, function(){
								$(target).qtip('reposition');
							});
						});

						$('.cancelEdit').click(function(){
							var version_id = $(this).attr('version_id');
							$('.editVersionForm[version_id='+version_id+']').slideUp(100, function(){
								$(target).qtip('reposition');
							});
						});

						$('.editVersionConfirm').click(function(){
							var version_id = $(this).attr('version_id');
							var form = $('.editVersionForm[version_id='+version_id+']')
							var jsonData = [];
							jsonData.push({name:'id', value:version_id});
							
							$('input[type=text]', form).each(function(){
								var label = $(this).attr('name');
								var value = $(this).val();
								if(value!='' && value){
									jsonData.push({name:label, value:value});
								}
							});
							
							if(Core_checkValidForm(form)){
								$.ajax({
									url:'vocab_service/updateVersion/', 
									type: 'POST',
									data: jsonData,
									success: function(data){
										loadVersions(vocab_id,view);
										//showSubAlert('Version saved successfully', 'success');
									},
									error: function(data){
										logErrorOnScreen(data);
									}
								});
							}else{
								alert('form is not valid');
							}
						});

						$('.deleteVersion').click(function(){
							var version_id = $(this).attr('version_id');
							$('.deleteVersionForm[version_id='+version_id+']').slideDown(100, function(){
								$(target).qtip('reposition');
							});
						});

						$('.cancelDelete').click(function(){
							var version_id = $(this).attr('version_id');
							$('.deleteVersionForm[version_id='+version_id+']').slideUp(100, function(){
								$(target).qtip('reposition');
							});
						});

						$('.cancelAddFormat').click(function(){
							var version_id = $(this).attr('version_id');
							$('.addFormatForm[version_id='+version_id+']').slideUp(100, function(){
								$(target).qtip('reposition');
							});
						});

						$('.deleteVersionConfirm').click(function(){
							var version_id = $(this).attr('version_id');
							var vocab_id = $(this).attr('vocab_id');
							$.ajax({
								url:'vocab_service/deleteVersion/', 
								type: 'POST',
								data: {version_id:version_id},
								success: function(data){	
									loadVersions(vocab_id,view);
									//showSubAlert('Version deleted successfully', 'success');
								},
								error: function(data){
									logErrorOnScreen(data);
								}
							});
						});

						$('.addFormat').click(function(){
							var version_id = $(this).attr('version_id');
							$('.addFormatForm[version_id='+version_id+']').slideDown(100, function(){
								$(target).qtip('reposition');

								var form = $(this);
								//Core_bindFormValidation(form);
								$('.typeahead', form).typeahead({
									source:[
										{value:'SKOS',subtext:''},
										{value:'OWL',subtext:''},
										{value:'TEXT',subtext:''},
										{value:'CSV',subtext:''},
										{value:'ZTHES',subtext:''},
										{value:'RDF',subtext:''},
										{value:'OTHER',subtext:''}
									]
								});

								$('.toggleAddFormatType .btn', form).click(function(){
									var show = $(this).attr('content');
									$('.addFormatTypeContent').hide();
									$('.addFormatTypeContent input', form).removeAttr('required');
									$('.'+show,form).show();
									$('.'+show+' input', form).attr('required', 'true');
									$('input[name=type]',form).val($(this).attr('value'));
								});



							});
						});

						

						$('.toggle.FormatType .btn').click(function(){
							var version_id = $(this).parent().attr('version_id');
							var show = $(this).attr('content');
							$('.addFormatTypeContent').hide();
							$('.'+show+'[version_id='+version_id+']').show();
							$('.inputAddFormatType[version_id='+version_id+']').val($(this).attr('value'));
						});

						$('.toggleAddFormat .btn').click(function(){
							var version_id = $(this).parent().attr('version_id');
							$('.inputAddFormat[version_id='+version_id+']').val($(this).attr('value'));
						});

						$('.addFormatSubmit').click(function(){
							var version_id = $(this).attr('version_id');
							var form = $('.addFormatForm[version_id='+version_id+'] .form');
							var view = $(this).attr('view');
							if(Core_checkValidForm(form)){
								var jsonData = [];
								$('input, select', form).each(function(){
									var label = $(this).attr('name');
									var value = $(this).val();
									if(value!='' && value){
										jsonData.push({name:label, value:value});
									}
								});
								var type = $('input[name=type]',form).val();
								if(type=='file'){
									var data = new FormData();
									$.each($('input.addFormatUploadValue', form)[0].files, function(i, file) {
										data.append('userfile', file);
									});
									$.ajax({
									    url: 'vocab_service/uploadFile',
									    data: data,
									    cache: false,
									    contentType: false,
									    processData: false,
									    type: 'POST',
									    success: function(data){
									        if(data.status!='OK'){
									        	logErrorOnScreen(data.message);
									        }else{
									        	jsonData.push({name:'value',value:data.fileName})
									        	$.ajax({
													url:'vocab_service/addFormat/'+version_id, 
													type: 'POST',
													data: jsonData,
													success: function(data){
														loadVersions(vocab_id,view);
														showSubAlert('Format saved successfully');
													}
												});
									        }
									    }
									});
								}else if(type=='uri'){
									$.ajax({
										url:'vocab_service/addFormat/'+version_id, 
										type: 'POST',
										data: jsonData,
										success: function(data){
											loadVersions(vocab_id,view);
											showSubAlert('Format saved successfully');
										}
									});
								}
								//requireChangeHistory(vocab_id);
							}else{
								//alert('form is not valid');
							}
						});

						$('.openDeleteFormatForm').click(function(){
							var format_id = $(this).attr('format_id');
							$('.deleteFormatConfirmForm[format_id='+format_id+']').show();
						});

						$('.cancelDeleteFormat').click(function(){
							var format_id = $(this).attr('format_id');
							$('.deleteFormatConfirmForm[format_id='+format_id+']').hide();
						});

						$('.deleteFormat').click(function(){
							var format_id = $(this).attr('format_id');
							$.ajax({
								url:'vocab_service/deleteFormat/'+format_id, 
								type: 'POST',
								success: function(data){	
									if(data.status=='OK'){
										$('.deleteFormatConfirmForm[format_id='+format_id+']').hide();
										$('tr.formatRow[format_id='+format_id+']').fadeOut('200', function(){
											$(target).qtip('reposition');
										});

										showSubAlert('Format deleted successfully', 'success');
									}else{
										logErrorOnScreen(data);
									}
								}
							});
						});
					}
				}
			},
			position:{
				my:'right center',
				at: 'left center'
			},
			show: {event: 'click',solo:true},
			hide: {event: 'click', solo:true},
			events: {},
			style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap ui-tooltip-large'}
		});
	});

	$('.downloadFormat').die().live({
		click:function(){
			var format_id = $(this).attr('format_id');
			//console.log(format_id);
			window.open('vocab_service/downloadFormat/'+format_id, '_blank');
		}
	});

	$('.addVersion').each(function(){
		var vocab_id = $(this).attr('vocab_id');
		var view = $(this).attr('view');
		var content = $('#add-version-to-vocab').html();
		$(this).qtip({
			content:{
				text:content
			},
			position:{
				my:'right center',
				at: 'left center'
			},
			show: {event: 'click'},
			//hide: {fixed:true,delay:2000,target: $('.closeTip')},
			hide: {event: 'click'},
			events: {
				show: function(event, api){
					var tooltip = api.elements.tooltip;
					var form = $('form',tooltip);
					// Core_bindFormValidation(form);

					$('.typeahead', tooltip).typeahead({
						source:[
							{value:'SKOS',subtext:''},
							{value:'OWL',subtext:''},
							{value:'TEXT',subtext:''},
							{value:'CSV',subtext:''},
							{value:'ZTHES',subtext:''},
							{value:'RDF',subtext:''},
							{value:'OTHER',subtext:''}
						]
					});
					$('.closeTip', tooltip).click(function(){
						$('.addVersion').qtip('hide');
					});

					$('.toggleAddFormatType .btn', tooltip).die().unbind().click(function(){
						var show = $(this).attr('content');
						$('.addFormatTypeContent').hide();
						$('.addFormatTypeContent input', tooltip).removeAttr('required');
						$('.'+show,tooltip).show();
						$('.'+show+' input', tooltip).attr('required', 'true');
						$('input[name=type]',tooltip).val($(this).attr('value'));
					});

					$('.addVersionButton', tooltip).die().unbind().click(function(e){
						e.preventDefault();
						var jsonData = [];
						var form = $('form', tooltip);

						jsonData.push({name:'vocab_id', value:vocab_id});
						
						$('input, select', form).each(function(){
							var label = $(this).attr('name');
							var value = $(this).val();
							if(value!='' && value){
								jsonData.push({name:label, value:value});
							}
						});

						
						if(Core_checkValidForm(form)){
							var type = $('input[name=type]', tooltip).val();
							if(type=='uri'){
								$.ajax({
									url:'vocab_service/addVersion/'+vocab_id, 
									type: 'POST',
									data: jsonData,
									success: function(data){	
										//requireChangeHistory(vocab_id);
										loadVersions(vocab_id,view);
										$(this).button('reset');
										//showSubAlert('Version saved successfully', 'success');
									},
									error: function(data){
										logErrorOnScreen(data);
									}
								});
							}else if(type=='file'){
								var data = new FormData();
								$.each($('input.addFormatUploadValue', tooltip)[0].files, function(i, file) {
									data.append('userfile', file);
									jsonData.push({name:'value',value:file.name})
								});
								$.ajax({
								    url: 'vocab_service/uploadFile',
								    data: data,
								    cache: false,
								    contentType: false,
								    processData: false,
								    type: 'POST',
								    success: function(data){
								        if(data.status!='OK'){
								        	doAdd = false;
								        	logErrorOnScreen(data.message);
								        }else{
								        	$.ajax({
												url:'vocab_service/addVersion/'+vocab_id, 
												type: 'POST',
												data: jsonData,
												success: function(data){
													loadVersions(vocab_id,view);
													//requireChangeHistory(vocab_id);
													$(this).button('reset');
													//showSubAlert('Version saved successfully', 'success');
												},
												error: function(data){
													logErrorOnScreen(data);
												}
											});
								        }
								    }
								});
							}
						}else{
							alert('form is not valid');
						}
						
					});
				}
			},
			style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap ui-tooltip-large'}
		});
	});

}

function requireChangeHistory(vocab_id){
	//require all changes on view screen
	$('div.qtip:visible').qtip('hide');//close all qtip
	var html = $('#changeHistoryForm').html();
	$('#myModal-noClose .modal-body').html(html);
	$('#myModal-noClose').modal({backdrop:'static',keyboard:false});
	var form = $('#myModal-noClose form');
	// Core_bindFormValidation(form);
	$('#confirmAddChangeHistory').click(function(){
		var description = $('#myModal-noClose .changeHistoryDescription').val();
		if(Core_checkValidForm($('#myModal-noClose form'))){
			$.ajax({
				url:'vocab_service/addChangeHistory/', 
				type: 'POST',
				data: {vocab_id:vocab_id,description:description},
				success: function(data){
					$('#myModal-noClose').modal('hide');
				},
				error: function(data){
					logErrorOnScreen(data);
					$('#myModal-noClose').modal('hide');
				}
			});
		}
	});
}

/*
 * Load a vocab edit view (redundancy)
 * @TODO: refactor
 * With animation, slide the view into place, 
 * hide the browse section and hide other section in progress
 * @params vocab__id
 * @return [void]
 */
function load_vocab_edit(vocab_id){
	$('#edit-vocab').html('Loading');
	$('#browse-vocab').slideUp(500);
	$('#view-vocabs').slideUp(500);
	$.ajax({
		url: 'vocab_service/getVocab/'+vocab_id+'/edit/',
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			if(data.status=='OK'){
				var template = $('#vocab-edit-template').html();
				var output = Mustache.render(template, data);
				$('#edit-vocab').html(output);
				$('#edit-vocab').fadeIn(500);
				loadVersions(vocab_id, data.item.view);
				var form = $('#edit-form');
				// Core_bindFormValidation(form);

				var record_owner = $('#edit-vocab input[name=record_owner]').val();
				$('#chooseRecordOwner').val(record_owner);
				$('#chooseRecordOwner').chosen().trigger("liszt:updated");

				$("#edit-vocab #chooseRecordOwner").chosen().change(function(){
					var input = $('#edit-vocab input[name=record_owner]');
					$(input).val($(this).val());
				});
				
				$('#edit-vocab .cancel').click(function(){
					var vocab_id = $(this).attr('vocab_id');
					$.ajax({
						url:'vocab_service/undoVocab/'+vocab_id, 
						type: 'POST',
						success: function(data){
							changeHashTo('view/'+vocab_id);
						}
					});
				});

			}else{
				logErrorOnScreen(data.message);
			}
			
		}
	});
	return false;
}

function showSubAlert(content, alert_class){
	if(alert_class=='undefined') alert_class='success';
	var vocab_id = $('#subalert').attr('vocab_id');
	$('#subalert').slideUp(100, function(){
		$('#subalert').removeClass('alert-success').removeClass('alert-warning').removeClass('alert-error').addClass('alert-'+alert_class).html("");
		$('#subalert').html(''+content+' <a href="#!/view/'+vocab_id+'">View vocabulary</a>').slideDown();
	});
}

function showEditVersionModal(vocab_id,version_id)
{
	$.ajax({
		url: 'vocab_service/getVocabVersion/'+version_id,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			//console.log(data);				
			var template = $('#vocab-version-edit-template').html();
			var output = Mustache.render(template, data);
			$('#versionModal').modal();
			$('#modal-form').html(output);
		}
	});
	
}


function load_vocab_add(){
	$.ajax({
		url: 'vocab_service/createBlankVocab/',
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			if(data.status=='OK'){
				var id = data.id;
				changeHashTo('edit/'+id);
			}else if(data.status=='WARNING'){
				window.location =data.redirect;
			}
		}
	});
}

$('#save-edit-form').die().live({
	click: function(e){
		e.preventDefault();
		var jsonData = [];
		$(this).button('loading');
		var vocab_id = $('#edit-form').attr('vocab_id');
		var form = $('#edit-form');

		jsonData.push({name:'vocab_id', value:vocab_id});
		$('#edit-vocab #edit-form input, #edit-vocab #edit-form textarea').each(function(){
			var label = $(this).attr('name');
			var value = $(this).val();
			if(value!='' && value){
				jsonData.push({name:label, value:value});
			}
		});

		if(Core_checkValidForm(form)){
			$.ajax({
				url:'vocab_service/updateVocab', 
				type: 'POST',
				data: jsonData,
				success: function(data){
					if(data.status=='OK'){
						$('#save-edit-form-message').html(data.message);
						requireChangeHistory(vocab_id);
						loadVersions(vocab_id,'edit');
					}else if(data.status=='WARNING'){
						$('#myModal .modal-body').html(data.message);
						$('#myModal').modal();
					}else{
						$('#myModal').modal();
						logErrorOnScreen("An error occured whilst saving your changes!", $('#myModal .modal-body'));
						$('#myModal .modal-body').html("<br/><pre>" + data + "</pre>");
					}
				},
				error: function(){
					$('#myModal').modal();
					logErrorOnScreen("An error occured whilst saving your changes!", $('#myModal .modal-body'));
					$('#myModal .modal-body').html("<br/><pre>" + data + "</pre>");
				}
			});
		}else{
			$('#myModal .modal-body').html('<p>Form is not valid.</p>');
			$('#myModal').modal();
		}
		$(this).button('reset');
	}
});