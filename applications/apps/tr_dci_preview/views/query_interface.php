<?php 

/**
 * 
 * @author Ben Greenwood <ben.greenwood@anu.edu.au>
 * @package apps/tr_dci_preview
 * 
 */
?>
<?php $this->load->view('header');?>
<div class="container" id="main-content">
	
<section>
	
<div class="row">
	<div class="span2">
		&nbsp;
	</div>
	<div class="span8">
		<div class="box">
			<div class="box-header clearfix">
				<h1><?=$title;?></h1>
			</div>
			
			<div class="box-contents">
					Access your data source feed in <i>Thomson Reuters Data Citation Index</i> XML format:
					<br/><small><strong>Note:</strong> Collections which do not have the necessary attributes/relationships will be excluded from the feed.</small>
					<hr/>

					<?php if (!isset($data_sources) || !is_array($data_sources)): ?>
						<i>Oops! You are not the owner of any data sources! :-(</i>
					<?php else: ?>
						<?php foreach ($data_sources AS $_data_source): ?>

							<h3><?=$_data_source->title;?> <small>(<?=$_data_source->count_collection;?> collections)</small></h3>
							<?=($_data_source->count_collection > 200 ? '<span class="label label-warning"> Warning - This data source is very large. Only the first 200 collections will be displayed.</span>' : '');?>

							<?php $url = registry_url('services/api/getDCI/?q=data_source_id:('.trim($_data_source->data_source_id).')'); ?>
							<a href="<?=addslashes($url);?>" target="_blank"><?=$url;?></a><br/><br/>
						<?php endforeach; ?>
					<?php endif; ?>	
			    	  
			</div> 
			    
		</div>
	</div>
</div>


</section>

</div>
<?php $this->load->view('footer');?>