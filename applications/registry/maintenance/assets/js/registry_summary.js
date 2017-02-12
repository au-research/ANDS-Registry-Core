/**
 */
$(document).ready(function() {
//	google.setOnLoadCallback(drawChart);
//	google.setOnLoadCallback(drawStatusCharts);

	//$('#quality_report_status_dropdown').on('change',function(e){
	//	$('#detailed_report_link').attr('href', $('#detailed_report_link').attr('data-default-href') + '/' + this.value);
	//	$('#download_report_link').attr('href', $('#download_report_link').attr('data-default-href') + '/' + this.value + '/true');
	//	drawChart(null, this.value);
//	});

	$('.drawGraph').each(function(){
		//alert("we have a hit")	
		drawChart($(this), $(this).attr('id'))
	});

	function drawChart(e, ds_id) {

		if (typeof(status) == "undefined") { status = ''; }
	
		$.ajax({
			url: base_url + 'data_source/charts/getDataSourceQualityChart/' + ds_id + "/" + status, 	//?XDEBUG_TRACE=1', //XDEBUG_PROFILE=1&
			type: 'GET',
			success: function(data)
			{
				var chart_data = new google.visualization.DataTable();
				var columns = {};

				// Calculate the sums of quality levels per status for % calculation
				$.each(data, function(i, item){
  					var miniSum=0;
  					$.each(item, function(j, qa_i){
  						if(!isNaN(qa_i))
  						{
  							miniSum += qa_i;

  							if (qa_i != 0)
  							{
  								columns[j] = true;
  							}
 
  						}
  					});
  					data[i].sum = miniSum;

  				});

				var levelChart = {
					"Quality Level 1": 1,
					"Quality Level 2": 2,
					"Quality Level 3": 3,
					"Gold Standard Record": 4,
				}

				// Setup the graph columns, only displaying columns which actually have data...
  				chart_data.addColumn("string", "Class");
  				$.each(columns, function(i, item){
  					if (i != 'sum' && columns[i])
  					{
  						chart_data.addColumn({
  	  				        	label:i,
  	  				        	type:'number'
  	  				    });
  					}
  				});
  				chart_data.addColumn("number", "Sum");

  				// Handle colour corrections
				var chosenColourChart = [];
				var colorChart = {
					"Quality Level 1": '#F06533',
					"Quality Level 2": '#F2CE3B',
					"Quality Level 3": '#6DA539',
					"Gold Standard Record": '#4491AB',
				}
				var levelChart = {
					"Quality Level 1": 1,
					"Quality Level 2": 2,
					"Quality Level 3": 3,
					"Gold Standard Record": 4,
				}
				for (_class in data)
				{
					var row = [_class];
					for (_quality in data[_class])
					{
						if (_quality != 'sum')
						{
							if (columns[_quality])
							{
								// If it hasn't already, add it to the color chart
								if (columns[_quality] > 0)
								{
									if (colorChart[_quality])
									{
										chosenColourChart.push(colorChart[_quality]);
									//	colorChart[_quality] = false;

									}
								}


								// Calculate value as a percentage!
								var numRecords = parseInt(data[_class][_quality]);
								var sumRecords = parseInt(data[_class].sum);
								var qualityLevel = {_quality: levelChart[_quality]}
								row.push({v:numRecords/sumRecords, f:numRecords + " record(s)", p: qualityLevel});
							}
						}
						else
						{
							var sum = parseInt(data[_class][_quality]);
						}
					}
					row.push(sum);

					if (data[_class].sum > 0)
					{
						chart_data.addRow(row);
					}
				}


				// Setup the chart...
				var options = {
				  title: '',
				  sliceVisibilityThreshold:0,
				  isStacked:true,
				  colors: chosenColourChart,
				  hAxis: {title: "",format:'##%' },
				  vAxis: {title: "Class" },
				  chartArea:{left:100},
				  height: 200,
				  legend: {position: 'none'},
				  backgroundColor: '#f9f9f9',
				  fontName:"'Arial'"
				};
				var dataView = new google.visualization.DataView(chart_data);
				
				// Set the display for all columns (Except the first!)
				columns = [0];
				for (var i=1; i<=chart_data.getNumberOfColumns()-1; i++)
				{
					if (chart_data.getColumnLabel(i) != 'Sum')
					{
						columns.push(i);
					}
				}

				if (columns.length > 1)
				{

					dataView.setColumns(columns);
					var chart = new google.visualization.BarChart(document.getElementById('overall_chart_div_'+ds_id));
					chart.draw(dataView, options);

    				google.visualization.events.addListener(chart, 'select', selectHandler)

    				function selectHandler(e){ 

       					if(dataView.getValue(chart.getSelection()[0].row,0).toLowerCase()=="all records")
    					{
    						var classValue = ""
    					}else{
    						var classValue = '"class":"'+dataView.getValue(chart.getSelection()[0].row,0).toLowerCase()+'", '
    					}

    					var selected = chart.getSelection();
     					var quality = dataView.getProperties(selected[0].row,selected[0].column); 

    					var targetString = '{"sort":{"updated":"desc"},"filter":{'+classValue+'"quality_level":"'+quality._quality+'"}}'
    					window.location.href = base_url + "data_source/manage_records/"+ds_id+"/#!/" + targetString
      				}

					var legendBar = '';
					$.each(colorChart, function(i, val)
					{
						legendBar += '<i class="legend-icon" style="background-color:'+ val +'">&nbsp;</i> ' + i;
					});

					$('#quality_status_legend').html(legendBar);
				}
				else
				{
					$('#overall_chart_div_'+ds_id).html("<i>No record data to display</i>");
					$('#overall_chart_div_'+ds_id).css('min-height','40px');	
				}
			},
			dataType: 'json'
		});
	
	} 
});
