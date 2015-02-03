@if($ro->subjects)
<div class="swatch-white">
	<div class="panel panel-primary element-no-top element-short-bottom panel-content">
		<div class="panel-heading">
	        <a href="">Subjects</a>
	    </div>
		<div class="panel-body swatch-white">
			@foreach($ro->subjects as $col)
			<a href="">{{$col['resolved']}}</a> |
			@endforeach
		</div>
	</div>
</div>
@endif
