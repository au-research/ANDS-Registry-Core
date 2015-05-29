
<div class="panel swatch-white">
	<div class="panel-body">

		<h3 class="element-short-bottom">{{ titlecase($vocab['current_version']['title']) }}</h3>
        @if(isset($vocab['current_version']['access_points']))
            @foreach($vocab['current_version']['access_points'] as $ap)
                <div class="current-version-ap-block">
                    <a class="btn btn-lg btn-block btn-primary" href="{{ $ap['uri'] }}"><i class="fa fa-cube"></i> {{ $ap['type'] }}</a>
                    <div class="btn-group btn-group-justified element element-shorter-bottom element-no-top" role="group" aria-label="...">
                        <a class="btn btn-sm btn-default"><i class="fa fa-edit"></i> {{ $ap['format'] }}</a>
                    </div>
                </div>
            @endforeach
        @endif
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
            @if($version['status']!='current')
				<li>
					<a href="">{{ titlecase($version['title']) }} </a>
                    <small>({{ $version['status'] }}) </small>
	                 @if(isset($version['note']))
                    <a href="" tip="{{ $version['release_date'] }} <hr />{{$version['note']}}"><i class="fa fa-file-text-o"></i></a>
                    @endif
					<ul>
						@if(isset($version['access_points']))
							@foreach($version['access_points'] as $ap)
							<li><a href="{{ $ap['uri'] }}" class="btn btn-default">{{ $ap['format'] }} - {{ $ap['type'] }}</a></li>
							@endforeach
						@endif
					</ul>
				</li>
            @endif
			@endforeach
		</ul>
	</div>
</div>