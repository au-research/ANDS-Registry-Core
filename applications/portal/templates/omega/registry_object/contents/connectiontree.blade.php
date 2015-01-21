@if($ro->connectiontrees[0]['children'])

<h2>This dataset is part of a larger collection </h2>
<?php $data = urlencode(json_encode($ro->connectiontrees));?>
<div id="connectionTree" mydata="<?=$data?>" ro_id="<?=$ro->id?>">
</div>
@endif



