<?php

/**
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>
<?php $this->load->view('header');?>
<style>
[ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
	display: none !important;
}
</style>

<div class="container" ng-app="task_mgr_app">
	<div ng-view></div>	
</div>


<div class="container hide" id="main">
	Main
</div>

<?php $this->load->view('footer');?>