<div class="sidebar-widget widget_search os-animation animated fadeInDown">
	<h3 class="sidebar-header">{{$this->user->name()}}</h3>
	<ul class="list-unstyled">
		<li>Logged-in via: {{$this->user->authMethod()}}</li>
		<li><a href="{{portal_url('profile/logout')}}" class="btn btn-danger">Logout</a></li>
	</ul>
</div>