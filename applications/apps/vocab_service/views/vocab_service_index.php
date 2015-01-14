<?php 

/**
 * Core Data Source Template File
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/vocab_services/_vocab
 * @package ands/vocab_services
 * 
 */
?>

<?php  $this->load->view('header');?>
<div class="container" id="main-content">

<div class="modal hide" id="myModal">
	<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal">×</button>
	<h3>&nbsp;</h3>
	</div>
	<div class="modal-body"></div>
	<div class="modal-footer">
		<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
	</div>
</div>

<div class="modal hide" id="myModal-noClose">
	<div class="modal-header">
	<!--button type="button" class="close" data-dismiss="modal">×</button-->
	<h3>Alert</h3>
	</div>
	<div class="modal-body"></div>
	<div class="modal-footer"></div>
</div>

<div id="changeHistoryForm" class="hide">
	<form>
		<p>Please add a change description:</p>
		<p><textarea class="changeHistoryDescription" required></textarea></p>
		<button class="btn btn-primary" id="confirmAddChangeHistory">Add</button>
	</form>
</div>

<div id="add-version-to-vocab" class="hide">
		<form class="form-inline" vocab_id="{{id}}">
			<fieldset>
				<legend>Add Version</legend>
				<div class="control-group">
					<label class="control-label" for="title">Version Title: </label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="title" value="" required placeholder="Enter a title for the version">
						<p class="help-inline"><small></small></p>
					</div>
				</div>
			</fieldset>
			<fieldset>
				<legend>Add Format</legend>

				<div class="control-group">
					<label class="control-label" for="format">File Format: </label>
					<div class="controls">
						<!-- <input type="text" class="input-xlarge typeahead" name="format" value="" placeholder="Enter File Format" required> -->
						<select name="format" id="">
							<option value="SKOS">SKOS</option>
							<option value="OWL">OWL</option>
							<option value="TEXT">TEXT</option>
							<option value="CSV">CSV</option>
							<option value="ZTHES">ZTHES</option>
							<option value="RDF">RDF</option>
							<option value="OTHER">OTHER</option>
						</select>
						<p class="help-inline"><small></small></p>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="type">Type: </label>
					<div class="controls">
						<div class="btn-group toggleAddFormatType" data-toggle="buttons-radio">
							<button type="button" class="btn active" value="file" content="fileSubmit">File</button>
							<button type="button" class="btn" value="uri" content="uriSubmit">URI</button>
						</div>
						<input type="hidden" class="input" name="type" value="file">
					</div>
				</div>

				<div class="control-group uriSubmit hide addFormatTypeContent">
					<label class="control-label" for="uri">URI: </label>
					<div class="controls">
						<input type="text" class="input-medium" name="value"/>
					</div>
				</div>

				<div class="control-group fileSubmit addFormatTypeContent">
					<label class="control-label" for="uri">File Upload: </label>
					<div class="controls">
						<input type="file" class="input-medium addFormatUploadValue" name="file" version_id="{{id}}" required/>
					</div>
				</div>

			</fieldset>
			<button type="submit" class="btn addVersionButton">Add Version</button> <a href="javascript:;" class="closeTip">Cancel</a>
		</form>
	</div>

