@if($ro->subjects)
<h2>Subjects</h2>
	@foreach($ro->subjects as $col)
	<a href="">{{$col['resolved']}}</a> |
	@endforeach
@endif