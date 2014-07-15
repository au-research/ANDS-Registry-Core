$(document).ready(function() {

	initTips();

	$('#search_box').typeahead({
		name:'Search Suggestion',
		remote: base_url+'search/suggest/?q=%QUERY'
	}).on('typeahead:selected', function(){
		window.location = base_url+'search/#!/q='+encodeURIComponent($('#search_box').val());
	});
	$('.inner .twitter-typeahead').attr('style', '');

	if ($.browser.msie && $.browser.version <= 9.0) {
		$('#who_contributes li').css({
			float:'left',
			width:'310px',
			listStyleType:'none'
		});
		$('#who_contributes').addClass('clearfix');
	}

	$('.text_select').each(function() {
		var $this = $(this),
			$input = $this.find('input'),
			$ul = $this.find('ul'),
			$span = $this.find('.default_value');
		$input.val('');	
		var emptyValue = $span.text();
		$('<li />').text(emptyValue).prependTo($ul);
		$this.click(function() {
			$ul.slideDown();
			$this.addClass('current');				
		});
		$this.mouseleave(function() {
			$ul.slideUp('fast');
			$this.removeClass('current');				
		});			
		$ul.find('li').click(function() {
			var value = $(this).text();
			if(value!=emptyValue) {
				$input.val($(this).text());
				$span.hide();
			} else {
				$input.val('');
				$span.show();				
			}
		});
	});


    $('#clear_search').click(function() {
    	var $form = $(this).parents('form');
    	$form.find('input[type="text"]').val('');
    	$form.find('input[type="checkbox"]').removeAttr('checked');
    	$form.find('option').attr('selected', false);
    	$form.find('select').find('option').first().attr('selected', true);
		$("#slider").editRangeSlider("min",1544);
		$("#slider").editRangeSlider("max",2012);
		$("#slider").hide();
    	return false;
    });

    $('#show_dropdown').click(function() {
        $("#export_dropdown").toggle();
        return false;
    });

    $('.login_st').toggle(function(){
        $('div.qtip:visible').qtip('hide');
    	$('.login_banner').slideDown();
    	$(this).addClass('exped');
    }, function(){
        $('div.qtip:visible').qtip('hide');
		$('.login_banner').slideUp('fast');
		$(this).removeClass('exped');
    });

    $('.login_close').click(function(){
    	$('.login_banner').slideUp('fast');
    });

    $('.tags_helper').qtip({
    	content:{
    		text:'<p>\'User Contributed Tags\' are terms added to records by Research Data Australia users to assist discovery of these records by themselves and others. By clicking on an added tag you can discover other related records with the same tag.</p><p> In order to tag a record you must first login to Research Data Australia. Tags can be any string you choose but should be meaningful and have relevance to the record the tag is being added to. To assist you in assigning a tag, previously used tags and terms from the ANZSRC Fields of research (FOR) and Socio-economic objective (SEO) vocabularies are offered via autocomplete suggestions.</p>'
    	},
    	show:'mouseover',
    	hide:'mouseout',
    	style:{classes:'ui-tooltip-light ui-tooltip-shadow'}
    });
    $('.endnote_helper').qtip({
        content:{
            text:'Export help'
        },
        show:'mouseover',
        hide:'mouseout',
        style:{classes:'ui-tooltip-light ui-tooltip-shadow'}
    });
    $('#ad_st').toggle(function() {
        $('div.qtip:visible').qtip('hide');
	//don't init slider until we show the advanced search slidedown
		$("#slider").editRangeSlider({
	    	    scales: [
			// Primary scale
			{
			    first: function(val){ return val; },
			    next: function(val){ return val + 50; },
			    stop: function(val){ return false; },
			    label: function(val){ return val; }
			}],
	    	    bounds:{min: 1544, max: 2012},
	    	    defaultValues:{min: 1544, max: 2012},
	    	    valueLabels:"change",
	    	    type:"number",
	    	    arrows:false,
	    	    delayOut:200
		});

        $('a.adv_note').qtip({
          content: {
	    title: 'Search notes',
	    text: $('#adv_note_content')
	  	},
          show: 'mouseover',
          hide: 'mouseout',
          style: {
            classes: 'ui-tooltip-light ui-tooltip-shadow'
          }
	});

    	$(this).addClass('exped');
    	$('.advanced_search').slideDown();
    	$("#slider").editRangeSlider("valueLabels","hide");
    	$("#slider").editRangeSlider("resize");
     	return false;
    }, function() {
     	$(this).removeClass('exped');
    	$('.advanced_search').slideUp('fast');
    	return false;
    });

    $('a.adv_note').on('click', function(e) { e.preventDefault(); });

    $('.ad_close > a').on('click', function(e){ e.preventDefault(); $('#ad_st').click(); });

    $('#searchTrigger').on('click', function(){
    	window.location = base_url+'search/#!/q='+encodeURIComponent($('#search_box').val());
    });
    $('#search_box').keypress(function(e){
		if(e.which==13){//press enter
			window.location = base_url+'search/#!/q='+encodeURIComponent($(this).val());
		}
	});

	$('#search_map_toggle').click(function(e){
		window.location = base_url+'search/#!/map=show';
	});

	$('#adv_start_search').click(function(e){
		e.preventDefault();
		var q = '';
		var all = $('.adv_all').val();
		var input = $('.adv_input').val();
		var nots = $('.adv_not');
		var not = '';
		$.each(nots, function(e){
			var v = $(this).val();
			if(v!='')not +='-'+v+' ';
		});
		if(all!='') q +='"'+all+'" ';
		q += input+ ' '+not;
		var tab = $('#record_tab').val();
		
		var url = base_url+'search/#!/q='+q+'/tab='+tab;
		if($('#rst_range').prop('checked')){
			var temporal = $("#slider").editRangeSlider("values");
			url += '/temporal='+Math.round(temporal.min)+'-'+Math.round(temporal.max);
		}
		window.location = url;
	});

	$('#slider').hide();
	$('#rst_range').on('change',function(){
		$('#slider').toggle();
		$('#slider').editRangeSlider('resize');
	});

	$(document).on('click', '.ro_preview_header', function(e){
    	e.preventDefault();
    	$(this).next('.ro_preview_description').slideToggle();
	});


	$('#contact-send-button').live({

		click: function(e){
			clear = true;
			$.each($('.verify'), function(){
				if($(this).val()=='') {
					clear=false;
					 $(this).qtip({
        				content:$(this).attr('title'),
        				style: {classes: 'ui-tooltip-light ui-tooltip-shadow seealso-tooltip',width: '250px'},
						show:{ready:'true'},
						hide:{event:'focus'},
    				}); 
				}
				else
				{
					$(this).qtip("disable");
				}
				
			});
			if($('#contact-email').val()!='')
			{
				if($('#contact-email').val()!='' && !validateEmail($('#contact-email').val()))
				{
				 	clear=false;
				 	$('#contact-email').qtip({
        			content:"The provided email address was not valid",
        			style: {classes: 'ui-tooltip-light ui-tooltip-shadow seealso-tooltip',width: '250px'},
					show:{ready:'true'},
					hide:{event:'focus'},
    				}); 
    			}
    			else
    			{
    				$('#contact-email').qtip("disable");
				
    			}					
			}	

			if(clear){ 
		 	$.ajax({
		  		type:"POST",
		  		url: base_url+"/home/contact/?sent=true",
		  		data:"name="+$('#contact-name').val()+"&email="+$('#contact-email').val()+"&content="+$('#contact-content').val(),   
		  			success:function(msg){
		  				$('#contact-us-form').html(msg);
		  			},
		  			error:function(msg){
		  			}
	  			}); 
			}
			else
			{
				return false;
			}
		}
	});

	$("#grant-query-form").submit(function(e){
		e.preventDefault(); //STOP default action
		$.ajax({
	  		type:"POST",
	  		url: base_url+"home/requestGrantEmail",
	  		data: $("#grant-query-form").serialize(),   
	  			success:function(msg){
	  				$('#message').html(msg);
	  			},
	  			error:function(msg){
	  				$('#message').html(msg);
	  			}
  			});
	    return false; 
	});

$(document).on('click', '.sharing_widget', function(){
	addthis.init();
	$(this).remove();
});

$('#moreCodeVersions').on('click', function(){
	$(this).hide();
	$(this).siblings().fadeIn();
});

window.ATL_JQ_PAGE_PROPS =  {
    "triggerFunction": function(showCollectorDialog) {
      //Requries that jQuery is available!
        jQuery(".myCustomTrigger").click(function(e) {
            e.preventDefault();
            showCollectorDialog();
    });
}};

function validateEmail(email) 
{
    var re = /\S+@\S+\.\S+/;


     return re.test(email);	


    
}
	function recurseGetText() { 
		if (this.nodeType == 3)
		{
			return this.nodeValue;
		}
		else if (this.nodeType == 1 && this.nodeName.toLowerCase() == "br")
		{
			return '<br/>';
		}
		else
		{
			if (typeof $(this).contents == 'function' && $(this).contents().length > 0)
			{
				return $(this).contents().map(recurseGetText).get().join(' ');
			}
		}
		return this.nodeType == 3 ? this.nodeValue : undefined;
	}

	// get any text inside the element $(this).directText()
	$.fn.directText=function(delim) {
	  if (!delim) delim = ' ';
	  return this.contents().map(recurseGetText).get().join(delim);
	};
	//setTimeout(function(){alert("Hello")},3000)
});
// usage: log('inside coolFunc',this,arguments);
// http://paulirish.com/2009/log-a-lightweight-wrapper-for-consolelog/
window.log = function(){
  log.history = log.history || [];   // store logs to an array for reference
  log.history.push(arguments);
  if(this.console && deployment_state!== undefined && deployment_state=='development'){
    console.log( Array.prototype.slice.call(arguments) );
  }
};

