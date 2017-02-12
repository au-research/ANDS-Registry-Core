<?php 

/**
 * API Key Listing
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/administration
 * @package registry/administration
 * 
 */
?>
<?php $this->load->view('header');?>
<div class="container" id="main-content">
	
<section id="registry-api-keys">
	
<div class="row">
	<div class="span12" id="registry-api-keys-list">
		<div class="box">
			<div class="box-header clearfix">
				<h1>API Keys</h1>
				<div class="pull-right">
					<a href="<?=base_url('administration/');?>"><small>Back to Admin Panel</small></a>
				</div>
			</div>
		

			<div>	
			    <div class="box-content">
			    	
			    	<table class="table table-condensed table-striped">
					<thead>
					<tr>
						<th>
							API Key
						</th>
						<th>
							Organisation
						</th>
						<th>
							Email
						</th>
						<th>
							Purpose
						</th>
						<th>
							Queries<small>(Past 30 days)</small>
						</th>
						<th>
							Total Queries
						</th>
					</tr>
					</thead>
					<tbody>
					<?php if (count($api_keys) > 0): ?>
					<?php foreach($api_keys AS $result): ?>

						<tr<?=($this->input->get('api_key') == $result['api_key'] ? " class='success'" : "");?>>
							<td><?=$result['api_key'];?></td>
							<td><?=$result['owner_organisation'];?></td>
							<td><?=$result['owner_email'];?></td>
							<td><?=$result['owner_purpose'];?></td>
							<td><?=$result['queries_this_month'];?></td>
							<td><?=$result['queries_ever'];?></td>
						</tr>
					<?php endforeach;?>
					<?php else: ?>

						<tr><td colspan="6"><i>No API Keys registered...</i></td></tr>

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