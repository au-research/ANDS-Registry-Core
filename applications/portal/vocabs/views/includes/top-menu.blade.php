<div class="navbar swatch-black" role="banner">
    <div class="container" style="z-index:10">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".main-navbar">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <div>
                <a href="{{portal_url()}}" class="navbar-brand">
                    Vocabulary Portal
                </a>
            </div>
            
            @if(current_url()!=base_url())
           <!--  <div class="clear"><small>One liner</small></div> -->
            @endif
        </div>
        <nav class="collapse navbar-collapse main-navbar" role="navigation">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="{{portal_url('page/help')}}">Help</a></li>
                <li><a href="{{portal_url('page/about')}}">About</a></li>
                <li><a href="{{portal_url('page/contribute')}}">Contribute</a></li>
                @if(!$this->user->loggedIn())
                    <li><a href="{{portal_url('profile/login')}}" class="login_btn">My Vocabs Login</a></li>
                @else
                    <li><a href="{{portal_url('profile')}}">My Vocabs</a></li>
                @endif

                <?php 
                    $profile_image = profile_image();
                ?>
                @if($profile_image)
                   <li><a href="{{portal_url('profile')}}"><img src="{{ $profile_image }}" alt="" class="profile_image_small"></a></li>
                @endif
            </ul>
        </nav>
    </div>
</div>
