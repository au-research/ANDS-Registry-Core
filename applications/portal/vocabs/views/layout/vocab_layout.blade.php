<!DOCTYPE html>
<html lang="en" ng-app="app" ng-controller="searchCtrl">
	@include('includes/header')
	<body>
		@include('includes/top-menu')
		<div id="content">
			@yield('content')
		</div>
        @include('includes/footer')
	</body>
</html>