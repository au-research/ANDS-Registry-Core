<div class="tag_content">
	<div class="alert alert-info">
		Tags are shared between published and drafts records
	</div>
	<ul class="tags" ro_id="<?php echo $ro->id;?>" ro_key="<?php echo $ro->key; ?>">
		<?php
			if($tags){
				foreach($tags as $t){
					echo '<li>'.$t.'<span class="hide"><i class="icon icon-remove"></i></span></li>';
				}
			}else{
				echo '<div class="notag">This record has no tags</div>';
			}
		?>
	</ul>
</div>
<hr/>
<form class="form tag_form" ro_id="<?php echo $ro->id;?>" ro_key="<?php echo $ro->key; ?>">
	<div class="input-append">
		<input type="text" class="span8"/>
		<button type="submit" class="btn"><i class="icon icon-plus"></i> Add Tag</button>
	</div>
	<div id="status_message"></div>
</form>