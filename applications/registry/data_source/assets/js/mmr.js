var selected_ids=[],selecting_status,select_all=false,processing=false,selected_some=true,num_selected_records=0;
var filters = {};
$(function() {

    bindClickables();

    $(window).hashchange(function(){
        var hash = window.location.hash;
        var hash = location.href.substr(location.href.indexOf("#"));
        var query = hash.substring(3, hash.length);
        query = decodeURIComponent(query);
        if(query) filters = jQuery.parseJSON(query);
        if(filters['sort'] === undefined){
            var sort = {}; sort['updated'] = 'desc';
            filters['sort'] = sort;
        }
        init(filters);
    });
    $(window).hashchange(); //do the hashchange on page load
});

function formatFilters(filters){
    var query_string = '';
    query_string += JSON.stringify(filters);
    return query_string;
}

function init(filters){

    var data_source_id = $('#data_source_id').val();
    if(filters['search'] && filters['search']!=''){
        $('#search_form .search-query').val(filters['search']);
        $('#search_form button').html('<i class="icon icon-remove"></i> Search');
    }
    
    //$('.pool').hide();
    //$('#status_message').removeClass('alert-error').addClass('alert-info');
    //$('#status_message').html('<em>Loading...</em> <img src="'+base_url+'assets/img/ajax-loader.gif" alt="Loading..Please wait.."/>');
    //$('#status_message').show();
    $.ajax({
        url:base_url+'data_source/get_mmr_data/'+data_source_id, 
        type: 'POST',
        dataType:'JSON',
        data: {'filters':filters},
        success: function(data){

            // If we are filtering by status, hide all other status boxes
            if($(data.statuses).size() === 1)
            {
                resetBlocks();
            }

            $.each(data.statuses, function(d){
                var template = $('#mmr_status_template').html();
                var output = Mustache.render(template, this);
                $('#'+d).html(output);
                var block = $('#'+d).parent();
                var num = parseInt($(block).attr('count'));
                if(!num){
                    $(block).attr('count', this.count);
                }else{
                    num = num + parseInt(this.count);
                    $(block).attr('count', num);
                }
                $('#'+d).parent().show();
            });

            $.each(selected_ids, function(){
                $('#'+this).addClass('active');
                $('#'+this).removeClass('active', 3000);
            });
            selected_ids = [];
            selecting_status = '';
            select_all = false;

            bindSortables();
            bindShowMore();
            initLayout();
        
            // Set the action (drop-down) icon to be select records by default
            bind_select_all();
       
            // $('.stick').sticky();
            window.setTimeout(function(){
                $('#status_message').removeClass('alert-error').addClass('alert-info');
                $('#status_message').fadeOut();
            }, 8000);

        }
    });

}

function resetBlocks()
{
    $('.pool .block').each(function(){
        $(this).hide();
        $(this).removeAttr('count');
    });
}

function bindShowMore(){
    $('.show_more').click(function(){
        var ds_id = $(this).attr('ds_id');
        var offset = parseInt($(this).attr('offset'));
        var status = $(this).attr('status');
        var button = this;
       // var filter = JSON.stringify(filters, null, 2);

        $.ajax({
            url:base_url+'data_source/get_more_mmr_data/', 
            type: 'POST',
            data: {ds_id:ds_id,offset:offset,filters:filters,status:status},
            success: function(data){
                if(data){
                    new_offset = offset+10;
                    $(button).attr('offset', new_offset);

                    var template = $('#mmr_data_more').html();
                    var output = Mustache.render(template, data);
                    $('ul[status='+status+']').append(output);
                    if(!data.hasMore) $(button).remove();
                }
                initLayout();
                bindClickables();
                bindSortables();
            }
        });
    });
}


