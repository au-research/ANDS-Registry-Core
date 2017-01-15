@if($ro->connectiontrees[0]['children'])
<div class="panel panel-primary panel-content swatch-white">
	<!-- <div class="panel-heading">Connection Tree</div> -->
	<div class="panel-tools"><a href="" tip="Closely related datasets to this record (they have a parent-child relationship) are displayed in a browsable tree structure to provide contextual information for this record and to facilitate discovery. Browse the related datasets by expanding each tree node. Access a related dataset of interest by clicking on the hyperlink."><i class="fa fa-info"></i></a></div>
	<div class="panel-body">
		<?php $data = rawurlencode(json_encode($ro->connectiontrees));?>
		<h4>This dataset is part of a larger collection</h4>
		<div id="connectionTree" mydata="<?=$data?>" ro_id="<?=$ro->id?>"> </div>
	</div>
</div>
@endif