<div class="">
	<!-- tabs -->
	<ul class="nav nav-tabs">
	  <li class="active"><a href="#admin" data-toggle="tab">Record Administration</a></li>
	  <li><a href="#names" data-toggle="tab">Names</a></li>
	  <li><a href="#descriptions" data-toggle="tab">Descriptions/Rights</a></li>
	  <li><a href="#descriptions" data-toggle="tab">Identifiers</a></li>
	  <li><a href="#descriptions" data-toggle="tab">Locations</a></li>
	  <li><a href="#descriptions" data-toggle="tab">Related Objects</a></li>
	  <li><a href="#descriptions" data-toggle="tab">Subjects</a></li>
	  <li><a href="#descriptions" data-toggle="tab">Related Info</a></li>
	</ul>

	<!-- form-->
	<form class="form-horizontal" id="edit-form">
		<!-- All the tab contents -->
		<div class="tab-content">

			<!-- Record Admin-->
			<div id="admin" class="tab-pane active">
				<fieldset>
					<legend>Record Administration</legend>
				
					<div class="control-group">
						<label class="control-label" for="title">Type</label>
						<div class="controls">
							<input type="text" class="input-xlarge" name="title" value="">
							<p class="help-inline"><small></small></p>
						</div>
					</div>

					<div class="control-group">
						<label class="control-label" for="title">Data Source</label>
						<div class="controls">
							<input type="text" class="input-xlarge" name="title" value="">
							<p class="help-inline"><small></small></p>
						</div>
					</div>

					<div class="control-group">
						<label class="control-label" for="title">Group</label>
						<div class="controls">
							<input type="text" class="input-xlarge" name="title" value="">
							<p class="help-inline"><small></small></p>
						</div>
					</div>

					<div class="control-group warning">
						<label class="control-label" for="title">Key</label>
						<div class="controls">
							<input type="text" class="input-xlarge" name="title" value="">
							<button class="btn btn">
							  <i class="icon-refresh"></i> Generate Random Key
							</button>
							<p class="help-inline"><small>Key must be unique and is case sensitive</small></p>
						</div>
					</div>

					<div class="control-group">
						<label class="control-label" for="title">Date Modified</label>
						<div class="controls">
							<input type="text" class="input-xlarge" name="title" value="">
							<p class="help-inline"><small></small></p>
						</div>
					</div>

				</fieldset>
			</div>


			<!-- Names -->
			<div id="names" class="tab-pane">
				<fieldset>
					<legend>Names</legend>
					
					<div class="aro_box">
						<div class="aro_box_display">
							<h1>Dr. Smith James<small><b> Type: </b> Primary</small></h1>
						</div>
						<div class="aro_box_part">
							<div class="control-group">
								<label class="control-label" for="title">Title</label>
								<div class="controls">
									<input type="text" class="input-xlarge" name="title" value="Dr">
									<button class="btn btn-mini btn-danger">
									  <i class="icon-remove icon-white"></i>
									</button>
									<p class="help-inline"><small></small></p>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="title">First Name</label>
								<div class="controls">
									<input type="text" class="input-xlarge" name="title" value="James">
									<button class="btn btn-mini btn-danger">
									  <i class="icon-remove icon-white"></i>
									</button>
									<p class="help-inline"><small></small></p>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="title">Sur Name</label>
								<div class="controls">
									<input type="text" class="input-xlarge" name="title" value="Smith">
									<button class="btn btn-mini btn-danger">
									  <i class="icon-remove icon-white"></i>
									</button>
									<p class="help-inline"><small></small></p>
								</div>
							</div>
							<div class="control-group">
								<div class="controls">
									<button class="btn btn-primary">
									  <i class="icon-plus icon-white"></i> Add Name Part
									</button>
								</div>
							</div>
						</div>
					</div>

					<div class="aro_box">
						<div class="aro_box_display">
							<h1>James, Smith<small><b> Type: </b> Alternative</small></h1>
						</div>
						<div class="aro_box_part">
							<div class="control-group">
								<label class="control-label" for="title">Alternative</label>
								<div class="controls">
									<input type="text" class="input-xlarge" name="title" value="James, Smith">
									<button class="btn btn-mini btn-danger">
									  <i class="icon-remove icon-white"></i>
									</button>
									<p class="help-inline"><small></small></p>
								</div>
							</div>
							<div class="control-group">
								<div class="controls">
									<button class="btn btn-primary">
									  <i class="icon-plus icon-white"></i> Add Name Part
									</button>
								</div>
							</div>
						</div>
					</div>
					<button class="btn btn-primary">
					  <i class="icon-plus icon-white"></i> Add Name
					</button>

				</fieldset>
			</div>
		</div>

		<div class="aro_toolbar">
			<div class="message">
				Auto-saved: 5 seconds ago...
			</div>
			<div class="aro_controls">
				

				<div class="btn-toolbar">				  
				  <div class="btn-group">
				  	<button class="btn btn-primary">
					  <i class="icon-download-alt icon-white"></i> Save
					</button>
				  </div>

				  <div class="btn-group">
				  	<a class="btn"><i class="icon-chevron-left"></i></a>
				  	<a class="btn"><i class="icon-chevron-right"></i></a>
				  </div>

				</div>
			</div>		
			<div class="clearfix"></div>	
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
	</form>

		
</div>