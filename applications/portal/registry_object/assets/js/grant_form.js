app.controller('grantForm', function($scope, $log, $http){

    $scope.processGrantRequestForm = function(){
        var data = {
            contact_email: $scope.contact_email,
            contact_name: $scope.contact_name,
            grant_id: $('#grant_id').val(),
            grant_title: $scope.grant_title,
            institution: $('#institution').val(),
            contact_company: $scope.contact_company,
            purl: $('#purl').val()
        };
        $http.post(base_url+'page/requestGrantEmail', {'data':data}).then(function(response){
                $('#grant-query-div').html(response.message);
        });
    }
});