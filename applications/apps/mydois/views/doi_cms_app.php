<?php $this->load->view('header'); ?>
<?php
session_start();
if (!isset($_SESSION['token'])) {
    $token = md5(uniqid(rand(), TRUE));
    $_SESSION['token'] = $token;
}
else
{
    $token = $_SESSION['token'];
}
?>
<input type="hidden" name="token" id="token" value="<?php echo $token ?>">

<?php if($this->user->isLoggedIn()): ?>
    <input type="hidden" id="logged_in_user_id" value="<?php echo $this->user->localIdentifier(); ?>">
<?php endif; ?>
<div ng-app="doi_cms_app">
    <div ng-view></div>
</div>
<?php $this->load->view('footer'); ?>