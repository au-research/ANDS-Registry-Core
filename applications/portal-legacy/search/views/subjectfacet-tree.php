<div>
<ul class="vocab_tree_standard vocab_tree">
	<?php foreach($azTree as $display=>$t):?>
		<li class='tree_closed <?php echo ($t['total']==0) ? 'tree_empty' : '';?>'>
			<ins></ins>
			<?php echo $display;?> (<?php echo $t['total'];?>)
			<ul style="display:none;">
			<?php foreach($t['subjects'] as $l):?>
				<li class="tree_leaf <?php echo ($l['count']==0) ? 'tree_empty' : '';?>" vocab_value="<?php echo $l['value'];?>">
					<ins></ins>
					<span><?php echo $l['value'];?> (<?php echo $l['count'];?>)</span>
				</li>
			<?php endforeach;?>
			</ul>
		</li>
	<?php endforeach;?>
</ul>
</div>