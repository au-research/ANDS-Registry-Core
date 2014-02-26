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
				<h1>DOI Test Suite <small><a href=""><?php $app_id ?></a></small></h1>
				<div class="pull-right">
					<a href="<?=base_url('administration/');?>"><small>Back to Admin Panel</small></a>
				</div>
			</div>
		

			<div>	
			    <div class="box-content">

			    	<h3>Test results</h3>
				    <table class="table table-condensed">
						<thead>
						<tr>
							<th>
								Record Details for client from ip <?=$app_id?>
							</th>
							<th>
								Last Pulled Back
							</th>
						</tr>
						</thead>
						<tbody>
						
							<tr>
								<td></td>
								<td></td>
							</tr>
						
							<tr><td colspan="2"><i>No records found...</i></td></tr>

					
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