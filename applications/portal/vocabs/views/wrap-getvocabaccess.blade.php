@if(isset($vocab['current_version']['title']))

<?php
	$aps = array();
	//get file
	foreach($vocab['current_version']['version_access_points'] as $ap) {
		if (!isset($aps[$ap['type']])) $aps[$ap['type']] = array();
		array_push($aps[$ap['type']], $ap);
	}
    //set current version display date
    if(isset($vocab['current_version']['release_date'])){

        $display_date = $vocab['current_version']['release_date'];

        if(strlen(trim($vocab['current_version']['release_date']))==4){
           $display_date = $vocab['current_version']['release_date'];
        }elseif(strlen($vocab['current_version']['release_date'])==7) {
          $display_date = date("M Y",strtotime($vocab['current_version']['release_date']));
        }elseif(strlen(trim(str_replace(" 00:00:00","",$vocab['current_version']['release_date'])))==10)  {
          $display_date = date(" d M Y",strtotime($vocab['current_version']['release_date']));
        }elseif(strlen(trim(str_replace("T00:00:00.000Z","",$vocab['current_version']['release_date'])))==10)  {
          $display_date = date(" d M Y",strtotime($vocab['current_version']['release_date']));
        }elseif(strlen($vocab['current_version']['release_date']>0) && date(" d M Y",strtotime(substr($vocab['current_version']['release_date'],0,10)))!='01 Jan 1970'){
          $display_date = date(" d M Y",strtotime(substr($vocab['current_version']['release_date'],0,10)));
        }
    }
	//checking if current version has a file download and has a sesame downloads
	$hasFile = false;
	$hasSesameDownloads = false;
	$fileCounter = 0;
	foreach ($vocab['current_version']['version_access_points'] as $ap) {
		if ($ap['type']=='file') {
			$hasFile = true;
			$fileCounter++;
		}
		if ($ap['type']=='sesameDownload') $hasSesameDownloads = true;
	}

	//single file happens when there is only 1 download for a file and no other formats
	$singleFile = false;
	if ($hasFile && !$hasSesameDownloads && $fileCounter < 2) {
		$singleFile = true;
	}

	//check if there's not current version
	$hasNotCurrentVersion = false;
	foreach ($vocab['versions'] as $version) {
		if ($version['status']!='current' && $version['version_access_points']) {
			$hasNotCurrentVersion = true;
		}
	}
?>
<div class="box" ng-non-bindable>
	<div class="box-title {{ $hasNotCurrentVersion ? 'box-title-collapsible' : '' }}">
		<h4> {{ htmlspecialchars($vocab['current_version']['title']) }} </h4>
		<span class="box-tag box-tag-green"> current </span>
	</div>
	<div class="clearfix"></div>
	<div class="box-content">

		@foreach($vocab['current_version']['version_access_points'] as $ap)
		    @if(($ap['type']=='file' && !$singleFile) || ($hasSesameDownloads))
		        <a class="btn btn-lg btn-block btn-primary download-chooser"><i class="fa fa-download"></i> Download <i class="fa fa-caret-right"></i></a>
		        <?php break; ?>
		    @endif
		@endforeach

		@if($singleFile)
			@foreach($vocab['current_version']['version_access_points'] as $ap)
			    @if($ap['type']=='file')
			        <a class="btn btn-lg btn-block btn-primary" href="{{ json_decode($ap['portal_data'])->uri }}"><i class="fa fa-download"></i> Download ({{ json_decode($ap['portal_data'])->format }})</a>
			    @endif
			@endforeach
		@endif

		@foreach($vocab['current_version']['version_access_points'] as $ap)
		    @if($ap['type']=='webPage')
		    <div class="btn-group btn-group-justified element element-no-bottom element-no-top" role="group" aria-label="...">
		        <a target="_blank" class="btn btn-sm btn-default {{$ap['type']}}" href="{{ json_decode($ap['portal_data'])->uri }}"><i class="fa fa-external-link"></i> Access Web Page</a>
		    </div>
		    @endif
		@endforeach

		@foreach($vocab['current_version']['version_access_points'] as $ap)
		    @if($ap['type']=='sissvoc')
		    <div class="btn-group btn-group-justified element element-no-bottom element-no-top" role="group" aria-label="...">
		        <a target="_blank" class="btn btn-sm btn-default {{$ap['type']}}" href="{{ json_decode($ap['portal_data'])->uri }}/concept"><i class="fa fa-external-link"></i> Access Linked Data API</a>
		    </div>
		    @endif
		@endforeach

		@foreach($vocab['current_version']['version_access_points'] as $ap)
		    @if($ap['type']=='apiSparql')
		    <div class="text-center">
		        <small><a class="showsp" href="javascript:;">Show SPARQL Endpoint</a></small>
		    </div>
		    <div class="sp text-center collapse">
		    	<small>SPARQL Endpoint:</small>
		    	<p style="word-break:break-all">
		    		<a target="_blank" href="{{ json_decode($ap['portal_data'])->uri }}">{{ json_decode($ap['portal_data'])->uri }}</a>
		    	</p>
		    	<p>
		    		<a target="_blank" href="https://documentation.ands.org.au/display/DOC/SPARQL+endpoint">Learn More</a>
		    	</p>
		    </div>
		    @endif
		@endforeach
        <div class="text-center">
		{{ isset($vocab['current_version']['release_date']) ? '<span class="small"><em>released: '. $display_date."</em></span>": '' }}{{ isset($vocab['current_version']['note']) ? ' <a href="" tip="'.htmlentities($vocab['current_version']['note']).'">view notes</a>': '' }}
