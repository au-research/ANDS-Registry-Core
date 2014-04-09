<?php 
/**
 * Theme Page Content View
 * Used for displaying the theme page content in the left and right region
 * Several contents will be loaded imediately.
 * Other content will be loaded asynchronously through AJAX. Eg: search results, facet, list_ro, relations
 * 
 * @param OBJ page 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
 ?>
<?php if(isset($page[$region]) && sizeof($page[$region])>0): ?>
<?php foreach($page[$region] as $f): ?>
<div class="theme_content_section">
	<?php
		if(isset($f['title']) && trim($f['title'])!='' && isset($f['heading']) && $f['heading']){
			echo '<'.$f['heading'].'>'.$f['title'].'</'.$f['heading'].'>';
		}
	?>
	<?php if($f['type']=='html'): ?>
		<div class="widget" style="border-bottom:none;">
			<?php echo $f['content']; ?>
		</div>
	<?php endif; ?>

	<?php if($f['type']=='separator'): ?><hr/><?php endif; ?>

	<?php if($f['type']=='gallery'): ?>
		<div class="flexslider <?php echo $f['gallery_type']; ?>" <?php echo $f['gallery_type']; ?>>
			<ul class="slides">
				<?php foreach($f['gallery'] as $i): ?>
				<li <?php if($f['gallery_type']=='carousel'): ?>style="margin-left:20px; margin-right:20px;" <?php endif;?>><a href="<?php echo $i['src'] ?>" colorbox rel="<?php echo $f['title']; ?>"><img src="<?php echo $i['src'];?>" alt="" rel="<?php echo $f['title'] ?>"></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if($f['type']=='search'): ?>
		<div class="theme_search search-result hide" id="<?php echo (isset($f['search']['id'])? $f['search']['id'] : 'NOID'); ?>">
			<input type="hidden" value="<?php echo (isset($f['search']['query'])? urlencode($f['search']['query']): ''); ?>" class="theme_search_query">
			<?php if(isset($f['search']['fq'])): ?>
			<?php foreach($f['search']['fq'] as $fq): ?>
				<input type="hidden" value="<?php echo $fq['value']; ?>" class="theme_search_fq" fq-type="<?php echo $fq['name'] ?>">
			<?php endforeach; ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if($f['type']=='facet'): ?>
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