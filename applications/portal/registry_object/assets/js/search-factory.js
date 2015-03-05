app.factory('search_factory', function($http, $log){
	return {
		status : 'idle',
		filters: [],
		query: '',
		search_type: 'q',
		result: null,
		facets: null,
		pp : [
			{value:15,label:'Show 15'},
			{value:30,label:'Show 30'},
			{value:60,label:'Show 60'},
			{value:100,label:'Show 100'}
		],

		available_search_type: [
			'q', 'title', 'identifier', 'related_people', 'related_organisations', 'description'
		],

		class_choices: [
			{value:'collection', label:'Data'},
			{value:'party', label:'People and Organisation'},
			{value:'service', label:'Services and Tools'},
			{value:'activity', label:'Grants and Projects'}
		],

		default_filters: {
			'rows':15,
			'sort':'score desc',
			'class':'collection'
			// 'spatial_coverage_centres': '*'
		},

		sort : [
			{value:'score desc',label:'Relevance'},
			{value:'title asc',label:'Title A-Z'},
			{value:'title desc',label:'Title Z-A'},
			{value:'title desc',label:'Popular'},
			{value:'record_created_timestamp asc',label:'Date Added'}
		],

		advanced_fields: [
			{'name':'terms', 'display':'Search Terms', 'active':true},
			{'name':'subject', 'display':'Subjects'},
			{'name':'group', 'display':'Data Provider'},
			{'name':'access_rights', 'display':'Access'},
			{'name':'license_class', 'display':'Licence'},
			{'name':'temporal', 'display':'Time Period'},
			{'name':'spatial', 'display':'Location'},
			{'name':'review', 'display':'Review'}
		],

		advanced_fields_activity: [
			{'name':'terms', 'display':'Search Terms', 'active':true},
			{'name':'type', 'display':'Types'},
			{'name':'activity_status', 'display':'Status'},
			{'name':'subject', 'display':'Subjects'},
			{'name':'administering_institution', 'display':'Administering Institution'},
			{'name':'date_range', 'display':'Date Range'},
			{'name':'funders', 'display':'Funders'},
			{'name':'funding_scheme', 'display':'Funding Scheme'},
			{'name':'funding_amount', 'display':'Funding Amount'},
			{'name':'review', 'display':'Review'}
		],

		collection_facet_order: ['group', 'access_rights', 'license_class'],
		activity_facet_order: ['type', 'activity_status', 'funding_scheme', 'administering_institution', 'funders'],

		ingest: function(hash) {
			this.filters = this.filters_from_hash(hash);
			if (this.filters.q) this.query = this.filters.q;
			// $log.debug(this.available_search_type);
			var that = this;
			angular.forEach(this.available_search_type, function(x){
				if (that.filters.hasOwnProperty(x)) {
					that.query = that.filters[x];
					that.search_type = x;
				}
			});
			return this.filters;
		},

		reset: function(){
			this.filters = {q:''};
			this.search_type = 'q';
			this.query = '';
		},

		update: function(which, what) {
			this[which] = what;
		},

		search: function(filters){
			this.status = 'loading';
			// $log.debug('search filters', filters);
			var promise = $http.post(base_url+'registry_object/filter', {'filters':filters}).then(function(response){
				this.status = 'idle';
				// $log.debug('response', response.data);
				return response.data;
			});
			return promise;
		},

		search_no_record: function(filters) {
			var promise = $http.post(base_url+'registry_object/filter/true', {'filters':filters}).then(function(response){
				this.status = 'idle';
				return response.data;
			});
			return promise;
		},

		construct_facets: function(result) {
			var facets = [];

			//subjects DEPRECATED in favor of ANZSRC codes directly from the home page
			// facets['subject'] = [];
			// angular.forEach(result.facet_counts.facet_queries, function(item, index) {
			// 	var fa = {
			// 		name: index,
			// 		value: parseInt(item)
			// 	}
			// 	facets['subject'].push(fa)
			// });

			//other facet fields
			angular.forEach(result.facet_counts.facet_fields, function(item, index) {
				facets[index] = [];
				for (var i = 0; i < result.facet_counts.facet_fields[index].length ; i+=2) {
					var fa = {
						name: result.facet_counts.facet_fields[index][i],
						value: result.facet_counts.facet_fields[index][i+1]
					}
					facets[index].push(fa);
				}
			});

			var order = this.collection_facet_order;

			if(this.filters['class']=='activity'){
				var order = this.activity_facet_order;
			}

			var orderedfacets = [];
			angular.forEach(order, function(item){
				// orderedfacets[item] = facets[item]
				orderedfacets.push({
					name: item,
					value: facets[item]
				});
			});

			// $log.debug(result.facet_counts.facet_fields.earliest_year);
			

			// $log.debug('orderedfacet', orderedfacets);
			// $log.debug('facets', facets);
			return orderedfacets;
		},

		temporal_range: function(result) {
			var range = [];
			var earliest_year = false;
			var latest_year = false;

			if(result.facet_counts.facet_fields.earliest_year) {
				earliest_year = result.facet_counts.facet_fields.earliest_year[0];
			}
			if(result.facet_counts.facet_fields.latest_year) {
				latest_year = result.facet_counts.facet_fields.latest_year[0];
			}

			if(earliest_year && latest_year) {
				// $log.debug(earliest_year, latest_year);
				for(i = parseInt(earliest_year); i < parseInt(latest_year);i++){
					range.push(i);
				}
			}

			return range;
		},

		filters_from_hash:function(hash) {
			var xp = hash.split('/');
			var filters = {};
			$.each(xp, function(){
				var t = this.split('=');
				var term = t[0];
				var value = t[1];
				if(term=='rows'||term=='year_from'||term=='year_to') value = parseInt(value);
				if(term && value && term!=''){

					if(filters[term]) {
						if(typeof filters[term]=='string') {
							var old = filters[term];
							filters[term] = [];
							filters[term].push(old);
							filters[term].push(value);
						} else if(typeof filters[term]=='object') {
							filters[term].push(value);
						}
					} else {
						filters[term] = value;
					}
				}
			});

			angular.forEach(this.default_filters, function(content,type){
				if(!filters[type]) filters[type] = content;
			});

			//auto switch to activity search in grants
			if(location.href.indexOf('grants')>-1) {
				filters['class'] = 'activity';
				
			}

			if(filters['class']=='activity' && location.href.indexOf('search')>-1) {
				$('#banner-image').css('background-image', "url('"+base_url+"assets/core/images/activity_banner.jpg')");
			} else if(filters['class']=='collection' && location.href.indexOf('search')>-1) {
				$('#banner-image').css('background-image', "url('"+base_url+"assets/core/images/collection_banner.jpg')");
			}

			return filters;
		},
		filters_to_hash: function(filters) {
			var hash = '';
			$.each(filters, function(i,k){
				if(typeof k!='object'){
					hash+=i+'='+k+'/';
				} else if (typeof k=='object'){
					$.each(k, function(){
						hash+=i+'='+decodeURIComponent(this)+'/';
					});
				}
			});
			return hash;
		},
		get_matching_records: function(id) {
			var promise = $http.get(registry_url+'services/api/registry_objects/'+id+'/identifiermatch').then(function(response){
				this.status = 'idle';
				return response.data;
			});
			return promise;
		}
	}
});