<?php 
/**
 * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */
?>

<?php  $this->load->view('header');?>
<?php echo $content;?>
<script type="text/x-mustache"  id="related_object_search_result">
<ul class="search_related_list">
{{#results}}
	<li><a href="javascript:;" key="{{key}}" class="select_related"><img src="<?php echo asset_url('img/{{class}}.png', 'base');?>"/><span>{{title}}</span></a></li>
{{/results}}
{{#no_result}}
	<li>No Result</li>
{{/no_result}}
</ul>
</script>


<script type="text/x-mustache"  id="save-record-template">
	<div class="alert alert-success alert-block">
		<h4>Record Saved!</h4>
			Your Record has been saved in DRAFT state. You can continue editing by clicking one of the sections in the left menu.
	</div>

	{{#action_bar}}
	<p>
		{{& action_bar}}
	<br class="clear"/>
	</p>
	{{/action_bar}}

	<hr/>
	<h5><span class="icon icon-list-alt"></span> Metadata Content Report</h5>

	<div class="qa">
		{{{qa}}}
	</div>

	<div class="alert alert-info alert-block">
		<strong>Note</strong>: Draft records are not yet visible in Research Data Australia. Use the action bar above to progress your record towards being Published in Research Data Australia.
	</div>

</script>



<script type="text/x-mustache"  id="save-error-record-template">
<div class="alert alert-error alert-block"><strong>This DRAFT NOT BEEN SAVED due to validation errors in the record</strong><br/> Please refer to the tabs marked with a red error icon to the left of the page. Additional information is provided below:</div>
<div class="alert well alert-error alert-block"><pre>{{{message}}}</pre></div>
<div class="alert alert-warning alert-block">
	As a precaution, if you are unable to resolve the issues with this record, we recommend you download a copy of the <button class="btn btn-warning btn-mini show_rifcs">Record RIFCS <i class="icon-white icon-download-alt"></i></button>
</div>
</script>



<?php $this->load->view('footer');?>