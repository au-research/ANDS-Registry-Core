<?php 

/**
 * Web Service API Key registration
 * 
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
			    	
				    <form action="#" method="POST">
					  <legend>Register for an API key</legend>
					  
					  <label>* <strong>Organisation</strong></label>
					  <input name="organisation" type="text" class="input-xlarge" required="required" placeholder="Name of your project or institution">
					  
					  <label>* <strong>Contact Email Address</strong></label>
					  <input name="contact_email" type="text" class="input-xlarge" required="required" placeholder="Your email address">

					  <label><strong>Purpose of this API key</strong></label>
					  <input name="purpose" type="text" class="input-xlarge" placeholder="What cool things are you doing?">
					  <br/>
					  <!--
					  <label class="checkbox">
					    <input type="checkbox"> I agree to the Web Services Terms of Use
					  </label>
					  -->
					  <input type="submit" name="submit" class="btn" value="Register my API key" />
					</form>
					
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