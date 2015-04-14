<?php $this->load->view('header');?>
<div class="container" id="main-content">
	<div class="box">
		<div class="box-header clearfix">
			<h1>Account Setup</h1>
		</div>
		<div class="box-content">
			<p>
				As this is your first time logging into the system you will need to add yourself to an organisation. Organisational roles are used within the ANDS Vocabulary Discovery Portal to control access to vocabularies. A logged in user can access and manage any vocabulary created under the organisation they are a member of. This allows multiple users from the same organisation to manage the organisation's vocabularies.
			</p>
			<p>
				If your organisation doesn't exist in the dropdown select the 'Organisation not in list?' link at the bottom of the form to add a new one
			</p>

			<?php
				echo '<div class="well">';
      			echo '<p><select id="organisational_roles" class="chosen" data-placeholder="Select an organisation">';
      			echo '<option value></option>';
      			foreach($available_organisations as $o){
      				echo '<option value="'.$o['role_id'].'">'.$o['name'].'</option>';
      			}
      			echo '</select></p>';
      			echo '<p><button class="btn disabled" id="affiliation_signup" localIdentifier="'.$this->user->localIdentifier().'">Save</button></p>';
      			echo '<p><a href="javascript:;" id="openAddOrganisation">Organisation not in list?</a></p>';
      			echo '</div>';
      		?>
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

		<div class="modal hide" id="myModal">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3>Alert</h3>
  </div>
  <div class="modal-body"></div>
  <div class="modal-footer">
    
  </div>
</div>
	</div>

</div>
<?php $this->load->view('footer');?>