function initTips(selector){
	var qSelector = $('*[tip]');
	if (selector)
	{
		qSelector = $(selector);
	}
	qSelector.qtip({
		content: {
			text: function(api) {
				// Retrieve content from custom attribute of the $('.selector') elements.
				return $(this).attr('tip');
			}
		},
		position:{my:'left center', at:'right center', viewport: $(window)},
		style: {
	        classes: 'ui-tooltip-light'
	    }
	});
}

/* Not used currently, but would be better than scattered strings... :-( 
function initExplanations()
{
	var explanations = {}
	explanations["collection"] = "Research dataset or collection of research materials.";
	explanations["party"] = "Researcher or research organisation that creates or maintains research datasets or collections.";
	explanations["services"] = "Service that supports the creation or use of research datasets or collections.";
	explanations["activities"] = "Project or program that creates research datasets or collections.";
}*/

// decode htmlentities()
function htmlDecode(value) {
	return (typeof value === 'undefined') ? '' : $('<div/>').html(value).text();
}

function generatePreviewTip(element, slug, registry_object_id, relation_type, relation_description, relation_url)
{
    var preview_url;
    if (slug != null)
    {
        preview_url = base_url + "preview/" + slug;
        //alert(preview_url)
    }
    else
    {
        preview_url = base_url + "preview/?registry_object_id=" + registry_object_id;
    }
    /* Prepare the tooltip preview */
    $('a', element).qtip({
        content: {
            text: 'Loading preview...',
            title: {
                text: 'Preview',
                button: 'Close'
            },
            ajax: {
                url: preview_url, 
                type: 'GET',
               // data: { "slug": slug, "registry_object_id": registry_object_id },
                success: function(data, status) {
                    data = $.parseJSON(data);        
                    // Clean up any HTML rubbish...                   
                    var temp = $('<span/>');
                    temp.html(data.html);
                    $("div.descriptions", temp).html($("div.descriptions", temp).text());
                    $("div.descriptions", temp).html($("div.descriptions", temp).directText());

                    if (data.slug){
                        $('.viewRecord',temp).attr("href", base_url + data.slug);
                    }
                    else
                    {
                        $('.viewRecord').attr("href",base_url+"view/?id=" + data.registry_object_id);
                    }
                    this.set('content.text', temp.html());   



                    if (data.slug){
                        $('.viewRecordLink'+data.slug).attr("href",base_url + data.slug);
                        $('.viewRecord').attr("href", base_url + data.slug);
                        if(relation_type){
                            var relDesc = '';
                            if(relation_description)
                            {
                                relDesc = ' <br /><span style="color:#666666"><em>' + relation_description +'</em></span>'
                            }
                            var relUrl = '';
                            if(relation_url)
                            {
                                relUrl = ' <a href="' + relation_url +'" target="_blank"><em>(URL)</em></a></span>'
                            }
                         $('.previewItemHeader'+data.slug).html(relation_type + relDesc + relUrl);
                        }                       

                    }else{
                        $('.viewRecordLink'+data.registry_object_id).attr("href",base_url+"view/?id=" + data.registry_object_id);
                        if(relation_type){
                            if(relation_description)
                            {
                                relDesc = ' <br /><span style="color:#666666"><em>' + relation_description +'</em></span>'
                            }
                            var relUrl = '';
                            if(relation_url)
                            {
                                relUrl = ' <a href="' + relation_url +'" target="_blank"><em>(URL)</em></a></span>'
                            }                            
                            $('.previewItemHeader'+data.registry_object_id).html(relation_type + relDesc + relUrl);
                        }
                    }                   
                } 
            }
        },
        position: {
            my: 'left center',
            at: 'right center',
            viewport: $(window)
        },
        show: {
            event: 'click',
        },
        hide: {
            delay: 1000,
            fixed: true,
        },
        style: {
            classes: 'ui-tooltip-light ui-tooltip-shadow previewPopup',
            width: 550
        },
    }).on('click', function(e){e.preventDefault();return false;});
}

function ellipsis (string, length){
	if (string.length <= length){
		return string;
	}else{
		var trimmedString = string.substr(0, length-3);
		trimmedString = trimmedString.substr(0, Math.min(trimmedString.length, trimmedString.lastIndexOf(" "))) + '&hellip;';
		// return trimmedString + '<span class="showmore_excerpt"><br /><a href="javascript:void(0);">More &hellip;</a></span>';
		return trimmedString;
	}
}

// These helpers have dependencies outside of document.ready state, so declare them here instead.
function getURLParameter(name) 
{
    return unescape(
        (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
    );
}

// Check whether parameter is present in URL (including non-value params, i.e. ?isAlive&foo=bar)
function checkURLParameterExists(name)
{
	return (RegExp('(^|\\\\?|&)' + name + '(=.*?|&|$)').exec(location.search))
}