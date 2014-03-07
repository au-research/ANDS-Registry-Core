<script type="text/x-mustache" id="link_list_template">
{{#links}}
<div class="ro_preview">
	<div class="ro_preview_header">

		{{#class}}
			<img class="icon-heading" src="<?php echo base_url();?>assets/core/images/{{class}}.png"/>
		{{/class}}
		{{^class}}
			<img class="icon-heading" src="<?php echo base_url();?>assets/core/images/icons/external_link.png"/>
		{{/class}}
		<div class="title">{{{title}}}</div>
	</div>
	<div class="ro_preview_description hide">
		{{description}}
		{{{expanded_html}}}
		{{#display_footer}}
		<div class="ro_preview_footer">
			<a href="{{url}}">View Full Record</a>
		</div>
		{{/display_footer}}
	</div>
</div>
{{/links}}
{{#pagination}}
<div class="pagination" style="display:block;">
	<div class="results_navi">
		<div class='results'>{{count}} results</div>
		<div class="page_navi">Page: {{currentPage}}/{{totalPage}} <span id="separater">|</span> 
			{{#prev}}
				<a href="javascript:;" class="suggestor_paging" offset={{prev}} suggestor="{{suggestor}}">Prev</a>
			{{/prev}}
			{{#next}}
				<a href="javascript:;" class="suggestor_paging" offset={{next}} suggestor="{{suggestor}}">Next</a>
			{{/next}}
		</div>
		<div class="clear"></div>
	</div>
	</div>
</div>
{{/pagination}}
</script>

<div class="footer">
		<div class="foot">
			<p>Research Data Australia is an Internet-based discovery service designed to provide rich connections between data, projects, researchers and institutions, and promote visibility of Australian research data collections in search engines. 
				<a href="<?=base_url('home/about');?>">Read more about us...</a>
			</p>
			<p class="small">ANDS is supported by the Australian Government through the National Collaborative Research Infrastructure Strategy Program and the Education Investment Fund (EIF) Super Science Initiative.</p>
			<a href="http://www.innovation.gov.au/" class="gov_logo"><img src="<?php echo asset_url('images/dept_white.png', 'core');?>" alt="" /></a>
			<a href="http://www.ands.org.au/" class="footer_logo"><img src="<?php echo asset_url('images/footer_logo.jpg', 'core');?>" alt="" /></a>			
		</div><!-- foot -->		
	</div><!-- footer -->	
	<div class="foot_nav">
		<div class="inner">
			<ul>
				<li><a href="<?=base_url('');?>">Home</a></li>
				<li><a href="<?=base_url('home/about');?>">About</a></li>				
				<li><a href="<?=base_url('home/contact');?>">Contact Us</a></li>
				<li><a href="<?=base_url('home/disclaimer');?>">Disclaimer</a></li>	
				<li><a href="<?=developer_url('');?>">Developers</a></li>
				<li><a href="<?=base_url('search/#!/q=/tab=collection');?>">All Collections</a></li>
				<li><a href="<?=base_url('search/#!/q=/tab=party');?>">All Parties</a></li>			
				<li><a href="<?=base_url('search/#!/q=/tab=activity');?>">All Activities</a></li>
				<li><a href="<?=base_url('search/#!/q=/tab=service');?>">All Services</a></li>				
				<li id="registryViewLink"><a href="<?=registry_url('');?>" target="_blank">(Registry View)</a></li>	
				<li id="registryLink"><a href="<?=registry_url('');?>" target="_blank">ANDS Online Services</a></li>											
			</ul>
			<div class="clear"></div>
		</div><!-- inner -->
	</div><!-- foot_nav -->


	 <script>
        localStorage.clear();
        var base_url = '<?php echo base_url();?>';
        var default_base_url = "<?php echo $this->config->item('default_base_url');?>";
        var suffix = '#!/';
        var deployment_state = "<?php echo $this->config->item('deployment_state');?>";
        var rda_service_url = "<?php echo $this->config->item('registry_endpoint'); ?>"
        <!-- urchin code -->
        <?php echo urchin_for($this->config->item('rda_urchin_id')); ?>
        var urchin_id = "<?php echo $this->config->item('rda_urchin_id');?>";
    </script>

	<!-- Zoo Scripts Untouched -->
	<script type="text/javascript" src="<?php echo asset_url('ands_portal.combined.js', 'core'); ?>"></script>

	 <?php if(isset($js_lib)): ?>
	    <?php foreach($js_lib as $lib):?>
	 		<?php if($lib=='googleapi'):?>
	            <script type='text/javascript' src='https://www.google.com/jsapi'></script>
	            <script type="text/javascript">
	            	localGoogle = google;
	            	google.load("visualization", "1", {packages:["orgchart"]});
				</script>
	        <?php endif; ?>
	        <?php if($lib=='google_map'):?>
	           <script type="text/javascript" src="<?php echo $this->config->item('protocol');?>://maps.googleapis.com/maps/api/js?libraries=drawing&amp;sensor=false"></script>
	           <script type="text/javascript" src="<?php echo asset_url('lib/markerclusterer.js', 'base');?>"></script>
	        <?php endif; ?>
	        <?php if($lib=='dynatree'):?>
				<script type="text/javascript" src="<?php echo asset_url('js/jquery.dynatree-1.2.2.js', 'core');?>"></script>
	    	<?php endif; ?>
	    	<?php if($lib=='spacetree'):?>
				<script type="text/javascript" src="<?php echo asset_url('js/spacetree.js', 'core');?>"></script>
	    	<?php endif; ?>
		    <?php if ($lib=='accordion'): ?>
		    	<script src="<?php echo asset_url('lib/tinyaccordion/accordion.js', 'base');?>" type="text/javascript"></script>
		    <?php endif; ?>
		    <?php if ($lib=='vocab_widget'): ?>
		    	<script src="<?php echo apps_url('assets/vocab_widget/js/vocab_widget.js')?>" type="text/javascript"></script>
		    
		    <?php elseif($lib=='angular'):?>
	            <script type="text/javascript" src="<?php echo asset_url('lib/angular.min.js', 'base') ?>"></script>
	            <script type="text/javascript" src="<?php echo asset_url('lib/angularmod/angular-slugify.js', 'base') ?>"></script>
	            <script type="text/javascript" src="<?php echo asset_url('lib/angularmod/sortable.js', 'base') ?>"></script>
	            <script type="text/javascript" src="<?php echo asset_url('lib/angularmod/tinymce.js', 'base') ?>"></script>
	            <script type="text/javascript" src="<?php echo asset_url('lib/angularmod/angular-sanitize-1.0.1.js', 'base') ?>"></script>

	        <?php elseif($lib=='colorbox'):?>
	            <link href="<?php echo asset_url('lib/colorbox/colorbox.css', 'base');?>" rel="stylesheet" type="text/css">
	            <script src="<?php echo asset_url('lib/colorbox/jquery.colorbox-min.js', 'base');?>" type="text/javascript"></script>
	        <?php endif; ?>
		<?php endforeach;?>
	<?php endif; ?>

	<!-- Module-specific styles and scripts -->
    <?php if (isset($scripts)): foreach($scripts as $script):?>
        <script src="<?php echo asset_url('js/' . $script);?>.js"></script>
    <?php endforeach; endif; ?>

</body>
</html>