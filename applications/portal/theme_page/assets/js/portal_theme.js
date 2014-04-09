angular.module('portal_theme',[]).
	factory('pages', function($http){
		return {
			getPage: function(slug){
				var promise = $http.get(rda_service_url+'getThemePage/'+slug).then(function(response){
					return response.data;
				});
				return promise;
			}
		}
	}).
	factory('searches', function($http){
		return{
			search: function(filters){
				var promise = $http.post(base_url+'search/filter/', {'filters':filters}).then(function(response){
					return response.data;
				});
				return promise;
			},
			getByList: function(list_ro){
				return $http.post(rda_service_url+'getByList/', {'list_ro': list_ro}).then(function(response){
					return response.data;
				});
			},
			getConnections: function(key){
				return $http.get(rda_service_url+'getConnections/?registry_object_key='+encodeURIComponent(key)).then(function(response){
					return response.data;
				});
			}
		}
	}).
	directive('colorbox', function(){
		return {
			restrict: 'AC',
			link: function(scope, element, attrs){
				$(element).colorbox({
					maxWidth:'100%',
					maxHeight:'100%'
				});
			}
		}
	}).
	directive('carousel', function(){
		return {
			restrict: 'A',
			link: function(scope, element, attrs){
				$(element).flexslider({
				    animation: "slide",
				    animationLoop:true,
				    slideshowSpeed: 2500,
				    pauseOnHover:true,
				    directionNav:false,
				    itemWidth: 260,
				    itemMargin: 40,
				  });
			}
		}
	}).
	directive('filmstrip', function(){
		return {
			restrict : 'A',
			link: function(scope, element, attrs){
				$(element).flexslider({
					animation:'slide',
					controlNav: false,
					directionNav: true,
					animationLoop: false,
					itemWidth: 260,
					itemMargin: 2,
					move:1
				});
			}
		}
	}).
	filter('class_name', function(){
		return function(text){
			switch(text){
				case 'collection': return 'Collections';break;
				case 'activity': return 'Activities';break;
				case 'party': return 'Parties';break;
				case 'party_one': return 'People';break;
				case 'party_multi': return 'Organisations & Groups';break;
				case 'service': return 'Services';break;
				default: return text;break;
			}
		}
	}).
	controller('init', function($scope, pages, searches, $filter){
		$scope.search_results = {}; 
		$scope.slug = $('#slug').val();
		// pages.getPage($scope.slug).then(function(data){
		// 	$scope.page = data;
		// });
		// 
		
		/**
		 * Iteration for search and facets
		 * @return {[type]} [description]
		 */
		$('.theme_search').each(function(){
			var filter = {};
			filter['q'] = $('.theme_search_query', this).val();
			filter['limit'] = $('.theme_search_limit', this).val();
			filter['random'] = $('.theme_search_random', this).val();
			if($.trim(filter['q'])=='') delete filter['q'];
			// filter['id'] = $(this).attr('id');

			var view_search_text = $('.theme_search_view_search_text', this).val();
			if(view_search_text==''){
				view_search_text = 'View All Search';
			}
			
			var search_id = $(this).attr('id');
			$('.theme_search_fq', this).each(function(){
				if(filter[$(this).attr('fq-type')]){
					if(filter[$(this).attr('fq-type')] instanceof Array){
						filter[$(this).attr('fq-type')].push($(this).val());
					}else{
						var prev = filter[$(this).attr('fq-type')];
						filter[$(this).attr('fq-type')] = [];
						filter[$(this).attr('fq-type')].push(prev);
						filter[$(this).attr('fq-type')].push($(this).val());
					}
				}else filter[$(this).attr('fq-type')] = $(this).val();
			});




			searches.search(filter).then(function(data){
				$scope.search_results[search_id] = data;

				// console.log(filter);
				var filter_query = '';
				$.each(filter, function(i, k){
					if(k instanceof Array || (typeof(k)==='string' || k instanceof String)){
						if(i!='limit' && i!='random'){
							filter_query +=i+'='+encodeURIComponent(k)+'/';
						}
					}
				});
				data.filter_query = filter_query;
				data.view_search_text = view_search_text;

				
				data.tabs = [];
				$(data.facet_result).each(function(){
					if(this.facet_type=='class'){
						$.each(this.values, function(){
							var new_tab = {
								title: $filter('class_name')(this.title),
								inc_title: this.title,
								count: this.count
							};
							if(filter['class']==this.title) new_tab.current = true;
							data.tabs.push(new_tab);
						});
					}
				});
				
				//search data goes here
				var template = $('#search-result-template').html();
				var output = Mustache.render(template, data);
				$('#'+search_id).html(output).show();
				if($('.tabs a.current').length==0) $('.tabs a:first-child').addClass('current');

				$('.excerpt').each(function(){
					// This will unencode the encoded entities, but also hide any random HTML'ed elements
					$(this).html(htmlDecode(htmlDecode(htmlDecode($(this).html()))));
					$(this).html($(this).directText());

					var thecontent = $(this).html();
					var newContent = ellipsis(thecontent, 200);
					if(thecontent!=newContent){
						newContent = '<div class="hide" fullExcerpt="true">'+thecontent+'</div>' + newContent + '';
					}

					$(this).html(newContent);
				});

				//facets
				if($('.theme_facet[search-id='+search_id+']').length>0){
					var facets = $('.theme_facet[search-id='+search_id+']');
					$(facets).each(function(){
						var facet_type = $(this).attr('facet-type');
						var facet_data = '';
						$(data.facet_result).each(function(){
							if(this.facet_type==facet_type) facet_data = this;
						});
						$(facet_data.values).each(function(){
							this.inc_title = encodeURIComponent(this.title);
						});
						var template = $('#facet-template').html();
						var output = Mustache.render(template, facet_data);
						$(this).html(output).show();
					});
				}
				$('.sidebar ul.facet').each(function(idx, facet){
					if($('li', facet).length>5){
					    var $facet = $(facet);
					    $('li:gt(4)', facet).hide();
					    $facet.append('<li><a href="javascript:;" class="show-all-facet">Show More...</a></li>');
					    $('.show-all-facet', facet).click(function(){
							$(this).parent().siblings().show();
							$(this).parent().remove();
					    });
					}
				});
			});
		});

		/**
		 * Iteration for list_ro
		 * @return {[type]} [description]
		 */
		$('.list_ro').each(function(){
			var this_block = this;
			var list_ro = [];
			$('input.key', this).each(function(){
				list_ro.push($(this).val());
			});
			searches.getByList(list_ro).then(function(data){
				var template = $('#list_ro-template').html();
				var output = Mustache.render(template, data);
				$(this_block).html(output);

				$('.preview_connection', this_block).each(function(){
			        if(typeof $('a', this).attr('slug')!=='undefined'){
			            generatePreviewTip($(this), $('a',this).attr('slug'), null, $('a', this).attr('relation_type'), $('a', this).attr('relation_description'), $('a', this).attr('relation_url'));
			        }else if($('a', this).attr('draft_id')!=''){
			            generatePreviewTip($(this), null, $('a',this).attr('draft_id'), $('a', this).attr('relation_type'), $('a', this).attr('relation_description'), $('a', this).attr('relation_url'));
			            $('a', this).prepend(draftText);
			        }
			    });
			});
		});

		/**
		 * Iteration for relationships
		 */
		$('.relation').each(function(){
			var this_block = this;
			var key = $('input.key', this).val();
			var type = $('input.type', this).val();
			searches.getConnections(key).then(function(data){
				if(data.status!='ERROR'){
					var con = {};
					con.title = $filter('class_name')(type);
					con.connections = data.connections[0][type];
					con.count = data.connections[0][type+'_count'];
					con.slug = data.slug;
					con.type = type;
					if(con.count>5) con.more = true;
					var template = $('#relation-template').html();
					var output = Mustache.render(template, con);
					$(this_block).html(output);

					$('.preview_connection', this_block).each(function(){
				        if(typeof $('a', this).attr('slug')!=='undefined'){
				            generatePreviewTip($(this), $('a',this).attr('slug'), null, $('a', this).attr('relation_type'), $('a', this).attr('relation_description'), $('a', this).attr('relation_url'));
				        }else if($('a', this).attr('draft_id')!=''){
				            generatePreviewTip($(this), null, $('a',this).attr('draft_id'), $('a', this).attr('relation_type'), $('a', this).attr('relation_description'), $('a', this).attr('relation_url'));
				            $('a', this).prepend(draftText);
				        }
				    });
				}
			});
		});
	});

