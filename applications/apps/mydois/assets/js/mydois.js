$(document).on('click', '.nav li', function(){
    var tab = $(this).attr('name');
    if($('.widget-content[name='+tab+']').length) {
        $('.nav li').removeClass('active');
        $(this).addClass('active');
        $('.widget-content').hide();
        $('.widget-content[name='+tab+']').show();
    }
});

$(document).on('click', '#linkChecker', function(){
    $('#linkChecker_message').removeClass('alert alert-info');
    $('#linkChecker_message').html('');
    $("#linkChecker_message").html('<p>Checking.....</p><div class="progress progress-striped active"><div class="bar" style="width: 100%;"></div>')
	var app_id = $(this).attr('app_id');
	$.ajax({
		url:apps_url+'mydois/runDoiLinkChecker', 
		type: 'POST',
		data: {app_id:app_id},
		success: function(data){
			if(data.status=='SUCCESS'){
                $('#linkChecker_message').html('');
				$('#linkChecker_result').html(data.message);																				
			}
		}
	});
});
$(document).on('change','input:radio[name="xml_input"]',function(e){
    var toDisplay = this.value;
	var radio = $('input[name="xml_input"]')
	$.each(radio,function(){$('#'+this.value).css('display','none')});
    $('#'+toDisplay).css('display','block')
});

$('#formxml').show();

$(document).on('click', '#doi_mint_confirm', function(){

    var xml_input = 'formxml';
    var req_element_error = '';

    $.each($('input[name="xml_input"]'),function(){
       if($(this).is(":checked")){
           xml_input = ($(this).val())
       }
    });

    if(xml_input == 'formxml') req_element_error = checkFormInput()
    if($(this).hasClass('disabled')) return false;
    $(this).button('loading');
    $('#mint_result').removeClass('label label-important');
    $('#mint_result').html('');
    $("#mint_result").html('<p>Minting.....</p><div class="progress progress-striped active"><div class="bar" style="width: 100%;"></div>')
    $("#mint_form").addClass('hide');
    var theButton = this;
    var doi_url = $("input[name='url']").val();
    var client_id = $("input[name='client_id']").val();
    var app_id= $("input[name='app_id']").val();
    var url = apps_url+'mydois/mint.json/?manual_mint=true&url='+doi_url+'&app_id='+app_id;
    var theInput = document.getElementById('xml');
    var theInput_upload = document.getElementById('xml_upload');
    var xml = theInput.value
    var xml_upload = theInput_upload.value
    if(xml_upload!='')xml = xml_upload

    if(req_element_error!=''){
        message = req_element_error;
        $('#mint_result').css('white-space','normal')
        $('#mint_result').html(message).addClass('label label-important');
        $(theButton).button('reset');
        $("#loading").html('');
        $("#mint_form").removeClass('hide');
    }
    else if(doi_url==''){
        message = "You must provide a URL to mint a DOI."
        $('#mint_result').css('white-space','normal')
        $('#mint_result').html(message).addClass('label label-important');
        $(theButton).button('reset');
        $("#loading").html('');
        $("#mint_form").removeClass('hide');
    }
    else if(xml==''){
        message = "You must provide xml to mint a DOI."
        $('#mint_result').css('white-space','normal')
        $('#mint_result').html(message).addClass('label label-important');
        $(theButton).button('reset');
        $("#loading").html('');
        $("#mint_form").removeClass('hide');
    }else{
        $.ajax({
            url: url,
            type: 'POST',
            data: {xml:xml, client_id:client_id},
            success: function(data){
                if(data.response.type=='failure'){
                    var message =  data.response.message;
                    if(data.response.verbosemessage!='') message = message + ' <br /><i>'+data.response.verbosemessage+'</i>'
                    $('#mint_result').css('white-space','normal')
                    $('#mint_result').html(message).addClass('label label-important');
                    $(theButton).button('reset');
                    $("#loading").html('');
                    $("#mint_form").removeClass('hide');
                }else{
                    $('#mintDoiResult').modal('show');
                    $('#mint_result').html(message).removeClass('label label-important');
                    $('#mint_result').html();
                    $('#mintDoiResult .modal-body').html('<p>'+data.response.message+'</p>');
                    $('#doi_mint_close').removeClass('hide');
                }
            },
            error: function(data){
                console.log(data.response)
            }
        });
    }
})

