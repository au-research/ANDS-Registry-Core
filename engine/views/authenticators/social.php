<?php
    $oauth_conf = \ANDS\Util\Config::get('oauth');
?>
<?php if($oauth_conf['providers']['Facebook']['enabled']): ?>
    <a href="<?php echo registry_url('auth/authenticate/facebook'); ?>?redirect=<?php echo $this->input->get('redirect') ?>" class="btn btn-primary btn-block btn-icon-left">Login with Facebook <span><i class="fa fa-facebook"></i></span></a>
<?php endif; ?>
<?php if($oauth_conf['providers']['Twitter']['enabled']): ?>
    <a href="<?php echo registry_url('auth/authenticate/twitter'); ?>?redirect=<?php echo $this->input->get('redirect') ?>" class="btn btn-primary btn-block btn-icon-left">Login with Twitter <span><i class="fa fa-twitter"></i></span></a>
<?php endif; ?>
<?php if($oauth_conf['providers']['Google']['enabled']): ?>
    <a href="<?php echo registry_url('auth/authenticate/google'); ?>?redirect=<?php echo $this->input->get('redirect') ?>" class="btn btn-primary btn-block btn-icon-left">Login with Google <span><i class="fa fa-google"></i></span></a>
<?php endif; ?>
<?php if($oauth_conf['providers']['LinkedIn']['enabled']): ?>
    <a href="<?php echo registry_url('auth/authenticate/linkedin'); ?>?redirect=<?php echo $this->input->get('redirect') ?>" class="btn btn-primary btn-block btn-icon-left">Login with LinkedIn <span><i class="fa fa-linkedin"></i></span></a>
<?php endif; ?>