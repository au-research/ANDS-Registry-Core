<?php 

/**
 * SDMX Query Tool interface
 * 
 * @author Ben Greenwood <ben.greenwood@anu.edu.au>
 * @package ands/abs_sdmx_querytool
 * 
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
				    	
			    	 <form method="GET">
			    	 	
					  <label><strong>Enter your SDMX Query</strong> (include &lt;message:Query&gt; tags)</label>
					  <textarea rows="6" style="width:350px;" name="query" id="query" placeholder="e.g.
					  
					  <message:Query>
					  
					  your query
					  
					 </message:Query>"></textarea>
					  
					  <?php
					  	if ($cookie = $this->input->cookie('last_used_sdmx_query')):
					  ?>
					  	<script type="text/html" id="reusable_query"><?=$cookie;?></script><br/>
					  	<a class="small" onClick="$('#query').val($('#reusable_query').html());$(this).hide();">Re-use previous query...</a>
					  <?php
						endif;
					  ?>
					  <br/>
			    	  <button type="button" class="btn" id="query_sender">Send Query</button>
			    	  
			    	 </form>
			    </div> 
			    
			    
			    
			    <div class="span6">
				<br/><br/>
				<a id="view_query_btn" href="#queryModal" role="button" class="btn btn-small hide" data-toggle="modal">View Query Contents</a>
				<br/><br/>
					<div id="output"></div>    	
			    </div> 
			    
			    
			    
			    <div id="queryModal" class="modal hide fade">
				  <div class="modal-header">
				    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				    <h3>Remote Query</h3>
				  </div>
				  <div class="modal-body">
				  	<h5>Query Header</h5>
				    <p><pre id="query_header"></pre></p>
				    <br/>
				    <h5>Query Content</h5>
				    <p><pre id="query_content"></pre></p>
				  </div>
				  <div class="modal-footer">
				    <a href="#" data-dismiss="modal" aria-hidden="true" class="btn">Close</a>
				  </div>
				</div>
		   </div>
		</div>
		
	</div>
</div>


</section>

</div>
<?php $this->load->view('footer');?>