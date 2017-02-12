<?php

/**
 * Login form
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>
<?php $this->load->view('header');?>
<style>
[ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
	display: none !important;
}
</style>

<div class="container" ng-app="login_app">
	<div ng-view></div>	
</div>


<div class="container hide" id="main">
	<input type="hidden" value="<?php echo $default_authenticator;?>" id="default_authenticator">
	<div class="row">
		<div class="span6 offset3">
			<h3>Login</h3>
			<div class="alert" ng-show="error=='login_required'">You have to be logged in to use this functionality</div>
			<div class="widget-box" ng-cloak>
				<div class="widget-title">
					<ul class="nav nav-tabs">
						<?php foreach($authenticators as $auth): ?>
						<li ng-class="{'<?php echo $auth['slug']?>':'active'}[tab]"><a href="#/<?php echo $auth['slug']?>?redirect={{redirect}}"><?php echo $auth['display']; ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<?php foreach($authenticators as $auth): ?>
				<div class="widget-content" ng-show="tab=='<?php echo $auth['slug'];?>'" ng-cloak>
					<?php echo $auth['view']; ?>
				</div>
				<?php endforeach; ?>
			</div>
			<div class="alert alert-error" ng-show="message">{{message}}</div>
		</div>

	</div>
</div>

<?php $this->load->view('footer');?>