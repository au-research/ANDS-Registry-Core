angular.module('theme_cms_app', ['slugifier', 'ui.sortable', 'ui.tinymce', 'ngSanitize', 'ui.bootstrap', 'ui.utils']).
	factory('pages_factory', function($http){
		return {
			listAll : function(){
				var promise = $http.get(apps_url+'theme_cms/list_pages').then(function(response){
					return response.data;
				});
				return promise;
			},
			getPage : function(slug){
				var promise = $http.get(apps_url+'theme_cms/get/'+slug).then(function(response){
					return response.data;
				});
				return promise;
			},
			newPage: function(postData){
				var promise = $http.post(apps_url+'theme_cms/new_page/', postData).then(function(response){
					return response.data;
				});
				return promise;
			},
			deletePage: function(slug){
				var promise = $http.post(apps_url+'theme_cms/delete_page', {'slug':slug}).then(function(response){
					return response.data;
				});
				return promise;
			},
			savePage: function(postData){
				var promise = $http.post(apps_url+'theme_cms/save_page', postData).then(function(response){
					return response.data;
				});
				return promise;
			},
			generateSecretTag: function(slug){
				return $http.get(apps_url+'theme_cms/generateSecretTag/'+slug).then(function(response){return response.data});
			}
		}
	}).
	factory('search_factory', function($http){
		return{
			search: function(filters){
				var promise = $http.post(real_base_url+'registry/services/registry/post_solr_search', {'filters':filters}).then(function(response){
					return response.data;
				});
				return promise;
			},
			getConnections: function(key){
				var promise = $http.get(real_base_url+'registry/services/rda/getConnections/?registry_object_key='+encodeURIComponent(key)).then(function(response){
					return response.data;
				});
				return promise;
			}
		}
	}).
	filter('facet_display', function(){
		return function(text){
			var res = '';
			if(text){
				for(var i = 0 ;i<text.length-1;i=i+2){
					res+='<li>'+text[i]+' ('+text[i+1]+')'+'</li>';
				}
			}
			return res;
		}
	}).
	filter('relationships_display', function($filter){
		return function(text, type){
			var res = '';
			if(text && text[type]){
				var s = (text[type]);
				res += '<h4>'+$filter('class_name')(type)+'</h4>';
				res +='<ul>';
				$.each(s, function(i, k){
					res += '<li><a href="'+real_base_url+''+k['slug']+'">'+k['title']+' <small class="muted">'+k['relation_type']+'</small></a></li>';
				});
				res +='</ul>';
				if(text[type+'_count']>5){
					res += 'Total Count: '+text[type+'_count'];
				}
			}
			return res;
		}
	}).
	filter('class_name', function(){
		return function(text){
			switch(text){
				case 'collection': return 'Collections';break;
				case 'activity': return 'Activities';break;
				case 'party_one': return 'People';break;
				case 'party_multi': return 'Organisation & Groups';break;
				case 'service': return 'Services';break;
				default: return text;break;
			}
		}
	}).
	config(function($routeProvider){
		$routeProvider
			.when('/',{
				controller:ListCtrl,
				template:$('#list_template').html()
			})
			.when('/new_page',{
				controller:NewPageCtrl,
				template:$('#new_page_template').html()
			})
			.when('/view/:slug', {
				controller: ViewPage,
				template:$('#view_page_template').html()
			})
	}).
	directive('mapwidget', function($rootScope){
		return {
			restrict : 'A',
			link: function(scope, element, a){
				if(!scope.f.id || scope.f.id=='undefined') scope.f.id = Math.random().toString(36).substring(10);
				$(element).ands_location_widget({
					target:'geoLocation'+scope.f.id,
					return_callback: function(str){
						scope.f.value=str;
						$rootScope.$broadcast('refreshAll');
					}
				});
			}
		}
	}).
	directive('roSearch', function($compile){
		return {
			restrict : 'A',
			link: function(scope, element){
				$(element).registry_widget({
					search_btn_class: 'rowidget_search btn btn-default',
					lookup_callback: function(data, obj, s){
						if(scope.ro){
							scope.ro.key = data.result['key'];
						}else if(scope.c){
							scope.c.relation.key = data.result['key'];
						}
					}
				});
			}
		}
	}).
	directive('colorbox', function(){
		return {
			restrict: 'AC',
			link: function(scope, element, attrs){
				$(element).colorbox(attrs.colorbox);
			}
		}
	})

