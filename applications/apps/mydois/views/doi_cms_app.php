<?php $this->load->view('header'); ?>
<?php if($this->user->isLoggedIn()): ?>
    <input type="hidden" id="logged_in_user_id" value="<?php echo $this->user->localIdentifier(); ?>">
<?php endif; ?>
<div ng-app="doi_cms_app">
    <div ng-view></div>
</div>
<?php $this->load->view('footer'); ?>