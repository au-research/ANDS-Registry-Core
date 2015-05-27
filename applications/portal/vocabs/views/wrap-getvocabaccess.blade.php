<?php

$cc=$vocab['licence'];
?>

<div class="panel swatch-white">
	<div class="panel-body">
		<h3>{{ $vocab['current_version']['title'] }}</h3>
		@foreach($vocab['current_version']['access_points'] as $ap)
			<div class="current-version-ap-block">
				<a class="btn btn-lg btn-block btn-primary" href="{{ $ap['uri'] }}">{{ $ap['type'] }}</a>
				<div class="btn-group btn-group-justified element element-shorter-bottom element-no-top" role="group" aria-label="...">
					<a class="btn btn-sm btn-default"><i class="fa fa-edit"></i> {{ $ap['format'] }}</a>
				</div>
			</div>
		@endforeach
		<div class="clearfix"></div>
		
		<p>
			<h4>Note:</h4>
			{{ $vocab['current_version']['note'] }}
		</p>
	</div>
	<div class="panel-body">
		<h4>Previous Versions</h4>
		<ul class="vocab-prev-version-list">
			@foreach($vocab['versions'] as $version)
				<li>
					<a href="">{{ $version['title'] }}</a>
					<span></span>
					<ul>
						@if(isset($version['access_points']))
							@foreach($version['access_points'] as $ap)
							<li><a href="{{ $ap['uri'] }}">{{ $ap['format'] }} - {{ $ap['type'] }}</a></li>
							@endforeach
						@endif
					</ul>
				</li>
			@endforeach
		</ul>
	</div>
</div>