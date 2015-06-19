/**
 * Primary Controller for the Vocabulary CMS
 * For adding / editing vocabulary metadata
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
app.controller('addVocabsCtrl', function($log, $scope, $location, $modal, $templateCache, vocabs_factory){
    vocabs_factory.user().then(function(data){
        $scope.user_orgs = data.message;
    });
    $scope.form = {};

	$scope.vocab = {top_concept:[],subjects:[]};
	$scope.mode = 'add'; // [add|edit]
	$scope.langs = [{"value":"zh","text":"Chinese"},
        {"value":"en","text":"English"},
        {"value":"fr","text":"French"},
        {"value":"de","text":"German"},
        {"value":"it","text":"Italian"},
        {"value":"ja","text":"Japanese"},
        {"value":"mi","text":"Māori"},
        {"value":"ru","text":"Russian"},
        {"value":"es","text":"Spanish"}]
    $scope.licence =["CC-BY","CC-BY-SA","CC-BY-ND","CC-BY-NC","CC-BY-NC-SA","CC-BY-NC-ND","GPL","AusGoalRestrictive","NoLicence","Unknown/Other"]
    $scope.subject_sources=['ANZSRC-FOR','local']

    $scope.opened = false;
	$scope.decide = false;

	$scope.open = function($event) {
	    $event.preventDefault();
	    $event.stopPropagation();
	    $scope.opened = !$scope.opened;
	};

	/**
	 * If there is a slug available, this is an edit view for the CMS
	 * Proceed to overwrite the vocab object with the one fetched from the vocabs_factory.get()
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 */
	if ( $('#vocab_slug').val() ) {
		vocabs_factory.get($('#vocab_id').val()).then(function(data){
			$log.debug('Editing ', data.message);
			$scope.vocab = data.message;
			$scope.mode = 'edit';
			$scope.decide = true;
			$log.debug($scope.form.cms);
		});
	}

	if ($location.search().skip) {
		$scope.decide = true;
	}

	/**
	 * Collect All PoolParty Project
	 */
	$scope.projects = [];
	$scope.ppid = {};
	vocabs_factory.toolkit('listPoolPartyProjects').then(function(data){
		$scope.projects = data;
	});

	/**
	 * Collect all the user roles, for vocab.owner value
	 */
    vocabs_factory.user().then(function(data){
        $scope.user_orgs = data.message['affiliations'];
        $scope.vocab.user_owner = data.message['role_id'];
    });

	$scope.projectSearch = function(q) {
		return function(item) {
			if (item.title.toLowerCase().indexOf(q.toLowerCase()) > -1 || item['id'].toLowerCase().indexOf(q.toLowerCase()) > -1) {
				return true;
			} else return false;
		}
	}


	$scope.skip = function() {
		$scope.decide = true;
	}


	/**
	 * Helper method for helping choosing between the dcterms
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param  obj mess a mess to choose an item from
	 * @return chosen      the chosen one/s
	 */
	$scope.choose = function(mess) {
		
		//the order we should look
		var order_trig = ['concepts.trig', 'adms.trig', 'void.trig'];
		var order_lang = ['value_en', 'value'];

		//find the one with the right trig, default to the first one if none was found
		var which = false;
		angular.forEach(order_trig, function(trig){
			if (mess[trig] && !which) which = mess[trig]; 
		});
		if (!which) which = mess[0];
		// $log.debug(which);

		//find the right value for the right trig, default to the first one
		var chosen = false;
		angular.forEach(order_lang, function(lang){
			if (which[lang] && !chosen) chosen = which[lang];
		});
		if (!chosen) chosen = which[0];
		// $log.debug(trig);

		return chosen;
	}

	/**
	 * Populate the vocab with data
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param suggested project Suggest selected from the typeahead
	 * @return void         
	 */
	$scope.populate = function(project) {
		if (project) {

			//populate data from the PP API first
			$scope.vocab.pool_party_id = project.id;
			$scope.vocab.title = project.title;
			$scope.vocab.description = project.description;
			$scope.vocab.vocab_uri = project.uri;
			$scope.decide = true;
			if (project.availableLanguages) {
				$scope.vocab.language = [];
				angular.forEach(project.availableLanguages, function(lang){
					if (lang.toLowerCase() == 'en') lang = 'English';
					$scope.vocab.language.push(lang);
				});
			}
			if (project.subject) {
				$scope.vocab.subjects = []
				$scope.vocab.subjects.push({subject:project.subject,subject_source:'local'});
			}

			//populate with metadata from toolkit, overwrite the previous data where need be
			vocabs_factory.getMetadata($scope.vocab.pool_party_id).then(function(data){
				// $log.debug(data);
				if (data) {

					if (data['dcterms:title']) {
						$scope.vocab.title = $scope.choose(data['dcterms:title']);
						if (angular.isArray($scope.vocab.title)) $scope.vocab.title = $scope.vocab.title[0];
					}

					if (data['dcterms:description']) {
						$scope.vocab.description = $scope.choose(data['dcterms:description']);
						if (angular.isArray($scope.vocab.description)) $scope.vocab.description = $scope.vocab.description[0];
					}

					$log.debug($scope.vocab);

					if (data['dcterms:subject']) {
						//overwrite the previous ones
						var chosen = $scope.choose(data['dcterms:subject']);

						$scope.vocab.subjects = []
						angular.forEach(chosen, function(theone){
							$scope.vocab.subjects.push({subject:theone,subject_source:'local'});
						});
					}

					//related entity population
					if (!$scope.vocab.related_entity) $scope.vocab.related_entity = [];

					//Go through the list to determine the related entities to add
					var rel_ent = [
						{field:'dcterms:publisher', relationship:'publishedBy'},
						{field:'dcterms:contributor', relationship:'hasContributor'},
						{field:'dcterms:creator', relationship:'hasAuthor'},
					];
					angular.forEach(rel_ent, function(rel){
						if (data[rel.field]) {
							var chosen = $scope.choose(data[rel.field]);
							var list = [];
							if (angular.isString(chosen)) {
								list.push(chosen);
							} else {
								angular.forEach(chosen, function(item){
									list.push(item);
								});
							}
							angular.forEach(list, function(item){

								//check if same item exist
								var exist = false;
								angular.forEach($scope.vocab.related_entity, function(entity){
									if (entity.title==item) exist = entity; 
								});

								if (exist) {
									exist.relationship.push(rel.relationship);
								} else {
									$scope.vocab.related_entity.push({
										title:item,
										type:'party',
										relationship:[rel.relationship]
									});
								}
								
							})
						}
					});

				}
			});
		} else {
			console.log('no project to decide');
		}
	}

	/**
	 * Saving a vocabulary
	 * Based on the mode, add and edit will call different service point
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 */
	$scope.save = function(status) {
		
		$scope.error_message = false;
		$scope.success_message = false;

		//validation
		if (!$scope.validate()) {
			return false;
		}


		if ($scope.mode=='add' || ($scope.vocab.status=='published' && status=='draft')) {
            $scope.vocab.status = status;
			$log.debug('Adding Vocab', $scope.vocab);
			vocabs_factory.add($scope.vocab).then(function(data){
				$log.debug('Data Response from saving vocab', data);
				if(data.status=='ERROR') {
					$scope.error_message = data.message;
				} else {//success
					//navigate to the edit form if on the add form
					// $log.debug(data.message.prop[0].slug);
					var slug = data.message.prop.slug;
                    var id = data.message.prop.id;
					window.location.replace(base_url+"vocabs/edit/"+id);
				}
			});
		} else if ($scope.mode=='edit') {
            $scope.vocab.status = status;
			$log.debug('Saving Vocab', $scope.vocab);
			vocabs_factory.modify($scope.vocab.id, $scope.vocab).then(function(data){
				$log.debug('Data Response from saving vocab (edit)', data);
				if(data.status=='ERROR') {
					$scope.error_message = data.message;
				} else {//success
					$scope.success_message = data.message;
					vocabs_factory.get($scope.vocab.slug).then(function(data){
						$scope.vocab = data.message;
					});
				}
			});
		}
	}

	$scope.validate = function(){

		$log.debug($scope.form.cms);
		if ($scope.form.cms.$valid) {

			//language validation
			if (!$scope.vocab.language || $scope.vocab.language.length == 0) {
				$scope.error_message = 'There must be at least 1 language';
			}

			//subject validation
			if (!$scope.vocab.subjects || $scope.vocab.subjects.length == 0) {
				$scope.error_message = 'There must be at least 1 subject';
			}

			//publisher validation
			if (!$scope.vocab.related_entity) {
				$scope.error_message = 'There must be at least 1 related entity that is a publisher';
			} else {
				var hasPublisher = false;
				angular.forEach($scope.vocab.related_entity, function(obj){
					if (obj.relationship) {
						angular.forEach(obj.relationship, function(rel){
							if (rel=='publishedBy') hasPublisher = true;
						});
					}
				});
				if (!hasPublisher) {
					$scope.error_message = 'There must be a publisher related to this vocabulary';
				}
			}
			
		}

		if ($scope.error_message!=false) {
			return false;
		} else {
			return true;
		}
	}

	$scope.relatedmodal = function(action, type, obj) {
		var modalInstance = $modal.open({
			templateUrl: base_url+'assets/vocabs/templates/relatedModal.html',
			controller: 'relatedCtrl',
			windowClass: 'modal-center',
			resolve: {
				entity: function() {
					if (action=='edit') {
						return obj;
					} else {
						return false;
					}
				},
				type: function() {
					return type;
				}
			}
		});
		modalInstance.result.then(function(obj){
			//close
			if (obj.intent=='add') {
				var newObj = obj.data;
				newObj['type'] = type;
				if (newObj['type']=='publisher') newObj['type'] = 'party';
				if (!$scope.vocab.related_entity) $scope.vocab.related_entity = [];
				$scope.vocab.related_entity.push(newObj);		
			} else if (obj.intent=='save') {
				obj = obj.data;
			}
		}, function(){
			//dismiss
		});
	}

	$scope.versionmodal = function(action, obj) {
		var modalInstance = $modal.open({
			templateUrl: base_url+'assets/vocabs/templates/versionModal.html',
			controller: 'versionCtrl',
			windowClass: 'modal-center',
			resolve: {
				version: function() {
					if (action=='edit') {
						return obj;
					} else {
						return false;
					}
				},
				vocab: function() {
					return $scope.vocab
				},
				action: function() {
					return action;
				}
			}
		});
		modalInstance.result.then(function(obj){
			//close
			if (obj.intent=='add') {
				var newObj = obj.data;
                if(!$scope.vocab.versions) $scope.vocab.versions = [];
				$scope.vocab.versions.push(newObj);
			} else {
				obj = obj.data;
			}
		}, function(){
			//dismiss
		});
	}

	/**
	 * Add an item to an existing vocab
	 * Primarily used for adding multivalued contents to the vocabulary
	 * @param enum type
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 */
	
	$scope.addtolist = function(list, item) {
        if(!$scope.vocab[list]) $scope.vocab[list] = [];
        $scope.vocab[list].push(item);
        $scope.resetValues();
	}

	$scope.resetValues = function() {
		$scope.newValue = {
			language: "",
			subject: {subject:"", subject_source:"" }
		}
	}
	$scope.resetValues();

	$scope.list_remove = function(type, index) {
		if (index > 0) {
			$scope.vocab[type].splice(index, 1);
		} else {
			$scope.vocab[type].splice(0, 1);
		}
	}
});

