<?php $this->load->view('rda_header');?>
<div class="container">
<h1>Contact Research Data Australia</h1>

	<!-- Start Form -->

	<div id="contact-us-form">
	<p>If you have any questions or queries regarding Research Data Australia or you are interested in contributing, please complete the following form or alternatively email <a href="mailto:services@ands.org.au">services@ands.org.au</a> and we will respond to your request as soon as possible.</p>
	<p></p>
	<?php if(!$sent):?>
	<form action="?sent=true" method="post">
		<p><input type="text" placeholder="Name" name="name" size="40" title="please input your name" id="contact-name"/></p>
		<p><input type="text" placeholder="Email Address" name="email" id="contact-email" size="40" title="please input a valid email address"/></p>
		<p><textarea name="content" rows="10" cols="40" id="contact-content" title="please enter some text" default=""></textarea><p>
		<button id="contact-send-button">Send</button>
	</form>
	<?php else:?>
	<p><b>Thank you for your response. Your message has been delivered successfully</b></p>
	<?php endif;?>
	</div>
	<!-- End Form -->


</div><!-- container -->
<?php $this->load->view('rda_footer');?>