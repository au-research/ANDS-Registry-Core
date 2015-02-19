<div class="navbar swatch-black" role="banner">
    <div class="container">
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
                <li><a href="{{portal_url('explore')}}">Explore <i class="fa fa-caret-down"></i></a></li>
                <li><a href="{{portal_url('page/about')}}">About</a></li>
                <li><a href="{{portal_url('profile')}}">MyRDA</a></li>
            </ul>
        </nav>
    </div>
</div>
<button class="myCustomTrigger yellow_button feedback_button">Feedback</button>