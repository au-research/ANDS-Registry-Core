// Pseudo-namespace with automatic evaluation. 
(function(){
	var WIDGET_NAMESPACE = "ands_widget";
	
	var aStrDefs = {
		
		ands_search_query:"*:*",
		ands_search_query_mode:"query", // alt. "facet"
		ands_search_query_facet:"",
		ands_search_query_facet_incl_count:"",
		
		ands_search_sort:"",
		
		ands_search_service_point:"http://services.ands.org.au/home/orca/rda/api",
		ands_search_portal_url:"http://services.ands.org.au/home/orca/rda/",
		
		ands_search_widget_type:"right-box",
		
		ands_search_width:"",
		ands_search_height:"",
		
		ands_search_record_limit:10,
		
		ands_search_include_description:false,
		ands_search_title:"",
		ands_search_desc:"",
		ands_search_heading:"Search Results",
		ands_search_bg:"#fff",
		ands_search_border:"#999"		
	}
	
	function makeWidget(ands_widget_reference_id)
	{
		var win = window,
		    doc = document;
		
		initVars(win, ands_widget_reference_id);
		
		var ands = win[WIDGET_NAMESPACE][ands_widget_reference_id];
		
		g = "<div class='ands_widget_wrapper " + ands.ands_search_widget_type + "' id='ands_search_widget_" + ands.ands_widget_reference_id + "'" 
			+ ' style="position:relative;' 
			+ (ands.ands_search_bg != "" ? 'background-color:' + ands.ands_search_bg +';' : '')
			+ (ands.ands_search_border != "" ? '' + ands.ands_search_border +';' : '')
			+ (ands.ands_search_width != "" ? 'width:' + ands.ands_search_width + 'px;' : "")
			+ (ands.ands_search_height != "" ? 'height:' + ands.ands_search_height + 'px;' : "")
			+ '">'
			+ (ands.ands_search_heading != "" ? "<h2>" + ands.ands_search_heading + "</h2>" : "")
			+ "<div class='ands_search_widget_results'>"
			+ "<ul>"
			+ "</ul>"
			+ "</div>" 
			+ '</div>';

		doc.write(g);
		
		var search_params = '?';
		if (ands.ands_search_query_facet != "")
		{
			search_params += 'q='+encodeURIComponent(ands.ands_search_query);
			search_params += '&rows=0'; // no rows output in facet mode
			search_params += '&indent=on&wt=json';
			search_params += (ands.ands_search_sort != "" ? '&sort='+encodeURIComponent(ands.ands_search_sort) : '');
			search_params += '&facet=true&facet.mincount=1';
			search_params += '&facet.field=' + ands.ands_search_query_facet;
		}
		else
		{
			search_params = '?q='+encodeURIComponent(ands.ands_search_query)
								+ '&version=2.2&start=0&rows='+(parseInt(ands.ands_search_record_limit)+1)
								+ '&indent=on&wt=json'
								+ (ands.ands_search_sort != "" ? '&sort='+encodeURIComponent(ands.ands_search_sort) : '');					
		}
		
		$.getJSON(ands.ands_search_service_point 
				+ search_params
				+ '&rows=10&int_ref_id=' + ands.ands_widget_reference_id,
			function (data)
			{
				var ands = win[WIDGET_NAMESPACE][data['message']['params']['int_ref_id']];
				if (!ands)
				{
					console.log('Error - returned widget data does not match a reference ID');
					return;
				}
				
				var widget_results = $('#ands_search_widget_' + ands.ands_widget_reference_id + ' .ands_search_widget_results ul');

				
				// Facetted results behave differently
				if (ands.ands_search_query_facet != "")
				{
					
					if (data['message']['facet_counts']['facet_fields'][ands.ands_search_query_facet])
					{
						var t;
						$.each(data['message']['facet_counts']['facet_fields'][ands.ands_search_query_facet], function(key, doc) {
							// every second field is the count (bizarre)
							if (key%2==0)
							{
								t = doc;
							}
							else
							{
								widget_results.append("<li><a class=\"ellipsis\" href='" 
									+ ands.ands_search_portal_url 
									+ "search#!/q=" + t
									+ "'>" + t + (ands_search_query_facet_incl_count ? " (" + doc + ")" : "") + "</a></li>");
							}

							
						});
					}
					var result_count = $("li", widget_results).length;
					if ($("li", widget_results).length > 5)
					{
						$("li:gt(5)", widget_results).hide(); 
						$("li:nth-child(6)", widget_results).after("<a href='#'  class=\"moreLink\"><br/>More...</a>");
					}
					else if (result_count == 0)
					{
						widget_results.append('<li class="no_results grey">No matching records...</li>');
					}

					$("a.moreLink").live("click", function() {
						$(this).parent().children().slideDown();
						$(this).remove();
					    return false;
					});
				}
				else if (data['message']['numFound'] == 0)
				{
					widget_results.append('<li class="no_results grey">No matching records...</li>');
				}
				else
				{
					$.each(data['message']['docs'], function(key, doc) {
						
						if (key < ands.ands_search_record_limit)
						{ 
							widget_results.append("<li><a class=\"ellipsis\" href='" 
									+ ands.ands_search_portal_url 
									+ "" + encodeURIComponent(doc['slug'])
									+ "'>" + doc['display_title'] + "</a></li>");
						}
						
					});
					
					if (data['message']['numFound'] > ands.ands_search_record_limit)
					{
						widget_results.append("<a style='margin-top:10px;' href='"+ands.ands_search_portal_url+"search#!/dq=<automatically generated search>/rq="+encodeURIComponent(ands.ands_search_query)+"'>More...</a>");
					}
					
				}
			}
		);
		
	}
	
	/*
	*  Initialise the window variables by using those in the global scope
	*  (if available) or alternatively the defaults above.
	*/
	function initVars(win, id)
	{
		if (!win[WIDGET_NAMESPACE]) { win[WIDGET_NAMESPACE] = {}; }
		win[WIDGET_NAMESPACE][id] = {'ands_widget_reference_id':id};
		
		for (var def in aStrDefs)
		{
			if (!win[def])
			{
				win[WIDGET_NAMESPACE][id][def] = aStrDefs[def];
			}
			else
			{
				win[WIDGET_NAMESPACE][id][def] = decodeURIComponent(win[def].replace('%20',' '));
			}
			win[def] = '';
		}
	}
	

	makeWidget(ands_search_id);
}) ()