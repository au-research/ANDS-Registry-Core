<?php $this->load->view('rda_header');?>
<div class="container">
<h1>Contact Research Data Australia</h1>

	<!-- Start Form -->

	<div id="contact-us-form">
	<p>If you have any questions or queries regarding Research Data Australia or you are interested in contributing, please complete the following form or alternatively email <a href="mailto:<?php echo $contact_email; ?>"><?php echo $contact_email; ?></a> and we will respond to your request as soon as possible.</p>
	<p></p>
	<?php if(!$sent):?>
	<p><?php echo $message; ?></p>
	<form action="?sent=true" method="post">
		<p class="form-field"><input type="text" placeholder="Title" name="title" size="40" title="Please input your title" id="title"/></p>
		<p class="field"><input type="text" placeholder="Name" name="first_name" size="40" title="Please input your name" id="first_name" class="verify"/></p>
		<p class="form-field"><input type="text" placeholder="Last_Name" name="last_name" size="40" title="Please input your last name" id="last-name"/></p>
		<p class="form-field"><input type="text" placeholder="Email Address" name="email" id="email" size="40" title="Please input a valid email address"/></p>
		<p class="field"><input type="text" placeholder="Email Address" name="contact_email" id="contact-email" size="40" title="Please input a valid email address" class="verify"/></p>
		<p class="field"><textarea name="content" rows="10" cols="40" id="contact-content" title="Please enter some text" class="verify"></textarea><p>
		<button id="contact-send-button">Send</button>
	</form>
	<?php else:?>
	<p><b>Thank you for your response. Your message has been delivered successfully</b></p>
	<?php endif;?>
	</div>
	<!-- End Form -->


</div><!-- container -->
<?php $this->load->view('rda_footer');?>