app.controller('versionCtrl', function($scope, $modalInstance, $log, $upload, version, action, vocab){
	$scope.versionStatuses = ['current', 'superseded', 'deprecated'];
	$scope.version = version ? version : {provider_type:false};
	$scope.action = version ? 'save': 'add';
    $scope.formats=['RDF/XML','TTL','N-Triples','JSON','TriG','TriX','N3','CSV','TSV','XLS','XLSX','BinaryRDF','ODS','ZIP','XML','TXT','ODT', 'TEXT']
    $scope.types=[{"value":"webPage","text":"Web page"},
        {"value":"apiSparql","text":"API/SPARQL endpoint"},
        {"value":"file","text":"File"}
    ]
	//calendar operation
	$scope.opened = false;
	$scope.open = function($event) {
	    $event.preventDefault();
	    $event.stopPropagation();
	    $scope.opened = !$scope.opened;
	};

	$scope.addformat = function(obj) {
		if ($scope.validateAP() || $scope.version.provider_type=='poolparty') {
			if (!$scope.version) $scope.version = {};
			if (!$scope.version['access_points'] || $scope.version['access_points']==undefined) {
				$scope.version['access_points'] = [];
			}
			var newobj = {};
			angular.copy(obj, newobj)
			$scope.version.access_points.push(newobj);
			$scope.newap = {};
		} else return false;
		
	}

	$scope.validateAP = function(){
		delete $scope.ap_error_message;
		if ($scope.apForm.$valid) {
			return true;
		} else {
			return false;
		}
	}

	$scope.validateVersion = function() {
		delete $scope.error_message;
		if ($scope.versionForm.$valid) {

			//at least 1 access point require
			if ($scope.version && $scope.version.access_points && $scope.version.access_points.length > 0) {
				return true;
			} else {
				$scope.error_message = 'At least 1 access point is required';
				return false;
			}
		} else {
			$scope.error_message = 'Form Validation Failed';
			return false;
		}
	}

	$scope.save = function() {
		if ($scope.validateVersion()) {
			var ret = {
				'intent': $scope.action,
				'data' : $scope.version
			}
			$modalInstance.close(ret);
		} else return false;
	}

	//Import version from PoolParty
	$scope.importPP = function(){
		$scope.version.provider_type = 'poolparty';

		//add empty file
		var obj = {
			format: 'RDF/XML',
			type: 'file',
			uri: 'TBD'
		};
		$scope.addformat(obj);

		//add empty apiSparql endpoint
		var obj = {
			format: 'RDF/XML',
			type: 'apiSparql',
			uri: 'TBD'
		}
		$scope.addformat(obj);

		//add empty sissvoc endpoint
		var obj = {
			format: 'RDF/XML',
			type: 'webPage',
			uri: 'TBD'
		}
		$scope.addformat(obj);
	}

	$scope.upload = function(files, ap) {
		if (!ap) ap = {};
		if (files && files.length) {
			for (var i = 0; i < files.length; i++) {
				var file = files[i];
				$scope.uploading = true;
				delete $scope.error_upload_msg;
				$upload.upload({
					url: base_url+'vocabs/upload',
					file: file
				}).progress(function (evt) {
					var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
					$log.debug('progress: ' + progressPercentage + '% ' + evt.config.file.name);
				}).success(function (data, status, headers, config) {
					$log.debug(config);
					$scope.uploading = false;
					if(data.status == 'OK' && data.url) {
						ap.uri = data.url;
					} else if(data.status=='ERROR') {
						$scope.error_upload_msg = data.message;
					}
				});
			}
		}
	}

	$scope.list_remove = function(type, index) {
		if (index > 0) {
			$scope.version[type].splice(index, 1);
		} else {
			$scope.version[type].splice(0, 1);
		}
	}

	$scope.dismiss = function() {
		$modalInstance.dismiss();
	}
});

