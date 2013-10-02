
<div class="error_exception">

  <div class="message">
	<img src="<?=asset_url('images/sad_smiley.png','core');?>" alt="Sad Smiley" qip="<?=$message;?>" />

	<div>
	<?php if (ENVIRONMENT == "development"): ?>


	 <h3>We're sorry...</h3>
	 Oops! An error occured:<br/><br/>
	
		<div style="width:600px;"><pre><?=$message;?></pre></div>

	<?php else: ?>
	 
	 <h3>We're sorry...</h3>
	 The page or record you are looking for cannot be found or displayed.<br/><br/>

	 We've let our engineers know so that they can take a look at the problem.<br/><br/>You may wish to return to the <a href="<?=base_url();?>">home page</a> or contact <br/><a href="mailto:services@ands.org.au">services@ands.org.au</a> for further support.


	<?php endif; ?>
	</div>

  </div>

</div>