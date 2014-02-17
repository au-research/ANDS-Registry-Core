<?php if(config_item('oauth_config') && $logged_in = oauth_loggedin()): ?>
<div class="add_tag_form">
	<input type="text" id="tag_value" placeholder="Start typing to add tags"/>
	<button class="btn" id="tag_btn">Add Tag</button>
</div>
<?php else: ?>
<p>You have to <a href="#" class="login">login</a> in order to add tags</p>
<?php endif; ?>