app.controller('relatedCtrl', function($scope, $modalInstance, $log, entity, type, vocabs_factory){
	$scope.relatedEntityRelations = [
        {"value":"publishedBy","text":"Publisher"},
        {"value":"hasAuthor","text":"Author"},
        {"value":"hasContributor","text":"Contributor"},
        {"value":"pointOfContact","text":"Point of contact"},
        {"value":"implementedBy","text":"Implementer"},
        {"value":"consumerOf","text":"Consumer"}]

	$scope.relatedEntityTypes = ['publisher', 'vocabulary', 'service'];
	$scope.entity = false;
	$scope.intent = 'add';
	if (entity) {
		$scope.entity = entity;
		$scope.intent = 'save';
	}
	$scope.type = type;

	if ($scope.type=='publisher') {
		$scope.type = 'party';
		if (!$scope.entity) {
			$scope.entity = {
				relationship:['publishedBy']
			}
		}
	}

	$scope.populate = function(item, model, label) {
		$log.debug(item);
		$scope.entity.email = item.email;
		$scope.entity.phone = item.phone;
		$scope.entity.id = item.id;

		if (!$scope.entity.urls || $scope.entity.urls.length == 0 ) $scope.entity.urls = item.urls;
		if (!$scope.entity.identifiers || $scope.entity.identifiers.length == 0 ) $scope.entity.identifiers = item.identifiers;
	}

	$scope.list_add = function(type, obj) {
		if (!obj) var obj = {};
		if (type=='identifiers') {
			obj = {id:''};
		} else if(type=='url') {
			obj = {url:''};
		}
		if (!$scope.entity) $scope.entity = {};
		if (!$scope.entity[type]) $scope.entity[type] = [];
		$scope.entity[type].push(obj);
	}

	$scope.list_remove = function(type, index) {
		if (index > 0) {
			$scope.entity[type].splice(index, 1);
		} else {
			$scope.entity[type].splice(0, 1);
		}
	}

	$scope.save = function() {
		if($scope.validateEntity()) {
			var ret = {
				'intent': $scope.intent,
				'data' : $scope.entity
			}
			$modalInstance.close(ret);
		} else return false;
	}

	$scope.validateEntity = function() {
		delete $scope.error_message;
		if ($scope.reForm.$valid) {

			//at least 1 relationship
			if (!$scope.entity || !$scope.entity.relationship || $scope.entity.relationship.length == 0) {
				$scope.error_message = 'At least 1 relationship is required';
				return false
			}

			//at least 1 identifier
			if (!$scope.entity || !$scope.entity.identifiers || $scope.entity.identifiers.length == 0) {
				$scope.error_message = 'At least 1 identifier is required';
				return false
			}


			return true;
		} else {
			$scope.error_message = 'Form Validation Failed';
			return false;
		}
	}

	$scope.dismiss = function() {
		$modalInstance.dismiss();
	}

	vocabs_factory.suggest(type).then(function(data){
		if (data.status=='OK') {
			$scope.suggestions = data.message;
		}
	});
});

app.run(function($rootScope, $templateCache) {
   $rootScope.$on('$viewContentLoaded', function() {
      $templateCache.removeAll();
   });
});

app.filter('languageFilter', function() {
    return function(ln,langs) {
        for(i=0;i<langs.length;i++){
           if(ln==langs[i].value){
               return langs[i].text
           }
        }
        return ln;
     }
});

app.directive('languageValidation', function(){
	return {
		restrict: 'A',
		link: function(scope, elem, attr, ctrl){

		}
	}
});
