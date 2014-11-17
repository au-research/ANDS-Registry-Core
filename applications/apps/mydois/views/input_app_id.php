<?php 

/**
 * App ID input screen
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/mydois/controllers/mydois
 * @package ands/mydois
 * 
 */
?>
<?php $this->load->view('header');?>
<div class="container" id="main-content">
	
<section id="list-my-dois-input">
	
<div class="row">
	<div class="span3">&nbsp;</div>
	<div class="span6" id="list-my-dois-input">
		<div class="box">
			<div class="box-header clearfix">
				<h1>DOI Query Tool</h1>
			</div>
			<div class="box-content">
			    	
			    	 <form action="<?=base_url('mydois/show/');?>" method="GET">
			    	 	
					  <label><strong>Enter your DOI AppID</strong></label>
					  <input type="text" name="app_id" placeholder="e.g. f961122b4ef719b9534fd" />
					  <?php
					  	if ($cookie = $this->input->cookie('last_used_doi_appid')):
					  ?>
					  	Recently used: <?=anchor('mydois/show/?app_id=' . $cookie, '('.substr($cookie, 0, 8).'...)');?>
					  <?php
						endif;
					  ?>
					  <br/>
					  
					  <?php
					  	if (isset($associated_app_ids) && $associated_app_ids):
					  ?>
					    <p>
					  	or select from associated DOI App IDs:
						  <select name="app_id_select">
							<option value=""></option>
							<?php
								foreach ($associated_app_ids AS $app_id)
								{
									echo "<option value='".$app_id."'>" . $app_id . "</option>";
								}
							?>
						  </select>
						</p>
					  <?php endif; ?>


			    	  <button type="submit" class="btn">List My DOIs</button>
			    	  
			    	 </form>
			</div> 
		</div>

	</div>
	<div class="span3">&nbsp;</div>
</div>


</section>

</div>
<?php $this->load->view('footer');?>