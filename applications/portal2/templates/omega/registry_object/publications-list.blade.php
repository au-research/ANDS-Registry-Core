@if($ro->publicationss && isset($ro->publications[0]))
<h2>Related Publications</h2>
<ul>
	@foreach($ro->publications[0] as $pub)
	<li><a href="">{{$col['title']}}</a></li>
	@endforeach
</ul>
@endif