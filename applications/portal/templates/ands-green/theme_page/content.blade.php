@foreach($theme[$region] as $f)

<div class="panel swatch-white">

	<div class="panel-body">
	
		@if($f['title'])
			@if(isset($f['heading']) && $f['heading']!='')
				<{{$f['heading']}}>{{$f['title']}}</{{$f['heading']}}>
			@endif
		@endif

		<?php if($f['type']=='html'): ?>
			<?php echo $f['content']; ?>
		<?php endif; ?>

		<?php if($f['type']=='separator'): ?><hr/><?php endif; ?>

		<?php if($f['type']=='gallery'): ?>
			<div class="flexslider <?php echo $f['gallery_type']; ?>" <?php echo $f['gallery_type']; ?>>
				<ul class="slides">
					<?php foreach($f['gallery'] as $i): ?>
					<li <?php if($f['gallery_type']=='carousel'): ?><?php endif;?>><a href="<?php echo $i['src'] ?>" colorbox rel="<?php echo $f['title']; ?>"><img src="<?php echo $i['src'];?>" alt="" rel="<?php echo $f['title'] ?>"></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if($f['type']=='search'): ?>
			<div theme-search id="'{{$f['search']['id']}}'">{{$f['search']['id']}}</div>
		<?php endif; ?>

		<?php if($f['type']=='facet'): ?>
			<div theme-facet id="'{{$f['facet']['search_id']}}'">{{$f['facet']['search_id']}}</div>
		<?php endif; ?>

		<?php if($f['type']=='list_ro'): ?>
			<div list-ro>
				<ul>
					@foreach($f['list_ro'] as $rr)
					<li class="ro">{{$rr['key']}}</li>
					@endforeach
				</ul>
			</div>
		<?php endif; ?>

		<?php if($f['type']=='relation'): ?>
			<div theme-relation type="<?php echo $f['relation']['type']; ?>" key="<?php echo (isset($f['relation']['key']) ? $f['relation']['key'] : ''); ?>">
				<input type="hidden" class="type" value="<?php echo $f['relation']['type']; ?>">
				<input type="hidden" class="key" value="<?php echo (isset($f['relation']['key']) ? $f['relation']['key'] : ''); ?>">
			</div>
		<?php endif; ?>

	</div>
</div>

@endforeach