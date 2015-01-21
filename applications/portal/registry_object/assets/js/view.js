$(document).ready(function() {
    initConnectionGraph()
    //console.log($.browser)
    $('a[title]').qtip({

	    style: {classes: 'ui-tooltip-light ui-tooltip-shadow seealso-tooltip',width: '250px'},
	    show: 'mouseover',
	    hide: 'mouseout' })
	});

	$(document).on('click', '.panel-heading a', function(e){
		e.preventDefault();
		$(this).parents('.panel-content').children('.panel-body').height('auto');
	});


function traverseAndSelectChildren(tree, select_id)
{
	//console.log(tree);
    for (var i = tree.length - 1; i >= 0; i--) {
		if (tree[i].registry_object_id == select_id)
		{
			tree[i].select = true;
			tree[i].expand = true;
			tree[i].focus = true;
           		   // tree[i].activate = true;
		}
		else
		{
			            if (tree[i].children)
			{            
				tree[i].children = traverseAndSelectChildren(tree[i].children, select_id);
			}
		}
	}
	return tree;
}


function initConnectionGraph()
	{
	// Attach the dynatree widget to an existing <div id="tree"> element
	// and pass the tree options as an argument to the dynatree() function:

        var data = JSON.parse(decodeURIComponent($('#connectionTree').attr('mydata')));
        $('#connectionTree').removeAttr('mydata');
        var ro_id = $('#connectionTree').attr('ro_id');
        $('#connectionTree').removeAttr('ro_id');
        data = traverseAndSelectChildren(data, ro_id);

		/* Generate the tree */
			$("#connectionTree").dynatree({
				children: data,
				onActivate: function(node) {
				// If this has more parts, open them...
				    if (node.data.children)	{
					    node.expand();
				    }
				},
				onClick: function(node) {
							//if (node.data.registry_object_id != ro_id())
							//{
								// $('#' + node.li.id).qtip('show');
							//}

							// XXX: show the tooltip
							// A DynaTreeNode object is passed to the activation handler
							// Note: we also get this event, if persistence is on, and the page is reloaded.
						   //window.location = base_url + node.data.slug;
						},

				onDblClick: function(node) {
				// Change to view this record
						if (node.data.status=='PUBLISHED')	{
							window.location = base_url + node.data.slug + "/" + node.data.registry_object_id;
						}
						else{
							window.location = base_url + "view/?id=" + node.data.registry_object_id;
						}
				},

						onPostInit: function (isReloading, isError)
						{
							// Hackery to make the nodes representing THIS registry object
							// visible, but not highlighted
							nodes = this.getSelectedNodes();
							for (var i = nodes.length - 1; i >= 0; i--) {
								nodes[i].activate();
								nodes[i].deactivate();
							};
						},

						onRender: function(node, nodeSpan) {


							var preview_url;
							if (node.data.status=='PUBLISHED')
							{
								preview_url = base_url + "preview/" + node.data.slug;
							}
							else
							{
								$('a', nodeSpan).prepend(draftText);
								preview_url = base_url + "preview/?registry_object_id=" + node.data.registry_object_id;
							}

							/* Change the icon in the tree */
							if (node.data['class']=="collection")
							{
								$(nodeSpan).find("span.dynatree-icon").css("background-position", "-38px -155px");
							}
							else if (node.data['class']=="party")
							{
								$(nodeSpan).find("span.dynatree-icon").css("background-position", "-19px -155px");
							}
							else if (node.data['class']=="service")
							{
								$(nodeSpan).find("span.dynatree-icon").css("background-position", "0px -156px");
							}
							else if (node.data['class']=="activity")
							{
								$(nodeSpan).find("span.dynatree-icon").css("background-position", "-57px -155px");
							}
							
							$(nodeSpan).attr('title', $(nodeSpan).text());
							$('a',$(nodeSpan)).attr('href', base_url + node.data.slug +"/"+node.data.registry_object_id);

							if (node.data['class']=="more")
							{
								$(nodeSpan).find("span.dynatree-icon").remove();
								var a = $('a',$(nodeSpan));

								// Bind the accordion classes and attributes
								a.addClass('view_all_connection');
								if (node.data.status=='PUBLISHED')
								{
									a.attr('ro_slug', node.data.slug);
								}
								else
								{
									a.attr('ro_id', node.data.registry_object_id);
								}

								a.attr('relation_type','nested_collection');
								a.attr('page', 2);
								//console.log($(nodeSpan).html());
							}
                            /*	else
							{

								 Prepare the tooltip preview
								$('#' + node.li.id).qtip({
									content: {
										text: 'Loading preview...',
										title: {
											text: 'Preview',
											button: 'Close'
										},
										ajax: {
											url: preview_url, 
											type: 'GET',
											//data: { "slug": node.data.slug, "registry_object_id": node.data.registry_object_id },
											success: function(data, status) {
												data = $.parseJSON(data);                                       
												var decoded_content = $(data.html);
												var content_description = htmlDecode(decoded_content.find('.post .descriptions').html());
												decoded_content.find('.post .descriptions').html('<small>' + content_description + '</small>');
												this.set('content.text', decoded_content);

												if (data.slug)
												{
													$('.viewRecord').attr("href", base_url + data.slug);
												}
												else
												{
													$('.viewRecord').attr("href",base_url+"view/?id=" + data.registry_object_id);
												}
											} 
										}
									},
									position: {
										my: 'left center',
										at: 'right center',
										target: $('#' + node.li.id + " > span")
									},
									show: {
										event: 'none',
										solo: true
									},
									hide: {
										delay: 1000,
										fixed: true
									},
									style: {
										classes: 'ui-tooltip-light ui-tooltip-shadow previewPopup',
										width: 550
									}
								});
							}*/
						}

                    });

}

