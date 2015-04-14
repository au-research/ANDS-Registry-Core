angular.module('bulk_tag_app', ['slugifier', 'ui.sortable', 'ui.tinymce', 'ngSanitize', 'ui.bootstrap', 'ui.utils']).
	factory('search_factory', function($http){
		return{
			search: function(filters){
				return promise = $http.post(real_base_url+'registry/services/registry/post_solr_search', {'filters':filters}).then(function(response){ return response.data; });
			},
			tags_get_solr: function(filters){
				return promise = $http.post(real_base_url+'registry/services/registry/tags/solr/get', {'filters':filters}).then(function(response){ return response.data; });
			},
			tags_action_solr: function(filters, action, tag, tagType){
				return promise = $http.post(real_base_url+'registry/services/registry/tags/solr/'+action, {'filters':filters, 'tag':tag, 'tag_type':tagType}).then(function(response){ return response.data; });
			},
			tags_get_keys: function(keys){
				return promise = $http.post(real_base_url+'registry/services/registry/tags/keys/get', {'keys':keys}).then(function(response){ return response.data; });
			},
			tags_action_keys: function(keys, action, tag, tagType){
				return promise = $http.post(real_base_url+'registry/services/registry/tags/keys/'+action, {'keys':keys, 'tag':tag, 'tag_type':tagType}).then(function(response){ return response.data; });
			},
			tags_get_status: function(tags){
				return promise = $http.post(real_base_url+'registry/services/registry/tags_status/', {'tags':tags}).then(function(response){ return response.data; });	
			}
		}
	}).
	directive('mapwidget', function(){
		return {
			restrict : 'A',
			link: function(scope, element, a){
				$(element).ands_location_widget({
					target:'geoLocation'+scope.f.id,
					return_callback: function(str){
						scope.f.value=str;
						scope.search();
					}
				});
			}
		}
	}).
	config(function($routeProvider){
		$routeProvider
			.when('/',{
				controller:index,
				template:$('#index_template').html()
			})
	});

