<?php 

/**
 * Change Password Form
 *
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * 
 */
?>
<?php $this->load->view('header');?>

<div class="container" id="main-content">
	<div class="row">
		<div class="span2">&nbsp;</div>
		<div class="span8">
			<div class="box">
				<div class="box-header clearfix">
					<h1>Change Built-in User Password <small>for <strong><?=$this->user->name();?></strong> <em>(<?=$this->user->localIdentifier();?>)</em></small></h1>
				</div>
				<div class="box-content">
					<div class="alert alert-info">
						Please provide the new password for this account
					</div>

					<?php 
					if (isset($error)) 
					{
						echo '<div class="alert alert-error">'. $error . '</div>';
					}
					?>

					<div class="middle">
						<form action="#" method="post" class="form-vertical password-change-form" autocomplete="off">
							<div class="control-group">
								<label class="control-label">New Password:</label>
								<div class="controls">
									<input type="password" name="password" required autocomplete="off"  />
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" name="title">Confirm New Password:</label>
								<div class="controls"><input required type="password" name="password_confirm" autocomplete="off" /></div>
							</div>
							<input type="submit" class="btn btn-primary" data-loading-text="Updating..." value="Update Password" />
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('footer');?>