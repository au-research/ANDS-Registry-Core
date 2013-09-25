<div class="subjectfacet-header">
	<!-- <input type="text" id="subjectfacet-search" autocomplete="off"/> -->
	<select id="subjectfacet-select">
		<option value="anzsrc-for">ANZSRC-FOR</option>
		<?php foreach($this->config->item('subjects_categories') as $name=>$cat):?>
			<option value="<?php echo $name;?>" <?php if($name==$subjectType) echo 'selected=selected';?>><?php echo $cat['display'];?></option>
		<?php endforeach;?>
	</select>
</div>

<div id="subjectfacet">
	<div></div>
</div>