function initLayout(){

    // var spare = [];
    // var remain = 95;
    // $('.block:visible').each(function(){
    //     if($(this).attr('count')==0){
    //         $(this).width('20%');
    //         remain = remain - 20;
    //     }else{
    //         spare.push(this);
    //     }
    // });

    // $(spare).each(function(){
    //     var percentage = Math.ceil(remain / spare.length);
    //     $(this).width(percentage+'%');
    // });




    var numBlock = $('.block:visible').length;
    var percentage = Math.ceil(95 / numBlock);
    $('.block').width(percentage+'%');

    var max_height = 0;
    $('.block').height('auto');
    $('.block').each(function(){
        if($(this).height() > max_height) max_height = $(this).height();
    });
    $('.pool').height(max_height+50);

    if(numBlock==1){
        $('.pool').width('65%')
    }else $('.pool').width('100%');
    //$('.ro_box[status=SUBMITTED_FOR_ASSESSMENT], .ro_box[status=APPROVED], .ro_box[status=ASSESSMENT_IN_PROGRESS],.ro_box[status=PUBLISHED]').height(max_height);
    //var draft_height = $('.ro_box[status=DRAFT]').height() + max_height - $('.ro_box[status=DRAFT]').parent('.block').height();
    // $('.ro_box[status=DRAFT]').height(draft_height);
    // 
    // 


    $('#search_form').unbind('submit').submit(function(e){
        e.preventDefault();
        e.stopPropagation();
        var search_term = $('input', this).val();
        if (search_term != '' && filters['search']!=search_term)
        {
            $('button', this).html('<i class="icon icon-remove"></i> Search');
        }
        else
        {
            $('button', this).html('Search');
            $('input', this).val('');
            search_term = '';
        }

        filters['search']=search_term;
        changeHashTo(formatFilters(filters));
    })
    $('#search_form .search-query').on('keypress', function(){ $('button', $(this).parent()).html('Search'); });

    //init filters
    $('.sort').find('span').removeClass('icon-chevron-down').removeClass('icon-chevron-up');
    $(filters['sort']).each(function(){
        $.each(this, function(key, value){
            var direction = '';
            if(value=='asc'){
                direction = 'up';
            }else if(value=='desc'){
                direction = 'down';
            }
            $('.sort[sort='+key+']').attr('value', value).find('span').addClass('icon-chevron-'+direction);
        });
    });

    $('.sort').unbind('click').click(function(){
        var value = $(this).attr('value');
        var sort = $(this).attr('sort');
        if(value=='desc'){
            value = 'asc';
        }else if(value=='asc'){
            value='desc';
        }else{
            value='desc';
        }
        var sorting = {};
        sorting[sort] = value;
        filters['sort'] = sorting;
        changeHashTo(formatFilters(filters));
    });

    $('#active_filters').html('');
    if(filters['filter'] && filters['filter'].length > 0){
        $('#active_filters').append('<em>Active Filters: </em>');
    }

    $(filters['filter']).each(function(){
        $.each(this, function(key, value){
            var text = value;
            if(key==='tag'){
                text = 'Records with Tags';
            }else if(key==='flag'){
                text = 'Flagged Records';
            }else if(key==='quality_level'){
                if(value == 4)
                text = 'Gold Standard Records';
                else
                text = 'Quality Level '+value;
            }

            // Replace tag text with the "readable" version from the filter dropdown
            if(key==='status' || key==='class')
            {
                text = $('.filter[value='+value+']').html();
		if (typeof(text) !== 'undefined' && text !== null) {
		    text = text.replace(/ \(.*/,'');
		}
		else {
		    text = "Remove current filter";
		}
            }

            $('#active_filters').append('<span class="btn removeFilter" name="'+key+'"><a href="javascript:;">'+text+' <i class="icon icon-remove"></i></a></span>');
        });
    });

    $('.removeFilter').unbind('click').click(function(){
        var name = $(this).attr('name');
        delete filters['filter'][name];
        changeHashTo(formatFilters(filters));
    });

    $('.filter').unbind('click').click(function(e){
        if(!$(this).closest('li').hasClass('disabled')){
            var name = $(this).attr('name');
            var value = $(this).attr('value');
            var filter = {}; filter[name] = value;
            if(filters['filter']){
                filters['filter'][name] = value;
            }else filters['filter'] = filter;
            changeHashTo(formatFilters(filters));
        }else{
            e.preventDefault();
            e.stopPropagation();
        }
    });

    $('.contextmenu').click(function(e){
            e.preventDefault();
            e.stopPropagation();

            if($(this).closest('li').length==1) {
                click_ro($(this).closest('li'),'select');
            }
            var context_status = $(this).attr('status');
            $(this).qtip({
                content: {
                    text: 'Loading...',
                    ajax:{
                        url: base_url+'data_source/get_mmr_menu',
                        type: 'POST',
                        data: {data_source_id:$('#data_source_id').val(),status:context_status,affected_ids:selected_ids,selecting_status:selecting_status},
                        dataType: 'html'
                    },
                    onRender: function() {
                        $('a', this.elements.content).click(function(){$(this).hide();});
                    }
                },
                position: {viewport: $(window), my:'left center', at:'right center'},
                show:{ready:true,effect:false,event:'click'},
                hide:{event:'unfocus'},
                style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap'}
            });
        });

    $('.selector_menu').qtip({
        content:{
            text: function(){
                return $('.selecting_menu',this).html();
            },
            onRender: function() {
                $('a', this.elements.content).click(this.hide());
            }
        },
        position: {viewport: $(window), my:'left center', at:'right center'},
        show:{ready:false,effect:false,event:'click'},
        hide:{event:'unfocus'},
        style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap'}
    });

    $('.selector_btn').die().live({
        click:function(){
            var status = $(this).attr('status');
            var list = $('.sortable[status='+status+']');
            if($(this).hasClass('select_all')){
                action_list(status, 'select_all');
            }else if($(this).hasClass('select_display')){
                action_list(status, 'select_display');
            }else if($(this).hasClass('select_none')){
                action_list(status, 'select_none');
            }else if($(this).hasClass('select_flagged')){
                action_list(status, 'select_flagged');
            }
            $(".selector_menu").qtip("hide");
        }
    });

    if(select_all){
        var list = $('.sortable[status='+select_all+']');
        $.each($('li.ro_item', list), function(index, val) {
            $(this).addClass('ro_selected');
        });
    }

    $('.status_field:visible').each(function(){
        var total = $('li.ro_item',this).length;
        $('.select_display span', this).html(total);
    });

   

    $('.tipQA').on('mouseover', function(){
        $(this).qtip({
            content: {
                text: 'Loading...', // The text to use whilst the AJAX request is loading
                title: {
                    text: 'Quality Report',
                    button: 'Close'
                },
                ajax: {
                    url: base_url+'registry_object/get_quality_view/', 
                    type: 'POST',
                    data: {ro_id: $(this).attr('ro_id')},
                    loading:false,
                    dataType: 'text',
                    success: function(data, status) {
                        this.set('content.text', data);
                        formatTip(this);
                    }
                }
            },
            position: {viewport: $(window), my:'left center'},
            show: {
                //event: 'click',
                ready: true,
                solo:true,
                effect: function(offset) {
                    $(this).show(); // "this" refers to the tooltip
                }
            },
            hide: {
                fixed:true,
                delay: 1200
            },
            style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap'},
            overwrite: false
        });
    });

    $('.tipError').on('mouseover', function(){
        $(this).qtip({
            content: {
                text: 'Loading...', // The text to use whilst the AJAX request is loading
                title: {
                    text: 'Errors',
                    button: 'Close'
                },
                ajax: {
                    url: base_url+'registry_object/get_validation_text/', 
                    type: 'POST',
                    data: {ro_id: $(this).attr('ro_id')},
                    loading:false,
                    dataType: 'text',
                    success: function(data, status) {
                        this.set('content.text', data);
                        $('.quality-test-results span').hide();
                        $('.quality-test-results span.error').css({display:'block'}).show();
                    }
                }
            },
            position: {viewport: $(window), my:'left center'},
            show: {
                //event: 'click',
                ready: true,
                solo:true,
                effect: function(offset) {
                    $(this).show(); // "this" refers to the tooltip
                }
            },
            hide: {
                fixed:true,
                delay: 800
            },
            style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap'},
            overwrite: false
        });
    });

}

function formatTip(tt){
    var tooltip = $('#ui-tooltip-'+tt.id+'-content');
    
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
    var showThisQA = $('.qa_error:first', tooltip).parent();
    $(showThisQA).children().show();
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
        $(this).parent().children('.qa_ok, .qa_error').slideToggle('fast', function(){
            tt.reposition();//fix the positioning
        });
    });
    $('.qa_ok').addClass('success');
    $('.qa_error').addClass('warning');
}