</div>
		<div class="download-content hidden">
		@if($hasFile && $hasSesameDownloads)
			Original:
		@endif
		<ul>
		@foreach($vocab['current_version']['version_access_points'] as $ap)
		    @if($ap['type']=='file')
	        	<li><a href="{{ json_decode($ap['portal_data'])->uri }}">{{ json_decode($ap['portal_data'])->format }}</a></li>
		    @endif
		@endforeach
		</ul>
		@foreach($vocab['current_version']['version_access_points'] as $ap)
		    @if($ap['type'] == 'sesameDownload')
		    	@if($hasFile && $hasSesameDownloads)
		    		Other Formats:
		    	@endif
		    	<ul>
		    		<?php
		    			$sesameFormats = array(
		    				"rdf" => "RDF/XML",
		    				"nt" => "N-Triples",
		    				"ttl" => "Turtle",
		    				"n3" => "Notation3",
		    				"nq" => "N-Quads",
		    				"json" => "RDF/JSON",
		    				"trix" => "TriX",
		    				"trig" => "TriG",
		    				"bin" => "Sesame Binary RDF"
		    			);
		    		?>
		    		@foreach($sesameFormats as $key=>$val)
		    		<li><a target="_blank" href="{{ json_decode($ap['portal_data'])->uri }}{{$key}}">{{ $val }}</a></li>
		    		@endforeach
		    	</ul>
		    @endif
		@endforeach
		</div>
	</div>
</div>
@endif



