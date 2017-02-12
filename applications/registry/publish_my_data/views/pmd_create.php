<?php 
/**
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>

<div class="container" id="main-content">
	<div class="row">
		<div class="span12">
			<div class="box">
				<div class="box-header clearfix"><h1>Publish My Data</h1></div>
				<div class="box-content">

					<p>
					<div class="alert alert-info">
						You are registering to publish your data collection through the ANDS <i>Publish My Data</i> online service. 
						This means that you will provide ANDS with some descriptive metadata about your collection and 
						ANDS will publish this metadata on the world wide web where it can be discovered by the general public.
					</div>
					</p>

					<small><p>
					In using the ANDS <i>Publish My Data</i> online service you agree that:
					</p>
					<ul>
					<li>you will provide ANDS with metadata describing your research collection;</li>
					<li>you will take reasonable steps to ensure the quality, accuracy and currency of the metadata;</li>
					<li>you are authorised to provide the metadata and that in doing so you are not infringing the intellectual property rights or copyright of anybody else;</li>
					<li>you understand that in providing this data you will be registered by ANDS as a 'party' who has an association with this collection; and</li>
					<li>you will only provide metadata that is appropriate for public distribution.</li>
					</ul>

					<p>
					You can expect that:
					</p>
					<ul>
					<li>ANDS will review and publish the metadata you provide about your research collection on the web through Research Data Australia and the ANDS Collections Registry;</li>
					<!--li>ANDS will publish the metadata you provide about yourself as a party related to this collection on the world wide web through Research Data Australia and the ANDS Collections Registry;</li-->
					<li>ANDS will endeavour to provide discovery and access services to publicise your metadata and refer people to your data collection; and</li>
					<li>ANDS will allow third parties to harvest your metadata from our service and to publish it in their services.</li>
					</ul>
					</small>

					<p><small><span class="label label-success"> &nbsp; ! &nbsp;</span> Find more information about the Publish My Data self-service on the <a target="_blank" href="http://www.ands.org.au/services/publish-my-data.html">ANDS website</a>.</small></p>

					<hr/>

					<h4><i>Publish My Data</i> Registration</h4>
					<p>
					By completing this registration form and using the Publish My Data online service, you acknowledge and agree to the terms above:
					</p><br/>


					<form class="form-horizontal" action="publish_my_data/publish" method="post">
					  <div class="control-group">
					    <label class="control-label">Your Name</label>
					    <div class="controls">
					      <input type="text" name="name" required placeholder="Name" value="<?php echo $this->user->name();?>">
					    </div>
					  </div>
					  <div class="control-group">
					    <label class="control-label">Email Address</label>
					    <div class="controls">
					      <input type="email" name="email" required placeholder="Email Address">
					    </div>
					  </div>
					  <div class="control-group">
					    <label class="control-label">Organisation</label>
					    <div class="controls">
					      <input type="text" name="ds_title" required placeholder="Institution or Research Group">
					    </div>
					  </div>

					  <hr/>

					  <div class="control-group">
					    <label class="control-label">Reasons for Publishing<br/><small>(using Publish My Data)</small></label>
					    <div class="controls">
					      <textarea name="notes" placeholder=""></textarea>
					    </div>
					  </div>
					  <div class="control-group">
					    <div class="controls">
					      <button type="submit" class="btn btn-primary">Register an Account</button>
					    </div>
					  </div>
					</form>
				</div>
			</div>
			<!-- <div class="alert alert-error">you have been warned</div> -->
		</div>
	</div>
</div>
<?php $this->load->view('footer');?>