function action_list(status, action){
    var list = $('ul[status='+status+']');
    selecting_status = status;
    if(action=='select_display'){
        $('.sortable li').removeClass('ro_selected');
        $.each($('li.ro_item', list), function(index, val) {
            $(this).addClass('ro_selected');
        });
    }else if(action=='select_none'){
        selecting_status = '';
        select_all = false;
        $('.ro_selected').removeClass('ro_selected');
    }else if(action=='select_all'){
        $('.sortable li').removeClass('ro_selected');
        select_all = status;
        $.each($('li.ro_item', list), function(index, val) {
            $(this).addClass('ro_selected');
        });
    }else if(action=='select_flagged'){
        $('.sortable li').removeClass('ro_selected');
        $.each($('li.ro_item.flagged', list), function(index, val) {
            $(this).addClass('ro_selected');
        });
    }
    selected_ids = $.unique(selected_ids);
    update_selected_list(status);
}

function update_selected_list(status){
    selected_ids = [];
    selecting_status = status;
    $('.ro_selected').each(function(){
        selected_ids.push($(this).attr('id'));
    });

    var num = selected_ids.length;
    if(select_all)
    {
        num = parseInt($('#'+status+' .count').html());
        num -=  $('.sortable[status='+select_all+'] li:not(.ro_selected)').length;
    }  
    num_selected_records = num;

    var list = $('.ro_box[status='+status+']');
    // var selected = $('div.selected_status', list);
    var selected = $('#status_message');
    if(num>0){
        bind_get_options_menu();
        var text = num + ' records selected.';
        selected.html(text);
        selected.show();
    }else{
        select_all = false;
        bind_select_all();
        selected.hide(50);
    }
}

