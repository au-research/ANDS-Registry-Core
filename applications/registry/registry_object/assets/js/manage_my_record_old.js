$(function(){
	//TEST
	var data_source_id = $('#data_source_id').val();
	var oTable = $('#record_table').dataTable( {
		"bJQueryUI": true,
		"bServerSide": true,
		"sPaginationType": "full_numbers",
		"sDom": '<""l>t<"F"fp>',
		"sAjaxSource": base_url+"registry_object/getData/"+data_source_id,
		"sServerMethod": "POST",
		"iDisplayLength": 10,
		"iDisplayStart": 0,
		"aoColumns":[
	        {"sWidth": '50px', "mDataProp": "key", "sClass": "key"},
	        {"sWidth": '300px', "mDataProp": "Title"},
	        {"sWidth": '100px', "mDataProp": "Status"},
	        {"sWidth": '260px', "mDataProp": "Options", bSearchable: false, bSortable: false}
	    ]
	});

	$('.ds_filter').click(function(e){
		e.preventDefault();
		var type = $(this).attr('type');
		var value = $(this).attr('_value');
		//oTable.fnDestroy();
		oTable.fnReloadAjax(base_url+"registry_object/getData/"+data_source_id+'/'+type+'/'+value);
	});
	
});


$.fn.dataTableExt.oApi.fnReloadAjax = function ( oSettings, sNewSource, fnCallback, bStandingRedraw )
{
    if ( typeof sNewSource != 'undefined' && sNewSource != null )
    {
        oSettings.sAjaxSource = sNewSource;
    }
    this.oApi._fnProcessingDisplay( oSettings, true );
    var that = this;
    var iStart = oSettings._iDisplayStart;
     
    oSettings.fnServerData( oSettings.sAjaxSource, [], function(json) {
        /* Clear the old information from the table */
        that.oApi._fnClearTable( oSettings );
         
        /* Got the data - add it to the table */
        for ( var i=0 ; i<json.aaData.length ; i++ )
        {
            that.oApi._fnAddData( oSettings, json.aaData[i] );
        }
         
        oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();
        that.fnDraw();
         
        if ( typeof bStandingRedraw != 'undefined' && bStandingRedraw === true )
        {
            oSettings._iDisplayStart = iStart;
            that.fnDraw( false );
        }
         
        that.oApi._fnProcessingDisplay( oSettings, false );
         
        /* Callback user function - for event handlers etc */
        if ( typeof fnCallback == 'function' && fnCallback != null )
        {
            fnCallback( oSettings );
        }
    }, oSettings );
}

jQuery.fn.dataTableExt.oApi.fnProcessingIndicator = function ( oSettings, onoff )
{
    if( typeof(onoff) == 'undefined' )
    {
        onoff=true;
    }
    this.oApi._fnProcessingDisplay( oSettings, onoff );
};