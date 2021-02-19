<div class="navbar swatch-black" role="banner">
    <div class="container" style="z-index:10">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".main-navbar">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <div >
                <a href="{{portal_url()}}" class="navbar-brand">
                    <img  class="header_logo"  src="{{ asset_url(\ANDS\Util\config::get('app.environment_rda_logo'), 'base')}}" alt=""/>
                </a>
            </div>

            @if(current_url()!=base_url())
            <!-- <div class="clear"><small>Find data for research</small></div> -->
            @endif
        </div>
        <nav class="collapse navbar-collapse main-navbar" role="navigation">
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Explore <i class="fa fa-caret-down"></i></a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="{{portal_url('subjects')}}"><i class="fa fa-eye icon-portal"></i> Browse By Subjects</a></li>
                        <li><a href="{{portal_url('themes')}}"><i class="fa fa-folder-open icon-portal"></i> Themed collections</a></li>
                        <li><a href="{{portal_url('theme/services')}}"><i class="fa fa-wrench icon-portal"></i> Services and Tools</a></li>
                        <li><a href="{{portal_url('grants')}}"><i class="fa fa-flask icon-portal"></i> Grants and Projects</a></li>
                        <li><a href="{{portal_url('theme/open-data')}}"><i class="fa fa-unlock icon-portal"></i> Open data</a></li>
                        <li><a href="{{portal_url('theme/software')}}"><i class="fa fa-file-code-o icon-portal"></i> Software</a></li>
                        <li><a href="{{portal_url('theme/highlighting-ands-projects')}}"><i class="fa fa-sun-o icon-portal"></i> Program Highlights</a></li>

                    </ul>
                </li>
                <li><a href="{{portal_url('page/about')}}">About</a></li>
                @if(!$this->user->loggedIn())
                    <li><a href="{{portal_url('profile/login')}}" class="login_btn">MyRDA Login</a></li>
                @else
                    <li><a href="{{portal_url('profile')}}">MyRDA</a></li>
                @endif
                @if(!(get_cookie("rda_long_survey")))
                <li><a href="{{portal_url('page/survey')}}" class="click_to_long">
                        <button  id="click_to_long" >RDA Survey</button></a></li>
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




@if(!isBot())
    @if(!(get_cookie("rda_short_survey")))
        @include('includes/short_survey')
    @endif

    <button class="yellow_button feedback_button">Feedback</button>
    <button class="yellow_button help_button" data-toggle="modal" data-target="#help_modal"><i class="fa fa-question-circle"></i> Help</button>
    @include('includes/help-modal')
@endif