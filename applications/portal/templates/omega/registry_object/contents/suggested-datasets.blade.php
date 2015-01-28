@if($ro->suggest)
<div class="panel panel-primary panel-content swatch-white">
	<div class="panel-heading">Suggested Datasets</div>
	<div class="panel-body">
		<div class="sidebar-widget widget_recent_entries">
			<ul>
				@foreach($ro->suggest['final'] as $rs)
					<li class="clearfix">
						<div class="post-icon">
							<a href="{{portal_url($rs['slug'].'/'.$rs['id'])}}"><i class="fa fa-bolt"></i></a>
						</div>
						<a href="{{portal_url($rs['slug'].'/'.$rs['id'])}}">{{$rs['title']}}</a>
						<small>{{$rs['title']}}</small>
				@endforeach
			</ul>
		</div>
	</div>
</div>
@endif