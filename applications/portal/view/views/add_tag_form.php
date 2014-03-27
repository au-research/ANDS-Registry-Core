<?php if(config_item('oauth_config') && $logged_in = oauth_loggedin()): ?>
<div class="add_tag_form">
	<input type="text" id="tag_value" placeholder="Start typing to add tags"/>
	<button class="btn" id="tag_btn">Add Tag</button>
</div>
<?php elseif(config_item('oauth_config')): ?>
<p><a href="#" class="login">Login</a> to tag this record with meaningful keywords to make it easier to discover.</p>
<?php endif; ?>