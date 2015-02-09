@if($ro->connectiontrees[0]['children'])
<div class="panel panel-primary panel-content swatch-white">
	<div class="panel-heading">Connection Tree</div>
	<div class="panel-body">
		<?php $data = urlencode(json_encode($ro->connectiontrees));?>
		<h4>This dataset is part of a larger collection</h4>
		<div id="connectionTree" mydata="<?=$data?>" ro_id="<?=$ro->id?>"> </div>
	</div>
</div>
@endif