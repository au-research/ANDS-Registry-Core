@if($ro->relationships)
<div class="swatch-white">
	<div class="panel panel-primary element-no-top element-short-bottom panel-content">
		<div class="panel-heading">
	        <a href="">Related</a>
	    </div>
		<div class="panel-body swatch-white">
			@if($ro->relationships && isset($ro->relationships[0]['collection']))
			<h2>Related Collections</h2>
			<ul>
				@foreach($ro->relationships[0]['collection'] as $col)

				<li><a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}">{{$col['title']}}</a></li>
				@endforeach
			</ul>
			@endif

			@if($ro->relationships && isset($ro->relationships[0]['party_multi']))
			<h2>Organisations</h2>
			<ul>
				@foreach($ro->relationships[0]['party_multi'] as $col)
				<li><a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}">{{$col['title']}}</a></li>
				@endforeach
			</ul>
			@endif

			@if($ro->relationships && isset($ro->relationships[0]['service']))
			<h2>Services</h2>
			<ul>
				@foreach($ro->relationships[0]['service'] as $col)
				<li><a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}">{{$col['title']}}</a></li>
				@endforeach
			</ul>
			@endif

			@if($ro->relationships && isset($ro->relationships[0]['activity']))
			<h2>Programmes and Projects</h2>
			<ul>
				@foreach($ro->relationships[0]['activity'] as $col)
				<li><a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" title="{{$col['title']}}">{{$col['title']}}</a></li>
				@endforeach
			</ul>
			@endif
		</div>
	</div>
</div>
@endif
