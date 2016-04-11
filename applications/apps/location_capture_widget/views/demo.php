<?php $this->load->view('header');?>

<div style="width: 500px; margin:auto;">

	<h2>My Institution's Dataset Registration Form</h2>
	<form id="myform">

		<p>
		Dataset Identifier: <br/>
		<input type="text" id="datasetId" size="80" />

		</p>

		<p>
		Dataset Name: <br/>
		<input type="text" id="datasetName" size="80" />
		</p>

		<p>
		Location: <i>(click and draw a point or region | search for a place name)</i><br/>
		<div id="mapContainer"></div>
		</p>
	</form>


</div>


<?php $this->load->view('footer');?>
