/**
 */
$(function(){
	$('#exportRIFCS').click(function(){
        //CC-2857 add revision ID to the request
		$.getJSON(base_url+'registry_object/get_record_data/'+$('#ro_id').val()+ '/'+$('#revision_id').val(), function(data){
            $('#myModal .modal-header h3').html('<h3>RIFCS:</h3>');
			$('#myModal .modal-body').html('<pre class="prettyprint linenums"><code class="language-xml">' + htmlEntities(formatXml(data.ro.xml)) + '</code></pre>');
			prettyPrint();
			$('#myModal').modal();
		});
	});


    $('.status_change_action').on('click', function(e){
        e.preventDefault();
        url = base_url+'registry_object/update/';
        data = {affected_ids:[$('#ro_id').val()], attributes:[{name:'status',value:$(this).attr('to')}], data_source_id:$('#data_source_id').val()};
        $.ajax({
            url:url, 
            type: 'POST',
            data: data,
            dataType: 'JSON',
            success: function(data){
                if(data.status=='success')
                {
                    if(data.error_count != '0')
                    {
                        $("html, body").animate({ scrollTop: 0 });
                        logErrorOnScreen('ERROR WHILST CHANGING RECORD STATUS: ' + data.error_message);
                    }
                    else{
                        if (typeof(data.new_ro_id) !== 'undefined')
                        {
                            window.location = base_url + 'registry_object/view/' + data.new_ro_id;
                        }
                        else
                        {
                            window.location.reload();
                        }
                    }
                }
                else
                {
                    $("html, body").animate({ scrollTop: 0 });
                    logErrorOnScreen('ERROR WHILST CHANGING RECORD STATUS: ' + data.message);
                }
            }
        });
    });

    $('#delete_record_button').on('click', function(e){
        e.preventDefault();
        if (confirm('Are you sure you want to delete this record?' + "\n" 
            + "NOTE: Non-PUBLISHED records cannot be recovered once deleted.") !== true) 
        { 
            return; 
        }


        var data =  {affected_ids:[$('#ro_id').val()], data_source_id:$('#data_source_id').val()};
        $.ajax({
            url: base_url+'registry_object/delete/', 
            data: data,
            type: 'POST',
            success: function(data){
                if (!data.status == "success")
                {
                    alert(data.message);
                }
                else
                {
                     window.location = base_url + 'data_source/manage_records/' + $('#data_source_id').val();
                }
            }
        });

    });



    $('.tag_form').submit(function(e){
        e.preventDefault();
        e.stopPropagation();
        var ro_key = $(this).attr('ro_key');
        var tag = $('input', this).val();
        var tag_type = $('#tag_type').text();
        $('.notag').hide();
        if(tag!='' && $.trim(tag)!=''){
         $.ajax({
            url:real_base_url+'registry/services/registry/tags/keys/add', 
            type: 'POST',
            data: {keys:[ro_key],tag:tag, tag_type:tag_type},
            success: function(data){
                if(data.status=='ERROR'){
                    alert(data.message);
                }else{
                    location.reload();
                }
            }
         });
        }
    });

    $('.theme_tag_form').submit(function(e){
        e.preventDefault();
        e.stopPropagation();
        var ro_key = $(this).attr('ro_key');
        var tag = $('select#secret_tag').val();
        $.ajax({
           url:real_base_url+'registry/services/registry/tags/keys/add', 
           type: 'POST',
           data: {keys:[ro_key],tag:tag, tag_type:'secret'},
           success: function(data){
               if(data.status=='ERROR'){
                   alert(data.message);
               }else{
                   location.reload();
               }
           }
        });
    });

    $(document).on('click', '.tags .btn-remove', function(){
        var tag = $(this).parent().attr('tag');
        var ro_key = $(this).parent().attr('ro_key');
        var li_item = $(this).parent();
        $.ajax({
            url:real_base_url+'registry/services/registry/tags/keys/remove', 
            type: 'POST',
            data: {keys:[ro_key],tag:tag},
            success: function(data){
                if(data.status=='ERROR'){
                    alert(data.message);
                } else {
                    location.reload();
                }
            }
        });
    }).on('click', '.tag_type_choose', function(e){
        e.preventDefault();
        var text = $(this).text();
        $('#tag_type').html(text);
    });

	formatTip($('#qa_level_results'));
    processRelatedObjects();

});
function formatTip(tt){
    var tooltip = tt;
    
    //wrap around the current tooltip with a div
    for(var i=1;i<=3;i++){
        $('*[level='+i+']', tooltip).wrapAll('<div class="qa_container" qld="'+i+'"></div>');
    }
    //add the toggle header
    $('.qa_container', tooltip).prepend('<div class="toggleQAtip"></div>');
    $('.toggleQAtip', tooltip).each(function(){
        if ($(this).parent().attr('qld') == 5)
            $(this).text('Gold Standard Record');
        else if($(this).parent().attr('qld') == 1)
            $(this).text('Quality Level 1 - Required RIF-CS Schema Elements');
        else if($(this).parent().attr('qld') == 2)
            $(this).html('Quality Level 2 - Required Metadata Content Requirements.' );
        else if($(this).parent().attr('qld') == 3)
             $(this).html('Quality Level 3 - Recommended Metadata Content Requirements.' );
    });
    //hide all qa
    $('.qa_container', tooltip).each(function(){
        $(this).children('.qa_ok, .qa_error').hide();
    });
    
    //show the first qa that has error
    // var showThisQA = $('.qa_error:first', tooltip).parent();
    // $(showThisQA).children().show();
    
    //coloring the qa that has error, the one that doesn't have error will be the default one
    $('.qa_container', tooltip).each(function(){
        if($(this).children('.qa_error').length>0){//has an error
            //$(this).children('.toggleQAtip').addClass('hasError');
            $(this).addClass('warning');
            $('.toggleQAtip', this).prepend('<span class="label label-important"><i class="icon-white icon-info-sign"></i></span> ');
        }else{
            $(this).addClass('success');
            $('.toggleQAtip', this).prepend('<span class="label label-success"><i class="icon-white icon-ok"></i></span> ');
        }
    });
    //bind the toggle header to open all the qa inside
    $('.toggleQAtip', tooltip).click(function(){
        $(this).parent().children('.qa_ok, .qa_error').slideToggle('fast');
    });
    $('.qa_ok').addClass('success');
    $('.qa_error').addClass('warning');
}

