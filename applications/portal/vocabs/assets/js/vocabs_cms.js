/**
 * Primary Controller for the Vocabulary CMS
 * For adding / editing vocabulary metadata
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
app.controller('addVocabsCtrl', function($log, $scope, vocabs_factory){
	
	$scope.vocab = {};
	$scope.mode = 'add'; // [add|edit]

	/**
	 * If there is a slug available, this is an edit view for the CMS
	 * Proceed to overwrite the vocab object with the one fetched from the vocabs_factory.get()
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 */
	if ( $('#vocab_slug').val() ) {
		vocabs_factory.get($('#vocab_slug').val()).then(function(data){
			$log.debug('Editing ', data.message);
			$scope.vocab = data.message;
			$scope.mode = 'edit';
		});
	}

	/**
	 * Saving a vocabulary
	 * Based on the mode, add and edit will call different service point
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 */
	$scope.save = function() {
		$scope.error_message = false;
		$scope.success_message = false;
		if ($scope.mode=='add') {
			$log.debug('Adding Vocab', $scope.vocab);
			vocabs_factory.add($scope.vocab).then(function(data){
				$log.debug('Data Response from saving vocab', data);
				if(data.status=='ERROR') {
					$scope.error_message = data.message;
				} else {//success
					//navigate to the edit form if on the add form
					// $log.debug(data.message.prop[0].slug);
					var slug = data.message.prop.slug;
					window.location.replace(base_url+"vocabs/edit/"+slug);
				}
			});
		} else if ($scope.mode=='edit') {
			$log.debug('Saving Vocab', $scope.vocab);
			vocabs_factory.modify($scope.vocab.id, $scope.vocab).then(function(data){
				$log.debug('Data Response from saving vocab (edit)', data);
				if(data.status=='ERROR') {
					$scope.error_message = data.message;
				} else {//success
					$scope.success_message = data.message;
				}
			});
		}
	}

	/**
	 * Add an item to an existing vocab
	 * Primarily used for adding multivalued contents to the vocabulary
	 * @param enum type
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 */
	$scope.additem = function(type) {
		var obj = {};
		if (type=='versions') {
			obj = {title:'New Version'}
		}

		if (!$scope.vocab[type]) $scope.vocab[type] = [];
		$scope.vocab[type].push(obj);
	}

});