function index($scope, $http, search_factory){

	$scope.datasources = [];
	$scope.selected_ro = [];
	
	$scope.$watch('selected_ro.length', function(){
		$scope.refreshSelectedFacet();
	});

    $scope.refreshSelectedFacet = function(){
        if($scope.selected_ro.length > 0){
            search_factory.tags_get_keys($scope.selected_ro).then(function(data){
                var tags_array = [];
                $.each(data, function(i, k){
                    tags_array.push(k);
                });
                $scope.tags_result = {data:tags_array};
            });
        }else{
            if($scope.facet_result) $scope.tags_result = {data:$scope.facet_result.tag};
            if($scope.search_result && $scope.search_result.data.result.docs){
                $.each($scope.search_result.data.result.docs, function(){
                    this.selected = '';
                });
            }
        }
    }

	$scope.$watch('perPage', function(){
		$scope.search();
	});

	$scope.$watch('tags_result', function(newr, oldr){
		if(newr && newr.data && newr.data.length > 0){
			search_factory.tags_get_status($scope.tags_result).then(function(data){
				if(data.status=='OK') {
                    $.each($scope.tags_result.data, function(i, x){
                        $.each(data.content, function(i, y){
                            if (x.name== y.name){
                                x.type = y.type;
                            }
                        });
                    });
				}
			});
		}
	}, true);

	$scope.available_filters = [
		{value:'class', title:'Class'},
		{value:'type', title:'Type'},
		{value:'group', title:'Group'},
		{value:'tag', title:'Tag'},
		{value:'subject_vocab_uri', title:'Subject'},
		{value:'subject_value_resolved', title:'Keywords'},
		{value:'data_source_key', title:'Data Source'},
		{value:'originating_source', title:'Originating Source'},
		{value:'spatial', title:'Spatial'},
	];

	$scope.suggest = function(what, q){
		return $http.get(real_base_url+'registry/services/registry/suggest/'+what+'/'+q).then(function(response){
			return response.data;
		});
	}

	$scope.search = function(no_refresh){
		if(!no_refresh) $scope.currentPage = 1;
		var filters = $scope.constructSearchFilters();
		$scope.loading_search = true;

		search_factory.search(filters).then(function(data){
			
			$scope.loading_search = false;
			filter_query ='';
			$.each(filters, function(i, k){
				if(k instanceof Array || (typeof(k)==='string' || k instanceof String)){
					if(i!='fl') filter_query +=i+'='+encodeURIComponent(k)+'/';
				}
			});
			$scope.search_result = {data:data, filter_query:filter_query};
			if(data.result){
				
				//construct facet_fields for easy retrieval
				if($scope.search_result.data.facet){
					$scope.facet_result = {};
					for(var index in $scope.search_result.data.facet.facet_fields){
						$scope.facet_result[index] = [];
						for(var i=0;i<$scope.search_result.data.facet.facet_fields[index].length;i+=2){
							$scope.facet_result[index].push({
								name: $scope.search_result.data.facet.facet_fields[index][i],
								value: $scope.search_result.data.facet.facet_fields[index][i+1]
							});
						}
					}
				}

				$.each($scope.search_result.data.result.docs, function(){
					if ($.inArray(this.key, $scope.selected_ro)!=-1) {
						this.selected = 'ro_selected';
					}
				});

				if ($scope.selected_ro.length == 0) {
                    $scope.tags_result = {data:$scope.facet_result.tag};
                } else {
                    $scope.refreshSelectedFacet();
                }
				$scope.maxPage = Math.ceil($scope.search_result.data.numFound / $scope.perPage);

			}
		});
	}

	$scope.page = function(page){
		if(page >= 1 && page <= $scope.maxPage){
			$scope.currentPage = page;
			$scope.search(true);
		}
		if($scope.currentPage <= 1){
			$scope.minpage = 'disabled';
		}else{
			$scope.minpage = '';
		}
		if($scope.currentPage == $scope.maxPage){
			$scope.maxpage = 'disabled';
		}else{
			$scope.maxpage = '';
		}
	}

	$scope.tagAction = function(action, tag){
		var message = '';
		var affected_num = ($scope.selected_ro.length > 0) ? $scope.selected_ro.length : $scope.search_result.data.numFound;

        if($scope.selected_ro.length == 0){
            if(action=='remove'){
                $.each($scope.tags_result.data, function(){
                   if(this.name==tag){
                       affected_num = this.value;
                       return false;
                   }
                });
            }
        }
		if(action=='add'){
			tag = $scope.tagToAdd;
			message = 'Are you sure you want to add '+$scope.newTagType+' tag: ' + tag + ' to ' + affected_num + ' registry objects? ';
		}else{
			message = 'Are you sure you want to remove tag: ' + tag + ' from ' + affected_num + ' registry objects? ';
		}
		if(tag && confirm(message)){
			$scope.loading = true;
			var filters = $scope.constructSearchFilters();
			if($scope.selected_ro.length > 0){
				search_factory.tags_action_keys($scope.selected_ro, action, tag, $scope.newTagType).then(function(data){
                    if(data.status=='ERROR') alert(data.message);
                    $scope.tagToAdd = '';
					$scope.loading = false;
                    $scope.search();
				});
			}else{
				if(action=='add') filters['rows'] = 99999;
				search_factory.tags_action_solr(filters, action, tag, $scope.newTagType).then(function(data){
                    if(data.status=='ERROR') alert(data.message);
					$scope.tagToAdd = '';
					$scope.loading = false;
                    $scope.search();
				});
			}
		}else{
			//nothing
		}
	}

	$scope.addFilter = function(obj){
		var newObj = {name:'class', value:'', id:Math.random().toString(36).substring(10)};
		if(obj) newObj = obj;
		if(!$scope.filters) $scope.filters = [];
		$scope.filters.push(newObj);
		if(obj) $scope.search(false);
	}

	$scope.setFilterType = function(filter, type){
		filter.name = type;
	}

	$scope.removeFromList = function(list, index){
		list.splice(index, 1);
		$scope.search();
	}

	$scope.select = function(ro){
		if($scope.selected_ro.indexOf(ro.key)===-1){
			ro.selected = 'ro_selected';
			$scope.selected_ro.push(ro.key);
		}else{
			ro.selected = '';
			$scope.selected_ro.splice($scope.selected_ro.indexOf(ro.key), 1);
		}
	}

	$scope.constructSearchFilters = function(){
		var filters = {};
		var placeholder = '';
		filters['include_facet_tags'] = true;
		filters['include_facet'] = true;
		filters['fl'] = 'id, display_title, slug, key, tag, class, score';
		filters['rows'] = $scope.perPage;
		filters['p'] = $scope.currentPage;
		filters['facet.sort'] = 'index';
		if($scope.search_query) filters['q'] = $scope.search_query;
		$($scope.filters).each(function(){
			if(this.name){
				if(filters[this.name]){
					if(filters[this.name] instanceof Array){
						filters[this.name].push(this.value);
					}else{
						placeholder = filters[this.name];
						filters[this.name] = [];
						filters[this.name].push(placeholder);
						filters[this.name].push(this.value);
					}
				}else filters[this.name] = this.value;
			}
		});
		return filters;
	}

	$scope.show = 10;
	$scope.filters = [];
	$scope.currentPage = 1;
	$scope.minpage = 'disabled';
	$scope.perPage = 10;
	$scope.showHidden = false;
	$scope.hiddenDS = 0;
	$scope.newTagType = 'public';
	$scope.loading = false;
	// $scope.addFilter({name:'data_source_key', value:'acdata.unsw.edu.au'});
	$('.ds-restrict').each(function(){
		var k = $(this).attr('ds-key');
		$scope.filters.push({name:'data_source_key', value:k, disable:true});
		$scope.hiddenDS++;
	});
	// $scope.search()
}