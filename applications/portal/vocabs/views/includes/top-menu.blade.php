<div class="navbar" role="banner">
    <div class="container" style="z-index:10">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".main-navbar">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <div>
                <a href="{{portal_url()}}" class="navbar-brand">
                    <span>Vocabularies</span> Portal
                </a>
            </div>
            
            @if(current_url()!=base_url())
            <div class="clear"><small>One liner</small></div>
            @endif
        </div>
        <nav class="collapse navbar-collapse main-navbar" role="navigation">
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Explore <i class="fa fa-caret-down"></i></a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="{{portal_url('themes')}}"><i class="fa fa-folder-open icon-portal"></i> Themed collections</a></li>
                        <li><a href="{{portal_url('theme/services')}}"><i class="fa fa-wrench icon-portal"></i> Services and Tools</a></li>
                        <li><a href="{{portal_url('theme/open-data')}}"><i class="fa fa-unlock icon-portal"></i> Open data</a></li>
                        <li><a href="{{portal_url('grants')}}"><i class="fa fa-flask icon-portal"></i> Grants and Projects</a></li>
                    </ul>
                </li>
                <li><a href="{{portal_url('page/about')}}">About</a></li>
                @if(!$this->user->loggedIn())
                    <li><a href="{{portal_url('profile/login')}}" class="login_btn">MyRDA Login</a></li>
                @else
                    <li><a href="{{portal_url('profile')}}">MyRDA</a></li>
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
<button class="yellow_button feedback_button">Feedback</button>