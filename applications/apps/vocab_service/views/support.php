<?php $this->load->view('header');?>
<div class="container" id="main-content">
	<div class="box">
		<div class="box-header clearfix">
			<h1>Support</h1>
		</div>
		<div class="box-content">
			<?php
				if(isset($success)){
					echo '<p>'.$success.'</p>';
				}
			?>
			<form action="<?php echo base_url();?>vocab_service/support/submit">
				<div class="control-group">
					<label class="control-label" for="from_email">Your Email Address: </label>
					<div class="controls">
						<input type="email" class="input-xlarge" name="from_email" value="" placeholder="Enter your email address" required>
						<p class="help-inline"><small></small></p>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="from_title">Your Name: </label>
					<div class="controls">
						<input type="text" class="input-xlarge" name="from_title" value="" placeholder="Enter your full name" required>
						<p class="help-inline"><small></small></p>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="message">Message: </label>
					<div class="controls">
						<textarea name="message" class="textarea-large" required placeholder="Enter your message"></textarea>
					</div>
				</div>

				<button type="submit" class="btn btn-primary" id="submitSupportForm">Submit</button>
			</form>
		</div>
	</div>

</div>
<?php $this->load->view('footer');?>