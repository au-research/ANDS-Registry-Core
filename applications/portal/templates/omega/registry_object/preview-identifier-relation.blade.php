<div class="panel panel-primary panel-content">
	<div class="panel-heading">
		{{$record->relation_type}} 
		@if($record->related_title)
			{{$record->related_title}}
		@endif
	</div>
	<div class="panel-body">
		@if(!$pullback && $record->connections_preview_div)
			{{$record->connections_preview_div}}
		@elseif($pullback)
			<h4>{{$pullback['name']}}</h4>
			<p>{{$pullback['bio']}}</p>
			<p><a href="http://orcid.org/{{$pullback['orcid']}}">View Record in <img src="{{asset_url('img/orcid_tagline_small.png', 'base')}}" alt="" style="border: none;width: 50px;margin-top: -5px;
margin-left: 5px;"></a></p>
		@endif
	</div>
</div>