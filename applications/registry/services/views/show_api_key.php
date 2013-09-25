<?php 

/**
 * Show API Key
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/services/controllers/services
 * @package ands/services
 * 
 */
?>
<?php $this->load->view('header');?>
<div class="container" id="main-content">
	
<section id="registry-web-services">
	
<div class="row">
	<div class="span6" id="registry-web-services-left">
		<div class="box">
			<div class="box-header clearfix">
				<h1>Registry Web Services <small>API Key Registration</small></h1>
			</div>
		
	
		
			<div>	
				<!-- getRIFCS -->
			    <div class="box-content">
			    	
				   <div class="alert alert-success">
				   	<strong>Success!</strong> Your API Key has been generated.
				   </div>
					

					<p>
						Your API Key registered to <strong><?=$organisation;?></strong> is:
					</p>
					<p>
						<strong style="text-align:middle;"><?=$api_key;?></strong>
					</p>
					<p>
						Use this API key in all requests to ANDS Web Services. For example:<br/>
						<a href="<?=base_url('services/'.$api_key.'/getMetadata.json');?>"><?=base_url('services/');?>/</a><a href="<?=base_url('services/'.$api_key.'/getMetadata.json');?>"><strong><?=$api_key;?></strong></a><a href="<?=base_url('services/'.$api_key.'/getMetadata.json');?>">/getMetadata.json</a>
					</p>

					<p>
						<a href="<?=base_url('services');?>">Back to Services Documentation Page</a>
					</p>
			    </div>
			    
			</div>
		</div>
	</div>

	<div class="span6" style="margin-top:70px;">
		<div class="box">
			<p><span class="label label-warning">What is this?</span></p>
			<div>
				<p>
					All requests to our web services require the use of an API key (in the URL)
					to identify your system when a request is sent.
				</p>
				<p>
					ANDS uses your API key as a method for keeping you informed of changes to
					our services as well monitor the system for quality purposes. 
				</p>
			</div>
		</div>    
	</div>

</div>

</section>

</div>
<?php $this->load->view('footer');?>