function successFunction() {
    var iframeObject = document.getElementById('my_iframe');


    if (iframeObject.contentDocument) { // DOM
        doc = iframeObject.contentDocument;
    }
    else if (iframeObject.contentWindow) { // IE win
        doc = iframeObject.contentWindow.document;
    }
    if (doc) {
        console.log(doc);
        var outputxml = doc.getElementById('xml_p');
        var xml = outputxml.innerText;
        var displayXml = outputxml.innerHTML;
    }
    $("#xmldisplay").html(displayXml)
    var xmlObject = document.getElementById('xml');
    var xmlObject_upload = document.getElementById('xml_upload');
    xmlObject_upload.value = xml.replace("<br>","");
}


$(document).on('change','#fileupload',function(e){
    var theForm = document.getElementById('mint_form');
    $('#mint_result').html('').removeClass('label label-important');

    theForm.target = 'my_iframe';
    theForm.submit();
    var callback = function () {
        if (successFunction){
            successFunction();

        }
        $('#frame').unbind('load', callback);
    };

    $('#my_iframe').bind('load', callback);

});

$(document).on('click', '#doi_update_confirm', function(){

    if($(this).hasClass('disabled')) return false;
    $(this).button('loading');
    $('#update_result').removeClass('label label-important');
    $('#update_result').html('');
    $("#update_result").html('<p>Updating.....</p><div class="progress progress-striped active"><div class="bar" style="width: 100%;"></div>')
    $("#update_form").addClass('hide');
    var theButton = this;
    var doi = $("input[name='doi_id']").val();
    var doi_url = $("input[name='new_url']").val();
    var client_id = $("input[name='client_id']").val();
    var app_id= $("input[name='app_id']").val();
    var url = apps_url+'mydois/update.json/?manual_update=true&doi='+doi+'&url='+doi_url+'&app_id='+app_id;
    var xml = $("textarea[name='new_xml']").val();

    if(doi_url=='' & xml==''){
        message = "You must provide new url and/or new xml to update a DOI."
        $('#update_result').css('white-space','normal')
        $('#update_result').html(message).addClass('label label-important');
        $(theButton).button('reset');
        $("#loading").html('');
        $("#update_form").removeClass('hide');
    }else{
            $.ajax({
            url: url,
            type: 'POST',
            data: {doi_id:doi, xml:xml, client_id:client_id},
            success: function(data){
                if(data.response.type=='failure'){
                    var message =  data.response.message;
                    if(data.response.verbosemessage!='') message = message + ' <br /><i>'+data.response.verbosemessage+'</i>'
                    $('#update_result').css('white-space','normal')
                    $('#update_result').html(message).addClass('label label-important');
                    $(theButton).button('reset');
                    $("#loading").html('');
                    $("#update_form").removeClass('hide');
                }else{
                    $('#update_result').html(message).removeClass('label label-important');
                    $('#update_result').html(data.response.message);
                    $("#loading").html('');
                    $('#doi_update_confirm').addClass('hide');
                    $('#doi_update_close').removeClass('hide');
                }
            },
            error: function(data){
                //console.log(data)
            }
        });
    }
})

$('#mintDoiResult').on('hidden.bs.modal', function () {
    window.location.reload(true);
})

$('#updateDoiModal').on('hidden.bs.modal', function () {
    window.location.reload(true);
})

function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
function checkFormInput(){
    var error_msg = "You must provide the following fields <br />"

    if($("input#title").val()=='') error_msg =error_msg + "<em>Title(s)</em><br />"
    if($("input#creatorname").val()=='') error_msg =error_msg + "<em>Creator(s)</em><br />"
    if($("input#publisher").val()=='') error_msg =error_msg + "<em>Publisher</em><br />"
    if($("input#year").val()=='') error_msg =error_msg + "<em>Publication Year</em><br />"

    if(error_msg == "You must provide the following fields <br />"){
        error_msg = '';
    }
    return error_msg
}
