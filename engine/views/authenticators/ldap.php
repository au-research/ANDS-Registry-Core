<form action="" name="ldap" ng-submit="authenticate('ldap')">
	<input type="hidden" name="redirect" value="">
	<div class="control-group">
		<div class="controls">
			<label for="">Username</label>
			<input type="text" placeholder="Username" ng-model="username" auto-fill-sync>
		</div>
		<div class="controls">
			<label for="">Password</label>
			<input type="password" placeholder="Password" ng-model="password" auto-fill-sync>
		</div>
	</div>
	<div class="control-group">
		<button type="submit" class="btn btn-primary" data-loading-text="Logging in... Please wait">Login using LDAP Account</button>
	</div>
</form>