<?php 

/**
 * Role Dashboard Interface
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<input type="hidden" id="orcid" value="<?php echo $orcid_id; ?>"/>
<div class="content-header">
	<h1>Import Your Datasets to <img style="margin: -13px 0 0 1px;" src="<?php echo asset_url('img/orcid_tagline_small.png'); ?>"/></h1>
</div>

<div class="container-fluid" id="main-content">
	<div class="row-fluid">

		<div class="span8">
			<div class="widget-box">
				<div class="widget-title">
					<h5>Search for your relevant works in Research Data Australia</h5>
				</div>
				<div class="widget-content">
					<form class="form-search">
					  <div class="input-append">
					    <input type="text" class="search-query" value="<?php echo $name; ?>">
					    <button type="submit" class="btn">Search</button>
					  </div>
					  <!--a class="btn btn-link">Advanced Search <b class="caret"></b></a-->
					</form>
					<hr/>
					<div id="result"></div>
				</div>
			</div>
		</div>

		<div class="span4">
			<div class="widget-box">
				<div class="widget-title">
					<span class="icon">
						<a href="javascript:;" tip="<?php echo $tip; ?>"><i class="icon icon-question-sign"></i></a>
					</span>
					<h5 tip="<?php echo $tip; ?>">Suggested Datasets</h5>
				</div>
				<div class="widget-content">
					<?php
						if(sizeof($suggested_collections) > 0){
							foreach($suggested_collections as $c){
								echo '<div class="suggested_collections" ro_id="'.$c['registry_object_id'].'">';
								echo '<a href="'.portal_url($c['slug']).'">'.$c['title'].'</a>';
								echo '   <a class="remove" href="javascript:;"><i class="icon icon-remove" tip="Remove"></i></a>';
								echo '</div>';
							}
							echo '<hr/><a class="btn import_all_to_orcid btn-primary" ro_id="{{id}}"><i class="icon-white icon-plus"></i> Import All to ORCID</a>';
						}else{
							echo 'We found no relevant collections, please use the search widget';
						}
					?>
				</div>
			</div>

			<div class="widget-box">
				<div class="widget-title"><h5>Datasets already imported from Research Data Australia</h5></div>
				<div class="widget-content" id="imported_records">
					<ul>
						<?php
							if(sizeof($imported) > 0){
								foreach($imported as $c){
									echo '<li class="imported" ro_id="'.$c->registry_object_id.'">';
									echo anchor('registry_object/view/'.$c->registry_object_id,$c->title);
									echo '</li>';
								}
							}else{
								echo "No collections have been imported";
							}
						?>
					</ul>
				</div>
			</div>
		</div>

	</div>
</div>

<script type="text/x-mustache"  id="template">
{{#result.docs}}
	<h5><a href="<?php echo portal_url();?>{{slug}}" target="_blank">{{display_title}}</a></h5>
	<p>{{&description}}</p>
	<p>
		<a class="btn import_to_orcid btn-primary" ro_id="{{id}}"><i class="icon-white icon-plus"></i> Import to ORCID</a>
	</p>
	<hr/>
{{/result.docs}}
</script>

<script type="text/x-mustache"  id="imported">
<ul>
{{#imported}}
	<li><a href="<?php echo portal_url();?>{{slug}}" target="_blank">{{title}}</a></li>
{{/imported}}
</ul>
{{no_result}}
</script>

<div class="modal hide" id="myModal">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3>Alert</h3>
  </div>
  <div class="modal-body"></div>
  <div class="modal-footer">
    
  </div>
</div>
<?php $this->load->view('footer');?>