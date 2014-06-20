<?php 

/**
 * Core Data Source Template File
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/registry_object/_registry_object
 * @package ands/registry_object
 * 
 */
date_default_timezone_set('Australia/Melbourne');
?>
<?php $this->load->view('header');?>
<div id="content" style="margin-left:0px">
	<div class="content-header">
		
		<h1><?php echo $ro->title;?> <?php if($viewing_revision) echo '<small>('.$revisionInfo.')</small>'?></h1>
		
		<input class="hide" type="hidden" value="<?php echo $ro->id;?>" id="ro_id"/>
		<input class="hide" type="hidden" value="<?=$ds->id;?>" id="data_source_id"/>
		<input type="hidden" id="registry_object_id" value="<?php echo $ro_id;?>"/>
		<?php 
		if ($this->user->hasFunction('REGISTRY_USER') && $this->user->hasAffiliation($ds->record_owner)):
		?>
			<ul class="nav nav-pills" style="margin-right:80px;padding-top:5px;">
				<li class=""><?php echo anchor('data_source/manage#!/view/'.$ds->id,'Dashboard');?></li>
				<li class=""><?php echo anchor('data_source/manage_records/'.$ds->id,'Manage Records');?></li>
				<li class=""><?php echo anchor('data_source/report/'.$ds->id,'Reports');?></li>
				<li class=""><?php echo anchor('data_source/manage#!/settings/'.$ds->id,'Settings');?></li>
			</ul>
						<div class="btn-group">
				<?php 
					if(!$viewing_revision && (!in_array($ro->status, array(SUBMITTED_FOR_ASSESSMENT, ASSESSMENT_IN_PROGRESS)) || $this->user->hasFunction('REGISTRY_SUPERUSER'))) {
						echo anchor('registry_object/edit/'.$ro->id, '<i class="icon-edit"></i> Edit', array('class'=>'btn btn-small', 'title'=>'Edit Registry Object'));
						// XXX: Delete?
					}
				?>
			</div>
		<?php 
		endif;
		?>

	</div>
	<div id="breadcrumb">
		<?php 
			if ($this->user->hasFunction('REGISTRY_USER') && $this->user->hasAffiliation($ds->record_owner)) 
			{
				// // User has registry access...links can be more specific
				echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'title'=>'Go to Home'));
				echo anchor('data_source/manage_records/'.$ds->id, ($ds->title.' - Manage Records' ?: "unnamed datasource"), array('class'=>'', 'title'=>''));
			}
			else
			{
				// Just a guest user, take them to the *real* home page, no link to data source
				echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'title'=>'Go to Home'));
			}
		?>
		<a href="#" class="current"><?php echo $ro->title;?> </a>
		<?php if($viewing_revision) echo '<a href="#">('.$revisionInfo.')</a>'?>
	</div>

	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span8">
				<?php
					// If we reached this page as a result of a record action in
					// the add registry object screen, we display a message here...
					switch($this->input->get('message_code'))
					{
						case "SUBMITTED_FOR_ASSESSMENT":
							echo '<div class="alert alert-success"><strong>Congratulations!</strong> Your record has been submitted to ANDS for assessment and approval. <br/><small><strong>Note:</strong> You should contact your ANDS Client Liaison Officer to let them know the records are ready for assessment.</small></div>';
						break;
						case "SUBMITTED_FOR_ASSESSMENT_EMAIL_SENT":
							echo '<div class="alert alert-success"><strong>Congratulations!</strong> Your record has been sent to ANDS for assessment and approval. An ANDS Quality Assessor has been notified by email. </div>';
						break;
						case "APPROVED":
							echo '<div class="alert alert-success"><strong>Congratulations!</strong> Your record has been approved. It will be published to Research Data Australia when you change the status to Published.</div>';
						break;
						case "PUBLISHED":
							echo '<div class="alert alert-success"><strong>Congratulations!</strong> Your record has been successfully published! It will now be visible in Research Data Australia ' . anchor(portal_url().$ro->slug, "here", array("target"=>"_blank")) . '.</div>';
						break;
						case "PUBLISHED_OVERWRITTEN":
							echo '<div class="alert alert-success">Your record has been successfully published! It will now be visible in Research Data Australia ' . anchor(portal_url().$ro->slug, "here", array("target"=>"_blank")) .
								 '. <br/><small><strong>Note:</strong> The previous published version of this record has been overwritten. It has been saved as a revision (below). </small>' . 
								'</div>';
						break;
						default:
					}
				?>

				<?php echo $rif_html;?>
			</div>
			<div class="span4">

				<div>
					<center>
					<?php 
						if($ro->status=='PUBLISHED')
						{
							$anchor = portal_url().$ro->slug.'/'.$ro->registry_object_id;
							echo anchor($anchor, '<i class="icon-globe icon icon-white"></i> &nbsp; View in Research Data Australia', array('class'=>'btn btn-info','target'=>'_blank'));
						}
						else
						{
							$anchor = portal_url().'view/?id='.$ro->id ;
							echo anchor($anchor, '<i class="icon-globe icon-white"></i> &nbsp; Preview in Research Data Australia', array('class'=>'btn btn-info','target'=>'_blank'));
						}
					?>				
					</center>
				</div>

				<?php 
				if ($this->user->hasFunction('REGISTRY_USER') && $this->user->hasAffiliation($ds->record_owner)):
				?>
					<div class="widget-box">
						<div class="widget-title">
							<h5>Quality Report</h5>
						</div>
						<div class="widget-content nopadding">
							<?php echo $quality_text;?>
						</div>
					</div>
				<?php
				endif;
				?>

				<?php 
				if ($this->user->hasFunction('REGISTRY_USER') && $this->user->hasAffiliation($ds->record_owner)):
				?>
				<div class="widget-box">
					<div class="widget-title">
						<h5>Registry Metadata</h5>
						<?php 
						if (isset($action_bar) && is_array($action_bar) && count($action_bar) > 0)
						{
							echo '<div class="btn-group pull-right">
									  <a class="btn btn-small btn-warning dropdown-toggle" data-toggle="dropdown" href="#">
									    Change Status
									     <span class="caret"></span>
									  </a>
									  <ul class="dropdown-menu">
									   	';
									   foreach ($action_bar AS $action)
									   {
									   	echo '<li><a class="status_change_action" to="'.$action.'">To ' . readable($action,true) . '</a></li>';
									   }

							echo '	  </ul>
									</div>';
						}
						?>
					</div>
					<div class="widget-content">

						<table class="table table-bordered table-striped table-small">
							<tr><th>Title</th><td><?php echo $ro->title;?></td></tr>
							
							<?php if(!($viewing_revision && !$currentRevision))
							{
								echo "<tr><th>Status</th><td><strong>" . readable($ro->status, true) . "</strong></td></tr>"; 
							}
							else
							{
								echo "<tr><th>Status</th><td style='background-color:#FF6633; color:white;'><b>SUPERSEDED</b></td></tr>"; 
							}
							?>
							<tr><th>Data Source</th><td><?php echo $ds->title;?></td></tr>
							<tr><th>Key</th><td style="width:100%; word-break:break-all;"><?php echo $ro->key;?></td></tr>
							<?php 
							if ($this->user->hasFunction('REGISTRY_STAFF')):
							?>
								<tr><th>ID</th><td><?php echo $ro->id;?></td></tr>						
								<tr><th>URL "Slug"</th><td><?php echo anchor(portal_url($ro->slug),$ro->slug);?></td></tr>
							<?php
							endif;
							?>
							<tr><th>Last edited by</th><td><?php echo $ro->getAttribute('created_who'); ?></td></tr>
							<tr><th>Date last changed</th><td><?php echo date("j F Y, g:i a", (int)$ro->getAttribute('updated')); ?></td></tr>
							<tr><th>Date created</th><td><?php echo date("j F Y, g:i a", (int)$ro->getAttribute('created')); ?></td></tr>
							<tr><th>Feed type</th><td><?php echo (strpos($ro->getAttribute('harvest_id'),'MANUAL') === 0 ? 'Manual entry' : 'Harvest');?></td></tr>
							<tr><th>Quality Assessed</th><td><?php echo ucfirst($ro->getAttribute('manually_assessed') ? $ro->getAttribute('manually_assessed') : 'no');?></td></tr>
							
							<?php 
								if($native_format != 'rif') {
									echo '<tr><th>Native Format</th><td><a href="javascript:;" class="btn btn-small" id="exportNative"><i class="icon-eject"></i>Export '.$native_format.'</a></td></tr>';
								}
							?>
							<?php if(!($viewing_revision && !$currentRevision)): ?>
								<tr><td colspan="2"><a class="btn btn-small btn-danger pull-right" id="delete_record_button"> <i class="icon-white icon-warning-sign"></i> Delete Record <i class="icon-white icon-trash"></i> </a></td></tr>
							<?php endif; ?>
						</table>
					</div>

				</div>
				<?php
				endif;
				?>


				<div class="widget-box">
					<div class="widget-title">
						<h5>Revision</h5>
						<?php if($this->user->hasFunction('REGISTRY_SUPERUSER')): ?>
						<a href="javascript:;" class="btn btn-small pull-right" style="margin-top:5px; margin-right:5px;" id="exportExtRif"><i class="icon-eject"></i> Show ExtRIF</a>
						<a href="javascript:;" class="btn btn-small pull-right" style="margin-top:5px; margin-right:5px;" id="exportSOLR"><i class="icon-eject"></i> Show SOLR DOC</a>
						<?php endif; ?>
						<a href="javascript:;" class="btn btn-small pull-right" style="margin-top:5px; margin-right:5px;" id="exportRIFCS"><i class="icon-eject"></i> Show RIFCS</a>
						<?php if($ro->native_path): ?>
						<a href="javascript:;" class="btn btn-small pull-right" style="margin-top:5px; margin-right:5px;" id="exportNative"><i class="icon-eject"></i> Show Native</a>
						<?php endif; ?>
					</div>
					<div class="widget-content">
						<ul>
						<?php
							foreach($revisions as $time=>$_revision){
								if (!$_revision['current'])
								{
									$link = 'registry_object/view/'.$ro->id.'/'.$_revision['id'];

									// Bold if currently viewing this verision
									if ($_revision['id'] == $revision)
									{
										$text = "<strong>" . $time . "</strong>";
									}
									else
									{
										$text = $time;
									}
								}
								else
								{
									$link = 'registry_object/view/'.$ro->id;

									if (!$revision)
									{
										$text = "<strong>" . $time . "</strong>";
									}
									else
									{
										$text = $time;
									}
								}
								echo '<li>'.anchor($link, $text . ($_revision['current'] ? " (most recent version)" : "")).'</li>';
							}
						?>
						</ul>

					</div>
				</div>

				<?php if ($this->user->hasFunction('REGISTRY_USER') && $this->user->hasAffiliation($ds->record_owner)): ?>
				<div class="widget-box">
					<div class="widget-title">
						<h5>Tags Management</h5>
					</div>
					<div class="widget-content">
						<?php $data['tags'] = $tags; $this->load->view('tagging_interface', $data);?>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title">
						<h5>Add to Theme Page</h5>
					</div>
					<div class="widget-content">
						<?php $data['themepages'] = $themepages; $data['own_themepages'] = $own_themepages; $this->load->view('theme_tagging_interface', $data);?>
					</div>
				</div>

				<?php endif;?>
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
<?php $this->load->view('footer');?>