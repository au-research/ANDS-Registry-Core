<div class="tag_content">
	<ul class="tags" ro_id="<?php echo $ro->id;?>">
		<?php
			if($ro->tag){
				$tags = explode(';;', $ro->tag);
				foreach($tags as $t){
					echo '<li>'.$t.'<span class="hide"><i class="icon icon-remove"></i></span></li>';
				}
			}
		?>
	</ul>
	<?php if(!$ro->tag):?>
		<div class="notag">This record has no tags</div>
	<?php endif;?>
</div>
<hr/>
<form class="form tag_form" ro_id="<?php echo $ro->id;?>">
	<div class="input-append">
		<input type="text" class="span8"/>
		<button type="submit" class="btn"><i class="icon icon-plus"></i> Add Tag</button>
	</div>
</form>
		