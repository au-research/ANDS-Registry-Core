<div class="tab-content" ng-show="tab=='social'">
    <div class=" element-no-top element-no-bottom" data-os-animation="none" data-os-animation-delay="0s">
        <?php
            $oauth_conf = $this->config->item('oauth_config');
        ?>
        @if($oauth_conf['providers']['Facebook']['enabled'])
            <a href="{{registry_url('auth/authenticate/facebook')}}" class="btn btn-primary btn-block btn-icon-left">Login with Facebook <span><i class="fa fa-facebook"></i></span></a>
        @endif
        @if($oauth_conf['providers']['Twitter']['enabled'])
            <a href="{{registry_url('auth/authenticate/twitter')}}" class="btn btn-primary btn-block btn-icon-left">Login with Twitter <span><i class="fa fa-twitter"></i></span></a>
        @endif
        @if($oauth_conf['providers']['Google']['enabled'])
            <a href="{{registry_url('auth/authenticate/google')}}" class="btn btn-primary btn-block btn-icon-left">Login with Google <span><i class="fa fa-google"></i></span></a>
        @endif
        @if($oauth_conf['providers']['LinkedIn']['enabled'])
            <a href="{{registry_url('auth/authenticate/linkedin')}}" class="btn btn-primary btn-block btn-icon-left">Login with LinkedIn <span><i class="fa fa-linkedin"></i></span></a>
        @endif
    </div>
</div>