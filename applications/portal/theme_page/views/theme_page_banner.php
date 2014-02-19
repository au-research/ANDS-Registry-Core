<?php 

/**
 * Theme Page Banner View
 * Used for displaying a smaller version of the theme page which links to the theme page itself
 * Displays on record view page that has the same secret tag as the theme page
 * 
 * @param OBJ page 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

?>

<div class="theme_page_banner">
	<div class="img_slot">
		<a href="<?php echo base_url('theme/'.$page->slug)?>"><img src="<?php echo $page->img_src ?>" alt=""></a>
	</div>
	<div class="theme_page_header">
		<h4><?php echo anchor('theme/'.$page->slug, $page->title); ?></h4>
	</div>
</div>