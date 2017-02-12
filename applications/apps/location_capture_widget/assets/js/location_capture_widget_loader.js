$(document).ready(function() {
    $("#mapContainer").ands_location_widget({
        lonLat:"141.064453,-19.973349 138.251953,-24.527135 142.031250,-24.527135 146.250000,-22.512557 141.064453,-19.973349"

    });

});

// Demo script
$('#btnSubmit').click(function(e){

    e.preventDefault();
    var message = 	"Form data currently contains the following fields:" + "\n"
        +	"==========" + "\n"
        +	"datasetId: " + $('#datasetId').val() + "\n\n"
        +	"datasetName: " + $('#datasetName').val() + "\n\n"
        +	"geoLocation: " + $('#geoLocation').val() + "\n\n";
    alert(message);

});