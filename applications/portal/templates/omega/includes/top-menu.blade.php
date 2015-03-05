<div class="navbar swatch-black" role="banner">
    <div class="container" style="z-index:10">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".main-navbar">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="{{portal_url()}}" class="navbar-brand">
                <span>Research Data</span> Australia
            </a>
            @if(current_url()!=base_url())
            <small>Find data for research</small>
            @endif
        </div>
        <nav class="collapse navbar-collapse main-navbar" role="navigation">
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Explore <i class="fa fa-caret-down"></i></a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="{{portal_url('themes')}}"><i class="fa fa-folder-open icon-portal"></i> Themed collections</a></li>
                        <li><a href="{{portal_url('search')}}#!/class=service"><i class="fa fa-wrench icon-portal"></i> Services and Tools</a></li>
                        <li><a href="{{portal_url('search')}}#!/access_rights=open"><i class="fa fa-unlock icon-portal"></i> Open data</a></li>
                        <li><a href="{{portal_url('grants')}}"><i class="fa fa-flask icon-portal"></i> Grants and Projects</a></li>
                    </ul>
                </li>
                <li><a href="{{portal_url('page/about')}}">About</a></li>
                <li><a href="{{portal_url('profile')}}">MyRDA</a></li>
            </ul>
        </nav>
    </div>
</div>
<button class="myCustomTrigger yellow_button feedback_button">Feedback</button>