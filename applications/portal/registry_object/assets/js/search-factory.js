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

		search_types: [
			{value:'q', label:'All Fields'},
			{value:'title', label:'Title'},
			{value:'description', label:'Description'},
			{value:'identifier', label:'Identifier'},
			{value:'related_people', label:'Related People'},
			{value:'related_organisations', label:'Related Organisations'}
		],

		search_types_activities: [
			{value:'q', label:'All Fields'},
			{value:'title', label:'Title'},
			{value:'description', label:'Description'},
			{value:'identifier', label:'Identifier'},
			{value:'institution', label:'Institution'},
			{value:'researcher', label:'Researcher'}
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
			'sort':'list_title_sort asc',
			'class':'collection'
			// 'spatial_coverage_centres': '*'
		},

		sort : [
			{value:'score desc',label:'Relevance'},
			{value:'list_title_sort asc',label:'Title A-Z'},
			{value:'list_title_sort desc',label:'Title Z-A'},
			// {value:'title desc',label:'Popular'},
			{value:'record_created_timestamp desc',label:'Date Added  <i class="fa fa-sort-amount-desc"></i>'}
		],

		activity_sort : [
			{value: 'score desc', label: 'Relevance'},
			{value: 'list_title_sort asc',label:'Title A-Z'},
			{value: 'list_title_sort desc',label:'Title Z-A'},
			{value: 'earliest_year asc', label:'Commencement <i class="fa fa-sort-amount-asc"></i>'},
			{value: 'earliest_year desc', label:'Commencement <i class="fa fa-sort-amount-desc"></i>'},
			{value: 'latest_year asc', label:'Completion <i class="fa fa-sort-amount-asc"></i>'},
			{value: 'latest_year desc', label:'Completion <i class="fa fa-sort-amount-desc"></i>'},
			{value: 'funding_amount asc', label:'Funding Amount <i class="fa fa-sort-amount-asc"></i>'},
			{value: 'funding_amount desc', label:'Funding Amount <i class="fa fa-sort-amount-desc"></i>'}
		],

		advanced_fields: [
			{'name':'terms', 'display':'Search Terms', 'active':true},
			{'name':'subject', 'display':'Subject'},
			{'name':'group', 'display':'Data Provider'},
			{'name':'access_rights', 'display':'Access'},
			{'name':'license_class', 'display':'Licence'},
			{'name':'temporal', 'display':'Time Period'},
			{'name':'spatial', 'display':'Location'},
			{'name':'review', 'display':'Review'},
			{'name':'help', 'display':'<i class="fa fa-question-circle"></i> Help'}
		],

		advanced_fields_party: [
			{'name':'terms', 'display':'Search Terms', 'active':true},
			{'name':'type', 'display':'Type'},
			{'name':'subject', 'display':'Subject'},
			{'name':'group', 'display':'Data Provider'},
			{'name':'review', 'display':'Review'},
			{'name':'help', 'display':'<i class="fa fa-question-circle"></i> Help'}
		],

		advanced_fields_service: [
			{'name':'terms', 'display':'Search Terms', 'active':true},
			{'name':'type', 'display':'Type'},
			{'name':'subject', 'display':'Subject'},
			{'name':'group', 'display':'Data Provider'},
			{'name':'review', 'display':'Review'},
			{'name':'help', 'display':'<i class="fa fa-question-circle"></i> Help'}
		],

		advanced_fields_activity: [
			{'name':'terms', 'display':'Search Terms', 'active':true},
			{'name':'type', 'display':'Type'},
			{'name':'activity_status', 'display':'Status'},
			{'name':'subject', 'display':'Subject'},
			{'name':'administering_institution', 'display':'Managing Institution'},
			{'name':'date_range', 'display':'Date Range'},
			{'name':'funders', 'display':'Funder'},
			{'name':'funding_scheme', 'display':'Funding Scheme'},
			{'name':'funding_amount', 'display':'Funding Amount'},
			{'name':'review', 'display':'Review'},
			{'name':'help', 'display':'<i class="fa fa-question-circle"></i> Help'}
		],

		collection_facet_order: ['group', 'access_rights', 'license_class', 'type'],
		activity_facet_order: ['type', 'activity_status', 'funding_scheme', 'administering_institution', 'funders'],

		ingest: function(hash) {
			this.filters = this.filters_from_hash(hash);
			if (this.filters.q) this.query = this.filters.q;
			// $log.debug(this.available_search_type);
			var that = this;

			if(that.filters['class']!='activity') {
				angular.forEach(this.search_types, function(x){
					var term = x.value;
					if (that.filters.hasOwnProperty(term)) {
						that.query = that.filters[term];
						that.search_type = term;
					}
				});
			} else {
				angular.forEach(this.search_types_activities, function(x){
					var term = x.value;
					if (that.filters.hasOwnProperty(term)) {
						that.query = that.filters[term];
						that.search_type = term;
					}
				});
			}
		
			return this.filters;
		},

		reset: function(){
			var prev_class = this.filters['class'];
			this.filters = {q:'', 'class': prev_class};
			this.search_type = 'q';
			this.query = '';
		},

		update: function(which, what) {
			this[which] = what;
		},

		update_class: function(what) {
			this.default_filters['class'] = what;
		},

		search: function(filters){
			this.status = 'loading';
            filters = this.cleanFilters(filters);
			// $log.debug('search filters', filters);
			var promise = $http.post(base_url+'registry_object/filter', {'filters':filters}).then(function(response){
				this.status = 'idle';
				if(response.data.response && response.data.responseHeader.status==0) {
					return response.data;
				} else {
					$log.debug(response);
					return false;
				}
			});
			return promise;
		},

        cleanFilters: function(filters) {
          angular.forEach(filters, function(value, index) {
            if (value=='') delete filters[index];
          });
          return filters;
        },

		search_no_record: function(filters) {
			var promise = $http.post(base_url+'registry_object/filter/true', {'filters':filters}).then(function(response){
				this.status = 'idle';
				return response.data;
			});
			return promise;
		},

		construct_facets: function(result, sclass) {
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
            if(result.error)  console.log(result);
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

			if(sclass=='collection') {
				order = this.collection_facet_order;
			} else if(sclass=='activity') {
				order = this.activity_facet_order;
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

			// $log.debug(result.facet_counts.facet_fields.earliest_year);

			var earliest_array = result.facet_counts.facet_fields.earliest_year;
			var latest_array = result.facet_counts.facet_fields.latest_year;


			for (i=0;i<earliest_array.length-1;i+=2) {
				if (earliest_year && parseInt(earliest_array[i]) < earliest_year) {
					earliest_year = parseInt(earliest_array[i]);
				} else if(!earliest_year || earliest_year=='') {
					earliest_year = parseInt(earliest_array[i]);
				}
			}

			for (i=0;i<latest_array.length-1;i+=2) {
				if (latest_year && parseInt(latest_array[i]) > latest_year) {
					latest_year = parseInt(latest_array[i]);
				} else if(!latest_year) {
					latest_year = parseInt(latest_array[i]);
				}
			}

			if(earliest_year && latest_year) {
				// $log.debug(earliest_year, latest_year);
				for(i = parseInt(earliest_year); i < parseInt(latest_year)+1;i++){
					range.push(i);
				}
			}

			// $log.debug(range);

			return range;
		},

		filters_from_hash:function(hash) {

			var xp = hash.split('/');
			var filters = {};
			$.each(xp, function(){
				var t = this.split('=');
				var term = t[0];
				var value = t[1];
				if(term=='rows'||term=='year_from'||term=='year_to' && value.trim()!='') value = parseInt(value);
				if(term=='funding_from' || term=='funding_to') {
					value = decodeURIComponent(value);
					value = Number(value.replace(/[^0-9\.-]+/g,""));
				}
				if(term && value && term!='' && value!=''){

					if(filters[term]) {
						if(typeof filters[term]=='string') {
							var old = filters[term];
							filters[term] = [];
							filters[term].push(old);
							filters[term].push(decodeURIComponent(value));
						} else if(typeof filters[term]=='object') {
							filters[term].push(decodeURIComponent(value));
						}
					} else {
						filters[term] = decodeURIComponent(value);
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
			} else if(location.href.indexOf('search')>-1) {
				$('#banner-image').css('background-image', "url('"+base_url+"assets/core/images/collection_banner.jpg')");
			}

			return filters;
		},
		filters_to_hash: function(filters) {
			var hash = '';
			$.each(filters, function(i,k){
				if(typeof k!='object'){
					hash+=i+'='+encodeURIComponent(k)+'/';
				} else if (typeof k=='object'){
					$.each(k, function(){
						hash+=i+'='+encodeURIComponent(this)+'/';
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