<section id="browse-vocabs" class="hide">
	<div class="row">
		<div class="span8">
			<div class="box">
				<div class="box-header clearfix">
					<h1><?php echo $title;?><small><?php echo $small_title;?></small></h1>
					<span class="right-widget">
						
					</span>
				</div>
				<div class="box-content">

					<!-- Toolbar -->
				    <div class="row-fluid" id="mmr_toolbar">
				    	<div class="span6">
				    		<span class="dropdown" id="switch_menu">
				    		<a class="btn dropdown-toggle" data-toggle="dropdown" data-target="#switch_menu" href="#switch_menu">Switch View <span class="caret"></span></a>
							  <ul class="dropdown-menu" id="switch_view">
							    <li><a href="javascript:;" name="thumbnails"><i class="icon-th"></i> Thumbnails View</a></li>
							    <li><a href="javascript:;" name="lists"><i class="icon-th-list"></i> List View</a></li>
							  </ul>
							</span>
						</div>
						
				    	<div class="span6 right-aligned">
				    		<select data-placeholder="Choose a Vocabulary to View" tabindex="1" class="chzn-select" id="vocab-chooser">
								<option value=""></option>
								<?php
									foreach($vocabs as $vocab){
										echo '<option value="'.$vocab['id'].'">'.$vocab['title'].'</option>';
									}
								?>
							</select>
				    	</div>
				    </div>

				    <!-- List of items will be displayed here-->
				    <ul class="lists" id="items"></ul>
				    
				    <!-- View More Link -->
				    <div class="row-fluid" id="load_more_container">
						<div class="span12">
							<div class="well"><a href="javascript:;" id="load_more" page="1">Show More...</a></div>
						</div>
					</div>

				</div>
			</div>
		</div>

		<div class="span4">
			

			
			<div class="box">
				<div class="box-header clearfix">
					<h1>My Vocabularies</h1>
				</div>
				<div class="box-content">

					<?php
						if(sizeof($group_vocabs)>0 && $this->user->loggedIn()){
							echo '<ul>';
							foreach($group_vocabs AS $v){
								echo '<li><a href="#!/view/'.$v->id.'">'.$v->title . "</a></li>";
							}
							echo '</ul>';
						}else if(sizeof($group_vocabs)){
							echo "<p>You have no vocabularies.</p>";
						}
					?>
					<?php if($this->user->loggedIn()):?>
						<button class="btn add" id="add"><i class="icon-plus"></i>Add a vocabulary</button>
					<?php else:?>
						<p>Only logged in users can add a vocabulary <?php echo anchor('auth/login', 'Log In');?>
					<?php endif;?>
				</div>
			</div>


		</div>
	</div>
</section>

<section id="view-vocab" class="hide">Loading...</section>
<section id="edit-vocab" class="hide">Loading...</section>
<section id="add-vocab" class="hide">Loading...</section>
</div>
<!-- end of main content container -->


