<?php $this->load->view('rda_header');?>
<div class="container">
	<h3>Who contributes to Research Data Australia?</h3>
	<p><?php echo sizeof($groups);?> research organisations from around Australia contribute information to Research Data Australia.</p> 
	<div id="who_contributes">
		<ul>
			<?php 
				if($links){
					foreach($links as $l){
						echo '<li>'.$l.'</li>';
					}
				}
				
			?>
		</ul>
	</div>
</div><!-- container -->
<?php $this->load->view('rda_footer');?>