function processRelatedObjects(offset)
{
    var rows = 10;
    if(typeof offset !== 'undefined')
    {
	// This occurs when the "Show More" button is clicked
	  $('#moreRowsNotice').remove();
    }
    else
    {
	   // Default number of related Objects to get
	   offset = 0;
    }

    $.ajax({
        type: 'GET',
        url: api_url +'registry/relationships?from_id='+$('#registry_object_id').val() + "&boost_to_origin=RelatedObject&boost_to_reverse=false&rows=" + rows + "&offset="+offset,
        dataType: 'json',
        success: function(data){
             var showRelated = 0;
             var moreToShow = '';
             maxRelated = data.total;



            $.each(data.contents, function(){
       
                var id = this.to_identifier;
                var title = this.to_title;
                // TODO need to add ro_key to relationships to find exist records in the list
                var key = this.to_key;
                var ro_class = this.to_class;
                var status = "PUBLISHED";// we don't index drafts yet!! we should though this.status;
                if(id)
                {
                    title = '<a href="' + base_url + 'registry_object/view/'+id+'">'+title+'</a> <span class="muted">(' + ro_class + ')</span>';
                }
                var newRow = "";
                var display = false;

                newRow = '<table class="subtable">' +
                    '<tr><td><table class="subtable1">'+
                    '<tr><td>Title:</td><td class="resolvedRelated" >'+title+'</td></tr>'+
                    '<tr><td class="attribute">Key</td>' +
                    '<td class="valueAttribute resolvable_key" key_value="'+ key +'">'+key+'</td>' +
                    '</tr>';


                $.each(this.relations, function(){
                    var origin = this.relation_origin;
                    var reverse = this.relation_reverse;
                    var relationship = this.relation_type
                    var add = false;
                    var revStr = '';

                    if(reverse === false && origin === 'RelatedObject')
                    {
                        revStr = "";
                        display = true;
                        add = true;
                    }
                    else if(reverse === true && origin === 'RelatedObject')
                    {
                        revStr = "<em> (Automatically generated reverse link) </em>";
                        display = true;
                        add = true;
                    }
                    else if(origin === 'PrimaryLink')
                    {
                        revStr = "<em> (Automatically generated primary link) </em>";
                        display = true;
                        add = true;
                    }
                    else if(reverse === true && (origin === 'RelatedInfo' || origin === 'Identifier'))
                    {
                        revStr = "<em> (Automatically generated reverse link by Identifier) </em>";
                        display = true;
                        add = true;
                    }
                    else if(reverse === true && origin === 'GrantsNetwork')
                    {
                        revStr = "<em> (Automatically generated reverse link by GrantsNetwork) </em>";
                        display = true;
                        add = true;
                    }
                    else if (origin === 'RelatedInfo' || origin === 'isSameAs') {
                        showRelated++;
                        // probably shouldn't do much with them... they all visible via relatedInfo
                    }

                    if(add === true){
                        showRelated++;
                        newRow += '<tr><td class="attribute">Relation:</td>' +
                            '<td class="valueAttribute"><table class="subtable1"><tr><td>type:</td><td>'+
                            relationship+revStr+'</td></tr></table></td></tr>';
                    }
                });

                if(display === true){
                    newRow  += '</table></tr></td></table>';
                    $('#related_objects_table').last().append(newRow);
                    $('#rorow').show();
                }

            });
            if(data.total > (offset + rows))
            {
                numToShow = data.total - (offset + rows);
        		moreToShow = '<table class="subtable" id="moreRowsNotice"><a id="moreRelatedObjects" />' +
                                    '<tr><td><table class="subtable1">'+
        			    '<tr><td></td><td class="resolvedRelated" > There '+ (numToShow == 1 ? "is" : "are") + ' ' + numToShow + ' more related object(s) not being displayed - <a href="#moreRelatedObjects" id="relatedObjectShowMore" data-more-length="'+Math.min(numToShow,rows)+'">show more</a></td></tr>'+
                                    '</table></tr></td></table>';
        		$('#related_objects_table').last().append(moreToShow);
        		$('#relatedObjectShowMore').on('click', function()
        		{
			    processRelatedObjects(offset + rows);
        		});
            }
                              
        }
                      
    });



}
