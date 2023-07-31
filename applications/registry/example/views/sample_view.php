<?php 
/*
 * XXX
 */
?>
<?php $this->load->view('header');?>
<div class="container" id="main-content">
	
<section>
	
<div class="row">
	<div class="span12">
		<div class="box">
			<div class="box-header clearfix">
				<h1><?=$title;?></h1>
			</div>
			
			<div class="row-fluid">
				
				<div class="span6">
				    	
			    	 Text 
			    	 
			    </div> 
			    
			    
			    
			    <div class="span6">
					
					A module-specific config option: <?php echo $this->config->item('test_config_data');?>
					
			    </div> 
			    
			    
		   </div>
		</div>
		
	</div>
</div>


</section>

</div>
<?php $this->load->view('footer');?>