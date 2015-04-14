<?php /**
 * API Log Listing
 * 
 * @author Liz Woods <liz.woods@ands.org.au>
 * @see ands/administration
 * @package apps/test_suite
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
				<h1>DOI Test Suite <small><a href=""></a></small></h1>
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
									Basic function tests 
								</th>

							</tr>
						</thead>
						<tbody>				
							<tr>
								<td colspan="2"><pre><?php  print_r($test_mint) ?></pre></td>
							</tr>	
							<tr>
								<td colspan="2"><pre><?php  print_r($test_functions) ?></pre></td>
							</tr>																									
						</tbody>
						<thead>
							<tr>
								<th>
									Authentication tests 
								</th>

							</tr>
						</thead>
						<tbody>				
							<tr>
								<td colspan="2"><pre><?php  print_r($authentication) ?></pre></td>
							</tr>																									
						</tbody>
						<thead>
							<tr>
								<th>
									Valid xml tests 
								</th>

							</tr>
						</thead>
						<tbody>				
							<tr>
								<td colspan="2"><pre><?php  print_r($valid_xml) ?></pre></td>
							</tr>																									
						</tbody>
						<thead>
							<tr>
								<th>
									Version Service points 
								</th>

							</tr>
						</thead>
						<tbody>				
							<tr>
								<td colspan="2"><pre><?php print_r($service_point) ?></pre></td>
							</tr>																									
						</tbody>
						<thead>
							<tr>
								<th>
									Result type
								</th>

							</tr>
						</thead>
						<tbody>				
							<tr>
								<td colspan="2"><pre><?php print_r($response_type) ?></pre></td>
							</tr>																									
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