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
				<h1>NLA Party Pullback <small><a href="<?=$data_source_url;?>">view data source</a></small></h1>
				<div class="pull-right">
					<a href="<?=base_url('administration/');?>"><small>Back to Admin Panel</small></a>
				</div>
			</div>
		

			<div>	
			    <div class="box-content">

			    	<h3>Records recently retrieved from NLA</h3>
				    <table class="table table-condensed">
						<thead>
						<tr>
							<th>
								Record Details
							</th>
							<th>
								Last Pulled Back
							</th>
						</tr>
						</thead>
						<tbody>
						<?php if ($pullback_entries): ?>
						<?php foreach($pullback_entries AS $result): ?>
							<tr>
								<td><?=$result['title'];?> (<small><a target="_blank" href="<?=base_url('registry_object/view/' . $result['registry_object_id']);?>"><?=$result['key'];?></a></small>)</td>
								<td><?=date('M j H:i:s',$result['created']);?></td>
							</tr>
						<?php endforeach;?>
						<?php else: ?>

							<tr><td colspan="3"><i>No records found...</i></td></tr>

						<?php endif; ?>
						</tbody>
						</table>

						<a href="<?=base_url('administration/triggerNLAHarvest');?>" target="_blank" class="btn btn-danger"><i class="icon-flag icon-white"></i> Trigger Manual Update</a></a>
			    </div>
			    
			</div>
		</div>
	</div>
</div>

</section>

</div>
<?php $this->load->view('footer');?>