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

if(file_exists('assets/shared/dashboard_news/dashboard.html')){
	$news_content = @file_get_contents(asset_url('shared/dashboard_news/dashboard.html', 'base'));
} else {
	$news_content = @file_get_contents('http://services.ands.org.au/documentation/placeholder/dashboard.html');
	if(!$news_content){
		$news_content = '<div style="overflow: hidden; height: 1072px;" class="box-content dash_news">No News content found for your installation create <br/>file <b>assets/shared/dashboard_news/dashboard.html</b></div>';
	}
}


if(get_config_item('environment_name'))
{
  $site_title = get_config_item('environment_name').' Home';
}
else
{
  $site_title = 'ANDS Online Services Home';
}


?>
<?php $this->load->view('header');?>
<span>
	<div class="pull-right">
		<span class="label"><i class="icon-question-sign icon-white"></i>
			<a class="youtube" href="http://www.youtube.com/watch?v=R5q4t73aCoo"> New to these screens? Take a tour!</a>
		</span>
	</div>
	&nbsp;
</span>

<div class="container" id="main-content">
	<div class="row">

		<div class="span7">
			<div class="box">
				<div class="box-header clearfix">
					<h1><?php echo $site_title; ?></h1>	
					<a href="<?php echo portal_url();?>" style="margin-top:5px;" class="btn btn-info pull-right" target="_blank">
					<i class="icon-globe icon icon-white"></i> Visit Research Data Australia</a>
				</div>
				<?php echo $news_content; ?>
			</div>
		</div>

		<div class="span5">
			
			<div class="box hide">
				<div class="box-header clearfix">
					<h1>Affiliations</h1>
				</div>
				<div class="box-content">
					<?php
		      			if($hasAffiliation){
		      				echo '<ul>';
		      				foreach($this->user->affiliations() AS $role){
								echo '<li>'.$role. "</li>";
							}
							echo '</ul>';
		      			}else{
		      				echo '	<p>
		      							You currently do not have any affiliation with any organisation.
		      						</p>';
		      			}

		      			echo '<div class="well">';
		      			echo '<p><select id="organisational_roles" class="chosen">';
		      			foreach($available_organisations as $o){
		      				echo '<option value="'.$o['role_id'].'">'.$o['name'].'</option>';
		      			}
		      			echo '</select></p>';
		      			echo '<p><button class="btn" id="affiliation_signup" localIdentifier="'.$this->user->localIdentifier().'">Affiliate with this Organisation</button></p>';
		      			echo '<p><a href="javascript:;" id="openAddOrganisation">Organisation not in list?</a></p>';
		      			echo '</div>';
		      		?>
				</div>
			</div>

			<div class="hide" id="addOrgHTML">
				<form class="addOrgForm">
					<p>Please enter the name of your organisation to add it to the system:</p>
					<div class="control-group">
						<label class="control-label" for="title">Organisation Name:</label>
						<div class="controls">
							 <input type="text" class="input-large orgName" localIdentifier="<?php echo $this->user->localIdentifier();?>" required maxLength="255"/>
						</div>
					</div>
					<button class="btn" id="confirmAddOrganisation">Add</button>
				</form>
			</div>
	

	<?php
	if(mod_enabled('registry')){
	?>

			<div class="box">
				<div class="box-header clearfix">
					<h1>My Data Sources</h1>
				</div>
				<div class="box-content">
						<?php
							if(!$this->user->hasFunction('REGISTRY_USER'))
							{
								echo '<p>You are not registered as a Data Source Administrator.</p>';

								if ($this->user->affiliations())
								{
									echo '<small><span class="label label-warning"> &nbsp; ! &nbsp;</span> You are already registered as an affiliate with an organisation.</small><br/>';
									echo '<small><span class="label label-important"> &nbsp; ! &nbsp;</span> Please contact <a href="mailto:services@ands.org.au">services@ands.org.au</a> to register for a new Data Source.</small>';
								}
								else
								{
									echo '<br/><small><span class="label label-success"> &nbsp; ! &nbsp;</span> <strong> New Data Publishers </strong> <br/>
										<p>If your institution does not already <a target="_blank" href="'.portal_url('home/contributors').'">contribute to Research Data Australia</a> (at the institutional level), 
										you may wish to use the <a href="'.registry_url('publish_my_data').'"><b>Publish My Data self-service tool</b></a>.</p>
										<p><small><em>Note:</em> Publish My Data self-service is intended for use by researchers at organisations where there is no formal data archiving service and where ANDS has no distributed services in place. 
										Please first check for processes within your institution before using the self-service facility.</small></p></small><br/>';
								}
							}
							elseif(sizeof($data_sources)>0){
								echo '<ul>';
								$i=0;
								for($i=0; $i < sizeof($data_sources) && $i < 7; $i++){
									echo '<li><a href="'.registry_url('data_source/#!/view/'.$data_sources[$i]['data_source_id']).'">'.$data_sources[$i]['title'] . "</a></li>";
								}
								echo '</ul>';

								if ($i < sizeof($data_sources))
								{
									echo '<div style="margin-left:20px;">';
									echo '<select data-placeholder="Choose a Data Source to View" class="chzn-select" id="dashboard-datasource-chooser">';
									echo '	<option value=""></option>';
									foreach($data_sources as $ds){
										echo '<option value="'.$ds['data_source_id'].'">'.$ds['title'].'</option>';
									}
									echo '</select>';
									echo '</div>';
								}
								
							}else{
								echo 'You are not associated with any data sources yet!';
							}
						?>
				</div>
			</div>

			<?php
				// Display recently updated records
				// (don't display records if we have too many data sources
				// to avoid long loading/timeout issues)
				define('LIMIT_RECENTLY_UPDATED_DS_UPPER_BOUND',10);
				if(count($data_sources) < LIMIT_RECENTLY_UPDATED_DS_UPPER_BOUND)
				{
			?>
					<div class="box">
						<div class="box-header clearfix">
							<h3>Recently updated records</h3>
						</div>
						<div class="box-content" id="recentRecordsDashboard">
							<img src="<?=asset_url('img/ajax-loader.gif','base');?>" alt="Loading recently updated record information" />
							<small class="muted"> Fetching data from registry...</small>
						</div>
					</div>				
			<?php
				}
			?>
	<?php
	}
	?>

			<div class="box">
				<div class="box-header clearfix">
					<h1>My Vocabularies</h1>
				</div>
				<div class="box-content">
					<?php
						if($hasAffiliation){
							if(sizeof($group_vocabs)>0){
								echo '<ul>';
								foreach($group_vocabs as $g){
									echo '<li><a href="'.apps_url('vocab_service/#!/view/'.$g->id).'">'.$g->title . "</a></li>";
								}
								echo '</ul>';
							}else{
								echo 'You have not published any vocabularies.';
							}
						}else{
							echo 'You have not published any vocabularies.';
							//echo "You can't manage any vocabulary unless you are affiliate with an organisation";
						}
					?>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal hide" id="myModal">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3>Alert</h3>
  </div>
  <div class="modal-body"></div>
  <div class="modal-footer">
    
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
	 e.setAttribute("src", "../assets/js/update.js"); 
	 document.body.appendChild(e); 
	} 
</script> 

<?php 
/* Dirty hack to detect first login and display the growl notification */
if (isset($_SERVER["HTTP_REFERER"]) && strpos($_SERVER["HTTP_REFERER"], "login") !== FALSE)
{
?>
<script type="text/javascript"> 
	var displayGrowl = "<p>Welcome <strong><?=$this->user->name();?></strong></p>"+
						"<p>You are now successfully logged in using your authentication provider's token of: <strong><?=$this->user->localIdentifier();?></strong></p>"+
						"<p>You can logout at any time by clicking on the user icon at the top right of the screen.</p>";

</script> 
<?php
}
?>


<?php $this->load->view('footer');?>