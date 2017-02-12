<div class="tag_content">
	<div class="btn-toolbar tags">
		<?php if($own_themepages): ?>
		<?php foreach($own_themepages as $t): ?>
		<div class="btn-group" ro_key="<?php echo $ro->key; ?>" tag="<?php echo $t['secret_tag'] ?>">
			<button class="btn btn-small"><?php echo $t['title'];?></button>
			<button class="btn btn-small btn-remove"><i class="icon icon-trash"></i></button>
		</div>
		<?php endforeach; ?>
		<?php else: ?>
		<div class="notag">This record has no Theme Page affiliation</div>
		<?php endif; ?>
	</div>
</div>
<hr/>
<form class="form theme_tag_form" ro_id="<?php echo $ro->id;?>" ro_key="<?php echo $ro->key; ?>">
	<div class="input-append">
		<select name="" id="secret_tag">
			<?php foreach($themepages as $t): ?>
			<?php if($t['secret_tag']!=''): ?>
			<option value="<?php echo $t['secret_tag'];?>"><?php echo $t['title']; ?></option>
			<?php endif; ?>
			<?php endforeach; ?>
		</select>
		<button class="btn" ro_key="<?php echo $ro->key; ?>"><i class="icon icon-plus"></i> Add</button>
	</div>
	
	<div id="status_message"></div>
</form>