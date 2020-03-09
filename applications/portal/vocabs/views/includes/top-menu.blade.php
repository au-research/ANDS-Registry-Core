
<div class="navbar swatch-blue" role="banner">
    <div class="container" style="z-index:10">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".main-navbar">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <div>
                <a href="{{portal_url()}}" class="navbar-brand">
                    Research Vocabularies Australia
                </a>
            </div>
        </div>
        <nav class="collapse navbar-collapse main-navbar" role="navigation">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="{{portal_url('vocabs/page/about')}}">About</a></li>
                <li><a href="{{portal_url('vocabs/page/widget_explorer')}}">Widget Explorer</a></li>
                <li> <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Get Involved <i class="fa fa-caret-down"></i></a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="{{portal_url('vocabs/page/contribute')}}">Publish a vocabulary</a></li>
                        <li><a href="{{portal_url('vocabs/page/use')}}">Use a vocabulary</a></li>
                        <li><a href="{{portal_url('vocabs/page/feedback')}}">Give feedback on vocabularies</a></li>
                    </ul></li>
                @if(!$this->user->loggedIn())
                    <li><a href="{{ get_vocab_config('auth_url') }}login?redirect={{ portal_url('vocabs/myvocabs') }}#?redirect={{ portal_url('vocabs/myvocabs') }}" class="login_btn">My Vocabs Login</a></li>
                @else
                    <li><a href="{{ portal_url('vocabs/myvocabs') }}">My Vocabs</a></li>
                @endif
                <?php
                    $profile_image = $this->user->profileImage();
                ?>
                @if($profile_image)
                   <li><a href="{{portal_url('profile')}}"><img src="{{ $profile_image }}" alt="" class="profile_image_small"></a></li>
                @endif
            </ul>
        </nav>
    </div>
</div>
@if(isset($search_app))
    <input type="hidden" id="search_app" value="true">
@endif
@if(!isset($customSearchBlock))
<div class="swatch-dark-blue" style="position:relative">
    <div id="banner-image" class="background-media"></div>
    <div class="background-overlay grid-overlay-30" style="background-color: rgba(0,0,0,0.4)"></div>
    <div class="container">
        <div class="row element-shorter-bottom element-shorter-top">
            <div class="col-md-5">
                <form action="" ng-submit="search()">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search for a vocabulary or a concept" ng-model="filters.q" ng-debounce="500">
                        <span class="input-group-btn">
                            <button class="btn btn-primary" type="button" ng-click="search()"><i class="fa fa-search"></i> Search</button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
<button class="yellow_button feedback_button">Feedback</button>
<a href="https://documentation.ardc.edu.au/display/DOC/Research+Vocabularies" target="_blank" class="yellow_button help_button"><i class="fa fa-question-circle"></i> Help</a>