function click_ro(ro_item, action){
    var ro_id = $(ro_item).attr('id');
    var status = $(ro_item).attr('status');

    if(selecting_status!=status){
        $('.sortable li').removeClass('ro_selected');
    }

    if(action=='toggle'){
        $('#'+ro_id).toggleClass('ro_selected');
    }else if(action=='select'){
        $('#'+ro_id).addClass('ro_selected');
    }else if(action=='select_1'){
        $('.sortable li').removeClass('ro_selected');
        $('#'+ro_id).addClass('ro_selected');
    }else if(action=='select_until'){
        if(selecting_status==status){
            var prev = $('#'+ro_id).prevAll('.ro_selected').attr('id');
            if(prev){
                $('#'+ro_id).prevUntil('#'+prev).addClass('ro_selected');
            }else if(until = $('#'+ro_id).nextUntil('.ro_selected')){
                $(until).addClass('ro_selected');
            }
        }
        $('#'+ro_id).addClass('ro_selected');
    }

    selected_ids = $.unique(selected_ids);
    update_selected_list(status);
}


function bindSortables(){

    $('.sortable li').draggable('destroy');

    $('.sortable').each(function(){
        var status = $(this).attr('status');
        var from = '.sortable[status='+status+'] li';


        // Multiple connectors (ASSESSMENT needs to be able to be dragged in both directions)
        var connect_to = $(this).attr('connect_to').split(",");
        var target = $();

        $(connect_to).each(function(){
            target = target.add('.sortable[status="'+this+'"]');
        });

        var ds_id = $('#data_source_id').val();

        $('li.ro_item', this).draggable({
            cursor: "move",cursorAt:{top:-5,left:-5},scroll:true,
            helper: function(e){
                var list = $(this).parents('.status_field');
                return $( "<span class='label label-info helper'>"+ num_selected_records+"</span>" );
            },
            start: function(e, ui){
                if(e.shiftKey){
                    click_ro(e.currentTarget, 'select_until');
                }else{
                    click_ro(e.currentTarget, 'select');
                }
                $(ui.helper[0]).html(num_selected_records + " record" + (num_selected_records == 1 ? "" : "s") )
            },
            connectToSortable: target
        });

        $('li', this)
        .bind('contextmenu', function(){return false;})
        .bind('mouseover',function(){
            $('.right-menu', this).show();
            $('.toolbar', this).css({opacity:1.0});
        })
        .bind('mouseout',function(){
            $('.right-menu', this).hide();
            $('.toolbar', this).css({opacity:0.2});
        });

        $(target).parents('.status_field').droppable({
            accept: from,
            hoverClass:"droppable",
            drop: function( event, ui ) {
                if(selecting_status==status){
                    var attributes = [{
                        name:'status',
                        value:$('.ro_box', this).attr('status') // what status are we actually dropping on?
                    }];
                    if(!processing) update(selected_ids, attributes);
                }
            }
        });
    });
}