var loading_icon = '<div style="width:100%; padding:40px; text-align:center;"><img src="'+base_url+'assets/core/images/ajax-loader.gif" alt="Loading..." /><br/><br/><center><b>Loading...</b></center></div>';

$(document).on('click', '.view_all_connection', function(){
	var slug = $(this).attr('ro_slug');
    var id = $(this).attr('ro_id');
    var relation_type = $(this).attr('relation_type');
    var page = (typeof $(this).attr('page') != 'undefined' ? $(this).attr('page') : 1);
    if(slug != '')
        var url = base_url+'view/getConnections/?page='+page+'&slug='+slug+'&relation_type='+relation_type;
    if(typeof id != 'undefined' && id != '')
        var url = base_url+'view/getConnections/?page='+page+'&id='+id+'&relation_type='+relation_type;

    $(this).qtip({
        content: {
            text: loading_icon,
            title: {
                text: 'Connections',
                button: 'Close'
            },
            ajax: {
                url: url,
                type: 'POST',
                data: {ro_id: $(this).attr('ro_id')},
                loading:true,
                success: function(data, status) {
                    
                    // Clean up any HTML rubbish...                   
                    var temp = $('<span/>');
                    temp.html(data);
                    $("div.descriptions", temp).html($("div.descriptions", temp).text());
                    $("div.descriptions", temp).html($("div.descriptions", temp).directText());

                    this.set('content.text', temp.html());    

                    formatConnectionTip(this);
                }
            }
        },
        position: {viewport: $(window),my: 'right center',at: 'left center'},
        show: {
            event: 'click',
            ready: true,
            solo: true
        },
        hide: {
            fixed:true,
            event:'unfocus',
        },
        style: {classes: 'ui-tooltip-light ui-tooltip-shadow previewPopup', width: 600} ,
        overwrite: false
    });
});

function formatConnectionTip(tt){
    var tooltip = $('#ui-tooltip-'+tt.id+'-content');
    bindPaginationConnection(tooltip);
}

function bindPaginationConnection(tt){
    $('.goto', tt).on('click',function(e){
        var slug = $(this).attr('ro_slug');
        var id = $(this).attr('ro_id');
        var page = $(this).attr('page');
        var relation_type = $(this).attr('relation_type');

        if(slug != '')
            var url = base_url+'view/getConnections/?slug='+slug+'&relation_type='+relation_type+'&page='+page;
        if(id != '')
            var url = base_url+'view/getConnections/?id='+id+'&relation_type='+relation_type+'&page='+page;

        $.ajax({
            url: url, 
            type: 'GET',
            success: function(data){
                $(tt).html(data);
                bindPaginationConnection(tt);
            }
        });
    });
}
