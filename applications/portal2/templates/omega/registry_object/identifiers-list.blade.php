@if($ro->identifiers)
<h2>Identifiers</h2>
<ul>
	@foreach($ro->identifiers as $col)
	<li>{{$col['type']}} : <a href="">{{$col['value']}}</a></li>
	@endforeach
</ul>
@endif