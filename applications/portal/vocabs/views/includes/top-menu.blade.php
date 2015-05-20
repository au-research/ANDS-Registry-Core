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
					Open Vocabularies Australia
				</a>
			</div>
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
<div class="swatch-dark-blue">
	<div class="container">
		<div class="row element-shorter-bottom element-shorter-top">
			<div class="col-md-5">
				<form action="" ng-submit="search()">
					<div class="input-group">
						<input type="text" class="form-control" placeholder="Search for a vocabulary or a concept" ng-model="filters.q">
						<span class="input-group-btn">
							<button class="btn btn-primary" type="button"><i class="fa fa-search"></i> Search</button>
						</span>
					</div>
				</form>
				
			</div>
		</div>
	</div>
</div>
