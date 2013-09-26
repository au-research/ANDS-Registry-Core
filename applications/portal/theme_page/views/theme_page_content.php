<?php if(isset($page[$region]) && sizeof($page[$region])>0): ?>
<?php foreach($page[$region] as $f): ?>
<div style="margin:10px 0">
	<?php if($f['type']=='html'): ?>
		<?php echo $f['content']; ?>
	<?php endif; ?>

	<?php if($f['type']=='separator'): ?><hr/><?php endif; ?>

	<?php if($f['type']=='gallery'): ?>
		<?php if($f['gallery_type']=='carousel'): ?>
			<div class="flexslider" carousel style="min-height:0;margin-bottom:60px;">
				<ul class="slides">
					<?php foreach($f['gallery'] as $i): ?>
					<li><a href="<?php echo $i['src'] ?>" rel="<?php echo $f['title'] ?>" colorbox><img src="<?php echo $i['src'];?>" alt="" rel="<?php echo $f['title'] ?>"></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
		<?php if($f['gallery_type']=='filmstrip'): ?>
			<div class="scroller" filmstrip>
				<div class="scroll left"></div>
				<div class="scroll right"></div>
				<div class="filmstrip">
					<ul>
						<?php foreach($f['gallery'] as $i): ?>
						<li><a href="<?php echo $i['src'] ?>" rel="<?php echo $f['title'] ?>" colorbox><img src="<?php echo $i['src'];?>" alt="" rel="<?php echo $f['title'] ?>"></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php if($f['type']=='search'): ?>
		<div class="theme_search search-result hide" id="<?php echo $f['search']['id']; ?>">
			<input type="hidden" value="<?php echo $f['search']['query']; ?>" class="theme_search_query">
			<?php foreach($f['search']['fq'] as $fq): ?>
				<input type="hidden" value="<?php echo $fq['value']; ?>" class="theme_search_fq" fq-type="<?php echo $fq['name'] ?>">
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if($f['type']=='facet'): ?>
		<h2><?php echo $f['title']; ?></h2>
		<div class="theme_facet" search-id="<?php echo $f['facet']['search_id'] ?>" facet-type="<?php echo $f['facet']['type'] ?>"></div>
	<?php endif; ?>

	<?php if($f['type']=='list_ro'): ?>
		<div class="list_ro">
			<?php foreach($f['list_ro'] as $i): ?>
			<input type="hidden" class="key" value="<?php echo $i['key']; ?>">
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if($f['type']=='relation'): ?>
		<div class="relation">
			<input type="hidden" class="type" value="<?php echo $f['relation']['type']; ?>">
			<input type="hidden" class="key" value="<?php echo (isset($f['relation']['key']) ? $f['relation']['key'] : ''); ?>">
		</div>
	<?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>