function ListCtrl($scope, pages_factory){
	pages_factory.listAll().then(function(data){
		$scope.pages = data;
	});
}

function ViewPage($scope, $http, $routeParams, pages_factory, $location, search_factory){

	$scope.tinymceOptions = {
	    theme: "modern",
	    plugins: [
	        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
	        "searchreplace wordcount visualblocks visualchars code fullscreen",
	        "insertdatetime media nonbreaking save table contextmenu directionality",
	        "emoticons template paste"
	    ],
	    height:"250px",
	    width:"100%",
	    entity_encoding : "raw",
	    toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
	}

	$scope.sortableOptions = {
		handle:'.widget-title',
		connectWith: '.region',
		stop: function(ev, ui){
			$scope.save();
		}
	}

	$scope.available_filters = [
		{value:'class', title:'Class'},
		{value:'type', title:'Type'},
		{value:'group', title:'Group'},
		{value:'boost_key', title:'Boost'},
		{value:'tag', title:'Tag'},
		{value:'subject_vocab_uri', title:'Subject'},
		{value:'subject_value_resolved', title:'Keywords'},
		{value:'data_source_key', title:'Data Source'},
		{value:'originating_source', title:'Originating Source'},
		{value:'spatial', title:'Spatial'},
        {value:'access_rights', title:'Access rights'},
	];

	$scope.available_headings = [
		{value:false, title:'Not Visible'},
		{value:'h1', title:'Heading 1'},
		{value:'h2', title:'Heading 2'},
		{value:'h3', title:'Heading 3'},
		{value:'h4', title:'Heading 4'}
	]

	$scope.suggest = function(what, q){
		return $http.get(real_base_url+'registry/services/registry/suggest/'+what+'/'+q).then(function(response){
			return response.data;
		});
	}

	pages_factory.getPage($routeParams.slug).then(function(data){
		$scope.page = data;
		$scope.page.left = $scope.page.left || [];
		$scope.page.right = $scope.page.right || [];
		$scope.search_result = {};
		$scope.available_search = [];
		$scope.relationships = [];
		$($scope.page.left).each(function(){
			if(this.type=='search'){
				$scope.preview_search(this);
			}else if(this.type=='relation'){
				$scope.preview_relation(this);
			}
		});
		$($scope.page.right).each(function(){
			if(this.type=='search'){
				$scope.preview_search(this);
			}else if(this.type=='relation'){
				$scope.preview_relation(this);
			}
		});
		$scope.available_facets = [
			{type:'class', name:'Class'},
			{type:'group', name:'Organisation & Groups'},
			{type:'license_class', name:'Licences'}
		];
		$scope.available_relation_class = [
			{type:'collection', name:'Collections'},
			{type:'party_one', name:'People'},
			{type:'party_multi', name:'Organisation & Groups'},
			{type:'activity', name:'Activities'},
			{type:'service', name:'Services'}
		];
		$scope.gallery_types = [
			{name: 'Carousel', value:'carousel'},
			{name: 'Filmstrip', value:'filmstrip'}
		]
	});
	$scope.addContent = function(region){
		var blob = {
			'title':'', 
			'type':'html', 
			'content':'', 
			'editing':true, 
			'id':Math.random().toString(36).substring(10), 
			'list_ro':[{key:''},{key:''},{key:''},{key:''},{key:''}],
			'gallery_type':'carousel',
			'gallery':[{src:''},{src:''},{src:''},{src:''},{src:''}]
		};
		if(region=='left'){
			$scope.page.left.push(blob);
		}else if(region=='right'){
			$scope.page.right.push(blob);
		}
	}

	$scope.show_delete_confirm = false;
	$scope.deleting = function(param){
		if(param=='true'){
			$scope.show_delete_confirm = true;
		}else $scope.show_delete_confirm = false;
	}
	$scope.delete = function(slug){
		pages_factory.deletePage(slug).then(function(data){
			$location.path('/');
		});
	}

	$scope.save = function(){
		// console.log($scope.page);
		pages_factory.savePage($scope.page).then(function(data){
			var now = new Date();
			$scope.saved_msg = 'Last Saved: '+now; 
		});
	}

	$scope.edit = function(c){
		if(c.editing){
			c.editing = false;
			if(c.type=='search'){
				$scope.preview_search(c);
			}else if(c.type=='relation'){
				$scope.preview_relation(c);
			}
			$scope.save();
		}else c.editing = true;
	}
	$scope.delete_blob = function(region, index){
		if(confirm('Are you sure you want to delete this content? This action is irreversible')){
			$scope.page[region].splice(index, 1);
		}
	}

	$scope.addToList = function(blob, list){
		var newObj = {};
		switch(blob.type){
			case 'gallery': 
				newObj = {'src':''};
				if(!blob.gallery) blob.gallery = []; list = blob.gallery;
				break;
			case 'list_ro': 
				newObj = {'key':''};
				if(!blob.list_ro) blob.list_ro = []; list = blob.list_ro
				break;
			case 'search': 
				newObj = {name:'', value:''};
				if(!blob.search) blob.search = {};
				if(!blob.search.fq) blob.search.fq = []; list = blob.search.fq;
				break;
		}
		list.push(newObj);
	}

	$scope.setFilterType = function(filter, type){
		filter.name = type;
	}

	$scope.removeFromList = function(list, index){
		list.splice(index, 1);
	}

	$scope.$on('refreshAll', function(){
		$.each($scope.page.left, function(){
			$scope.preview_search(this);
		});
		$.each($scope.page.right, function(){
			$scope.preview_search(this);
		});
	});

	$scope.preview_search = function(c){
		if(c.search){
			if(!c.search.id) c.search.id = Math.random().toString(36).substring(10);
			var filters = $scope.constructSearchFilters(c);
			search_factory.search(filters).then(function(data){
				filter_query ='';
				$.each(filters, function(i, k){
					if(k instanceof Array || (typeof(k)==='string' || k instanceof String)){
						if(i!='fl') filter_query +=i+'='+encodeURIComponent(k)+'/';
					}
				});
				$scope.search_result[c.search.id] = {name:c.title, data:data, search_id:c.search.id, filter_query:filter_query};
				$scope.$watch('search_result', function(){
					$scope.available_search = [];
					angular.forEach($scope.search_result, function(key, value){
						$scope.available_search.push({search_id:key.search_id, name:key.name});
					});
				});
			});
		}
	}

	$scope.preview_relation= function(c){
		if(c.relation){
			search_factory.getConnections(c.relation.key).then(function(data){
				if(data.status!='ERROR'){
					$scope.relationships[c.relation.key] = data.connections[0];
				}
			});
		}
	}

	$scope.addBoost = function(blob,key){
		if(!blob.search.fq) blob.search.fq = [];
		blob.search.fq.push({name:'boost_key', value:key});
	}

	$scope.constructSearchFilters = function(c){
		var filters = {};
		var placeholder = '';
		filters['include_facet'] = true;
		filters['fl'] = 'id, display_title, slug, key';
		if(c.search.limit) filters['rows'] = c.search.limit;
		if(c.search.random) filters['sort'] = 'random_'+Math.random().toString(36).substring(10)+' desc';
		if(c.search.query) filters['q'] = c.search.query;
		$(c.search.fq).each(function(){
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

	$scope.generateSecretTag = function(){
		pages_factory.generateSecretTag($scope.page.slug).then(function(data){
			$scope.page.secret_tag = data;
		});
	}
}

function NewPageCtrl($scope, pages_factory, Slug, $location){
	$scope.addPage = function(){
		$scope.ok, $scope.fail = null;
		var slug = Slug.slugify(this.new_page_title);
		var postData = {
			title: this.new_page_title,
			slug: slug,
			img_src: this.new_page_img_src,
			desc: this.new_page_desc,
			visible: 0,
			secret_tag: ''
		}
		pages_factory.newPage(postData).then(function(data){
			if(data==1){
				$scope.ok = {'msg': 'Your Theme Page has been created.', 'slug':slug};
				$location.path('/view/'+slug);
			}else{
				$scope.fail = {'msg':'There is a problem creating your page'};
			}
		});
	}
}

$(document).on('keypress', 'input', function(e){
	if(e.which==13){
		e.preventDefault();
	}
});