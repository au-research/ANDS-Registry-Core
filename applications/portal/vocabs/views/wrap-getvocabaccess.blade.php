@if(isset($vocab['current_version']['title']))

<?php 

	$aps = array();

	//get file
	foreach($vocab['current_version']['access_points'] as $ap) {
		if (!isset($aps[$ap['type']])) $aps[$ap['type']] = array();
		array_push($aps[$ap['type']], $ap);
	}

?>

<div class="panel swatch-white">
	<div class="panel-body">
		
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-8 col-md-offset-2 text-center">
					<h3>{{ titlecase($vocab['current_version']['title']) }}</h3>
					<p>{{ isset($vocab['current_version']['note']) ? $vocab['current_version']['note']: '' }}</p>


					@foreach($aps as $type=>$ap)
						@if($type=='file')
							@foreach($ap as $a)
								<a class="btn btn-lg btn-block btn-primary" href="{{ $a['uri'] }}"><i class="fa fa-cube"></i> Download File</a>
							@endforeach
						@endif
					@endforeach
					@foreach($aps as $type=>$ap)
						@if($type!='file')
							@foreach($ap as $a)
								<div class="btn-group btn-group-justified element element-no-bottom element-no-top" role="group" aria-label="...">
					                <a class="btn btn-sm btn-default" href="{{ $a['uri'] }}"><i class="fa fa-edit"></i> Access {{ $a['type'] }} ({{ $a['format'] }})</a>
					            </div>
							@endforeach
						@endif
					@endforeach
				</div>
			</div>
			<div class="row element-short-top">
				<div class="col-md-12">
					<div class="">
						@foreach($vocab['versions'] as $version)
			            @if($version['status']!='current')
							<li>
								<a href="">{{ titlecase($version['title']) }} </a>
			                    <small>({{ $version['status'] }}) </small>
				                @if(isset($version['note']))
			                    <a href="" tip="{{ $version['release_date'] }} <hr />{{$version['note']}}"><i class="fa fa-info"></i></a>
			                    @endif
							</li>
			            @endif
						@endforeach
					</div>
				</div>
				
			</div>
		</div>
	</div>
</div>
@endif