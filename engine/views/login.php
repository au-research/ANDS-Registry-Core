<?php 

/**
 * Core Data Source Template File
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/registry_object/_registry_object
 * @package ands/datasource
 * 
 */
?>
<?php $this->load->view('header');?>

<div class="container" id="main-content">
	<div class="row">
			<div class="span3">&nbsp;</div>

			<div class="span6">
				<div class="widget-box">
					<div class="widget-title">
						<h5>Login</h5>
						<div class="buttons">
							<?php printAlternativeLoginControl($authenticators); ?>
						</div>
						<div class="right-widget">
													
						</div>
					</div>
					<div class="widget-content">
						
						<?php if (isset($error_message)): ?>
							<div class="alert alert-error">
								<?php echo $error_message; ?>
							</div>
						<?php endif; ?>
						<?php /* REMOVED - prints user's password to screen
						// USEFUL FOR DEBUGGING ONLY
						if(false): ?>
							<div class="alert alert-error">
								Error: <?php echo $exception->getMessage(); ?>
							</div>
						<?php endif; */ ?>
						<?php 
						printLoginForm($authenticators, $default_authenticator, 'loginForm', $redirect);
						printAlternativeLoginForms($authenticators, $default_authenticator, $redirect);
						?>
					</div>

				</div>
			</div>

			<div class="span3 pull-right">
			</div>
	</div>


	<div class="row">
		<div class="span3">&nbsp;</div>
		<div class="span6">
			<div class="alert alert-info">
				<center>
					<small>Searching for Research Data? <a href="<?php echo portal_url();?>" target="_blank" style="color:inherit;">Visit <b>Research Data Australia</b> <i class="icon-globe icon"></i></a></small>
				</center>
			</div>
		</div>
	</div>
</div>


<!-- Prompt user to upgrade browser -->
<script type="text/javascript"> 
var $buoop = {vs:{i:7,f:3.6,o:10.6,s:4,n:9}} 
	$buoop.ol = window.onload; 
	window.onload=function(){ 
	 try {if ($buoop.ol) $buoop.ol();}catch (e) {} 
	 var e = document.createElement("script"); 
	 e.setAttribute("type", "text/javascript"); 
	 e.setAttribute("src", "../../assets/js/update.js"); 
	 document.body.appendChild(e); 
	} 
</script> 

<?php $this->load->view('footer');?>


<?php

?>