<!-- mustache template for list of items-->
<script type="text/x-mustache" id="items-template">
	{{#items}}
		<li>
		  	<div class="item" vocab_id="{{id}}">
		  		<div class="item-info"></div>
		  		<div class="item-snippet">
			  		<h3>{{title}}</h3>
				  	<p>{{description}}</p>
			  	</div>
		  		<div class="btn-group item-control">
		  			<button class="btn view" tip="View Vocabulary" vocab_id="{{id}}"><i class="icon-eye-open"></i></button>
		  			{{#owned}}
				  		<button class="btn edit" tip="Edit Vocabulary" vocab_id="{{id}}"><i class="icon-edit"></i></button>
			  		{{/owned}}
				</div>
		  	</div>
		</li>
	{{/items}}
</script>


<!-- mustache template for vocab view single-->
<script type="text/x-mustache" id="vocab-view-template">

	{{#item}}
	<div class="row">
		<div class="span8" id="vocab_view_container" vocab_id="{{id}}">
			<div class="box">
				<div class="box-header">
					<ul class="breadcrumbs">
						<li><a href="<?php echo base_url();?>" tip="Back to Dashboard"><i class="icon-home"></i></a></li>
						<li><?php echo anchor('vocab_service', 'Browse Vocabularies');?></li>
						<li><a href="javascript:;" class="active">{{title}}</a></li>
					</ul>
			        <div class="clearfix"></div>
			    </div>

			    <div class="box-content">

			    	<h3>{{title}}</h3>
			    	
			    	{{#publisher}}
						<h5>Publisher</h5> {{publisher}}
					{{/publisher}}

					{{#description}}
						<h5>Description</h5> {{description}}
					{{/description}}

					<h5>Available Formats</h5>
					{{#hasFormats}}
						{{#available_formats}}	
							<span class="largeTag format" format="{{.}}" vocab_id="{{id}}">{{.}}</span>
						{{/available_formats}}
					{{/hasFormats}}

					{{#noFormats}}
						There is no available format for this vocabulary
					{{/noFormats}}

					

					{{#language}}
						<h5>Language</h5> {{language}}
					{{/language}}

					{{#subjects}}
						<h5>Subjects</h5> {{subjects}}
					{{/subjects}}

					{{#revision_cycle}}
						<h5>Revision Cycle</h5> {{revision_cycle}}
					{{/revision_cycle}}

					{{#website}}
						<h5>Website</h5> {{website}}
					{{/website}}

					{{#notes}}
						<h5>Notes</h5> {{notes}}
					{{/notes}}

					{{#owned}}
						{{#contact_name}}
							<h5>Contact name</h5> {{contact_name}}
						{{/contact_name}}
						{{#contact_email}}
							<h5>Contact Email</h5> {{contact_email}}
						{{/contact_email}}
						{{#contact_number}}
							<h5>Contact Number</h5> {{contact_number}}
						{{/contact_number}}
					{{/owned}}

					<?php //if($this->user->loggedIn()):?>
			    	<div class="btn-toolbar">
						<div class="btn-group item-control" vocab_id="{{id}}">
							<button class="btn contact" id="contactPublisher"><i class="icon-user"></i> Contact Publisher</button>
						</div>
					</div>
					<?php //endif;?>

					<div id="contactPublisherForm" class="hide">
						<form class="contactPublisherForm">
							<div class="control-group">
								<label class="control-label" for="title">Your Name</label>
								<div class="controls">
									<input type="text" class="input-xlarge" name="name" value="" required placeholder="Enter your full name">
									<p class="help-inline"><small></small></p>
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" for="title">Your Email Address</label>
								<div class="controls">
									<input type="email" class="input-xlarge" name="email" value="" required placeholder="Enter your email address">
									<p class="help-inline"><small></small></p>
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" for="description">Your Message</label>
								<div class="controls">
									<textarea class="input-xlarge" name="description" required placeholder="Enter your message"></textarea>
								</div>
							</div>
							<button class="btn btn-primary confirmContactPublisher" vocab_id="{{id}}">Submit</button>
						</form>
					</div>
			    </div>
			</div>
		</div>

		<div class="span4">
			
			{{#owned}}
			<div class="box">
				<div class="box-header"><h3>Admin</h3><div class="clearfix"></div></div>
				<div class="box-content">
					<div class="btn-toolbar">
						<div class="btn-group btn-group-vertical btn-group-left item-control" vocab_id="{{id}}">
							<button class="btn edit" vocab_id="{{id}}"><i class="icon-edit"></i> Edit Vocabulary</button>
							<button class="btn" vocab_id="{{id}}" id="deleteVocab"><i class="icon-trash"></i> Delete Vocabulary</button>
						</div>
					</div>
					
					<div class="hide" id="deleteVocabForm">
						<p>Are you sure you want to delete this vocabulary? <br/>
						All versions, formats and change history associated with this version will also be deleted<br/>
						This action is <b>irreversible</b></p>
						<p><button class="btn btn-danger" id="deleteVocabConfirm" vocab_id="{{id}}">Delete vocabulary</button><button class="btn btn-link closeTip">Cancel</button></p>
					</div>

				</div>
			</div>
			{{/owned}}

			<div id="versions-view">
			
			</div>

			<div class="box">
				<div class="box-header"><h3>Changes</h3><div class="clearfix"/></div>
				<div class="box-content">
					{{#hasChanges}}
						<ul>
						{{#changes}}
							<li><a href="javascript:;" class="viewChange" change_id="{{change_id}}" change_description="{{change_description}}">{{change_date}}</a></li>
						{{/changes}}
						</ul>
					{{/hasChanges}}

					{{#noChanges}}
						<div class="well">This vocabulary has no changes</div>
					{{/noChanges}}
				</div>
			</div>

		</div>
	</div>

	
			
	{{/item}}
</script>	

<script type="text/x-mustache" id="vocab-versions">
{{#item}}
	<div class="box {{#noVersions}}box-error{{/noVersions}}" vocab_id="{{id}}">
		<div class="box-header"><h3>Versions</h3><div class="clearfix"/></div>
		<div class="box-content">
			<ul class="ro-list" style="margin:-10px;">
			{{#hasVersions}}
				{{#versions}}
					<li><a href="javascript:;" class="version" version_id="{{id}}"><span class="name">{{title}}</span></a><span class="num">{{status}}</span></li>
				{{/versions}}
			{{/hasVersions}}
			{{#noVersions}}
				<li>This vocab does not have any available versions</li>
			{{/noVersions}}

			</ul>
		</div>

		{{#owned}}
			{{#editable}}
			<div class="box-footer clearfix">
				<button class="btn btn-primary addVersion" vocab_id="{{id}}" view="{{view}}"><i class="icon-plus icon-white"></i> Add a Version</button>
				<span id="versions-message" vocab_id="{{id}}"></span>
			</div>
			{{/editable}}
		{{/owned}}
		
	</div>
{{/item}}
</script>

<script type="text/x-mustache" id="vocab-format-downloadable-template">
	{{#hasItems}}
	<table class="table table-bordered">
		<thead>
			<tr>
				<th>Version</th><th>Type</th><th>Format</th><th>Action</th>
			</tr>
		</thead>
		<tbody>
			{{#items}}
			<tr class="formatRow" format_id="{{id}}">
				<td>{{version_name}}</td><td><span class="label label-info">{{type}}</span></td><td><span class="label label-info">{{format}}</span></td>
				<td>
					<div class="btn-group">
			  			<button class="btn downloadFormat" tip="{{#tip}}{{tip}}{{/tip}}" format_id="{{id}}"><i class="icon-download"></i></button>
			  			{{#owned}}
			  			{{#editable}}
				  		<button class="btn deleteFormat" tip="{{#tip}}{{tip}}{{/tip}}" format_id="{{id}}"><i class="icon-trash"></i></button>
				  		{{/editable}}
				  		{{/owned}}
					</div>
				</td>
			</tr>
			{{/items}}
		</tbody>
	</table>
	{{/hasItems}}
	<a href="javascript:;" class="closeTip">Close</a>
</script>

<script type="text/x-mustache" id="vocab-format-downloadable-template-by-version">
	{{#hasItems}}
	<table class="table table-bordered">
		<thead>
			<tr>
				<th>Version</th><th>Type</th><th>Format</th><th>Action</th>
			</tr>
		</thead>
		<tbody>
			{{#items}}
			<tr class="formatRow" format_id="{{id}}">
				<td>{{version_name}}</td><td><span class="label label-info">{{type}}</span></td><td><span class="label label-info">{{format}}</span></td>
				<td>
					<div class="btn-group">
			  			<button class="btn downloadFormat" tip="{{#tip}}{{tip}}{{/tip}}" format_id="{{id}}"><i class="icon-download"></i></button>
			  			{{#owned}}
			  			{{#editable}}
				  		<button class="btn openDeleteFormatForm" tip="Delete This Format" format_id="{{id}}"><i class="icon-trash"></i></button>
				  		{{/editable}}
				  		{{/owned}}
					</div>
				</td>
			</tr>
			{{/items}}
		</tbody>
	</table>

		{{#items}}
			<div class="hide deleteFormatConfirmForm" format_id="{{id}}">
				<div class='well'>
					<p>
						Are you sure you want to delete this {{format}} format?
					</p>
					<p>
						<button class="btn deleteFormat" format_id="{{id}}">Yes</button>
						<button class="btn btn-link cancelDeleteFormat" format_id="{{id}}">No</button>
					</p>
				</div>
			</div>
		{{/items}}

	{{/hasItems}}

	{{#noItems}}
		<p>This version has no downloadable formats available</p>
	{{/noItems}}

	{{#owned}}
	{{#editable}}
		<div class="btn-group">
  			<button class="btn addFormat" version_id="{{id}}"><i class="icon-plus"></i> Add a Format</button>
  			<button class="btn editVersion" version_id="{{id}}" tip="Edit this Version"><i class="icon-edit"></i></button>
	  		<button class="btn deleteVersion" version_id="{{id}}" tip="Delete this Version"><i class="icon-trash"></i></button>
	  		
		</div>
		


		<div class="addFormatForm hide" version_id="{{id}}"><hr/>
			<div class="form well">
				<fielset>
					<legend>Add Format</legend>

					<div class="control-group">
						<label class="control-label" for="format">File Format: </label>
						<div class="controls">
							<!--input type="text" class="input-xlarge typeahead" name="format" value="" placeholder="Enter File Format" required-->
							<select name="format" id="">
								<option value="SKOS">SKOS</option>
								<option value="OWL">OWL</option>
								<option value="TEXT">TEXT</option>
								<option value="CSV">CSV</option>
								<option value="ZTHES">ZTHES</option>
								<option value="RDF">RDF</option>
								<option value="OTHER">OTHER</option>
							</select>
							<p class="help-inline"><small></small></p>
						</div>
					</div>

					<div class="control-group">
						<label class="control-label" for="type">Type: </label>
						<div class="controls">
							<div class="btn-group toggleAddFormatType" data-toggle="buttons-radio">
								<button type="button" class="btn active" value="file" content="fileSubmit">File</button>
								<button type="button" class="btn" value="uri" content="uriSubmit">URI</button>
							</div>
							<input type="hidden" class="input" name="type" value="file">
						</div>
					</div>

					<div class="control-group uriSubmit hide addFormatTypeContent">
						<label class="control-label" for="uri">URI: </label>
						<div class="controls">
							<input type="text" class="input-medium" name="value"/>
						</div>
					</div>

					<div class="control-group fileSubmit addFormatTypeContent">
						<label class="control-label" for="uri">File Upload: </label>
						<div class="controls">
							<input type="file" class="input-medium addFormatUploadValue" name="file" version_id="{{id}}" required/>
						</div>
					</div>

				</fieldset>
				<hr/>
				<button type="submit" class="btn addFormatSubmit" version_id="{{id}}" view="{{view}}">Add Format</button> <a href="javascript:;" version_id="{{id}}" class="cancelAddFormat">Cancel</a>
				</div>
			</div>
		</div>
		
		<div class="editVersionForm hide" version_id="{{id}}"><hr/>
			<form class="form well" vocab_id="{{id}}">
				<label>Version Title: </label>
				<input type="text" class="input-medium" name="title" value="{{title}}" required><br/>
				<button type="submit" class="btn editVersionConfirm" version_id="{{id}}">Submit Changes</button> <a href="javascript:;" version_id="{{id}}" class="cancelEdit">Cancel</a>
			</form>
		</div>

		<div class="deleteVersionForm hide" version_id="{{id}}"><hr/>
			<div class='well'>
				<p>Are you sure you want to delete this version <br/>and all file formats associated with this version?</p>
				<p>
					<button type="submit" version_id="{{id}}" vocab_id="{{vocab_id}}" class="btn btn-error deleteVersionConfirm">Yes</button>
					<a href="javascript:;" version_id="{{id}}" class="cancelDelete">No</a>
				</p>
			</div>
		</div>
	{{/editable}}
	{{/owned}}
	<a href="javascript:;" class="btn btn-link closeTip">Close</a>
</script>


<script type="text/x-mustache" id="vocab-edit-template">
	{{#item}}
	<div class="row">
		<div class="span8">
			<div class="box">
				<div class="box-header clearfix">
					<ul class="breadcrumbs">
						<li><a href="<?php echo base_url();?>"><i class="icon-home"></i></a></li>
						<li><?php echo anchor('vocab_service', 'Browse Vocabularies');?></li>
						<li><a href="<?php echo base_url();?>vocab_service/#!/view/{{id}}">{{title}}</a></li>
					</ul>
				</div>

				<div class="box-content">
					<form class="form-horizontal"  enctype="multipart/form-data"  id="edit-form" vocab_id="{{id}}">
							<fieldset>
								<legend>Vocabulary Administration Information</legend>

								<div class="control-group">
									<label class="control-label" for="title">Title</label>
									<div class="controls">
										<input type="text" class="input-xlarge" name="title" value="{{title}}" required placeholder="Enter a title for the vocabulary">
										<p class="help-inline"><small></small></p>
									</div>
								</div>

								<input type="hidden" class="input" name="record_owner" value="{{record_owner}}">

								<div class="control-group">
									<label class="control-label" for="description">Description</label>
									<div class="controls">
										<textarea class="input-xlarge" name="description" required placeholder="Enter a description for the vocabulary">{{description}}</textarea>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" for="publisher">Publisher</label>
									<div class="controls">
										<input type="text" class="input-xlarge" name="publisher" value="{{publisher}}" required placeholder="Enter a publisher for the vocabulary">
									</div>
								</div>					

								<div class="control-group">
									<label class="control-label" for="subjects">Subjects</label>
									<div class="controls">
										<input type="text" class="input-xlarge" name="subjects" value="{{subjects}}">
									</div>
								</div>		

								<div class="control-group">
									<label class="control-label" for="revision_cycle">Revision Cycle</label>
									<div class="controls">
										<input type="text" class="input-xlarge" name="revision_cycle" value="{{revision_cycle}}">
									</div>
								</div>	

								<div class="control-group">
									<label class="control-label" for="notes">Notes</label>
									<div class="controls">
										<textarea class="input-xlarge" name="notes">{{notes}}</textarea>
									</div>
								</div>

								<div class="control-group">
									<label class="control-label" for="language">Language</label>
									<div class="controls">
										<input type="text" class="input-xlarge" name="language" value="{{language}}"></input>
									</div>
								</div>
							</fieldset>

							<fieldset>
								<legend>Contact Details</legend>
								<div class="control-group">
									<label class="control-label" for="contact_name">Contact Name</label>
									<div class="controls">
										<input type="text" class="input-xlarge" name="contact_name" value="{{contact_name}}">
									</div>
								</div>

								<div class="control-group">
									<label class="control-label" for="contact_email">Contact Email</label>
									<div class="controls">
										<input type="email" class="input-xlarge" name="contact_email" value="{{contact_email}}" required placeholder="Enter a valid contact email">
									</div>
								</div>
								
								<div class="control-group">
									<label class="control-label" for="contact_number">Contact Number</label>
									<div class="controls">
										<input type="text" class="input-xlarge" name="contact_number" value="{{contact_number}}">
									</div>
								</div>

								<div class="control-group">
									<label class="control-label" for="website">Website</label>
									<div class="controls">
										<input type="text" class="input-xlarge" name="website" value="{{website}}">
									</div>
								</div>

							</fieldset>

							<div class="aro_toolbar">
								<div class="message" id="save-edit-form-message"></div>
								<div class="aro_controls">
									
									<div class="btn-toolbar">				  
									  	<button class="btn btn-primary" id="save-edit-form">
										  <i class="icon-download-alt icon-white"></i> Save
										</button>
										<a class="btn btn-link cancel" vocab_id="{{id}}" href="javascript:;">Cancel</a>
									</div>


								</div>		
							<div class="clearfix"></div>	
						</div>
						
					</form>

				</div>
			</div>
		</div>

		<div class="span4">

			<div class="box">
				<div class="box-header clearfix"><h1>Vocabulary Owner</h1></div>
				<div class="box-content">
					<?php
						$orgs = $this->user->affiliations();
						echo '<select class="chosen" id="chooseRecordOwner">';
						foreach($orgs as $o){
							echo '<option value="'.$o.'">'.$o.'</option>';
						}
						echo '</select>';
					?>
				</div>
			</div>


			<div id="versions-edit">
	
			</div>

			<div class="alert alert-success hide" id="subalert" vocab_id="{{id}}"></div>
		</div>

		

	</div>
	{{/item}}
</script>


<?php $this->load->view('footer');?>