<?php $this->load->view('rda_header');?>
<input type="hidden" class="hide" id="viewRenderer" value="view" />
<div class="container less_padding">
	<?php
		if (isset($registry_object_contents))
		{
			$this->load->view('main'); 
		}
		else
		{
			echo "<h4>Error: Registry Object could not be loaded</h4>";
		}
	?>

</div>
<?php $this->load->view('rda_footer');?>