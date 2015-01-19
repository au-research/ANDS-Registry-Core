@if($ro->relationships && isset($ro->relationships[0]['collection']))
<h2>Related Collections</h2>

	@foreach($ro->relationships[0]['collection'] as $col)

	<a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="this title" class="tooltip2">{{$col['title']}}</a><br />
	@endforeach

@endif

@if($ro->relationships && isset($ro->relationships[0]['party_multi']))
<h2>Organisations</h2>
<ul>
	@foreach($ro->relationships[0]['party_multi'] as $col)
	<li><a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}">{{$col['title']}}</a></li>
	@endforeach
</ul>
@endif

@if($ro->relationships && isset($ro->relationships[0]['service']))
<h2>Services</h2>
<ul>
	@foreach($ro->relationships[0]['service'] as $col)
	<li><a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}">{{$col['title']}}</a></li>
	@endforeach
</ul>
@endif

@if($ro->relationships && isset($ro->relationships[0]['activity']))
<h2>Programmes and Projects</h2>
<ul>
	@foreach($ro->relationships[0]['activity'] as $col)
	<li><a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}">{{$col['title']}}</a></li>
	@endforeach
</ul>
@endif