function bind_get_options_menu()
{
    if (!selected_some) 
    {
        $('.primarycontextmenu').qtip("destroy").click(function(e){
            e.preventDefault();
            var context_status = $(this).attr('status');

            $(this).qtip({
                content: {
                    text: 'Loading...',
                    ajax:{
                        url: base_url+'data_source/get_mmr_menu',
                        type: 'POST',
                        data: {data_source_id:$('#data_source_id').val(),status:context_status,affected_ids:selected_ids,selecting_status:selecting_status},
                        dataType: 'html'
                    },
                    onRender: function() {
                        $('a', this.elements.content).click(this.hide());
                    }
                },
                position: {viewport: $(window), my:'left center', at:'right center'},
                show:{ready:true,effect:false,event:'click'},
                hide:{event:'unfocus'},
                style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap'}
            });
        });
        selected_some=true;
    }
}


function bind_select_all()
{
    if (selected_some)
    {
        $('.primarycontextmenu').qtip("destroy").unbind('click').qtip({
            content:{
                text: function(){
                    return $('.selecting_menu',this.parent().parent()).html();
                },
                onRender: function() {
                    $('a', this.elements.content).click(function(){$(this).hide()});
                }
            },
            position: {viewport: $(window), my:'left center'},
            show:{ready:false,effect:false,event:'click'},
            hide:{event:'unfocus'},
            style: {classes: 'ui-tooltip-shadow ui-tooltip-bootstrap'}
        });
        selected_some=false;
    }
}

