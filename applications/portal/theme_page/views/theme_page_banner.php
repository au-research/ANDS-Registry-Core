<div class="theme_page_banner">
	<div class="img_slot">
		<a href="<?php echo base_url('theme/'.$page->slug)?>"><img src="<?php echo $page->img_src ?>" alt=""></a>
	</div>
	<div class="theme_page_header">
		<h4><?php echo anchor('theme/'.$page->slug, $page->title); ?></h4>
	</div>
</div>