@foreach($vocab['versions'] as $version)
	@if($version['status']!='current' && $version['version_access_points'])
	<?php
		$hasFile = false;
		$hasSesameDownloads = false;
		$fileCounter = 0;
		foreach ($version['version_access_points'] as $ap) {
			if ($ap['type']=='file') {
				$hasFile = true;
				$fileCounter++;
			}
			if ($ap['type']=='sesameDownload') $hasSesameDownloads = true;
		}

		//single file happens when there is only 1 download for a file and no other formats
		$singleFile = false;
		if ($hasFile && !$hasSesameDownloads && $fileCounter < 2) {
			$singleFile = true;
		}
	?>
	<div class="box" ng-non-bindable>
		<div class="box-title {{ $hasNotCurrentVersion ? 'box-title-collapsible' : '' }}">
			<h4> {{ htmlspecialchars($version['title']) }} </h4>
			<span class="box-tag box-tag box-tag-{{ $version['status'] }}"> {{htmlspecialchars($version['status'])}} </span>
		</div>
		<div class="clearfix"></div>
		<div class="box-content collapse">

			@foreach($version['version_access_points'] as $ap)
			    @if(($ap['type']=='file' && !$singleFile) || ($hasSesameDownloads))
			        <a class="btn btn-lg btn-block btn-primary download-chooser"><i class="fa fa-download"></i> Download <i class="fa fa-caret-right"></i></a>
			        <?php break; ?>
			    @endif
			@endforeach

			@if($singleFile)
				@foreach($version['version_access_points'] as $ap)
				    @if($ap['type']=='file')
				        <a class="btn btn-lg btn-block btn-primary" href="{{ json_decode($ap['portal_data'])->uri }}"><i class="fa fa-download"></i> Download ({{ json_decode($ap['portal_data'])->format }})</a>
				    @endif
				@endforeach
			@endif

			@foreach($version['version_access_points'] as $ap)
			    @if($ap['type']=='webPage')
			    <div class="btn-group btn-group-justified element element-no-bottom element-no-top" role="group" aria-label="...">
			        <a target="_blank" class="btn btn-sm btn-default {{$ap['type']}}" href="{{ json_decode($ap['portal_data'])->uri }}"><i class="fa fa-external-link"></i> Access Web Page</a>
			    </div>
			    @endif
			@endforeach

			@foreach($version['version_access_points'] as $ap)
			    @if($ap['type']=='sissvoc')
			    <div class="btn-group btn-group-justified element element-no-bottom element-no-top" role="group" aria-label="...">
			        <a target="_blank" class="btn btn-sm btn-default {{$ap['type']}}" href="{{ json_decode($ap['portal_data'])->uri }}/concept"><i class="fa fa-external-link"></i> Access Linked Data API</a>
			    </div>
			    @endif
			@endforeach

			@foreach($version['version_access_points'] as $ap)
			    @if($ap['type']=='apiSparql')
			    <div class="text-center">
			        <small><a class="showsp" href="{{ json_decode($ap['portal_data'])->uri }}">Show SPARQL Endpoint</a></small>
			    </div>
			    <div class="sp text-center collapse">
			    	<small>SPARQL Endpoint:</small>
			    	<p style="word-break:break-all">
			    		<a target="_blank" href="{{ json_decode($ap['portal_data'])->uri }}">{{ json_decode($ap['portal_data'])->uri }}</a>
			    	</p>
			    	<p>
			    		<a target="_blank" href="https://documentation.ands.org.au/display/DOC/SPARQL+endpoint">Learn More</a>
			    	</p>
			    </div>
			    @endif
			@endforeach

			<p>{{ isset($version['note']) ? $version['note']: '' }}</p>

			<div class="download-content hidden">
			@foreach($version['version_access_points'] as $ap)
			    @if($ap['type']=='file')
			    	@if($hasFile && $hasSesameDownloads)
			    		Original:
			    	@endif
			        <ul>
			        	<li><a target="_blank" href="{{ json_decode($ap['portal_data'])->uri }}">{{ json_decode($ap['portal_data'])->format }}</a></li>
			        </ul>
			    @endif
			@endforeach
			@foreach($version['version_access_points'] as $ap)
			    @if($ap['type'] == 'sesameDownload')
			    	@if($hasFile && $hasSesameDownloads)
			    		Other Formats:
			    	@endif
			    	<ul>
			    		<?php
			    			$sesameFormats = array(
			    				"rdf" => "RDF/XML",
			    				"nt" => "N-Triples",
			    				"ttl" => "Turtle",
			    				"n3" => "Notation3",
			    				"nq" => "N-Quads",
			    				"json" => "RDF/JSON",
			    				"trix" => "TriX",
			    				"trig" => "TriG",
			    				"bin" => "Sesame Binary RDF"
			    			);
			    		?>
			    		@foreach($sesameFormats as $key=>$val)
			    		<li><a target="_blank" href="{{ json_decode($ap['portal_data'])->uri }}{{$key}}">{{ $val }}</a></li>
			    		@endforeach
			    	</ul>
			    @endif
			@endforeach
			</div>

		</div>
	</div>
	@endif
@endforeach