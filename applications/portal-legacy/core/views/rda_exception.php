
<div class="error_exception">


	<?php if (ENVIRONMENT == "developmentsss"): ?>
	  <div class="message">
		<img src="<?=asset_url('images/sad_smiley.png','core');?>" alt="Sad Smiley" qip="<?=$message;?>" />

		<div>

		 <h3>We're sorry...</h3>
		 Oops! An error occured:<br/><br/>		
		<div style="width:600px;"><pre><?=$message;?></pre></div>

	<?php elseif(strpos($message, 'key:http://purl.org/au-research/grants/nhmrc/') !== false || strpos($message, 'key:http://purl.org/au-research/grants/arc/') !== false) : 

			
			if(strpos($message, 'key:http://purl.org/au-research/grants/nhmrc/') !== false){
				$institution = 'National Health and Medical Research Council';
				$grantIdPos = strpos($message, 'key:http://purl.org/au-research/grants/nhmrc/') + 45;
				$grantId =	substr ($message, $grantIdPos);
				$purl = substr ($message, strpos($message, 'key:') + 4);
			}
			else{
				$institution = 'Australian Research Council';
				$grantIdPos = strpos($message, 'key:http://purl.org/au-research/grants/arc/') + 43;
				$grantId =	substr ($message, $grantIdPos);
				$purl = substr ($message, strpos($message, 'key:') + 4);
			}
		?>
		<div id="message" class="message" style="width:900px; padding: 50px; padding-bottom: 550px;">
		<img src="<?=asset_url('images/sad_smiley.png','core');?>" alt="Sad Smiley" qip="<?=$message;?>" />

		<div id="grant-query-div">

		<h3>We're sorry...</h3>
		<p>The page or record you are looking for cannot be found or displayed.</p><p>
		Were you looking for information about the <?=$institution;?> grant <b><?=$grantId;?></b>?</p>
		<p>The record for this grant is not available in Research Data Australia yet;<br/> 
		however, we can send you a notification once the grant record has been published in Research Data Australia. To receive the notification, please complete the below form:</p>
		<h4>Register for notification</h4>

			 	<form id="grant-query-form">
					
					<div class="control-group">
						<div class="controls">
							<label class="control-label" for="garnt-id-val">Grant ID: </label><br/>
							<input type="text" size="35" class="input-xlarge" disabled="disabled" name="garnt-id-val" value="<?=$grantId;?>"/>
							<input type="hidden" name="grant-id" value="<?=$grantId;?>"/>
							<input type="hidden" name="purl" value="<?=$purl;?>"/>
							<input type="hidden" name="institution" value="<?=$institution;?>"/>
							<p class="help-inline"><small></small></p>
						</div>
					</div>

					<div class="control-group">
						<div class="controls">
							<label class="control-label" for="grant-title">Grant Title: </label><br/>
							<input type="text" size="80" class="input-xlarge" name="grant-title" value="" placeholder="Title of the grant you were looking for">
							<p class="help-inline"><small></small></p>
						</div>
					</div>
					<hr/>
					<div class="control-group">
						<div class="controls">
							<label class="control-label" for="contact-name">Your Name: </label><br/>
							<input type="text" size="35" class="input-xlarge" name="contact-name" value="" placeholder="Enter your full name" required/>
							<p class="help-inline"><small></small></p>
						</div>
					</div>

					<div class="control-group">
						<div class="controls">
							<label class="control-label" for="contact-company">Your Company / University / Affiliation: </label><br/>
							<input type="text" size="35" class="input-xlarge" name="contact-company" value="" placeholder="Company/ university/ affiliation" required/>
							<p class="help-inline"><small></small></p>
						</div>
					</div>
					<div class="control-group">
						<div class="controls">
							<label class="control-label" for="contact-email">Your Contact Email: </label><br/>
							<input type="email" size="35" class="input-xlarge" name="contact-email" value="" placeholder="Enter your email address" required/>
							<p class="help-inline"><small></small></p>
						</div>
					</div>
					<button type="submit" class="btn btn-primary" id="grant-query-send-button">Submit</button>
				</form>


	<?php else: ?>
  <div class="message">
	<img src="<?=asset_url('images/sad_smiley.png','core');?>" alt="Sad Smiley" qip="<?=$message;?>" />

	<div>	 
	 <h3>We're sorry...</h3>
	 The page or record you are looking for cannot be found or displayed.<br/><br/>

	 We've let our engineers know so that they can take a look at the problem.<br/><br/>You may wish to return to the <a href="<?=base_url();?>">home page</a> or contact <br/><a href="mailto:services@ands.org.au">services@ands.org.au</a> for further support.


	<?php endif; ?>
	</div>

  </div>

</div>