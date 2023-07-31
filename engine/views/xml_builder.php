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
    <div ng-app="doi_cms_app1" ng-controller="mainCtrl" >
        <div ng-view></div>
    </div>


<?php $this->load->view('footer'); ?>