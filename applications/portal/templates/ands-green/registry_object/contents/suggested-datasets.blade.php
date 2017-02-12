@if($ro->suggest && isset($ro->suggest['final']))
<div class="panel swatch-white">
	<div class="panel-heading">Similar datasets you may be interested in:</div>
	<div class="panel-body">
		<div class="sidebar-widget widget_recent_entries">
			<ul>
				@foreach($ro->suggest['final'] as $rs)
					<li class="clearfix">
						<div class="post-icon">
							<a href="{{portal_url($rs['slug'].'/'.$rs['id'])}}?source=suggested_datasets" class="ro_preview" ro_id="{{$rs['id']}}"><i class="fa fa-folder-open"></i></a>
						</div>
                        <a href="{{portal_url($rs['slug'].'/'.$rs['id'])}}?source=suggested_datasets" class="ro_preview" ro_id="{{$rs['id']}}" style="margin-right:5px;">{{$rs['title']}}</a>
					</li>
				@endforeach
			</ul>
		</div>
	</div>
</div>
@endif