<?php 

/**
 * DOI View Activity Log
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/mydois/controllers/mydois
 * @package ands/mydois
 * 
 */
?>

<table class="table table-hover table-condensed">
	<thead>
		<tr>
			<th>Service</th>
			<th>Date</th>	
			<th>DOI</th>
			<th>Message</th>		
		</tr>
	</thead>
	<tbody>
	<?php foreach($activities AS $act): ?>
		<tr class="<?=($act->result == "FAILURE" ? 'error' : 'success');?>">
			<td><small><?=$act->activity;?></small></td>
			<td>
				<small><?=date('Y-m-d H:i:s', strtotime($act->timestamp));?></small>
			</td>
			<td>
				<small><?=$act->doi_id;?></small>
			</td>
			<td width="60%"><pre><small><?=nl2br($act->message);?></small></pre></td>
		</tr>	
	<?php endforeach; ?>
	</tbody>
</table>
