<?php 

/**
 * Image Upload Interface
 * 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/apps
 * 
 */
// Default view variables
$registry_statistics = isset($registry_statistics) ? $registry_statistics : array();

?>
<style>
.ui-datepicker-calendar {
    display: none;
    }
</style>
<?php $this->load->view('header');?>

<div style="margin-left:0px">

	<div class="span12">

		<h3>ANDS Management Report</h3>
		<form class="form form-inline">
            <div class="input-append">
            	 <p>   
            	 	<label for="dateFrom"> &nbsp;Date From:  &nbsp; </label> <input name="dateFrom" id="dateFrom" class="datepicker" />  &nbsp; &nbsp; 
            	 	<label for="dateTo">  &nbsp;Date To: &nbsp;</label> <input name="dateTo" id="dateTo" class="datepicker" /><span style="padding:5px"> <button type="submit" class="btn">Get Report</button></span>
            	 </p>
		
			  
			</div>
			
          </form>
    </div>	

	<div class="span12">
		<div class="box-header clearfix">
			<h5>Registry</h5>
		</div>

		<div class="well">
					<table class="table table-striped">
						
						<?php if (!$registry_statistics):?>
						<thead>
							<tr>				
								<th><small><em>No Registry Statistics Available for the given range</em></small></th>
							</tr>													
						</thead>
						<?php else: ?>
						<thead>
						<tr>
							<th></th>
							<?php foreach ($registry_statistics AS $key=>$statistics): ?>						
							<th style="text-align:left"><?php echo $key ?></th>																																
							<?php endforeach; ?>										
						</tr>
						</thead>
						<tr>
							<td>Total records<br />
							<?php 
							reset($registry_statistics);
							$first_key = key($registry_statistics); 
							foreach($registry_statistics[$first_key] as $key=>$count):
							{
								echo ucfirst($key)."<br />";
							}  
							endforeach;?>
							</td>

							<?php foreach ($registry_statistics AS $key=>$statistics): ?>														
							<td>
								<?php 
								$total = 0;
								foreach($statistics as $key=>$count):
									{
										$total = $total + $count;
									}														
								endforeach;
								echo $total."<br />";

								foreach($statistics as $key=>$count):
								{
									echo $count."<br />";
								}  
								endforeach; ?>
							</td>
							<?php endforeach; ?>
						</tr>
						<?php endif; ?>
					</table>
				</div>
				<div class="box-header clearfix">
					<h5>Users</h5>
				</div>
				<div class="well">
					<table class="table table-striped">
						
						<?php if (!$user_statistics):?>
						<thead>
							<tr>				
								<th><small><em>No User Statistics Available for the given range</em></small></th>
							</tr>													
						</thead>
						<?php else: ?>
						<thead>
						<tr>
							<th></th>
							<?php foreach ($user_statistics AS $key=>$statistics): ?>						
							<th style="text-align:left"><?php echo $key ?></th>																																
							<?php endforeach; ?>										
						</tr>
						</thead>
						<tr>
							<td>
							<?php 
							reset($user_statistics);
							$first_key = key($user_statistics); 
							foreach($user_statistics[$first_key] as $key=>$count):
							{
								echo $key."<br />";
							}  
							endforeach;?>
							</td>

							<?php foreach ($user_statistics AS $key=>$statistics): ?>														
							<td>
								<?php 
								
								foreach($statistics as $key=>$count):
								{
									echo $count."<br />";
								}  
								endforeach; ?>
							</td>
							<?php endforeach; ?>
						</tr>


					<?php endif; ?>

					</table>
				</div>
				<div class="box-header clearfix">
					<h5>DOI</h5>
				</div>
				<div class="well">
					<table class="table table-striped">
						
						<?php if (!$doi_statistics):?>
						<thead>
							<tr>				
								<th><small><em>No DOI Statistics Available for the given range</em></small></th>
							</tr>													
						</thead>
						<?php else: ?>
						<thead>
						<tr>
							<th></th>
							<?php foreach ($doi_statistics AS $key=>$statistics): ?>						
							<th style="text-align:left"><?php echo $key ?></th>																																
							<?php endforeach; ?>										
						</tr>
						</thead>
						<tr>
							<td>
							<?php 
							reset($doi_statistics);
							$first_key = key($doi_statistics); 
							foreach($doi_statistics[$first_key] as $key=>$count):
							{
								echo $key."<br />";
							}  
							endforeach;?>
							</td>

							<?php foreach ($doi_statistics AS $key=>$statistics): ?>														
							<td>
								<?php 
								
								foreach($statistics as $key=>$count):
								{
									echo $count."<br />";
								}  
								endforeach; ?>
							</td>
							<?php endforeach; ?>
						</tr>


					<?php endif; ?>

					</table>
				</div>
				<div class="box-header clearfix">
					<h5>PIDs</h5>
				</div>
				<div class="well">
					<table class="table table-striped">
						
						<?php if (!$pids_statistics):?>
						<thead>
							<tr>				
								<th><small><em>No PIDs Statistics Available for the given range</em></small></th>
							</tr>													
						</thead>
						<?php else: ?>
						<thead>
						<tr>
							<th></th>
							<?php foreach ($pids_statistics AS $key=>$statistics): ?>						
							<th style="text-align:left"><?php echo $key ?></th>																																
							<?php endforeach; ?>										
						</tr>
						</thead>
						<tr>
							<td>
							<?php 
							reset($pids_statistics);
							$first_key = key($pids_statistics); 
							foreach($pids_statistics[$first_key] as $key=>$count):
							{
								echo $key."<br />";
							}  
							endforeach;?>
							</td>

							<?php foreach ($pids_statistics AS $key=>$statistics): ?>														
							<td>
								<?php 
								
								foreach($statistics as $key=>$count):
								{
									echo $count."<br />";
								}  
								endforeach; ?>
							</td>
							<?php endforeach; ?>
						</tr>


					<?php endif; ?>

					</table>
				</div>

	
	</div>
</div>



</div>
<br class="clear"/>

<?php $this->load->view('footer');?>