function bindClickables()
{
    $(document).off('mouseup dblclick click', '.sortable li');
    $(document).off('click','.op');

    $(document).on('mouseup', '.sortable li', function(e){
        if(e.which==3){
            e.preventDefault();
            $('.contextmenu',this).click();
        }
    }).on('dblclick', '.sortable li', function(e){
        if ($(this).attr('id'))
        {
            window.location = base_url+'registry_object/view/'+$(this).attr('id');
        }
    }).on('click','.sortable li',function(e){
        if(e.metaKey || e.ctrlKey){
            click_ro(this, 'select');
        }else if(e.shiftKey){
            click_ro(this, 'select_until');
        }else{
            if(!$(this).hasClass('ro_selected')){
                click_ro(this, 'select_1');
            }else{
                click_ro(this, 'toggle');
            }
        }
    });


    $(document).on('click', '.op', function(e){

        var action = $(this).attr('action');
        var status = $(this).attr('status');
        if(processing) return;

        switch(action){
            case 'select_all':
                action_list(status, 'select_all');
                break;
            case 'select_none':
                action_list(status, 'select_none');
                break;
            case 'to_draft':
                var attributes = [{
                    name:'status',
                    value:'DRAFT'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'to_submit':
                var attributes = [{
                    name:'status',
                    value:'SUBMITTED_FOR_ASSESSMENT'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'to_assess':
                var attributes = [{
                    name:'status',
                    value:'ASSESSMENT_IN_PROGRESS'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'to_approve':
                var attributes = [{
                    name:'status',
                    value:'APPROVED'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'to_publish':
                var attributes = [{
                    name:'status',
                    value:'PUBLISHED'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'to_moreworkrequired':
                var attributes = [{
                    name:'status',
                    value:'MORE_WORK_REQUIRED'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'delete':
                if($(this).attr('ro_id')){
                    if(confirm('Are you sure you want to delete this record?' + "\n" + "NOTE: Non-PUBLISHED records cannot be recovered once deleted.")){
                        deleting = [$(this).attr('ro_id')];
                        delete_ro(deleting, false);
                    }
                }else{
                    if(select_all){
                        var num = parseInt($('#'+status+' .count').html());
                        num -=  $('.sortable[status='+select_all+'] li:not(.ro_selected)').length;
                        if(confirm('Are you sure you want to delete '+num+' records?' + "\nNOTE: Non-PUBLISHED records cannot be recovered once deleted.")){
                            delete_ro(false, select_all, data_source_id);
                        }
                    }else{
                        if(confirm('Are you sure you want to delete '+selected_ids.length+' records?' + "\nNOTE: Non-PUBLISHED records cannot be recovered once deleted.")){
                            delete_ro(selected_ids, false);
                        }
                    }
                }
                break;
            case 'flag':
                var attributes = [{
                    name:'flag',
                    value:'t'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'un_flag':
                var attributes = [{
                    name:'flag',
                    value:'f'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'set_gold_status_flag':
                var attributes = [{
                    name:'gold_status_flag',
                    value:'t'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'un_set_gold_status_flag':
                var attributes = [{
                    name:'gold_status_flag',
                    value:'f'
                  }];
                  update(selected_ids, attributes);
                break;
            case 'view':
                if($(this).attr('ro_id')){
                    window.location = base_url+'registry_object/view/'+$(this).attr('ro_id');
                }else window.location = base_url+'registry_object/view/'+selected_ids[0];
                break;
            case 'edit':
                if($(this).attr('ro_id')){
                    window.location = base_url+'registry_object/edit/'+$(this).attr('ro_id')+'#!/advanced/admin';
                }else window.location = base_url+'registry_object/edit/'+selected_ids[0]+'#!/advanced/admin';
                break;
            case 'advance_status':
                var status_to = $(this).attr('to');
                if (status_to.search(/,/) >= 0)
                {
                    status_to.split(/,/).pop();
                }
                var attributes = [{
                    name:'status',
                    value:status_to
                  }];
                var updating = [$(this).attr('ro_id')];
                  update(updating, attributes);
                break;
            case 'manage_deleted_records':
                var data_source_id = $(this).attr('data_source_id');
                window.location = base_url+'data_source/manage_deleted_records/'+data_source_id;
                break;
        }

    });
}



function update(ids, attributes){
    processing = true;
    // $(".qtip").qtip("api").hide();
    if(select_all){
        ids = select_all;
        url = base_url+'registry_object/update/all';

        // specifically exclude deselected records!
        var excluded_records = []
        $('.sortable[status='+select_all+'] li:not(.ro_selected)').each(function()
        {
            excluded_records.push($(this).attr('id'));
        });

        data = {data_source_id:$('#data_source_id').val(),filters:filters,select_all:select_all,excluded_records:excluded_records,attributes:attributes};
        total = parseInt($('#'+select_all+' .count').html());
        total -=  $('.sortable[status='+select_all+'] li:not(.ro_selected)').length;
    }else{
        url = base_url+'registry_object/update/'
        data = {affected_ids:ids, attributes:attributes, data_source_id:$('#data_source_id').val()};
        total = selected_ids.length
    }
    $("html, body").animate({ scrollTop: 0 }, "slow");
    var text = total+' records updating...<img src="'+base_url+'assets/img/ajax-loader.gif" alt="Loading..Please wait.."/>';
    $('#status_message').html(text).show();
    $.ajax({
        url:url, 
        type: 'POST',
        data: data,
        dataType: 'JSON',
        success: function(data){
            processing = false;
            if(data.status=='error'){
                $('#status_message').removeClass('alert-info').addClass('alert-error');
                $('#status_message').html(data.error_message);
            }else if(data.status=='success'){
                $('#status_message').hide();
                if(data.error_count != '0'){
                    $('#status_message').removeClass('alert-info').addClass('alert-error');
                    $('#status_message').html(data.error_message).show();
                }
                else{
                   $('#status_message').removeClass('alert-error').addClass('alert-info');
                   var data_success_message = $('<div>'+data.success_message+'</div>').text()
                   if(data_success_message!='') $('#status_message').html(data.success_message).show();
                }

                init(filters);
            }
        }
    });
}


function delete_ro(ids, selectAll){
    var data_source_id = $('#data_source_id').val();
    var excluded_records = []

    $('.sortable[status='+select_all+'] li:not(.ro_selected)').each(function()
    {
        excluded_records.push($(this).attr('id'));
    });

    $.ajax({
        url:base_url+'registry_object/delete/', 
        type: 'POST',
        data: {affected_ids:ids, filters:filters, select_all:selectAll, excluded_records: excluded_records, data_source_id:data_source_id},
        success: function(data){
            init(filters);
        }
    });
}