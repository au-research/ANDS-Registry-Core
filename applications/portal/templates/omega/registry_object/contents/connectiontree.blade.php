@if($ro->connectiontrees[0]['children'])
<div class="panel panel-primary panel-content swatch-white">
	<div class="panel-heading">Connection Tree</div>
	<div class="panel-body">
		<?php $data = urlencode(json_encode($ro->connectiontrees));?>
		<div id="connectionTree" mydata="<?=$data?>" ro_id="<?=$ro->id?>"> </div>
	</div>
</div>
@endif