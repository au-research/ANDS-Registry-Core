<?php

$cc=$vocab['licence'];
?>

<div class="panel swatch-white">
	<div class="panel-body">
		<div class="version-licence">
			@if($cc=='CC-BY')
				<a href="http://creativecommons.org/licenses/by/3.0/au/" tip="Attribution"><img src="{{asset_url('images/icons/CC-BY.png', 'core')}}" class="img-cc" alt="CC-BY"></a> <br/>
				@elseif($cc=='CC-BY-SA')
				<a href="http://creativecommons.org/licenses/by-sa/3.0/au/" tip="Attribution-Shared Alike"><img src="{{asset_url('images/icons/CC-BY-SA.png', 'core')}}" class="img-cc" alt="CC-BY-SA"></a> <br/>
				@elseif($cc=='CC-BY-ND')
				<a href="http://creativecommons.org/licenses/by-nd/3.0/au/" tip="Attribution-No Derivatives"><img src="{{asset_url('images/icons/CC-BY-ND.png', 'core')}}" class="img-cc" alt="CC-BY-ND"></a> <br/>
				@elseif($cc=='CC-BY-NC')
				<a href="http://creativecommons.org/licenses/by-nc/3.0/au/" tip="Attribution-Non Commercial"><img src="{{asset_url('images/icons/CC-BY-NC.png', 'core')}}" class="img-cc" alt="CC-BY-NC"></a> <br/>
				@elseif($cc=='CC-BY-NC-SA')
				<a href="http://creativecommons.org/licenses/by-nc-sa/3.0/au/" tip="Attribution-Non Commercial-Shared Alike"><img src="{{asset_url('images/icons/CC-BY-NC-SA.png', 'core')}}" class="img-cc" alt="CC-BY-NC-SA"></a> <br/>
				@elseif($cc=='CC-BY-NC-ND')
				<a href="http://creativecommons.org/licenses/by-nc-nd/3.0/au/" tip="Attribution-Non Commercial-Non Derivatives"><img src="{{asset_url('images/icons/CC-BY-NC-ND.png', 'core')}}" class="img-cc" alt="CC-BY-NC-ND"></a> <br/>
				@else
				<span>Licence: {{sentenceCase($cc)}}</span>
			@endif
		</div>

		<h3 class="element-short-bottom">{{ $vocab['current_version']['title'] }}</h3>
		@foreach($vocab['current_version']['access_points'] as $ap)
			<div class="current-version-ap-block">
				<a class="btn btn-lg btn-block btn-primary" href="{{ $ap['uri'] }}"><i class="fa fa-cube"></i> {{ $ap['type'] }}</a>
				<div class="btn-group btn-group-justified element element-shorter-bottom element-no-top" role="group" aria-label="...">
					<a class="btn btn-sm btn-default"><i class="fa fa-edit"></i> {{ $ap['format'] }}</a>
				</div>
			</div>
		@endforeach
		<div class="clearfix"></div>
		
		<p>
			<h4>Note</h4>
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
							<li><a href="{{ $ap['uri'] }}" class="btn btn-default">{{ $ap['format'] }} - {{ $ap['type'] }}</a></li>
							@endforeach
						@endif
					</ul>
				</li>
			@endforeach
		</ul>
	</div>
</div>