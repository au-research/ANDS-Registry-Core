<div ng-app="registry_tag" ng-controller="tag">
	<div class="tag_content">
		<div class="alert alert-info">
			Tags are shared between published and drafts records
		</div>

		<div class="btn-toolbar tags">
			<?php if($tags): ?>
			<?php foreach($tags as $t): ?>
			<div class="btn-group" ro_key="<?php echo $ro->key ?>" tag="<?php echo $t['name'] ?>">
				<button class="btn btn-small <?php echo ($t['type']=='secret'?'btn-warning':''); ?>"><?php echo $t['name'] ?></button>
				<button class="btn btn-small btn-remove <?php echo ($t['type']=='secret'?'btn-warning':''); ?>"><i class="icon icon-trash <?php echo ($t['type']=='secret'?'icon-white':''); ?>"></i></button>
			</div>
			<?php endforeach; ?>
			<?php else: ?>
			<div class="notag">This record has no tags</div>
			<?php endif; ?>
		</div>
	</div>
	<hr/>
	<form class="form tag_form" ro_id="<?php echo $ro->id;?>" ro_key="<?php echo $ro->key; ?>">
		<div class="input-prepend input-append">
			<div class="btn-group" style="display:inline-block;">
				<button class="btn dropdown-toggle" data-toggle="dropdown"><span id="tag_type">public</span> <span class="caret"></span></button>
				<ul class="dropdown-menu">
					<li><a href="" class="tag_type_choose">public</a></li>
					<li><a href="" class="tag_type_choose">secret</a></li>
				</ul>
			</div>
			<input type="text" id="tag_value" ng-model="tagToAdd" typeahead="c.value as c.label for c in suggest('tag', tagToAdd) | filter:$viewValue | limitTo:5"/>
			<button type="submit" class="btn" data-loading="Loading..."><i class="icon icon-plus"></i> Add Tag</button>
		</div>
		<div id="status_message"></div>
	</form>
</div>