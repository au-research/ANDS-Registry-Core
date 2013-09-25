<?php $this->load->view('rda_header');?>	

<div class="container">

<h1>Browse Research Data Australia</h1>
<dl>
	<dt>ANZSRC Field of Research:</dt>
	<dd><input type="text" id="anzsrc-vocab" name="anzsrc-for" value="" size="40" /><br /> <i>(autocomplete; begin typing something (e.g. "BIOL"))</i></dd>
</dl> 

<div id="vocab-tree">
</div>
	   
<div class="container_clear"></div>
<div class="border"><?php echo $resultsDiv; ?></div>
</div>
<?php $this->load->view('rda_footer');?>