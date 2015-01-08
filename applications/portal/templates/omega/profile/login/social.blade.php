<div class="tab-content" ng-show="tab=='social'">
    <div class=" element-no-top element-no-bottom" data-os-animation="none" data-os-animation-delay="0s">
        <?php
            $oauth_conf = $this->config->item('oauth_config');
        ?>
        @if($oauth_conf['providers']['Facebook']['enabled'])
            <a href="{{registry_url('auth/authenticate/facebook/?redirect=profile')}}" class="btn btn-primary btn-block btn-icon-left">Login with Facebook <span><i class="fa fa-facebook"></i></span></a>
        @endif
        <p><?php if($oauth_conf['providers']['Twitter']['enabled']) echo anchor('auth/login/Twitter/?redirect='.current_url(),'Login With Twitter', array('class'=>'zocial twitter')); ?></p>
        <p><?php if($oauth_conf['providers']['Google']['enabled']) echo anchor('auth/login/Google/?redirect='.current_url(),'Login With Google', array('class'=>'zocial google')); ?></p>
        <p><?php if($oauth_conf['providers']['LinkedIn']['enabled']) echo anchor('auth/login/LinkedIn/?redirect='.current_url(),'Login With LinkedIn', array('class'=>'zocial linkedin')); ?></p>
    </div>
</div>