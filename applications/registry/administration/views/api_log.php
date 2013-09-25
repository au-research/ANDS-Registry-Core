<?php 

/**
 * API Log Listing
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/administration
 * @package registry/administration
 * 
 */
?>
<?php $this->load->view('header');?>
<div class="container" id="main-content">
	
<section id="registry-api-log">
	
<div class="row">
	<div class="span12" id="registry-api-log-list">
		<div class="box">
			<div class="box-header clearfix">
				<h1>API Log <small>(Last 100 requests)</small></h1>
				<div class="pull-right">
					<a href="<?=base_url('administration/');?>"><small>Back to Admin Panel</small></a>
				</div>
			</div>
		

			<div>	
			    <div class="box-content">
			    	
			    	<table class="table table-condensed">
					<thead>
					<tr>
						<th>
							Time
						</th>
						<th>
							Service
						</th>
						<th>
							Params
						</th>
						<th>
							API Key
						</th>
						<th>
							IP Address
						</th>
						<th>
							Note
						</th>
					</tr>
					</thead>
					<tbody>
					<?php if ($log_entries->num_rows() > 0): ?>
					<?php foreach($log_entries->result_array() AS $result): ?>

						<tr class="<?=($result['status'] == SUCCESS ? 'success' : 'error');?>">
							<td><?=date('M j H:i:s',$result['timestamp']);?></td>
							<td><?=$result['service'];?></td>
							<td><?=htmlentities(substr(rawurldecode($result['params']), 0, 128));?></td>
							<td><?=anchor('administration/api_keys?api_key='.$result['api_key'], $result['api_key']);?></td>
							<td><?=$result['ip_address'];?></td>
							<td><?=$result['note'];?></td>
						</tr>
					<?php endforeach;?>
					<?php else: ?>

						<tr><td colspan="6"><i>No log entries/service requests...</i></td></tr>

					<?php endif; ?>
					</tbody>
					</table>

			    </div>
			    
			</div>
		</div>
	</div>
</div>

</section>

</div>
<?php $this->load->view('footer');?>